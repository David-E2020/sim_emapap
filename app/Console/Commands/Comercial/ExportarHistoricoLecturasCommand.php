<?php

declare(strict_types=1);

namespace App\Console\Commands\Comercial;

use App\Services\Comercial\ExportarHistoricoLecturasService;
use Illuminate\Console\Command;

class ExportarHistoricoLecturasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comercial:exportar-historico-lecturas {job_id : Identificador único del trabajo de exportación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa en segundo plano la exportación masiva de lecturas y abonados en formato Excel o CSV.';

    /**
     * Execute the console command.
     */
    public function handle(ExportarHistoricoLecturasService $service): int
    {
        $jobId = (string) $this->argument('job_id');
        $this->info("Iniciando exportación para Job ID: {$jobId}");

        try {
            $service->procesarJob($jobId);
            $this->info("Job {$jobId} procesado con éxito.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error al procesar Job {$jobId}: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
