<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Facturacion\FacturaController;
use App\Models\Comercial\Abonado;
use App\Models\Comercial\AporteConexion;
use App\Models\Facturacion\Factura;
use App\Services\Contabilidad\ReporteFinancieroPdfService;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class AporteConexionController extends Controller
{
    /**
     * Listado paginado de contratos de aportes e instalaciones con filtros.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = trim((string) $request->input('search', ''));
        $tipoBusqueda = trim((string) $request->input('tipo_busqueda', 'codigo_abonado'));
        $tipoServicio = strtoupper(trim((string) $request->input('tipo_servicio', 'TODOS')));
        $estadoPago = strtoupper(trim((string) $request->input('estado_pago', 'TODOS')));
        $zona = trim((string) $request->input('zona', ''));

        $query = AporteConexion::query()->orderBy('fecha', 'desc')->orderBy('id', 'desc');

        if (!empty($search)) {
            $codigoPad = str_pad($search, 5, '0', STR_PAD_LEFT);
            if ($tipoBusqueda === 'codigo_abonado' || $tipoBusqueda === 'codigo_socio') {
                $query->where(function ($q) use ($search, $codigoPad) {
                    $q->where('codigo_socio', $search)
                        ->orWhere('codigo_socio', $codigoPad)
                        ->orWhere('codigo_socio', 'like', "{$search}%");
                });
            } elseif ($tipoBusqueda === 'carnet_nit') {
                $codigosPorCi = Abonado::where('numero_documento', 'like', "%{$search}%")->pluck('codigo')->toArray();
                $query->whereIn('codigo_socio', $codigosPorCi);
            } elseif ($tipoBusqueda === 'cliente') {
                $query->where('nombre_socio', 'ilike', "%{$search}%");
            } elseif ($tipoBusqueda === 'numero_factura') {
                $query->where(function ($q) use ($search) {
                    $q->where('factura', 'like', "%{$search}%")
                        ->orWhere('orden', 'like', "%{$search}%");
                });
            } else {
                // todos
                $query->where(function ($q) use ($search, $codigoPad) {
                    $q->where('codigo_socio', $search)
                        ->orWhere('codigo_socio', $codigoPad)
                        ->orWhere('codigo_socio', 'like', "%{$search}%")
                        ->orWhere('nombre_socio', 'ilike', "%{$search}%")
                        ->orWhere('factura', 'like', "%{$search}%")
                        ->orWhere('orden', 'like', "%{$search}%");
                });
            }
        }

        if ($tipoServicio !== 'TODOS' && in_array($tipoServicio, ['AGUA', 'ALCANTARILLADO'])) {
            $query->where('tipo_servicio', $tipoServicio);
        }

        if ($estadoPago === 'PAGADO') {
            $query->where('pagado', true);
        } elseif ($estadoPago === 'PENDIENTE') {
            $query->where('pagado', false);
        }

        if (!empty($zona) && $zona !== 'TODAS') {
            $query->where('zona', 'ilike', "%{$zona}%");
        }

        $paginator = $query->paginate($perPage);

        // Métricas globales
        $resumen = [
            'total_contratos' => AporteConexion::count(),
            'total_agua' => AporteConexion::where('tipo_servicio', 'AGUA')->count(),
            'total_alcantarillado' => AporteConexion::where('tipo_servicio', 'ALCANTARILLADO')->count(),
            'monto_total' => (float) AporteConexion::sum('total'),
            'total_pagados' => AporteConexion::where('pagado', true)->count(),
            'total_pendientes' => AporteConexion::where('pagado', false)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'resumen' => $resumen,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ], Response::HTTP_OK);
    }

    /**
     * Historial de aportes y contratos de conexión para un abonado específico.
     */
    public function porAbonado(int|string $id): JsonResponse
    {
        $abonado = is_numeric($id) ? Abonado::find($id) : null;
        if (!$abonado) {
            $abonado = Abonado::where('codigo', (string) $id)
                ->orWhere('codigo', str_pad((string) $id, 5, '0', STR_PAD_LEFT))
                ->first();
        }

        if (!$abonado) {
            return response()->json([
                'success' => false,
                'message' => 'Abonado no encontrado',
            ], Response::HTTP_NOT_FOUND);
        }

        $codigo = $abonado->codigo;
        $codigoSinCeros = ltrim($codigo, '0');

        $aportes = AporteConexion::where(function ($q) use ($codigo, $codigoSinCeros) {
            $q->where('codigo_socio', $codigo)
                ->orWhere('codigo_socio', $codigoSinCeros);
        })
        ->orderBy('fecha', 'desc')
        ->orderBy('id', 'desc')
        ->get();

        // Detectar si existen antecedentes históricos a otro nombre (ej: MITA TUSCO vs TOLA ACARAPI)
        $nombresHistoricos = $aportes->pluck('nombre_socio')->filter()->unique()->values();
        $nombreHistoricoDiferente = null;
        foreach ($nombresHistoricos as $nh) {
            $nhNorm = strtoupper(trim((string) preg_replace('/\s+/', ' ', $nh)));
            $abNorm = strtoupper(trim((string) preg_replace('/\s+/', ' ', $abonado->nombre_completo)));
            if ($nhNorm !== $abNorm && levenshtein($nhNorm, $abNorm) > 4) {
                $nombreHistoricoDiferente = $nh;
                break;
            }
        }

        return response()->json([
            'success' => true,
            'abonado' => [
                'id' => $abonado->id,
                'codigo' => $abonado->codigo,
                'nombre_completo' => $abonado->nombre_completo,
                'numero_documento' => $abonado->numero_documento,
                'zona' => $abonado->zona?->nombre,
                'calle' => $abonado->calle?->nombre,
                'numero_vivienda' => $abonado->numero_vivienda,
                'tiene_alcantarillado' => (bool) $abonado->tiene_alcantarillado,
                'categoria' => $abonado->categoria?->nombre,
                'nombre_historico_diferente' => $nombreHistoricoDiferente,
            ],
            'data' => $aportes,
            'total' => $aportes->count(),
        ], Response::HTTP_OK);
    }

    /**
     * Registro de nuevo contrato de conexión domiciliaria.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'codigo_socio' => 'required|string|max:50',
            'tipo_servicio' => 'required|in:AGUA,ALCANTARILLADO',
            'aporte' => 'nullable|numeric|min:0',
            'instalacion' => 'required|numeric|min:0',
            'plazo' => 'required|integer|min:1|max:36',
            'fecha' => 'required|date',
            'periodo' => 'nullable|string|max:20',
            'observaciones' => 'nullable|string',
            'cuotas' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $codigoPad = str_pad(trim((string) $request->input('codigo_socio')), 5, '0', STR_PAD_LEFT);
        $abonado = Abonado::where('codigo', $codigoPad)
            ->orWhere('codigo', trim((string) $request->input('codigo_socio')))
            ->first();

        $aporteMonto = (float) $request->input('aporte', 0);
        $instalacionMonto = (float) $request->input('instalacion', 0);
        $totalContrato = $aporteMonto + $instalacionMonto;
        $abono = (float) $request->input('abono', 0);
        $saldo = max(0, $totalContrato - $abono);
        $plazo = (int) $request->input('plazo', 1);

        DB::beginTransaction();
        try {
            $cuotas = $request->input('cuotas', []);
            $primerPeriodo = !empty($cuotas) ? ($cuotas[0]['periodo'] ?? null) : null;
            if (!$primerPeriodo) {
                $primerPeriodo = $request->input('periodo') ?: Carbon::parse($request->input('fecha'))->format('m/Y');
            }

            $primerAporte = null;
            if ($plazo > 1 && !empty($cuotas)) {
                foreach ($cuotas as $index => $c) {
                    $montoCuota = (float) ($c['monto'] ?? ($totalContrato / $plazo));
                    $cuotaPagada = ($index === 0 && $abono >= $montoCuota);
                    $registroCuota = AporteConexion::create([
                        'tipo_servicio' => $request->input('tipo_servicio'),
                        'periodo' => $c['periodo'] ?? $primerPeriodo,
                        'codigo_socio' => $codigoPad,
                        'nombre_socio' => $abonado ? $abonado->nombre_completo : trim((string) $request->input('nombre_socio', '')),
                        'zona' => $abonado?->zona?->nombre ?: trim((string) $request->input('zona', '')),
                        'estado' => 'ACTIVO',
                        'fecha' => $request->input('fecha'),
                        'aporte' => ($index === 0) ? $aporteMonto : 0.00,
                        'instalacion' => ($index === 0) ? $instalacionMonto : 0.00,
                        'total' => $montoCuota,
                        'abono' => $cuotaPagada ? $montoCuota : 0.00,
                        'saldo' => $cuotaPagada ? 0.00 : $montoCuota,
                        'plazo' => $plazo,
                        'pagado' => $cuotaPagada,
                        'fecha_pago' => $cuotaPagada ? date('Y-m-d') : null,
                        'orden' => 'Cuota ' . ($index + 1) . '/' . $plazo,
                        'factura' => '',
                        'observaciones' => 'Cuota ' . ($index + 1) . ' de ' . $plazo . ($request->filled('observaciones') ? ' - ' . $request->input('observaciones') : ''),
                    ]);

                    if ($index === 0) {
                        $primerAporte = $registroCuota;
                    }
                }
            } else {
                $pagado = $saldo <= 0;
                $primerAporte = AporteConexion::create([
                    'tipo_servicio' => $request->input('tipo_servicio'),
                    'periodo' => $primerPeriodo,
                    'codigo_socio' => $codigoPad,
                    'nombre_socio' => $abonado ? $abonado->nombre_completo : trim((string) $request->input('nombre_socio', '')),
                    'zona' => $abonado?->zona?->nombre ?: trim((string) $request->input('zona', '')),
                    'estado' => 'ACTIVO',
                    'fecha' => $request->input('fecha'),
                    'aporte' => $aporteMonto,
                    'instalacion' => $instalacionMonto,
                    'total' => $totalContrato,
                    'abono' => $abono,
                    'saldo' => $saldo,
                    'plazo' => $plazo,
                    'pagado' => $pagado,
                    'fecha_pago' => $pagado ? ($request->input('fecha_pago') ?: date('Y-m-d')) : null,
                    'orden' => '1/1',
                    'factura' => $request->input('factura', ''),
                    'observaciones' => $request->input('observaciones', ''),
                ]);
            }

            if ($request->input('tipo_servicio') === 'ALCANTARILLADO' && $abonado) {
                $abonado->update(['tiene_alcantarillado' => true]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contrato de conexión domiciliaria registrado exitosamente',
                'data' => $primerAporte,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al registrar contrato de conexión', ['exception' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el contrato de conexión: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Emisión del Contrato Oficial de Pago Diferido de Conexión Domiciliaria en PDF.
     * Homologado exactamente con el reporte apagua.frx / alcanta.frx de FoxPro.
     */
    public function contratoPdf(int $id): Response
    {
        $aporte = AporteConexion::findOrFail($id);

        $codigoPad = str_pad(trim((string) $aporte->codigo_socio), 5, '0', STR_PAD_LEFT);
        $abonado = Abonado::with(['zona', 'calle'])
            ->where('codigo', $codigoPad)
            ->orWhere('codigo', $aporte->codigo_socio)
            ->first();

        // En reportes de contratos historicos, se respeta el nombre suscrito originalmente en el registro
        $nombreSocio = strtoupper(trim((string) ($aporte->nombre_socio ?: $abonado?->nombre_completo)));
        $ci = $abonado?->numero_documento ?: 'S/N';
        if ($abonado?->complemento) {
            $ci .= ' ' . $abonado->complemento;
        }

        $zona = strtoupper(trim((string) ($aporte->zona ?: ($abonado?->zona?->nombre ?: 'S/Z'))));
        $calle = strtoupper(trim((string) ($abonado?->calle?->nombre ?: 'S/N')));
        if ($abonado?->numero_vivienda) {
            $calle .= ' #' . $abonado->numero_vivienda;
        }

        $esAgua = strtoupper((string) $aporte->tipo_servicio) === 'AGUA';
        $tituloReporte = $esAgua
            ? 'CONTRATO DE PAGO DIFERIDO DEL COSTO DE LA CONEXIÓN DOMICILIARIA'
            : 'CRONOGRAMA DE PAGOS / INSTALACIÓN DE ALCANTARILLADO';

        $tipoServicioNombre = $esAgua ? 'Agua Potable' : 'Alcantarillado Sanitario';

        // Buscar cuotas hermanas registradas en aportes_conexiones para este contrato
        $cuotasRegistradas = AporteConexion::where('codigo_socio', $aporte->codigo_socio)
            ->where('tipo_servicio', $aporte->tipo_servicio)
            ->where('fecha', $aporte->fecha)
            ->orderBy('id', 'asc')
            ->get();

        $fechaBase = $aporte->fecha ? Carbon::parse($aporte->fecha) : Carbon::now();
        $cuotas = [];
        if ($cuotasRegistradas->count() > 1) {
            $totalContrato = (float) $cuotasRegistradas->sum('total');
            $sumaAporteInst = (float) $aporte->aporte + (float) $aporte->instalacion;
            if ($sumaAporteInst > $totalContrato) {
                $totalContrato = $sumaAporteInst;
            }

            foreach ($cuotasRegistradas as $index => $c) {
                $cuotas[] = [
                    'numero' => $index + 1,
                    'periodo' => $c->periodo ?: ($c->fecha ? Carbon::parse($c->fecha)->addMonths($index)->format('m/Y') : '-'),
                    'fecha_pago' => $c->fecha_pago ? Carbon::parse($c->fecha_pago)->format('d/m/Y') : ($c->fecha ? Carbon::parse($c->fecha)->addMonths($index)->format('d/m/Y') : '-'),
                    'importe' => (float) $c->total,
                    'factura' => $c->factura,
                    'pagado' => (bool) $c->pagado,
                ];
            }
        } else {
            $totalContrato = ((float) $aporte->aporte + (float) $aporte->instalacion) > 0
                ? ((float) $aporte->aporte + (float) $aporte->instalacion)
                : (float) $aporte->total;

            $plazo = max(1, (int) $aporte->plazo);
            $montoPorCuota = round($totalContrato / $plazo, 2);

            for ($i = 0; $i < $plazo; $i++) {
                $fechaCuota = (clone $fechaBase)->addMonths($i + 1);
                $cuotas[] = [
                    'numero' => $i + 1,
                    'periodo' => $fechaCuota->format('m/Y'),
                    'fecha_pago' => $fechaCuota->format('d/m/Y'),
                    'importe' => ($i === $plazo - 1)
                        ? ($totalContrato - ($montoPorCuota * ($plazo - 1)))
                        : $montoPorCuota,
                    'factura' => $aporte->factura,
                    'pagado' => (bool) $aporte->pagado,
                ];
            }
        }

        // Monto en literal
        $numeroLiteral = ReporteFinancieroPdfService::convertirNumeroALetras($totalContrato);

        // Fecha en texto legible (ej: 17 DE SEPTIEMBRE DE 2026)
        $meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
        ];
        $dia = $fechaBase->format('d');
        $mes = $meses[(int) $fechaBase->format('n')];
        $anio = $fechaBase->format('Y');
        $fechaTexto = "{$dia} DE {$mes} DE {$anio}";

        $aporte->total = $totalContrato;

        $html = View::make('reportes.comercial.contrato-conexion', [
            'aporte' => $aporte,
            'abonado' => $abonado,
            'codigoSocio' => $codigoPad,
            'nombreSocio' => $nombreSocio,
            'ci' => $ci,
            'zona' => $zona,
            'calle' => $calle,
            'tipoServicioNombre' => $tipoServicioNombre,
            'tituloReporte' => $tituloReporte,
            'cuotas' => $cuotas,
            'numeroLiteral' => $numeroLiteral,
            'fechaTexto' => $fechaTexto,
        ])->render();

        try {
            $pdf = SnappyPdf::loadHTML($html)
                ->setPaper('letter')
                ->setOrientation('portrait')
                ->setOption('margin-top', '12mm')
                ->setOption('margin-bottom', '12mm')
                ->setOption('margin-left', '15mm')
                ->setOption('margin-right', '15mm')
                ->setOption('enable-local-file-access', true)
                ->output();

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="Contrato_Conexion_%s.pdf"', $codigoPad),
            ]);
        } catch (Exception $e) {
            // Fallback a DomPdf si wkhtmltopdf no está disponible
            $dompdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $dompdf->setPaper('letter', 'portrait');

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="Contrato_Conexion_%s.pdf"', $codigoPad),
            ]);
        }
    }

    /**
     * Emisión o Visualización del PDF de la Factura / Recibo de Pago de la Cuota.
     * Si la factura está en el subsistema SIAT, emite la factura electrónica oficial.
     * Si es un registro histórico o pre-SIAT, emite el Comprobante Oficial de Caja.
     */
    public function facturaPdf(Request $request, int $id): Response
    {
        $aporte = AporteConexion::findOrFail($id);
        $formato = (string) $request->input('formato', 'rollo');

        $numFactura = trim((string) $aporte->factura);
        if (empty($numFactura)) {
            abort(404, 'Esta cuota no cuenta con un número de factura o comprobante registrado.');
        }

        $codigoPad = str_pad(trim((string) $aporte->codigo_socio), 5, '0', STR_PAD_LEFT);
        $abonado = Abonado::where('codigo', $codigoPad)
            ->orWhere('codigo', $aporte->codigo_socio)
            ->first();

        // 1. Intentar localizar la factura oficial en facturacion.facturas
        $numInt = is_numeric($numFactura) ? (int) $numFactura : null;
        $facturaOficial = null;

        if ($numInt !== null) {
            $facturaOficial = Factura::where('numero_factura', $numInt)
                ->where(function ($q) use ($abonado, $aporte) {
                    if ($abonado) {
                        $q->where('id_abonado', $abonado->id);
                    }
                    $q->orWhere('nombre_razon_social', 'ilike', '%' . trim($aporte->nombre_socio) . '%');
                })
                ->first();
        }

        // Si se encuentra la factura oficial SIAT, delegar la generación gráfica a FacturaController
        if ($facturaOficial) {
            return app(FacturaController::class)->descargarPdf($request, $facturaOficial->id);
        }

        // 2. Si no existe en facturacion.facturas (comprobante histórico de FoxPro o pago manual de ventanilla)
        $empresa = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('facturacion.configuracion_empresa')) {
                $empresa = \App\Models\Facturacion\ConfiguracionEmpresa::getActiva();
            }
        } catch (\Throwable $e) {
            $empresa = null;
        }

        $nombreSocio = strtoupper(trim((string) ($aporte->nombre_socio ?: $abonado?->nombre_completo)));
        $ci = $abonado?->numero_documento ?: 'S/N';
        if ($abonado?->complemento) {
            $ci .= ' ' . $abonado->complemento;
        }

        $zona = strtoupper(trim((string) ($aporte->zona ?: ($abonado?->zona?->nombre ?: 'S/Z'))));
        $montoTotal = (float) $aporte->total;
        $numeroLiteral = ReporteFinancieroPdfService::convertirNumeroALetras($montoTotal);

        $esAgua = strtoupper((string) $aporte->tipo_servicio) === 'AGUA';
        $tipoServicioNombre = $esAgua ? 'Agua Potable' : 'Alcantarillado Sanitario';

        $fechaEmision = $aporte->fecha_pago ? Carbon::parse($aporte->fecha_pago) : ($aporte->fecha ? Carbon::parse($aporte->fecha) : Carbon::now());

        $viewName = ($formato === 'rollo') 
            ? 'reportes.comercial.recibo-aporte-rollo-pdf'
            : 'reportes.comercial.recibo-aporte-carta-pdf';

        $html = View::make($viewName, [
            'aporte' => $aporte,
            'abonado' => $abonado,
            'empresa' => $empresa,
            'codigoSocio' => $codigoPad,
            'nombreSocio' => $nombreSocio,
            'ci' => $ci,
            'zona' => $zona,
            'tipoServicioNombre' => $tipoServicioNombre,
            'montoTotal' => $montoTotal,
            'numeroLiteral' => $numeroLiteral,
            'fechaEmision' => $fechaEmision,
            'formato' => $formato,
        ])->render();

        try {
            $pdfBuilder = SnappyPdf::loadHTML($html);

            if ($formato === 'rollo') {
                $pdf = $pdfBuilder
                    ->setOption('page-width', '80mm')
                    ->setOption('page-height', '200mm')
                    ->setOption('margin-top', '3mm')
                    ->setOption('margin-bottom', '3mm')
                    ->setOption('margin-left', '4mm')
                    ->setOption('margin-right', '4mm')
                    ->setOption('enable-local-file-access', true)
                    ->output();
            } else {
                $pdf = $pdfBuilder
                    ->setPaper('letter')
                    ->setOrientation('portrait')
                    ->setOption('margin-top', '12mm')
                    ->setOption('margin-bottom', '12mm')
                    ->setOption('margin-left', '15mm')
                    ->setOption('margin-right', '15mm')
                    ->setOption('enable-local-file-access', true)
                    ->output();
            }

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="Factura_Recibo_%s_%s.pdf"', $numFactura, $formato),
            ]);
        } catch (\Throwable $e) {
            $dompdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            if ($formato === 'rollo') {
                $dompdf->setPaper([0, 0, 226.77, 566.93], 'portrait');
            } else {
                $dompdf->setPaper('letter', 'portrait');
            }

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="Factura_Recibo_%s_%s.pdf"', $numFactura, $formato),
            ]);
        }
    }
}
