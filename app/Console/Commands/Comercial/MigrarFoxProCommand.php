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
                            {--sin-recibos : Omitir migración de recibos.DBF}
                            {--sin-aportes : Omitir migración de contratos apagua.DBF y alcanta.DBF}';

    protected $description = 'Migra datos vivos completos desde el sistema FoxPro heredado (Estados, Categorías/Tarifas, Conceptos, Zonas, Calles, Abonados, Bajas, Aportes, Convenios, Recibos, Períodos y Facturas) hacia SIM EMAPAP';

    public function handle(FoxProMigradorService $migrador): int
    {
        $path = $this->option('path');
        if (!$path) {
            $candidatosDirs = [
                '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026/DATA',
                '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026',
                storage_path('app/legacy_emapa_2026'),
                '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA',
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
        $sinAportes = (bool) $this->option('sin-aportes');

        $this->info("=================================================================");
        $this->info("MIGRACIÓN COMPLETA E INTEGRAL FOXPRO -> SIM EMAPAP 2026");
        $this->info("Modo: " . ($dryRun ? "DRY-RUN (Simulación, la BD no será modificada)" : "PRODUCCIÓN (Inserción real en PostgreSQL)"));
        $this->info("Ruta Primaria DBF: {$path}");
        $this->info("=================================================================");

        // 1. Paramétrica de Estados de Servicio (estado.DBF)
        $estadoDbf = $this->buscarArchivo($path, 'estado.DBF');
        if ($estadoDbf) {
            $this->comment("1. Migrando Estados de Servicio ({$estadoDbf})...");
            $resEstados = $migrador->migrarEstadosAbonados($estadoDbf, $dryRun);
            $this->line("   Total en DBF: {$resEstados['total_en_dbf']} | Estados migrados a paramétrica: {$resEstados['insertados']}");
        } else {
            $this->warn("1. Archivo estado.DBF no encontrado. Se omitió la migración de estados.");
        }

        // 2. Categorías y Tarifas (categor.DBF)
        $categorDbf = $this->buscarArchivo($path, 'categor.DBF');
        if ($categorDbf) {
            $this->comment("2. Migrando Categorías Tarifarias y Escalas ({$categorDbf})...");
            $resTarifas = $migrador->migrarTarifas($categorDbf, $dryRun);
            $this->line("   Total en DBF: {$resTarifas['total_en_dbf']} | Categorías y escalas migradas: {$resTarifas['insertados']}");
        } else {
            $this->warn("2. Archivo categor.DBF no encontrado. Verifique la estructura de categorías.");
        }

        // 3. Conceptos de Otros Ingresos (concepin.DBF)
        $concepinDbf = $this->buscarArchivo($path, 'concepin.DBF');
        if ($concepinDbf) {
            $this->comment("3. Migrando Conceptos de Otros Ingresos ({$concepinDbf})...");
            $resConcepin = $migrador->migrarConceptosIngresos($concepinDbf, $dryRun);
            $this->line("   Total en DBF: {$resConcepin['total_en_dbf']} | Conceptos migrados a paramétrica: {$resConcepin['insertados']}");
        } else {
            $this->warn("3. Archivo concepin.DBF no encontrado. Se omitió la migración de conceptos no tarifarios.");
        }

        // 4. Zonas Comerciales (zonas.dbf)
        $zonasDbf = $this->buscarArchivo($path, 'zonas.dbf');
        if ($zonasDbf) {
            $this->comment("4. Migrando Catálogo Maestro de Zonas ({$zonasDbf})...");
            $resZonas = $migrador->migrarZonas($zonasDbf, $dryRun);
            $this->line("   Total en DBF: {$resZonas['total_en_dbf']} | Zonas registradas: {$resZonas['insertados']}");
        }

        // 5. Calles y Avenidas (calles.DBF)
        $callesDbf = $this->buscarArchivo($path, 'calles.DBF');
        if ($callesDbf) {
            $this->comment("5. Migrando Calles y Avenidas ({$callesDbf})...");
            $resCalles = $migrador->migrarCalles($callesDbf, $dryRun);
            $this->line("   Total en DBF: {$resCalles['total_en_dbf']} | Procesadas: {$resCalles['calles_procesadas']} | Insertadas: {$resCalles['calles_insertadas']}");
        }

        // 6. Abonados y Medidores (socios.DBF)
        $sociosDbf = $this->buscarArchivo($path, 'socios.DBF');
        if ($sociosDbf) {
            $this->comment("6. Migrando Abonados y Medidores ({$sociosDbf})...");
            $resSocios = $migrador->migrarAbonados($sociosDbf, $dryRun, $limit);
            $this->line("   Total en DBF: {$resSocios['total_en_dbf']} | Procesados: {$resSocios['abonados_procesados']} | Insertados: {$resSocios['abonados_insertados']}");
        }

        // 7. Bajas Formales de Socios (bajasoc.DBF o sociosba.DBF)
        $bajasDbf = $this->buscarArchivo($path, 'bajasoc.DBF') ?: $this->buscarArchivo($path, 'sociosba.DBF');
        if ($bajasDbf) {
            $this->comment("7. Aplicando Bajas Formales ({$bajasDbf})...");
            $resBajas = $migrador->migrarBajas($bajasDbf, $dryRun);
            $this->line("   Total en DBF: {$resBajas['total_bajas_dbf']} | Abonados dados de baja: {$resBajas['abonados_dados_de_baja']}");
        }

        // 8. Contratos de Aportes e Instalaciones (apagua.DBF y alcanta.DBF)
        if (!$sinAportes) {
            $apaguaDbf = $this->buscarArchivo($path, 'apagua.DBF');
            if ($apaguaDbf) {
                $this->comment("8. Migrando Aportes de Agua Potable ({$apaguaDbf})...");
                $resApagua = $migrador->migrarAportesConexiones($apaguaDbf, 'AGUA', $dryRun, $limit);
                $this->line("   Total en DBF: {$resApagua['total_en_dbf']} | Contratos Agua migrados: {$resApagua['insertados']}");
            }

            $alcantaDbf = $this->buscarArchivo($path, 'alcanta.DBF');
            if ($alcantaDbf) {
                $this->comment("9. Migrando Aportes de Alcantarillado ({$alcantaDbf})...");
                $resAlcanta = $migrador->migrarAportesConexiones($alcantaDbf, 'ALCANTARILLADO', $dryRun, $limit);
                $this->line("   Total en DBF: {$resAlcanta['total_en_dbf']} | Contratos Alcantarillado migrados: {$resAlcanta['insertados']}");
            }
        }

        // 10. Convenios de Pago (convenio.DBF y detconve.dbf)
        $convenioDbf = $this->buscarArchivo($path, 'convenio.DBF');
        $detconveDbf = $this->buscarArchivo($path, 'detconve.dbf');
        if ($convenioDbf && $detconveDbf) {
            $this->comment("10. Migrando Convenios de Pago ({$convenioDbf})...");
            $resConvenios = $migrador->migrarConvenios($convenioDbf, $detconveDbf, $dryRun);
            $this->line("   Total en DBF: {$resConvenios['total_convenios_dbf']} | Insertados: {$resConvenios['convenios_insertados']}");
        }

        // 11. Recibos de Caja / Otros conceptos (recibos.DBF)
        if (!$sinRecibos) {
            $recibosDbf = $this->buscarArchivo($path, 'recibos.DBF');
            if ($recibosDbf) {
                $this->comment("11. Migrando Recibos de Otros Conceptos ({$recibosDbf})...");
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

        // 12. Ciclos y Períodos de Facturación (periodos.DBF)
        $periodosDbf = $this->buscarArchivo($path, 'periodos.DBF');
        if ($periodosDbf) {
            $this->comment("12. Migrando Cronograma y Períodos de Facturación ({$periodosDbf})...");
            $resPeriodos = $migrador->migrarPeriodos($periodosDbf, $dryRun);
            $this->line("   Total en DBF: {$resPeriodos['total_en_dbf']} | Períodos registrados: {$resPeriodos['insertados']}");
        }

        // 13. Facturas Reales Emitidas (ventas.DBF)
        if (!$sinFacturas) {
            $ventasDbf = $this->buscarArchivo($path, 'ventas.DBF');
            if ($ventasDbf) {
                $this->comment("13. Migrando Facturas Reales Emitidas ({$ventasDbf})...");
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
            $this->line("  php artisan comercial:migrar-foxpro --confirm");
        } else {
            $this->info("¡Migración real completada e insertada exitosamente en PostgreSQL!");
        }

        return Command::SUCCESS;
    }

    private function buscarArchivo(string $directorio, string $nombre): ?string
    {
        $carpetasABuscar = array_unique([
            $directorio,
            $directorio . '/DATA',
            $directorio . '/data',
            '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026/DATA',
            '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026',
            storage_path('app/legacy_emapa_2026'),
            '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA',
            '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/cr070923',
        ]);

        foreach ($carpetasABuscar as $carpeta) {
            if (!is_dir($carpeta)) {
                continue;
            }
            $candidatos = [
                $carpeta . '/' . $nombre,
                $carpeta . '/' . strtolower($nombre),
                $carpeta . '/' . strtoupper($nombre),
                $carpeta . '/' . ucfirst(strtolower($nombre)),
            ];
            foreach ($candidatos as $c) {
                if (file_exists($c)) {
                    return $c;
                }
            }
        }

        return null;
    }
}
