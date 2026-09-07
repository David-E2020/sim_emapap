<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Rrhh\Persona;
use App\Services\Correspondencia\CaratulaPdfService;
use App\Services\Correspondencia\CiteGeneratorService;
use App\Services\Correspondencia\DerivacionWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class HojaRutaController extends Controller
{
    public function __construct(
        protected readonly CiteGeneratorService $citeService,
        protected readonly DerivacionWorkflowService $workflowService,
        protected readonly CaratulaPdfService $caratulaService
    ) {}

    /**
     * Listar Hojas de Ruta con filtros por bandeja (ENTRADA, SALIDA, AGRUPADOS, ARCHIVADOS, TODOS) - LONDRA REVERSE ENGINEERED
     */
    public function index(Request $request): JsonResponse
    {
        $bandeja = strtoupper((string) $request->input('bandeja', 'ENTRADA'));
        $busqueda = trim((string) $request->input('q', ''));
        $prioridad = $request->input('prioridad');
        $user = auth()->user();
        $idPersona = $user?->usr_externo_id ?: ($user?->id_persona ?: ($request->filled('id_persona') ? (int) $request->input('id_persona') : (Persona::first()?->id ?? 1)));

        $query = HojaRuta::with([
            'unidadOrigen',
            'personaOrigen',
            'cargoOrigen',
            'documentos',
            'derivaciones.funcionarioOrigen',
            'derivaciones.funcionarioDestino',
            'derivaciones.unidadDestino',
            'agrupaciones.hojaRutaAnexada',
        ])->where('_estado', 'ACTIVO');

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nro_hoja_ruta', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('asunto', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('referencia', 'ILIKE', "%{$busqueda}%");
            });
        }

        if ($prioridad && $prioridad !== 'TODAS') {
            $query->where('prioridad', $prioridad);
        }

        if ($bandeja === 'ENTRADA') {
            $query->where('estado', '!=', 'CERRADO')
                ->where('estado', '!=', 'ANULADO')
                ->where('estado', '!=', 'AGRUPADO')
                ->whereHas('derivaciones', function ($q) use ($idPersona) {
                    $q->where('id_funcionario_destino', $idPersona)
                        ->where('_estado', 'ACTIVO')
                        ->whereIn('estado_derivacion', ['PENDIENTE_RECEPCION', 'RECIBIDO']);
                });
        } elseif ($bandeja === 'SALIDA') {
            $query->whereHas('derivaciones', function ($q) use ($idPersona) {
                $q->where('id_funcionario_origen', $idPersona)
                    ->where('_estado', 'ACTIVO');
            });
        } elseif ($bandeja === 'ARCHIVADOS') {
            $query->where('estado', 'CERRADO');
        } elseif ($bandeja === 'AGRUPADOS') {
            $query->where(function ($q) {
                $q->where('estado', 'AGRUPADO')->orWhereHas('agrupaciones', function ($qa) {
                    $qa->where('_estado', 'ACTIVO');
                });
            });
        }

        $hojasRuta = $query->orderBy('id', 'desc')->paginate((int) $request->input('per_page', 25));

        $hojasRuta->getCollection()->transform(function ($hr) use ($idPersona) {
            $ultimaDerivacion = $hr->derivaciones->where('_estado', 'ACTIVO')->sortByDesc('id')->first();
            $miDerivacion = $hr->derivaciones->where('id_funcionario_destino', $idPersona)->where('_estado', 'ACTIVO')->sortByDesc('id')->first();

            $hr->semaforo = $ultimaDerivacion ? $this->workflowService->calcularSemaforo($ultimaDerivacion) : null;
            $hr->mi_derivacion = $miDerivacion;
            $hr->acciones_permitidas = $this->workflowService->calcularAccionesPermitidas($hr, $idPersona, $miDerivacion);

            return $hr;
        });

        // Contadores para pestañas
        $totalEntrada = HojaRuta::where('_estado', 'ACTIVO')
            ->where('estado', '!=', 'CERRADO')
            ->where('estado', '!=', 'ANULADO')
            ->where('estado', '!=', 'AGRUPADO')
            ->whereHas('derivaciones', function ($q) use ($idPersona) {
                $q->where('id_funcionario_destino', $idPersona)
                    ->where('_estado', 'ACTIVO')
                    ->whereIn('estado_derivacion', ['PENDIENTE_RECEPCION', 'RECIBIDO']);
            })->count();

        $totalSalida = HojaRuta::where('_estado', 'ACTIVO')
            ->whereHas('derivaciones', function ($q) use ($idPersona) {
                $q->where('id_funcionario_origen', $idPersona)
                    ->where('_estado', 'ACTIVO');
            })->count();

        return response()->json([
            'success' => true,
            'data' => $hojasRuta->items(),
            'meta' => [
                'total' => $hojasRuta->total(),
                'current_page' => $hojasRuta->currentPage(),
                'last_page' => $hojasRuta->lastPage(),
                'total_entrada' => $totalEntrada,
                'total_salida' => $totalSalida,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Bandeja de Hojas de Ruta (Alias para endpoint de bandeja)
     */
    public function bandeja(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    /**
     * Consultar acciones permitidas en una Hoja de Ruta
     */
    public function accionesPermitidas(Request $request, int $id): JsonResponse
    {
        $hr = HojaRuta::with(['derivaciones'])->findOrFail($id);
        $idPersona = auth()->user()?->id_persona ?: (int) $request->input('id_persona', 1);
        $miDerivacion = $hr->derivaciones->where('id_funcionario_destino', $idPersona)->where('_estado', 'ACTIVO')->sortByDesc('id')->first();
        $acciones = $this->workflowService->calcularAccionesPermitidas($hr, $idPersona, $miDerivacion);

        return response()->json([
            'success' => true,
            'data' => [
                'acciones' => $acciones,
                'hoja_ruta' => $hr,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Crear una nueva Hoja de Ruta institucional (LONDRA: Creación y Derivación Inicial)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'asunto' => 'required|string|max:500',
            'prioridad' => 'nullable|string|in:URGENTE,ALTA,MEDIA,BAJA',
            'tipo_hr' => 'nullable|string|in:INTERNA,EXTERNA',
            'origen' => 'nullable|string|in:INTERNO,VENTANILLA_FISICA,VENTANILLA_DIGITAL',
            'nro_fojas' => 'nullable|integer|min:1',
            'nro_anexos' => 'nullable|integer|min:0',
            'id_unidad_origen' => 'nullable|integer',
            'id_persona_origen' => 'nullable|integer',
            'id_cargo_origen' => 'nullable|integer',
            'id_documento_principal' => 'nullable|integer',
            'destinatarios' => 'nullable|array',
            'proveido' => 'nullable|string',
            'instruccion_detalle' => 'nullable|string',
            'dias_plazo' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idPersonaOrigen = (int) $request->input('id_persona_origen', auth()->user()?->id_persona ?: 1);

        $cite = $this->citeService->generarCiteHojaRuta(
            $request->input('id_regional') ? (int) $request->input('id_regional') : null,
            (int) date('Y')
        );

        $hojaRuta = HojaRuta::create([
            'nro_hoja_ruta' => $cite,
            'gestion' => (int) date('Y'),
            'tipo_hr' => $request->input('tipo_hr', 'INTERNA'),
            'origen' => $request->input('origen', 'INTERNO'),
            'asunto' => strtoupper(trim((string) $request->input('asunto'))),
            'referencia' => $request->input('referencia'),
            'prioridad' => $request->input('prioridad', 'MEDIA'),
            'confidencial' => (bool) $request->input('confidencial', false),
            'nro_fojas' => (int) $request->input('nro_fojas', 1),
            'nro_anexos' => (int) $request->input('nro_anexos', 0),
            'id_unidad_origen' => $request->input('id_unidad_origen'),
            'id_persona_origen' => $idPersonaOrigen,
            'id_cargo_origen' => $request->input('id_cargo_origen'),
            'remitente_externo' => $request->input('remitente_externo'),
            'id_ventanilla_origen' => $request->input('id_ventanilla_origen'),
            'estado' => 'CREADO',
            'fecha_solicitud' => now(),
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        if ($request->filled('id_documento_principal')) {
            $doc = Documento::find($request->input('id_documento_principal'));
            if ($doc) {
                $doc->id_hoja_ruta = $hojaRuta->id;
                $doc->save();
                $hojaRuta->documentos()->attach($doc->id, [
                    'es_documento_principal' => true,
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);
            }
        }

        if ($request->filled('destinatarios') && is_array($request->input('destinatarios'))) {
            $this->workflowService->derivar([
                'id_hoja_ruta' => $hojaRuta->id,
                'id_documento_principal' => $request->input('id_documento_principal'),
                'id_unidad_origen' => $hojaRuta->id_unidad_origen,
                'id_funcionario_origen' => $hojaRuta->id_persona_origen,
                'id_cargo_origen' => $hojaRuta->id_cargo_origen,
                'proveido' => $request->input('proveido', 'PASE A SUS EFECTOS'),
                'instruccion_detalle' => $request->input('instruccion_detalle'),
                'dias_plazo' => (int) $request->input('dias_plazo', 2),
                'prioridad' => $hojaRuta->prioridad,
                'destinatarios' => $request->input('destinatarios'),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hoja de ruta creada exitosamente.',
            'data' => $hojaRuta->fresh(['unidadOrigen', 'personaOrigen', 'derivaciones', 'documentos']),
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $idPersona = auth()->user()?->id_persona ?: 1;
        $hojaRuta = HojaRuta::with([
            'unidadOrigen',
            'personaOrigen',
            'cargoOrigen',
            'entidadExterna',
            'documentos.firmasAprobaciones.persona',
            'derivaciones.funcionarioOrigen',
            'derivaciones.cargoOrigen',
            'derivaciones.unidadOrigen',
            'derivaciones.funcionarioDestino',
            'derivaciones.cargoDestino',
            'derivaciones.unidadDestino',
            'archivosAdjuntos',
            'agrupaciones.hojaRutaAnexada',
        ])->findOrFail($id);

        $miDerivacion = $hojaRuta->derivaciones->where('id_funcionario_destino', $idPersona)->where('_estado', 'ACTIVO')->sortByDesc('id')->first();
        $hojaRuta->acciones_permitidas = $this->workflowService->calcularAccionesPermitidas($hojaRuta, $idPersona, $miDerivacion);

        return response()->json([
            'success' => true,
            'data' => $hojaRuta,
        ], Response::HTTP_OK);
    }

    public function caratula(int $id): Response
    {
        $hojaRuta = HojaRuta::with([
            'unidadOrigen',
            'personaOrigen',
            'cargoOrigen',
            'derivaciones.funcionarioOrigen',
            'derivaciones.funcionarioDestino',
            'derivaciones.cargoDestino',
            'derivaciones.unidadDestino',
        ])->findOrFail($id);

        $html = $this->caratulaService->renderCaratulaHtml($hojaRuta);

        return response($html, Response::HTTP_OK)->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function cerrar(Request $request, int $id): JsonResponse
    {
        $idPersona = auth()->user()?->id_persona ?: 1;
        $motivo = (string) $request->input('motivo_cierre', 'Trámite finalizado y atendido a satisfacción.');
        $hojaRuta = $this->workflowService->cerrarHojaRuta($id, $idPersona, $motivo);

        return response()->json([
            'success' => true,
            'message' => 'Hoja de ruta concluida y archivada exitosamente.',
            'data' => $hojaRuta,
        ], Response::HTTP_OK);
    }

    public function reabrir(Request $request, int $id): JsonResponse
    {
        $idPersona = auth()->user()?->id_persona ?: 1;
        $motivo = (string) $request->input('motivo_reapertura', 'Reapertura de trámite para diligencias complementarias.');
        $hojaRuta = $this->workflowService->reabrirHojaRuta($id, $idPersona, $motivo);

        return response()->json([
            'success' => true,
            'message' => 'Hoja de ruta reabierta exitosamente.',
            'data' => $hojaRuta,
        ], Response::HTTP_OK);
    }

    public function agrupar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_hoja_ruta_principal' => 'required|integer|exists:App\Models\Correspondencia\HojaRuta,id',
            'id_hoja_ruta_anexada' => 'required|integer|exists:App\Models\Correspondencia\HojaRuta,id|different:id_hoja_ruta_principal',
            'motivo' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idPersona = auth()->user()?->id_persona ?: 1;
        $agrupacion = $this->workflowService->agrupar(
            (int) $request->input('id_hoja_ruta_principal'),
            (int) $request->input('id_hoja_ruta_anexada'),
            $idPersona,
            $request->input('motivo')
        );

        return response()->json([
            'success' => true,
            'message' => 'Expedientes agrupados exitosamente.',
            'data' => $agrupacion,
        ], Response::HTTP_CREATED);
    }

    public function desagrupar(Request $request, int $id): JsonResponse
    {
        $idPersona = auth()->user()?->id_persona ?: 1;
        $this->workflowService->desagrupar($id, $idPersona);

        return response()->json([
            'success' => true,
            'message' => 'Hoja de ruta desagrupada exitosamente.',
        ], Response::HTTP_OK);
    }
}
