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
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteComercialController extends Controller
{
    public function __construct(
        protected ReporteRecaudacionConsolidadaPdfService $pdfConsolidadoService
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
}
