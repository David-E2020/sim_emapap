<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\ArchivoAdjunto;
use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\ParticipanteDocumento;
use App\Models\Correspondencia\PlantillaDocumento;
use App\Models\Rrhh\Persona;
use App\Services\Correspondencia\CaratulaPdfService;
use App\Services\Correspondencia\CiteGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentoController extends Controller
{
    protected CiteGeneratorService $citeService;
    protected CaratulaPdfService $caratulaService;

    public function __construct(CiteGeneratorService $citeService, CaratulaPdfService $caratulaService)
    {
        $this->citeService = $citeService;
        $this->caratulaService = $caratulaService;
    }

    /**
     * Listar documentos oficiales creados o en los que participa el usuario
     */
    public function index(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo_documento');
        $estado = $request->input('estado');
        $busqueda = trim((string)$request->input('q', ''));
        $idPersona = auth()->user()?->id_persona ?: (int)$request->input('id_persona', 1);

        $query = Documento::with([
            'creador',
            'unidadGeneradora',
            'plantilla',
            'participantes.persona',
            'participantes.puesto',
            'firmasAprobaciones.persona',
            'archivosAdjuntos',
        ])->where('_estado', 'ACTIVO');

        if ($tipo) {
            $query->where('tipo_documento', $tipo);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('cite', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('asunto', 'ILIKE', "%{$busqueda}%");
            });
        }

        // Filtrar documentos donde la persona es creador o participante
        $query->where(function ($q) use ($idPersona) {
            $q->where('id_creador', $idPersona)
                ->orWhereHas('participantes', function ($qp) use ($idPersona) {
                    $qp->where('id_persona', $idPersona);
                });
        });

        $documentos = $query->orderBy('id', 'desc')->paginate((int)$request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => $documentos->items(),
            'meta' => [
                'total' => $documentos->total(),
                'current_page' => $documentos->currentPage(),
                'last_page' => $documentos->lastPage(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Crear nuevo documento oficial con generación de CITE automático
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|string',
            'asunto' => 'required|string|max:500',
            'contenido_html' => 'required|string',
            'id_unidad_generadora' => 'required|integer',
            'participantes' => 'required|array|min:1',
            'participantes.*.id_persona' => 'required|integer',
            'participantes.*.tipo_participacion' => 'required|string|in:REMITENTE_DE,DESTINATARIO_A,VIA,CON_COPIA_A',
            'id_plantilla' => 'nullable|integer',
            'id_hoja_ruta' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idPersona = auth()->user()?->id_persona ?: 1;
        $idUnidad = (int)$request->input('id_unidad_generadora');
        $tipoDoc = (string)$request->input('tipo_documento');

        // Mapear sigla de plantilla
        $siglaPlantilla = 'DOC';
        if ($request->filled('id_plantilla')) {
            $plantilla = PlantillaDocumento::find($request->input('id_plantilla'));
            if ($plantilla) $siglaPlantilla = $plantilla->sigla;
        } else {
            $map = ['MEMORANDUM' => 'MEM', 'INFORME_TECNICO' => 'INF', 'NOTA_INTERNA' => 'NI', 'CIRCULAR' => 'CIR', 'CARTA_EXTERNA' => 'CAR', 'RESOLUCION' => 'RES'];
            $siglaPlantilla = $map[$tipoDoc] ?? 'DOC';
        }

        $cite = $this->citeService->generarCiteDocumento($idUnidad, $siglaPlantilla, (int)date('Y'));
        $token = $this->citeService->generarCodigoVerificacion();

        $documento = DB::transaction(function () use ($request, $cite, $token, $idPersona, $idUnidad, $tipoDoc) {
            $doc = Documento::create([
                'cite' => $cite,
                'codigo_verificacion' => $token,
                'tipo_documento' => $tipoDoc,
                'asunto' => strtoupper(trim((string)$request->input('asunto'))),
                'contenido_html' => $request->input('contenido_html'),
                'id_plantilla' => $request->input('id_plantilla'),
                'id_unidad_generadora' => $idUnidad,
                'id_creador' => $idPersona,
                'id_hoja_ruta' => $request->input('id_hoja_ruta'),
                'estado' => 'BORRADOR',
                '_usuario_creacion' => auth()->id() ?? 1,
                '_fecha_creacion' => now(),
            ]);

            // Crear participantes
            $participantes = $request->input('participantes', []);
            foreach ($participantes as $index => $part) {
                ParticipanteDocumento::create([
                    'id_documento' => $doc->id,
                    'id_persona' => $part['id_persona'],
                    'id_puesto' => $part['id_puesto'] ?? null,
                    'tipo_participacion' => $part['tipo_participacion'],
                    'orden_participacion' => $index + 1,
                    'cargo_snapshot' => $part['cargo_snapshot'] ?? null,
                    'bandeja' => 'BORRADOR',
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);
            }

            return $doc;
        });

        return response()->json([
            'success' => true,
            'message' => 'Documento redactado exitosamente.',
            'data' => $documento->fresh(['creador', 'unidadGeneradora', 'participantes.persona']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Ver documento con participantes y firmas
     */
    public function show(int $id): JsonResponse
    {
        $doc = Documento::with([
            'creador',
            'unidadGeneradora',
            'plantilla',
            'participantes.persona',
            'participantes.puesto',
            'firmasAprobaciones.persona',
            'archivosAdjuntos',
            'hojaRuta',
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $doc], Response::HTTP_OK);
    }

    /**
     * Previsualizar e imprimir documento oficial con firmas y QR
     */
    public function previewHtml(int $id): Response
    {
        $doc = Documento::with([
            'creador',
            'unidadGeneradora',
            'plantilla',
            'participantes.persona',
            'firmasAprobaciones.persona',
        ])->findOrFail($id);

        $html = $this->caratulaService->renderDocumentoHtml($doc);
        return response($html, Response::HTTP_OK)->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Subir archivo adjunto a un documento
     */
    public function adjuntarArchivo(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'archivo' => 'required|file|max:20480', // Max 20MB
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $documento = Documento::findOrFail($id);
        $file = $request->file('archivo');

        $path = $file->store("correspondencia/adjuntos/{$documento->id}", 'public');
        $sha256 = hash_file('sha256', $file->getRealPath());

        $adjunto = ArchivoAdjunto::create([
            'id_documento' => $documento->id,
            'nombre_original' => $file->getClientOriginalName(),
            'ruta_almacenamiento' => $path,
            'mime_type' => $file->getClientMimeType(),
            'tamanio_bytes' => $file->getSize(),
            'hash_sha256' => $sha256,
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        $documento->tamanio_archivos_adjuntos += $file->getSize();
        $documento->save();

        return response()->json([
            'success' => true,
            'message' => 'Archivo adjuntado exitosamente.',
            'data' => $adjunto,
        ], Response::HTTP_CREATED);
    }
}
