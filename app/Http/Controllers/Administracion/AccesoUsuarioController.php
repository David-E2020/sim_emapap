<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\User;

class AccesoUsuarioController extends Controller
{
    public function listar_usuario_acceso() {
        $users = User::with('rolPersmisos', 'permissions')
            ->orderBy('id', 'asc')
            ->get();
        return $users;
    }
}