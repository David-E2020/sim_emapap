<?php

declare(strict_types=1);

namespace App\Console\Commands\Datos;

use App\Services\Comercial\FoxProMigradorService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrarSegundoPlanoCommand extends Command
{
    protected $signature = 'datos:migrar-segundo-plano {job_id}';
    protected $description = 'Ejecuta una migración de FoxPro a PostgreSQL en un hilo/proceso independiente en segundo plano';

    public function handle(FoxProMigradorService $migrador): int
    {
        $jobId = (string) $this->argument('job_id');
        $jobFile = storage_path("app/migracion_jobs/{$jobId}.json");
        $cancelFile = storage_path("app/migracion_jobs/{$jobId}.cancel");

        if (!file_exists($jobFile)) {
            $this->error("No se encontró el archivo de configuración para el Job: {$jobId}");
            return Command::FAILURE;
        }

        $config = json_decode((string) file_get_contents($jobFile), true) ?: [];
        $ruta = $config['ruta'] ?? '';
        $esSimulacion = (bool) ($config['es_simulacion'] ?? true);
        $limite = (int) ($config['limite'] ?? 0);

        // Registrar PID para control de cancelación
        $config['pid'] = getmypid();
        $config['estado'] = 'PROCESANDO';
        $config['modulo_actual'] = 'Preparando directorios...';
        file_put_contents($jobFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '2048M');
        DB::disableQueryLog();

        $ordenLogico = [
            'zonas', 'calles', 'estados_abonado', 'conceptos_ingresos',
            'tarifas', 'abonados', 'bajas_socios', 'aportes_agua',
            'aportes_alcantarillado', 'convenios', 'recibos', 'periodos',
            'facturas', 'lecturas', 'plan_cuentas', 'comprobantes',
            'compras', 'materiales_almacen', 'rubros_activos', 'bienes_activos'
        ];

        $modulos = !empty($config['modulos']) ? $config['modulos'] : $ordenLogico;
        // Ordenar respetando dependencias
        usort($modulos, function ($a, $b) use ($ordenLogico) {
            $idxA = array_search($a, $ordenLogico);
            $idxB = array_search($b, $ordenLogico);
            return ($idxA === false ? 999 : $idxA) <=> ($idxB === false ? 999 : $idxB);
        });

        // Helper para resolver archivos DBF de forma insensible a mayúsculas
        $resolverArchivo = function (array $nombresPosibles) use ($ruta): ?string {
            if (!file_exists($ruta) || !is_dir($ruta)) {
                return null;
            }
            $files = @scandir($ruta) ?: [];
            foreach ($nombresPosibles as $posible) {
                foreach ($files as $f) {
                    if (strcasecmp($f, $posible) === 0) {
                        return "{$ruta}/{$f}";
                    }
                }
            }
            return null;
        };

        $totalModulos = count($modulos);
        $procesados = 0;
        $inicioGlobal = microtime(true);

        $this->actualizarProgreso($jobFile, $config, 0, 'Iniciando módulos...', 'INFO', "Iniciando migración en segundo plano ({$totalModulos} módulos)...");

        foreach ($modulos as $mod) {
            // Chequear si se solicitó cancelación
            if (file_exists($cancelFile)) {
                $this->actualizarProgreso($jobFile, $config, $this->calcProgreso($procesados, $totalModulos), $mod, 'CANCELADO', 'Proceso detenido inmediatamente a petición del usuario.');
                $config['estado'] = 'CANCELADO';
                file_put_contents($jobFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                @unlink($cancelFile);
                return Command::SUCCESS;
            }

            $labelMod = $this->getLabelModulo($mod);
            $this->actualizarProgreso($jobFile, $config, $this->calcProgreso($procesados, $totalModulos), $mod, 'PROCESANDO', "Procesando {$labelMod}...");

            $estadoModulo = 'EXITO';
            $mensajeModulo = '';
            $regProcesados = 0;
            $regCorrectos = 0;
            $regErroneos = 0;
            $detalles = [];

            try {
                switch ($mod) {
                    case 'zonas':
                        $path = $resolverArchivo(['zonas.dbf']);
                        if ($path) {
                            $res = $migrador->migrarZonas($path, $esSimulacion);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Zonas procesadas: {$regProcesados} (Insertadas: {$regCorrectos})";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo zonas.dbf no encontrado.';
                        }
                        break;

                    case 'calles':
                        $path = $resolverArchivo(['calles.dbf']);
                        if ($path) {
                            $res = $migrador->migrarCalles($path, $esSimulacion);
                            $regProcesados = $res['calles_procesadas'];
                            $regCorrectos = $res['calles_insertadas'];
                            $mensajeModulo = "Calles procesadas: {$regProcesados} (Insertadas: {$regCorrectos})";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo calles.dbf no encontrado.';
                        }
                        break;

                    case 'estados_abonado':
                        $path = $resolverArchivo(['estado.dbf']);
                        if ($path) {
                            $res = $migrador->migrarEstadosAbonados($path, $esSimulacion);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Estados procesados: {$regProcesados} (Insertados: {$regCorrectos})";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo estado.dbf no encontrado.';
                        }
                        break;

                    case 'conceptos_ingresos':
                        $path = $resolverArchivo(['concepin.dbf', 'concepte.dbf']);
                        if ($path) {
                            $res = $migrador->migrarConceptosIngresos($path, $esSimulacion);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Conceptos de ingreso: {$regProcesados} (Insertados: {$regCorrectos})";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo concepin.dbf no encontrado.';
                        }
                        break;

                    case 'tarifas':
                        $path = $resolverArchivo(['categor.dbf', 'tarifa.dbf', 'tarifas.dbf']);
                        if ($path) {
                            $res = $migrador->migrarTarifas($path, $esSimulacion);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Tarifas procesadas: {$regProcesados} (Insertadas: {$regCorrectos})";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo categor.dbf / tarifa.dbf no encontrado.';
                        }
                        break;

                    case 'abonados':
                        $path = $resolverArchivo(['socios.dbf']);
                        if ($path) {
                            $res = $migrador->migrarAbonados($path, $esSimulacion, $limite);
                            $regProcesados = $res['abonados_procesados'];
                            $regCorrectos = $res['abonados_insertados'];
                            $mensajeModulo = "Abonados procesados: {$regProcesados} (Insertados: {$regCorrectos})";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo socios.dbf no encontrado.';
                        }
                        break;

                    case 'bajas_socios':
                        $path = $resolverArchivo(['bajasoc.dbf', 'sociosba.dbf']);
                        if ($path) {
                            $res = $migrador->migrarBajas($path, $esSimulacion);
                            $regProcesados = $res['total_bajas_dbf'];
                            $regCorrectos = $res['abonados_dados_de_baja'];
                            $mensajeModulo = "Bajas aplicadas: {$regCorrectos} de {$regProcesados} en DBF";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo bajasoc.dbf no encontrado.';
                        }
                        break;

                    case 'aportes_agua':
                        $path = $resolverArchivo(['apagua.dbf']);
                        if ($path) {
                            $res = $migrador->migrarAportesConexiones($path, 'AGUA', $esSimulacion, $limite);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Aportes Agua migrados: {$regCorrectos} de {$regProcesados}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo apagua.dbf no encontrado.';
                        }
                        break;

                    case 'aportes_alcantarillado':
                        $path = $resolverArchivo(['alcanta.dbf']);
                        if ($path) {
                            $res = $migrador->migrarAportesConexiones($path, 'ALCANTARILLADO', $esSimulacion, $limite);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Aportes Alcantarillado migrados: {$regCorrectos} de {$regProcesados}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo alcanta.dbf no encontrado.';
                        }
                        break;

                    case 'convenios':
                        $pathConv = $resolverArchivo(['convenio.dbf']);
                        $pathDet = $resolverArchivo(['detconve.dbf']);
                        if ($pathConv && $pathDet) {
                            $res = $migrador->migrarConvenios($pathConv, $pathDet, $esSimulacion);
                            $regProcesados = $res['total_convenios_dbf'];
                            $regCorrectos = $res['convenios_insertados'];
                            $mensajeModulo = "Convenios migrados: {$regCorrectos} de {$regProcesados}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivos convenio.dbf / detconve.dbf no encontrados.';
                        }
                        break;

                    case 'recibos':
                        $path = $resolverArchivo(['recibos.dbf']);
                        if ($path) {
                            $res = $migrador->migrarRecibosOtros($path, $esSimulacion, null);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['recibos_migrados'];
                            $mensajeModulo = "Recibos migrados: {$regCorrectos} de {$regProcesados}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo recibos.dbf no encontrado.';
                        }
                        break;

                    case 'periodos':
                        $path = $resolverArchivo(['periodos.dbf']);
                        if ($path) {
                            $res = $migrador->migrarPeriodos($path, $esSimulacion);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Períodos registrados: {$regCorrectos} de {$regProcesados}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo periodos.dbf no encontrado.';
                        }
                        break;

                    case 'facturas':
                        $path = $resolverArchivo(['ventas.dbf', 'ventahis.dbf']);
                        if ($path) {
                            $res = $migrador->migrarFacturasVentas($path, $esSimulacion, $limite, null);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['facturas_migradas'];
                            $mensajeModulo = "Facturas migradas: {$regCorrectos} de {$regProcesados}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo ventas.dbf no encontrado.';
                        }
                        break;

                    case 'lecturas':
                        $path = $resolverArchivo(['operacio.dbf', 'operahis.dbf']);
                        if ($path) {
                            $res = $migrador->migrarOperacionesDbf($path, $esSimulacion, null, $limite, null);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['lecturas_migradas'];
                            $extra = !empty($res['facturas_vinculadas']) ? ", Facturas vinculadas: {$res['facturas_vinculadas']}" : '';
                            $mensajeModulo = "Lecturas migradas: {$regCorrectos} de {$regProcesados}{$extra}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo operacio.dbf no encontrado.';
                        }
                        break;

                    case 'plan_cuentas':
                        $path = $resolverArchivo(['cuentas.dbf', 'plancta.dbf']);
                        if ($path) {
                            $res = $migrador->migrarPlanCuentas($path, $esSimulacion, $limite);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Plan de Cuentas: {$regCorrectos} de {$regProcesados}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo cuentas.dbf no encontrado.';
                        }
                        break;

                    case 'comprobantes':
                        $pathDiario = $resolverArchivo(['diariotr.dbf']);
                        $pathGlosas = $resolverArchivo(['glosastr.dbf', 'glosas.dbf']);
                        if ($pathDiario) {
                            $res = $migrador->migrarComprobantesDiario($pathDiario, $pathGlosas, $esSimulacion, $limite);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Comprobantes Diario: {$regCorrectos} líneas (Cabeceras: {$res['comprobantes_cabeceras']})";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo diariotr.dbf no encontrado.';
                        }
                        break;

                    case 'compras':
                        $path = $resolverArchivo(['compras.dbf', 'comprasC.dbf']);
                        if ($path) {
                            $res = $migrador->migrarFacturasCompra($path, $esSimulacion, $limite);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Facturas de Compra: {$regCorrectos} de {$regProcesados}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo compras.dbf no encontrado.';
                        }
                        break;

                    case 'materiales_almacen':
                        $path = $resolverArchivo(['almacen.dbf', 'articulos.dbf']);
                        if ($path) {
                            $res = $migrador->migrarMaterialesAlmacen($path, $esSimulacion, $limite);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Materiales de Almacén: {$regCorrectos} de {$regProcesados}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo almacen.dbf no encontrado.';
                        }
                        break;

                    case 'rubros_activos':
                        $path = $resolverArchivo(['afrubros.dbf', 'rubros.dbf']);
                        if ($path) {
                            $res = $migrador->migrarRubrosActivos($path, $esSimulacion);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Rubros Activos Fijos: {$regCorrectos} de {$regProcesados}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo afrubros.dbf no encontrado.';
                        }
                        break;

                    case 'bienes_activos':
                        $path = $resolverArchivo(['afijo.dbf', 'afijo01.dbf']);
                        if ($path) {
                            $res = $migrador->migrarBienesActivos($path, $esSimulacion, $limite);
                            $regProcesados = $res['total_en_dbf'];
                            $regCorrectos = $res['insertados'];
                            $mensajeModulo = "Bienes y Activos Fijos: {$regCorrectos} de {$regProcesados}";
                            $detalles = $res;
                        } else {
                            $estadoModulo = 'ADVERTENCIA';
                            $mensajeModulo = 'Archivo afijo.dbf no encontrado.';
                        }
                        break;

                    default:
                        $estadoModulo = 'INFO';
                        $mensajeModulo = "Módulo {$mod} completado.";
                        break;
                }
            } catch (\Throwable $e) {
                $estadoModulo = 'ERROR';
                $mensajeModulo = "Excepción en {$mod}: " . $e->getMessage();
                Log::error("Error en migración background {$mod}: " . $e->getMessage());
            }

            // Registrar en base de datos si la tabla existe
            try {
                DB::table('migracion.logs')->insert([
                    'job_id' => $jobId,
                    'modulo' => $mod,
                    'tabla_origen' => $mod,
                    'tabla_destino' => $mod,
                    'es_simulacion' => $esSimulacion,
                    'registros_procesados' => $regProcesados,
                    'registros_correctos' => $regCorrectos,
                    'registros_erroneos' => $regErroneos,
                    'mensaje' => $mensajeModulo,
                    'detalles_json' => !empty($detalles) ? json_encode($detalles) : null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } catch (\Throwable $ex) {
                Log::warning("No se pudo registrar log de migración para {$mod}: " . $ex->getMessage());
            }

            $procesados++;
            $pct = $this->calcProgreso($procesados, $totalModulos);
            $this->actualizarProgreso($jobFile, $config, $pct, $mod, $estadoModulo, $mensajeModulo);
        }

        $duracion = round(microtime(true) - $inicioGlobal, 2);
        $msgFinal = $esSimulacion
            ? "Simulación finalizada exitosamente en {$duracion}s. La BD no fue modificada."
            : "Migración oficial completada exitosamente en {$duracion}s.";

        $this->actualizarProgreso($jobFile, $config, 100, 'FINALIZADO', 'EXITO', $msgFinal);
        $config['estado'] = 'COMPLETADO';
        $config['duracion_segundos'] = $duracion;
        file_put_contents($jobFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        @unlink($cancelFile);
        return Command::SUCCESS;
    }

    private function calcProgreso(int $procesados, int $total): int
    {
        if ($total <= 0) return 100;
        return (int) min(100, round(($procesados / $total) * 100));
    }

    private function actualizarProgreso(string $jobFile, array &$config, int $progreso, string $modulo, string $estado, string $mensaje): void
    {
        $hora = Carbon::now()->format('H:i:s');
        $config['progreso'] = $progreso;
        $config['modulo_actual'] = $modulo;
        $config['ultimo_estado'] = $estado;
        $config['logs'][] = [
            'hora' => $hora,
            'id' => $modulo,
            'estado' => $estado,
            'mensaje' => $mensaje,
        ];

        // Mantener últimos 100 logs
        if (count($config['logs']) > 100) {
            $config['logs'] = array_slice($config['logs'], -100);
        }

        @file_put_contents($jobFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function getLabelModulo(string $mod): string
    {
        $labels = [
            'zonas' => 'Zonas Tarifarias',
            'calles' => 'Calles y Avenidas',
            'estados_abonado' => 'Estados de Abonado',
            'conceptos_ingresos' => 'Conceptos de Otros Ingresos',
            'tarifas' => 'Categorías y Tarifas',
            'abonados' => 'Abonados y Medidores',
            'bajas_socios' => 'Bajas Formales de Socios',
            'aportes_agua' => 'Aportes Agua Potable',
            'aportes_alcantarillado' => 'Aportes Alcantarillado',
            'convenios' => 'Convenios de Pago',
            'recibos' => 'Recibos de Caja',
            'periodos' => 'Períodos de Facturación',
            'facturas' => 'Facturas Computarizadas',
            'lecturas' => 'Lecturas y Consumos',
            'plan_cuentas' => 'Plan de Cuentas',
            'comprobantes' => 'Comprobantes Contables',
            'compras' => 'Compras',
            'materiales_almacen' => 'Materiales de Almacén',
            'rubros_activos' => 'Rubros de Activos Fijos',
            'bienes_activos' => 'Bienes de Activos Fijos',
        ];
        return $labels[$mod] ?? ucfirst(str_replace('_', ' ', $mod));
    }
}
