<?php

namespace App\Http\Controllers;

use App\Models\RolUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolUserController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$validator = Validator::make($request->all(), [
			'rol_id' => 'required|integer',
			'usuario_id' => 'required|integer',
		]);

		if ($validator->fails()) {
			return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
		}

		$rolId = $request->input('rol_id');
		$usuarioId = $request->input('usuario_id');

		$rolUser = RolUser::where('usuario_id', $usuarioId)->first();

		if ($rolUser != null) {
			$rolUser_ = RolUser::find($rolUser->id);
			$rolUser_->rol_id = $rolId;
			$rolUser_->save();
			$resp = $rolUser_;
		} else {
			$resp = RolUser::create([
				'rol_id' => $rolId,
				'usuario_id' => $usuarioId,
			]);
		}

		// Sincronizar Spatie roles y permisos en backend
		$targetUser = User::find($usuarioId);
		if ($targetUser) {
			$permission = Permission::firstOrCreate(['name' => 'SIGP', 'guard_name' => 'api']);
			$targetUser->givePermissionTo($permission);
			$role = Role::find($rolId);
			if ($role) {
				$targetUser->syncRoles([$role]);
			}
		}

		return response()->json($resp, 200);
	}

	/**
	 * Actualización segura de contraseña de usuario.
	 * Corrige la vulnerabilidad IDOR / BOLA.
	 */
	public function update_user_password(Request $request) {
		$authUser = Auth::guard('api')->user();

		if (!$authUser) {
			return response()->json(["success" => false, "mensaje" => "No autorizado"], 401);
		}

		$validator = Validator::make($request->all(), [
			'password' => 'required|string|min:6',
			'current_password' => 'nullable|string',
			'id' => 'nullable|integer',
		]);

		if ($validator->fails()) {
			return response()->json(["success" => false, "mensaje" => $validator->errors()->first()], 422);
		}

		$targetUserId = $request->input('id');

		// REGLA DE SEGURIDAD IDOR:
		// Si no se envía ID o el ID es igual al usuario autenticado, cambia su propia clave.
		if (!$targetUserId || (int)$targetUserId === (int)$authUser->id) {
			$userToUpdate = User::find($authUser->id);

			// Verificar contraseña actual si fue provista
			if ($request->has('current_password') && !Hash::check($request->current_password, $userToUpdate->password)) {
				return response()->json(["success" => false, "mensaje" => "La contraseña actual es incorrecta"], 400);
			}
		} else {
			// Si intenta cambiar la clave de OTRO usuario, debe ser Administrador
			if (!$authUser->hasRole('Administrador General')) {
				return response()->json(["success" => false, "mensaje" => "Acceso denegado: No tiene permisos para modificar este usuario"], 403);
			}

			$userToUpdate = User::find($targetUserId);
			if (!$userToUpdate) {
				return response()->json(["success" => false, "mensaje" => "Usuario no encontrado"], 404);
			}
		}

		try {
			$userToUpdate->password = bcrypt($request->password);
			// Eliminar guardado en texto claro por seguridad
			$userToUpdate->usr_new_password = null;
			$userToUpdate->save();

			return response()->json([
				"success" => true,
				"mensaje" => "Contraseña actualizada de forma segura",
			], 200);
		} catch (\Exception $ex) {
			return response()->json(["success" => false, "mensaje" => "Error al actualizar la contraseña"], 500);
		}
	}
}
