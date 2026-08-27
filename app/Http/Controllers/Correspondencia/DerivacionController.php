<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\Derivacion;
use App\Models\Correspondencia\HojaRuta;
use App\Services\Correspondencia\DerivacionWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class DerivacionController extends Controller
{
    protected DerivacionWorkflowService $workflowService;

    public function __construct(DerivacionWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Derivar Hoja de Ruta a uno o varios destinatarios
     */
    public function derivar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_hoja_ruta' => 'required|integer|exists:correspondencia.hojas_ruta,id',
            'id_derivacion_padre' => 'nullable|integer|exists:correspondencia.derivaciones,id',
            'destinatarios' => 'required|array|min:1',
            'destinatarios.*.id_unidad_destino' => 'required|integer',
            'destinatarios.*.id_funcionario_destino' => 'nullable|integer',
            'destinatarios.*.id_cargo_destino' => 'nullable|integer',
            'destinatarios.*.es_copia' => 'nullable|boolean',
            'proveido' => 'required|string',
            'instruccion_detalle' => 'nullable|string',
            'dias_plazo' => 'nullable|integer|min:1',
            'prioridad' => 'nullable|string|in:URGENTE,ALTA,MEDIA,BAJA',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $derivaciones = $this->workflowService->derivar($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Hoja de ruta derivada exitosamente.',
            'data' => $derivaciones,
        ], Response::HTTP_CREATED);
    }

    /**
     * Recepción de una derivación en la bandeja del funcionario
     */
    public function recibir(Request $request, int $id): JsonResponse
    {
        $idPersona = auth()->user()?->id_persona ?: (int)$request->input('id_persona', 1);
        $derivacion = $this->workflowService->recibir($id, $idPersona);

        return response()->json([
            'success' => true,
            'message' => 'Derivación recibida en bandeja con éxito.',
            'data' => $derivacion,
        ], Response::HTTP_OK);
    }

    /**
     * Devolución de derivación con observaciones
     */
    public function devolver(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'motivo' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idPersona = auth()->user()?->id_persona ?: (int)$request->input('id_persona', 1);
        $retorno = $this->workflowService->devolver($id, $idPersona, $request->input('motivo'));

        return response()->json([
            'success' => true,
            'message' => 'Trámite devuelto con observaciones.',
            'data' => $retorno,
        ], Response::HTTP_OK);
    }
}
