<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\FirmaAprobacion;
use App\Models\Correspondencia\ParticipanteDocumento;
use App\Services\Correspondencia\FirmaDigitalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class FirmaAprobacionController extends Controller
{
    protected FirmaDigitalService $firmaService;

    public function __construct(FirmaDigitalService $firmaService)
    {
        $this->firmaService = $firmaService;
    }

    /**
     * Bandeja de documentos pendientes de firma para el usuario
     */
    public function pendientes(Request $request): JsonResponse
    {
        $idPersona = auth()->user()?->id_persona ?: (int)$request->input('id_persona', 1);

        $pendientes = Documento::with([
            'creador',
            'unidadGeneradora',
            'plantilla',
            'participantes.persona',
            'participantes.puesto',
            'firmasAprobaciones.persona',
        ])
        ->whereHas('participantes', function ($q) use ($idPersona) {
            $q->where('id_persona', $idPersona)
                ->whereIn('tipo_participacion', ['REMITENTE_DE', 'VIA', 'DESTINATARIO_A']);
        })
        ->whereDoesntHave('firmasAprobaciones', function ($q) use ($idPersona) {
            $q->where('id_persona', $idPersona)->where('estado', 'FIRMADO');
        })
        ->where('_estado', 'ACTIVO')
        ->orderBy('id', 'desc')
        ->get();

        return response()->json(['success' => true, 'data' => $pendientes], Response::HTTP_OK);
    }

    /**
     * Firmar / Aprobar documento con PIN de seguridad
     */
    public function firmar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_documento' => 'required|integer|exists:correspondencia.documentos,id',
            'pin' => 'nullable|string',
            'tipo_firma' => 'nullable|string|in:PIN_ELECTRONICO,TOKEN_DIGITAL,CIUDADANIA_DIGITAL',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idPersona = auth()->user()?->id_persona ?: (int)$request->input('id_persona', 1);
        $tipoFirma = $request->input('tipo_firma', 'PIN_ELECTRONICO');

        $firma = $this->firmaService->firmarDocumento(
            (int)$request->input('id_documento'),
            $idPersona,
            $request->input('pin'),
            $tipoFirma
        );

        return response()->json([
            'success' => true,
            'message' => 'Documento firmado y rubricado exitosamente.',
            'data' => $firma->fresh(['documento', 'persona']),
        ], Response::HTTP_OK);
    }

    /**
     * Rechazar u observar documento
     */
    public function rechazar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_documento' => 'required|integer|exists:correspondencia.documentos,id',
            'motivo' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idPersona = auth()->user()?->id_persona ?: (int)$request->input('id_persona', 1);

        $firma = $this->firmaService->observarDocumento(
            (int)$request->input('id_documento'),
            $idPersona,
            $request->input('motivo')
        );

        return response()->json([
            'success' => true,
            'message' => 'Documento devuelto con observaciones.',
            'data' => $firma,
        ], Response::HTTP_OK);
    }
}
