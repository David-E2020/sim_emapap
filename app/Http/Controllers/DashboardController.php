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
     * Obtener métricas, KPIs globales y series estadísticas de todos los módulos.
     */
    public function metricas(Request $request)
    {
        // 1. Métricas de Catastro y Padrón de Abonados
        $catastroStats = Abonado::select(
            DB::raw('COUNT(*) as total'),
            DB::raw("COUNT(CASE WHEN estado_servicio = 'ACTIVO' THEN 1 END) as activos"),
            DB::raw("COUNT(CASE WHEN estado_servicio = 'CORTADO' THEN 1 END) as cortados"),
            DB::raw("COUNT(CASE WHEN tiene_medidor = true THEN 1 END) as con_medidor"),
            DB::raw("COUNT(CASE WHEN tiene_alcantarillado = true THEN 1 END) as con_alcantarillado"),
            DB::raw("COUNT(CASE WHEN saldo_deuda > 0 THEN 1 END) as en_mora"),
            DB::raw("COALESCE(SUM(saldo_deuda), 0) as deuda_total")
        )->first();

        // 2. Métricas de Recaudación y Cajas
        $totalRecibos = ReciboCaja::where('estado', 'VALIDO')->sum('monto_total');
        $cajasAbiertasHoy = CajaSesion::where('estado', 'ABIERTA')->count();

        // 3. Métricas del Último Período Comercial y Consumo Global
        $ultimoPeriodo = PeriodoFacturacion::whereHas('lecturas')
            ->orderBy('gestion', 'desc')
            ->orderBy('mes', 'desc')
            ->first();

        if (!$ultimoPeriodo) {
            $ultimoPeriodo = PeriodoFacturacion::orderBy('id', 'desc')->first();
        }

        $lecturasPeriodoStats = null;
        if ($ultimoPeriodo) {
            $lecturasPeriodoStats = LecturaMensual::where('id_periodo', $ultimoPeriodo->id)->select(
                DB::raw('COUNT(*) as total_lecturas'),
                DB::raw('COALESCE(SUM(total_facturado), 0) as total_facturado'),
                DB::raw('COALESCE(SUM(consumo_m3), 0) as total_m3')
            )->first();
        }

        // 4. Facturación Fiscal SIAT (Sector 13 - Servicios Básicos)
        $sinStats = Factura::select(
            DB::raw('COUNT(*) as total'),
            DB::raw("COUNT(CASE WHEN estado_factura = 'VALIDADA' THEN 1 END) as validas"),
            DB::raw("COUNT(CASE WHEN estado_factura = 'CONTINGENCIA' THEN 1 END) as contingencia"),
            DB::raw("COUNT(CASE WHEN estado_factura = 'ANULADA' THEN 1 END) as anuladas"),
            DB::raw("COALESCE(SUM(CASE WHEN estado_factura = 'VALIDADA' THEN monto_total_sujeto_iva ELSE 0 END), 0) as credito_fiscal")
        )->first();
        $eventosContingencia = EventoSignificativo::count();

        // 5. Módulos Adicionales (Contabilidad, RRHH, Correspondencia)
        $contabilidadComprobantes = Comprobante::count();
        $contabilidadCuentas = PlanCuenta::count();
        $rrhhPersonal = Persona::count();
        $correspondenciaHojasRuta = HojaRuta::count();

        // 6. Datos para Gráficos ApexCharts (Histórico de 6 Períodos con Lecturas)
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

        // Distribución por Categorías Tarifarias (Donut chart)
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

        // 7. Últimas Transacciones en Ventanilla
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

        return response()->json([
            'catastro' => [
                'total_abonados' => (int)($catastroStats->total ?? 0),
                'activos' => (int)($catastroStats->activos ?? 0),
                'cortados' => (int)($catastroStats->cortados ?? 0),
                'con_medidor' => (int)($catastroStats->con_medidor ?? 0),
                'con_alcantarillado' => (int)($catastroStats->con_alcantarillado ?? 0),
            ],
            'recaudacion' => [
                'total_recaudado' => round((float)$totalRecibos, 2),
                'cajas_abiertas' => $cajasAbiertasHoy,
            ],
            'periodo_actual' => [
                'nombre' => $ultimoPeriodo ? $ultimoPeriodo->periodo : 'Sin Período Activo',
                'mes' => $ultimoPeriodo ? $ultimoPeriodo->mes : null,
                'gestion' => $ultimoPeriodo ? $ultimoPeriodo->gestion : null,
                'total_facturado' => round((float)($lecturasPeriodoStats->total_facturado ?? 0), 2),
                'total_m3' => round((float)($lecturasPeriodoStats->total_m3 ?? 0), 2),
                'total_lecturas' => (int)($lecturasPeriodoStats->total_lecturas ?? 0),
            ],
            'sin' => [
                'total_emitidas' => (int)($sinStats->total ?? 0),
                'validas' => (int)($sinStats->validas ?? 0),
                'contingencia' => (int)($sinStats->contingencia ?? 0),
                'anuladas' => (int)($sinStats->anuladas ?? 0),
                'credito_fiscal' => round((float)($sinStats->credito_fiscal ?? 0), 2),
                'eventos_contingencia' => $eventosContingencia,
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
