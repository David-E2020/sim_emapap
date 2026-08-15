<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use App\Models\RolUser;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;

class AuthController extends Controller {

	public function login(Request $request) {

		$credentials = $request->only('usr_usuario', 'password');
		$rules       = [
			'usr_usuario' => 'required',
			'password'    => 'required',
		];
		$validator = Validator::make($credentials, $rules);

		if ($validator->fails()) {
			return response()->json([
				'status'  => 'error',
				'message' => $validator->messages(),
			], 400);
		}

		try {
			// Intentar verificar credenciales y generar token JWT
			$token = JWTAuth::attempt($credentials);

			if (!$token) {
				return response()->json([
					'status'  => 'error',
					'message' => 'Las credenciales son incorrectas.',
				], 401);
			}
		} catch (JWTException $e) {
			return response()->json([
				'status'  => 'error',
				'message' => 'No se pudo iniciar sesión, intente nuevamente.',
			], 500);
		}

		$user = Auth::user();
		$usuarioId_ = $user->id;
		$rolUser_   = RolUser::where('usuario_id', $usuarioId_)->first();
		$rol_       = $rolUser_ ? Rol::find($rolUser_->rol_id) : null;

		// Obtener todos los nombres de permisos de Spatie (directos + heredados de roles)
		$spatiePermissions = $user->getAllPermissions()->pluck('name');

		return response()->json([
			'status'      => 'success',
			'token'       => $token,
			'user'        => $user,
			'permissions' => $spatiePermissions,
			'roles'       => $user->getRoleNames(),
			'rol'         => $rol_ ? $rol_->name : ($user->roles->first() ? $user->roles->first()->name : 'Usuario'),
			'rute_home'   => 'dashboard',
		]);

	}

	public function logout(Request $request) {

		$token = $request->header('Authorization');

		try {
			JWTAuth::invalidate($token);
			return response()->json([
				'status'  => 'success',
				'message' => "Sesión cerrada correctamente.",
			]);
		} catch (JWTException $e) {
			return response()->json([
				'status'  => 'error',
				'message' => 'Fallo al cerrar sesión, intente nuevamente.',
			], 500);
		}
	}
}
