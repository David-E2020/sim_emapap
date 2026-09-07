<?php

declare(strict_types=1);

namespace App\Console\Commands\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\PeriodoFacturacion;
use App\Services\Comercial\FoxProMigradorService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarLecturasPendientesCommand extends Command
{
    protected $signature = 'comercial:migrar-lecturas-pendientes
                            {--confirm : Confirmar inserción real en la base de datos}
                            {--limit=0 : Límite de abonados o lecturas}';

    protected $description = 'Pobla periodos de facturación y lecturas pendientes reales desde FoxPro para caja ventanilla';

    public function handle(FoxProMigradorService $migrador): int
    {
        $dryRun = !$this->option('confirm');
        $this->info("=================================================================");
        $this->info("POBLACIÓN DE PERIODOS Y LECTURAS PENDIENTES - CAJA FACTIFIV");
        $this->info("Modo: " . ($dryRun ? "DRY-RUN (Simulación)" : "PRODUCCIÓN (Inserción en PostgreSQL)"));
        $this->info("=================================================================");

        // 1. Poblar periodos_facturacion desde periodos.DBF
        $periodosDbf = storage_path('app/legacy_emapa_2026/periodos.DBF');
        if (!file_exists($periodosDbf)) {
            $periodosDbf = '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/cr070923/periodos.DBF';
        }

        $this->comment("1. Migrando Periodos de Facturación ({$periodosDbf})...");
        $dataPeriodos = $migrador->leerDbf($periodosDbf);
        $periodosInsertados = 0;

        foreach ($dataPeriodos['records'] as $r) {
            $perNom = trim($r['PERIODO'] ?? '');
            if (empty($perNom) || !str_contains($perNom, '/')) {
                continue;
            }

            $partes = explode('/', $perNom);
            $mes = (int) ($partes[0] ?? 1);
            $gestion = (int) ($partes[1] ?? 2023);

            $fechadStr = trim($r['FECHAD'] ?? '');
            $fechahStr = trim($r['FECHAH'] ?? '');
            $fechavStr = trim($r['FECHAV'] ?? '');

            $fechaInicio = $this->parseFechaDbf($fechadStr) ?: Carbon::create($gestion, $mes, 1)->toDateString();
            $fechaFin = $this->parseFechaDbf($fechahStr) ?: Carbon::create($gestion, $mes, 1)->endOfMonth()->toDateString();
            $fechaVenc = $this->parseFechaDbf($fechavStr) ?: Carbon::create($gestion, $mes, 1)->addMonth()->day(25)->toDateString();

            if (!$dryRun) {
                PeriodoFacturacion::firstOrCreate(
                    ['periodo' => $perNom],
                    [
                        'mes' => $mes,
                        'gestion' => $gestion,
                        'fecha_inicio_consumo' => $fechaInicio,
                        'fecha_fin_consumo' => $fechaFin,
                        'fecha_vencimiento_pago' => $fechaVenc,
                        'estado' => ($gestion < 2026 || ($gestion == 2026 && $mes < 8)) ? 'CERRADO' : 'ABIERTO',
                    ]
                );
            }
            $periodosInsertados++;
        }
        $this->line("   Total periodos procesados/insertados: {$periodosInsertados}");

        // 2. Poblar lecturas pendientes desde &codios.DBF si existe
        $codiosDbf = '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/cr070923/&codios.DBF';
        $lecturasFromDbf = 0;

        if (file_exists($codiosDbf)) {
            $this->comment("2. Migrando lecturas impagas reales desde &codios.DBF...");
            $abonadosMap = Abonado::pluck('id', 'codigo')->toArray();
            $periodosMap = PeriodoFacturacion::pluck('id', 'periodo')->toArray();

            $fp = fopen($codiosDbf, 'rb');
            $header = fread($fp, 32);
            $unpacked = unpack('Vnum_records/vheader_len/vrecord_len', substr($header, 4, 8));
            $headerLen = $unpacked['header_len'];
            $recordLen = $unpacked['record_len'];

            fseek($fp, 32);
            $offset = 1;
            $posMap = [];
            while (true) {
                $fData = fread($fp, 32);
                if (!$fData || ord($fData[0]) === 0x0D) break;
                $fName = trim(substr($fData, 0, 11));
                $fLen = ord($fData[16]);
                $posMap[$fName] = ['offset' => $offset, 'len' => $fLen];
                $offset += $fLen;
            }

            fseek($fp, $headerLen);
            $batch = [];

            while (!feof($fp)) {
                $row = fread($fp, $recordLen);
                if (strlen($row) < $recordLen) break;
                if (ord($row[0]) === 0x2A) continue; // deleted

                $pagado = trim(substr($row, $posMap['PAGADO']['offset'] ?? 0, $posMap['PAGADO']['len'] ?? 1));
                if ($pagado === 'S') {
                    continue; // solo migramos impagas/deudas
                }

                $codigo = trim(substr($row, $posMap['CODIGO']['offset'] ?? 0, $posMap['CODIGO']['len'] ?? 5));
                $codigoPad = str_pad($codigo, 5, '0', STR_PAD_LEFT);
                $idAbonado = $abonadosMap[$codigoPad] ?? null;
                if (!$idAbonado) {
                    continue;
                }

                $perStr = trim(substr($row, $posMap['PERIODO']['offset'] ?? 0, $posMap['PERIODO']['len'] ?? 7));
                $idPeriodo = $periodosMap[$perStr] ?? null;
                if (!$idPeriodo) {
                    continue;
                }

                $anterior = (float) trim(substr($row, $posMap['ANTERIOR']['offset'] ?? 0, $posMap['ANTERIOR']['len'] ?? 8));
                $actual = (float) trim(substr($row, $posMap['ACTUAL']['offset'] ?? 0, $posMap['ACTUAL']['len'] ?? 8));
                $consumo = (float) trim(substr($row, $posMap['CONSUMO']['offset'] ?? 0, $posMap['CONSUMO']['len'] ?? 8));
                $montoAgua = (float) trim(substr($row, $posMap['IMPAGUA']['offset'] ?? 0, $posMap['IMPAGUA']['len'] ?? 8));
                $montoAlca = (float) trim(substr($row, $posMap['IMPALCA']['offset'] ?? 0, $posMap['IMPALCA']['len'] ?? 8));
                $descto = (float) trim(substr($row, $posMap['DESCTO3']['offset'] ?? 0, $posMap['DESCTO3']['len'] ?? 8));
                $total = $montoAgua + $montoAlca - $descto;

                $batch[] = [
                    'id_periodo' => $idPeriodo,
                    'id_abonado' => $idAbonado,
                    'lectura_anterior' => $anterior,
                    'lectura_actual' => $actual,
                    'consumo_m3' => $consumo,
                    'monto_agua' => $montoAgua,
                    'monto_alcantarillado' => $montoAlca,
                    'monto_descuento_ley1886' => $descto,
                    'total_facturado' => max(0, $total),
                    'estado_pago' => 'PENDIENTE',
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'MIGRACION_FOXPRO',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ];

                if (count($batch) >= 1000) {
                    if (!$dryRun) {
                        DB::table('comercial.lecturas_mensuales')->upsert(
                            $batch,
                            ['id_periodo', 'id_abonado'],
                            ['monto_agua', 'monto_alcantarillado', 'monto_descuento_ley1886', 'total_facturado', 'estado_pago']
                        );
                    }
                    $lecturasFromDbf += count($batch);
                    $batch = [];
                }
            }
            fclose($fp);

            if (!empty($batch)) {
                if (!$dryRun) {
                    DB::table('comercial.lecturas_mensuales')->upsert(
                        $batch,
                        ['id_periodo', 'id_abonado'],
                        ['monto_agua', 'monto_alcantarillado', 'monto_descuento_ley1886', 'total_facturado', 'estado_pago']
                    );
                }
                $lecturasFromDbf += count($batch);
            }
            $this->line("   Total lecturas impagas migradas desde &codios.DBF: {$lecturasFromDbf}");
        }

        // 3. Garantizar que abonados con meses_mora > 0 tengan sus periodos pendientes generados
        $this->comment("3. Verificando abonados con meses en mora...");
        $periodosOrdenados = PeriodoFacturacion::orderBy('gestion', 'desc')->orderBy('mes', 'desc')->take(24)->get();
        $categorias = CategoriaTarifaria::all()->keyBy('id');

        $abonadosEnMora = Abonado::where('meses_mora', '>', 0)->get();
        $generadasAdicionales = 0;

        foreach ($abonadosEnMora as $ab) {
            $numMora = min($ab->meses_mora, 24);
            $existentes = LecturaMensual::where('id_abonado', $ab->id)->where('estado_pago', 'PENDIENTE')->count();

            if ($existentes < $numMora) {
                $faltan = $numMora - $existentes;
                $cat = $categorias->get($ab->id_categoria) ?: $categorias->first();
                $tarifaAgua = (float) ($cat->tarifa_minima ?? 12.60);
                $tarifaAlca = $ab->tiene_alcantarillado ? (float) ($cat->tarifa_alcantarillado ?? 2.00) : 0.00;
                $descto = $ab->es_tercera_edad ? round($tarifaAgua * 0.20, 2) : 0.00;
                $totalMes = round($tarifaAgua + $tarifaAlca - $descto, 2);

                $periodosDisponibles = $periodosOrdenados->take($numMora)->reverse();
                $insertedForAb = 0;

                foreach ($periodosDisponibles as $per) {
                    if ($insertedForAb >= $faltan) break;

                    $yaExiste = LecturaMensual::where('id_abonado', $ab->id)->where('id_periodo', $per->id)->exists();
                    if (!$yaExiste) {
                        if (!$dryRun) {
                            LecturaMensual::create([
                                'id_periodo' => $per->id,
                                'id_abonado' => $ab->id,
                                'id_medidor' => $ab->id_medidor_actual,
                                'lectura_anterior' => 0.00,
                                'lectura_actual' => 0.00,
                                'consumo_m3' => (float) ($cat->volumen_base ?? 6.00),
                                'monto_agua' => $tarifaAgua,
                                'monto_alcantarillado' => $tarifaAlca,
                                'monto_descuento_ley1886' => $descto,
                                'total_facturado' => $totalMes,
                                'estado_pago' => 'PENDIENTE',
                                '_estado' => 'ACTIVO',
                                '_transaccion' => 'SINCRONIZACION_MORA',
                                '_usuario_creacion' => 1,
                            ]);
                        }
                        $generadasAdicionales++;
                        $insertedForAb++;
                    }
                }
            }
        }

        $this->info("   Lecturas pendientes sincronizadas para abonados en mora: {$generadasAdicionales}");

        if (!$dryRun) {
            $this->info('   Recalculando y sincronizando saldos de deuda en comercial.abonados...');
            \Illuminate\Support\Facades\DB::statement("
                UPDATE comercial.abonados a
                SET 
                    saldo_deuda = COALESCE(sub.total_deuda, 0.00),
                    meses_mora = COALESCE(sub.total_meses, 0)
                FROM (
                    SELECT id_abonado, ROUND(SUM(total_facturado)::numeric, 2) as total_deuda, COUNT(*) as total_meses
                    FROM comercial.lecturas_mensuales
                    WHERE estado_pago = 'PENDIENTE'
                    GROUP BY id_abonado
                ) sub
                WHERE a.id = sub.id_abonado;
            ");

            \Illuminate\Support\Facades\DB::statement("
                UPDATE comercial.abonados
                SET saldo_deuda = 0.00, meses_mora = 0
                WHERE id NOT IN (
                    SELECT DISTINCT id_abonado 
                    FROM comercial.lecturas_mensuales 
                    WHERE estado_pago = 'PENDIENTE'
                );
            ");
        }

        $this->info("MIGRACIÓN Y SINCRONIZACIÓN DE LECTURAS FINALIZADA EXITOSAMENTE.");
        return 0;
    }

    protected function parseFechaDbf(string $str): ?string
    {
        if (strlen($str) === 8 && is_numeric($str)) {
            $y = substr($str, 0, 4);
            $m = substr($str, 4, 2);
            $d = substr($str, 6, 2);
            if ((int)$m >= 1 && (int)$m <= 12 && (int)$d >= 1 && (int)$d <= 31) {
                return "{$y}-{$m}-{$d}";
            }
        }
        return null;
    }
}
