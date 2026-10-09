<?php

declare(strict_types=1);

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\EventoSignificativo;
use App\Models\Facturacion\Factura;
use App\Models\Facturacion\FacturaPaquete;
use App\Models\Facturacion\SiatCufd;
use App\Models\Facturacion\SiatCuis;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Services\Facturacion\SiatSoapService;
use App\Services\Facturacion\XmlFacturaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class EventoSignificativoController extends Controller
{
    public function __construct(
        private readonly SiatSoapService $siatSoapService,
        private readonly XmlFacturaService $xmlFacturaService
    ) {}

    /**
     * Listado paginado de eventos significativos (contingencias).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 10);
        $query = EventoSignificativo::with([
            'sucursal',
            'puntoVenta',
            'paquetes',
            'facturas:id,id_evento_significativo,numero_factura,fecha_emision,nombre_razon_social,numero_documento,monto_total,estado_factura',
        ]);

        if ($request->filled('id_sucursal')) {
            $idSucursal = (int) $request->input('id_sucursal');
            $query->whereHas('sucursal', function ($q) use ($idSucursal) {
                $q->where('codigo_sucursal', $idSucursal)->orWhere('id', $idSucursal);
            });
        }

        if ($request->filled('id_punto_venta')) {
            $idPv = (int) $request->input('id_punto_venta');
            $query->whereHas('puntoVenta', function ($q) use ($idPv) {
                $q->where('codigo_punto_venta', $idPv)->orWhere('id', $idPv);
            });
        }

        if ($request->filled('estado_evento')) {
            $query->where('estado_evento', strtoupper((string) $request->input('estado_evento')));
        }

        $paginator = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ], Response::HTTP_OK);
    }

    /**
     * Iniciar un Evento Significativo (Apertura de contingencia).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'codigo_evento' => 'required|integer', // 1=Corte energía, 2=Corte internet, etc.
            'descripcion' => 'required|string|max:255',
            'id_sucursal' => 'required|integer',
            'id_punto_venta' => 'nullable|integer',
            'fecha_inicio' => 'nullable|date',
            'cafc' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idSucursal = (int) $request->input('id_sucursal');
        $hasPv = $request->has('id_punto_venta') && $request->input('id_punto_venta') !== null;
        $idPuntoVenta = $hasPv ? (int) $request->input('id_punto_venta') : null;

        $sucursal = SiatSucursal::where('codigo_sucursal', $idSucursal)
            ->orWhere('id', $idSucursal)
            ->firstOrFail();

        $puntoVenta = null;
        if ($idPuntoVenta !== null) {
            $puntoVenta = SiatPuntoVenta::where('id_sucursal', $sucursal->id)
                ->where(function ($q) use ($idPuntoVenta) {
                    $q->where('codigo_punto_venta', $idPuntoVenta)
                      ->orWhere('id', $idPuntoVenta);
                })
                ->first();
        }

        // Obtener el CUFD activo al iniciar el evento (del punto de venta o de la sucursal)
        $cufd = SiatCufd::getVigente($sucursal->id, $puntoVenta?->id, $idPuntoVenta ?? 0);

        $fechaInicio = $request->filled('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))
            : Carbon::now();

        $evento = EventoSignificativo::create([
            'id_sucursal' => $sucursal->id,
            'id_punto_venta' => $puntoVenta ? $puntoVenta->id : null,
            'codigo_evento' => (int) $request->input('codigo_evento'),
            'descripcion' => $request->input('descripcion'),
            'cufd_evento' => $cufd ? $cufd->codigo : 'CUFD_EVENTO',
            'fecha_inicio' => $fechaInicio,
            'cafc' => $request->input('cafc'),
            'estado_evento' => 'INICIADO',
        ]);

        return response()->json([
            'success' => true,
            'data' => $evento->load(['sucursal', 'puntoVenta', 'facturas', 'paquetes']),
            'message' => 'Evento significativo iniciado. El sistema ha entrado en modo contingencia.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Cerrar contingencia y empaquetar facturas para envío masivo al SIN.
     */
    public function cerrar(int $id): JsonResponse
    {
        $evento = EventoSignificativo::with(['facturas', 'sucursal', 'puntoVenta'])->findOrFail($id);

        if ($evento->estado_evento === 'CERRADO') {
            return response()->json([
                'success' => false,
                'message' => 'El evento significativo ya fue cerrado.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $evento->fecha_fin = Carbon::now();
        $evento->estado_evento = 'CERRADO';
        $evento->save();

        $facturas = $evento->facturas;
        $cantidadFacturas = $facturas->count();

        // 1. Crear directorio temporal para compresión .tar.gz
        $tempDir = storage_path("app/siat/temp_paquete_{$evento->id}_" . time());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tarFile = "{$tempDir}/paquete.tar";
        $tarGzFile = "{$tempDir}/paquete.tar.gz";

        $phar = new \PharData($tarFile);

        if ($cantidadFacturas > 0) {
            foreach ($facturas as $factura) {
                if ($factura->xml_firmado_path && Storage::disk('local')->exists($factura->xml_firmado_path)) {
                    $xml = Storage::disk('local')->get($factura->xml_firmado_path);
                } else {
                    $xml = $this->xmlFacturaService->construirXml($factura);
                }
                $phar->addFromString("factura_{$factura->numero_factura}.xml", $xml);
            }
        } else {
            // Archivo de control para contingencia sin facturas
            $phar->addFromString("control_evento_{$evento->id}.txt", "Evento de contingencia cerrado sin facturas emitidas.");
        }

        $phar->compress(\Phar::GZ);
        unset($phar);

        $binarioTarGz = file_get_contents($tarGzFile);
        $hashArchivo = hash('sha256', $binarioTarGz);

        $destinoStorage = "siat/paquetes/paquete_evento_{$evento->id}.tar.gz";
        Storage::disk('local')->put($destinoStorage, $binarioTarGz);

        // Limpiar archivos temporales
        @unlink($tarFile);
        @unlink($tarGzFile);
        @rmdir($tempDir);

        // Obtener CUIS y CUFD vigentes
        $codigoPv = $evento->puntoVenta ? (int) $evento->puntoVenta->codigo_punto_venta : 0;
        $cuis = SiatCuis::getVigente($evento->id_sucursal, $evento->id_punto_venta, $codigoPv);
        $cufdObj = SiatCufd::getVigente($evento->id_sucursal, $evento->id_punto_venta, $codigoPv);
        $cufd = $cufdObj ? $cufdObj->codigo : ($evento->cufd_evento ?? 'CUFD_EVENTO');

        // 2. Registrar evento significativo en el SIN para obtener el código de recepción oficial
        $sucursal = $evento->sucursal ? (int) $evento->sucursal->codigo_sucursal : 0;
        $puntoVenta = $evento->puntoVenta ? (int) $evento->puntoVenta->codigo_punto_venta : 0;
        $cufdEvento = $evento->cufd_evento ?: $cufd;

        $respEventoSin = $this->siatSoapService->registrarEventoSignificativo(
            (int) $evento->codigo_evento,
            $evento->descripcion ?: 'Contingencia operativa',
            $evento->fecha_inicio,
            $evento->fecha_fin,
            $cufdEvento,
            $cuis,
            $cufd,
            $sucursal,
            $puntoVenta
        );

        $codigoEventoParaPaquete = !empty($respEventoSin['codigo_recepcion_evento'])
            ? (int) $respEventoSin['codigo_recepcion_evento']
            : (int) $evento->codigo_evento;

        if (!empty($respEventoSin['codigo_recepcion_evento'])) {
            $evento->update(['codigo_recepcion_evento' => $respEventoSin['codigo_recepcion_evento']]);
        }

        // 3. Enviar paquete al SIAT vía SOAP
        $respSiat = $this->siatSoapService->enviarPaqueteFacturas(
            $binarioTarGz,
            $hashArchivo,
            $cantidadFacturas,
            $codigoEventoParaPaquete,
            $cuis,
            $cufd,
            $sucursal,
            $puntoVenta,
            $evento->cafc
        );

        $paquete = FacturaPaquete::create([
            'id_evento_significativo' => $evento->id,
            'codigo_recepcion_paquete' => $respSiat['codigo_recepcion'] ?? ('PAQ_' . strtoupper(bin2hex(random_bytes(6)))),
            'cantidad_facturas' => $cantidadFacturas,
            'archivo_tar_gz_path' => $destinoStorage,
            'hash_archivo' => $hashArchivo,
            'estado_paquete' => $respSiat['codigo_estado'] ?? 'RECIBIDO',
            'observaciones' => is_string($respSiat['mensajes'] ?? null) ? $respSiat['mensajes'] : json_encode($respSiat['mensajes'] ?? 'Paquete procesado.'),
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR_PAQ',
            '_usuario_creacion' => 1,
        ]);

        return response()->json([
            'success' => true,
            'data' => $paquete,
            'message' => "Contingencia cerrada exitosamente. Se empaquetaron y enviaron {$cantidadFacturas} facturas al SIN.",
        ], Response::HTTP_OK);
    }

    /**
     * Validar estado de un paquete de contingencia ante el SIAT.
     */
    public function validarPaquete(int $paqueteId): JsonResponse
    {
        $paquete = FacturaPaquete::with('eventoSignificativo')->findOrFail($paqueteId);
        $evento = $paquete->eventoSignificativo;

        $codigoPv = $evento->puntoVenta ? (int) $evento->puntoVenta->codigo_punto_venta : 0;
        $cuis = SiatCuis::getVigente($evento->id_sucursal, $evento->id_punto_venta, $codigoPv);
        $cufdObj = SiatCufd::getVigente($evento->id_sucursal, $evento->id_punto_venta, $codigoPv);
        $cufd = $cufdObj ? $cufdObj->codigo : 'CUFD_DEFAULT';

        $sucursal = $evento->sucursal ? (int) $evento->sucursal->codigo_sucursal : 0;
        $puntoVenta = $evento->puntoVenta ? (int) $evento->puntoVenta->codigo_punto_venta : 0;

        $res = $this->siatSoapService->validarPaqueteFacturas(
            $paquete->codigo_recepcion_paquete,
            $cuis,
            $cufd,
            $sucursal,
            $puntoVenta
        );

        if ($res['success']) {
            $paquete->estado_paquete = $res['codigo_descripcion'] ?? 'VALIDADA';
            $paquete->save();
        }

        return response()->json([
            'success' => true,
            'data' => $paquete,
            'mensaje_siat' => $res['mensajes'] ?? $res['mensaje'] ?? 'Estado actualizado.',
        ], Response::HTTP_OK);
    }

    /**
     * Descargar el archivo .tar.gz oficial enviado al SIAT.
     */
    public function descargarPaquete(int $paqueteId): mixed
    {
        $paquete = FacturaPaquete::findOrFail($paqueteId);

        if (!$paquete->archivo_tar_gz_path || !Storage::disk('local')->exists($paquete->archivo_tar_gz_path)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo comprimido del paquete no existe en el almacenamiento del servidor.',
            ], Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('local')->download(
            $paquete->archivo_tar_gz_path,
            basename($paquete->archivo_tar_gz_path),
            ['Content-Type' => 'application/gzip']
        );
    }
}
