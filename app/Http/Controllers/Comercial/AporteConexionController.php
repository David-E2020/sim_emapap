<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\Abonado;
use App\Models\Comercial\AporteConexion;
use App\Services\Contabilidad\ReporteFinancieroPdfService;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $tipoServicio = strtoupper(trim((string) $request->input('tipo_servicio', 'TODOS')));
        $estadoPago = strtoupper(trim((string) $request->input('estado_pago', 'TODOS')));
        $zona = trim((string) $request->input('zona', ''));

        $query = AporteConexion::query()->orderBy('fecha', 'desc')->orderBy('id', 'desc');

        if (!empty($search)) {
            $codigoPad = str_pad($search, 5, '0', STR_PAD_LEFT);
            $query->where(function ($q) use ($search, $codigoPad) {
                $q->where('codigo_socio', $search)
                    ->orWhere('codigo_socio', $codigoPad)
                    ->orWhere('codigo_socio', 'like', "%{$search}%")
                    ->orWhere('nombre_socio', 'ilike', "%{$search}%")
                    ->orWhere('factura', 'like', "%{$search}%")
                    ->orWhere('orden', 'like', "%{$search}%");
            });
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

        return response()->json([
            'success' => true,
            'abonado' => [
                'id' => $abonado->id,
                'codigo' => $abonado->codigo,
                'nombre_completo' => $abonado->nombre_completo,
                'numero_documento' => $abonado->numero_documento,
                'zona' => $abonado->zona?->nombre,
                'calle' => $abonado->calle?->nombre,
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
            'aporte' => 'required|numeric|min:0',
            'instalacion' => 'required|numeric|min:0',
            'plazo' => 'required|integer|min:1|max:36',
            'fecha' => 'required|date',
            'periodo' => 'nullable|string|max:20',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $codigoPad = str_pad(trim((string) $request->input('codigo_socio')), 5, '0', STR_PAD_LEFT);
        $abonado = Abonado::where('codigo', $codigoPad)->first();

        $aporteMonto = (float) $request->input('aporte');
        $instalacionMonto = (float) $request->input('instalacion');
        $total = $aporteMonto + $instalacionMonto;
        $abono = (float) $request->input('abono', 0);
        $saldo = max(0, $total - $abono);
        $pagado = $saldo <= 0;

        $aporte = AporteConexion::create([
            'tipo_servicio' => $request->input('tipo_servicio'),
            'periodo' => $request->input('periodo') ?: Carbon::parse($request->input('fecha'))->format('m/Y'),
            'codigo_socio' => $codigoPad,
            'nombre_socio' => $abonado ? $abonado->nombre_completo : trim((string) $request->input('nombre_socio', '')),
            'zona' => $abonado?->zona?->nombre ?: trim((string) $request->input('zona', '')),
            'estado' => 'ACTIVO',
            'fecha' => $request->input('fecha'),
            'aporte' => $aporteMonto,
            'instalacion' => $instalacionMonto,
            'total' => $total,
            'abono' => $abono,
            'saldo' => $saldo,
            'plazo' => (int) $request->input('plazo', 1),
            'pagado' => $pagado,
            'fecha_pago' => $pagado ? ($request->input('fecha_pago') ?: date('Y-m-d')) : null,
            'orden' => $request->input('orden', ''),
            'factura' => $request->input('factura', ''),
            'observaciones' => $request->input('observaciones', ''),
        ]);

        if ($request->input('tipo_servicio') === 'ALCANTARILLADO' && $abonado) {
            $abonado->update(['tiene_alcantarillado' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Contrato de conexión domiciliaria registrado exitosamente',
            'data' => $aporte,
        ], Response::HTTP_CREATED);
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

        $nombreSocio = strtoupper(trim((string) ($abonado?->nombre_completo ?: $aporte->nombre_socio)));
        $ci = $abonado?->numero_documento ?: 'S/N';
        if ($abonado?->complemento) {
            $ci .= ' ' . $abonado->complemento;
        }

        $zona = strtoupper(trim((string) ($abonado?->zona?->nombre ?: ($aporte->zona ?: 'S/Z'))));
        $calle = strtoupper(trim((string) ($abonado?->calle?->nombre ?: 'S/N')));
        if ($abonado?->numero_vivienda) {
            $calle .= ' #' . $abonado->numero_vivienda;
        }

        $esAgua = strtoupper((string) $aporte->tipo_servicio) === 'AGUA';
        $tituloReporte = $esAgua
            ? 'CONTRATO DE PAGO DIFERIDO DEL COSTO DE LA CONEXIÓN DOMICILIARIA'
            : 'CRONOGRAMA DE PAGOS / INSTALACIÓN DE ALCANTARILLADO';

        $tipoServicioNombre = $esAgua ? 'Agua Potable' : 'Alcantarillado Sanitario';

        // Generar desglose de cuotas del cronograma
        $cuotas = [];
        $plazo = max(1, (int) $aporte->plazo);
        $montoPorCuota = round(((float) $aporte->total) / $plazo, 2);
        $fechaBase = $aporte->fecha ? Carbon::parse($aporte->fecha) : Carbon::now();

        for ($i = 0; $i < $plazo; $i++) {
            $fechaCuota = (clone $fechaBase)->addMonths($i + 1);
            $cuotas[] = [
                'periodo' => $fechaCuota->format('m/Y'),
                'fecha_pago' => $fechaCuota->format('d/m/Y'),
                'importe' => ($i === $plazo - 1)
                    ? ((float) $aporte->total - ($montoPorCuota * ($plazo - 1)))
                    : $montoPorCuota,
            ];
        }

        // Monto en literal
        $numeroLiteral = ReporteFinancieroPdfService::convertirNumeroALetras((float) $aporte->total);

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
}
