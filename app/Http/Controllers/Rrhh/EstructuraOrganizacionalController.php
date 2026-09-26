<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\AsignacionPuesto;
use App\Models\Rrhh\EscalaSalarial;
use App\Models\Rrhh\Gestion;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\Regional;
use App\Models\Rrhh\UnidadOrganizacional;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'padreId' => 'nullable|integer|exists:pgsql.rrhh.unidades_organizacionales,id',
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
                'nombre' => strtoupper(trim((string) $request->input('nombre'))),
                'sigla' => $request->input('sigla') ? strtoupper(trim((string) $request->input('sigla'))) : null,
                'padreId' => $request->input('padreId'),
                'es_unidad_recursos_humanos' => (bool) $request->input('es_unidad_recursos_humanos', false),
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

    public function updateUnidad(Request $request, int $id): JsonResponse
    {
        $unidad = UnidadOrganizacional::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'sigla' => 'nullable|string|max:20',
            'padreId' => [
                'nullable',
                'integer',
                'exists:pgsql.rrhh.unidades_organizacionales,id',
                function ($attribute, $value, $fail) use ($id) {
                    if ((int) $value === (int) $id) {
                        $fail('Una unidad no puede depender de sí misma.');
                    }
                },
            ],
            'es_unidad_recursos_humanos' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $oldValues = $unidad->toArray();
            $unidad->update([
                'nombre' => strtoupper(trim((string) $request->input('nombre'))),
                'sigla' => $request->input('sigla') ? strtoupper(trim((string) $request->input('sigla'))) : null,
                'padreId' => $request->input('padreId'),
                'es_unidad_recursos_humanos' => (bool) $request->input('es_unidad_recursos_humanos', false),
                '_usuario_modificacion' => auth()->id() ?? 1,
                '_fecha_modificacion' => now(),
            ]);

            $this->auditService->log(
                event: 'unidad_organizacional_updated',
                model: $unidad,
                oldValues: $oldValues,
                newValues: $unidad->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Unidad Organizacional actualizada exitosamente.',
                'data' => $unidad,
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al actualizar unidad', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error interno al actualizar la unidad.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroyUnidad(int $id): JsonResponse
    {
        $unidad = UnidadOrganizacional::findOrFail($id);

        $tieneHijos = UnidadOrganizacional::where('padreId', $id)
            ->where('_estado', 'ACTIVO')
            ->exists();
        if ($tieneHijos) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la unidad porque tiene sub-unidades dependientes activas. Elimínelas o reasígnelas primero.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tienePuestos = Puesto::where('id_unidad_organizacional', $id)
            ->where('_estado', 'ACTIVO')
            ->exists();
        if ($tienePuestos) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la unidad porque tiene puestos de trabajo registrados. Elimínelos o reasígnelos primero.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $unidad->delete();

            $this->auditService->log(
                event: 'unidad_organizacional_deleted',
                model: $unidad,
                oldValues: $unidad->toArray()
            );

            return response()->json([
                'success' => true,
                'message' => 'Unidad Organizacional eliminada exitosamente.',
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            $unidad->update([
                '_estado' => 'INACTIVO',
                '_usuario_modificacion' => auth()->id() ?? 1,
                '_fecha_modificacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Unidad Organizacional dada de baja exitosamente.',
            ], Response::HTTP_OK);
        }
    }

    public function storePuesto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'tipo_puesto' => 'nullable|string|max:50',
            'id_unidad_organizacional' => 'required|integer|exists:pgsql.rrhh.unidades_organizacionales,id',
            'id_escala_salarial' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $puesto = Puesto::create([
                'nombre' => strtoupper(trim((string) $request->input('nombre'))),
                'tipo_puesto' => $request->input('tipo_puesto', 'PLANTA'),
                'id_unidad_organizacional' => (int) $request->input('id_unidad_organizacional'),
                'id_escala_salarial' => $request->input('id_escala_salarial') ? (int) $request->input('id_escala_salarial') : null,
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

    public function updatePuesto(Request $request, int $id): JsonResponse
    {
        $puesto = Puesto::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'tipo_puesto' => 'nullable|string|max:50',
            'id_unidad_organizacional' => 'required|integer|exists:pgsql.rrhh.unidades_organizacionales,id',
            'id_escala_salarial' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $puesto->update([
                'nombre' => strtoupper(trim((string) $request->input('nombre'))),
                'tipo_puesto' => $request->input('tipo_puesto', $puesto->tipo_puesto ?? 'PLANTA'),
                'id_unidad_organizacional' => (int) $request->input('id_unidad_organizacional'),
                'id_escala_salarial' => $request->input('id_escala_salarial') ? (int) $request->input('id_escala_salarial') : $puesto->id_escala_salarial,
                '_usuario_modificacion' => auth()->id() ?? 1,
                '_fecha_modificacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Puesto actualizado exitosamente.',
                'data' => $puesto,
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al actualizar puesto', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error interno al actualizar puesto.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroyPuesto(int $id): JsonResponse
    {
        $puesto = Puesto::findOrFail($id);

        $tieneAsignacion = AsignacionPuesto::where('id_puesto', $id)
            ->where('_estado', 'ACTIVO')
            ->whereNull('fecha_fin')
            ->exists();

        if ($tieneAsignacion) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el puesto porque tiene un funcionario actualmente asignado. Desasígnelo primero.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $puesto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Puesto eliminado exitosamente.',
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            $puesto->update([
                '_estado' => 'INACTIVO',
                '_usuario_modificacion' => auth()->id() ?? 1,
                '_fecha_modificacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Puesto dado de baja exitosamente.',
            ], Response::HTTP_OK);
        }
    }

    public function asignarPuesto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_puesto' => 'required|integer|exists:pgsql.rrhh.puestos,id',
            'id_persona' => 'required|integer|exists:pgsql.rrhh.personas,id',
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
            // Desasignar cualquier asignación activa previa para este puesto
            AsignacionPuesto::where('id_puesto', (int) $request->input('id_puesto'))
                ->where('_estado', 'ACTIVO')
                ->whereNull('fecha_fin')
                ->update([
                    'fecha_fin' => now()->toDateString(),
                    '_estado' => 'INACTIVO',
                    '_usuario_modificacion' => auth()->id() ?? 1,
                    '_fecha_modificacion' => now(),
                ]);

            $asignacion = AsignacionPuesto::create([
                'id_puesto' => (int) $request->input('id_puesto'),
                'id_persona' => (int) $request->input('id_persona'),
                'nro_item' => (int) $request->input('nro_item'),
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

    public function desasignarPuesto(int $id): JsonResponse
    {
        try {
            $asignacion = AsignacionPuesto::findOrFail($id);
            $asignacion->update([
                'fecha_fin' => now()->toDateString(),
                '_estado' => 'INACTIVO',
                '_usuario_modificacion' => auth()->id() ?? 1,
                '_fecha_modificacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Funcionario desasignado del puesto exitosamente.',
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al desasignar puesto', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al desasignar puesto.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function listarEscalasSalariales(): JsonResponse
    {
        $escalas = EscalaSalarial::where('_estado', 'ACTIVO')->orderBy('salario', 'desc')->get();

        return response()->json(['success' => true, 'data' => $escalas], Response::HTTP_OK);
    }

    public function storeEscalaSalarial(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'salario' => 'nullable|numeric',
            'salario_mensual' => 'nullable|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $salario = (float) ($request->input('salario') ?? $request->input('salario_mensual') ?? 0);

        $escala = EscalaSalarial::create([
            'nombre' => strtoupper(trim((string) $request->input('nombre'))),
            'salario' => $salario,
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Escala salarial registrada.', 'data' => $escala], Response::HTTP_CREATED);
    }

    public function listarRegionales(): JsonResponse
    {
        $regionales = Regional::where('_estado', 'ACTIVO')->orderBy('id')->get();

        return response()->json(['success' => true, 'data' => $regionales], Response::HTTP_OK);
    }

    public function storeRegional(Request $request): JsonResponse
    {
        $reg = Regional::create([
            'nombre' => strtoupper(trim((string) $request->input('nombre'))),
            'sigla' => $request->input('sigla') ? strtoupper(trim((string) $request->input('sigla'))) : null,
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Regional registrada.', 'data' => $reg], Response::HTTP_CREATED);
    }

    public function listarGestiones(): JsonResponse
    {
        $gestiones = Gestion::where('_estado', 'ACTIVO')->orderBy('anio', 'desc')->get();

        return response()->json(['success' => true, 'data' => $gestiones], Response::HTTP_OK);
    }

    public function storeGestion(Request $request): JsonResponse
    {
        $anio = (int) $request->input('anio', date('Y'));
        $g = Gestion::create([
            'nombre' => $request->input('nombre') ?: ('Gestión '.$anio),
            'anio' => $anio,
            'fecha_inicio' => "{$anio}-01-01",
            'fecha_fin' => "{$anio}-12-31",
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Gestión registrada.', 'data' => $g], Response::HTTP_CREATED);
    }
}
