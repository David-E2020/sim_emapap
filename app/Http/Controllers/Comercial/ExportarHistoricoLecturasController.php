<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\PeriodoFacturacion;
use App\Models\Comercial\Zona;
use App\Services\Comercial\ExportarHistoricoLecturasService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\PhpExecutableFinder;

class ExportarHistoricoLecturasController extends Controller
{
    /**
     * Retorna los períodos históricos disponibles, zonas y categorías tarifarias para alimentar el modal.
     */
    public function periodosDisponibles(Request $request): JsonResponse
    {
        $periodos = PeriodoFacturacion::select([
            'id',
            'periodo',
            'mes',
            'gestion',
            'estado',
            'fecha_inicio_consumo',
            'fecha_fin_consumo',
        ])
        ->selectSub(function ($query) {
            $query->from('comercial.lecturas_mensuales')
                ->whereColumn('lecturas_mensuales.id_periodo', 'periodos_facturacion.id')
                ->selectRaw('COUNT(*)');
        }, 'total_lecturas')
        ->orderBy('gestion', 'desc')
        ->orderBy('mes', 'desc')
        ->get();

        $zonas = Zona::where('_estado', 'ACTIVO')->orderBy('nombre')->get(['id', 'nombre', 'codigo']);
        $categorias = CategoriaTarifaria::where('_estado', 'ACTIVO')->orderBy('codigo')->get(['id', 'nombre', 'codigo']);
        $columnas = array_values(ExportarHistoricoLecturasService::obtenerDefinicionColumnas());

        return response()->json([
            'periodos' => $periodos,
            'zonas' => $zonas,
            'categorias' => $categorias,
            'columnas' => $columnas,
        ]);
    }

    /**
     * Inicia el proceso de exportación en segundo plano.
     */
    public function iniciar(Request $request): JsonResponse
    {
        $tipoRango = $request->input('tipo_rango', '5_anios');
        $formato = strtolower((string) $request->input('formato', 'xlsx'));
        if (!in_array($formato, ['xlsx', 'csv'], true)) {
            $formato = 'xlsx';
        }

        $idZona = $request->filled('id_zona') ? (int) $request->input('id_zona') : null;
        $idCategoria = $request->filled('id_categoria') ? (int) $request->input('id_categoria') : null;
        $estadoPago = $request->filled('estado_pago') ? (string) $request->input('estado_pago') : null;

        // Determinar qué períodos corresponden según el tipo de rango
        $periodoIds = [];

        if ($tipoRango === 'manual' && $request->filled('periodo_ids')) {
            $periodoIds = array_map('intval', (array) $request->input('periodo_ids'));
        } elseif ($tipoRango === '5_anios') {
            // Últimos 5 años (hasta 60 períodos)
            $periodoIds = PeriodoFacturacion::orderBy('gestion', 'desc')
                ->orderBy('mes', 'desc')
                ->take(60)
                ->pluck('id')
                ->toArray();
        } elseif ($tipoRango === '3_anios') {
            // Últimos 3 años (hasta 36 períodos)
            $periodoIds = PeriodoFacturacion::orderBy('gestion', 'desc')
                ->orderBy('mes', 'desc')
                ->take(36)
                ->pluck('id')
                ->toArray();
        } elseif ($tipoRango === '1_anio') {
            // Último año (12 períodos)
            $periodoIds = PeriodoFacturacion::orderBy('gestion', 'desc')
                ->orderBy('mes', 'desc')
                ->take(12)
                ->pluck('id')
                ->toArray();
        } elseif ($tipoRango === 'actual') {
            // Período activo o más reciente
            $periodoActivo = PeriodoFacturacion::where('estado', 'ACTIVO')
                ->orWhere('estado', 'FACTURACION')
                ->orderBy('gestion', 'desc')
                ->orderBy('mes', 'desc')
                ->first();

            if (!$periodoActivo) {
                $periodoActivo = PeriodoFacturacion::orderBy('gestion', 'desc')->orderBy('mes', 'desc')->first();
            }

            if ($periodoActivo) {
                $periodoIds = [$periodoActivo->id];
            }
        } elseif ($tipoRango === 'personalizado') {
            $idDesde = (int) $request->input('periodo_id_desde');
            $idHasta = (int) $request->input('periodo_id_hasta');

            $pDesde = PeriodoFacturacion::find($idDesde);
            $pHasta = PeriodoFacturacion::find($idHasta);

            if ($pDesde && $pHasta) {
                $minGestion = min($pDesde->gestion, $pHasta->gestion);
                $maxGestion = max($pDesde->gestion, $pHasta->gestion);

                $periodosQuery = PeriodoFacturacion::whereBetween('gestion', [$minGestion, $maxGestion]);

                $lista = $periodosQuery->get()->filter(function ($p) use ($pDesde, $pHasta) {
                    $valActual = ($p->gestion * 100) + $p->mes;
                    $valDesde = ($pDesde->gestion * 100) + $pDesde->mes;
                    $valHasta = ($pHasta->gestion * 100) + $pHasta->mes;
                    $valMin = min($valDesde, $valHasta);
                    $valMax = max($valDesde, $valHasta);
                    return $valActual >= $valMin && $valActual <= $valMax;
                });

                $periodoIds = $lista->pluck('id')->toArray();
            }
        }

        if (empty($periodoIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontraron períodos en el rango seleccionado.',
            ], 422);
        }

