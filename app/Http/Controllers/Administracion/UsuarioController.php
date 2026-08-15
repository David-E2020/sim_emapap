<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use App\Models\RolUser;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Auth;
use Illuminate\Http\Request;

class UsuarioController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		$users = User::with('roles', 'permissions', 'rolPersmisos')->orderBy('id', 'asc')
			->where('usr_estado', 'A')
			->get()->makeHidden(['usr_new_password', 'deleted_at', 'usr_cargo_add', 'usr_archivo', 'usr_modificado', 'usr_registrado']);
		return $users;
	}

	public function quitarSistema($id) {
		try {
			$user = User::find($id);
			if ($user) {
				$user->syncPermissions([]);
				$user->syncRoles([]);
				RolUser::where('usuario_id', $id)->forceDelete();
			}
			$result = array(
				'code' => 200,
				'message' => 'Se quitó el acceso al sistema',
			);
			return response()->json($result, 200);
		} catch (\Exception $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex->getMessage()]);
		}
	}

	public function agregarSistema($id) {
		try {
			$user = User::find($id);
			if ($user) {
				// Sincronizar Spatie permission 'SIGP'
				$permission = Permission::firstOrCreate(['name' => 'SIGP', 'guard_name' => 'api']);
				$user->givePermissionTo($permission);

				// Obtener el rol predeterminado o primer rol
				$role = Role::where('guard_name', 'api')->first() ?: Role::first();
				if ($role) {
					$user->assignRole($role);
					RolUser::firstOrCreate([
						'usuario_id' => $user->id,
						'rol_id' => $role->id,
					]);
				}
			}
			$result = array(
				'code' => 200,
				'message' => 'Se asignó el acceso al sistema correctamente',
			);
			return response()->json($result, 200);
		} catch (\Exception $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex->getMessage()]);
		}
	}

	public function rolUser($userId) {
		$rolUser_ = RolUser::where('usuario_id', $userId)->first();

		$usuarioRolId = null;
		if ($rolUser_ != null) {
			$usuarioRolId = $rolUser_->rol_id;
		}
		$roles_ = Rol::all();
		return array('roles' => $roles_->toArray(), 'rolUser' => $usuarioRolId);
	}

	public function menuAcopio($usuarioId) {
		$rolUser_ = RolUser::where('usuario_id', $usuarioId)->first();

		$menus_ = [];
		if ($rolUser_ != null) {
			$rolId = $rolUser_->rol_id;
			$menuRol_ = MenuRol::where('rol_id', $rolId)->where('check', true)->get()->pluck('menu_id');
			$menus_ = Menu::with(['subMenuN1' => function ($query) {
				$query->orderBy('order', 'asc');
			}])->where('menu_id', '=', null)->orderBy('order', 'asc')->get();
		} else {
			$menuRol_ = collect([]);
			$menus_ = Menu::with(['subMenuN1' => function ($query) {
				$query->orderBy('order', 'asc');
			}])->where('menu_id', '=', null)->orderBy('order', 'asc')->get();
		}

		$menusResp = [];
		foreach ($menus_ as $key => $value) {
			$subMenus = $value->subMenuN1;
			$subMenuComex = $this->verificaEstadoSubmenuAcopio($subMenus, $menuRol_);
			$nroMenus = count($subMenuComex);

			if ($nroMenus != 0) {
				$value->sub_menu = $subMenuComex;
				$menusResp[] = $value;
			}
		}

		return array('menus' => $menusResp);
	}

	public function menuRol($rolId) {
		$menuRol_ = MenuRol::where('rol_id', $rolId)->where('check', true)->get()->pluck('menu_id');
		$menus_ = Menu::with('subMenuN1')->where('menu_id', '=', null)->get();

		$menusResp = [];
		foreach ($menus_ as $key => $value) {
			$subMenus = $value->subMenuN1;
			$subM_ = $this->verificaEstadoSubmenu($subMenus, $menuRol_);
			$value->sub_menu = $subM_;
			$value->progreso = $this->progresoMenu($subM_);
			$menusResp[] = $value;
		}

		return array('menus' => $menusResp);
	}

	public function progresoMenu($menus) {
		$resp = 0;
		$total = count($menus);
		$countActive = 0;

		foreach ($menus as $key => $value) {
			if ($value->active) {
				$countActive = $countActive + 1;
			}
		}

		if ($countActive == 0) {
			return array('porcentaje' => $resp, 'countActive' => $countActive, 'total' => $total);
		} else {
			$resp = 100 / ($total / $countActive);
			return array('porcentaje' => $resp, 'countActive' => $countActive, 'total' => $total);
		}
	}

	public function verificaEstadoSubmenu($subMenus, $arrayMenuUser) {
		$resp = [];
		if (!is_array($subMenus)) {
			foreach ($subMenus as $key => $value) {
				if (in_array($value->id, $arrayMenuUser->toArray())) {
					$value->active = true;
					$resp[] = $value;
				} else {
					$value->active = false;
					$resp[] = $value;
				}
			}
		}
		return $resp;
	}

	public function verificaEstadoSubmenuAcopio($subMenus, $arrayMenuUser) {
		$resp = [];
		if (!is_array($subMenus)) {
			foreach ($subMenus as $key => $value) {
				if (in_array($value->id, $arrayMenuUser->toArray())) {
					$value->active = null;
					$resp[] = $value;
				}
			}
		}
		return $resp;
	}

	public function usuario_rol() {
		try {
			$users = User::with('roles', 'rolPersmisos')->get();
			return response()->json(["success" => "true", "mensaje" => "Listado de usuarios", "data" => $users]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex->getMessage()]);
		}
	}
}
