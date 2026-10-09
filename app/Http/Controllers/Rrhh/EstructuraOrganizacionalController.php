<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\AsignacionPuesto;
use App\Models\Rrhh\DatoLaboral;
use App\Models\Rrhh\EscalaSalarial;
use App\Models\Rrhh\FichaPersonal;
use App\Models\Rrhh\Gestion;
use App\Models\Rrhh\Nivel;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\Regional;
use App\Models\Rrhh\UnidadOrganizacional;
use App\Models\User;
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
            'puestos.escalaSalarial.nivel',
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
            'nro_documento' => 'nullable|string|max:100',
            'fecha_documento' => 'nullable|date',
            'tipo_movimiento' => 'nullable|string|max:50',
            'permitir_transferencia' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $asignacion = DB::transaction(function () use ($request) {
                $idPuesto = (int) $request->input('id_puesto');
                $idPersona = (int) $request->input('id_persona');
                $nroItem = (int) $request->input('nro_item');
                $fechaInicio = $request->input('fecha_inicio');
                $puesto = Puesto::with('unidadOrganizacional')->findOrFail($idPuesto);
                $persona = Persona::findOrFail($idPersona);

                // 1. Verificar si este mismo funcionario ya ocupa este mismo puesto
                $asignacionMismoPuesto = AsignacionPuesto::where('id_puesto', $idPuesto)
                    ->where('id_persona', $idPersona)
                    ->where('_estado', 'ACTIVO')
                    ->whereNull('fecha_fin')
                    ->first();

                if ($asignacionMismoPuesto) {
                    throw new \RuntimeException('El funcionario ya se encuentra asignado a este puesto.');
                }

                // 2. Verificar si el puesto ya tenía otro funcionario asignado (cerrar la asignación anterior del puesto)
                $asignacionAnteriorPuesto = AsignacionPuesto::where('id_puesto', $idPuesto)
                    ->where('_estado', 'ACTIVO')
                    ->whereNull('fecha_fin')
                    ->first();

                if ($asignacionAnteriorPuesto) {
                    $asignacionAnteriorPuesto->update([
                        'fecha_fin' => $fechaInicio,
                        '_estado' => 'FINALIZADO',
                        'asignacion' => 'REEMPLAZADO',
                        '_usuario_modificacion' => auth()->id() ?? 1,
                        '_fecha_modificacion' => now(),
                    ]);

                    // Cerrar dato laboral del funcionario que ocupaba el puesto
                    $fichaAnt = FichaPersonal::where('id_persona', $asignacionAnteriorPuesto->id_persona)->first();
                    if ($fichaAnt) {
                        DatoLaboral::where('id_ficha_personal', $fichaAnt->id)
                            ->where('cargo', $puesto->nombre)
                            ->where('es_puesto_anterior', false)
                            ->update([
                                'fecha_desvinculacion' => $fechaInicio,
                                'es_puesto_anterior' => true,
                                'tipo_movimiento' => 'DESASIGNACION',
                                '_estado' => 'FINALIZADO',
                                '_usuario_modificacion' => auth()->id() ?? 1,
                                '_fecha_modificacion' => now(),
                            ]);
                    }
                }

                // 3. Verificar si el funcionario seleccionado ya tenía OTRO cargo activo (Transferencia / Promoción)
                $asignacionPreviaPersona = AsignacionPuesto::with('puesto.unidadOrganizacional')
                    ->where('id_persona', $idPersona)
                    ->where('_estado', 'ACTIVO')
                    ->whereNull('fecha_fin')
                    ->first();

                $tipoMovimiento = 'DESIGNACION';

                if ($asignacionPreviaPersona) {
                    $tipoMovimiento = $request->input('tipo_movimiento') ?: 'TRANSFERENCIA';

                    // Finalizar su asignación anterior
                    $asignacionPreviaPersona->update([
                        'fecha_fin' => $fechaInicio,
                        '_estado' => 'FINALIZADO',
                        'asignacion' => $tipoMovimiento,
                        '_usuario_modificacion' => auth()->id() ?? 1,
                        '_fecha_modificacion' => now(),
                    ]);

                    // Actualizar el historial laboral previo en su ficha personal
                    $ficha = FichaPersonal::firstOrCreate(['id_persona' => $idPersona]);
                    DatoLaboral::where('id_ficha_personal', $ficha->id)
                        ->where('es_puesto_anterior', false)
                        ->update([
                            'fecha_desvinculacion' => $fechaInicio,
                            'es_puesto_anterior' => true,
                            'tipo_movimiento' => $tipoMovimiento,
                            '_estado' => 'FINALIZADO',
                            '_usuario_modificacion' => auth()->id() ?? 1,
                            '_fecha_modificacion' => now(),
                        ]);
                }

                // 4. Crear la nueva asignación activa en el puesto
                $nuevaAsignacion = AsignacionPuesto::create([
                    'id_puesto' => $idPuesto,
                    'id_persona' => $idPersona,
                    'nro_item' => $nroItem,
                    'fecha_inicio' => $fechaInicio,
                    'tipo_asignacion' => 'ITEM',
                    'asignacion' => $tipoMovimiento,
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                // 5. Registrar en el Legajo / Historial Laboral (rrhh.datos_laborales)
                $ficha = FichaPersonal::firstOrCreate(['id_persona' => $idPersona]);
                DatoLaboral::create([
                    'id_ficha_personal' => $ficha->id,
                    'cargo' => $puesto->nombre,
                    'unidad_organizacional' => $puesto->unidadOrganizacional?->nombre ?? '',
                    'tipo_funcionario' => $puesto->tipo_puesto ?? 'PLANTA',
                    'nro_item' => $nroItem,
                    'fecha_ingreso' => $fechaInicio,
                    'tipo_movimiento' => $tipoMovimiento,
                    'nro_documento' => $request->input('nro_documento'),
                    'fecha_documento' => $request->input('fecha_documento') ?: $fechaInicio,
                    'es_puesto_anterior' => false,
                    '_estado' => 'ACTIVO',
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                // 6. Si el usuario del ERP estaba inactivo, reactivarlo
                User::where('usr_externo_id', $idPersona)->where('usr_estado', '!=', 'A')->update(['usr_estado' => 'A']);

                $this->auditService->log(
                    event: 'puesto_asignado',
                    model: $nuevaAsignacion,
                    newValues: [
                        'funcionario' => $persona->nombre_completo,
                        'puesto' => $puesto->nombre,
                        'item' => $nroItem,
                        'tipo_movimiento' => $tipoMovimiento,
                    ]
                );

                return $nuevaAsignacion;
            });

            return response()->json([
                'success' => true,
                'message' => 'Puesto asignado exitosamente al funcionario y registrado en su historial laboral.',
                'data' => $asignacion,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al asignar puesto', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => $ex->getMessage() ?: 'Error al asignar puesto.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function desasignarPuesto(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'motivo' => 'nullable|string|in:RENUNCIA,DESTITUCION,DESPIDO,CONCLUSION_CONTRATO,JUBILACION,TRANSFERENCIA,DESASIGNACION',
            'fecha_desvinculacion' => 'nullable|date',
            'nro_documento' => 'nullable|string|max:100',
            'observacion' => 'nullable|string|max:255',
            'desactivar_acceso_erp' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::transaction(function () use ($request, $id) {
                $asignacion = AsignacionPuesto::with(['puesto.unidadOrganizacional', 'persona'])->findOrFail($id);
                $motivo = $request->input('motivo') ?: 'DESASIGNACION';
                $fechaDesvinculacion = $request->input('fecha_desvinculacion') ?: now()->toDateString();
                $nroDocumento = $request->input('nro_documento');

                // 1. Cerrar asignación en el puesto (el puesto pasa a estado VACANTE)
                $asignacion->update([
                    'fecha_fin' => $fechaDesvinculacion,
                    'asignacion' => $motivo,
                    '_estado' => 'FINALIZADO',
                    '_usuario_modificacion' => auth()->id() ?? 1,
                    '_fecha_modificacion' => now(),
                ]);

                // 2. Cerrar y actualizar el registro en el historial laboral (rrhh.datos_laborales)
                $ficha = FichaPersonal::firstOrCreate(['id_persona' => $asignacion->id_persona]);
                $datoLaboral = DatoLaboral::where('id_ficha_personal', $ficha->id)
                    ->where('es_puesto_anterior', false)
                    ->whereNull('fecha_desvinculacion')
                    ->latest('id')
                    ->first();

                if ($datoLaboral) {
                    $datoLaboral->update([
                        'fecha_desvinculacion' => $fechaDesvinculacion,
                        'tipo_movimiento' => $motivo,
                        'nro_documento' => $nroDocumento ?: $datoLaboral->nro_documento,
                        'es_puesto_anterior' => true,
                        '_estado' => 'FINALIZADO',
                        '_usuario_modificacion' => auth()->id() ?? 1,
                        '_fecha_modificacion' => now(),
                    ]);
                } else {
                    // Si no había registro previo, se crea el dato histórico completo
                    DatoLaboral::create([
                        'id_ficha_personal' => $ficha->id,
                        'cargo' => $asignacion->puesto?->nombre ?? 'Cargo Asignado',
                        'unidad_organizacional' => $asignacion->puesto?->unidadOrganizacional?->nombre ?? '',
                        'tipo_funcionario' => $asignacion->puesto?->tipo_puesto ?? 'PLANTA',
                        'nro_item' => $asignacion->nro_item,
                        'fecha_ingreso' => $asignacion->fecha_inicio,
                        'fecha_desvinculacion' => $fechaDesvinculacion,
                        'tipo_movimiento' => $motivo,
                        'nro_documento' => $nroDocumento,
                        'es_puesto_anterior' => true,
                        '_estado' => 'FINALIZADO',
                        '_usuario_creacion' => auth()->id() ?? 1,
                        '_fecha_creacion' => now(),
                    ]);
                }

                // 3. Control de acceso al ERP: si renuncia, destitución, despido o cese, desactivar usuario si se solicitó
                $debeDesactivar = $request->has('desactivar_acceso_erp')
                    ? $request->boolean('desactivar_acceso_erp')
                    : in_array($motivo, ['RENUNCIA', 'DESTITUCION', 'DESPIDO', 'CONCLUSION_CONTRATO', 'JUBILACION']);

                if ($debeDesactivar) {
                    User::where('usr_externo_id', $asignacion->id_persona)->update(['usr_estado' => 'I']);
                }

                // 4. Registro de auditoría
                $this->auditService->log(
                    event: 'funcionario_desvinculado',
                    model: $asignacion,
                    newValues: [
                        'funcionario' => $asignacion->persona?->nombre_completo,
                        'puesto' => $asignacion->puesto?->nombre,
                        'motivo' => $motivo,
                        'fecha_desvinculacion' => $fechaDesvinculacion,
                        'nro_documento' => $nroDocumento,
                        'usuario_erp_desactivado' => $debeDesactivar,
                    ]
                );
            });

            return response()->json([
                'success' => true,
                'message' => 'Desvinculación procesada exitosamente. El puesto ahora está vacante y el legajo laboral fue actualizado.',
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al desvincular puesto', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al procesar la desvinculación: ' . $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function historialPuesto(int $idPuesto): JsonResponse
    {
        $historial = AsignacionPuesto::with('persona')
            ->where('id_puesto', $idPuesto)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $historial,
        ], Response::HTTP_OK);
    }

    public function listarEscalasSalariales(): JsonResponse
    {
        $escalas = EscalaSalarial::with('nivel')
            ->withCount(['puestos' => function ($q) {
                $q->where('_estado', 'ACTIVO');
            }])
            ->where('_estado', 'ACTIVO')
            ->orderBy('salario', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $escalas], Response::HTTP_OK);
    }

    public function storeEscalaSalarial(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'salario' => 'nullable|numeric|min:0',
            'salario_mensual' => 'nullable|numeric|min:0',
            'id_nivel' => 'nullable|integer',
            'codigo' => 'nullable|string|max:50',
            'id_gestion' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $salario = (float) ($request->input('salario') ?? $request->input('salario_mensual') ?? 0);

        $escala = EscalaSalarial::create([
            'nombre' => trim((string) $request->input('nombre')),
            'salario' => $salario,
            'id_nivel' => $request->input('id_nivel') ? (int) $request->input('id_nivel') : null,
            'codigo' => $request->input('codigo') ? trim((string) $request->input('codigo')) : null,
            'id_gestion' => $request->input('id_gestion') ? (int) $request->input('id_gestion') : null,
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Escala salarial registrada exitosamente.', 'data' => $escala->load('nivel')], Response::HTTP_CREATED);
    }

    public function updateEscalaSalarial(Request $request, int $id): JsonResponse
    {
        $escala = EscalaSalarial::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'salario' => 'nullable|numeric|min:0',
            'salario_mensual' => 'nullable|numeric|min:0',
            'id_nivel' => 'nullable|integer',
            'codigo' => 'nullable|string|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $salario = (float) ($request->input('salario') ?? $request->input('salario_mensual') ?? $escala->salario);

        $escala->update([
            'nombre' => trim((string) $request->input('nombre')),
            'salario' => $salario,
            'id_nivel' => $request->input('id_nivel') ? (int) $request->input('id_nivel') : null,
            'codigo' => $request->input('codigo') ? trim((string) $request->input('codigo')) : null,
            '_usuario_modificacion' => auth()->id() ?? 1,
            '_fecha_modificacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Escala salarial actualizada exitosamente.', 'data' => $escala->load('nivel')], Response::HTTP_OK);
    }

    public function deleteEscalaSalarial(int $id): JsonResponse
    {
        $escala = EscalaSalarial::findOrFail($id);

        $puestosCount = Puesto::where('id_escala_salarial', $id)->where('_estado', 'ACTIVO')->count();
        if ($puestosCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "No se puede dar de baja la escala porque tiene {$puestosCount} puesto(s) activo(s) asignado(s). Reasigne los puestos primero.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $escala->update([
            '_estado' => 'INACTIVO',
            '_usuario_modificacion' => auth()->id() ?? 1,
            '_fecha_modificacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Escala salarial dada de baja exitosamente.'], Response::HTTP_OK);
    }

    public function listarNiveles(): JsonResponse
    {
        $niveles = Nivel::where('_estado', 'ACTIVO')->orderBy('nivel', 'asc')->get();

        return response()->json(['success' => true, 'data' => $niveles], Response::HTTP_OK);
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
