<?php

declare(strict_types=1);

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\Factura;
use App\Models\Facturacion\SiatSucursal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteFacturacionController extends Controller
{
    /**
     * Consulta y agregaciones del Libro de Ventas IVA.
     */
    public function libroVentas(Request $request): JsonResponse
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
        $estado = $request->input('estado'); // VALIDADA, CONTINGENCIA, ANULADA, RECHAZADA, o null
        $tipoEmision = $request->input('tipo_emision'); // 1: En Línea, 2: Fuera de línea / contingencia

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

        // Métricas agregadas calculadas en base de datos de forma ultra-rápida y eficiente
        $metricas = (clone $query)->selectRaw("
            COUNT(*) as total_registros,
            COUNT(CASE WHEN estado_factura = 'VALIDADA' AND (tipo_emision = 1 OR tipo_emision IS NULL) THEN 1 END) as cantidad_validas,
            COUNT(CASE WHEN estado_factura IN ('CONTINGENCIA', 'OFFLINE_PENDIENTE', 'OFFLINE_REGULARIZADA') OR (tipo_emision = 2 AND estado_factura != 'ANULADA') THEN 1 END) as cantidad_contingencias,
            COUNT(CASE WHEN estado_factura = 'ANULADA' THEN 1 END) as cantidad_anuladas,
            COUNT(CASE WHEN estado_factura IN ('RECHAZADA', 'OBSERVADA') THEN 1 END) as cantidad_rechazadas,
            COALESCE(SUM(CASE WHEN estado_factura != 'ANULADA' THEN monto_total ELSE 0 END), 0) as total_facturado,
            COALESCE(SUM(CASE WHEN estado_factura != 'ANULADA' THEN monto_descuento ELSE 0 END), 0) as total_descuento,
            COALESCE(SUM(CASE WHEN estado_factura != 'ANULADA' THEN monto_total_sujeto_iva ELSE 0 END), 0) as total_base_debito_fiscal
        ")->first();

        $totalRegistros = (int) ($metricas->total_registros ?? 0);
        $cantidadValidas = (int) ($metricas->cantidad_validas ?? 0);
        $cantidadContingencias = (int) ($metricas->cantidad_contingencias ?? 0);
        $cantidadAnuladas = (int) ($metricas->cantidad_anuladas ?? 0);
        $cantidadRechazadas = (int) ($metricas->cantidad_rechazadas ?? 0);
        $totalFacturado = (float) ($metricas->total_facturado ?? 0);
        $totalDescuento = (float) ($metricas->total_descuento ?? 0);
        $totalBaseDebito = (float) ($metricas->total_base_debito_fiscal ?? 0);
        $debitoFiscal = round($totalBaseDebito * 0.13, 2);

        // Limitamos la lista para respuesta JSON evitando desbordamiento de memoria en meses con miles de facturas
        $facturas = (clone $query)
            ->with(['cliente:id,nombre_razon_social,numero_documento,complemento', 'sucursal:id,nombre,codigo_sucursal', 'puntoVenta:id,nombre,codigo_punto_venta'])
            ->orderBy('numero_factura', 'asc')
            ->limit(2000)
            ->get();

        return response()->json([
            'success' => true,
            'periodo' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta,
            ],
            'resumen' => [
                'total_registros' => $totalRegistros,
                'cantidad_validas' => $cantidadValidas,
                'cantidad_contingencias' => $cantidadContingencias,
                'cantidad_anuladas' => $cantidadAnuladas,
                'cantidad_rechazadas' => $cantidadRechazadas,
                'total_facturado' => round($totalFacturado, 2),
                'total_descuento' => round($totalDescuento, 2),
                'total_base_debito_fiscal' => round($totalBaseDebito, 2),
                'debito_fiscal_iva' => $debitoFiscal,
            ],
            'data' => $facturas,
        ], Response::HTTP_OK);
    }

    /**
     * Exportar el Libro de Ventas IVA en formato oficial CSV / Texto delimitado del SIN.
     * Estructura oficial según Resolución Normativa de Directorio (RND) del SIN.
     */
    public function exportarCsvLibroVentas(Request $request): StreamedResponse
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
        $estadoFiltro = $request->input('estado');
        $tipoEmisionFiltro = $request->input('tipo_emision');

        $query = Factura::query()
            ->whereDate('fecha_emision', '>=', $fechaDesde)
            ->whereDate('fecha_emision', '<=', $fechaHasta);

        if ($sucursalId !== null && $sucursalId !== '') {
            $query->where('id_sucursal', (int) $sucursalId);
        }

        if ($puntoVentaId !== null && $puntoVentaId !== '') {
            $query->where('id_punto_venta', (int) $puntoVentaId);
        }

        if (!empty($estadoFiltro)) {
            if ($estadoFiltro === 'CONTINGENCIA') {
                $query->where(function ($q) {
                    $q->whereIn('estado_factura', ['CONTINGENCIA', 'OFFLINE_PENDIENTE', 'OFFLINE_REGULARIZADA'])
                      ->orWhere('tipo_emision', 2);
                });
            } else {
                $query->where('estado_factura', $estadoFiltro);
            }
        }

        if (!empty($tipoEmisionFiltro)) {
            $query->where('tipo_emision', (int) $tipoEmisionFiltro);
        }

        $query->orderBy('numero_factura', 'asc');
        $filename = "Libro_Ventas_IVA_EMAPAP_{$fechaDesde}_a_{$fechaHasta}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            
            // BOM para compatibilidad con Excel UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados oficiales de las 23 columnas del SIN
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

            // Procesamiento en streaming mediante cursor para mínimo consumo de memoria
            foreach ($query->cursor() as $f) {
                $esAnulada = ($f->estado_factura === 'ANULADA');
                
                $totalVenta = $esAnulada ? 0.00 : (float) $f->monto_total;
                $descuento = $esAnulada ? 0.00 : (float) $f->monto_descuento;
                $subtotal = $totalVenta;
                $baseFiscal = $esAnulada ? 0.00 : (float) $f->monto_total_sujeto_iva;
                $debitoFiscal = round($baseFiscal * 0.13, 2);

                if ($esAnulada) {
                    $estado = 'A';
                } elseif (in_array($f->estado_factura, ['CONTINGENCIA', 'OFFLINE_PENDIENTE'], true) || (int) $f->tipo_emision === 2) {
                    $estado = 'E'; // Emitida en Contingencia (RND SIN)
                } else {
                    $estado = 'V'; // Válida
                }

                fputcsv($handle, [
                    $correlativo++,
                    1, // 1 = Compra Venta Estándar
                    Carbon::parse($f->fecha_emision)->format('d/m/Y'),
                    $f->numero_factura,
                    $f->cuf,
                    $f->numero_documento,
                    $f->complemento ?? '',
                    $f->nombre_razon_social,
                    number_format($totalVenta, 2, '.', ''),
                    '0.00', // ICE
                    '0.00', // IEHD
                    '0.00', // IPJ
                    '0.00', // Tasas
                    '0.00', // Otros no sujetos
                    '0.00', // Exentas
                    '0.00', // Tasa Cero
                    number_format($subtotal, 2, '.', ''),
                    number_format($descuento, 2, '.', ''),
                    '0.00', // Gift Card
                    number_format($baseFiscal, 2, '.', ''),
                    number_format($debitoFiscal, 2, '.', ''),
                    $estado,
                    '0', // Código de control manual
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
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
