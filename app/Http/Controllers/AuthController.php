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
			]);
		}
		try {
			// Attempt to verify the credentials and create a token for the user
			$token = JWTAuth::attempt($credentials);

			if (!$token) {
				return response()->json([
					'status'  => 'error',
					'message' => 'Las credenciales son incorrectas.',
				], 401);
			}
		} catch (JWTException $e) {
			// Something went wrong with JWT Auth.
			return response()->json([
				'status'  => 'error',
				'message' => 'No se pudo iniciar sesión, intente nuevamente.',
			], 500);
		}

		$usuarioId_ = Auth::user()->id;
		$rolUser_   = RolUser::where('usuario_id', $usuarioId_)->first();
		$usuario    = User::where('id', $usuarioId_)
			->whereHas('permissions', function ($query) {
				$query->where('name', 'SIGP');
			})->first();
		if (empty($usuario)) {
			return response()->json([
				'status'  => 'error',
				'message' => 'El usuario no tiene Acceso al sistema de Acopio y Transformación',
			], 401);
		}
		if ($rolUser_ != null) {
			$menuRol_ = MenuRol::where('rol_id', $rolUser_->rol_id)->where('check', true)->first();
			if ($menuRol_ != null) {
				$menu_ = Menu::find($menuRol_->menu_id);
				$rol_  = Rol::find($rolUser_->rol_id);
				if ($menu_->route != null) {
					$puntos = Auth::user()->getSellingPoints();
					if (count($puntos) > 0) {
						session()->put('almacen_id', $puntos[0]->id);
					} else {
						return response()->json([
							'status'  => 'error',
							'message' => 'El usuario no tiene asignado ninguna PLANTA MOLINO O INGENIO.',
						], 401);
					}
					return response()->json([
						'status'      => 'success',
						'token'       => $token,
						'user'        => Auth::user(),
						'permissions' => Auth::user()->permissions,
						'roles'       => Auth::user()->roles,
						'employee'    => Auth::user()->employee,
						'rol'         => $rol_->name,
						'rute_home'   => 'dashboard',
					]);
				} else {
					return response()->json([
						'status'  => 'error',
						'message' => 'Verifique el menu asignado.',
					], 401);
				}
			} else {
				return response()->json([
					'status'  => 'error',
					'message' => 'No tiene asignado un Menu.',
				], 401);
			}
		} else {
			return response()->json([
				'status'  => 'error',
				'message' => 'No tiene asignado un ROL.',
			], 401);
		}

	}

	public function logout(Request $request) {

		// Get JWT Token from the request header key "Authorization"
		$token = $request->header('Authorization');

		try {
			JWTAuth::invalidate($token);
			// $this->guard()->logout();
			return response()->json([
				'status'  => 'success',
				'message' => "User successfully logged out.",
			]);
		} catch (JWTException $e) {
			// something went wrong whilst attempting to encode the token
			return response()->json([
				'status'  => 'error',
				'message' => 'Failed to logout, please try again.',
			], 500);
		}
	}

	public function login_aplicativo_movil(Request $request) {

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
			]);
		}
		try {
			// Attempt to verify the credentials and create a token for the user
			$token = JWTAuth::attempt($credentials);

			if (!$token) {
				return response()->json([
					'status'  => 'error',
					'message' => 'Las credenciales son incorrectas.',
				], 401);
			}
		} catch (JWTException $e) {
			// Something went wrong with JWT Auth.
			return response()->json([
				'status'  => 'error',
				'message' => 'No se pudo iniciar sesión, intente nuevamente.',
			], 500);
		}

		$usuarioId_ = Auth::user()->id;
		$rolUser_   = RolUser::where('usuario_id', $usuarioId_)->first();
		$usuario    = User::where('id', $usuarioId_)
			->whereHas('permissions', function ($query) {
				$query->where('name', 'SIESUBSIDIO');
			})->first();

		
		if (empty($usuario)) {
			return response()->json([
				'status'  => 'restringido',
				'message' => 'El usuario no tiene Acceso al aplicativo SIE-SUBSIDIO',
				'test'=> User::where('id', $usuarioId_)
				->whereHas('permissions', function ($query) {
					$query->where('name', 'SIESUBSIDIO');
				})->toSql()
			], 401);
		}
		// if ($rolUser_ != null) {
		// 	$menuRol_ = MenuRol::where('rol_id', $rolUser_->rol_id)->where('check', true)->first();
		// 	if ($menuRol_ != null) {
		// 		$menu_ = Menu::find($menuRol_->menu_id);
		// 		$rol_  = Rol::find($rolUser_->rol_id);
		// 		if ($menu_->route != null) {
					$puntos = Auth::user()->getSellingPointsSEDEM();
					if (count($puntos) > 0) {
						session()->put('almacen_id', $puntos[0]->id);
					} else {
						return response()->json([
							'status'  => 'restringido',
							'message' => 'El usuario no tiene asignado ningun Punto de Venta Asignado.',
						], 401);
					}
					return response()->json([
						'status'      => 'success',
						'token'       => $token,
						'user'        => Auth::user(),
						'permissions' => Auth::user()->permissions,
						'employee'    => Auth::user()->employee,
						'almacen'     => Auth::user()->getSellingPointsSEDEM(),
					]);
		// 		} else {
		// 			return response()->json([
		// 				'status'  => 'error',
		// 				'message' => 'Verifique el menu asignado.',
		// 			], 401);
		// 		}
		// 	} else {
		// 		return response()->json([
		// 			'status'  => 'error',
		// 			'message' => 'No tiene asignado un Menu.',
		// 		], 401);
		// 	}
		// } else {
		// 	return response()->json([
		// 		'status'  => 'error',
		// 		'message' => 'No tiene asignado un ROL.',
		// 	], 401);
		// }

	}
	
}
