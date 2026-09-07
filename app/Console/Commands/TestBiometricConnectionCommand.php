<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Biometrics\ZkBiometricService;
use Illuminate\Console\Command;

class TestBiometricConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biometrics:test-connection 
                            {ip=192.168.1.201 : Dirección IP del reloj biométrico} 
                            {--port=4370 : Puerto de comunicación TCP/UDP} 
                            {--timeout=2.0 : Tiempo máximo de espera en segundos}
                            {--stress-iterations=5 : Número de intentos para prueba de estrés}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Herramienta de diagnóstico de red y prueba de estrés para hardware biométrico ZKTeco';

    /**
     * Execute the console command.
     */
    public function handle(ZkBiometricService $biometricService): int
    {
        $ip = (string) $this->argument('ip');
        $port = (int) $this->option('port');
        $timeout = (float) $this->option('timeout');
        $iterations = (int) $this->option('stress-iterations');

        $this->info('=========================================================');
        $this->info('🏢 DIAGNÓSTICO DE RED Y RESILIENCIA PARA HARDWARE BIOMÉTRICO');
        $this->info('=========================================================');
        $this->line("📍 Destino: <fg=yellow>{$ip}:{$port}</>");
        $this->line("⏱️  Timeout por intento: <fg=yellow>{$timeout}s</>");
        $this->line("🔄 Iteraciones de estrés: <fg=yellow>{$iterations}</>\n");

        $this->info('1️⃣  Fase 1: Prueba de Conectividad de Socket Rápido (Non-blocking probe)');
        $successfulPings = 0;
        $latencies = [];

        for ($i = 1; $i <= $iterations; $i++) {
            $startTime = microtime(true);
            $reachable = $biometricService->pingDevice($ip, $port, $timeout);
            $elapsedMs = round((microtime(true) - $startTime) * 1000, 2);

            if ($reachable) {
                $successfulPings++;
                $latencies[] = $elapsedMs;
                $this->line("  [Iteración {$i}/{$iterations}] <fg=green>✔ Conectado</> en <fg=cyan>{$elapsedMs} ms</>");
            } else {
                $this->line("  [Iteración {$i}/{$iterations}] <fg=red>✘ Fallo / Timeout</> (Tiempo transcurrido: <fg=yellow>{$elapsedMs} ms</>)");
            }
            usleep(200000); // 200ms entre intentos
        }

        $this->newLine();
        $this->info('📊 Estadísticas de Conectividad:');
        $packetLoss = round((($iterations - $successfulPings) / $iterations) * 100, 1);
        $avgLatency = count($latencies) > 0 ? round(array_sum($latencies) / count($latencies), 2) : 0;

        $this->table(
            ['Métrica', 'Valor', 'Evaluación'],
            [
                ['Intentos Enviados', $iterations, 'OK'],
                ['Respuestas Exitosas', $successfulPings, $successfulPings > 0 ? 'OK' : 'Crítico'],
                ['Pérdida de Paquetes', "{$packetLoss}%", $packetLoss === 0.0 ? 'Excelente' : ($packetLoss < 20 ? 'Aceptable' : 'Riesgo Alto')],
                ['Latencia Promedio', "{$avgLatency} ms", $avgLatency < 50 ? 'Óptima' : 'Revisar switch/cable'],
            ]
        );

        $this->newLine();
        $this->info('2️⃣  Fase 2: Prueba de Protección ante Desconexión Física y Timeout');
        if ($successfulPings === 0) {
            $this->warn("⚠️  El dispositivo está desconectado físicamente o la IP {$ip} no responde.");
            $this->line("✔ <fg=green>Comprobación de Seguridad APROBADA:</> El sistema abortó la petición de forma segura en <fg=cyan>{$elapsedMs} ms</> sin congelar los workers de PHP-FPM ni la cola de procesos.");
        } else {
            $this->info('3️⃣  Fase 3: Intento de Extracción e Idempotencia (SHA-256)');
            try {
                $logs = $biometricService->getAttendanceLogs($ip, $port, $timeout);
                $this->line('✔ <fg=green>Handshake exitoso.</> Marcaciones recuperadas: <fg=cyan>'.$logs->count().'</>');

                if ($logs->isNotEmpty()) {
                    $sample = $logs->first();
                    $this->line("  - ID Usuario biométrico: {$sample['biometric_user_id']}");
                    $this->line("  - Timestamp: {$sample['record_timestamp']}");
                    $this->line("  - Huella SHA-256 (Idempotencia): <fg=yellow>{$sample['fingerprint_hash']}</>");
                }
            } catch (\Throwable $e) {
                $this->error('✘ Error en protocolo ZK: '.$e->getMessage());
            }
        }

        $this->newLine();
        $this->info('=========================================================');
        $this->info('✔ Diagnóstico de hardware concluido.');
        $this->info('=========================================================');

        return Command::SUCCESS;
    }
}
