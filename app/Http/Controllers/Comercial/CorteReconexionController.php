<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\OrdenTrabajo;
use App\Services\Comercial\CorteReconexionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class CorteReconexionController extends Controller
{
    public function __construct(
        protected CorteReconexionService $corteService
    ) {}

    /**
     * Listado de órdenes de trabajo (cortes, reconexiones, etc.).
     */
    public function indexOrdenes(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $tipo = $request->input('tipo_orden');
        $estado = $request->input('estado');
        $search = $request->input('search');

        $query = OrdenTrabajo::with(['abonado.zona', 'abonado.calle', 'tecnico'])
            ->orderByDesc('id');

        if (!empty($tipo)) {
            $query->where('tipo_orden', $tipo);
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_orden', 'like', "%{$search}%")
                    ->orWhereHas('abonado', fn($qa) => $qa->where('codigo', 'like', "%{$search}%")
                        ->orWhere('nombre_completo', 'ilike', "%{$search}%"));
            });
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ], Response::HTTP_OK);
    }

    /**
     * Listado de abonados en mora (>= 2 meses) candidatos para corte operativo.
     */
    public function candidatosCorte(Request $request): JsonResponse
    {
        $mesesMora = (int) $request->input('meses_mora', 2);
        $idZona = $request->input('id_zona') ? (int) $request->input('id_zona') : null;

        $candidatos = $this->corteService->obtenerAbonadosParaCorte($mesesMora, $idZona);

        return response()->json([
            'success' => true,
            'data' => $candidatos,
            'total' => $candidatos->count(),
        ], Response::HTTP_OK);
    }

    /**
     * Generación masiva de órdenes de corte por mora.
     */
    public function generarCortes(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'abonados_ids' => 'required|array|min:1',
            'fecha_programada' => 'required|date',
            'id_tecnico_asignado' => 'nullable|integer',
            'motivo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $resultado = $this->corteService->generarOrdenesCorte(
                (array) $request->input('abonados_ids'),
                (string) $request->input('fecha_programada'),
                $request->input('id_tecnico_asignado') ? (int) $request->input('id_tecnico_asignado') : null,
                $request->input('motivo')
            );

            return response()->json([
                'success' => true,
                'message' => "Se generaron {$resultado['total_generadas']} órdenes de corte con éxito.",
                'data' => $resultado,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar órdenes de corte: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Ejecución de corte físico de servicio en campo.
     */
    public function ejecutarCorte(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lectura_en_corte' => 'required|numeric|min:0',
            'numero_precinto' => 'required|string|max:50',
            'informe_tecnico' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $orden = $this->corteService->ejecutarCorte(
                $id,
                (float) $request->input('lectura_en_corte'),
                (string) $request->input('numero_precinto'),
                $request->input('informe_tecnico')
            );

            return response()->json([
                'success' => true,
                'message' => 'Corte de servicio registrado exitosamente.',
                'data' => $orden,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar corte: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Ejecución de reconexión física de servicio en campo.
     */
    public function ejecutarReconexion(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'informe_tecnico' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $orden = $this->corteService->ejecutarReconexion(
                $id,
                $request->input('informe_tecnico')
            );

            return response()->json([
                'success' => true,
                'message' => 'Reconexión de servicio registrada exitosamente.',
                'data' => $orden,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar reconexión: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Descarga de la Orden de Trabajo Técnica en PDF.
     */
    public function descargarOrdenTrabajo(
        int $id,
        \App\Services\Comercial\DocumentoComercialPdfService $pdfService
    ): \Illuminate\Http\Response {
        $orden = OrdenTrabajo::findOrFail($id);
        $pdf = $pdfService->generarOrdenTrabajoPdf($orden);

        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Orden_Trabajo_{$orden->numero_orden}.pdf\"",
        ]);
    }
}

