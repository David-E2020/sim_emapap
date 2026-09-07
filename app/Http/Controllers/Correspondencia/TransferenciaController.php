<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Services\Correspondencia\TransferenciaBandejaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TransferenciaController extends Controller
{
    protected TransferenciaBandejaService $transferenciaService;

    public function __construct(TransferenciaBandejaService $transferenciaService)
    {
        $this->transferenciaService = $transferenciaService;
    }

    public function transferir(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_funcionario_origen' => 'required|integer|exists:App\Models\Rrhh\Persona,id',
            'id_funcionario_destino' => 'required|integer|exists:App\Models\Rrhh\Persona,id|different:id_funcionario_origen',
            'id_cargo_destino' => 'nullable|integer',
            'motivo' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $res = $this->transferenciaService->transferirBandeja(
            (int) $request->input('id_funcionario_origen'),
            (int) $request->input('id_funcionario_destino'),
            $request->filled('id_cargo_destino') ? (int) $request->input('id_cargo_destino') : null,
            (string) $request->input('motivo')
        );

        $total = ($res['derivaciones_transferidas'] ?? 0) + ($res['documentos_transferidos'] ?? 0);

        return response()->json([
            'success' => true,
            'message' => "Se transfirieron exitosamente {$total} registros.",
            'data' => $res,
        ], Response::HTTP_OK);
    }
}
