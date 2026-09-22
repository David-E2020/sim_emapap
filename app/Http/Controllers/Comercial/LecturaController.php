<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\PeriodoFacturacion;
use App\Services\Comercial\LecturacionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LecturaController extends Controller
{
    public function __construct(
        protected LecturacionService $lecturacionService
    ) {}

    /**
     * Listado de periodos de facturación.
     */
    public function indexPeriodos(): JsonResponse
    {
        $periodos = PeriodoFacturacion::withCount('lecturas')
            ->orderByDesc('gestion')
            ->orderByDesc('mes')
            ->get()
            ->map(function ($p) {
                $p->estado_label = $p->estado_label;
                $p->estado_normalizado = $p->estado_normalizado;
                return $p;
            });

        return response()->json([
            'success' => true,
            'data' => $periodos,
        ], Response::HTTP_OK);
    }

    /**
     * Apertura de un nuevo ciclo/periodo mensual.
     */
    public function abrirPeriodo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mes' => 'required|integer|min:1|max:12',
            'gestion' => 'required|integer|min:2020|max:2050',
            'fecha_inicio_consumo' => 'required|date',
            'fecha_fin_consumo' => 'required|date|after_or_equal:fecha_inicio_consumo',
            'fecha_vencimiento_pago' => 'required|date|after:fecha_fin_consumo',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $periodo = $this->lecturacionService->abrirPeriodo(
                (int) $request->input('mes'),
                (int) $request->input('gestion'),
                (string) $request->input('fecha_inicio_consumo'),
                (string) $request->input('fecha_fin_consumo'),
                (string) $request->input('fecha_vencimiento_pago'),
                $request->input('observaciones')
            );

            return response()->json([
                'success' => true,
                'message' => "Periodo {$periodo->periodo} abierto exitosamente con órdenes de lectura inicializadas.",
                'data' => $periodo,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al abrir el periodo: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Obtiene la planilla de lecturas de un periodo para captura o visualización.
     */
    public function obtenerPlanilla(Request $request, int $idPeriodo): JsonResponse
    {
        $idZona = $request->input('id_zona') ? (int) $request->input('id_zona') : null;
        $idCalle = $request->input('id_calle') ? (int) $request->input('id_calle') : null;

        $planilla = $this->lecturacionService->obtenerPlanilla($idPeriodo, $idZona, $idCalle);

        return response()->json([
            'success' => true,
            'data' => $planilla,
            'total' => $planilla->count(),
        ], Response::HTTP_OK);
    }

    /**
     * Guarda una lectura individual y calcula sus importes tarifarios en tiempo real.
     */
    public function guardarLectura(Request $request, int $idLectura): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lectura_actual' => 'required|numeric|min:0',
            'es_estimada' => 'boolean',
            'observacion_lectura' => 'nullable|string|max:150',
            'otros_cargos' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $lectura = $this->lecturacionService->registrarLectura(
                $idLectura,
                (float) $request->input('lectura_actual'),
                $request->boolean('es_estimada'),
                $request->input('observacion_lectura'),
                $request->user()?->id ?? 1,
                (float) $request->input('otros_cargos', 0.00)
            );

            return response()->json([
                'success' => true,
                'message' => 'Lectura registrada y calculada correctamente.',
                'data' => $lectura,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Guarda un lote masivo de lecturas enviadas desde la planilla.
     */
    public function guardarLote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lecturas' => 'required|array|min:1',
            'lecturas.*.id' => 'required|integer|exists:pgsql.comercial.lecturas_mensuales,id',
            'lecturas.*.lectura_actual' => 'required|numeric|min:0',
            'lecturas.*.es_estimada' => 'boolean',
            'lecturas.*.observacion_lectura' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $items = $request->input('lecturas');
        $guardados = 0;
        $errores = [];

        foreach ($items as $item) {
            try {
                $this->lecturacionService->registrarLectura(
                    (int) $item['id'],
                    (float) $item['lectura_actual'],
                    !empty($item['es_estimada']),
                    $item['observacion_lectura'] ?? null,
                    $request->user()?->id ?? 1
                );
                $guardados++;
            } catch (Exception $e) {
                $errores[] = "ID {$item['id']}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Se procesaron {$guardados} lecturas.",
            'errores' => $errores,
        ], Response::HTTP_OK);
    }

    /**
     * Liquida y cierra la facturación del periodo completo.
     */
    public function liquidarPeriodo(int $idPeriodo): JsonResponse
    {
        try {
            $periodo = $this->lecturacionService->liquidarPeriodo($idPeriodo);

            return response()->json([
                'success' => true,
                'message' => "Periodo {$periodo->periodo} liquidado y facturado con éxito.",
                'data' => $periodo,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al liquidar periodo: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Descarga masiva en PDF de los avisos de cobranza de un periodo (opcionalmente filtrado por zona).
     */
    public function descargarAvisosLote(
        Request $request,
        int $idPeriodo,
        \App\Services\Comercial\DocumentoComercialPdfService $pdfService
    ): \Illuminate\Http\Response {
        $idZona = $request->input('id_zona') ? (int) $request->input('id_zona') : null;
        $pdf = $pdfService->generarAvisosZonaPdf($idPeriodo, $idZona);

        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Avisos_Cobranza_Periodo_{$idPeriodo}.pdf\"",
        ]);
    }

    /**
     * Actualiza fechas y observaciones de un período existente.
     */
    public function actualizarPeriodo(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio_consumo' => 'nullable|date',
            'fecha_fin_consumo' => 'nullable|date',
            'fecha_vencimiento_pago' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'estado' => 'nullable|string|in:LECTURA,FACTURACION,CERRADO,ABIERTO,FACTURADO',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $periodo = $this->lecturacionService->actualizarPeriodo($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => "Periodo {$periodo->periodo} actualizado correctamente.",
                'data' => $periodo,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar periodo: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cambia el estado del período en el ciclo de vida comercial (LECTURA, FACTURACION, CERRADO).
     */
    public function cambiarEstadoPeriodo(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|string|in:LECTURA,FACTURACION,CERRADO,ABIERTO,FACTURADO',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $periodo = $this->lecturacionService->cambiarEstadoPeriodo($id, (string) $request->input('estado'));

            return response()->json([
                'success' => true,
                'message' => "Estado del periodo {$periodo->periodo} actualizado a {$periodo->estado_label}.",
                'data' => $periodo,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del periodo: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Elimina un período si no tiene lecturas pagadas.
     */
    public function eliminarPeriodo(int $id): JsonResponse
    {
        try {
            $this->lecturacionService->eliminarPeriodo($id);

            return response()->json([
                'success' => true,
                'message' => 'Periodo eliminado exitosamente.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
