<?php

declare(strict_types=1);

namespace App\Services\Biometrics;

use App\Models\Rrhh\Asistencia;
use App\Models\Rrhh\Biometrico;
use App\Models\Rrhh\Marcacion;
use App\Models\Rrhh\Persona;
use App\Services\Audit\AuditService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Rats\Zkteco\Lib\ZKTeco;
use RuntimeException;

class ZkBiometricService
{
    public function __construct(
        private readonly ?AuditService $auditService = null
    ) {}

    /**
     * Comprueba si el dispositivo biométrico físico en red está alcanzable mediante socket rápido.
     */
    public function pingDevice(string $deviceIp, int $port = 4370, float $timeoutSeconds = 2.0): bool
    {
        $socket = @fsockopen($deviceIp, $port, $errorCode, $errorMessage, $timeoutSeconds);
        if (is_resource($socket)) {
            fclose($socket);
            return true;
        }
        return false;
    }

    /**
     * Extrae los logs de asistencia del reloj biométrico con huella de idempotencia (SHA-256).
     */
    public function getAttendanceLogs(string $deviceIp, int $port = 4370, float $socketTimeout = 3.0): Collection
    {
        if (!$this->pingDevice($deviceIp, $port, $socketTimeout)) {
            throw new RuntimeException("El reloj biométrico en {$deviceIp}:{$port} no responde en la red local.");
        }

        $zk = new ZKTeco($deviceIp, $port);

        if (!$zk->connect()) {
            throw new RuntimeException("Fallo en la negociación del protocolo ZKTeco con el dispositivo en {$deviceIp}:{$port}.");
        }

        try {
            $rawAttendance = $zk->getAttendance();
            $zk->disconnect();

            if (!is_array($rawAttendance)) {
                return collect([]);
            }

            return collect($rawAttendance)->map(function ($record) use ($deviceIp) {
                $userId = (string)($record['id'] ?? $record['uid'] ?? '');
                $timestamp = (string)($record['timestamp'] ?? '');
                $state = (int)($record['state'] ?? 0);
                $type = (int)($record['type'] ?? 0);

                return [
                    'device_ip' => $deviceIp,
                    'biometric_user_id' => $userId,
                    'record_timestamp' => $timestamp,
                    'attendance_state' => $state,
                    'verification_type' => $type,
                    'fingerprint_hash' => hash('sha256', "{$deviceIp}|{$userId}|{$timestamp}|{$state}"),
                ];
            });
        } catch (\Throwable $e) {
            @$zk->disconnect();
            throw new RuntimeException("Error durante la extracción de datos biométricos: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Sincroniza las marcaciones de un reloj físico directamente a la base de datos (rrhh.marcaciones).
     */
    public function syncBiometricoToDatabase(Biometrico $biometrico): array
    {
        $logs = $this->getAttendanceLogs($biometrico->url, (int)$biometrico->puerto);
        $insertadas = 0;
        $omitidas = 0;

        DB::transaction(function () use ($biometrico, $logs, &$insertadas, &$omitidas) {
            foreach ($logs as $log) {
                $timestamp = Carbon::parse($log['record_timestamp']);
                $fecha = $timestamp->format('Y-m-d');
                $hora = $timestamp->format('H:i:s');
                $hash = $log['fingerprint_hash'];

                $exists = Marcacion::where('hash', $hash)->exists();
                if ($exists) {
                    $omitidas++;
                    continue;
                }

                Marcacion::create([
                    'id_usuario_marcacion' => $log['biometric_user_id'],
                    'id_biometrico' => $biometrico->id,
                    'fecha' => $fecha,
                    'hora' => $hora,
                    'hash' => $hash,
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'SINCRONIZACION',
                ]);
                $insertadas++;
            }

            // Registrar log de sincronización
            DB::table('rrhh.sincronizaciones')->insert([
                'id_biometrico' => $biometrico->id,
                'fecha_sincronizacion' => now(),
                'total_marcaciones' => $insertadas,
                'estado_sincronizacion' => 'EXITOSO',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
                '_usuario_creacion' => auth()->id() ?? 1,
                '_fecha_creacion' => now(),
            ]);
        });

        return [
            'total' => $logs->count(),
            'insertadas' => $insertadas,
            'omitidas' => $omitidas,
        ];
    }

    /**
     * Procesa y calcula la asistencia diaria de los funcionarios para una fecha dada.
     */
    public function calculateDailyAttendanceForDate(string $fecha): int
    {
        $personas = Persona::with(['asistencias' => function ($q) use ($fecha) {
            $q->where('fecha', $fecha);
        }])->get();

        $procesados = 0;

        foreach ($personas as $persona) {
            // Obtener todas las marcaciones del día para esta persona
            $ci = $persona->nro_documento;
            $marcaciones = Marcacion::where('fecha', $fecha)
                ->where('id_usuario_marcacion', $ci)
                ->orderBy('hora')
                ->get();

            if ($marcaciones->isEmpty()) {
                continue;
            }

            $primeraMarcacion = $marcaciones->first()->hora;
            $ultimaMarcacion = $marcaciones->count() > 1 ? $marcaciones->last()->hora : null;

            // Calcular atraso respecto a las 08:30 (ejemplo estándar)
            $horaEntrada = Carbon::parse("{$fecha} {$primeraMarcacion}");
            $horaLimite = Carbon::parse("{$fecha} 08:30:00");
            $minutosAtraso = $horaEntrada->greaterThan($horaLimite)
                ? (int)$horaLimite->diffInMinutes($horaEntrada)
                : 0;

            $asistencia = Asistencia::updateOrCreate(
                [
                    'fecha' => $fecha,
                    'id_persona' => $persona->id,
                ],
                [
                    'entrada_primer_periodo' => $primeraMarcacion,
                    'salida_primer_periodo' => $ultimaMarcacion,
                    'minutos_de_atraso_primer_periodo' => $minutosAtraso,
                    'merece_refrigerio' => true,
                    'dia' => Carbon::parse($fecha)->locale('es')->dayName,
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CALCULO_ASISTENCIA',
                ]
            );

            $procesados++;
        }

        return $procesados;
    }
}
