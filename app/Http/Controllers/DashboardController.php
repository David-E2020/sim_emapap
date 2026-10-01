<?php

namespace App\Http\Controllers;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\CajaSesion;
use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\PeriodoFacturacion;
use App\Models\Comercial\ReciboCaja;
use App\Models\Contabilidad\Comprobante;
use App\Models\Contabilidad\PlanCuenta;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Facturacion\EventoSignificativo;
use App\Models\Facturacion\Factura;
use App\Models\Rrhh\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Obtener métricas, KPIs globales y series estadísticas de todos los módulos
     * con desgloses temporales dinámicos (Día, Semana, Mes, Mes Anterior, Año, Total, y Rango Personalizado).
     */
    public function metricas(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $hayRango = !empty($fechaDesde) && !empty($fechaHasta);

        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        // 1. Métricas de Catastro y Padrón de Abonados + Nuevos ingresos temporales
        $catastroStats = Abonado::select(
            DB::raw('COUNT(*) as total'),
            DB::raw("COUNT(CASE WHEN estado_servicio = 'ACTIVO' THEN 1 END) as activos"),
            DB::raw("COUNT(CASE WHEN estado_servicio = 'CORTADO' THEN 1 END) as cortados"),
            DB::raw("COUNT(CASE WHEN tiene_medidor = true THEN 1 END) as con_medidor"),
            DB::raw("COUNT(CASE WHEN tiene_alcantarillado = true THEN 1 END) as con_alcantarillado"),
            DB::raw("COUNT(CASE WHEN saldo_deuda > 0 THEN 1 END) as en_mora"),
            DB::raw("COALESCE(SUM(saldo_deuda), 0) as deuda_total"),
            DB::raw("COUNT(CASE WHEN fecha_ingreso = CURRENT_DATE THEN 1 END) as nuevos_hoy"),
            DB::raw("COUNT(CASE WHEN fecha_ingreso >= date_trunc('week', now()) THEN 1 END) as nuevos_semana"),
            DB::raw("COUNT(CASE WHEN fecha_ingreso >= date_trunc('month', now()) THEN 1 END) as nuevos_mes"),
            DB::raw("COUNT(CASE WHEN fecha_ingreso >= date_trunc('month', now() - interval '1 month') AND fecha_ingreso < date_trunc('month', now()) THEN 1 END) as nuevos_mes_anterior"),
            DB::raw("COUNT(CASE WHEN fecha_ingreso >= date_trunc('year', now()) THEN 1 END) as nuevos_anio")
        )->first();

        // 2. Facturación Fiscal SIAT (Agua Potable y Servicios Básicos)
        $sinStats = Factura::select(
            DB::raw('COUNT(*) as total'),
            DB::raw("COUNT(CASE WHEN estado_factura = 'VALIDADA' THEN 1 END) as validas"),
            DB::raw("COUNT(CASE WHEN estado_factura = 'CONTINGENCIA' THEN 1 END) as contingencia"),
            DB::raw("COUNT(CASE WHEN estado_factura = 'ANULADA' THEN 1 END) as anuladas"),
            DB::raw("COALESCE(SUM(CASE WHEN estado_factura = 'VALIDADA' THEN monto_total_sujeto_iva ELSE 0 END), 0) as credito_fiscal"),
            DB::raw("COUNT(CASE WHEN estado_factura = 'VALIDADA' AND fecha_emision::date = CURRENT_DATE THEN 1 END) as facturas_hoy"),
            DB::raw("COUNT(CASE WHEN estado_factura = 'VALIDADA' AND fecha_emision >= date_trunc('week', now()) THEN 1 END) as facturas_semana"),
            DB::raw("COUNT(CASE WHEN estado_factura = 'VALIDADA' AND fecha_emision >= date_trunc('month', now()) THEN 1 END) as facturas_mes"),
            DB::raw("COUNT(CASE WHEN estado_factura = 'VALIDADA' AND fecha_emision >= date_trunc('month', now() - interval '1 month') AND fecha_emision < date_trunc('month', now()) THEN 1 END) as facturas_mes_anterior"),
            DB::raw("COUNT(CASE WHEN estado_factura = 'VALIDADA' AND fecha_emision >= date_trunc('year', now()) THEN 1 END) as facturas_anio"),
            DB::raw("COALESCE(SUM(CASE WHEN estado_factura = 'VALIDADA' AND fecha_emision::date = CURRENT_DATE THEN monto_total ELSE 0 END), 0) as monto_hoy"),
            DB::raw("COALESCE(SUM(CASE WHEN estado_factura = 'VALIDADA' AND fecha_emision >= date_trunc('week', now()) THEN monto_total ELSE 0 END), 0) as monto_semana"),
            DB::raw("COALESCE(SUM(CASE WHEN estado_factura = 'VALIDADA' AND fecha_emision >= date_trunc('month', now()) THEN monto_total ELSE 0 END), 0) as monto_mes"),
            DB::raw("COALESCE(SUM(CASE WHEN estado_factura = 'VALIDADA' AND fecha_emision >= date_trunc('month', now() - interval '1 month') AND fecha_emision < date_trunc('month', now()) THEN monto_total ELSE 0 END), 0) as monto_mes_anterior"),
            DB::raw("COALESCE(SUM(CASE WHEN estado_factura = 'VALIDADA' AND fecha_emision >= date_trunc('year', now()) THEN monto_total ELSE 0 END), 0) as monto_anio"),
            DB::raw("COALESCE(SUM(CASE WHEN estado_factura = 'VALIDADA' THEN monto_total ELSE 0 END), 0) as monto_total")
        )->first();

        // 3. Recibos de Caja en Ventanilla (Trámites, Conexiones, Reconexiones, etc.)
        $recibosStats = ReciboCaja::where('estado', 'VALIDO')->select(
            DB::raw('COALESCE(SUM(monto_total), 0) as total'),
            DB::raw('COUNT(*) as count_total'),
            DB::raw('COALESCE(SUM(CASE WHEN fecha_cobro::date = CURRENT_DATE THEN monto_total ELSE 0 END), 0) as hoy'),
            DB::raw('COUNT(CASE WHEN fecha_cobro::date = CURRENT_DATE THEN 1 END) as count_hoy'),
            DB::raw("COALESCE(SUM(CASE WHEN fecha_cobro >= date_trunc('week', now()) THEN monto_total ELSE 0 END), 0) as semana"),
            DB::raw("COUNT(CASE WHEN fecha_cobro >= date_trunc('week', now()) THEN 1 END) as count_semana"),
            DB::raw("COALESCE(SUM(CASE WHEN fecha_cobro >= date_trunc('month', now()) THEN monto_total ELSE 0 END), 0) as mes"),
            DB::raw("COUNT(CASE WHEN fecha_cobro >= date_trunc('month', now()) THEN 1 END) as count_mes"),
            DB::raw("COALESCE(SUM(CASE WHEN fecha_cobro >= date_trunc('month', now() - interval '1 month') AND fecha_cobro < date_trunc('month', now()) THEN monto_total ELSE 0 END), 0) as mes_anterior"),
            DB::raw("COUNT(CASE WHEN fecha_cobro >= date_trunc('month', now() - interval '1 month') AND fecha_cobro < date_trunc('month', now()) THEN 1 END) as count_mes_anterior"),
            DB::raw("COALESCE(SUM(CASE WHEN fecha_cobro >= date_trunc('year', now()) THEN monto_total ELSE 0 END), 0) as anio"),
            DB::raw("COUNT(CASE WHEN fecha_cobro >= date_trunc('year', now()) THEN 1 END) as count_anio")
        )->first();

        $cajasAbiertasHoy = CajaSesion::where('estado', 'ABIERTA')->count();

        // 4. Si se especificó Rango de Fechas Personalizado, calcular sus métricas completas
        $rangoPersonalizado = null;
        if ($hayRango) {
            $fRango = DB::selectOne("
                SELECT COUNT(*) as count,
                       COUNT(CASE WHEN estado_factura = 'VALIDADA' THEN 1 END) as validas,
                       COALESCE(SUM(CASE WHEN estado_factura = 'VALIDADA' THEN monto_total ELSE 0 END), 0) as total,
                       COALESCE(SUM(CASE WHEN estado_factura = 'VALIDADA' THEN monto_total_sujeto_iva ELSE 0 END), 0) as credito_fiscal
                FROM facturacion.facturas
                WHERE fecha_emision::date >= ? AND fecha_emision::date <= ?
            ", [$fechaDesde, $fechaHasta]);

            $rRango = DB::selectOne("
                SELECT COUNT(*) as count, COALESCE(SUM(monto_total), 0) as total
                FROM comercial.recibos_caja
                WHERE estado = 'VALIDO' AND fecha_cobro::date >= ? AND fecha_cobro::date <= ?
            ", [$fechaDesde, $fechaHasta]);

            $sRango = DB::selectOne("
                SELECT COUNT(*) as count
                FROM comercial.abonados
                WHERE fecha_ingreso >= ? AND fecha_ingreso <= ?
            ", [$fechaDesde, $fechaHasta]);

            $cRango = DB::selectOne("
                SELECT count(l.id) as total_lecturas,
                       coalesce(sum(l.consumo_m3), 0) as total_m3,
                       coalesce(sum(l.total_facturado), 0) as total_facturado
                FROM comercial.periodos_facturacion p
                JOIN comercial.lecturas_mensuales l ON l.id_periodo = p.id
                WHERE (make_date(p.gestion, p.mes, 1) >= date_trunc('month', ?::date)::date
                   AND make_date(p.gestion, p.mes, 1) <= date_trunc('month', ?::date)::date)
            ", [$fechaDesde, $fechaHasta]);

            $rangoPersonalizado = [
                'label' => 'Rango: ' . date('d/m/Y', strtotime($fechaDesde)) . ' al ' . date('d/m/Y', strtotime($fechaHasta)),
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'recaudacion' => [
                    'monto' => round((float)($fRango->total + $rRango->total), 2),
                    'cantidad' => (int)($fRango->validas + $rRango->count),
                    'monto_agua' => round((float)$fRango->total, 2),
                    'facturas_agua' => (int)$fRango->validas,
                    'monto_ventanilla' => round((float)$rRango->total, 2),
                    'recibos_ventanilla' => (int)$rRango->count,
                    'label' => 'Rango: ' . date('d/m/Y', strtotime($fechaDesde)) . ' al ' . date('d/m/Y', strtotime($fechaHasta)),
                ],
                'socios_nuevos' => (int)($sRango->count ?? 0),
                'consumo' => [
                    'total_m3' => round((float)($cRango->total_m3 ?? 0), 2),
                    'total_lecturas' => (int)($cRango->total_lecturas ?? 0),
                    'total_facturado' => round((float)($cRango->total_facturado ?? 0), 2),
                    'label' => 'Rango: ' . date('d/m/Y', strtotime($fechaDesde)) . ' al ' . date('d/m/Y', strtotime($fechaHasta)),
                ],
                'facturacion' => [
                    'cantidad' => (int)($fRango->validas ?? 0),
                    'monto' => round((float)($fRango->total ?? 0), 2),
                    'credito_fiscal' => round((float)($fRango->credito_fiscal ?? 0), 2),
                    'label' => 'Rango: ' . date('d/m/Y', strtotime($fechaDesde)) . ' al ' . date('d/m/Y', strtotime($fechaHasta)),
                ],
            ];
        }

        // 5. Histórico Completo de los últimos 24 Períodos (Navegación Mes por Mes sin límites)
        $periodosCompletosRaw = DB::select("
            WITH per AS (
                SELECT id, periodo, mes, gestion,
                       make_date(gestion, mes, 1) as f_ini,
                       (make_date(gestion, mes, 1) + interval '1 month - 1 day')::date as f_fin
                FROM comercial.periodos_facturacion
                WHERE EXISTS (SELECT 1 FROM comercial.lecturas_mensuales WHERE id_periodo = periodos_facturacion.id)
                ORDER BY gestion DESC, mes DESC
                LIMIT 24
            ),
            lec AS (
                SELECT id_periodo, count(id) as lecturas, coalesce(sum(consumo_m3), 0) as consumo_m3, coalesce(sum(total_facturado), 0) as facturado_lecturas
                FROM comercial.lecturas_mensuales
                WHERE id_periodo IN (SELECT id FROM per)
                GROUP BY id_periodo
            ),
            fac AS (
                SELECT p.id as id_periodo, count(f.id) as facturas_siat, coalesce(sum(f.monto_total), 0) as monto_siat, coalesce(sum(f.monto_total_sujeto_iva), 0) as credito_fiscal
                FROM per p
                LEFT JOIN facturacion.facturas f ON f.estado_factura = 'VALIDADA' AND f.fecha_emision::date >= p.f_ini AND f.fecha_emision::date <= p.f_fin
                GROUP BY p.id
            ),
            rec AS (
                SELECT p.id as id_periodo, count(r.id) as recibos_caja, coalesce(sum(r.monto_total), 0) as monto_recibos
                FROM per p
                LEFT JOIN comercial.recibos_caja r ON r.estado = 'VALIDO' AND r.fecha_cobro::date >= p.f_ini AND r.fecha_cobro::date <= p.f_fin
                GROUP BY p.id
            ),
            soc AS (
                SELECT p.id as id_periodo, count(a.id) as nuevos_socios
                FROM per p
                LEFT JOIN comercial.abonados a ON a.fecha_ingreso >= p.f_ini AND a.fecha_ingreso <= p.f_fin
                GROUP BY p.id
            )
            SELECT
                p.id, p.periodo, p.mes, p.gestion, p.f_ini, p.f_fin,
                coalesce(lec.lecturas, 0) as lecturas,
                coalesce(lec.consumo_m3, 0) as consumo_m3,
                coalesce(lec.facturado_lecturas, 0) as facturado_lecturas,
                coalesce(fac.facturas_siat, 0) as facturas_siat,
                coalesce(fac.monto_siat, 0) as monto_siat,
                coalesce(fac.credito_fiscal, 0) as credito_fiscal,
                coalesce(rec.recibos_caja, 0) as recibos_caja,
                coalesce(rec.monto_recibos, 0) as monto_recibos,
                (coalesce(fac.monto_siat, 0) + coalesce(rec.monto_recibos, 0)) as recaudacion_total,
                (coalesce(fac.facturas_siat, 0) + coalesce(rec.recibos_caja, 0)) as cobros_totales,
                coalesce(soc.nuevos_socios, 0) as nuevos_socios
            FROM per p
            LEFT JOIN lec ON lec.id_periodo = p.id
            LEFT JOIN fac ON fac.id_periodo = p.id
            LEFT JOIN rec ON rec.id_periodo = p.id
            LEFT JOIN soc ON soc.id_periodo = p.id
            ORDER BY p.gestion DESC, p.mes DESC
        ");

        $periodosCompletos = collect($periodosCompletosRaw)->map(function ($row) use ($mesesNombres) {
            $nombreMes = $mesesNombres[$row->mes] ?? 'Mes ' . $row->mes;
            return [
                'id' => (int)$row->id,
                'periodo' => $row->periodo,
                'mes' => (int)$row->mes,
                'gestion' => (int)$row->gestion,
                'nombre_mes' => $nombreMes . ' ' . $row->gestion,
                'f_ini' => $row->f_ini,
                'f_fin' => $row->f_fin,
                'recaudacion' => [
                    'monto' => round((float)$row->recaudacion_total, 2),
                    'cantidad' => (int)$row->cobros_totales,
                    'monto_agua' => round((float)$row->monto_siat, 2),
                    'facturas_agua' => (int)$row->facturas_siat,
                    'monto_ventanilla' => round((float)$row->monto_recibos, 2),
                    'recibos_ventanilla' => (int)$row->recibos_caja,
                    'label' => 'Período ' . $row->periodo . ' (' . $nombreMes . ' ' . $row->gestion . ')',
                ],
                'socios_nuevos' => (int)$row->nuevos_socios,
                'consumo' => [
                    'total_m3' => round((float)$row->consumo_m3, 2),
                    'total_lecturas' => (int)$row->lecturas,
                    'total_facturado' => round((float)$row->facturado_lecturas, 2),
                    'label' => $row->periodo . ' (' . $nombreMes . ')',
                ],
                'facturacion' => [
                    'cantidad' => (int)$row->facturas_siat,
                    'monto' => round((float)$row->monto_siat, 2),
                    'credito_fiscal' => round((float)$row->credito_fiscal, 2),
                    'label' => 'Período ' . $row->periodo . ' (' . $nombreMes . ')',
                ],
            ];
        });

        // 6. Gestiones anuales completas
        $gestionesDisponibles = DB::table('comercial.periodos_facturacion as p')
            ->join('comercial.lecturas_mensuales as l', 'l.id_periodo', '=', 'p.id')
            ->select(
                'p.gestion',
                DB::raw('COUNT(l.id) as total_lecturas'),
                DB::raw('COALESCE(SUM(l.consumo_m3), 0) as total_m3'),
                DB::raw('COALESCE(SUM(l.total_facturado), 0) as total_facturado')
            )
            ->groupBy('p.gestion')
            ->orderBy('p.gestion', 'desc')
            ->get()
            ->map(function ($row) {
                return [
                    'gestion' => (int)$row->gestion,
                    'total_lecturas' => (int)$row->total_lecturas,
                    'total_m3' => round((float)$row->total_m3, 2),
                    'total_facturado' => round((float)$row->total_facturado, 2),
                ];
            });

        // Período activo (el primero de la lista o el solicitado)
        $periodoSeleccionado = null;
        if ($request->filled('id_periodo')) {
            $periodoSeleccionado = PeriodoFacturacion::find($request->id_periodo);
        } elseif ($request->filled('periodo')) {
            $periodoSeleccionado = PeriodoFacturacion::where('periodo', $request->periodo)->first();
        }

        if (!$periodoSeleccionado) {
            $periodoSeleccionado = PeriodoFacturacion::whereHas('lecturas')
                ->orderBy('gestion', 'desc')
                ->orderBy('mes', 'desc')
                ->first();

            if (!$periodoSeleccionado) {
                $periodoSeleccionado = PeriodoFacturacion::orderBy('id', 'desc')->first();
            }
        }

        $lecturasPeriodoStats = null;
        if ($periodoSeleccionado) {
            $lecturasPeriodoStats = LecturaMensual::where('id_periodo', $periodoSeleccionado->id)->select(
                DB::raw('COUNT(*) as total_lecturas'),
                DB::raw('COALESCE(SUM(total_facturado), 0) as total_facturado'),
                DB::raw('COALESCE(SUM(consumo_m3), 0) as total_m3')
            )->first();
        }

        $eventosContingencia = EventoSignificativo::count();

        // 7. Módulos Adicionales (Contabilidad, RRHH, Correspondencia)
        $contabilidadComprobantes = Comprobante::count();
        $contabilidadCuentas = PlanCuenta::count();
        $rrhhPersonal = Persona::count();
        $correspondenciaHojasRuta = HojaRuta::count();

        // 8. Datos para Gráficos ApexCharts (Histórico de 6 Períodos con Lecturas)
        $periodosGrafico = PeriodoFacturacion::whereHas('lecturas')
            ->orderBy('gestion', 'desc')
            ->orderBy('mes', 'desc')
            ->take(6)
            ->get()
            ->reverse();

        $idsPeriodos = $periodosGrafico->pluck('id');
        $statsPeriodos = LecturaMensual::whereIn('id_periodo', $idsPeriodos)
            ->select('id_periodo', DB::raw('SUM(total_facturado) as total_monto'), DB::raw('SUM(consumo_m3) as total_m3'))
            ->groupBy('id_periodo')
            ->get()
            ->keyBy('id_periodo');

        $chartCategories = [];
        $chartFacturado = [];
        $chartConsumoM3 = [];

        foreach ($periodosGrafico as $p) {
            $st = $statsPeriodos->get($p->id);
            $chartCategories[] = $p->periodo;
            $chartFacturado[] = $st ? round((float)$st->total_monto, 2) : 0.00;
            $chartConsumoM3[] = $st ? round((float)$st->total_m3, 1) : 0.0;
        }

        // Distribución por Categorías Tarifarias (Donut chart - 100% Datos Reales)
        $categorias = CategoriaTarifaria::all()->keyBy('id');
        $abonadosPorCat = Abonado::select('id_categoria', DB::raw('count(*) as total'))
            ->groupBy('id_categoria')
            ->get();

        $donutLabels = [];
        $donutSeries = [];
        foreach ($abonadosPorCat as $row) {
            $cat = $categorias->get($row->id_categoria);
            $donutLabels[] = $cat ? $cat->nombre : 'OTRA';
            $donutSeries[] = (int)$row->total;
        }

        // 9. Últimas Transacciones en Ventanilla (100% Datos Reales de Recibos de Caja)
        $ultimosPagos = ReciboCaja::with('abonado')
            ->orderBy('id', 'desc')
            ->take(6)
            ->get()
            ->map(function ($pago) {
                return [
                    'id' => $pago->id,
                    'nro_recibo' => $pago->numero_recibo,
                    'codigo_socio' => $pago->abonado ? $pago->abonado->codigo : '-',
                    'titular' => $pago->nombre_cliente ?: ($pago->abonado ? $pago->abonado->nombre_completo : 'VENTANILLA'),
                    'fecha_pago' => $pago->fecha_cobro ? date('d/m/Y H:i', strtotime($pago->fecha_cobro)) : '-',
                    'concepto' => str_replace('_', ' ', $pago->concepto_tipo),
                    'monto' => round((float)$pago->monto_total, 2),
                ];
            });

        // Estructura de Recaudación Combinada (Agua Facturada + Recibos Ventanilla)
        $desgloseRecaudacion = [
            'hoy' => [
                'monto' => round((float)($sinStats->monto_hoy + $recibosStats->hoy), 2),
                'cantidad' => (int)($sinStats->facturas_hoy + $recibosStats->count_hoy),
                'monto_agua' => round((float)$sinStats->monto_hoy, 2),
                'facturas_agua' => (int)$sinStats->facturas_hoy,
                'monto_ventanilla' => round((float)$recibosStats->hoy, 2),
                'recibos_ventanilla' => (int)$recibosStats->count_hoy,
                'label' => 'Hoy (' . date('d/m/Y') . ')',
            ],
            'semana' => [
                'monto' => round((float)($sinStats->monto_semana + $recibosStats->semana), 2),
                'cantidad' => (int)($sinStats->facturas_semana + $recibosStats->count_semana),
                'monto_agua' => round((float)$sinStats->monto_semana, 2),
                'facturas_agua' => (int)$sinStats->facturas_semana,
                'monto_ventanilla' => round((float)$recibosStats->semana, 2),
                'recibos_ventanilla' => (int)$recibosStats->count_semana,
                'label' => 'Esta Semana',
            ],
            'mes' => [
                'monto' => round((float)($sinStats->monto_mes + $recibosStats->mes), 2),
                'cantidad' => (int)($sinStats->facturas_mes + $recibosStats->count_mes),
                'monto_agua' => round((float)$sinStats->monto_mes, 2),
                'facturas_agua' => (int)$sinStats->facturas_mes,
                'monto_ventanilla' => round((float)$recibosStats->mes, 2),
                'recibos_ventanilla' => (int)$recibosStats->count_mes,
                'label' => 'Este Mes (' . date('m/Y') . ')',
            ],
            'mes_anterior' => [
                'monto' => round((float)($sinStats->monto_mes_anterior + $recibosStats->mes_anterior), 2),
                'cantidad' => (int)($sinStats->facturas_mes_anterior + $recibosStats->count_mes_anterior),
                'monto_agua' => round((float)$sinStats->monto_mes_anterior, 2),
                'facturas_agua' => (int)$sinStats->facturas_mes_anterior,
                'monto_ventanilla' => round((float)$recibosStats->mes_anterior, 2),
                'recibos_ventanilla' => (int)$recibosStats->count_mes_anterior,
                'label' => 'Mes Anterior',
            ],
            'anio' => [
                'monto' => round((float)($sinStats->monto_anio + $recibosStats->anio), 2),
                'cantidad' => (int)($sinStats->facturas_anio + $recibosStats->count_anio),
                'monto_agua' => round((float)$sinStats->monto_anio, 2),
                'facturas_agua' => (int)$sinStats->facturas_anio,
                'monto_ventanilla' => round((float)$recibosStats->anio, 2),
                'recibos_ventanilla' => (int)$recibosStats->count_anio,
                'label' => 'Gestión ' . date('Y'),
            ],
            'total' => [
                'monto' => round((float)($sinStats->monto_total + $recibosStats->total), 2),
                'cantidad' => (int)($sinStats->validas + $recibosStats->count_total),
                'monto_agua' => round((float)$sinStats->monto_total, 2),
                'facturas_agua' => (int)$sinStats->validas,
                'monto_ventanilla' => round((float)$recibosStats->total, 2),
                'recibos_ventanilla' => (int)$recibosStats->count_total,
                'label' => 'Histórico Acumulado',
            ],
        ];

        // Estructura de Facturación SIAT
        $desgloseFacturacion = [
            'hoy' => [
                'cantidad' => (int)($sinStats->facturas_hoy ?? 0),
                'monto' => round((float)($sinStats->monto_hoy ?? 0), 2),
                'label' => 'Hoy (' . date('d/m/Y') . ')',
            ],
            'semana' => [
                'cantidad' => (int)($sinStats->facturas_semana ?? 0),
                'monto' => round((float)($sinStats->monto_semana ?? 0), 2),
                'label' => 'Esta Semana',
            ],
            'mes' => [
                'cantidad' => (int)($sinStats->facturas_mes ?? 0),
                'monto' => round((float)($sinStats->monto_mes ?? 0), 2),
                'label' => 'Este Mes (' . date('m/Y') . ')',
            ],
            'mes_anterior' => [
                'cantidad' => (int)($sinStats->facturas_mes_anterior ?? 0),
                'monto' => round((float)($sinStats->monto_mes_anterior ?? 0), 2),
                'label' => 'Mes Anterior',
            ],
            'anio' => [
                'cantidad' => (int)($sinStats->facturas_anio ?? 0),
                'monto' => round((float)($sinStats->monto_anio ?? 0), 2),
                'label' => 'Gestión ' . date('Y'),
            ],
            'total' => [
                'cantidad' => (int)($sinStats->validas ?? 0),
                'monto' => round((float)($sinStats->monto_total ?? 0), 2),
                'label' => 'Histórico Acumulado',
            ],
        ];

        // Estructura de Catastro y Nuevos Socios
        $nuevosSocios = [
            'hoy' => (int)($catastroStats->nuevos_hoy ?? 0),
            'semana' => (int)($catastroStats->nuevos_semana ?? 0),
            'mes' => (int)($catastroStats->nuevos_mes ?? 0),
            'mes_anterior' => (int)($catastroStats->nuevos_mes_anterior ?? 0),
            'anio' => (int)($catastroStats->nuevos_anio ?? 0),
            'total' => (int)($catastroStats->total ?? 0),
        ];

        if ($hayRango && $rangoPersonalizado) {
            $desgloseRecaudacion['personalizado'] = $rangoPersonalizado['recaudacion'];
            $desgloseFacturacion['personalizado'] = $rangoPersonalizado['facturacion'];
            $nuevosSocios['personalizado'] = $rangoPersonalizado['socios_nuevos'];
        }

        return response()->json([
            'servidor' => [
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s'),
                'timestamp' => now()->toISOString(),
                'zona_horaria' => config('app.timezone', 'America/La_Paz'),
            ],
            'filtro_personalizado' => $hayRango ? $rangoPersonalizado : null,
            'periodos_completos' => $periodosCompletos,
            'catastro' => [
                'total_abonados' => (int)($catastroStats->total ?? 0),
                'activos' => (int)($catastroStats->activos ?? 0),
                'cortados' => (int)($catastroStats->cortados ?? 0),
                'con_medidor' => (int)($catastroStats->con_medidor ?? 0),
                'con_alcantarillado' => (int)($catastroStats->con_alcantarillado ?? 0),
                'nuevos' => $nuevosSocios,
            ],
            'recaudacion' => [
                'total_recaudado' => round((float)($sinStats->monto_total + $recibosStats->total), 2),
                'total_recibos_ventanilla' => round((float)($recibosStats->total ?? 0), 2),
                'total_facturacion_agua' => round((float)($sinStats->monto_total ?? 0), 2),
                'cajas_abiertas' => $cajasAbiertasHoy,
                'desglose' => $desgloseRecaudacion,
            ],
            'periodo_actual' => [
                'id' => $periodoSeleccionado ? $periodoSeleccionado->id : null,
                'nombre' => $periodoSeleccionado ? $periodoSeleccionado->periodo : 'Sin Período Activo',
                'mes' => $periodoSeleccionado ? $periodoSeleccionado->mes : null,
                'gestion' => $periodoSeleccionado ? $periodoSeleccionado->gestion : null,
                'total_facturado' => round((float)($lecturasPeriodoStats->total_facturado ?? 0), 2),
                'total_m3' => round((float)($lecturasPeriodoStats->total_m3 ?? 0), 2),
                'total_lecturas' => (int)($lecturasPeriodoStats->total_lecturas ?? 0),
            ],
            'periodos_disponibles' => $periodosCompletos->map(fn($p) => [
                'id' => $p['id'],
                'periodo' => $p['periodo'],
                'mes' => $p['mes'],
                'gestion' => $p['gestion'],
                'total_lecturas' => $p['consumo']['total_lecturas'],
                'total_m3' => $p['consumo']['total_m3'],
                'total_facturado' => $p['consumo']['total_facturado'],
            ]),
            'gestiones_disponibles' => $gestionesDisponibles,
            'sin' => [
                'total_emitidas' => (int)($sinStats->total ?? 0),
                'validas' => (int)($sinStats->validas ?? 0),
                'contingencia' => (int)($sinStats->contingencia ?? 0),
                'anuladas' => (int)($sinStats->anuladas ?? 0),
                'credito_fiscal' => round((float)($sinStats->credito_fiscal ?? 0), 2),
                'eventos_contingencia' => $eventosContingencia,
                'desglose' => $desgloseFacturacion,
            ],
            'mora' => [
                'socios_en_mora' => (int)($catastroStats->en_mora ?? 0),
                'deuda_total' => round((float)($catastroStats->deuda_total ?? 0), 2),
            ],
            'modulos' => [
                'contabilidad_comprobantes' => $contabilidadComprobantes,
                'contabilidad_cuentas' => $contabilidadCuentas,
                'rrhh_personal' => $rrhhPersonal,
                'correspondencia_hojas_ruta' => $correspondenciaHojasRuta,
            ],
            'graficos' => [
                'categorias' => $chartCategories,
                'facturado' => $chartFacturado,
                'consumo_m3' => $chartConsumoM3,
                'donut_labels' => $donutLabels,
                'donut_series' => $donutSeries,
            ],
            'ultimos_pagos' => $ultimosPagos,
        ]);
    }
}
