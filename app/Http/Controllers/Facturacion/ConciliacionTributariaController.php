<?php

declare(strict_types=1);

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Services\Facturacion\ConciliacionTributariaService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ConciliacionTributariaController extends Controller
{
    protected ConciliacionTributariaService $conciliacionService;

    public function __construct(ConciliacionTributariaService $conciliacionService)
    {
        $this->conciliacionService = $conciliacionService;
    }

    /**
     * Lista los archivos de ventas del SIN detectados en el sistema/servidor.
     */
    public function archivosDisponibles(): JsonResponse
    {
        $archivos = [];
        $directorio = storage_path('app/sin_conciliacion');

        if (is_dir($directorio)) {
            $ficheros = glob($directorio . '/*.{xlsx,xls,csv}', GLOB_BRACE) ?: [];
            foreach ($ficheros as $realPath) {
                if (file_exists($realPath)) {
                    $archivos[] = [
                        'nombre' => basename($realPath),
                        'ruta' => $realPath,
                        'tamano' => round(filesize($realPath) / 1024, 2) . ' KB',
                        'fecha_modificacion' => date('d/m/Y H:i:s', filemtime($realPath)),
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $archivos,
        ], Response::HTTP_OK);
    }

    /**
     * Ejecuta el cotejo entre el archivo del SIN y las facturas de la base de datos.
     */
    public function conciliar(Request $request): JsonResponse
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $request->validate([
            'archivo' => 'nullable|file|mimes:xlsx,xls,csv|max:20480',
            'ruta_archivo' => 'nullable|string',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date',
        ]);

        try {
            $rutaFinal = $this->resolverRutaArchivo($request);
            if (!$rutaFinal) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se proporcionó ningún archivo de ventas del SIN (Excel) válido.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $resultado = $this->conciliacionService->conciliarArchivo(
                $rutaFinal,
                $request->input('fecha_desde'),
                $request->input('fecha_hasta')
            );

            return response()->json($resultado, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error durante el cotejo tributario: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Sincroniza y regulariza los estados fiscales en la base de datos según el reporte oficial del SIN.
     */
    public function sincronizar(Request $request): JsonResponse
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $request->validate([
            'archivo' => 'nullable|file|mimes:xlsx,xls,csv|max:20480',
            'ruta_archivo' => 'nullable|string',
            'importar_validas' => 'nullable|boolean',
            'importar_anuladas' => 'nullable|boolean',
            'regularizar_ley1886' => 'nullable|boolean',
        ]);

        try {
            $rutaFinal = $this->resolverRutaArchivo($request);
            if (!$rutaFinal) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se proporcionó ningún archivo de ventas del SIN (Excel) válido para sincronizar.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $opciones = [
                'importar_anuladas' => $request->boolean('importar_anuladas', true),
                'regularizar_ley1886' => $request->boolean('regularizar_ley1886', true),
                'fecha_desde' => $request->input('fecha_desde'),
                'fecha_hasta' => $request->input('fecha_hasta'),
            ];

            if ($request->has('importar_validas')) {
                $opciones['importar_validas'] = $request->boolean('importar_validas');
            }

            $usuarioId = Auth::id() ?? 1;

            $resultado = $this->conciliacionService->sincronizarConSin($rutaFinal, $opciones, (int) $usuarioId);

            return response()->json($resultado, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error durante la regularización de estados: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Resuelve la ruta física del archivo ya sea subido por HTTP o referenciado en el servidor.
     */
    protected function resolverRutaArchivo(Request $request): ?string
    {
        if ($request->hasFile('archivo')) {
            $file = $request->file('archivo');
            $dir = storage_path('app/sin_conciliacion');
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            $nombre = 'sin_upload_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $nombre);
            $ruta = $dir . '/' . $nombre;
            return file_exists($ruta) ? $ruta : null;
        }

        $rutaDirecta = $request->input('ruta_archivo');
        if (!empty($rutaDirecta) && file_exists($rutaDirecta)) {
            return $rutaDirecta;
        }

        // Buscar automáticamente archivoVentas.xlsx en el directorio raíz del proyecto
        $archivoRaiz = base_path('../archivoVentas.xlsx');
        if (file_exists($archivoRaiz)) {
            return $archivoRaiz;
        }

        return null;
    }
}
