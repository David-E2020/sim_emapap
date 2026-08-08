<?php

namespace App\Http\Controllers;

use App\Models\RolUser;
use App\Models\User;
use Illuminate\Http\Request;

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

		$input = $request->all();

		$rolId = $input['rol_id'];
		$usuarioId = $input['usuario_id'];

		$rolUser = RolUser::where('usuario_id', $usuarioId)->first();

		if ($rolUser != null) {

			$rolUser_ = RolUser::find($rolUser->id);

			$rolUser_->rol_id = $rolId;
			$rolUser_->save();
			return $rolUser;

		} else {
			$rol = RolUser::create($input);
			return $rol;
		}

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
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		//
	}

	public function update_user_password(Request $request) {
        //return $request->all();
        try {
            $usuario                   = User::find($request->id);
            $usuario->password         = bcrypt($request->password);
            $usuario->usr_new_password = $request->password;
            $usuario->name             = $usuario->name;
            $usuario->usr_usuario      = $usuario->usr_usuario;
            $usuario->email            = $usuario->email;
            $usuario->save();
            return response()->json(["success" => "true", "mensaje" => "Contraseña Actualizada", "data" => $usuario]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response()->json(["success" => "false", "mensaje" => "No se pudo actualizar la contraseña intente nuevamente", "data" => $ex]);
        }
    }
}
