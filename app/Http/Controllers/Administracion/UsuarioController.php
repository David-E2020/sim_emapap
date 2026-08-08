<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\PlantaUsuario;
use App\Models\Rol;
use App\Models\RolUser;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class UsuarioController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		$users = User::with('roles', 'permissions')->orderBy('id', 'asc')
			->where('usr_estado', 'A')
			->get()->makeHidden(['usr_new_password', 'deleted_at', 'usr_cargo_add', 'usr_archivo', 'usr_modificado', 'usr_registrado']);
		return $users;
	}

	public function asignarPuntoVenta($usarioId, $puntoVentaId) {
		$puntoventaUsers = PlantaUsuario::where('user_id', $usarioId)->first();
		if ($puntoventaUsers != null) {
			$puntoVenta = PlantaUsuario::find($puntoventaUsers->id);
			$puntoVenta->planta_id = $puntoVentaId;
			$puntoVenta->user_id = $usarioId;
			$puntoVenta->usr_modificado = Auth::user()->id;
			$puntoVenta->save();
		} else {
			$puntoVenta = new PlantaUsuario();
			$puntoVenta->planta_id = $puntoVentaId;
			$puntoVenta->user_id = $usarioId;
			$puntoVenta->usr_registrado = Auth::user()->id;
			$puntoVenta->save();
		}
		return $puntoVenta;
	}

	public function quitarSistema($id) {
		try {
			$user = User::find($id);
			$user->syncRoles([]);
			$user->revokePermissionTo('SIGP');
			$user->save();
			$result = array(
				'code' => 200,
				'message' => 'Se quito el acceso',
				'sistemas' => 'SIGP',
			);
			return response()->json($result, 200);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
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
			return array('porcentaje' => $resp, 'countActive' => $countActive, 'total' => $total); //.'/'.$countActive.' : '. $resp;
		} else {
			$resp = 100 / ($total / $countActive);
			return array('porcentaje' => $resp, 'countActive' => $countActive, 'total' => $total); //.'/'.$countActive.' : '. $resp;
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

	public function agregarSistema($id) {

		try {
			$user = User::find($id);
			$user->givePermissionTo('SIGP');
			$result = array(
				'code' => 200,
				'message' => 'Se asigno al sistema',
				'sistemas' => 'ACOPIO Y TRANSFORMACION',
			);
			return response()->json($result, 200);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}

	public function dar_acceso_usuario(Request $request) {
		try {
			$user = User::find($request->id);
			$user->sellingpoints()->sync([1]);
			$user->givePermissionTo('SAV');
			return response()->json(["success" => "true", "mensaje" => "Asignacion exitosa", "data" => $user]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}

	public function quitar_acceso_usuario(Request $request) {
		try {
			$user = User::find($request->id);
			$user->syncRoles([]);
			$user->revokePermissionTo(Acl::PERMISSION_SYSTEM);
			$user->save();
			return response()->json(["success" => "true", "mensaje" => "Eliminacion exitosa", "data" => $user]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {

	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {

	}
	public function usuario_rol() {
		try {
			$rol_usuario = PlantaUsuario::with('role_user', 'planta_usuario')->get();
			$resultado = $rol_usuario->map(function ($rol_usuario) {
				$rol_usuario->nombre_usuario = optional($rol_usuario->role_user)->usuario->name;
				$rol_usuario->nombre_rol = optional($rol_usuario->role_user)->rol->name;
				$rol_usuario->nombre_planta = optional($rol_usuario->planta_usuario)->nombre;
				return $rol_usuario;
			});
			return response()->json(["success" => "true", "mensaje" => "Listado de usuarios", "data" => $resultado]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}
}
