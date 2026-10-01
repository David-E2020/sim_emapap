<?php

declare(strict_types=1);

namespace App\Console\Commands\Facturacion;

use App\Services\Facturacion\CufService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SincronizarHorasCufCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturacion:sincronizar-horas-cuf
                            {--gestion= : Filtrar por gestión/año específico (ej. 2026)}
                            {--chunk=2000 : Tamaño del lote por actualización}
                            {--dry-run : Ejecutar simulación sin guardar cambios en base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decodifica el CUF oficial del SIN y actualiza la fecha, hora, minuto y segundo reales de emisión de las facturas electrónicas';

    public function handle(CufService $cufService): int
    {
        $this->info('========================================================================');
        $this->info('  EMAPAP - Sincronización de Horas Reales de Emisión desde CUF (SIAT)');
        $this->info('========================================================================');

        $gestion = $this->option('gestion') ? (int) $this->option('gestion') : null;
        $chunkSize = max(100, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn(' Modo SIMULACIÓN (--dry-run activo): no se modificarán registros.');
        }

        // 1. Auditoría preliminar de CUFs duplicados
        $this->line("\n[1/3] Verificando integridad de CUFs y detección de duplicados...");
        $duplicadosQuery = DB::table('facturacion.facturas')
            ->select('cuf', DB::raw('COUNT(*) as cantidad'))
            ->whereNotNull('cuf')
            ->whereRaw("cuf NOT LIKE 'SFV%'")
            ->whereRaw('LENGTH(cuf) >= 42')
            ->groupBy('cuf')
            ->havingRaw('COUNT(*) > 1');

        $totalCufsDuplicados = $duplicadosQuery->count();
        if ($totalCufsDuplicados > 0) {
            $this->error("  ALERTA: Se detectaron {$totalCufsDuplicados} CUF(s) con registros duplicados en la base de datos.");
            $muestras = $duplicadosQuery->limit(5)->get();
            foreach ($muestras as $m) {
                $this->warn("    - CUF: {$m->cuf} (repetido {$m->cantidad} veces)");
            }
        } else {
            $this->info('  ✓ Integridad confirmada: 0 CUFs duplicados en el registro fiscal SIAT.');
        }

        // 2. Consulta de facturas candidatas a sincronización
        $this->line("\n[2/3] Identificando facturas con hora 00:00:00 o pendientes de sincronización...");
        $query = DB::table('facturacion.facturas')
            ->whereNotNull('cuf')
            ->whereRaw("cuf NOT LIKE 'SFV%'")
            ->whereRaw('LENGTH(cuf) >= 42')
            ->whereRaw("fecha_emision::text LIKE '%00:00:00%'");

        if ($gestion) {
            $query->whereRaw('EXTRACT(YEAR FROM fecha_emision) = ?', [$gestion]);
        }

        $totalPendientes = $query->count();
        $this->line("  Total de facturas a procesar: <comment>{$totalPendientes}</comment>");

        if ($totalPendientes === 0) {
            $this->info('  ✓ Todas las facturas ya cuentan con su hora real de emisión sincronizada.');
            return Command::SUCCESS;
        }

        // 3. Procesamiento en lotes de alto rendimiento
        $this->line("\n[3/3] Decodificando CUFs y actualizando horas de emisión en lotes de {$chunkSize}...");
        $bar = $this->output->createProgressBar($totalPendientes);
        $bar->start();

        $procesadas = 0;
        $actualizadas = 0;
        $erroresDecodificacion = 0;

        // Iterar usando cursor paginado por id ascendente para evitar problemas de offset con actualizaciones concurrentes
        $ultimoId = 0;

        while (true) {
            $loteQuery = DB::table('facturacion.facturas')
                ->select('id', 'numero_factura', 'cuf', 'fecha_emision')
                ->where('id', '>', $ultimoId)
                ->whereNotNull('cuf')
                ->whereRaw("cuf NOT LIKE 'SFV%'")
                ->whereRaw('LENGTH(cuf) >= 42')
                ->whereRaw("fecha_emision::text LIKE '%00:00:00%'");

            if ($gestion) {
                $loteQuery->whereRaw('EXTRACT(YEAR FROM fecha_emision) = ?', [$gestion]);
            }

            $lote = $loteQuery->orderBy('id', 'asc')
                ->limit($chunkSize)
                ->get();

            if ($lote->isEmpty()) {
                break;
            }

            $values = [];
            foreach ($lote as $row) {
                $ultimoId = $row->id;
                $procesadas++;

                $fechaReal = $cufService->extraerFechaHoraDesdeCuf($row->cuf);
                if ($fechaReal instanceof Carbon) {
                    $fechaStr = $fechaReal->format('Y-m-d H:i:s');
                    $values[] = "({$row->id}, '{$fechaStr}')";
                    $actualizadas++;
                } else {
                    $erroresDecodificacion++;
                }
            }

            if (!$dryRun && !empty($values)) {
                $valuesSql = implode(',', $values);
                DB::statement("
                    UPDATE facturacion.facturas AS f
                    SET fecha_emision = v.nueva_fecha::timestamp
                    FROM (VALUES {$valuesSql}) AS v(id, nueva_fecha)
                    WHERE f.id = v.id
                ");
            }

            $bar->advance(count($lote));
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('========================================================================');
        $this->info('  RESULTADOS DE LA SINCRONIZACIÓN');
        $this->info('========================================================================');
        $this->line("  * Facturas evaluadas         : {$procesadas}");
        $this->line("  * Horas reales actualizadas   : {$actualizadas}");
        $this->line("  * Errores de decodificación   : {$erroresDecodificacion}");
        $this->line("  * Alertas de CUFs duplicados  : {$totalCufsDuplicados}");
        $this->info('========================================================================');

        return Command::SUCCESS;
    }
}
