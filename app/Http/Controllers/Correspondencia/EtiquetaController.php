<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\Etiqueta;
use App\Models\Correspondencia\EtiquetaParticipante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class EtiquetaController extends Controller
{
    public function index(): JsonResponse
    {
        $idUsuario = auth()->id() ?? 1;
        $etiquetas = Etiqueta::where('id_usuario', $idUsuario)->where('_estado', 'ACTIVO')->get();

        return response()->json(['success' => true, 'data' => $etiquetas], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idUsuario = auth()->id() ?? 1;
        $etiqueta = Etiqueta::firstOrCreate(
            [
                'nombre' => strtoupper(trim((string) $request->input('nombre'))),
                'id_usuario' => $idUsuario,
            ],
            [
                'color' => $request->input('color', '#1976D2'),
                '_usuario_creacion' => $idUsuario,
                '_fecha_creacion' => now(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Etiqueta creada.', 'data' => $etiqueta], Response::HTTP_CREATED);
    }

    public function asignar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_etiqueta' => 'required|integer|exists:App\Models\Correspondencia\Etiqueta,id',
            'id_hoja_ruta' => 'required|integer|exists:App\Models\Correspondencia\HojaRuta,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idUsuario = auth()->id() ?? 1;
        $asig = EtiquetaParticipante::firstOrCreate([
            'id_etiqueta' => $request->input('id_etiqueta'),
            'id_hoja_ruta' => $request->input('id_hoja_ruta'),
            'id_usuario' => $idUsuario,
        ], [
            '_usuario_creacion' => $idUsuario,
            '_fecha_creacion' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Etiqueta asignada.', 'data' => $asig], Response::HTTP_OK);
    }

    public function desasignar(Request $request): JsonResponse
    {
        $idUsuario = auth()->id() ?? 1;
        EtiquetaParticipante::where('id_etiqueta', $request->input('id_etiqueta'))
            ->where('id_hoja_ruta', $request->input('id_hoja_ruta'))
            ->where('id_usuario', $idUsuario)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Etiqueta quitada.'], Response::HTTP_OK);
    }
}
