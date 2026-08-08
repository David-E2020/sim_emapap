<?php

namespace App\Http\Controllers;

use App\Models\Inventario\Parametricas;
use App\Models\Parametrica;
use App\Models\Planta;
use App\Models\PlantaUsuario;
use App\Models\RolUser;
use Auth;
use Illuminate\Http\Request;
use Jenssegers\Date\Date;
use Yajra\Datatables\Datatables;

class PlantaController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {

		$planta = Planta::where('estado', 'A')->orderby('id', 'desc')->get();
		return Datatables::of($planta)
			->addIndexColumn()
			->editColumn('created_at', function ($fac) {
				return $fac->created_at ? with(new Date($fac->created_at))->format('l j F Y H:m') : '';;
			})
			->make(true);
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
		try {
			if ($request->id) {
				$planta = Planta::find($request->id);
				$planta->nombre = strtoupper($request->nombre) ?? '';
				$planta->descripcion = strtoupper($request->descripcion) ?? '';
				$planta->codigo = strtoupper($request->codigo) ?? '';
				$planta->tipo_acopio_id = strtoupper($request->tipo_acopio_id) ?? 0;
				$planta->latitud = strtoupper($request->latitud) ?? '';
				$planta->longitud = strtoupper($request->longitud) ?? '';

				$planta->departamento_id = strtoupper($request->departamento_id) ?? 0;
				$planta->provincia_id = strtoupper($request->provincia_id) ?? 0;
				$planta->municipio_id = strtoupper($request->municipio_id) ?? 0;
				$planta->localidad_id = strtoupper($request->localidad_id) ?? 0;

				$planta->usr_modificado = Auth::user()->id;
				$planta->save();
				return response()->json(["success" => "true", "mensaje" => "La planta se actualizo correctamente", "data" => $planta]);
			} else {
				$query = Planta::where('codigo', strtoupper($request->codigo))->first();
				$query_nombre = Planta::where('nombre', strtoupper($request->nombre))->first();
				if ($query) {
					return response()->json(["success" => "false", "mensaje" => "El codigo de la  planta ya existe", "data" => []]);
				}
				if ($query_nombre) {
					return response()->json(["success" => "false", "mensaje" => "El nombre de la planta ya existe", "data" => []]);
				}
				$planta = new Planta;
				$planta->nombre = strtoupper($request->nombre) ?? '';
				$planta->descripcion = strtoupper($request->descripcion) ?? '';
				$planta->codigo = strtoupper($request->codigo) ?? '';
				$planta->tipo_acopio_id = strtoupper($request->tipo_acopio_id) ?? 0;
				$planta->latitud = strtoupper($request->latitud) ?? '';
				$planta->longitud = strtoupper($request->longitud) ?? '';
				
				$planta->departamento_id = strtoupper($request->departamento_id) ?? 0;
				$planta->provincia_id = strtoupper($request->provincia_id) ?? 0;
				$planta->municipio_id = strtoupper($request->municipio_id) ?? 0;
				$planta->localidad_id = strtoupper($request->localidad_id) ?? 0;

				$planta->usr_registrado = Auth::user()->id;
				$planta->save();
				return response()->json(["success" => "true", "mensaje" => "La planta se registro correctamente", "data" => $planta]);
			}
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "No se pudo registrar la linea intente nuevamente", "data" => $ex]);
		}
	}

	public function show($id) {
		//
	}

	public function edit($id) {
		//
	}

	public function update(Request $request, $id) {
		//
	}

	public function destroy($id) {
		try {
			$planta = Planta::find($id);
			$planta->estado = 'B';
			$planta->usr_eliminado = Auth::user()->id;
			$planta->delete();
			return response()->json(["success" => "true", "mensaje" => "La planta se pudo eliminar correctamente", "data" => $planta]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "No se pudo eliminar la planta intente nuevamente", "data" => $ex]);
		}
	}

	public function almacen() {

	}

	public function silos() {

	}

	public function listar_planta_gat() {

		$planta = Planta::where('estado', 'A')->whereIn('tipo_acopio_id', [1, 2])->where('id', '<>', 1)->orderby('id', 'desc')->get();
		return Datatables::of($planta)
			->addIndexColumn()
			->editColumn('created_at', function ($fac) {
				return $fac->created_at ? with(new Date($fac->created_at))->format('l j F Y H:m') : '';;
			})
			->make(true);
	}
	public function listar_planta_gat_destino($origen_planta_id) {

		$planta = Planta::where('estado', 'A')
					->whereIn('tipo_acopio_id', [1, 2])
					->where('id', '<>', 1)
					->where('id', $origen_planta_id)
					->orderby('id', 'desc')->get();
		return Datatables::of($planta)
			->addIndexColumn()
			->editColumn('created_at', function ($fac) {
				return $fac->created_at ? with(new Date($fac->created_at))->format('l j F Y H:m') : '';;
			})
			->make(true);
	}
	public function listar_tipo_plantas($tipo_solicitud_id) {
		switch ($tipo_solicitud_id) {
			case 3: //ORDEN DESPACHO
				$tipo_planta = Parametrica::where('param_tabla', 'TABLA_TIPO_PLANTA')
				->whereIn('param_valor', [1, 2, 3])
				->get();
				break;
			case 7: //ORDEN CARGA
				$tipo_planta = Parametrica::where('param_tabla', 'TABLA_TIPO_PLANTA')
				->whereIn('param_valor', [1, 2, 3])
				->get();
				break;
			case 4: //ORDEN TRASLADO
				$tipo_planta = Parametrica::where('param_tabla', 'TABLA_TIPO_PLANTA')
				->whereIn('param_valor', [1, 2])
				->get();
				break;
			case 8: //ORDEN ENVIO
				$tipo_planta = Parametrica::where('param_tabla', 'TABLA_TIPO_PLANTA')
				->whereIn('param_valor', [3, 5])
				->get();
				break;
		}
		return $tipo_planta;
	}
	public function listar_planta($tipo_planta_id) {

		$planta = Planta::where('estado', 'A')->where('tipo_acopio_id', $tipo_planta_id)->orderby('id', 'desc')->get();
		return Datatables::of($planta)
			->addIndexColumn()
			->editColumn('created_at', function ($fac) {
				return $fac->created_at ? with(new Date($fac->created_at))->format('l j F Y H:m') : '';;
			})
			->make(true);
	}
	public function listar_tipo_plantas_gat() {
		$tipo_planta = Parametrica::where('param_tabla', 'TABLA_TIPO_PLANTA')
		->whereIn('param_valor', [1, 2])
		->get();

		return $tipo_planta;
	}
	public function listar_tipo_plantas_gat_gc() {
		$tipo_planta = Parametrica::where('param_tabla', 'TABLA_TIPO_PLANTA')
		->whereIn('param_valor', [1, 2, 3])
		->get();

		return $tipo_planta;
	}
	public function listar_tipo_plantas_gc() {
		$tipo_planta = Parametrica::where('param_tabla', 'TABLA_TIPO_PLANTA')
		->whereIn('param_valor', [3])
		->get();

		return $tipo_planta;
	}
	public function verificar_planta_asignada_rol() {
		try {
			$user_ = Auth::user();
			$USER_ID = $user_->id;
			//VERIFICACION
			$rol = RolUser::with('usuario')->where('usuario_id', $USER_ID)->first();
			$rol_id = $rol->rol_id;
			switch ($rol_id) {
				case 14: //RESPONSABLE PUNTO
					$puntoUser = PlantaUsuario::with(['planta' => function ($query) {
						$query->with(['sucursal']);
					}])->where('user_id', $USER_ID)->first();
					$puntoUser = $puntoUser->planta;
					/*$usuarios = PlantaUsuario::where('planta_id', $puntoUser->planta[0]->id)
					->select('user_id')
					->get();
					$responsable = RolUser::with('usuario')->whereIn('usuario_id', $usuarios)->where('rol_id', 14)->first(); //responsable
					$puntoUser->responsable = $responsable->usuario->name ?? '';*/
				break;
				case 24: //CENTRAL GAT  //PENDIENTE PARA DIVIDIR POR PROGRAMA
					$puntoUser = Planta::with('tipo_planta', 'punto')->where('estado', 'A')->orderby('id', 'desc')->whereIn('tipo_acopio_id', [1, 2, 4])->get();
				break;
				case 25: //CENTRAL GC
					$puntoUser = Planta::with('tipo_planta', 'punto')->where('estado', 'A')->orderby('id', 'desc')->whereIn('tipo_acopio_id', [3])->get();
				break;
				case 1: //ADMIN
					$puntoUser = Planta::with('tipo_planta', 'punto')->where('estado', 'A')->orderby('id', 'desc')->whereIn('tipo_acopio_id', [1, 2, 3, 4])->get();
					$puntoUser->user_id = $USER_ID;
				break;
				/*case 27: //CENTRAL GAT  //PENDIENTE PARA DIVIDIR POR PROGRAMA
					$puntoUser = Planta::with('tipo_planta', 'punto')->where('estado', 'A')->orderby('id', 'desc')->whereIn('tipo_acopio_id', [1, 2, 3])->get();
				break;*/
				case 15: //LOGISTICA
					$puntoUser = Planta::with('tipo_planta', 'punto')->where('estado', 'A')->orderby('id', 'desc')->whereIn('tipo_acopio_id', [1, 2, 4])->get();
				break;
				
				default:
					$puntoUser = PlantaUsuario::with(['planta' => function ($query) {
						$query->with(['sucursal']);
					}])->where('user_id', $USER_ID)->first();
					$puntoUser = $puntoUser->planta;
			}
			if ($puntoUser == null) {
				$result = array('message' => 'No tiene asignado una planta');
				return response()->json($result, 406);
			}
			return response()->json(["success" => "true", "mensaje" => "Planta asignada correctamente", "data" => $puntoUser]);
		}	catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "No se listar, intente nuevamente", "data" => $ex]);
		}
	}
	public function verificar_planta_asignada_rol_tipo($tipo_planta_id) {
		try {
			$user_ = Auth::user();
			$USER_ID = $user_->id;
			//VERIFICACION
			$rol = RolUser::with('usuario')->where('usuario_id', $USER_ID)->first();
			$rol_id = $rol->rol_id;
			if($tipo_planta_id != 1000){
				switch ($rol_id) {
					case 14: //RESPONSABLE PUNTO
						$puntoUser = PlantaUsuario::with(['planta' => function ($query) {
							$query->with(['sucursal']);
						}])
						->WhereHas('planta', function ($query) use ($tipo_planta_id) {
							$query->where('tipo_acopio_id', $tipo_planta_id);
						})
						->where('user_id', $USER_ID)->first();
						$puntoUser = $puntoUser->planta?? '';
						/*$usuarios = PlantaUsuario::where('planta_id', $puntoUser->planta[0]->id)
						->select('user_id')
						->get();
						$responsable = RolUser::with('usuario')->whereIn('usuario_id', $usuarios)->where('rol_id', 14)->first(); //responsable
						$puntoUser->responsable = $responsable->usuario->name ?? '';*/
					break;
					case 24: //CENTRAL GAT  //PENDIENTE PARA DIVIDIR POR PROGRAMA
						$puntoUser = Planta::with('tipo_planta', 'punto')->where('estado', 'A')->orderby('id', 'desc')->whereIn('tipo_acopio_id', [1, 2, 4])->where('tipo_acopio_id', $tipo_planta_id)->get();
					break;
					case 25: //CENTRAL GC
						$puntoUser = Planta::with('tipo_planta', 'punto')->where('estado', 'A')->orderby('id', 'desc')->whereIn('tipo_acopio_id', [3])->where('tipo_acopio_id', $tipo_planta_id)->get();
					break;
					case 1: //ADMIN
						$puntoUser = Planta::with('tipo_planta', 'punto')->where('estado', 'A')->orderby('id', 'desc')->whereIn('tipo_acopio_id', [1, 2, 3, 4])->get();
						$puntoUser->user_id = $USER_ID;
					break;
					/*case 27: //CENTRAL GAT  //PENDIENTE PARA DIVIDIR POR PROGRAMA
						$puntoUser = Planta::with('tipo_planta', 'punto')->where('estado', 'A')->orderby('id', 'desc')->whereIn('tipo_acopio_id', [1, 2, 3])->get();
					break;*/
					case 15: //LOGISTICA
						$puntoUser = Planta::with('tipo_planta', 'punto')->where('estado', 'A')->orderby('id', 'desc')->whereIn('tipo_acopio_id', [1, 2, 4])->get();
					break;
					default:
						$puntoUser = PlantaUsuario::with(['planta' => function ($query) {
							$query->with(['sucursal']);
						}])
						->WhereHas('planta', function ($query) use ($tipo_planta_id) {
							$query->where('tipo_acopio_id', $tipo_planta_id);
						})
						->where('user_id', $USER_ID)->first();
						$puntoUser = $puntoUser->planta ?? '';
				}
			}
            else {
				if($rol_id == 1 || $rol_id == 24 || $rol_id == 15 || $rol_id == 29){
                    $puntoUser = Planta::with('tipo_planta', 'punto')->where('estado', 'A')->orderby('id', 'desc')->whereIn('tipo_acopio_id', [1, 2, 4])->get();
						$puntoUser->user_id = $USER_ID;
				} else {
					$result = array('message' => 'No tiene permisos');
			        return response()->json($result, 406);
				}
				
			}
			if ($puntoUser == null) {
				$result = array('message' => 'No tiene asignado una planta');
				return response()->json($result, 406);
			}
			return response()->json(["success" => "true", "mensaje" => "Planta asignada correctamente", "data" => $puntoUser]);
		}	catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "No se listar, intente nuevamente", "data" => $ex]);
		}
	}

	public function usuario_rol_planta($planta_id) {
		try {
			$rol_id = 14; //responsable
			$rol_usuario = PlantaUsuario::with('role_user')
				->where('planta_id', $planta_id)
				->WhereHas('role_user', function ($query) use ($rol_id) {
					$query->where('rol_id', $rol_id);
				})
				->get();
			//return $rol_usuario;
			foreach ($rol_usuario as $value) {
				$value->usuario_nombre = $value->role_user->usuario->name ?? '';
				$value->usuario_id = $value->role_user->usuario->id ?? '';

			}
			return response()->json(["success" => "true", "mensaje" => "Listado de usuarios", "data" => $rol_usuario]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}
	public function verificar_planta_asignada() {
		$user_ = Auth::user();
		$USER_ID = $user_->id;

		$puntoUser = PlantaUsuario::with(['planta' => function ($query) {
			$query->with(['sucursal']);
		}])->where('user_id', $USER_ID)->first();
        
        $usuarios = PlantaUsuario::where('planta_id', $puntoUser->planta[0]->id)
        ->select('user_id')
        ->get();
        $responsable = RolUser::with('usuario')->whereIn('usuario_id', $usuarios)->where('rol_id', 14)->first(); //responsable
        $puntoUser->responsable = $responsable->usuario->name ?? '';

		if ($puntoUser == null) {
			$result = array('message' => 'No tiene asignado una planta');
			return response()->json($result, 406);
		}
		return $puntoUser;
	}
}
