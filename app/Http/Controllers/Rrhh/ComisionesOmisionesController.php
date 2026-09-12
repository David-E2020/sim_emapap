<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\SolicitudSalida;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ComisionesOmisionesController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * Listar comisiones de viaje oficial.
     */
    public function listarComisiones(): JsonResponse
    {
        $comisiones = SolicitudSalida::with(['permiso'])
            ->whereHas('permiso', function ($q) {
                $q->where('sigla', 'C.O.')->orWhere('nombre', 'LIKE', '%COMISION%');
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comisiones,
        ], Response::HTTP_OK);
    }

    /**
     * Registrar comisión de viaje con viáticos.
     */
    public function storeComision(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_persona' => ['required', 'integer', Rule::exists(Persona::class, 'id')],
            'lugar' => 'required|string|max:255',
            'motivo' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'monto_viatico' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $solicitud = DB::transaction(function () use ($request) {
                // Obtener ID del permiso de Comisión Oficial
                $permiso = DB::table('rrhh.permisos')->where('sigla', 'C.O.')->first();
                $idPermiso = $permiso ? $permiso->id : 2;

                $sol = SolicitudSalida::create([
                    'id_permiso' => $idPermiso,
                    'motivo' => $request->input('motivo'),
                    'lugar' => $request->input('lugar'),
                    'fecha_inicio' => $request->input('fecha_inicio'),
                    'fecha_fin' => $request->input('fecha_fin'),
                    'dia_completo' => true,
                    'metadata' => [
                        'monto_viatico' => (float) $request->input('monto_viatico', 0),
                        'transporte' => $request->input('transporte', 'TERRESTRE'),
                        'tipo_viaje' => 'COMISION_OFICIAL',
                    ],
                    'cite' => 'CITE-COMISION-'.date('Y').'-'.strtoupper(uniqid()),
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                DB::table('rrhh.usuarios_solicitudes_salidas')->insert([
                    'id_solicitud_salida' => $sol->id,
                    'id_persona' => (int) $request->input('id_persona'),
                    'estado_aprobacion' => 'PENDIENTE',
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                $this->auditService->log('comision_created', $sol, $sol->toArray());

                return $sol;
            });

            return response()->json([
                'success' => true,
                'message' => 'Comisión de viaje registrada con CITE '.$solicitud->cite,
                'data' => $solicitud,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al registrar comision', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al registrar comisión.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Listar omisiones de marcado biométrico.
     */
    public function listarOmisiones(): JsonResponse
    {
        $omisiones = SolicitudSalida::with(['permiso'])
            ->whereHas('permiso', function ($q) {
                $q->where('sigla', 'OM')->orWhere('nombre', 'LIKE', '%OMISION%');
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $omisiones,
        ], Response::HTTP_OK);
    }

    /**
     * Registrar justificación de omisión de marcado biométrico.
     */
    public function storeOmision(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_persona' => ['required', 'integer', Rule::exists(Persona::class, 'id')],
            'fecha' => 'required|date',
            'turno_periodo' => 'required|string', // ENTRADA_MANANA, SALIDA_MANANA, ENTRADA_TARDE, SALIDA_TARDE
            'hora_marcado_omision' => 'required|string',
            'motivo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $solicitud = DB::transaction(function () use ($request) {
                $permiso = DB::table('rrhh.permisos')->where('sigla', 'OM')->first();
                $idPermiso = $permiso ? $permiso->id : 10;

                $sol = SolicitudSalida::create([
                    'id_permiso' => $idPermiso,
                    'motivo' => $request->input('motivo'),
                    'fecha_inicio' => $request->input('fecha'),
                    'fecha_fin' => $request->input('fecha'),
                    'hora_marcado_omision' => $request->input('hora_marcado_omision'),
                    'turno_periodo' => $request->input('turno_periodo'),
                    'cite' => 'CITE-OMISION-'.date('Y').'-'.strtoupper(uniqid()),
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                DB::table('rrhh.usuarios_solicitudes_salidas')->insert([
                    'id_solicitud_salida' => $sol->id,
                    'id_persona' => (int) $request->input('id_persona'),
                    'estado_aprobacion' => 'PENDIENTE',
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => auth()->id() ?? 1,
                    '_fecha_creacion' => now(),
                ]);

                return $sol;
            });

            return response()->json([
                'success' => true,
                'message' => 'Regularización de omisión de marcado solicitada.',
                'data' => $solicitud,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al registrar omision', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al registrar omisión.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bandeja de entrada de solicitudes para firma y aprobación.
     */
    public function bandejaAprobaciones(): JsonResponse
    {
        $pendientes = DB::table('rrhh.usuarios_solicitudes_salidas as uss')
            ->join('rrhh.solicitudes_salidas as ss', 'ss.id', '=', 'uss.id_solicitud_salida')
            ->join('rrhh.personas as p', 'p.id', '=', 'uss.id_persona')
            ->join('rrhh.permisos as perm', 'perm.id', '=', 'ss.id_permiso')
            ->select(
                'uss.id as asignacion_id',
                'uss.estado_aprobacion',
                'ss.id as solicitud_id',
                'ss.cite',
                'ss.motivo',
                'ss.lugar',
                'ss.fecha_inicio',
                'ss.fecha_fin',
                'ss.hora_inicio',
                'ss.hora_fin',
                'ss.horas_solicitadas',
                'ss.turno_periodo',
                'ss.hora_marcado_omision',
                'p.id as persona_id',
                'p.nombres',
                'p.primer_apellido',
                'p.segundo_apellido',
                'p.nro_documento',
                'perm.nombre as permiso_nombre',
                'perm.sigla as permiso_sigla'
            )
            ->orderBy('ss.id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pendientes,
        ], Response::HTTP_OK);
    }

    /**
     * Resolver (Aprobar / Rechazar) una solicitud.
     */
    public function resolverSolicitud(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|string|in:APROBADO,RECHAZADO',
            'observacion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::transaction(function () use ($request, $id) {
                $estado = $request->input('estado');
                $observacion = $request->input('observacion');

                DB::table('rrhh.usuarios_solicitudes_salidas')
                    ->where('id_solicitud_salida', $id)
                    ->update([
                        'estado_aprobacion' => $estado,
                        'fecha_revision' => now(),
                        'observacion' => $observacion,
                        '_usuario_modificacion' => auth()->id() ?? 1,
                        '_fecha_modificacion' => now(),
                    ]);

                DB::table('rrhh.solicitudes_salidas')
                    ->where('id', $id)
                    ->update([
                        'fecha_aprobacion' => $estado === 'APROBADO' ? now() : null,
                        '_estado' => $estado,
                        '_usuario_modificacion' => auth()->id() ?? 1,
                        '_fecha_modificacion' => now(),
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Solicitud {$request->input('estado')} con éxito.",
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al resolver solicitud', ['exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al resolver solicitud.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
