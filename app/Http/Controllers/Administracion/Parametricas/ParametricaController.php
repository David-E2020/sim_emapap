<?php

namespace App\Http\Controllers\Administracion\Parametricas;

use App\Http\Controllers\Controller;
use App\Models\Parametrica;
use Auth;
use Illuminate\Http\Request;

class ParametricaController extends Controller {
	public function index() {
		$parametrica = Parametrica::where('param_codigo', 'ORIGEN')->where('param_valor', '=', 0)->orderBy('param_tabla', 'ASC')->get();
		foreach ($parametrica as $value) {
			$parametrica_contador = Parametrica::where('param_estado', 'A')
				->where('param_tabla', $value->param_tabla)
				->count();
			$value->param_valor_contador = $parametrica_contador;
		}
		return $parametrica;
	}

	public function create() {
		//
	}
	public function store(Request $request) {
		try {
			if ($request->id) {
				$parametrica = Parametrica::find($request->id);
				$parametrica->param_tabla = strtoupper($request->param_tabla) ?? '';
				$parametrica->param_nombre = strtoupper($request->param_nombre) ?? '';
				$parametrica->param_descripcion = strtoupper($request->param_descripcion) ?? '';
				$parametrica->param_codigo = 'ORIGEN';
				$parametrica->param_valor = 0 ?? 0;
				$parametrica->param_usr_registrado = Auth::user()->id;
				$parametrica->save();
			} else {
				$parametrica = new Parametrica;
				$parametrica->param_tabla = strtoupper($request->param_tabla) ?? '';
				$parametrica->param_nombre = strtoupper($request->param_nombre) ?? '';
				$parametrica->param_descripcion = strtoupper($request->param_descripcion) ?? '';
				$parametrica->param_codigo = 'ORIGEN' ?? '';
				$parametrica->param_valor = 0 ?? 0;
				$parametrica->param_usr_registrado = Auth::user()->id;
				$parametrica->save();
			}
			return response()->json(["success" => "true", "mensaje" => "La parametrica se registro correctamente", "data" => $request->all(), "resultado_campo" => $parametrica]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "No se pudo registrar la parametrica intente nuevamente", "data" => $ex]);
		}
	}

	public function show($param_tabla) {
		try {
			$parametrica = Parametrica::where('param_estado', 'A')->where('param_tabla', $param_tabla)->where('param_valor', '>', 0)->get();
			foreach ($parametrica as $value) {
				$parametrica_contador = Parametrica::where('param_estado', 'A')
					->where('param_tabla', $value->param_tabla)
					->count();
				$value->param_valor_contador = $parametrica_contador;
			}
			return $parametrica;
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "No se pudo mostrar la paramétrica intente nuevamente", "data" => $ex]);
		}
	}

	public function edit($id) {
		//
	}

	public function update(Request $request, $id) {
		//
	}

	public function destroy($id) {
		try {
			$proveedor = Parametrica::find($id);
			if ($proveedor->param_valor == 0) {
				$parametrica_update = Parametrica::where('param_tabla', $proveedor->param_tabla)->where('param_valor', '<>', 0)->where('param_estado', 'A')->get();
				if (count($parametrica_update) > 0) {
					return response()->json(["success" => "false", "mensaje" => "La paramétrica no se pudo eliminar correctamente porque existe sub campos en esta tabla", "data" => $proveedor]);
				} else {
					$proveedor->param_estado = 'B';
					$proveedor->param_usr_eliminado = Auth::user()->id;
					$proveedor->deleted_at = now();
					$proveedor->save();
					return response()->json(["success" => "true", "mensaje" => "La paramétrica se pudo eliminar correctamente", "data" => $proveedor]);

				}
			} else {
				$proveedor->param_estado = 'B';
				$proveedor->param_usr_eliminado = Auth::user()->id;
				$proveedor->deleted_at = now();
				$proveedor->save();
				return response()->json(["success" => "true", "mensaje" => "el campo se pudo eliminar correctamente", "data" => $proveedor]);

			}
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "No se pudo eliminar la paramétrica intente nuevamente", "data" => $ex]);
		}
	}

	public function registrar_campo(Request $request) {
		try {
			if ($request->id) {
				$parametrica = Parametrica::find($request->id);
				$parametrica->param_tabla = strtoupper($request->param_tabla) ?? '';
				$parametrica->param_nombre = strtoupper($request->param_nombre) ?? '';
				$parametrica->param_descripcion = strtoupper($request->param_detalle) ?? '';
				$parametrica->param_codigo = strtoupper($request->param_codigo) ?? '';
				$parametrica->param_valor = strtoupper($request->param_valor) ?? 0;
				$parametrica->param_usr_registrado = Auth::user()->id;
				$parametrica->save();
			} else {
				$parametricalistar = Parametrica::where('param_tabla', $request->param_tabla)->where('param_estado', 'A')->get();
				$parametrica = new Parametrica;
				$parametrica->param_tabla = strtoupper($request->param_tabla) ?? '';
				$parametrica->param_nombre = strtoupper($request->param_nombre) ?? '';
				$parametrica->param_descripcion = strtoupper($request->param_detalle) ?? '';
				$parametrica->param_codigo = strtoupper($request->param_codigo) ?? '';
				$parametrica->param_valor = count($parametricalistar) ?? 0;
				$parametrica->param_usr_registrado = Auth::user()->id;
				$parametrica->save();
			}
			return response()->json(["success" => "true", "mensaje" => "La parametrica se registro correctamente", "data" => $request->all()]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "No se pudo registrar el almacen intente nuevamente", "data" => $ex]);
		}
	}
}
