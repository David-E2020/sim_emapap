<?php

declare(strict_types=1);

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\ClienteFactura;
use App\Models\Facturacion\Factura;
use App\Models\Facturacion\FacturaDetalle;
use App\Models\Facturacion\ProductoServicioFactura;
use App\Models\Facturacion\SiatCufd;
use App\Models\Facturacion\SiatCuis;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Services\Facturacion\CufService;
use App\Services\Facturacion\FirmaDigitalService;
use App\Services\Facturacion\RepresentacionGraficaService;
use App\Services\Facturacion\SiatSoapService;
use App\Services\Facturacion\XmlFacturaService;
use Carbon\Carbon;
use Exception;
use App\Mail\Facturacion\FacturaEmitidaMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class FacturaController extends Controller
{
    public function __construct(
        private readonly CufService $cufService,
        private readonly XmlFacturaService $xmlFacturaService,
        private readonly FirmaDigitalService $firmaDigitalService,
        private readonly SiatSoapService $siatSoapService,
        private readonly RepresentacionGraficaService $representacionGraficaService,
        private readonly \App\Services\Facturacion\EmisionFacturaService $emisionFacturaService
    ) {}

    /**
     * Listado paginado de facturas con filtros de búsqueda.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $estado = $request->input('estado');
        $idSucursal = $request->input('id_sucursal');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        $query = Factura::with(['detalles', 'sucursal', 'puntoVenta'])
            ->orderByDesc('id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_documento', 'like', "%{$search}%")
                    ->orWhere('nombre_razon_social', 'ilike', "%{$search}%")
                    ->orWhere('numero_factura', 'like', "%{$search}%")
                    ->orWhere('cuf', 'like', "%{$search}%");
            });
        }

        if (!empty($estado)) {
            $query->where('estado_factura', $estado);
        }

        if (!empty($idSucursal)) {
            $query->where('id_sucursal', $idSucursal);
        }

        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $query->whereBetween('fecha_emision', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay(),
            ]);
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ], Response::HTTP_OK);
    }

    /**
     * Emisión de Factura Electrónica en Línea.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_sucursal' => 'nullable|integer',
            'id_punto_venta' => 'nullable|integer',
            'codigo_tipo_documento_identidad' => 'required|integer',
            'numero_documento' => 'required|string|max:50',
            'complemento' => 'nullable|string|max:10',
            'nombre_razon_social' => 'required|string|max:255',
            'correo_electronico' => 'nullable|email|max:150',
            'codigo_metodo_pago' => 'required|integer',
            'numero_tarjeta' => 'nullable|string|max:20',
            'monto_descuento' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.codigo_producto_empresa' => 'required|string',
            'items.*.descripcion' => 'required|string',
            'items.*.cantidad' => 'required|numeric|min:0.0001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.monto_descuento' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $datos = $request->all();
            $datos['usuario_emision'] = auth()->user()->name ?? 'operador_emapa';
            $factura = $this->emisionFacturaService->emitir($datos);

            // Generar y guardar PDF
            try {
                $pdfBinario = $this->representacionGraficaService->generarPdf($factura);
                $pdfFilename = "siat/facturas/pdf/{$factura->cuf}.pdf";
                Storage::disk('local')->put($pdfFilename, $pdfBinario);
                $factura->pdf_path = $pdfFilename;
                $factura->save();
            } catch (Exception $e) {
                // Generable bajo demanda
            }

            // Envío por correo si está registrado
            $correoCliente = $factura->cliente->correo_electronico ?? $request->input('correo_electronico');
            if (!empty($correoCliente) && filter_var($correoCliente, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($correoCliente)->send(new FacturaEmitidaMail($factura));
                } catch (\Throwable $e) {
                    Log::warning("No se pudo enviar correo automático de factura ID {$factura->id} a {$correoCliente}: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data' => $factura->load(['detalles', 'sucursal', 'puntoVenta']),
                'message' => 'Factura emitida y registrada exitosamente.',
            ], Response::HTTP_CREATED);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al emitir factura: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Anulación de Factura ante el SIN.
     */
    public function anular(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'codigo_motivo' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $factura = Factura::with(['sucursal', 'puntoVenta'])->find($id);
        if (!$factura) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($factura->estado_factura === 'ANULADA') {
            return response()->json([
                'success' => false,
                'message' => 'La factura ya se encuentra anulada.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $motivo = (int) $request->input('codigo_motivo');
        $cuis = 'CUIS_EMAPA_DEMO';

        $resp = $this->siatSoapService->anularFactura(
            $factura->cuf,
            $motivo,
            $cuis,
            $factura->cufd,
            $factura->sucursal->codigo_sucursal ?? 0,
            $factura->puntoVenta->codigo_punto_venta ?? 0
        );

        $factura->estado_factura = 'ANULADA';
        $factura->codigo_motivo_anulacion = $motivo;
        $factura->fecha_anulacion = Carbon::now();
        $factura->_usuario_modificacion = auth()->id() ?? 1;
        $factura->_fecha_modificacion = Carbon::now();
        $factura->_transaccion = 'ANULAR';
        $factura->save();

        return response()->json([
            'success' => true,
            'message' => 'Factura anulada exitosamente.',
        ], Response::HTTP_OK);
    }

    /**
     * Descarga / Visualización del PDF de la factura (Carta o Rollo 80mm).
     */
    public function descargarPdf(Request $request, int $id): HttpResponse
    {
        $factura = Factura::findOrFail($id);
        $formato = (string) $request->input('formato', 'carta');

        $pdfBinario = $this->representacionGraficaService->generarPdf($factura, $formato);

        return response($pdfBinario, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Factura_{$factura->numero_factura}_{$formato}.pdf\"",
        ]);
    }

    /**
     * Descarga del XML oficial de la factura.
     */
    public function descargarXml(int $id): HttpResponse
    {
        $factura = Factura::findOrFail($id);

        if ($factura->xml_firmado_path && Storage::disk('local')->exists($factura->xml_firmado_path)) {
            $xml = Storage::disk('local')->get($factura->xml_firmado_path);
        } else {
            $xml = $this->xmlFacturaService->construirXml($factura);
        }

        return response($xml, Response::HTTP_OK, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => "attachment; filename=\"Factura_{$factura->numero_factura}.xml\"",
        ]);
    }

    /**
     * Reenviar factura por correo electrónico a un destinatario específico.
     */
    public function enviarPorCorreo(Request $request, int $id): JsonResponse
    {
        $factura = Factura::with(['detalles', 'cliente', 'sucursal', 'puntoVenta'])->findOrFail($id);
        $correo = $request->input('correo_electronico') ?: ($factura->cliente->correo_electronico ?? null);

        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Debe proporcionar una dirección de correo electrónico válida.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            Mail::to($correo)->send(new FacturaEmitidaMail($factura));

            return response()->json([
                'success' => true,
                'message' => "La factura N° {$factura->numero_factura} fue enviada exitosamente a {$correo}.",
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verificar y sincronizar el estado tributario de una factura directamente con el SIN.
     */
    public function verificarEstadoSin(int $id): JsonResponse
    {
        $factura = Factura::with(['sucursal', 'puntoVenta', 'cufdModel'])->findOrFail($id);

        $cuisActivo = SiatCuis::where('id_sucursal', $factura->id_sucursal)->latest('id')->first();
        $cuis = $cuisActivo ? $cuisActivo->codigo_cuis : 'CUIS_EMAPAP_DEFAULT';
        $cufd = $factura->cufd;
        $sucursal = $factura->sucursal ? (int) $factura->sucursal->codigo_sucursal : 0;
        $puntoVenta = $factura->puntoVenta ? (int) $factura->puntoVenta->codigo_punto_venta : 0;

        $res = $this->siatSoapService->verificarEstadoFactura(
            $factura->cuf,
            $cuis,
            $cufd,
            $sucursal,
            $puntoVenta,
            (int) $factura->tipo_emision
        );

        if ($res['success'] && !empty($res['codigo_descripcion'])) {
            $estadoSin = $res['codigo_descripcion'];
            if (in_array($estadoSin, ['VALIDADA', 'ANULADA', 'OBSERVADA', 'RECHAZADA'])) {
                $factura->estado_factura = $estadoSin;
                $factura->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => $res['mensajes'] ?? $res['mensaje'] ?? 'Estado consultado en el SIN.',
            'estado_local' => $factura->estado_factura,
            'estado_sin' => $res['codigo_descripcion'] ?? $factura->estado_factura,
            'mensaje_sin' => $res['mensajes'] ?? $res['mensaje'] ?? 'Estado consultado en el SIN.',
            'siat' => [
                'codigoDescripcion' => $res['codigo_descripcion'] ?? $factura->estado_factura,
                'codigoEstado' => $res['codigo_estado'] ?? null,
                'codigoRecepcion' => $res['codigo_recepcion'] ?? null,
                'mensajes' => $res['mensajes'] ?? $res['mensaje'] ?? null,
            ],
            'data' => $factura,
        ], Response::HTTP_OK);
    }

    /**
     * Catálogo rotativo de Leyendas Oficiales del SIN (Ley N° 453).
     */
    private function obtenerLeyendaOficial(): string
    {
        $leyendas = [
            'Ley N° 453: El proveedor deberá suministrar el servicio en las condiciones ofertadas o convenidas.',
            'Ley N° 453: Los servicios básicos no pueden ser suspendidos sin previo aviso y conforme a normativa sectorial.',
            'Ley N° 453: El proveedor debe brindar atención sin discriminación, con trato digno y cordial a los usuarios.',
            'Ley N° 453: Tienes derecho a recibir información veraz, clara y oportuna sobre las tarifas y consumos.',
            'Ley N° 453: El usuario tiene derecho a la reposición o compensación por deficiencias imputables al proveedor.',
        ];

        return $leyendas[array_rand($leyendas)];
    }
}
