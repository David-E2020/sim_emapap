<?php

declare(strict_types=1);

namespace App\Console\Commands\Comercial;

use App\Services\Comercial\FoxProMigradorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarOperacionesCommand extends Command
{
    protected $signature = 'comercial:migrar-operaciones
                            {--path= : Ruta al archivo operacio.dbf}
                            {--hasta= : Fecha limite superior YYYY-MM-DD (ej. 2026-02-06, opcional)}
                            {--limit=0 : Limite de registros para prueba (0 = todos)}
                            {--confirm : Confirmar insercion real en la base de datos}';

    protected $description = 'Migra el historico completo de operaciones, consumos y pagos desde operacio.DBF a comercial.lecturas_mensuales';

    public function handle(FoxProMigradorService $migrador): int
    {
        $dryRun = !$this->option('confirm');
        $rutaDbf = $this->option('path') ?: storage_path('app/legacy_emapa_2026/operacio.dbf');
        $fechaLimite = $this->option('hasta') ?: null;
        $limite = (int) $this->option('limit');

        $this->info("=================================================================");
        $this->info("MIGRACION HISTORICA DE OPERACIONES Y PAGOS (operacio.DBF)");
        $this->info("Modo:         " . ($dryRun ? "DRY-RUN (Simulación)" : "PRODUCCION (Insercion/Actualizacion en DB)"));
        $this->info("Archivo:      " . $rutaDbf);
        $this->info("Fecha Limite: " . ($fechaLimite ?: "SIN LIMITE (Historico completo)"));
        $this->info("Limite Regs:  " . ($limite > 0 ? (string)$limite : "TODOS"));
        $this->info("=================================================================");

        if (!file_exists($rutaDbf)) {
            $this->error("El archivo no existe: {$rutaDbf}");
            return Command::FAILURE;
        }

        $tamanoMb = round(filesize($rutaDbf) / (1024 * 1024), 2);
        $this->comment("Tamano del archivo DBF: {$tamanoMb} MB");

        // Conteo inicial en BD
        $lecturasAntes = DB::table('comercial.lecturas_mensuales')
            ->selectRaw("estado_pago, count(*) as cant")
            ->groupBy('estado_pago')
            ->pluck('cant', 'estado_pago')
            ->toArray();

        $this->line("Estado actual en comercial.lecturas_mensuales:");
        foreach ($lecturasAntes as $est => $cant) {
            $this->line("   - {$est}: " . number_format($cant));
        }

        $inicio = microtime(true);
        $progressBar = null;

        $callback = function ($procesados, $total) use (&$progressBar, $limite) {
            $totalInt = (int) ($limite > 0 ? min($limite, $total) : $total);
            $procInt = (int) $procesados;
            if (!$progressBar) {
                $progressBar = $this->output->createProgressBar($totalInt);
                $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- Tiempo transcurrido: %elapsed:6s%');
                $progressBar->start();
            }
            $progressBar->setProgress(min($procInt, $totalInt));
        };

        try {
            $this->comment("\nIniciando procesamiento...");
            $res = $migrador->migrarOperacionesDbf(
                rutaOperacioDbf: $rutaDbf,
                dryRun: $dryRun,
                fechaLimite: $fechaLimite,
                limite: $limite,
                progressCallback: $callback
            );

            if ($progressBar) {
                $progressBar->finish();
                $this->line("");
            }

            $duracion = round(microtime(true) - $inicio, 2);

            $this->info("\n=================================================================");
            $this->info("RESUMEN DE MIGRACION");
            $this->info("=================================================================");
            $this->table(
                ['Metrica', 'Valor'],
                [
                    ['Total registros leidos en DBF', number_format($res['total_en_dbf'])],
                    ['Lecturas procesadas/upsert', number_format($res['lecturas_migradas'])],
                    ['Omitidos (sin codigo o fuera de fecha)', number_format($res['omitidos'])],
                    ['Duracion total', "{$duracion} segundos"],
                    ['Modo', $dryRun ? 'DRY-RUN (Sin cambios guardados)' : 'PRODUCCION (Cambios aplicados)'],
                ]
            );

            if (!$dryRun) {
                $this->comment("\nSincronizando saldo_deuda y meses_mora en comercial.abonados...");
                DB::statement("
                    UPDATE comercial.abonados a
                    SET 
                        saldo_deuda = COALESCE(sub.total_deuda, 0.00),
                        meses_mora = COALESCE(sub.total_meses, 0)
                    FROM (
                        SELECT id_abonado, ROUND(SUM(total_facturado)::numeric, 2) as total_deuda, COUNT(*) as total_meses
                        FROM comercial.lecturas_mensuales
                        WHERE estado_pago = 'PENDIENTE' AND total_facturado > 0
                        GROUP BY id_abonado
                    ) sub
                    WHERE a.id = sub.id_abonado;
                ");

                DB::statement("
                    UPDATE comercial.abonados
                    SET saldo_deuda = 0.00, meses_mora = 0
                    WHERE id NOT IN (
                        SELECT DISTINCT id_abonado 
                        FROM comercial.lecturas_mensuales 
                        WHERE estado_pago = 'PENDIENTE' AND total_facturado > 0
                    );
                ");

                $lecturasDespues = DB::table('comercial.lecturas_mensuales')
                    ->selectRaw("estado_pago, count(*) as cant")
                    ->groupBy('estado_pago')
                    ->pluck('cant', 'estado_pago')
                    ->toArray();

                $this->info("\nEstado posterior en comercial.lecturas_mensuales:");
                foreach ($lecturasDespues as $est => $cant) {
                    $cantAntes = $lecturasAntes[$est] ?? 0;
                    $dif = $cant - $cantAntes;
                    $difStr = $dif >= 0 ? "+".number_format($dif) : number_format($dif);
                    $this->line("   - {$est}: " . number_format($cant) . " ({$difStr})");
                }
            } else {
                $this->warn("\nRecordatorio: Para aplicar los cambios en la base de datos, ejecuta con la opcion --confirm");
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("\nError durante la migracion: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
