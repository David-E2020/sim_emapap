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
use App\Services\Correspondencia\FirmaDigitalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class DocumentoController extends Controller
{
    public function __construct(
        protected readonly CiteGeneratorService $citeService,
        protected readonly CaratulaPdfService $caratulaService,
        protected readonly FirmaDigitalService $firmaService
    ) {}

    /**
     * Listar documentos oficiales creados o en los que participa el usuario (LONDRA: Bandejas filtradas)
     */
    public function index(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo_documento');
        $estado = $request->input('estado');
        $bandeja = strtoupper((string) $request->input('bandeja', 'TODOS'));
        $busqueda = trim((string) $request->input('q', ''));
        $user = auth()->user();
        $idPersona = $user?->usr_externo_id ?: ($user?->id_persona ?: ($request->filled('id_persona') ? (int) $request->input('id_persona') : (Persona::first()?->id ?? 1)));

        $query = Documento::with([
            'creador',
            'unidadGeneradora',
            'plantilla',
            'participantes.persona',
            'participantes.puesto',
            'firmasAprobaciones.persona',
            'archivosAdjuntos',
        ])->where('_estado', 'ACTIVO');

        if ($tipo && $tipo !== 'TODOS') {
            $query->where('tipo_documento', $tipo);
        }

        if ($estado && $estado !== 'TODOS') {
            $query->where('estado', $estado);
        }

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('cite', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('asunto', 'ILIKE', "%{$busqueda}%");
            });
        }

        // Filtro por Bandejas estilo Londra
        if ($bandeja === 'MIS_DOCUMENTOS') {
            $query->where('id_creador', $idPersona);
        } elseif ($bandeja === 'EN_REVISION') {
            $query->where('estado', 'EN_REVISION')
                ->whereHas('participantes', function ($q) use ($idPersona) {
                    $q->where('id_persona', $idPersona);
                });
        } elseif ($bandeja === 'OBSERVADOS') {
            $query->where('estado', 'OBSERVADO')
                ->where('id_creador', $idPersona);
        } elseif ($bandeja === 'FIRMADOS') {
            $query->where('estado', 'FIRMADO')
                ->where(function ($q) use ($idPersona) {
                    $q->where('id_creador', $idPersona)
                        ->orWhereHas('participantes', function ($qp) use ($idPersona) {
                            $qp->where('id_persona', $idPersona);
                        });
                });
        } else {
            // Filtrar documentos donde la persona es creador o participante
            $query->where(function ($q) use ($idPersona) {
                $q->where('id_creador', $idPersona)
                    ->orWhereHas('participantes', function ($qp) use ($idPersona) {
                        $qp->where('id_persona', $idPersona);
                    });
            });
        }

        $documentos = $query->orderBy('id', 'desc')->paginate((int) $request->input('per_page', 25));

        $documentos->getCollection()->transform(function ($doc) use ($idPersona) {
            $doc->acciones_permitidas = $this->firmaService->calcularAccionesDocumento($doc, $idPersona);

            return $doc;
        });

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
     * Bandeja de documentos estilo Londra
     */
    public function bandeja(Request $request): JsonResponse
    {
        return $this->index($request);
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
            'enviar_a_revision' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idPersona = auth()->user()?->id_persona ?: 1;
        $idUnidad = (int) $request->input('id_unidad_generadora');
        $tipoDoc = (string) $request->input('tipo_documento');

        // Mapear sigla de plantilla
        $siglaPlantilla = 'DOC';
        if ($request->filled('id_plantilla')) {
            $plantilla = PlantillaDocumento::find($request->input('id_plantilla'));
            if ($plantilla) {
                $siglaPlantilla = $plantilla->sigla;
            }
        } else {
            $map = [
                'MEMORANDUM' => 'MEM',
                'INFORME_TECNICO' => 'INF',
                'NOTA_INTERNA' => 'NI',
                'CIRCULAR' => 'CIR',
                'CARTA_EXTERNA' => 'CAR',
                'RESOLUCION' => 'RES',
            ];
            $siglaPlantilla = $map[$tipoDoc] ?? 'DOC';
        }

        $cite = $this->citeService->generarCiteDocumento($idUnidad, $siglaPlantilla, (int) date('Y'));
        $token = $this->citeService->generarCodigoVerificacion();
        $estadoInicial = $request->input('enviar_a_revision') ? 'EN_REVISION' : 'BORRADOR';

        $documento = DB::transaction(function () use ($request, $cite, $token, $idPersona, $idUnidad, $tipoDoc, $estadoInicial) {
            $doc = Documento::create([
                'cite' => $cite,
                'codigo_verificacion' => $token,
                'tipo_documento' => $tipoDoc,
                'asunto' => strtoupper(trim((string) $request->input('asunto'))),
                'contenido_html' => $request->input('contenido_html'),
                'id_plantilla' => $request->input('id_plantilla'),
                'id_unidad_generadora' => $idUnidad,
                'id_creador' => $idPersona,
                'id_hoja_ruta' => $request->input('id_hoja_ruta'),
                'estado' => $estadoInicial,
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
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
                    'bandeja' => $estadoInicial,
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
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
     * Actualizar documento oficial en borrador u observado
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $documento = Documento::findOrFail($id);

        if (! in_array($documento->estado, ['BORRADOR', 'OBSERVADO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar documentos en estado BORRADOR u OBSERVADO.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validator = Validator::make($request->all(), [
            'asunto' => 'nullable|string|max:500',
            'contenido_html' => 'nullable|string',
            'enviar_a_revision' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->filled('asunto')) {
            $documento->asunto = strtoupper(trim((string) $request->input('asunto')));
        }
        if ($request->filled('contenido_html')) {
            $documento->contenido_html = $request->input('contenido_html');
        }

        if ($request->input('enviar_a_revision')) {
            $documento->estado = 'EN_REVISION';
        }

        $documento->_usuario_modificacion = auth()->id() ?? 1;
        $documento->_fecha_modificacion = now();
        $documento->save();

        return response()->json([
            'success' => true,
            'message' => 'Documento actualizado exitosamente.',
            'data' => $documento->fresh(['creador', 'unidadGeneradora', 'participantes.persona']),
        ], Response::HTTP_OK);
    }

    /**
     * Enviar documento borrador a revisión
     */
    public function enviarRevision(Request $request, int $id): JsonResponse
    {
        $documento = Documento::findOrFail($id);

        if (! in_array($documento->estado, ['BORRADOR', 'OBSERVADO'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden enviar a revisión documentos en BORRADOR u OBSERVADO.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $documento->estado = 'EN_REVISION';
        $documento->_usuario_modificacion = auth()->id() ?? 1;
        $documento->_fecha_modificacion = now();
        $documento->save();

        return response()->json([
            'success' => true,
            'message' => 'Documento enviado a revisión exitosamente.',
            'data' => $documento,
        ], Response::HTTP_OK);
    }

    /**
     * Anular documento oficial
     */
    public function anular(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'motivo' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idPersona = auth()->user()?->id_persona ?: 1;
        $doc = $this->firmaService->anularDocumento($id, $idPersona, (string) $request->input('motivo'));

        return response()->json([
            'success' => true,
            'message' => 'Documento anulado exitosamente.',
            'data' => $doc,
        ], Response::HTTP_OK);
    }

    /**
     * Eliminar documento en borrador
     */
    public function destroy(int $id): JsonResponse
    {
        $documento = Documento::findOrFail($id);

        if ($documento->estado !== 'BORRADOR') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar documentos en estado BORRADOR.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $documento->_estado = 'INACTIVO';
        $documento->save();

        return response()->json([
            'success' => true,
            'message' => 'Documento eliminado exitosamente.',
        ], Response::HTTP_OK);
    }

    /**
     * Ver documento con participantes y firmas
     */
    public function show(int $id): JsonResponse
    {
        $idPersona = auth()->user()?->id_persona ?: 1;
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

        $doc->acciones_permitidas = $this->firmaService->calcularAccionesDocumento($doc, $idPersona);

        return response()->json([
            'success' => true,
            'data' => $doc,
        ], Response::HTTP_OK);
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
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
