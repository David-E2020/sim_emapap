<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\Asistencia;
use App\Models\Rrhh\Biometrico;
use App\Services\Biometrics\ZkBiometricService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AsistenciaController extends Controller
{
    public function __construct(
        private readonly ZkBiometricService $biometricService
    ) {}

    public function listarBiometricos(): JsonResponse
    {
        $biometricos = Biometrico::orderBy('id')->get()->map(function ($b) {
            $isOnline = $this->biometricService->pingDevice($b->url, (int) $b->puerto, 0.5);
            $b->is_online = $isOnline;

            return $b;
        });

        return response()->json([
            'success' => true,
            'data' => $biometricos,
        ], Response::HTTP_OK);
    }

    public function probarConexion(int $id): JsonResponse
    {
        $biometrico = Biometrico::findOrFail($id);
        $isOnline = $this->biometricService->pingDevice($biometrico->url, (int) $biometrico->puerto, 1.5);

        return response()->json([
            'success' => true,
            'online' => $isOnline,
            'message' => $isOnline
                ? "El reloj biométrico \"{$biometrico->nombre}\" ({$biometrico->url}) está en línea."
                : "No se puede alcanzar el reloj biométrico en {$biometrico->url}:{$biometrico->puerto}.",
        ], Response::HTTP_OK);
    }

    public function sincronizar(int $id): JsonResponse
    {
        $biometrico = Biometrico::findOrFail($id);

        try {
            $resultado = $this->biometricService->syncBiometricoToDatabase($biometrico);

            return response()->json([
                'success' => true,
                'message' => "Sincronización completada: {$resultado['insertadas']} nuevas marcaciones registradas ({$resultado['omitidas']} ya existentes).",
                'data' => $resultado,
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al sincronizar reloj biometrico', ['id' => $id, 'exception' => $ex->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Fallo al sincronizar con el reloj: '.$ex->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function listarAsistencias(Request $request): JsonResponse
    {
        $fecha = $request->query('fecha', now()->format('Y-m-d'));
        $asistencias = Asistencia::with(['persona.asignacionesPuestos.puesto.unidadOrganizacional'])
            ->where('fecha', $fecha)
            ->orderBy('id_persona')
            ->get();

        return response()->json([
            'success' => true,
            'fecha' => $fecha,
            'data' => $asistencias,
        ], Response::HTTP_OK);
    }

    public function calcularAsistencia(Request $request): JsonResponse
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));

        try {
            $totalProcesados = $this->biometricService->calculateDailyAttendanceForDate($fecha);

            return response()->json([
                'success' => true,
                'message' => "Cálculo de asistencia completado para {$fecha}: {$totalProcesados} funcionarios procesados.",
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al calcular asistencia', ['fecha' => $fecha, 'exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al calcular asistencia.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
