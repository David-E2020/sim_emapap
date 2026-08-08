<?php

namespace App\Http\Controllers;

use Jenssegers\Date\Date;

class DashboardController extends Controller {
	public function total_acopio_general() {
		$fecha_actual = Date::now();
		$gestion = $fecha_actual->format('Y');
		try {
			$totales = \DB::select('select * from acopio.total_acopio_general(?)', array($gestion));
			return response()->json(["success" => "true", "mensaje" => "Acopio se registró correctamente", "data" => $totales]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}
}
