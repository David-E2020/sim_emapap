<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\AccesoCompartido;
use App\Models\Correspondencia\HojaRuta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AccesoCompartidoController extends Controller
{
    public function index(): JsonResponse
    {
        $idUsuario = auth()->id() ?? 1;
        $compartidosConmigo = AccesoCompartido::with(['hojaRuta.documentos', 'usuarioAutorizador.persona'])
            ->where('id_usuario_destinatario', $idUsuario)
            ->where('_estado', 'ACTIVO')
            ->get();

        return response()->json(['success' => true, 'data' => $compartidosConmigo], Response::HTTP_OK);
    }

    public function compartir(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_hoja_ruta' => 'required|integer|exists:App\Models\Correspondencia\HojaRuta,id',
            'id_usuario_destinatario' => 'required|integer|exists:users,id',
            'dias_validez' => 'nullable|integer|min:1',
            'motivo' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $idUsuarioAuth = auth()->id() ?? 1;
        $dias = (int)$request->input('dias_validez', 7);

        $acceso = AccesoCompartido::create([
            'id_hoja_ruta' => $request->input('id_hoja_ruta'),
            'id_usuario_destinatario' => $request->input('id_usuario_destinatario'),
            'id_usuario_autorizador' => $idUsuarioAuth,
            'fecha_expiracion' => now()->addDays($dias),
            'motivo' => $request->input('motivo', 'Acceso temporal de consulta concedido.'),
            '_usuario_creacion' => $idUsuarioAuth,
            '_fecha_creacion' => now(),
        ]);

        // Actualizar contador
        $hr = HojaRuta::find($request->input('id_hoja_ruta'));
        if ($hr) {
            $hr->increment('cantidad_usuarios_compartidos');
        }

        return response()->json([
            'success' => true,
            'message' => 'Expediente compartido con éxito.',
            'data' => $acceso->fresh(['usuarioDestinatario.persona', 'hojaRuta']),
        ], Response::HTTP_CREATED);
    }
}
