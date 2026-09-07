<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\Horario;
use App\Models\Rrhh\Periodo;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class HorarioController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    public function index(): JsonResponse
    {
        $horarios = Horario::with('periodos')->where('_estado', 'ACTIVO')->orderBy('id')->get();

        return response()->json([
            'success' => true,
            'data' => $horarios,
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|string|in:CONTINUO,DISCONTINUO,ESPECIAL',
            'tolerancia_minutos' => 'required|integer|min:0|max:60',
            'periodos' => 'required|array|min:1',
            'periodos.*.hora_inicio' => 'required|string',
            'periodos.*.hora_fin' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $horario = DB::transaction(function () use ($request) {
                $horario = Horario::create([
                    'nombre' => strtoupper(trim((string) $request->input('nombre'))),
                    'tipo' => $request->input('tipo'),
                    'dias_laborales' => $request->input('dias_laborales', '1,2,3,4,5'),
                    'tolerancia_minutos' => (int) $request->input('tolerancia_minutos', 10),
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                foreach ($request->input('periodos') as $idx => $p) {
                    Periodo::create([
                        'id_horario' => $horario->id,
                        'hora_inicio' => $p['hora_inicio'],
                        'hora_fin' => $p['hora_fin'],
                        'hora_inicio_tolerancia' => $p['hora_inicio_tolerancia'] ?? null,
                        'hora_fin_tolerancia' => $p['hora_fin_tolerancia'] ?? null,
                        'orden' => $idx + 1,
                        '_usuario_creacion' => auth()->id() ?? 1,
                        '_fecha_creacion' => now(),
                    ]);
                }

                $this->auditService->log('horario_created', $horario, $horario->toArray());

                return $horario->load('periodos');
            });

            return response()->json([
                'success' => true,
                'message' => 'Horario y turnos registrados exitosamente.',
                'data' => $horario,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al registrar horario', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al registrar horario.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function listarAsignaciones(): JsonResponse
    {
        $asignaciones = DB::table('rrhh.asignaciones_horarios as ah')
            ->join('rrhh.personas as p', 'p.id', '=', 'ah.id_persona')
            ->join('rrhh.horarios as h', 'h.id', '=', 'ah.id_horarios')
            ->select(
                'ah.id',
                'ah.fecha_inicio',
                'ah.fecha_fin',
                'ah.permanente',
                'p.id as persona_id',
                'p.nombres',
                'p.primer_apellido',
                'p.segundo_apellido',
                'p.nro_documento',
                'h.nombre as horario_nombre',
                'h.tipo as horario_tipo',
                'h.tolerancia_minutos'
            )
            ->where('ah._estado', 'ACTIVO')
            ->orderBy('p.primer_apellido')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $asignaciones,
        ], Response::HTTP_OK);
    }

    public function asignarHorario(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_horario' => 'required|integer|exists:rrhh.horarios,id',
            'personas_ids' => 'required|array|min:1',
            'fecha_inicio' => 'required|date',
            'permanente' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::transaction(function () use ($request) {
                $idHorario = (int) $request->input('id_horario');
                $fechaInicio = $request->input('fecha_inicio');
                $fechaFin = $request->input('fecha_fin');
                $permanente = (bool) $request->input('permanente', true);

                foreach ($request->input('personas_ids') as $idPersona) {
                    // Desactivar asignación anterior activa si existe
                    DB::table('rrhh.asignaciones_horarios')
                        ->where('id_persona', $idPersona)
                        ->where('_estado', 'ACTIVO')
                        ->update(['_estado' => 'INACTIVO']);

                    DB::table('rrhh.asignaciones_horarios')->insert([
                        'id_horarios' => $idHorario,
                        'id_persona' => $idPersona,
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                        'permanente' => $permanente,
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'ASIGNACION',
                        '_usuario_creacion' => auth()->id() ?? 1,
                        '_fecha_creacion' => now(),
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Horario asignado correctamente al personal seleccionado.',
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al asignar horario', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al asignar horario.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
