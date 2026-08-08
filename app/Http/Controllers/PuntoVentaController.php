<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use App\Models\PlantaUsuario;
use App\Models\PuntoVenta;
use App\Models\Silo;
use Auth;
use Illuminate\Http\Request;

class PuntoVentaController extends Controller {

	public function puntoVenta() {
		$user_ = Auth::user();
		$USER_ID = $user_->usr_id;

		$puntoventaUser = PlantaUsuario::with(['puntoventa' => function ($query) {
			$query->with(['sucursal']);
		}])->where('user_id', $USER_ID)->first();

		if ($puntoventaUser == null) {
			$result = array('message' => 'No tiene asignado un punto de venta');
			return response()->json($result, 406);
		}

		return $puntoventaUser->puntoventa;
	}

	public function puntoVentaUser($userId) {
		$puntoVenta = PlantaUsuario::where('user_id', $userId)->first();
		return $puntoVenta;
	}

	public function index() {

		$puntoVentas = Planta::with(['sucursal'])->get();
		return $puntoVentas;
	}

	public function show($id) {
		//
	}

	public function update(Request $request, $id) {
		//
	}

	public function destroy($id) {
		//
	}

	public function planta_asignada() {
		$user_ = Auth::user();
		$USER_ID = $user_->id;

		$puntoventaUser = PlantaUsuario::with(['planta' => function ($query) {
			$query->with(['sucursal']);
		}])->where('user_id', $USER_ID)->first();

		if ($puntoventaUser == null) {
			$result = array('message' => 'No tiene asignado una planta');
			return response()->json($result, 406);
		}
		$puntoVentaCodigo = $puntoventaUser->planta ?? '';
		$sucursalCodigo = $puntoventaUser->planta ?? '';
		return array(
			"puntoventaUser" => $puntoventaUser,
		);
	}
	public function listar_puntos() {
		$user_ = Auth::user();
		$USER_ID = $user_->id;

		$puntoventaUser = Silo::where('estado', 'A')
        ->orderby('id', 'desc')
        ->whereIn('tipo', ['Silo', 'Almacen'])
		->where('tipo_almacen','Acopio')
        ->get();
		if ($puntoventaUser == null) {
			$result = array('message' => 'No hay silo/almacenes/galpones disponibles');
			return response()->json($result, 406);
		}
		return $puntoventaUser;
	}
	

}
