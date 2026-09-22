<?php

declare(strict_types=1);

namespace App\Console\Commands\Comercial;

use App\Services\Comercial\FoxProMigradorService;
use Illuminate\Console\Command;

class MigrarFoxProCommand extends Command
{
    protected $signature = 'comercial:migrar-foxpro 
                            {--path= : Ruta al directorio con los archivos DBF}
                            {--dry-run : Ejecutar en modo solo simulación sin tocar la BD}
                            {--confirm : Confirmar inserción real en la base de datos}
                            {--limit=0 : Límite de registros de facturas/socios para pruebas}
                            {--sin-facturas : Omitir migración masiva de ventas.DBF}
                            {--sin-recibos : Omitir migración de recibos.DBF}';

    protected $description = 'Migra datos vivos desde el sistema FoxPro heredado (Abonados, Calles, Convenios, Bajas, Recibos y Facturas) hacia SIM EMAPAP';

    public function handle(FoxProMigradorService $migrador): int
    {
        $path = $this->option('path') ?: storage_path('app/legacy_emapa_2026');
        if (!is_dir($path)) {
            $candidatosDirs = [
                '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026/DATA',
                '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026',
                '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/cr070923',
            ];
            foreach ($candidatosDirs as $cd) {
                if (is_dir($cd)) {
                    $path = $cd;
                    break;
                }
            }
        }

        $dryRun = !$this->option('confirm');
        $limit = (int) $this->option('limit');
        $sinFacturas = (bool) $this->option('sin-facturas');
        $sinRecibos = (bool) $this->option('sin-recibos');

        $this->info("=================================================================");
        $this->info("MIGRACIÓN COMPLETA FOXPRO -> SIM EMAPAP 2026");
        $this->info("Modo: " . ($dryRun ? "DRY-RUN (Simulación, la BD no será modificada)" : "PRODUCCIÓN (Inserción real en PostgreSQL)"));
        $this->info("Ruta DBF: {$path}");
        $this->info("=================================================================");

        // 1. Calles y Zonas
        $callesDbf = $this->buscarArchivo($path, 'calles.DBF');
        if ($callesDbf) {
            $this->comment("1. Migrando Calles y Zonas ({$callesDbf})...");
            $resCalles = $migrador->migrarCalles($callesDbf, $dryRun);
            $this->line("   Total en DBF: {$resCalles['total_en_dbf']} | Procesadas: {$resCalles['calles_procesadas']} | Insertadas: {$resCalles['calles_insertadas']}");
        }

        // 2. Abonados y Medidores
        $sociosDbf = $this->buscarArchivo($path, 'socios.DBF');
        if ($sociosDbf) {
            $this->comment("2. Migrando Abonados y Medidores ({$sociosDbf})...");
            $resSocios = $migrador->migrarAbonados($sociosDbf, $dryRun, $limit);
            $this->line("   Total en DBF: {$resSocios['total_en_dbf']} | Procesados: {$resSocios['abonados_procesados']} | Insertados: {$resSocios['abonados_insertados']}");
        }

        // 3. Bajas Formales
        $bajasDbf = $this->buscarArchivo($path, 'bajasoc.DBF');
        if ($bajasDbf) {
            $this->comment("3. Aplicando Bajas Formales ({$bajasDbf})...");
            $resBajas = $migrador->migrarBajas($bajasDbf, $dryRun);
            $this->line("   Total en DBF: {$resBajas['total_bajas_dbf']} | Abonados dados de baja: {$resBajas['abonados_dados_de_baja']}");
        }

        // 4. Convenios de Pago
        $convenioDbf = $this->buscarArchivo($path, 'convenio.DBF');
        $detconveDbf = $this->buscarArchivo($path, 'detconve.dbf');
        if ($convenioDbf && $detconveDbf) {
            $this->comment("4. Migrando Convenios de Pago ({$convenioDbf})...");
            $resConvenios = $migrador->migrarConvenios($convenioDbf, $detconveDbf, $dryRun);
            $this->line("   Total en DBF: {$resConvenios['total_convenios_dbf']} | Insertados: {$resConvenios['convenios_insertados']}");
        }

        // 5. Recibos de Caja (Otros conceptos)
        if (!$sinRecibos) {
            $recibosDbf = $this->buscarArchivo($path, 'recibos.DBF');
            if ($recibosDbf) {
                $this->comment("5. Migrando Recibos de Otros Conceptos ({$recibosDbf})...");
                $progressBar = $this->output->createProgressBar();
                $resRecibos = $migrador->migrarRecibosOtros($recibosDbf, $dryRun, function ($proc, $tot) use ($progressBar) {
                    $progressBar->setMaxSteps($tot);
                    $progressBar->setProgress($proc);
                });
                $progressBar->finish();
                $this->newLine();
                $this->line("   Total en DBF: {$resRecibos['total_en_dbf']} | Recibos de caja migrados: {$resRecibos['recibos_migrados']}");
            }
        }

        // 6. Facturas Reales Emitidas (ventas.DBF)
        if (!$sinFacturas) {
            $ventasDbf = $this->buscarArchivo($path, 'ventas.DBF');
            if ($ventasDbf) {
                $this->comment("6. Migrando Facturas Reales Emitidas ({$ventasDbf})...");
                $progressBar = $this->output->createProgressBar();
                $resFacturas = $migrador->migrarFacturasVentas($ventasDbf, $dryRun, $limit, function ($proc, $tot) use ($progressBar) {
                    $progressBar->setMaxSteps($tot);
                    $progressBar->setProgress($proc);
                });
                $progressBar->finish();
                $this->newLine();
                $this->line("   Total en DBF: {$resFacturas['total_en_dbf']} | Facturas reales migradas: {$resFacturas['facturas_migradas']}");
            }
        }

        $this->info("=================================================================");
        if ($dryRun) {
            $this->warn("Simulación completada con éxito. No se alteró la base de datos.");
            $this->line("Para ejecutar la migración definitiva a producción, ejecute:");
            $this->line("  php artisan comercial:migrar-foxpro --path=\"{$path}\" --confirm");
        } else {
            $this->info("¡Migración real completada e insertada exitosamente en PostgreSQL!");
        }

        return Command::SUCCESS;
    }

    private function buscarArchivo(string $directorio, string $nombre): ?string
    {
        $candidatos = [
            $directorio . '/' . $nombre,
            $directorio . '/' . strtolower($nombre),
            $directorio . '/' . strtoupper($nombre),
            $directorio . '/DATA/' . $nombre,
            $directorio . '/DATA/' . strtolower($nombre),
            $directorio . '/DATA/' . strtoupper($nombre),
            $directorio . '/data/' . $nombre,
            $directorio . '/data/' . strtolower($nombre),
            $directorio . '/data/' . strtoupper($nombre),
        ];

        foreach ($candidatos as $c) {
            if (file_exists($c)) {
                return $c;
            }
        }

        return null;
    }
}
