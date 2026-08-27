<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\Justificacion;
use App\Models\Rrhh\Permiso;
use App\Models\Rrhh\SolicitudSalida;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class SolicitudSalidaController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    public function catalogoPermisos(): JsonResponse
    {
        $permisos = Permiso::where('_estado', 'ACTIVO')->get();
        $justificaciones = Justificacion::where('_estado', 'ACTIVO')->get();

        return response()->json([
            'success' => true,
            'permisos' => $permisos,
            'justificaciones' => $justificaciones,
        ], Response::HTTP_OK);
    }

    public function index(Request $request): JsonResponse
    {
        $solicitudes = SolicitudSalida::with(['permiso'])
            ->orderBy('id', 'desc')
            ->paginate((int)$request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $solicitudes->items(),
            'total' => $solicitudes->total(),
            'current_page' => $solicitudes->currentPage(),
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_permiso' => 'required|integer|exists:rrhh.permisos,id',
            'id_persona' => 'required|integer|exists:rrhh.personas,id',
            'motivo' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'hora_inicio' => 'nullable|string',
            'hora_fin' => 'nullable|string',
            'horas_solicitadas' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $solicitud = DB::transaction(function () use ($request) {
                $solicitud = SolicitudSalida::create([
                    'id_permiso' => (int)$request->input('id_permiso'),
                    'id_justificacion' => $request->input('id_justificacion'),
                    'motivo' => $request->input('motivo'),
                    'lugar' => $request->input('lugar'),
                    'fecha_inicio' => $request->input('fecha_inicio'),
                    'fecha_fin' => $request->input('fecha_fin'),
                    'hora_inicio' => $request->input('hora_inicio'),
                    'hora_fin' => $request->input('hora_fin'),
                    'horas_solicitadas' => (float)$request->input('horas_solicitadas', 0),
                    'cite' => 'CITE-RRHH-' . date('Y') . '-' . strtoupper(uniqid()),
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                DB::table('rrhh.usuarios_solicitudes_salidas')->insert([
                    'id_solicitud_salida' => $solicitud->id,
                    'id_persona' => (int)$request->input('id_persona'),
                    'estado_aprobacion' => 'PENDIENTE',
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                $this->auditService->log(
                    event: 'solicitud_salida_created',
                    model: $solicitud,
                    newValues: $solicitud->toArray()
                );

                return $solicitud;
            });

            return response()->json([
                'success' => true,
                'message' => 'Boleta de salida registrada exitosamente.',
                'data' => $solicitud,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al registrar solicitud de salida', ['exception' => $ex->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error interno al registrar solicitud.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
