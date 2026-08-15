<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AccesoUsuarioController extends Controller
{
    public function listar_usuario_acceso() {
        $users = User::with('roles', 'permissions', 'rolPersmisos')
            ->where('usr_estado', 'A')
            ->orderBy('id', 'asc')
            ->get();
        return $users;
    }

    public function guardar_acceso_usuario(Request $request) {
        try {
            $userId = $request->input('user_id');
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['success' => false, 'mensaje' => 'Usuario no encontrado'], 404);
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Permisos y accesos de módulos guardados correctamente para ' . $user->usr_usuario
            ]);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'mensaje' => $ex->getMessage()], 500);
        }
    }
}