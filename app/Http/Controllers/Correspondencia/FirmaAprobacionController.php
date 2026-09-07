<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\Documento;
use App\Services\Correspondencia\FirmaDigitalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class FirmaAprobacionController extends Controller
{
    public function __construct(
        protected readonly FirmaDigitalService $firmaService
    ) {}

    /**
     * Bandeja de documentos pendientes de firma para el usuario
     */
    public function pendientes(Request $request): JsonResponse
    {
        $user = auth()->user();
        $idPersona = $request->filled('id_persona')
            ? (int) $request->input('id_persona')
            : ($user?->usr_externo_id ?: ($user?->id_persona ?: null));

        $verTodas = $request->boolean('todas') || ($request->input('bandeja') === 'TODAS');

        $query = Documento::with([
            'creador',
            'unidadGeneradora',
            'plantilla',
            'participantes.persona',
            'participantes.puesto',
            'firmasAprobaciones.persona',
        ])
            ->whereIn('estado', ['BORRADOR', 'EN_REVISION', 'OBSERVADO'])
            ->where('_estado', 'ACTIVO');

        if (! $verTodas && $idPersona) {
            $query->whereHas('participantes', function ($q) use ($idPersona) {
                $q->where('id_persona', $idPersona)
                    ->whereIn('tipo_participacion', ['REMITENTE_DE', 'VIA']);
            })
                ->whereDoesntHave('firmasAprobaciones', function ($q) use ($idPersona) {
                    $q->where('id_persona', $idPersona)->where('estado', 'FIRMADO');
                });
        }

        $pendientes = $query->orderBy('id', 'desc')->get();

        // Si el usuario específico no tiene pendientes directos o es admin, recuperar los pendientes generales
        if ($pendientes->isEmpty() && (! $idPersona || $user?->id === 1 || $user?->usr_usuario === 'admin' || ! auth()->check())) {
            $pendientes = Documento::with([
                'creador',
                'unidadGeneradora',
                'plantilla',
                'participantes.persona',
                'participantes.puesto',
                'firmasAprobaciones.persona',
            ])
                ->whereIn('estado', ['BORRADOR', 'EN_REVISION', 'OBSERVADO'])
                ->where('_estado', 'ACTIVO')
                ->orderBy('id', 'desc')
                ->get();
        }

        $pendientes->transform(function ($doc) use ($idPersona) {
            $personaCalculo = $idPersona ?: ($doc->participantes->whereIn('tipo_participacion', ['REMITENTE_DE', 'VIA'])->first()?->id_persona ?? 1);
            $doc->acciones_permitidas = $this->firmaService->calcularAccionesDocumento($doc, $personaCalculo);

            return $doc;
        });

        return response()->json([
            'success' => true,
            'data' => $pendientes,
        ], Response::HTTP_OK);
    }

    /**
     * Firmar / Aprobar documento con PIN de seguridad o certificado
     */
    public function firmar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_documento' => 'required|integer|exists:App\Models\Correspondencia\Documento,id',
            'pin' => 'nullable|string',
            'tipo_firma' => 'nullable|string|in:PIN_ELECTRONICO,TOKEN_DIGITAL,CIUDADANIA_DIGITAL',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = auth()->user();
        $idPersona = $request->filled('id_persona')
            ? (int) $request->input('id_persona')
            : ($user?->usr_externo_id ?: ($user?->id_persona ?: 1));
        $tipoFirma = $request->input('tipo_firma', 'PIN_ELECTRONICO');

        $firma = $this->firmaService->firmarDocumento(
            (int) $request->input('id_documento'),
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
            'id_documento' => 'required|integer|exists:App\Models\Correspondencia\Documento,id',
            'motivo' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = auth()->user();
        $idPersona = $request->filled('id_persona')
            ? (int) $request->input('id_persona')
            : ($user?->usr_externo_id ?: ($user?->id_persona ?: 1));
        $firma = $this->firmaService->rechazarDocumento(
            (int) $request->input('id_documento'),
            $idPersona,
            (string) $request->input('motivo')
        );

        return response()->json([
            'success' => true,
            'message' => 'Documento devuelto con observaciones.',
            'data' => $firma,
        ], Response::HTTP_OK);
    }
}
