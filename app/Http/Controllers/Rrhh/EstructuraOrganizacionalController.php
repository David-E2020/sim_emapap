<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\AsignacionPuesto;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\UnidadOrganizacional;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class EstructuraOrganizacionalController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    public function organigrama(): JsonResponse
    {
        $unidades = UnidadOrganizacional::with([
            'puestos.asignaciones.persona',
            'dependencias',
        ])
        ->whereNull('padreId')
        ->where('_estado', 'ACTIVO')
        ->orderBy('id')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $unidades,
        ], Response::HTTP_OK);
    }

    public function storeUnidad(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'sigla' => 'nullable|string|max:20',
            'padreId' => 'nullable|integer|exists:rrhh.unidades_organizacionales,id',
            'es_unidad_recursos_humanos' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $unidad = UnidadOrganizacional::create([
                'nombre' => strtoupper(trim((string)$request->input('nombre'))),
                'sigla' => $request->input('sigla') ? strtoupper(trim((string)$request->input('sigla'))) : null,
                'padreId' => $request->input('padreId'),
                'es_unidad_recursos_humanos' => (bool)$request->input('es_unidad_recursos_humanos', false),
                '_usuario_creacion' => auth()->id() ?? 1,
                '_fecha_creacion' => now(),
            ]);

            $this->auditService->log(
                event: 'unidad_organizacional_created',
                model: $unidad,
                newValues: $unidad->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Unidad Organizacional creada exitosamente.',
                'data' => $unidad,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al crear unidad', ['exception' => $ex->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error interno al crear unidad.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function storePuesto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'tipo_puesto' => 'nullable|string|max:50',
            'id_unidad_organizacional' => 'required|integer|exists:rrhh.unidades_organizacionales,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $puesto = Puesto::create([
                'nombre' => strtoupper(trim((string)$request->input('nombre'))),
                'tipo_puesto' => $request->input('tipo_puesto', 'PLANTA'),
                'id_unidad_organizacional' => (int)$request->input('id_unidad_organizacional'),
                '_usuario_creacion' => auth()->id() ?? 1,
                '_fecha_creacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Puesto creado exitosamente.',
                'data' => $puesto,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al crear puesto', ['exception' => $ex->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error interno al crear puesto.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function asignarPuesto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_puesto' => 'required|integer|exists:rrhh.puestos,id',
            'id_persona' => 'required|integer|exists:rrhh.personas,id',
            'nro_item' => 'required|integer',
            'fecha_inicio' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $asignacion = AsignacionPuesto::create([
                'id_puesto' => (int)$request->input('id_puesto'),
                'id_persona' => (int)$request->input('id_persona'),
                'nro_item' => (int)$request->input('nro_item'),
                'fecha_inicio' => $request->input('fecha_inicio'),
                'tipo_asignacion' => 'ITEM',
                '_usuario_creacion' => auth()->id() ?? 1,
                '_fecha_creacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Puesto asignado exitosamente al funcionario.',
                'data' => $asignacion,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al asignar puesto', ['exception' => $ex->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al asignar puesto.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function listarEscalasSalariales(): JsonResponse
    {
        $escalas = \App\Models\Rrhh\EscalaSalarial::where('_estado', 'ACTIVO')->orderBy('salario', 'desc')->get();
        return response()->json(['success' => true, 'data' => $escalas], Response::HTTP_OK);
    }

    public function storeEscalaSalarial(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'salario' => 'nullable|numeric',
            'salario_mensual' => 'nullable|numeric',
        ]);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);

        $salario = (float)($request->input('salario') ?? $request->input('salario_mensual') ?? 0);

        $escala = \App\Models\Rrhh\EscalaSalarial::create([
            'nombre' => strtoupper(trim((string)$request->input('nombre'))),
            'salario' => $salario,
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Escala salarial registrada.', 'data' => $escala], Response::HTTP_CREATED);
    }

    public function listarRegionales(): JsonResponse
    {
        $regionales = \App\Models\Rrhh\Regional::where('_estado', 'ACTIVO')->orderBy('id')->get();
        return response()->json(['success' => true, 'data' => $regionales], Response::HTTP_OK);
    }

    public function storeRegional(Request $request): JsonResponse
    {
        $reg = \App\Models\Rrhh\Regional::create([
            'nombre' => strtoupper(trim((string)$request->input('nombre'))),
            'sigla' => $request->input('sigla') ? strtoupper(trim((string)$request->input('sigla'))) : null,
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Regional registrada.', 'data' => $reg], Response::HTTP_CREATED);
    }

    public function listarGestiones(): JsonResponse
    {
        $gestiones = \App\Models\Rrhh\Gestion::where('_estado', 'ACTIVO')->orderBy('anio', 'desc')->get();
        return response()->json(['success' => true, 'data' => $gestiones], Response::HTTP_OK);
    }

    public function storeGestion(Request $request): JsonResponse
    {
        $anio = (int)$request->input('anio', date('Y'));
        $g = \App\Models\Rrhh\Gestion::create([
            'nombre' => $request->input('nombre') ?: ('Gestión ' . $anio),
            'anio' => $anio,
            'fecha_inicio' => "{$anio}-01-01",
            'fecha_fin' => "{$anio}-12-31",
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Gestión registrada.', 'data' => $g], Response::HTTP_CREATED);
    }
}
