<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\Derivacion;
use App\Services\Correspondencia\DerivacionWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class DerivacionController extends Controller
{
    public function __construct(
        protected readonly DerivacionWorkflowService $workflowService
    ) {}

    /**
     * Derivar Hoja de Ruta a uno o varios destinatarios (LONDRA: Principal + Copias CC)
     */
    public function derivar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_hoja_ruta' => 'required|integer|exists:App\Models\Correspondencia\HojaRuta,id',
            'id_derivacion_padre' => 'nullable|integer|exists:App\Models\Correspondencia\Derivacion,id',
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
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idPersona = auth()->user()?->id_persona ?: (int) $request->input('id_funcionario_origen', 1);
        $payload = $request->all();
        $payload['id_funcionario_origen'] = $idPersona;

        $derivaciones = $this->workflowService->derivar($payload);

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
        $idPersona = auth()->user()?->id_persona ?: (int) $request->input('id_persona', 1);
        $derivacion = $this->workflowService->recibir($id, $idPersona);

        return response()->json([
            'success' => true,
            'message' => 'Derivación recibida en bandeja con éxito.',
            'data' => $derivacion,
        ], Response::HTTP_OK);
    }

    /**
     * Devolución de derivación con observaciones (LONDRA: Retorno al remitente)
     */
    public function devolver(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'motivo' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idPersona = auth()->user()?->id_persona ?: (int) $request->input('id_persona', 1);
        $retorno = $this->workflowService->devolver($id, $idPersona, (string) $request->input('motivo'));

        return response()->json([
            'success' => true,
            'message' => 'Trámite devuelto con observaciones.',
            'data' => $retorno,
        ], Response::HTTP_OK);
    }

    /**
     * Anular / Deshacer una derivación en tránsito (LONDRA: Si aún no fue recibida)
     */
    public function anular(Request $request, int $id): JsonResponse
    {
        $derivacion = Derivacion::findOrFail($id);
        $idPersona = auth()->user()?->id_persona ?: (int) $request->input('id_persona', $derivacion->id_funcionario_origen ?: 1);
        $this->workflowService->anularDerivacion($id, $idPersona);

        return response()->json([
            'success' => true,
            'message' => 'Derivación anulada con éxito. El trámite ha retornado a su bandeja.',
        ], Response::HTTP_OK);
    }
}
