<?php

declare(strict_types=1);

namespace App\Http\Controllers\Datos;

use App\Http\Controllers\Controller;
use App\Services\Comercial\FoxProMigradorService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class MigracionFoxProController extends Controller
{
    protected FoxProMigradorService $migrador;

    public function __construct(FoxProMigradorService $migrador)
    {
        $this->migrador = $migrador;
    }

    /**
     * Mapeo integral de tablas FoxPro vs PostgreSQL ordenadas por esquema.
     */
    protected function getMapeoTablas(): array
    {
        return [
            // ==========================================
            // ESQUEMA COMERCIAL
            // ==========================================
            [
                'id' => 'calles',
                'modulo' => 'comercial',
                'label' => 'Calles y Avenidas',
                'schema' => 'comercial',
                'table' => 'calles',
                'archivos_dbf' => ['calles.dbf'],
                'icono' => 'mdi-road-variant',
            ],
            [
                'id' => 'zonas',
                'modulo' => 'comercial',
                'label' => 'Zonas Tarifarias',
                'schema' => 'comercial',
                'table' => 'zonas',
                'archivos_dbf' => ['zonas.dbf'],
                'icono' => 'mdi-map-marker-multiple',
            ],
            [
                'id' => 'estados_abonado',
                'modulo' => 'comercial',
                'label' => 'Estados de Abonado (Paramétrica)',
                'schema' => 'public',
                'table' => 'parametricas',
                'filtro_sql' => ['param_tabla' => 'TABLA_COMERCIAL_ESTADOS_ABONADO', 'param_codigo' => ['!=', 'ORIGEN']],
                'archivos_dbf' => ['estado.dbf'],
                'icono' => 'mdi-tag-check-outline',
            ],
            [
                'id' => 'conceptos_ingresos',
                'modulo' => 'comercial',
                'label' => 'Conceptos / Otros Ingresos (Paramétrica)',
                'schema' => 'public',
                'table' => 'parametricas',
                'filtro_sql' => ['param_tabla' => 'TABLA_COMERCIAL_CONCEPTOS_OTROS_INGRESOS', 'param_codigo' => ['!=', 'ORIGEN']],
                'archivos_dbf' => ['concepin.dbf', 'concepte.dbf'],
                'icono' => 'mdi-cash-plus',
            ],
            [
                'id' => 'tarifas',
                'modulo' => 'comercial',
                'label' => 'Categorías y Tarifas',
                'schema' => 'comercial',
                'table' => 'categorias_tarifarias',
                'archivos_dbf' => ['categor.dbf', 'tarifa.dbf', 'tarifas.dbf'],
                'icono' => 'mdi-currency-usd',
            ],
            [
                'id' => 'abonados',
                'modulo' => 'comercial',
                'label' => 'Abonados / Socios',
                'schema' => 'comercial',
                'table' => 'abonados',
                'archivos_dbf' => ['socios.dbf'],
                'icono' => 'mdi-account-group',
            ],
            [
                'id' => 'aportes_agua',
                'modulo' => 'comercial',
                'label' => 'Aportes Conexión Agua',
                'schema' => 'comercial',
                'table' => 'aportes_conexiones',
                'filtro_sql' => ['tipo_servicio' => 'AGUA'],
                'archivos_dbf' => ['apagua.dbf'],
                'icono' => 'mdi-water',
            ],
            [
                'id' => 'aportes_alcantarillado',
                'modulo' => 'comercial',
                'label' => 'Aportes Alcantarillado',
                'schema' => 'comercial',
                'table' => 'aportes_conexiones',
                'filtro_sql' => ['tipo_servicio' => 'ALCANTARILLADO'],
                'archivos_dbf' => ['alcanta.dbf'],
                'icono' => 'mdi-water-check',
            ],
            [
                'id' => 'bajas_socios',
                'modulo' => 'comercial',
                'label' => 'Abonados Dados de Baja',
                'schema' => 'comercial',
                'table' => 'abonados_bajas',
                'archivos_dbf' => ['bajasoc.dbf', 'sociosba.dbf'],
                'icono' => 'mdi-account-cancel',
            ],
            [
                'id' => 'convenios',
                'modulo' => 'comercial',
                'label' => 'Convenios de Pago',
                'schema' => 'comercial',
                'table' => 'convenios_pago',
                'archivos_dbf' => ['convenio.dbf'],
                'icono' => 'mdi-handshake-outline',
            ],
            [
                'id' => 'recibos',
                'modulo' => 'comercial',
                'label' => 'Recibos de Caja',
                'schema' => 'comercial',
                'table' => 'recibos_caja',
                'archivos_dbf' => ['recibos.dbf'],
                'icono' => 'mdi-receipt-text-outline',
            ],
            [
                'id' => 'lecturas',
                'modulo' => 'comercial',
                'label' => 'Lecturas y Consumos Mensuales',
                'schema' => 'comercial',
                'table' => 'lecturas_mensuales',
                'archivos_dbf' => ['operacio.dbf', 'operahis.dbf'],
                'icono' => 'mdi-gauge',
            ],
            [
                'id' => 'periodos',
                'modulo' => 'comercial',
                'label' => 'Cronograma de Períodos',
                'schema' => 'comercial',
                'table' => 'periodos_facturacion',
                'archivos_dbf' => ['periodos.dbf'],
                'icono' => 'mdi-calendar-sync',
            ],

            // ==========================================
            // ESQUEMA FACTURACION
            // ==========================================
            [
                'id' => 'facturas',
                'modulo' => 'facturacion',
                'label' => 'Facturas Computarizadas',
                'schema' => 'facturacion',
                'table' => 'facturas',
                'archivos_dbf' => ['ventas.dbf', 'ventahis.dbf'],
                'icono' => 'mdi-file-document-check-outline',
            ],

            // ==========================================
            // ESQUEMA CONTABILIDAD
            // ==========================================
            [
                'id' => 'plan_cuentas',
                'modulo' => 'contabilidad',
                'label' => 'Plan de Cuentas Contables',
                'schema' => 'contabilidad',
                'table' => 'plan_cuentas',
                'archivos_dbf' => ['cuentas.dbf', 'plancta.dbf'],
                'icono' => 'mdi-book-open-outline',
            ],
            [
                'id' => 'comprobantes',
                'modulo' => 'contabilidad',
                'label' => 'Comprobantes de Diario',
                'schema' => 'contabilidad',
                'table' => 'comprobante_detalles',
                'archivos_dbf' => ['diariotr.dbf'],
                'icono' => 'mdi-book-edit-outline',
            ],
            [
                'id' => 'compras',
                'modulo' => 'contabilidad',
                'label' => 'Facturas de Compra (Libro de Compras)',
                'schema' => 'contabilidad',
                'table' => 'facturas_compra',
                'archivos_dbf' => ['compras.dbf', 'comprasC.dbf'],
                'icono' => 'mdi-cart-outline',
            ],

            // ==========================================
            // ESQUEMA ALMACEN
            // ==========================================
            [
                'id' => 'materiales_almacen',
                'modulo' => 'almacen',
                'label' => 'Materiales e Insumos',
                'schema' => 'almacen',
                'table' => 'materiales',
                'archivos_dbf' => ['almacen.dbf', 'articulos.dbf'],
                'icono' => 'mdi-package-variant-closed',
            ],

            // ==========================================
            // ESQUEMA ACTIVOS FIJOS
            // ==========================================
            [
                'id' => 'rubros_activos',
                'modulo' => 'activos_fijos',
                'label' => 'Rubros de Activos Fijos',
                'schema' => 'activos_fijos',
                'table' => 'rubros',
                'archivos_dbf' => ['afrubros.dbf', 'rubros.dbf'],
                'icono' => 'mdi-domain',
            ],
            [
                'id' => 'bienes_activos',
                'modulo' => 'activos_fijos',
                'label' => 'Activos Fijos / Bienes',
                'schema' => 'activos_fijos',
                'table' => 'bienes',
                'archivos_dbf' => ['afijo.dbf', 'afijo01.dbf'],
                'icono' => 'mdi-office-building-marker',
            ],
        ];
    }

    /**
     * Entrega las rutas de respaldos predefinidas conocidas en el sistema.
     */
    public function rutasPredefinidas(): JsonResponse
    {
        $baseShared = '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO';
        $rutas = [
            [
                'nombre' => 'Respaldo Oficial Activo (19/09/2026)',
                'ruta' => "{$baseShared}/DATA_19_09_2026/DATA",
                'descripcion' => 'Último respaldo extraído del servidor compartido con más de 1.6 millones de registros',
                'existe' => file_exists("{$baseShared}/DATA_19_09_2026/DATA"),
            ],
            [
                'nombre' => 'Respaldo Anterior (cr070923/DATA)',
                'ruta' => "{$baseShared}/cr070923/DATA",
                'descripcion' => 'Respaldo histórico previo',
                'existe' => file_exists("{$baseShared}/cr070923/DATA"),
            ],
            [
                'nombre' => 'Carpeta Raíz Compartida (SRV EMAPA COMPARTIDO/DATA)',
                'ruta' => "{$baseShared}/DATA",
                'descripcion' => 'Directorio DATA en la raíz compartida',
                'existe' => file_exists("{$baseShared}/DATA"),
            ],
        ];

        // Buscar también carpetas extraídas en storage
        $subidasPath = storage_path('app/respaldos_migracion');
        if (file_exists($subidasPath)) {
            $dirs = scandir($subidasPath);
            foreach ($dirs as $d) {
                if ($d === '.' || $d === '..') {
                    continue;
                }
                $full = "{$subidasPath}/{$d}";
                if (is_dir($full)) {
                    $rutas[] = [
                        'nombre' => "Subida en Servidor: {$d}",
                        'ruta' => $full,
                        'descripcion' => 'Archivo descomprimido por el usuario en esta sesión',
                        'existe' => true,
                    ];
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'rutas' => $rutas,
        ]);
    }

    /**
     * Escanea el directorio DBF y compara volumen contra PostgreSQL.
     */
    public function escanear(Request $request): JsonResponse
    {
        $ruta = $request->input('ruta');
        if (empty($ruta)) {
            $ruta = '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026/DATA';
        }

        if (!file_exists($ruta) || !is_dir($ruta)) {
            return response()->json([
                'status' => 'error',
                'message' => "El directorio especificado no existe o no es accesible: {$ruta}",
            ], 404);
        }

        $files = scandir($ruta);
        $mapeo = $this->getMapeoTablas();
        $items = [];

        $totalRegistrosDbf = 0;
        $totalRegistrosPg = 0;
        $dbfsEncontrados = 0;

        foreach ($mapeo as $item) {
            $archivoEncontrado = null;
            $rutaCompletaArchivo = null;

            foreach ($item['archivos_dbf'] as $posibleDbf) {
                foreach ($files as $f) {
                    if (strcasecmp($f, $posibleDbf) === 0) {
                        $archivoEncontrado = $f;
                        $rutaCompletaArchivo = "{$ruta}/{$f}";
                        break 2;
                    }
                }
            }

            $conteoDbf = 0;
            $dbfExiste = false;

            if ($archivoEncontrado && file_exists($rutaCompletaArchivo)) {
                $dbfExiste = true;
                $dbfsEncontrados++;
                try {
                    $header = $this->migrador->leerDbf($rutaCompletaArchivo, 1);
                    $conteoDbf = $header['total_records'];
                    $totalRegistrosDbf += $conteoDbf;
                } catch (Exception $e) {
                    $conteoDbf = 0;
                }
            }

            // Conteo en PostgreSQL en su respectivo esquema
            $conteoPg = 0;
            try {
                $query = DB::table("{$item['schema']}.{$item['table']}");
                if (!empty($item['filtro_sql'])) {
                    foreach ($item['filtro_sql'] as $col => $val) {
                        if (is_array($val)) {
                            $query->where($col, $val[0], $val[1]);
                        } else {
                            $query->where($col, $val);
                        }
                    }
                }
                $conteoPg = $query->count();
                $totalRegistrosPg += $conteoPg;
            } catch (Exception $e) {
                $conteoPg = 0;
            }

            $diferencia = $conteoDbf - $conteoPg;
            $estado = 'SIN_ORIGEN';
            if ($dbfExiste) {
                if ($conteoPg === 0 && $conteoDbf > 0) {
                    $estado = 'PENDIENTE';
                } elseif ($conteoPg > 0 && $conteoPg >= $conteoDbf) {
                    $estado = 'SINCRONIZADO';
                } elseif ($conteoPg > 0 && $conteoPg < $conteoDbf) {
                    $estado = 'PARCIAL';
                } else {
                    $estado = 'VACIO';
                }
            }

            $items[] = [
                'id' => $item['id'],
                'modulo' => $item['modulo'],
                'label' => $item['label'],
                'schema' => $item['schema'],
                'table' => $item['table'],
                'icono' => $item['icono'],
                'dbf_archivo' => $archivoEncontrado,
                'dbf_ruta' => $rutaCompletaArchivo,
                'dbf_existe' => $dbfExiste,
                'dbf_registros' => $conteoDbf,
                'pg_registros' => $conteoPg,
                'diferencia' => $diferencia,
                'estado' => $estado,
            ];
        }

        // Agrupación por esquema/módulo
        $modulosResumen = [
            'comercial' => ['nombre' => 'Gestión Comercial', 'dbf' => 0, 'pg' => 0, 'tablas' => 0],
            'facturacion' => ['nombre' => 'Facturación', 'dbf' => 0, 'pg' => 0, 'tablas' => 0],
            'contabilidad' => ['nombre' => 'Contabilidad', 'dbf' => 0, 'pg' => 0, 'tablas' => 0],
            'almacen' => ['nombre' => 'Almacenes e Insumos', 'dbf' => 0, 'pg' => 0, 'tablas' => 0],
            'activos_fijos' => ['nombre' => 'Activos Fijos', 'dbf' => 0, 'pg' => 0, 'tablas' => 0],
        ];

        foreach ($items as $it) {
            $m = $it['modulo'];
            if (isset($modulosResumen[$m])) {
                $modulosResumen[$m]['dbf'] += $it['dbf_registros'];
                $modulosResumen[$m]['pg'] += $it['pg_registros'];
                $modulosResumen[$m]['tablas']++;
            }
        }

        return response()->json([
            'status' => 'success',
            'ruta_escaneada' => $ruta,
            'total_dbfs_encontrados' => $dbfsEncontrados,
            'total_registros_dbf' => $totalRegistrosDbf,
            'total_registros_pg' => $totalRegistrosPg,
            'tablas' => $items,
            'resumen_modulos' => $modulosResumen,
        ]);
    }

    /**
     * Sube un archivo .zip o .rar y lo descomprime para su escaneo.
     */
    public function subirRespaldo(Request $request): JsonResponse
    {
        $request->validate([
            'archivo' => 'required|file|max:512000', // hasta 500MB
        ]);

        $archivo = $request->file('archivo');
        $extension = strtolower($archivo->getClientOriginalExtension());

        if (!in_array($extension, ['zip', 'rar', 'gz', 'tar'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'El formato del archivo debe ser .zip, .rar o comprimido estándar.',
            ], 422);
        }

        $folderName = 'unpacked_' . date('Ymd_His') . '_' . Str::random(6);
        $targetDir = storage_path("app/respaldos_migracion/{$folderName}");

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $tmpPath = $archivo->getRealPath();

        if ($extension === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($tmpPath) === true) {
                $zip->extractTo($targetDir);
                $zip->close();
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo abrir o descomprimir el archivo ZIP.',
                ], 500);
            }
        } else {
            // Manejar rar / otros con comando de sistema si está disponible
            $cmd = "unrar x -o+ " . escapeshellarg($tmpPath) . " " . escapeshellarg($targetDir) . " 2>&1";
            exec($cmd, $output, $returnCode);
            if ($returnCode !== 0) {
                // Si no hay unrar, intentar con 7z
                $cmd7z = "7z x -y " . escapeshellarg($tmpPath) . " -o" . escapeshellarg($targetDir) . " 2>&1";
                exec($cmd7z, $output2, $returnCode2);
                if ($returnCode2 !== 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Error al descomprimir archivo RAR. Asegúrese de que el archivo no esté protegido con contraseña.',
                        'debug' => implode("\n", array_merge($output, $output2)),
                    ], 500);
                }
            }
        }

        // Si se descomprimió en una subcarpeta, localizar la carpeta con los .DBF
        $finalPath = $targetDir;
        $scan = scandir($targetDir);
        foreach ($scan as $s) {
            if ($s === '.' || $s === '..') {
                continue;
            }
            if (is_dir("{$targetDir}/{$s}")) {
                // Verificar si tiene DBFs adentro
                $subFiles = scandir("{$targetDir}/{$s}");
                $hasDbf = false;
                foreach ($subFiles as $sf) {
                    if (preg_match('/\.dbf$/i', $sf)) {
                        $hasDbf = true;
                        break;
                    }
                }
                if ($hasDbf) {
                    $finalPath = "{$targetDir}/{$s}";
                    break;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Archivo de respaldo descomprimido con éxito.',
            'ruta_extraida' => $finalPath,
        ]);
    }

    /**
     * Ejecuta la migración modular con soporte de Dry-Run y límites.
     */
    public function ejecutar(Request $request): JsonResponse
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '2048M');
        DB::disableQueryLog();

        $ruta = $request->input('ruta');
        if (empty($ruta)) {
            $ruta = '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026/DATA';
        }

        $modulos = $request->input('modulos', []);
        if (empty($modulos)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debe seleccionar al menos un módulo o tabla para procesar.',
            ], 422);
        }

        $esSimulacion = (bool) $request->input('es_simulacion', true);
        $limite = (int) $request->input('limite', 0);
        $jobId = (string) Str::uuid();

        $inicio = microtime(true);
        $logs = [];
        $resultados = [];

        // Helper para resolver archivos DBF case-insensitively
        $resolverArchivo = function (array $nombresPosibles) use ($ruta): ?string {
            if (!file_exists($ruta)) {
                return null;
            }
            $files = scandir($ruta);
            foreach ($nombresPosibles as $posible) {
                foreach ($files as $f) {
                    if (strcasecmp($f, $posible) === 0) {
                        return "{$ruta}/{$f}";
                    }
                }
            }
            return null;
        };

        // Orden lógico de ejecución para garantizar que las dependencias existan
        $ordenLogico = [
            'zonas', 'calles', 'estados_abonado', 'conceptos_ingresos',
            'tarifas', 'abonados', 'aportes_agua', 'aportes_alcantarillado',
            'bajas_socios', 'convenios', 'recibos', 'periodos',
            'facturas', 'lecturas', 'plan_cuentas', 'comprobantes',
            'compras', 'materiales_almacen', 'rubros_activos', 'bienes_activos'
        ];
        usort($modulos, function ($a, $b) use ($ordenLogico) {
            $idxA = array_search($a, $ordenLogico);
            $idxB = array_search($b, $ordenLogico);
            $valA = $idxA === false ? 999 : $idxA;
            $valB = $idxB === false ? 999 : $idxB;
            return $valA <=> $valB;
        });

        foreach ($modulos as $mod) {
            $logItem = [
                'id' => $mod,
                'hora' => Carbon::now()->format('H:i:s'),
                'estado' => 'PROCESANDO',
                'mensaje' => '',
            ];

            try {
                switch ($mod) {
                    case 'zonas':
                        $path = $resolverArchivo(['zonas.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarZonas($path, $esSimulacion);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Zonas procesadas: {$res['total_en_dbf']} (Insertadas: {$res['insertados']})";
                            $resultados['zonas'] = $res;
                        } else {
                            $logItem['estado'] = 'ADVERTENCIA';
                            $logItem['mensaje'] = 'Archivo zonas.dbf no encontrado.';
                        }
                        break;

                    case 'calles':
                        $path = $resolverArchivo(['calles.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarCalles($path, $esSimulacion);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Calles procesadas: {$res['calles_procesadas']} (Insertadas: {$res['calles_insertadas']})";
                            $resultados['calles'] = $res;
                        } else {
                            $logItem['estado'] = 'ADVERTENCIA';
                            $logItem['mensaje'] = 'Archivo calles.dbf no encontrado.';
                        }
                        break;

                    case 'estados_abonado':
                        $path = $resolverArchivo(['estado.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarEstadosAbonados($path, $esSimulacion);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Estados procesados: {$res['total_en_dbf']} (Insertados: {$res['insertados']})";
                            $resultados['estados_abonado'] = $res;
                        } else {
                            $logItem['estado'] = 'ADVERTENCIA';
                            $logItem['mensaje'] = 'Archivo estado.dbf no encontrado.';
                        }
                        break;

                    case 'conceptos_ingresos':
                        $path = $resolverArchivo(['concepin.dbf', 'concepte.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarConceptosIngresos($path, $esSimulacion);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Conceptos de ingreso: {$res['total_en_dbf']} (Insertados: {$res['insertados']})";
                            $resultados['conceptos_ingresos'] = $res;
                        } else {
                            $logItem['estado'] = 'ADVERTENCIA';
                            $logItem['mensaje'] = 'Archivo concepin.dbf no encontrado.';
                        }
                        break;

                    case 'tarifas':
                        $path = $resolverArchivo(['categor.dbf', 'tarifa.dbf', 'tarifas.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarTarifas($path, $esSimulacion);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Tarifas y Categorías: {$res['total_en_dbf']} (Sincronizadas: {$res['insertados']})";
                            $resultados['tarifas'] = $res;
                        } else {
                            $logItem['estado'] = 'ADVERTENCIA';
                            $logItem['mensaje'] = 'Archivo categor.dbf no encontrado.';
                        }
                        break;

                    case 'abonados':
                        $pathSocios = $resolverArchivo(['socios.dbf']);
                        $pathApagua = $resolverArchivo(['apagua.dbf']);
                        if ($pathSocios) {
                            $res = $this->migrador->migrarAbonados($pathSocios, $esSimulacion, $limite, $pathApagua);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Abonados procesados: {$res['abonados_procesados']} (Insertados: {$res['abonados_insertados']})";
                            $resultados['abonados'] = $res;
                        } else {
                            $logItem['estado'] = 'ADVERTENCIA';
                            $logItem['mensaje'] = 'Archivo socios.dbf no encontrado.';
                        }
                        break;

                    case 'aportes_agua':
                        $path = $resolverArchivo(['apagua.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarAportesConexiones($path, 'AGUA', $esSimulacion, $limite);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Aportes Agua procesados: {$res['total_en_dbf']} (Insertados: {$res['insertados']})";
                            $resultados['aportes_agua'] = $res;
                        }
                        break;

                    case 'aportes_alcantarillado':
                        $path = $resolverArchivo(['alcanta.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarAportesConexiones($path, 'ALCANTARILLADO', $esSimulacion, $limite);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Aportes Alcantarillado: {$res['total_en_dbf']} (Insertados: {$res['insertados']})";
                            $resultados['aportes_alcantarillado'] = $res;
                        }
                        break;

                    case 'bajas_socios':
                        $path = $resolverArchivo(['bajasoc.dbf', 'sociosba.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarBajas($path, $esSimulacion);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Bajas registradas: {$res['total_bajas_dbf']} (Actualizados: {$res['abonados_dados_de_baja']})";
                            $resultados['bajas_socios'] = $res;
                        }
                        break;

                    case 'convenios':
                        $pathConve = $resolverArchivo(['convenio.dbf']);
                        $pathDet = $resolverArchivo(['detconve.dbf']);
                        if ($pathConve && $pathDet) {
                            $res = $this->migrador->migrarConvenios($pathConve, $pathDet, $esSimulacion);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Convenios procesados: {$res['convenios_insertados']}";
                            $resultados['convenios'] = $res;
                        }
                        break;

                    case 'recibos':
                        $path = $resolverArchivo(['recibos.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarRecibosOtros($path, $esSimulacion);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Recibos procesados: {$res['total_en_dbf']} (Migrados: {$res['recibos_migrados']})";
                            $resultados['recibos'] = $res;
                        }
                        break;

                    case 'lecturas':
                        $path = $resolverArchivo(['operacio.dbf', 'operahis.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarOperacionesDbf($path, $esSimulacion, null, $limite);
                            $logItem['estado'] = 'EXITO';
                            $extraInfo = (!empty($res['facturas_vinculadas'])) ? ", Facturas vinculadas: {$res['facturas_vinculadas']}" : "";
                            $logItem['mensaje'] = "Lecturas procesadas: {$res['total_en_dbf']} (Migradas: {$res['lecturas_migradas']}{$extraInfo})";
                            $resultados['lecturas'] = $res;
                        }
                        break;

                    case 'periodos':
                        $path = $resolverArchivo(['periodos.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarPeriodos($path, $esSimulacion);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Períodos procesados: {$res['total_en_dbf']} (Migrados: {$res['insertados']})";
                            $resultados['periodos'] = $res;
                        }
                        break;

                    case 'facturas':
                        $path = $resolverArchivo(['ventas.dbf', 'ventahis.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarFacturasVentas($path, $esSimulacion, $limite);
                            $logItem['estado'] = 'EXITO';
                            $extraMsg = '';

                            // VINCULACIÓN AUTOMÁTICA: Si ya existen lecturas migradas en PostgreSQL, vincular de inmediato con las facturas recién migradas
                            if (!$esSimulacion) {
                                $pathOperacio = $resolverArchivo(['operacio.dbf', 'operahis.dbf']);
                                if ($pathOperacio && DB::table('comercial.lecturas_mensuales')->exists()) {
                                    $vinculadas = $this->migrador->vincularFacturasConLecturas($pathOperacio);
                                    if ($vinculadas > 0) {
                                        $extraMsg = ", Facturas enlazadas con lecturas: {$vinculadas}";
                                    }
                                }
                            }

                            $logItem['mensaje'] = "Facturas procesadas: {$res['total_en_dbf']} (Migradas: {$res['facturas_migradas']}{$extraMsg})";
                            $resultados['facturas'] = $res;
                        }
                        break;

                    case 'plan_cuentas':
                        $path = $resolverArchivo(['cuentas.dbf', 'plancta.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarPlanCuentas($path, $esSimulacion, $limite);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Plan de Cuentas: {$res['total_en_dbf']} (Migradas: {$res['insertados']})";
                            $resultados['plan_cuentas'] = $res;
                        }
                        break;

                    case 'comprobantes':
                        $pathDiario = $resolverArchivo(['diariotr.dbf']);
                        $pathGlosas = $resolverArchivo(['glosastr.dbf', 'glosas.dbf']);
                        if ($pathDiario) {
                            $res = $this->migrador->migrarComprobantesDiario($pathDiario, $pathGlosas, $esSimulacion, $limite);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Comprobantes Diario: {$res['total_en_dbf']} líneas (Cabeceras: {$res['comprobantes_cabeceras']}, Detalles: {$res['insertados']})";
                            $resultados['comprobantes'] = $res;
                        } else {
                            $logItem['estado'] = 'ADVERTENCIA';
                            $logItem['mensaje'] = 'Archivo diariotr.dbf no encontrado.';
                        }
                        break;

                    case 'compras':
                        $path = $resolverArchivo(['compras.dbf', 'comprasC.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarFacturasCompra($path, $esSimulacion, $limite);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Facturas Compra: {$res['total_en_dbf']} (Migradas: {$res['insertados']})";
                            $resultados['compras'] = $res;
                        }
                        break;

                    case 'materiales_almacen':
                        $path = $resolverArchivo(['almacen.dbf', 'articulos.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarMaterialesAlmacen($path, $esSimulacion, $limite);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Materiales Almacén: {$res['total_en_dbf']} (Migrados: {$res['insertados']})";
                            $resultados['materiales_almacen'] = $res;
                        }
                        break;

                    case 'rubros_activos':
                        $path = $resolverArchivo(['afrubros.dbf', 'rubros.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarRubrosActivos($path, $esSimulacion);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Rubros Activos Fijos: {$res['total_en_dbf']} (Migrados: {$res['insertados']})";
                            $resultados['rubros_activos'] = $res;
                        }
                        break;

                    case 'bienes_activos':
                        $path = $resolverArchivo(['afijo.dbf', 'afijo01.dbf']);
                        if ($path) {
                            $res = $this->migrador->migrarBienesActivos($path, $esSimulacion, $limite);
                            $logItem['estado'] = 'EXITO';
                            $logItem['mensaje'] = "Bienes Activos: {$res['total_en_dbf']} (Migrados: {$res['insertados']})";
                            $resultados['bienes_activos'] = $res;
                        }
                        break;

                    default:
                        $logItem['estado'] = 'INFO';
                        $logItem['mensaje'] = "Módulo {$mod} reconocido sin handler específico.";
                        break;
                }

                // Guardar en tabla de bitácora migracion.logs
                DB::table('migracion.logs')->insert([
                    'job_id' => $jobId,
                    'modulo' => $mod,
                    'tabla_origen' => $mod,
                    'tabla_destino' => $mod,
                    'registros_procesados' => $resultados[$mod]['total_en_dbf'] ?? ($resultados[$mod]['abonados_procesados'] ?? ($resultados[$mod]['total_bajas_dbf'] ?? ($resultados[$mod]['calles_procesadas'] ?? 0))),
                    'registros_correctos' => $resultados[$mod]['insertados'] ?? ($resultados[$mod]['abonados_insertados'] ?? ($resultados[$mod]['recibos_migrados'] ?? ($resultados[$mod]['lecturas_migradas'] ?? ($resultados[$mod]['calles_insertadas'] ?? 0)))),
                    'registros_erroneos' => $resultados[$mod]['omitidos'] ?? 0,
                    'es_simulacion' => $esSimulacion,
                    'mensaje' => $logItem['mensaje'],
                    'detalles_json' => json_encode($resultados[$mod] ?? []),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } catch (\Throwable $e) {
                $logItem['estado'] = 'ERROR';
                $logItem['mensaje'] = "Error: {$e->getMessage()}";
                Log::error("Error migrando {$mod}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }

            $logs[] = $logItem;
        }

        $segundos = round(microtime(true) - $inicio, 2);

        return response()->json([
            'status' => 'success',
            'job_id' => $jobId,
            'es_simulacion' => $esSimulacion,
            'tiempo_segundos' => $segundos,
            'memoria_pico' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            'logs' => $logs,
            'resultados' => $resultados,
        ]);
    }

    /**
     * Retorna el historial de ejecuciones y bitácora de migración.
     */
    public function historial(Request $request): JsonResponse
    {
        $limite = (int) $request->input('limite', 150);
        $modulo = $request->input('modulo');
        $jobId = $request->input('job_id');

        $query = DB::table('migracion.logs')->orderBy('id', 'desc');

        if (!empty($modulo)) {
            $query->where('modulo', $modulo);
        }

        if (!empty($jobId)) {
            $query->where('job_id', $jobId);
        }

        $logs = $query->take($limite)->get();

        // Resumen general de migraciones
        $totalMigraciones = DB::table('migracion.logs')->distinct('job_id')->count('job_id');
        $totalProcesados = (int) DB::table('migracion.logs')->where('es_simulacion', false)->sum('registros_procesados');
        $totalCorrectos = (int) DB::table('migracion.logs')->where('es_simulacion', false)->sum('registros_correctos');
        $ultimoLog = DB::table('migracion.logs')->orderBy('id', 'desc')->first();

        return response()->json([
            'status' => 'success',
            'logs' => $logs,
            'kpis' => [
                'total_migraciones' => $totalMigraciones,
                'total_procesados' => $totalProcesados,
                'total_correctos' => $totalCorrectos,
                'ultima_ejecucion' => $ultimoLog ? $ultimoLog->created_at : null,
                'ultimo_estado' => $ultimoLog ? ($ultimoLog->registros_erroneos > 0 ? 'ADVERTENCIA' : 'EXITO') : 'SIN_DATOS',
            ],
        ]);
    }

    /**
     * Endpoint para revertir una migración ejecutada (Rollback).
     */
    public function revertir(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => 'required|string',
        ]);

        $jobId = $request->input('job_id');

        try {
            $resultado = $this->migrador->revertirJob($jobId);

            return response()->json([
                'status' => 'success',
                'message' => 'Migración revertida correctamente.',
                'resultado' => $resultado,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Error al revertir la migración: {$e->getMessage()}",
            ], 500);
        }
    }

    /**
     * Vincula en lote facturas con lecturas mensuales usando operacio.dbf bajo demanda.
     */
    public function vincularFacturasLecturas(Request $request): JsonResponse
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '2048M');

        $ruta = $request->input('ruta');
        if (empty($ruta)) {
            $ruta = '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026/DATA';
        }

        $resolverArchivo = function (array $nombresPosibles) use ($ruta): ?string {
            if (!file_exists($ruta)) {
                return null;
            }
            $files = scandir($ruta);
            foreach ($nombresPosibles as $posible) {
                foreach ($files as $f) {
                    if (strcasecmp($f, $posible) === 0) {
                        return "{$ruta}/{$f}";
                    }
                }
            }
            return null;
        };

        $pathOperacio = $resolverArchivo(['operacio.dbf', 'operahis.dbf']);
        if (!$pathOperacio) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró el archivo operacio.dbf en la ruta especificada.',
            ], 404);
        }

        $inicio = microtime(true);
        $totalVinculadas = $this->migrador->vincularFacturasConLecturas($pathOperacio);
        $segundos = round(microtime(true) - $inicio, 2);

        $totalConFactura = DB::table('comercial.lecturas_mensuales')->whereNotNull('id_factura')->count();

        return response()->json([
            'status' => 'success',
            'message' => "Proceso de vinculación completado en {$segundos}s. Lecturas vinculadas con factura: {$totalConFactura}",
            'vinculadas' => $totalVinculadas,
            'total_con_factura' => $totalConFactura,
            'tiempo_segundos' => $segundos,
        ]);
    }
}
