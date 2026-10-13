<?php

declare(strict_types=1);

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\Factura;
use App\Models\Facturacion\SiatSucursal;
use App\Services\Facturacion\ReporteFacturasExcelService;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteFacturacionController extends Controller
{
    public function __construct(
        private readonly ReporteFacturasExcelService $reporteFacturasExcelService
    ) {}

    /**
     * Construye la consulta base y resuelve el rango de fechas para el Libro de Ventas.
     *
     * @return array{query: Builder, fechaDesde: string, fechaHasta: string, filtros: array}
     */
    private function prepararQueryLibroVentas(Request $request): array
    {
        if ($request->filled('mes') && $request->filled('gestion')) {
            $fechaDesde = Carbon::create((int) $request->input('gestion'), (int) $request->input('mes'), 1)->startOfMonth()->format('Y-m-d');
            $fechaHasta = Carbon::create((int) $request->input('gestion'), (int) $request->input('mes'), 1)->endOfMonth()->format('Y-m-d');
        } else {
            $fechaDesde = $request->input('fecha_desde', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $fechaHasta = $request->input('fecha_hasta', Carbon::now()->endOfMonth()->format('Y-m-d'));
        }

        $sucursalId = $request->input('id_sucursal');
        $puntoVentaId = $request->input('id_punto_venta');
        $estado = $request->input('estado');
        $tipoEmision = $request->input('tipo_emision');

        $query = Factura::query()
            ->whereDate('fecha_emision', '>=', $fechaDesde)
            ->whereDate('fecha_emision', '<=', $fechaHasta);

        if ($sucursalId !== null && $sucursalId !== '') {
            $query->where('id_sucursal', (int) $sucursalId);
        }

        if ($puntoVentaId !== null && $puntoVentaId !== '') {
            $query->where('id_punto_venta', (int) $puntoVentaId);
        }

        if (!empty($estado)) {
            if ($estado === 'CONTINGENCIA') {
                $query->where(function ($q) {
                    $q->whereIn('estado_factura', ['CONTINGENCIA', 'OFFLINE_PENDIENTE', 'OFFLINE_REGULARIZADA'])
                      ->orWhere('tipo_emision', 2);
                });
            } else {
                $query->where('estado_factura', $estado);
            }
        }

        if (!empty($tipoEmision)) {
            $query->where('tipo_emision', (int) $tipoEmision);
        }

        return [
            'query' => $query,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'filtros' => [
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'id_sucursal' => $sucursalId,
                'id_punto_venta' => $puntoVentaId,
                'estado' => $estado,
                'tipo_emision' => $tipoEmision,
            ],
        ];
    }

    /**
     * Calcula las métricas tributarias y estructura la Cédula RCV oficial idéntica al SIN.
     *
     * @return array{resumen: array, consolidacion_rcv: array}
     */
    private function calcularMetricasYConsolidacion(Builder $query, string $fechaDesde, Request $request): array
    {
        $metricas = (clone $query)->selectRaw("
            COUNT(*) as total_registros,
            COUNT(CASE WHEN estado_factura = 'VALIDADA' AND (tipo_emision = 1 OR tipo_emision IS NULL) THEN 1 END) as cantidad_validas,
            COUNT(CASE WHEN estado_factura IN ('CONTINGENCIA', 'OFFLINE_PENDIENTE', 'OFFLINE_REGULARIZADA') OR (tipo_emision = 2 AND estado_factura != 'ANULADA') THEN 1 END) as cantidad_contingencias,
            COUNT(CASE WHEN estado_factura = 'ANULADA' THEN 1 END) as cantidad_anuladas,
            COUNT(CASE WHEN estado_factura IN ('RECHAZADA', 'OBSERVADA') THEN 1 END) as cantidad_rechazadas,
            COALESCE(SUM(CASE WHEN estado_factura != 'ANULADA' THEN monto_total ELSE 0 END), 0) as total_facturado,
            COALESCE(SUM(CASE WHEN estado_factura != 'ANULADA' THEN GREATEST(0, (monto_total - monto_total_sujeto_iva - monto_descuento)) ELSE 0 END), 0) as total_tasas,
            COALESCE(SUM(CASE WHEN estado_factura != 'ANULADA' THEN monto_descuento ELSE 0 END), 0) as total_descuento,
            COALESCE(SUM(CASE WHEN estado_factura != 'ANULADA' THEN monto_total_sujeto_iva ELSE 0 END), 0) as total_base_debito_fiscal,
            COALESCE(SUM(CASE WHEN estado_factura != 'ANULADA' THEN ROUND((monto_total_sujeto_iva * 0.13)::numeric, 2) ELSE 0 END), 0) as total_debito_fiscal
        ")->first();

        $totalRegistros = (int) ($metricas->total_registros ?? 0);
        $cantidadValidas = (int) ($metricas->cantidad_validas ?? 0);
        $cantidadContingencias = (int) ($metricas->cantidad_contingencias ?? 0);
        $cantidadAnuladas = (int) ($metricas->cantidad_anuladas ?? 0);
        $cantidadRechazadas = (int) ($metricas->cantidad_rechazadas ?? 0);
        $totalFacturado = (float) ($metricas->total_facturado ?? 0);
        $totalTasas = (float) ($metricas->total_tasas ?? 0);
        $totalDescuento = (float) ($metricas->total_descuento ?? 0);
        $totalBaseDebito = (float) ($metricas->total_base_debito_fiscal ?? 0);
        $debitoFiscal = round($totalBaseDebito * 0.13, 2);

        $periodo = $request->filled('mes') ? (int) $request->input('mes') : Carbon::parse($fechaDesde)->month;
        $gestion = $request->filled('gestion') ? (int) $request->input('gestion') : Carbon::parse($fechaDesde)->year;

        $resumen = [
            'total_registros' => $totalRegistros,
            'cantidad_validas' => $cantidadValidas,
            'cantidad_contingencias' => $cantidadContingencias,
            'cantidad_anuladas' => $cantidadAnuladas,
            'cantidad_rechazadas' => $cantidadRechazadas,
            'total_facturado' => round($totalFacturado, 2),
            'total_tasas' => round($totalTasas, 2),
            'total_descuento' => round($totalDescuento, 2),
            'total_base_debito_fiscal' => round($totalBaseDebito, 2),
            'debito_fiscal_iva' => $debitoFiscal,
        ];

        // Cédula oficial que replica el formulario de "CONSTANCIA DE REGISTRO DE COMPRAS Y VENTAS" del SIN
        $consolidacionRcv = [
            'nit' => '1002393029',
            'razon_social' => 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO - EMAPA',
            'periodo' => $periodo,
            'gestion' => $gestion,
            'fecha_hora' => Carbon::now()->format('d/m/Y h:i:s A'),
            'estado' => 'ORIGINAL',
            'ventas' => [
                'cantidad_facturas' => $totalRegistros,
                'facturas_validas' => $cantidadValidas + $cantidadContingencias,
                'facturas_anuladas' => $cantidadAnuladas,
                'importe_total' => round($totalFacturado, 2),
                'importe_no_sujetas_iva' => round($totalTasas, 2),
                'importe_ice' => 0.00,
                'importe_iehd' => 0.00,
                'importe_ipj' => 0.00,
                'importe_tasa_cero' => 0.00,
                'importe_tasas' => round($totalTasas, 2),
                'importe_otros_no_iva' => 0.00,
                'importe_exentas' => 0.00,
                'importe_descuentos' => round($totalDescuento, 2),
                'importe_gift_card' => 0.00,
                'importe_sin_derecho_df' => 0.00,
                'importe_con_derecho_df' => round($totalBaseDebito, 2),
                'importe_iva_df' => round($debitoFiscal, 2),
            ],
            'compras' => [
                'cantidad_facturas' => 0,
                'importe_total' => 0.00,
                'importe_no_sujetas_iva' => 0.00,
                'importe_tasas' => 0.00,
                'importe_con_derecho_cf' => 0.00,
                'importe_iva_cf' => 0.00,
            ],
            'notas_credito_debito' => [
                'cantidad_notas_cd' => 0,
                'cantidad_validas' => 0,
                'cantidad_anuladas' => 0,
                'importe_validas' => 0.00,
                'importe_anuladas' => 0.00,
            ],
        ];

        return [
            'resumen' => $resumen,
            'consolidacion_rcv' => $consolidacionRcv,
        ];
    }

    /**
     * Consulta y agregaciones del Libro de Ventas IVA.
     */
    public function libroVentas(Request $request): JsonResponse
    {
        @ini_set('memory_limit', '512M');

        $prep = $this->prepararQueryLibroVentas($request);
        $query = $prep['query'];
        $fechaDesde = $prep['fechaDesde'];
        $fechaHasta = $prep['fechaHasta'];

        $metricasYConsol = $this->calcularMetricasYConsolidacion($query, $fechaDesde, $request);
        $resumen = $metricasYConsol['resumen'];
        $consolidacionRcv = $metricasYConsol['consolidacion_rcv'];

        // Selección optimizada de columnas necesarias para la grilla del libro de ventas
        $limite = (int) $request->input('limite', 10000);
        $facturas = (clone $query)
            ->select([
                'id',
                'id_sucursal',
                'id_punto_venta',
                'numero_factura',
                'cuf',
                'fecha_emision',
                'nombre_razon_social',
                'numero_documento',
                'complemento',
                'tipo_emision',
                'monto_total',
                'monto_descuento',
                'monto_total_sujeto_iva',
                DB::raw('GREATEST(0, (monto_total - monto_total_sujeto_iva - monto_descuento)) as monto_tasas'),
                DB::raw('ROUND((monto_total_sujeto_iva * 0.13)::numeric, 2) as debito_fiscal'),
                'estado_factura',
                'representacion_grafica_qr',
            ])
            ->orderBy('numero_factura', 'asc')
            ->limit($limite)
            ->get();

        return response()->json([
            'success' => true,
            'periodo' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta,
            ],
            'resumen' => $resumen,
            'consolidacion_rcv' => $consolidacionRcv,
            'data' => $facturas,
        ], Response::HTTP_OK);
    }

    /**
     * Exportar el Libro de Ventas IVA en formato oficial CSV / Texto delimitado del SIN.
     */
    public function exportarCsvLibroVentas(Request $request): StreamedResponse
    {
        $prep = $this->prepararQueryLibroVentas($request);
        $query = $prep['query']->orderBy('numero_factura', 'asc');
        $fechaDesde = $prep['fechaDesde'];
        $fechaHasta = $prep['fechaHasta'];

        $filename = "Libro_Ventas_IVA_EMAPA_{$fechaDesde}_a_{$fechaHasta}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'NRO',
                'ESPECIFICACION',
                'FECHA DE LA FACTURA',
                'NRO. DE LA FACTURA',
                'CODIGO DE AUTORIZACION (CUF)',
                'NIT / CI / CEX CLIENTE',
                'COMPLEMENTO',
                'NOMBRE O RAZON SOCIAL',
                'IMPORTE TOTAL VENTA',
                'IMPORTE ICE',
                'IMPORTE IEHD',
                'IMPORTE IPJ',
                'TASAS',
                'OTROS NO SUJETOS A CREDITO FISCAL',
                'EXPORTACIONES Y EXENTAS',
                'VENTAS TASA CERO',
                'SUBTOTAL',
                'DESCUENTOS / BONIFICACIONES',
                'IMPORTE GIFT CARD',
                'BASE PARA DEBITO FISCAL',
                'DEBITO FISCAL (13%)',
                'ESTADO',
                'CODIGO DE CONTROL',
            ], ';');

            $correlativo = 1;

            foreach ($query->cursor() as $f) {
                $esAnulada = ($f->estado_factura === 'ANULADA');
                
                $totalVenta = $esAnulada ? 0.00 : (float) $f->monto_total;
                $descuento = $esAnulada ? 0.00 : (float) $f->monto_descuento;
                $baseFiscal = $esAnulada ? 0.00 : (float) $f->monto_total_sujeto_iva;
                $tasas = $esAnulada ? 0.00 : max(0.00, round($totalVenta - $baseFiscal - $descuento, 2));
                $subtotal = $esAnulada ? 0.00 : max(0.00, round($totalVenta - $tasas, 2));
                $debitoFiscal = round($baseFiscal * 0.13, 2);

                if ($esAnulada) {
                    $estado = 'A';
                } elseif (in_array($f->estado_factura, ['CONTINGENCIA', 'OFFLINE_PENDIENTE'], true) || (int) $f->tipo_emision === 2) {
                    $estado = 'E';
                } else {
                    $estado = 'V';
                }

                fputcsv($handle, [
                    $correlativo++,
                    1,
                    Carbon::parse($f->fecha_emision)->format('d/m/Y'),
                    $f->numero_factura,
                    $f->cuf,
                    $f->numero_documento,
                    $f->complemento ?? '',
                    $f->nombre_razon_social,
                    number_format($totalVenta, 2, '.', ''),
                    '0.00',
                    '0.00',
                    '0.00',
                    number_format($tasas, 2, '.', ''),
                    '0.00',
                    '0.00',
                    '0.00',
                    number_format($subtotal, 2, '.', ''),
                    number_format($descuento, 2, '.', ''),
                    '0.00',
                    number_format($baseFiscal, 2, '.', ''),
                    number_format($debitoFiscal, 2, '.', ''),
                    $estado,
                    '0',
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Exportar el Libro de Ventas RCV Oficial a Excel (.xlsx) profesional.
     * Incluye 2 Hojas: "Constancia RCV" (formato SIN) y "Libro de Ventas" (22 columnas RND).
     */
    public function exportarExcelLibroVentas(Request $request): StreamedResponse
    {
        $prep = $this->prepararQueryLibroVentas($request);
        $metricasYConsol = $this->calcularMetricasYConsolidacion($prep['query'], $prep['fechaDesde'], $request);

        return $this->reporteFacturasExcelService->exportarLibroVentasRcvXlsx(
            $prep['query'],
            $prep['filtros'],
            $metricasYConsol['resumen'],
            $metricasYConsol['consolidacion_rcv'],
            auth()->user()?->name ?? 'Administración EMAPA'
        );
    }

    /**
     * Exportar la Cédula Oficial de Constancia RCV a PDF, idéntica al formato impreso del SIN.
     */
    public function exportarPdfConstanciaRcv(Request $request): HttpResponse
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $prep = $this->prepararQueryLibroVentas($request);
        $metricasYConsol = $this->calcularMetricasYConsolidacion($prep['query'], $prep['fechaDesde'], $request);

        $html = View::make('reportes.facturacion.constancia-rcv-pdf', [
            'consolidacion' => $metricasYConsol['consolidacion_rcv'],
            'resumen' => $metricasYConsol['resumen'],
            'usuario' => auth()->user()?->email ?? auth()->user()?->name ?? 'patacamayaemapagmail',
        ])->render();

        $pdfBinario = SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('portrait')
            ->setOption('margin-top', '8mm')
            ->setOption('margin-bottom', '8mm')
            ->setOption('margin-left', '8mm')
            ->setOption('margin-right', '8mm')
            ->setOption('enable-local-file-access', true)
            ->output();

        $periodo = $metricasYConsol['consolidacion_rcv']['periodo'];
        $gestion = $metricasYConsol['consolidacion_rcv']['gestion'];
        $filename = "Constancia_RCV_EMAPA_{$gestion}_{$periodo}.pdf";

        return response($pdfBinario, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Resumen de ventas agrupadas por mes para gráficos del dashboard.
     */
    public function ventasMensuales(Request $request): JsonResponse
    {
        $anio = (int) $request->input('gestion', Carbon::now()->year);

        $facturas = Factura::whereYear('fecha_emision', $anio)
            ->where('estado_factura', '!=', 'ANULADA')
            ->selectRaw("EXTRACT(MONTH FROM fecha_emision)::int as mes, COUNT(id) as total_facturas, SUM(monto_total) as total_monto")
            ->groupByRaw("EXTRACT(MONTH FROM fecha_emision)")
            ->orderByRaw("EXTRACT(MONTH FROM fecha_emision)")
            ->get();

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $registro = $facturas->firstWhere('mes', $m);
            $data[] = [
                'mes' => $m,
                'nombre_mes' => $meses[$m],
                'total_facturas' => $registro ? (int) $registro->total_facturas : 0,
                'total_monto' => $registro ? round((float) $registro->total_monto, 2) : 0.0,
            ];
        }

        return response()->json([
            'success' => true,
            'gestion' => $anio,
            'data' => $data,
        ], Response::HTTP_OK);
    }
}
