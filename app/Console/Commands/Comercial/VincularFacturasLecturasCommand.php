<?php

declare(strict_types=1);

namespace App\Console\Commands\Comercial;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VincularFacturasLecturasCommand extends Command
{
    protected $signature = 'comercial:vincular-facturas-lecturas
                            {--path= : Ruta absoluta al archivo operacio.dbf}';

    protected $description = 'Vincula las facturas históricas emitidas con sus correspondientes lecturas mensuales usando operacio.dbf';

    public function handle(): int
    {
        $inicio = microtime(true);
        $this->info('=================================================================');
        $this->info('VINCULACIÓN HISTÓRICA: LECTURAS MENSUALES <-> FACTURAS FISCALES');
        $this->info('=================================================================');

        $rutaDbf = $this->option('path');
        if (empty($rutaDbf)) {
            $rutasCandidatas = [
                '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026/DATA/operacio.dbf',
                storage_path('app/legacy_emapa_2026/operacio.dbf'),
                storage_path('app/migracion_foxpro/DATA_19_09_2026/DATA/operacio.dbf'),
            ];
            foreach ($rutasCandidatas as $rc) {
                if (file_exists($rc)) {
                    $rutaDbf = $rc;
                    break;
                }
            }
        }

        if (!$rutaDbf || !file_exists($rutaDbf)) {
            $this->error("No se encontró el archivo operacio.dbf en: {$rutaDbf}");
            return Command::FAILURE;
        }

        $this->info("Origen de datos: {$rutaDbf}");
        $this->comment("Tamaño: " . round(filesize($rutaDbf) / (1024 * 1024), 2) . " MB");

        // 1. Crear tabla temporal optimizada en PostgreSQL
        $this->line("\n[1/4] Creando tabla temporal en PostgreSQL...");
        DB::statement('DROP TABLE IF EXISTS comercial.tmp_op_fac');
        DB::statement('
            CREATE UNLOGGED TABLE comercial.tmp_op_fac (
                codigo varchar(10),
                periodo varchar(10),
                numero_factura bigint,
                cuf text
            )
        ');

        // 2. Extraer streaming a alta velocidad desde operacio.dbf
        $this->line("[2/4] Extrayendo facturas y CUFs desde operacio.dbf vía streaming...");
        $fp = fopen($rutaDbf, 'rb');
        fseek($fp, 8);
        $hdr = unpack('vheader_len/vrecord_len', fread($fp, 4));
        $recordLen = $hdr['record_len'];
        fseek($fp, $hdr['header_len']);

        $pdo = DB::connection()->getPdo();
        $batch = [];
        $totalExtraidos = 0;
        $chunkSize = 10000;

        $bar = $this->output->createProgressBar(431040);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->setMessage('Leyendo DBF...');
        $bar->start();

        while (!feof($fp)) {
            $buf = fread($fp, $recordLen * 2000);
            if (empty($buf)) {
                break;
            }
            $n = strlen($buf) / $recordLen;
            for ($i = 0; $i < $n; $i++) {
                $rec = substr($buf, $i * $recordLen, $recordLen);
                if (strlen($rec) < $recordLen) {
                    break;
                }

                $bar->advance();

                $fac = (int) trim(substr($rec, 291, 8));
                $cuf = trim(substr($rec, 410, 64));

                if ($fac > 0 || !empty($cuf)) {
                    $per = trim(substr($rec, 1, 7));
                    $cod = str_pad(trim(substr($rec, 24, 5)), 5, '0', STR_PAD_LEFT);
                    $batch[] = "{$cod}\t{$per}\t{$fac}\t{$cuf}\n";
                    $totalExtraidos++;

                    if (count($batch) >= $chunkSize) {
                        $pdo->pgsqlCopyFromArray('comercial.tmp_op_fac', $batch);
                        $batch = [];
                    }
                }
            }
        }
        fclose($fp);

        if (!empty($batch)) {
            $pdo->pgsqlCopyFromArray('comercial.tmp_op_fac', $batch);
            $batch = [];
        }

        $bar->finish();
        $this->line("");
        $this->info("   -> {$totalExtraidos} registros de facturas/CUF cargados a tabla temporal.");

        // 3. Crear índices sobre la tabla temporal para joins instantáneos
        $this->line("\n[3/4] Indexando tabla temporal...");
        DB::statement('CREATE INDEX idx_tmp_op_cuf ON comercial.tmp_op_fac(cuf) WHERE cuf != \'\'');
        DB::statement('CREATE INDEX idx_tmp_op_cod_fac ON comercial.tmp_op_fac(codigo, numero_factura) WHERE numero_factura > 0');
        DB::statement('CREATE INDEX idx_tmp_op_cod_per ON comercial.tmp_op_fac(codigo, periodo)');

        // 4. Ejecutar actualizaciones en lote con JOIN SQL
        $this->line("[4/4] Ejecutando enlace relacional con facturacion.facturas...");

        // Pase 1: Enlace exacto por CUF (Facturación Computarizada / SIAT)
        $afectadosCuf = DB::update('
            UPDATE comercial.lecturas_mensuales l
            SET id_factura = f.id
            FROM comercial.tmp_op_fac tmp
            JOIN comercial.abonados a ON a.codigo = tmp.codigo
            JOIN comercial.periodos_facturacion p ON p.periodo = tmp.periodo
            JOIN facturacion.facturas f ON f.cuf = tmp.cuf
            WHERE l.id_abonado = a.id
              AND l.id_periodo = p.id
              AND l.id_factura IS NULL
              AND tmp.cuf != \'\'
        ');
        $this->info("   -> Enlazadas por CUF único SIAT: " . number_format($afectadosCuf));

        // Pase 2: Enlace por Código Abonado + Número de Factura (Facturación SFV clásica)
        $afectadosNum = DB::update('
            UPDATE comercial.lecturas_mensuales l
            SET id_factura = f.id
            FROM comercial.tmp_op_fac tmp
            JOIN comercial.abonados a ON a.codigo = tmp.codigo
            JOIN comercial.periodos_facturacion p ON p.periodo = tmp.periodo
            JOIN facturacion.facturas f ON f.id_abonado = a.id AND f.numero_factura = tmp.numero_factura
            WHERE l.id_abonado = a.id
              AND l.id_periodo = p.id
              AND l.id_factura IS NULL
              AND tmp.numero_factura > 0
        ');
        $this->info("   -> Enlazadas por N° Factura SFV: " . number_format($afectadosNum));

        // Limpieza de tabla temporal
        DB::statement('DROP TABLE IF EXISTS comercial.tmp_op_fac');

        $totalVinculadas = DB::table('comercial.lecturas_mensuales')->whereNotNull('id_factura')->count();
        $duracion = round(microtime(true) - $inicio, 2);

        $this->info("\n=================================================================");
        $this->info("PROCESO CONCLUIDO CON ÉXITO");
        $this->info("=================================================================");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Facturas/CUF extraídos de FoxPro', number_format($totalExtraidos)],
                ['Lecturas vinculadas por CUF SIAT', number_format($afectadosCuf)],
                ['Lecturas vinculadas por N° Factura SFV', number_format($afectadosNum)],
                ['Total acumulado de lecturas con factura en PostgreSQL', number_format($totalVinculadas)],
                ['Tiempo total de ejecución', "{$duracion} segundos"],
            ]
        );

        return Command::SUCCESS;
    }
}