        $jobsDir = storage_path('app/reportes_jobs');
        if (!file_exists($jobsDir)) {
            mkdir($jobsDir, 0775, true);
        }

        $jobId = (string) Str::uuid();
        $jobFile = "{$jobsDir}/{$jobId}.json";

        $columnas = (array) $request->input('columnas', []);

        $config = [
            'job_id' => $jobId,
            'tipo_rango' => $tipoRango,
            'periodo_ids' => $periodoIds,
            'formato' => $formato,
            'id_zona' => $idZona,
            'id_categoria' => $idCategoria,
            'estado_pago' => $estadoPago,
            'columnas' => $columnas,
            'estado' => 'EN_COLA',
            'progreso' => 0,
            'periodos_procesados' => 0,
            'total_periodos' => count($periodoIds),
            'filas_exportadas' => 0,
            'mensaje' => 'Exportación encolada. Iniciando hilo en segundo plano...',
            'iniciado_en' => Carbon::now()->toDateTimeString(),
            'actualizado_en' => Carbon::now()->toDateTimeString(),
        ];

        file_put_contents($jobFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Localizar binario PHP CLI
        $phpFinder = new PhpExecutableFinder();
        $phpPath = $phpFinder->find(false);
        if (!$phpPath || str_contains($phpPath, 'fpm') || str_contains($phpPath, 'cgi')) {
            if (file_exists('/usr/local/bin/php') && is_executable('/usr/local/bin/php')) {
                $phpPath = '/usr/local/bin/php';
            } elseif (file_exists('/usr/bin/php') && is_executable('/usr/bin/php')) {
                $phpPath = '/usr/bin/php';
            } else {
                $phpPath = 'php';
            }
        }

        $phpBinary = escapeshellarg($phpPath);
        $artisan = escapeshellarg(base_path('artisan'));
        $argJobId = escapeshellarg($jobId);
        $logOutput = escapeshellarg(storage_path("logs/reporte_exportar_{$jobId}.log"));

        $command = "nohup {$phpBinary} {$artisan} comercial:exportar-historico-lecturas {$argJobId} < /dev/null > {$logOutput} 2>&1 &";
        exec($command);

        return response()->json([
            'status' => 'success',
            'job_id' => $jobId,
            'total_periodos' => count($periodoIds),
            'formato' => $formato,
            'message' => 'Proceso de exportación iniciado en segundo plano exitosamente.',
        ]);
    }

