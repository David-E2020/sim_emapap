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

        // 1. Lecturas de Agua
        $queryLecturas = LecturaMensual::with(['abonado', 'facturaSiat', 'cajero', 'sesionCaja.puntoVenta'])
            ->where('estado_pago', 'PAGADO')
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);

        if ($idCajero) {
            $queryLecturas->where('id_cajero', $idCajero);
        }
        if ($idPuntoVenta) {
            $queryLecturas->whereHas('sesionCaja', fn($q) => $q->where('id_punto_venta', $idPuntoVenta));
        }
        if ($metodoPago) {
            $queryLecturas->whereHas('facturaSiat', fn($q) => $q->where('codigo_metodo_pago', $metodoPago));
        }
        $lecturas = $queryLecturas->get();

        // 2. Cuotas de Convenio
        $queryCuotas = ConvenioCuota::with(['convenio.abonado', 'facturaSiat', 'cajero', 'sesionCaja.puntoVenta'])
            ->where('estado_pago', 'PAGADO')
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);

        if ($idCajero) {
            $queryCuotas->where('id_cajero', $idCajero);
        }
        if ($idPuntoVenta) {
            $queryCuotas->whereHas('sesionCaja', fn($q) => $q->where('id_punto_venta', $idPuntoVenta));
        }
        if ($metodoPago) {
            $queryCuotas->whereHas('facturaSiat', fn($q) => $q->where('codigo_metodo_pago', $metodoPago));
        }
        $cuotas = $queryCuotas->get();

        // 3. Recibos de Caja
        $queryRecibos = ReciboCaja::with(['abonado', 'cajero', 'sesionCaja.puntoVenta'])
            ->where('estado', 'VALIDO')
            ->whereBetween('fecha_cobro', [$fechaInicio, $fechaFin]);

        if ($idCajero) {
            $queryRecibos->where('id_cajero', $idCajero);
        }
        if ($idPuntoVenta) {
            $queryRecibos->whereHas('sesionCaja', fn($q) => $q->where('id_punto_venta', $idPuntoVenta));
        }
        // Recibos son en efectivo salvo indicación contraria
        if ($metodoPago && $metodoPago !== 1) {
            $queryRecibos->whereRaw('1 = 0');
        }
        $recibos = $queryRecibos->get();

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

        // --- CÁLCULO DE RUBROS Y MÉTODOS ---
        $lecturasEf = $lecturas->filter(fn($l) => ($l->facturaSiat?->codigo_metodo_pago ?? 1) === 1);
        $lecturasQr = $lecturas->filter(fn($l) => ($l->facturaSiat?->codigo_metodo_pago ?? 1) !== 1);

        $cuotasEf = $cuotas->filter(fn($c) => ($c->facturaSiat?->codigo_metodo_pago ?? 1) === 1);
        $cuotasQr = $cuotas->filter(fn($c) => ($c->facturaSiat?->codigo_metodo_pago ?? 1) !== 1);

        $aguaEfectivo = (float) $lecturasEf->sum('monto_agua');
        $aguaQr = (float) $lecturasQr->sum('monto_agua');
        $totalAgua = round($aguaEfectivo + $aguaQr, 2);

        $alcantarilladoEfectivo = (float) $lecturasEf->sum('monto_alcantarillado');
        $alcantarilladoQr = (float) $lecturasQr->sum('monto_alcantarillado');
        $totalAlcantarillado = round($alcantarilladoEfectivo + $alcantarilladoQr, 2);

        $descLey1886 = (float) $lecturas->sum('monto_descuento_ley1886');

        $cuotasEfectivo = (float) $cuotasEf->sum('monto_cuota');
        $cuotasQr = (float) $cuotasQr->sum('monto_cuota');
        $totalConvenios = round($cuotasEfectivo + $cuotasQr, 2);

        $totalRecibos = (float) $recibos->sum('monto_total');

        $totalLecturas = (float) $lecturas->sum('total_facturado');
        $totalGeneralRecaudado = round($totalLecturas + $totalConvenios + $totalRecibos, 2);

        $totalEfectivo = round((float) $lecturasEf->sum('total_facturado') + $cuotasEfectivo + $totalRecibos, 2);
        $totalQr = round((float) $lecturasQr->sum('total_facturado') + $cuotasQr, 2);

        $totalFondoInicial = (float) $sesiones->sum('monto_apertura');
        $totalIngresosExtra = (float) $movimientos->where('tipo', 'INGRESO')->sum('monto');
        $totalEgresosExtra = (float) $movimientos->where('tipo', 'EGRESO')->sum('monto');

        $totalEsperadoEfectivo = (float) $sesiones->sum('monto_esperado_efectivo');
        $sesionesCerradas = $sesiones->where('estado', 'CERRADA');
        $totalDeclaradoFisico = (float) $sesionesCerradas->sum('monto_cierre_declarado');
        $diferenciaNeta = round((float) $sesionesCerradas->sum('diferencia'), 2);

        // Distribución por Rubros
        $porRubro = [
            [
                'nombre' => 'Servicio de Agua Potable',
                'cantidad' => $lecturas->count(),
                'efectivo' => $aguaEfectivo,
                'qr' => $aguaQr,
                'total' => $totalAgua,
            ],
            [
                'nombre' => 'Tasa de Alcantarillado Sanitario',
                'cantidad' => $lecturas->count(),
                'efectivo' => $alcantarilladoEfectivo,
                'qr' => $alcantarilladoQr,
                'total' => $totalAlcantarillado,
            ],
            [
                'nombre' => 'Descuento Ley 1886 (3ra Edad)',
                'cantidad' => $lecturas->where('monto_descuento_ley1886', '>', 0)->count(),
                'efectivo' => -$descLey1886,
                'qr' => 0.00,
                'total' => -$descLey1886,
            ],
            [
                'nombre' => 'Cuotas de Convenios de Pago',
                'cantidad' => $cuotas->count(),
                'efectivo' => $cuotasEfectivo,
                'qr' => $cuotasQr,
                'total' => $totalConvenios,
            ],
            [
                'nombre' => 'Recibos de Caja (Otros Conceptos)',
                'cantidad' => $recibos->count(),
                'efectivo' => $totalRecibos,
                'qr' => 0.00,
                'total' => $totalRecibos,
            ],
        ];

        // Distribución por Ventanilla / Caja
        $puntosVenta = SiatPuntoVenta::where('_estado', 'ACTIVO')->get()->keyBy('id');
        $porCaja = [];

        foreach ($puntosVenta as $pvId => $pv) {
            $lecCaja = $lecturas->filter(fn($l) => $l->sesionCaja?->id_punto_venta === $pvId);
            $cuoCaja = $cuotas->filter(fn($c) => $c->sesionCaja?->id_punto_venta === $pvId);
            $recCaja = $recibos->filter(fn($r) => $r->sesionCaja?->id_punto_venta === $pvId);

            $turnosCaja = $sesiones->where('id_punto_venta', $pvId)->count();
            $cantTrans = $lecCaja->count() + $cuoCaja->count() + $recCaja->count();

            $efCaja = (float) $lecCaja->filter(fn($l) => ($l->facturaSiat?->codigo_metodo_pago ?? 1) === 1)->sum('total_facturado')
                + (float) $cuoCaja->filter(fn($c) => ($c->facturaSiat?->codigo_metodo_pago ?? 1) === 1)->sum('monto_cuota')
                + (float) $recCaja->sum('monto_total');

            $qrCaja = (float) $lecCaja->filter(fn($l) => ($l->facturaSiat?->codigo_metodo_pago ?? 1) !== 1)->sum('total_facturado')
                + (float) $cuoCaja->filter(fn($c) => ($c->facturaSiat?->codigo_metodo_pago ?? 1) !== 1)->sum('monto_cuota');

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

        // Distribución por Cajero
        $cajerosGroup = [];
        foreach ($lecturas as $l) {
            $cid = $l->id_cajero ?? 0;
            $cnom = $l->cajero?->name ?? "Cajero #{$cid}";
            $esEf = ($l->facturaSiat?->codigo_metodo_pago ?? 1) === 1;
            $m = (float) $l->total_facturado;
            $cajerosGroup[$cid]['cajero'] = $cnom;
            $cajerosGroup[$cid]['transacciones'] = ($cajerosGroup[$cid]['transacciones'] ?? 0) + 1;
            $cajerosGroup[$cid]['efectivo'] = ($cajerosGroup[$cid]['efectivo'] ?? 0) + ($esEf ? $m : 0);
            $cajerosGroup[$cid]['qr'] = ($cajerosGroup[$cid]['qr'] ?? 0) + (!$esEf ? $m : 0);
            $cajerosGroup[$cid]['total'] = ($cajerosGroup[$cid]['total'] ?? 0) + $m;
        }
        foreach ($cuotas as $c) {
            $cid = $c->id_cajero ?? 0;
            $cnom = $c->cajero?->name ?? "Cajero #{$cid}";
            $esEf = ($c->facturaSiat?->codigo_metodo_pago ?? 1) === 1;
            $m = (float) $c->monto_cuota;
            $cajerosGroup[$cid]['cajero'] = $cnom;
            $cajerosGroup[$cid]['transacciones'] = ($cajerosGroup[$cid]['transacciones'] ?? 0) + 1;
            $cajerosGroup[$cid]['efectivo'] = ($cajerosGroup[$cid]['efectivo'] ?? 0) + ($esEf ? $m : 0);
            $cajerosGroup[$cid]['qr'] = ($cajerosGroup[$cid]['qr'] ?? 0) + (!$esEf ? $m : 0);
            $cajerosGroup[$cid]['total'] = ($cajerosGroup[$cid]['total'] ?? 0) + $m;
        }
        foreach ($recibos as $r) {
            $cid = $r->id_cajero ?? 0;
            $cnom = $r->cajero?->name ?? "Cajero #{$cid}";
            $m = (float) $r->monto_total;
            $cajerosGroup[$cid]['cajero'] = $cnom;
            $cajerosGroup[$cid]['transacciones'] = ($cajerosGroup[$cid]['transacciones'] ?? 0) + 1;
            $cajerosGroup[$cid]['efectivo'] = ($cajerosGroup[$cid]['efectivo'] ?? 0) + $m;
            $cajerosGroup[$cid]['qr'] = ($cajerosGroup[$cid]['qr'] ?? 0);
            $cajerosGroup[$cid]['total'] = ($cajerosGroup[$cid]['total'] ?? 0) + $m;
        }

        $porCajero = [];
        foreach ($cajerosGroup as $cid => $data) {
            $turnosCajero = $sesiones->where('id_cajero', $cid)->count();
            $porCajero[] = [
                'id_cajero' => $cid,
                'cajero' => $data['cajero'],
                'turnos' => $turnosCajero,
                'transacciones' => $data['transacciones'],
                'efectivo' => round($data['efectivo'], 2),
                'qr' => round($data['qr'], 2),
                'total' => round($data['total'], 2),
            ];
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
                'total_transacciones' => $lecturas->count() + $cuotas->count() + $recibos->count(),
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

        $totalAbonadosMora = $query->count();
        $totalDeudaAcumulada = (float) $query->sum('saldo_deuda');

        // Segmentación por antigüedad de mora
        $mora1Mes = (clone $query)->where('meses_mora', 1)->count();
        $mora2Meses = (clone $query)->where('meses_mora', 2)->count();
        $mora3OMas = (clone $query)->where('meses_mora', '>=', 3)->count();

        // Top 10 mayores deudores
        $topDeudores = (clone $query)->orderByDesc('saldo_deuda')->limit(10)->get();

        // Resumen por Zona
        $deudaPorZona = Zona::withCount(['abonados as en_mora' => fn($q) => $q->where('meses_mora', '>', 0)])
            ->get()
            ->map(function ($z) {
                return [
                    'zona' => $z->nombre,
                    'abonados_mora' => $z->en_mora,
                    'total_deuda' => (float) Abonado::where('id_zona', $z->id)->sum('saldo_deuda'),
                ];
            });

        return response()->json([
            'success' => true,
            'metricas' => [
                'total_abonados_mora' => $totalAbonadosMora,
                'total_deuda_acumulada' => $totalDeudaAcumulada,
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
            ->get()
            ->map(function ($p) {
                $lecturas = LecturaMensual::where('id_periodo', $p->id)->get();
                $consumoTotalM3 = (float) $lecturas->sum('consumo_m3');
                $totalFacturado = (float) $lecturas->sum('total_facturado');
                $totalCobrado = (float) $lecturas->where('estado_pago', 'PAGADO')->sum('total_facturado');

                return [
                    'periodo' => $p->periodo,
                    'estado' => $p->estado,
                    'abonados_medidos' => $p->lecturas_count,
                    'volumen_total_m3' => $consumoTotalM3,
                    'monto_facturado_bs' => $totalFacturado,
                    'monto_cobrado_bs' => $totalCobrado,
                    'porcentaje_recaudacion' => $totalFacturado > 0 ? round(($totalCobrado / $totalFacturado) * 100, 1) : 0,
                ];
            });

        return response()->json([
            'success' => true,
            'periodos' => $periodos,
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
            $query->where('id_zona', $idZona);
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
            $query->where('id_zona', $idZona);
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
