<?php

declare(strict_types=1);

namespace App\Services\Biometrics;

use Illuminate\Support\Collection;
use Rats\Zkteco\Lib\ZKTeco;
use RuntimeException;

class ZkBiometricService
{
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
     *
     * @param string $deviceIp Dirección IP del reloj biométrico en red local
     * @param int $port Puerto de comunicación (default 4370)
     * @param float $socketTimeout Tiempo máximo de conexión en segundos
     * @return Collection
     */
    public function getAttendanceLogs(string $deviceIp, int $port = 4370, float $socketTimeout = 3.0): Collection
    {
        // 1. Verificación no bloqueante de disponibilidad física
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
                    // Huella digital inmutable para evitar duplicados en base de datos
                    'fingerprint_hash' => hash('sha256', "{$deviceIp}|{$userId}|{$timestamp}|{$state}"),
                ];
            });
        } catch (\Throwable $e) {
            @$zk->disconnect();
            throw new RuntimeException("Error durante la extracción de datos biométricos: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
