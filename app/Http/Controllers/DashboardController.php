<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\QueryException;

class DashboardController extends Controller
{
    public function total_acopio_general()
    {
        $fecha_actual = Carbon::now();
        $gestion = $fecha_actual->format('Y');
        try {
            $totales = \DB::select('select * from acopio.total_acopio_general(?)', [$gestion]);

            return response()->json(['success' => 'true', 'mensaje' => 'Acopio se registró correctamente', 'data' => $totales]);
        } catch (QueryException $ex) {
            return response()->json(['success' => 'false', 'mensaje' => $ex]);
        }
    }
}