    /**
     * Consulta el estado del job de exportación.
     */
    public function estado(string $jobId): JsonResponse
    {
        $jobFile = storage_path("app/reportes_jobs/{$jobId}.json");

        if (!file_exists($jobFile)) {
            return response()->json([
                'status' => 'error',
                'message' => 'El trabajo de exportación no fue encontrado.',
            ], 404);
        }

        $datos = json_decode(file_get_contents($jobFile), true) ?: [];

        // Si el estado figura como PROCESANDO o EN_COLA, verificar que el proceso CLI continúe realmente con vida
        $estadoActual = $datos['estado'] ?? '';
        if (in_array($estadoActual, ['PROCESANDO', 'EN_COLA'], true)) {
            $updatedAt = isset($datos['actualizado_en']) ? Carbon::parse($datos['actualizado_en']) : null;
            $secondsSinceUpdate = $updatedAt ? $updatedAt->diffInSeconds(Carbon::now()) : 999;

            if ($secondsSinceUpdate > 15) {
                // Verificar si existe el proceso en el sistema operativo
                $output = [];
                @exec("ps -ef | grep 'comercial:exportar-historico-lecturas' | grep " . escapeshellarg($jobId) . " | grep -v grep", $output);
                $isAlive = !empty($output);

                if (!$isAlive) {
                    $logPath = storage_path("logs/reporte_exportar_{$jobId}.log");
                    $logDetail = '';
                    if (file_exists($logPath)) {
                        $rawLog = file_get_contents($logPath);
                        if (preg_match('/(?:Fatal error|Exception):?\s*([^\n\r]+)/i', $rawLog, $m)) {
                            $logDetail = $m[1];
                        }
                    }

                    $datos['estado'] = 'ERROR';
                    $datos['error'] = 'El proceso en segundo plano terminó inesperadamente.';
                    $datos['mensaje'] = $logDetail
                        ? "El proceso se detuvo por error: {$logDetail}"
                        : 'El proceso en segundo plano no respondió y se dio por cancelado.';
                    $datos['actualizado_en'] = Carbon::now()->toDateTimeString();

                    @file_put_contents($jobFile, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }
            }
        }

        return response()->json($datos);
    }

    /**
     * Descarga el archivo generado si el job está en estado COMPLETADO.
     */
    public function descargar(string $jobId): BinaryFileResponse|JsonResponse
    {
        $jobFile = storage_path("app/reportes_jobs/{$jobId}.json");

        if (!file_exists($jobFile)) {
            return response()->json(['status' => 'error', 'message' => 'Trabajo no encontrado.'], 404);
        }

        $config = json_decode(file_get_contents($jobFile), true);
        if (($config['estado'] ?? '') !== 'COMPLETADO') {
            return response()->json(['status' => 'error', 'message' => 'El archivo aún no ha terminado de generarse.'], 400);
        }

        $rutaArchivo = $config['ruta_archivo'] ?? '';
        $nombreArchivo = $config['nombre_archivo'] ?? 'reporte_historico.xlsx';

        if (!file_exists($rutaArchivo)) {
            return response()->json(['status' => 'error', 'message' => 'El archivo generado ya no existe en el servidor.'], 404);
        }

        $mimeType = str_ends_with($nombreArchivo, '.csv')
            ? 'text/csv; charset=UTF-8'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return response()->download($rutaArchivo, $nombreArchivo, [
            'Content-Type' => $mimeType,
        ]);
    }

    /**
     * Envía una señal de cancelación al hilo en segundo plano.
     */
    public function cancelar(string $jobId): JsonResponse
    {
        $cancelFile = storage_path("app/reportes_jobs/{$jobId}.cancel");
        file_put_contents($cancelFile, date('Y-m-d H:i:s'));

        return response()->json([
            'status' => 'success',
            'message' => 'Señal de cancelación enviada correctamente.',
        ]);
    }
}
