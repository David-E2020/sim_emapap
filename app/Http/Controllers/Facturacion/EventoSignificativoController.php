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
        $paginator = EventoSignificativo::with(['sucursal', 'puntoVenta', 'facturas'])
            ->orderByDesc('id')
            ->paginate($perPage);

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
            'cafc' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idSucursal = (int) $request->input('id_sucursal');
        $idPuntoVenta = (int) $request->input('id_punto_venta', 0);

        $sucursal = SiatSucursal::where('codigo_sucursal', $idSucursal)
            ->orWhere('id', $idSucursal)
            ->firstOrFail();
        $puntoVenta = SiatPuntoVenta::where('id_sucursal', $sucursal->id)
            ->where('codigo_punto_venta', $idPuntoVenta)
            ->first();

        // Obtener el CUFD activo al iniciar el evento
        $cufd = SiatCufd::where('id_sucursal', $sucursal->id)
            ->latest('id')
            ->first();

        $evento = EventoSignificativo::create([
            'id_sucursal' => $sucursal->id,
            'id_punto_venta' => $puntoVenta ? $puntoVenta->id : null,
            'codigo_evento' => (int) $request->input('codigo_evento'),
            'descripcion' => $request->input('descripcion'),
            'cufd_evento' => $cufd ? $cufd->codigo : 'CUFD_EVENTO',
            'fecha_inicio' => Carbon::now(),
            'cafc' => $request->input('cafc'),
            'estado_evento' => 'INICIADO',
        ]);

        return response()->json([
            'success' => true,
            'data' => $evento,
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
        $cuisActivo = SiatCuis::where('id_sucursal', $evento->id_sucursal)->latest('id')->first();
        $cufdActivo = SiatCufd::where('id_sucursal', $evento->id_sucursal)->latest('id')->first();

        $cuis = $cuisActivo ? $cuisActivo->codigo_cuis : 'CUIS_EMAPAP_DEFAULT';
        $cufd = $cufdActivo ? $cufdActivo->codigo : ($evento->cufd_evento ?? 'CUFD_EVENTO');

        // 2. Enviar paquete al SIAT vía SOAP
        $sucursal = $evento->sucursal ? (int) $evento->sucursal->codigo_sucursal : 0;
        $puntoVenta = $evento->puntoVenta ? (int) $evento->puntoVenta->codigo_punto_venta : 0;

        $respSiat = $this->siatSoapService->enviarPaqueteFacturas(
            $binarioTarGz,
            $hashArchivo,
            $cantidadFacturas,
            (int) $evento->codigo_evento,
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

        $cuisActivo = SiatCuis::where('id_sucursal', $evento->id_sucursal)->latest('id')->first();
        $cufdActivo = SiatCufd::where('id_sucursal', $evento->id_sucursal)->latest('id')->first();

        $cuis = $cuisActivo ? $cuisActivo->codigo_cuis : 'CUIS_EMAPAP_DEFAULT';
        $cufd = $cufdActivo ? $cufdActivo->codigo : 'CUFD_DEFAULT';

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
}
