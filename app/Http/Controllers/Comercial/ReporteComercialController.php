<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\Abonado;
use App\Models\Comercial\CajaMovimiento;
use App\Models\Comercial\CajaSesion;
use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\ConvenioCuota;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\PeriodoFacturacion;
use App\Models\Comercial\ReciboCaja;
use App\Models\Comercial\Zona;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\User;
use App\Services\Comercial\ReporteRecaudacionConsolidadaPdfService;
use App\Services\Comercial\ReportesOperativosPdfService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteComercialController extends Controller
{
    public function __construct(
        protected ReporteRecaudacionConsolidadaPdfService $pdfConsolidadoService,
        protected ReportesOperativosPdfService $pdfOperativosService
    ) {}

    /**
     * Reporte consolidado de recaudación y cuadre de caja (Día, Semana, Mes o Rango).
     */
    public function recaudacionConsolidada(Request $request): JsonResponse
    {
        $datos = $this->obtenerDatosRecaudacionConsolidada($request);

        return response()->json([
            'success' => true,
            'data' => $datos,
        ], Response::HTTP_OK);
    }

    /**
     * Descarga de la Planilla Oficial de Recaudación Consolidada en PDF (Snappy).
     */
    public function descargarPdfConsolidado(Request $request): Response
    {
        $datos = $this->obtenerDatosRecaudacionConsolidada($request);
        $pdf = $this->pdfConsolidadoService->generarPdf($datos);

        $nombreArchivo = sprintf(
            'Recaudacion_Consolidada_%s_al_%s.pdf',
            str_replace('/', '-', $datos['periodo']['desde']),
            str_replace('/', '-', $datos['periodo']['hasta'])
        );

        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$nombreArchivo}\"",
        ]);
    }

    /**
     * Exportación de la recaudación consolidada a CSV (compatible con Excel con BOM UTF-8).
     */
    public function exportarCsvConsolidado(Request $request): StreamedResponse
    {
        $datos = $this->obtenerDatosRecaudacionConsolidada($request);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"Recaudacion_EMAPAP_{$datos['periodo']['desde']}_a_{$datos['periodo']['hasta']}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($datos) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para visualización correcta de tildes y caracteres en Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezado institucional
            fputcsv($handle, ['EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO PATACAMAYA - EMAPAP']);
            fputcsv($handle, ['PLANILLA DE RECAUDACIÓN Y CUADRE DE CAJA']);
            fputcsv($handle, ['Período:', $datos['periodo']['etiqueta'], 'Desde:', $datos['periodo']['desde'], 'Hasta:', $datos['periodo']['hasta']]);
            fputcsv($handle, []);

            // Resumen de Métricas
            fputcsv($handle, ['--- RESUMEN EJECUTIVO DE RECAUDACIÓN ---']);
            fputcsv($handle, ['Métrica', 'Monto (Bs)']);
            fputcsv($handle, ['Total General Recaudado', number_format((float) $datos['metricas']['total_recaudado'], 2, '.', '')]);
            fputcsv($handle, ['Total Efectivo Físico (Gaveta)', number_format((float) $datos['metricas']['total_efectivo'], 2, '.', '')]);
            fputcsv($handle, ['Total QR / Banco Unión', number_format((float) $datos['metricas']['total_qr_banco'], 2, '.', '')]);
            fputcsv($handle, ['Fondo Inicial de Apertura Cajas', number_format((float) $datos['metricas']['total_fondo_inicial'], 2, '.', '')]);
            fputcsv($handle, ['Ingresos Menores de Caja Chica', number_format((float) $datos['metricas']['total_ingresos_extra'], 2, '.', '')]);
            fputcsv($handle, ['Egresos de Caja Chica', number_format((float) $datos['metricas']['total_egresos_extra'], 2, '.', '')]);
            fputcsv($handle, ['Total Efectivo Esperado en Arqueos', number_format((float) $datos['metricas']['total_esperado_efectivo'], 2, '.', '')]);
            fputcsv($handle, ['Total Físico Declarado por Cajeros', number_format((float) $datos['metricas']['total_declarado_fisico'], 2, '.', '')]);
            fputcsv($handle, ['Diferencia Neta de Cuadratura', number_format((float) $datos['metricas']['diferencia_neta'], 2, '.', '')]);
            fputcsv($handle, ['Total Transacciones Realizadas', $datos['metricas']['total_transacciones']]);
            fputcsv($handle, []);

            // Desglose por Rubros
            fputcsv($handle, ['--- DISTRIBUCIÓN POR RUBROS CONTABLES ---']);
            fputcsv($handle, ['Rubro Contable', 'Transacciones', 'Efectivo (Bs)', 'QR/Banco (Bs)', 'Total (Bs)']);
            foreach ($datos['por_rubro'] as $r) {
                fputcsv($handle, [
                    $r['nombre'],
                    $r['cantidad'],
                    number_format((float) $r['efectivo'], 2, '.', ''),
                    number_format((float) $r['qr'], 2, '.', ''),
                    number_format((float) $r['total'], 2, '.', ''),
                ]);
            }
            fputcsv($handle, []);

            // Desglose por Caja / Ventanilla
            fputcsv($handle, ['--- RECAUDACIÓN POR VENTANILLA / CAJA ---']);
            fputcsv($handle, ['Caja / Punto de Venta', 'Turnos', 'Transacciones', 'Efectivo (Bs)', 'QR/Banco (Bs)', 'Total Cobrado (Bs)']);
            foreach ($datos['por_caja'] as $c) {
                fputcsv($handle, [
                    $c['caja'],
                    $c['turnos'],
                    $c['transacciones'],
                    number_format((float) $c['efectivo'], 2, '.', ''),
                    number_format((float) $c['qr'], 2, '.', ''),
                    number_format((float) $c['total'], 2, '.', ''),
                ]);
            }
            fputcsv($handle, []);

            // Desglose por Cajero
            fputcsv($handle, ['--- RECAUDACIÓN POR CAJERO / OPERADOR ---']);
            fputcsv($handle, ['Cajero(a)', 'Turnos', 'Transacciones', 'Efectivo (Bs)', 'QR/Banco (Bs)', 'Total Cobrado (Bs)']);
            foreach ($datos['por_cajero'] as $cj) {
                fputcsv($handle, [
                    $cj['cajero'],
                    $cj['turnos'],
                    $cj['transacciones'],
                    number_format((float) $cj['efectivo'], 2, '.', ''),
                    number_format((float) $cj['qr'], 2, '.', ''),
                    number_format((float) $cj['total'], 2, '.', ''),
                ]);
            }
            fputcsv($handle, []);

            // Detalle de Turnos
            fputcsv($handle, ['--- LISTADO DE TURNOS Y ARQUEOS ---']);
            fputcsv($handle, ['Nro Turno', 'Ventanilla', 'Cajero', 'Apertura', 'Cierre', 'Fondo Inicial', 'Ventas Ef.', 'Ventas QR', 'Esperado', 'Declarado', 'Diferencia', 'Estado']);
            foreach ($datos['turnos'] as $t) {
                fputcsv($handle, [
                    $t['numero_sesion'],
                    $t['caja_nombre'],
                    $t['cajero_nombre'],
                    $t['fecha_apertura'],
                    $t['fecha_cierre'] ?? 'EN CURSO',
                    number_format((float) $t['monto_apertura'], 2, '.', ''),
                    number_format((float) $t['monto_ventas_efectivo'], 2, '.', ''),
                    number_format((float) $t['monto_ventas_qr_banco'], 2, '.', ''),
                    number_format((float) $t['monto_esperado_efectivo'], 2, '.', ''),
                    number_format((float) $t['monto_cierre_declarado'], 2, '.', ''),
                    number_format((float) $t['diferencia'], 2, '.', ''),
                    $t['estado'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Helper centralizado para consolidar métricas, rubros, cajas, cajeros y turnos.
     */
    private function obtenerDatosRecaudacionConsolidada(Request $request): array
    {
        $tipo = (string) $request->input('periodo_tipo', 'hoy');
        $ahora = Carbon::now();

        switch ($tipo) {
            case 'hoy':
                $fechaInicio = $ahora->copy()->startOfDay();
                $fechaFin = $ahora->copy()->endOfDay();
                $etiqueta = 'Hoy (' . $ahora->format('d/m/Y') . ')';
                break;
            case 'semana':
                $fechaInicio = $ahora->copy()->startOfWeek();
                $fechaFin = $ahora->copy()->endOfWeek();
                $etiqueta = 'Esta Semana (' . $fechaInicio->format('d/m/Y') . ' al ' . $fechaFin->format('d/m/Y') . ')';
                break;
            case 'mes':
                $fechaInicio = $ahora->copy()->startOfMonth();
                $fechaFin = $ahora->copy()->endOfMonth();
                $etiqueta = 'Este Mes (' . $ahora->translatedFormat('F Y') . ')';
                break;
            case 'mes_anterior':
                $mesAnt = $ahora->copy()->subMonth();
                $fechaInicio = $mesAnt->copy()->startOfMonth();
                $fechaFin = $mesAnt->copy()->endOfMonth();
                $etiqueta = 'Mes Anterior (' . $mesAnt->translatedFormat('F Y') . ')';
                break;
            case 'personalizado':
            default:
                $fi = $request->input('fecha_inicio') ?? $request->input('fechaDesde') ?? $request->input('fecha', $ahora->toDateString());
                $ff = $request->input('fecha_fin') ?? $request->input('fechaHasta') ?? $fi;
                $fechaInicio = Carbon::parse($fi)->startOfDay();
                $fechaFin = Carbon::parse($ff)->endOfDay();
                $etiqueta = 'Del ' . $fechaInicio->format('d/m/Y') . ' al ' . $fechaFin->format('d/m/Y');
                break;
        }

        $idPuntoVenta = $request->filled('id_punto_venta') ? (int) $request->input('id_punto_venta') : null;
        $idCajero = $request->filled('id_cajero') ? (int) $request->input('id_cajero') : null;
        $metodoPago = $request->filled('metodo_pago') ? (int) $request->input('metodo_pago') : null;

        // 1. Estadísticas de Lecturas Mensuales (Agua Potable y Alcantarillado)
        $queryLecturas = DB::table('comercial.lecturas_mensuales as l')
            ->leftJoin('facturacion.facturas as f', 'l.id_factura', '=', 'f.id')
            ->leftJoin('comercial.caja_sesiones as s', 'l.id_sesion_caja', '=', 's.id')
            ->where('l.estado_pago', 'PAGADO')
            ->whereBetween('l.fecha_pago', [$fechaInicio, $fechaFin]);

        if ($idCajero) {
            $queryLecturas->where('l.id_cajero', $idCajero);
        }
        if ($idPuntoVenta) {
            $queryLecturas->where(function ($q) use ($idPuntoVenta) {
                $q->where('s.id_punto_venta', $idPuntoVenta);
                if ($idPuntoVenta === 1) {
                    $q->orWhereNull('s.id_punto_venta');
                }
            });
        }
        if ($metodoPago) {
            $queryLecturas->where(DB::raw('COALESCE(f.codigo_metodo_pago, 1)'), $metodoPago);
        }

        $lecturasStats = (clone $queryLecturas)->selectRaw('
            COUNT(*) as cantidad_total,
            SUM(COALESCE(l.monto_agua, 0)) as total_agua,
            SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) = 1 THEN COALESCE(l.monto_agua, 0) ELSE 0 END) as agua_efectivo,
            SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) != 1 THEN COALESCE(l.monto_agua, 0) ELSE 0 END) as agua_qr,
            SUM(COALESCE(l.monto_alcantarillado, 0)) as total_alcantarillado,
            SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) = 1 THEN COALESCE(l.monto_alcantarillado, 0) ELSE 0 END) as alcantarillado_efectivo,
            SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) != 1 THEN COALESCE(l.monto_alcantarillado, 0) ELSE 0 END) as alcantarillado_qr,
            COUNT(CASE WHEN COALESCE(l.monto_descuento_ley1886, 0) > 0 THEN 1 END) as cant_ley1886,
            SUM(COALESCE(l.monto_descuento_ley1886, 0)) as total_ley1886,
            SUM(COALESCE(l.total_facturado, 0)) as total_facturado,
            SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) = 1 THEN COALESCE(l.total_facturado, 0) ELSE 0 END) as total_efectivo,
            SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) != 1 THEN COALESCE(l.total_facturado, 0) ELSE 0 END) as total_qr
        ')->first();

        $cantLecturas = (int) ($lecturasStats?->cantidad_total ?? 0);
        $aguaEfectivo = (float) ($lecturasStats?->agua_efectivo ?? 0);
        $aguaQr = (float) ($lecturasStats?->agua_qr ?? 0);
        $totalAgua = round($aguaEfectivo + $aguaQr, 2);

        $alcantarilladoEfectivo = (float) ($lecturasStats?->alcantarillado_efectivo ?? 0);
        $alcantarilladoQr = (float) ($lecturasStats?->alcantarillado_qr ?? 0);
        $totalAlcantarillado = round($alcantarilladoEfectivo + $alcantarilladoQr, 2);

        $cantLey1886 = (int) ($lecturasStats?->cant_ley1886 ?? 0);
        $descLey1886 = (float) ($lecturasStats?->total_ley1886 ?? 0);

        $totalLecturasFacturado = (float) ($lecturasStats?->total_facturado ?? 0);
        $lecturasTotalEf = (float) ($lecturasStats?->total_efectivo ?? 0);
        $lecturasTotalQr = (float) ($lecturasStats?->total_qr ?? 0);

        // 2. Cuotas de Convenios
        $queryCuotas = DB::table('comercial.convenio_cuotas as c')
            ->leftJoin('facturacion.facturas as f', 'c.id_factura', '=', 'f.id')
            ->leftJoin('comercial.caja_sesiones as s', 'c.id_sesion_caja', '=', 's.id')
            ->where('c.estado_pago', 'PAGADO')
            ->whereBetween('c.fecha_pago', [$fechaInicio, $fechaFin]);

        if ($idCajero) {
            $queryCuotas->where('c.id_cajero', $idCajero);
        }
        if ($idPuntoVenta) {
            $queryCuotas->where(function ($q) use ($idPuntoVenta) {
                $q->where('s.id_punto_venta', $idPuntoVenta);
                if ($idPuntoVenta === 1) {
                    $q->orWhereNull('s.id_punto_venta');
                }
            });
        }
        if ($metodoPago) {
            $queryCuotas->where(DB::raw('COALESCE(f.codigo_metodo_pago, 1)'), $metodoPago);
        }

        $cuotasStats = (clone $queryCuotas)->selectRaw('
            COUNT(*) as cantidad_total,
            SUM(COALESCE(c.monto_cuota, 0)) as total_cuotas,
            SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) = 1 THEN COALESCE(c.monto_cuota, 0) ELSE 0 END) as cuotas_efectivo,
            SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) != 1 THEN COALESCE(c.monto_cuota, 0) ELSE 0 END) as cuotas_qr
        ')->first();

        $cantCuotas = (int) ($cuotasStats?->cantidad_total ?? 0);
        $cuotasEfectivo = (float) ($cuotasStats?->cuotas_efectivo ?? 0);
        $cuotasQr = (float) ($cuotasStats?->cuotas_qr ?? 0);
        $totalConvenios = round($cuotasEfectivo + $cuotasQr, 2);

        // 3. Recibos de Caja (Aportes e Instalaciones de Conexión, Reconexiones, etc.)
        $queryRecibos = DB::table('comercial.recibos_caja as r')
            ->leftJoin('comercial.caja_sesiones as s', 'r.id_sesion_caja', '=', 's.id')
            ->where('r.estado', 'VALIDO')
            ->whereBetween('r.fecha_cobro', [$fechaInicio, $fechaFin]);

        if ($idCajero) {
            $queryRecibos->where('r.id_cajero', $idCajero);
        }
        if ($idPuntoVenta) {
            $queryRecibos->where(function ($q) use ($idPuntoVenta) {
                $q->where('s.id_punto_venta', $idPuntoVenta);
                if ($idPuntoVenta === 1) {
                    $q->orWhereNull('s.id_punto_venta');
                }
            });
        }
        if ($metodoPago && $metodoPago !== 1) {
            $queryRecibos->whereRaw('1 = 0');
        }

        $recibosPorRubro = (clone $queryRecibos)->selectRaw("
            CASE
                WHEN LOWER(r.descripcion) LIKE '%alcantarillado%' THEN 'Conexiones e Instalaciones (Alcantarillado)'
                WHEN LOWER(r.descripcion) LIKE '%aportes/inst. agua%' OR LOWER(r.descripcion) LIKE '%instalacion de agua%' OR r.concepto_tipo IN ('APORTE', 'INSTALACION', 'DERECHO_CONEXION') THEN 'Conexiones e Instalaciones (Agua Potable)'
                WHEN r.concepto_tipo = 'RECONEXION' OR LOWER(r.descripcion) LIKE '%reconexion%' THEN 'Reconexiones de Servicio'
                WHEN r.concepto_tipo = 'CAMBIO_NOMBRE' OR LOWER(r.descripcion) LIKE '%cambio de nombre%' THEN 'Cambios de Titularidad / Nombre'
                WHEN LOWER(r.descripcion) LIKE '%medidor%' OR LOWER(r.descripcion) LIKE '%accesorios%' THEN 'Materiales y Servicios Operativos'
                ELSE 'Otros Trámites y Multas'
            END as rubro_nombre,
            COUNT(*) as transacciones,
            SUM(COALESCE(r.monto_total, 0)) as total
        ")->groupBy('rubro_nombre')->get()->keyBy('rubro_nombre');

        $cantRecibos = (int) (clone $queryRecibos)->count();
        $totalRecibos = (float) (clone $queryRecibos)->sum('r.monto_total');

        // 4. Sesiones de Caja
        $querySesiones = CajaSesion::with(['puntoVenta', 'cajero', 'supervisor'])
            ->whereBetween('fecha_apertura', [$fechaInicio, $fechaFin]);

        if ($idPuntoVenta) {
            $querySesiones->where('id_punto_venta', $idPuntoVenta);
        }
        if ($idCajero) {
            $querySesiones->where('id_cajero', $idCajero);
        }
        $sesiones = $querySesiones->orderByDesc('id')->get();

        // 5. Movimientos de Caja Chica
        $queryMovimientos = CajaMovimiento::with('sesion')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        if ($idPuntoVenta) {
            $queryMovimientos->whereHas('sesion', fn($q) => $q->where('id_punto_venta', $idPuntoVenta));
        }
        $movimientos = $queryMovimientos->get();

        // --- CÁLCULO DE TOTALES GENERALES ---
        $totalGeneralRecaudado = round($totalLecturasFacturado + $totalConvenios + $totalRecibos, 2);
        $totalEfectivo = round($lecturasTotalEf + $cuotasEfectivo + $totalRecibos, 2);
        $totalQr = round($lecturasTotalQr + $cuotasQr, 2);
        $totalTransacciones = $cantLecturas + $cantCuotas + $cantRecibos;

        $totalFondoInicial = (float) $sesiones->sum('monto_apertura');
        $totalIngresosExtra = (float) $movimientos->where('tipo', 'INGRESO')->sum('monto');
        $totalEgresosExtra = (float) $movimientos->where('tipo', 'EGRESO')->sum('monto');

        $totalEsperadoEfectivo = (float) $sesiones->sum('monto_esperado_efectivo');
        $sesionesCerradas = $sesiones->where('estado', 'CERRADA');
        $totalDeclaradoFisico = (float) $sesionesCerradas->sum('monto_cierre_declarado');
        $diferenciaNeta = round((float) $sesionesCerradas->sum('diferencia'), 2);

        // --- DISTRIBUCIÓN POR RUBROS ---
        $porRubro = [
            [
                'nombre' => 'Servicio de Agua Potable',
                'cantidad' => $cantLecturas,
                'efectivo' => $aguaEfectivo,
                'qr' => $aguaQr,
                'total' => $totalAgua,
            ],
            [
                'nombre' => 'Tasa de Alcantarillado Sanitario',
                'cantidad' => $cantLecturas,
                'efectivo' => $alcantarilladoEfectivo,
                'qr' => $alcantarilladoQr,
                'total' => $totalAlcantarillado,
            ],
            [
                'nombre' => 'Descuento Ley 1886 (3ra Edad)',
                'cantidad' => $cantLey1886,
                'efectivo' => -$descLey1886,
                'qr' => 0.00,
                'total' => -$descLey1886,
            ],
            [
                'nombre' => 'Cuotas de Convenios de Pago',
                'cantidad' => $cantCuotas,
                'efectivo' => $cuotasEfectivo,
                'qr' => $cuotasQr,
                'total' => $totalConvenios,
            ],
        ];

        // Añadir sub-rubros de recibos (Conexiones, Reconexiones, Titularidad, Materiales, Trámites)
        $rubrosDefinidos = [
            'Conexiones e Instalaciones (Agua Potable)',
            'Conexiones e Instalaciones (Alcantarillado)',
            'Reconexiones de Servicio',
            'Cambios de Titularidad / Nombre',
            'Materiales y Servicios Operativos',
            'Otros Trámites y Multas',
        ];

        foreach ($rubrosDefinidos as $rNom) {
            $item = $recibosPorRubro->get($rNom);
            if ($item && (float) $item->total > 0) {
                $porRubro[] = [
                    'nombre' => $rNom,
                    'cantidad' => (int) $item->transacciones,
                    'efectivo' => (float) $item->total,
                    'qr' => 0.00,
                    'total' => (float) $item->total,
                ];
            }
        }

        if ($totalRecibos > 0 && count($porRubro) === 4) {
            $porRubro[] = [
                'nombre' => 'Recibos de Caja (Varios)',
                'cantidad' => $cantRecibos,
                'efectivo' => $totalRecibos,
                'qr' => 0.00,
                'total' => $totalRecibos,
            ];
        }

        // --- DISTRIBUCIÓN POR VENTANILLA / CAJA ---
        $puntosVenta = SiatPuntoVenta::where('_estado', 'ACTIVO')->get()->keyBy('id');
        $porCaja = [];

        $lecPorCaja = (clone $queryLecturas)
            ->groupBy(DB::raw('COALESCE(s.id_punto_venta, 1)'))
            ->selectRaw('
                COALESCE(s.id_punto_venta, 1) as id_punto_venta,
                COUNT(*) as transacciones,
                SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) = 1 THEN COALESCE(l.total_facturado, 0) ELSE 0 END) as efectivo,
                SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) != 1 THEN COALESCE(l.total_facturado, 0) ELSE 0 END) as qr,
                SUM(COALESCE(l.total_facturado, 0)) as total
            ')->get()->keyBy('id_punto_venta');

        $cuoPorCaja = (clone $queryCuotas)
            ->groupBy(DB::raw('COALESCE(s.id_punto_venta, 1)'))
            ->selectRaw('
                COALESCE(s.id_punto_venta, 1) as id_punto_venta,
                COUNT(*) as transacciones,
                SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) = 1 THEN COALESCE(c.monto_cuota, 0) ELSE 0 END) as efectivo,
                SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) != 1 THEN COALESCE(c.monto_cuota, 0) ELSE 0 END) as qr,
                SUM(COALESCE(c.monto_cuota, 0)) as total
            ')->get()->keyBy('id_punto_venta');

        $recPorCaja = (clone $queryRecibos)
            ->groupBy(DB::raw('COALESCE(s.id_punto_venta, 1)'))
            ->selectRaw('
                COALESCE(s.id_punto_venta, 1) as id_punto_venta,
                COUNT(*) as transacciones,
                SUM(COALESCE(r.monto_total, 0)) as efectivo,
                0 as qr,
                SUM(COALESCE(r.monto_total, 0)) as total
            ')->get()->keyBy('id_punto_venta');

        foreach ($puntosVenta as $pvId => $pv) {
            $lC = $lecPorCaja->get($pvId);
            $cC = $cuoPorCaja->get($pvId);
            $rC = $recPorCaja->get($pvId);

            $turnosCaja = $sesiones->where('id_punto_venta', $pvId)->count();
            $cantTrans = (int) ($lC?->transacciones ?? 0) + (int) ($cC?->transacciones ?? 0) + (int) ($rC?->transacciones ?? 0);
            $efCaja = (float) ($lC?->efectivo ?? 0) + (float) ($cC?->efectivo ?? 0) + (float) ($rC?->efectivo ?? 0);
            $qrCaja = (float) ($lC?->qr ?? 0) + (float) ($cC?->qr ?? 0);
            $totCaja = round($efCaja + $qrCaja, 2);

            if ($turnosCaja > 0 || $cantTrans > 0) {
                $porCaja[] = [
                    'id' => $pvId,
                    'caja' => $pv->nombre,
                    'codigo_punto_venta' => $pv->codigo_punto_venta,
                    'turnos' => $turnosCaja,
                    'transacciones' => $cantTrans,
                    'efectivo' => $efCaja,
                    'qr' => $qrCaja,
                    'total' => $totCaja,
                ];
            }
        }

        // --- DISTRIBUCIÓN POR CAJERO ---
        $usersMap = User::pluck('name', 'id')->toArray();

        $lecPorCajero = (clone $queryLecturas)
            ->groupBy(DB::raw('COALESCE(l.id_cajero, 1)'))
            ->selectRaw('
                COALESCE(l.id_cajero, 1) as id_cajero,
                COUNT(*) as transacciones,
                SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) = 1 THEN COALESCE(l.total_facturado, 0) ELSE 0 END) as efectivo,
                SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) != 1 THEN COALESCE(l.total_facturado, 0) ELSE 0 END) as qr,
                SUM(COALESCE(l.total_facturado, 0)) as total
            ')->get()->keyBy('id_cajero');

        $cuoPorCajero = (clone $queryCuotas)
            ->groupBy(DB::raw('COALESCE(c.id_cajero, 1)'))
            ->selectRaw('
                COALESCE(c.id_cajero, 1) as id_cajero,
                COUNT(*) as transacciones,
                SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) = 1 THEN COALESCE(c.monto_cuota, 0) ELSE 0 END) as efectivo,
                SUM(CASE WHEN COALESCE(f.codigo_metodo_pago, 1) != 1 THEN COALESCE(c.monto_cuota, 0) ELSE 0 END) as qr,
                SUM(COALESCE(c.monto_cuota, 0)) as total
            ')->get()->keyBy('id_cajero');

        $recPorCajero = (clone $queryRecibos)
            ->groupBy(DB::raw('COALESCE(r.id_cajero, 1)'))
            ->selectRaw('
                COALESCE(r.id_cajero, 1) as id_cajero,
                COUNT(*) as transacciones,
                SUM(COALESCE(r.monto_total, 0)) as efectivo,
                0 as qr,
                SUM(COALESCE(r.monto_total, 0)) as total
            ')->get()->keyBy('id_cajero');

        $cajeroIds = collect([])
            ->concat($lecPorCajero->keys())
            ->concat($cuoPorCajero->keys())
            ->concat($recPorCajero->keys())
            ->concat($sesiones->pluck('id_cajero'))
            ->unique()
            ->filter();

        $porCajero = [];
        foreach ($cajeroIds as $cid) {
            $cid = (int) $cid;
            $lCj = $lecPorCajero->get($cid);
            $cCj = $cuoPorCajero->get($cid);
            $rCj = $recPorCajero->get($cid);

            $turnosCajero = $sesiones->where('id_cajero', $cid)->count();
            $cantTrans = (int) ($lCj?->transacciones ?? 0) + (int) ($cCj?->transacciones ?? 0) + (int) ($rCj?->transacciones ?? 0);
            $efCj = (float) ($lCj?->efectivo ?? 0) + (float) ($cCj?->efectivo ?? 0) + (float) ($rCj?->efectivo ?? 0);
            $qrCj = (float) ($lCj?->qr ?? 0) + (float) ($cCj?->qr ?? 0);
            $totCj = round($efCj + $qrCj, 2);

            if ($turnosCajero > 0 || $cantTrans > 0) {
                $nombreCajero = $usersMap[$cid] ?? "Cajero #{$cid}";
                $porCajero[] = [
                    'id_cajero' => $cid,
                    'cajero' => $nombreCajero,
                    'turnos' => $turnosCajero,
                    'transacciones' => $cantTrans,
                    'efectivo' => $efCj,
                    'qr' => $qrCj,
                    'total' => $totCj,
                ];
            }
        }

        // Listado formateado de turnos
        $turnosFormateados = $sesiones->map(function ($s) {
            return [
                'id' => $s->id,
                'numero_sesion' => $s->numero_sesion,
                'caja_nombre' => $s->puntoVenta?->nombre ?? "Caja #{$s->id_punto_venta}",
                'cajero_nombre' => $s->cajero?->name ?? "Cajero #{$s->id_cajero}",
                'fecha_apertura' => $s->fecha_apertura ? $s->fecha_apertura->format('d/m/Y H:i') : 'S/F',
                'fecha_cierre' => $s->fecha_cierre ? $s->fecha_cierre->format('d/m/Y H:i') : null,
                'monto_apertura' => (float) $s->monto_apertura,
                'monto_ventas_efectivo' => (float) $s->monto_ventas_efectivo,
                'monto_ventas_qr_banco' => (float) $s->monto_ventas_qr_banco,
                'monto_esperado_efectivo' => (float) $s->monto_esperado_efectivo,
                'monto_cierre_declarado' => (float) $s->monto_cierre_declarado,
                'diferencia' => (float) $s->diferencia,
                'estado' => $s->estado,
            ];
        })->values()->all();

        return [
            'periodo' => [
                'tipo' => $tipo,
                'desde' => $fechaInicio->format('d/m/Y'),
                'hasta' => $fechaFin->format('d/m/Y'),
                'fecha_inicio_iso' => $fechaInicio->toDateString(),
                'fecha_fin_iso' => $fechaFin->toDateString(),
                'etiqueta' => $etiqueta,
            ],
            'metricas' => [
                'total_recaudado' => $totalGeneralRecaudado,
                'total_efectivo' => $totalEfectivo,
                'total_qr_banco' => $totalQr,
                'total_agua' => $totalAgua,
                'total_alcantarillado' => $totalAlcantarillado,
                'total_descuentos_ley1886' => $descLey1886,
                'total_convenios' => $totalConvenios,
                'total_recibos' => $totalRecibos,
                'total_fondo_inicial' => $totalFondoInicial,
                'total_ingresos_extra' => $totalIngresosExtra,
                'total_egresos_extra' => $totalEgresosExtra,
                'total_esperado_efectivo' => $totalEsperadoEfectivo,
                'total_declarado_fisico' => $totalDeclaradoFisico,
                'diferencia_neta' => $diferenciaNeta,
                'total_transacciones' => $totalTransacciones,
            ],
            'por_rubro' => $porRubro,
            'por_caja' => $porCaja,
            'por_cajero' => $porCajero,
            'turnos' => $turnosFormateados,
        ];
    }

    /**
     * Reporte de recaudación en caja por fecha fija (Compatibilidad hacia atrás).
     */
    public function recaudacionDiaria(Request $request): JsonResponse
    {
        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $subRequest = Request::create('', 'GET', [
            'periodo_tipo' => 'personalizado',
            'fecha_inicio' => $fecha,
            'fecha_fin' => $fecha,
        ]);

        $datos = $this->obtenerDatosRecaudacionConsolidada($subRequest);

        return response()->json([
            'success' => true,
            'fecha' => $fecha,
            'metricas' => $datos['metricas'],
            'por_cajero' => $datos['por_cajero'],
            'por_rubro' => $datos['por_rubro'],
            'por_caja' => $datos['por_caja'],
            'data' => $datos,
        ], Response::HTTP_OK);
    }

    /**
     * Reporte de cartera vencida y morosidad de abonados.
     */
    public function morosidad(Request $request): JsonResponse
    {
        $idZona = $request->input('id_zona');

        $query = Abonado::with(['zona', 'categoria'])
            ->where('meses_mora', '>', 0);

        if (!empty($idZona)) {
            $query->where('id_zona', $idZona);
        }

        $totalAbonadosMora = (clone $query)->count();
        $totalDeudaAcumulada = (float) (clone $query)->sum('saldo_deuda');

        // Segmentación por antigüedad de mora
        $mora1Mes = (clone $query)->where('meses_mora', 1)->count();
        $mora2Meses = (clone $query)->where('meses_mora', 2)->count();
        $mora3OMas = (clone $query)->where('meses_mora', '>=', 3)->count();

        // Top 10 mayores deudores
        $topDeudores = (clone $query)->orderByDesc('saldo_deuda')->limit(10)->get();

        // Resumen por Zona optimizado con agregación SQL
        $deudaZonasMap = DB::table('comercial.abonados')
            ->selectRaw('id_zona, COUNT(*) as en_mora, SUM(COALESCE(saldo_deuda, 0)) as total_deuda')
            ->where('meses_mora', '>', 0)
            ->groupBy('id_zona')
            ->get()
            ->keyBy('id_zona');

        $deudaPorZona = Zona::orderBy('nombre')
            ->get()
            ->map(function ($z) use ($deudaZonasMap) {
                $stat = $deudaZonasMap->get($z->id);
                return [
                    'id_zona' => $z->id,
                    'zona' => $z->nombre,
                    'abonados_mora' => (int) ($stat?->en_mora ?? 0),
                    'total_deuda' => round((float) ($stat?->total_deuda ?? 0), 2),
                ];
            });

        return response()->json([
            'success' => true,
            'metricas' => [
                'total_abonados_mora' => $totalAbonadosMora,
                'total_deuda_acumulada' => round($totalDeudaAcumulada, 2),
                'mora_1_mes' => $mora1Mes,
                'mora_2_meses' => $mora2Meses,
                'mora_3_o_mas_meses' => $mora3OMas,
            ],
            'deuda_por_zona' => $deudaPorZona,
            'top_deudores' => $topDeudores,
        ], Response::HTTP_OK);
    }

    /**
     * Reporte de balance de consumo hídrico y facturación mensual.
     */
    public function balanceConsumo(): JsonResponse
    {
        $periodos = PeriodoFacturacion::withCount('lecturas')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $periodosIds = $periodos->pluck('id');
        $stats = DB::table('comercial.lecturas_mensuales')
            ->whereIn('id_periodo', $periodosIds)
            ->groupBy('id_periodo')
            ->selectRaw('
                id_periodo,
                COUNT(*) as abonados_medidos,
                SUM(COALESCE(consumo_m3, 0)) as volumen_total_m3,
                SUM(COALESCE(total_facturado, 0)) as monto_facturado_bs,
                SUM(CASE WHEN estado_pago = \'PAGADO\' THEN COALESCE(total_facturado, 0) ELSE 0 END) as monto_cobrado_bs
            ')
            ->get()
            ->keyBy('id_periodo');

        $periodosResult = $periodos->map(function ($p) use ($stats) {
            $st = $stats->get($p->id);
            $volumen = (float) ($st?->volumen_total_m3 ?? 0);
            $facturado = (float) ($st?->monto_facturado_bs ?? 0);
            $cobrado = (float) ($st?->monto_cobrado_bs ?? 0);

            return [
                'id_periodo' => $p->id,
                'periodo' => $p->periodo,
                'estado' => $p->estado,
                'abonados_medidos' => (int) ($st?->abonados_medidos ?? $p->lecturas_count),
                'volumen_total_m3' => round($volumen, 2),
                'monto_facturado_bs' => round($facturado, 2),
                'monto_cobrado_bs' => round($cobrado, 2),
                'porcentaje_recaudacion' => $facturado > 0 ? round(($cobrado / $facturado) * 100, 1) : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'periodos' => $periodosResult,
        ], Response::HTTP_OK);
    }

    /**
     * Descarga la Planilla de Campo de Toma de Lecturas en PDF.
     */
    public function descargarPlanillaLecturasPdf(Request $request): Response
    {
        $idPeriodo = (int) $request->input('id_periodo');
        $idZona = $request->input('id_zona') ? (int) $request->input('id_zona') : null;
        $idCalle = $request->input('id_calle') ? (int) $request->input('id_calle') : null;
        $aCiegas = $request->boolean('a_ciegas', false);

        $pdf = $this->pdfOperativosService->generarPlanillaLecturasPdf($idPeriodo, $idZona, $idCalle, $aCiegas);

        $sufijo = $aCiegas ? 'A_CIEGAS' : 'NORMAL';
        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Planilla_Lecturas_Periodo_{$idPeriodo}_{$sufijo}.pdf\"",
        ]);
    }

    /**
     * Exporta la Planilla de Campo de Toma de Lecturas en formato Excel / CSV.
     */
    public function exportarPlanillaLecturasExcel(Request $request): StreamedResponse
    {
        $idPeriodo = (int) $request->input('id_periodo');
        $idZona = $request->input('id_zona') ? (int) $request->input('id_zona') : null;
        $idCalle = $request->input('id_calle') ? (int) $request->input('id_calle') : null;
        $aCiegas = $request->boolean('a_ciegas', false);

        $query = LecturaMensual::with([
            'abonado.zona',
            'abonado.calle',
            'abonado.categoria',
            'medidor',
        ])->where('id_periodo', $idPeriodo);

        if ($idZona) {
            $query->whereHas('abonado', fn($q) => $q->where('id_zona', $idZona));
        }
        if ($idCalle) {
            $query->whereHas('abonado', fn($q) => $q->where('id_calle', $idCalle));
        }

        $lecturas = $query->join('comercial.abonados', 'lecturas_mensuales.id_abonado', '=', 'abonados.id')
            ->leftJoin('comercial.zonas', 'abonados.id_zona', '=', 'zonas.id')
            ->leftJoin('comercial.calles', 'abonados.id_calle', '=', 'calles.id')
            ->orderBy('zonas.nombre')
            ->orderBy('calles.nombre')
            ->orderBy('abonados.codigo')
            ->select('comercial.lecturas_mensuales.*')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"Planilla_Lecturas_Periodo_{$idPeriodo}.csv\"",
        ];

        return response()->stream(function () use ($lecturas, $aCiegas) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'N°',
                'Código Abonado',
                'Titular / Abonado',
                'Zona',
                'Calle / Dirección',
                'N° Vivienda',
                'Categoría',
                'N° Medidor',
                'Lectura Anterior (m³)',
                'Lectura Actual (m³)',
                'Observación / Novedad',
            ], ';');

            foreach ($lecturas as $idx => $l) {
                fputcsv($handle, [
                    $idx + 1,
                    $l->abonado?->codigo ?? '',
                    $l->abonado?->nombre_completo ?? '',
                    $l->abonado?->zona?->nombre ?? '',
                    $l->abonado?->calle?->nombre ?? '',
                    $l->abonado?->numero_vivienda ?? '',
                    $l->abonado?->categoria?->nombre ?? '',
                    $l->medidor?->numero_serie ?? $l->abonado?->numero_medidor ?? '',
                    $aCiegas ? '[A CIEGAS]' : number_format((float) $l->lectura_anterior, 0),
                    '',
                    '',
                ], ';');
            }

            fclose($handle);
        }, Response::HTTP_OK, $headers);
    }

    /**
     * Resumen de Operaciones y Facturación por Zonas (JSON).
     */
    public function resumenOperacionesZonas(Request $request): JsonResponse
    {
        $idPeriodo = (int) $request->input('id_periodo');
        $periodo = $idPeriodo ? PeriodoFacturacion::find($idPeriodo) : PeriodoFacturacion::latest('id')->first();

        if (!$periodo) {
            return response()->json(['success' => false, 'message' => 'No se encontró ningún período activo.'], Response::HTTP_NOT_FOUND);
        }

        $filas = DB::table('comercial.lecturas_mensuales as l')
            ->join('comercial.abonados as a', 'l.id_abonado', '=', 'a.id')
            ->leftJoin('comercial.zonas as z', 'a.id_zona', '=', 'z.id')
            ->where('l.id_periodo', $periodo->id)
            ->groupBy('z.id', 'z.codigo', 'z.nombre')
            ->orderBy('z.nombre')
            ->select([
                'z.id as id_zona',
                DB::raw("COALESCE(z.nombre, 'SIN ZONA ASIGNADA') as zona_nombre"),
                DB::raw("COALESCE(z.codigo, 'S/Z') as zona_codigo"),
                DB::raw("COUNT(l.id) as total_abonados"),
                DB::raw("SUM(COALESCE(l.consumo_m3, 0)) as consumo_total_m3"),
                DB::raw("SUM(COALESCE(l.monto_agua, 0)) as total_agua_bs"),
                DB::raw("SUM(COALESCE(l.monto_alcantarillado, 0)) as total_alcantarillado_bs"),
                DB::raw("SUM(COALESCE(l.monto_otros, 0)) as total_otros_cargos_bs"),
                DB::raw("SUM(COALESCE(l.monto_descuento_ley1886, 0)) as total_ley1886_bs"),
                DB::raw("SUM(COALESCE(l.total_facturado, 0)) as total_facturado_bs"),
                DB::raw("COUNT(CASE WHEN l.estado_pago = 'PAGADO' THEN 1 END) as abonados_pagados"),
                DB::raw("SUM(CASE WHEN l.estado_pago = 'PAGADO' THEN COALESCE(l.total_facturado, 0) ELSE 0 END) as total_cobrado_bs"),
            ])
            ->get();

        $totales = [
            'abonados' => $filas->sum('total_abonados'),
            'consumo_m3' => (float) $filas->sum('consumo_total_m3'),
            'agua_bs' => (float) $filas->sum('total_agua_bs'),
            'alcantarillado_bs' => (float) $filas->sum('total_alcantarillado_bs'),
            'otros_cargos_bs' => (float) $filas->sum('total_otros_cargos_bs'),
            'ley1886_bs' => (float) $filas->sum('total_ley1886_bs'),
            'facturado_bs' => (float) $filas->sum('total_facturado_bs'),
            'cobrado_bs' => (float) $filas->sum('total_cobrado_bs'),
        ];

        return response()->json([
            'success' => true,
            'periodo' => $periodo,
            'filas' => $filas,
            'totales' => $totales,
        ], Response::HTTP_OK);
    }

    /**
     * Descarga el Resumen de Operaciones y Facturación por Zonas en PDF.
     */
    public function descargarResumenOperacionesZonasPdf(Request $request): Response
    {
        $idPeriodo = (int) $request->input('id_periodo');
        $pdf = $this->pdfOperativosService->generarResumenZonasPdf($idPeriodo);

        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Resumen_Facturacion_Zonas_Periodo_{$idPeriodo}.pdf\"",
        ]);
    }

    /**
     * Exporta el Resumen de Operaciones y Facturación por Zonas en Excel / CSV.
     */
    public function exportarResumenOperacionesZonasExcel(Request $request): StreamedResponse
    {
        $idPeriodo = (int) $request->input('id_periodo');
        $periodo = PeriodoFacturacion::findOrFail($idPeriodo);

        $filas = DB::table('comercial.lecturas_mensuales as l')
            ->join('comercial.abonados as a', 'l.id_abonado', '=', 'a.id')
            ->leftJoin('comercial.zonas as z', 'a.id_zona', '=', 'z.id')
            ->where('l.id_periodo', $idPeriodo)
            ->groupBy('z.id', 'z.codigo', 'z.nombre')
            ->orderBy('z.nombre')
            ->select([
                DB::raw("COALESCE(z.codigo, 'S/Z') as zona_codigo"),
                DB::raw("COALESCE(z.nombre, 'SIN ZONA ASIGNADA') as zona_nombre"),
                DB::raw("COUNT(l.id) as total_abonados"),
                DB::raw("SUM(COALESCE(l.consumo_m3, 0)) as consumo_total_m3"),
                DB::raw("SUM(COALESCE(l.monto_agua, 0)) as total_agua_bs"),
                DB::raw("SUM(COALESCE(l.monto_alcantarillado, 0)) as total_alcantarillado_bs"),
                DB::raw("SUM(COALESCE(l.monto_otros, 0)) as total_otros_cargos_bs"),
                DB::raw("SUM(COALESCE(l.monto_descuento_ley1886, 0)) as total_ley1886_bs"),
                DB::raw("SUM(COALESCE(l.total_facturado, 0)) as total_facturado_bs"),
                DB::raw("SUM(CASE WHEN l.estado_pago = 'PAGADO' THEN COALESCE(l.total_facturado, 0) ELSE 0 END) as total_cobrado_bs"),
            ])
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"Resumen_Zonas_Periodo_{$idPeriodo}.csv\"",
        ];

        return response()->stream(function () use ($filas) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'N°',
                'Código Zona',
                'Zona Comercial',
                'Abonados',
                'Consumo (m³)',
                'Importe Agua (Bs)',
                'Alcantarillado (Bs)',
                'Otros Cargos (Bs)',
                'Desc. Ley 1886 (Bs)',
                'Total Facturado (Bs)',
                'Total Recaudado (Bs)',
                '% Cobro',
            ], ';');

            foreach ($filas as $idx => $f) {
                $pct = (float) $f->total_facturado_bs > 0 ? ((float) $f->total_cobrado_bs / (float) $f->total_facturado_bs) * 100 : 0;
                fputcsv($handle, [
                    $idx + 1,
                    $f->zona_codigo,
                    $f->zona_nombre,
                    $f->total_abonados,
                    number_format((float) $f->consumo_total_m3, 0),
                    number_format((float) $f->total_agua_bs, 2),
                    number_format((float) $f->total_alcantarillado_bs, 2),
                    number_format((float) $f->total_otros_cargos_bs, 2),
                    number_format((float) $f->total_ley1886_bs, 2),
                    number_format((float) $f->total_facturado_bs, 2),
                    number_format((float) $f->total_cobrado_bs, 2),
                    number_format($pct, 1) . '%',
                ], ';');
            }

            fclose($handle);
        }, Response::HTTP_OK, $headers);
    }

    /**
     * Nómina de abonados sujetos a cortes masivos por morosidad (JSON).
     */
    public function nominaCortes(Request $request): JsonResponse
    {
        $idZona = $request->input('id_zona') ? (int) $request->input('id_zona') : null;
        $mesesMoraMin = (int) $request->input('meses_mora', 2);

        $query = Abonado::with(['zona', 'calle', 'categoria', 'medidorActual'])
            ->where('meses_mora', '>=', $mesesMoraMin)
            ->where('saldo_deuda', '>', 0)
            ->whereNotIn('estado_servicio', ['BAJA', 'CORTADO']);

        if ($idZona) {
            $query->where('abonados.id_zona', $idZona);
        }

        $abonados = $query->leftJoin('comercial.zonas', 'abonados.id_zona', '=', 'zonas.id')
            ->leftJoin('comercial.calles', 'abonados.id_calle', '=', 'calles.id')
            ->orderBy('zonas.nombre')
            ->orderBy('calles.nombre')
            ->orderBy('abonados.codigo')
            ->select('comercial.abonados.*')
            ->get();

        return response()->json([
            'success' => true,
            'total_deudores' => $abonados->count(),
            'total_deuda' => (float) $abonados->sum('saldo_deuda'),
            'meses_mora_min' => $mesesMoraMin,
            'abonados' => $abonados,
        ], Response::HTTP_OK);
    }

    /**
     * Descarga la Planilla / Nómina de Cortes Masivos por Zona en PDF.
     */
    public function descargarNominaCortesPdf(Request $request): Response
    {
        $idZona = $request->input('id_zona') ? (int) $request->input('id_zona') : null;
        $mesesMoraMin = (int) $request->input('meses_mora', 2);

        $pdf = $this->pdfOperativosService->generarNominaCortesPdf($idZona, $mesesMoraMin);

        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nomina_Cortes_Masivos.pdf"',
        ]);
    }

    /**
     * Exporta la Planilla / Nómina de Cortes Masivos por Zona en Excel / CSV.
     */
    public function exportarNominaCortesExcel(Request $request): StreamedResponse
    {
        $idZona = $request->input('id_zona') ? (int) $request->input('id_zona') : null;
        $mesesMoraMin = (int) $request->input('meses_mora', 2);

        $query = Abonado::with(['zona', 'calle', 'categoria', 'medidorActual'])
            ->where('meses_mora', '>=', $mesesMoraMin)
            ->where('saldo_deuda', '>', 0)
            ->whereNotIn('estado_servicio', ['BAJA', 'CORTADO']);

        if ($idZona) {
            $query->where('abonados.id_zona', $idZona);
        }

        $abonados = $query->leftJoin('comercial.zonas', 'abonados.id_zona', '=', 'zonas.id')
            ->leftJoin('comercial.calles', 'abonados.id_calle', '=', 'calles.id')
            ->orderBy('zonas.nombre')
            ->orderBy('calles.nombre')
            ->orderBy('abonados.codigo')
            ->select('comercial.abonados.*')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Nomina_Cortes_Masivos.csv"',
        ];

        return response()->stream(function () use ($abonados) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'N°',
                'Código Abonado',
                'Titular / Abonado',
                'Zona',
                'Calle / Dirección',
                'N° Vivienda',
                'Categoría',
                'N° Medidor',
                'Meses Mora',
                'Saldo Deuda (Bs)',
                'Lectura Retiro',
                'N° Precinto',
                'Fecha Ejecución',
                'Técnico',
            ], ';');

            foreach ($abonados as $idx => $a) {
                fputcsv($handle, [
                    $idx + 1,
                    $a->codigo,
                    $a->nombre_completo,
                    $a->zona?->nombre ?? '',
                    $a->calle?->nombre ?? '',
                    $a->numero_vivienda ?? '',
                    $a->categoria?->nombre ?? '',
                    $a->medidorActual?->numero_serie ?? $a->numero_medidor ?? '',
                    $a->meses_mora,
                    number_format((float) $a->saldo_deuda, 2),
                    '',
                    '',
                    '',
                    '',
                ], ';');
            }

            fclose($handle);
        }, Response::HTTP_OK, $headers);
    }
}
