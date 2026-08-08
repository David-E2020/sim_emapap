<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use DB;
use Illuminate\Http\Request;

class AlmacenController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		$almacenes = Almacen::with(['linea:li_id,li_nombre'])->withCount([
			'existencias as stock_total' => function ($query) {
				$query->select(DB::raw("SUM(sk_cantidad_present) as stocktotal"));
			}])->withCount([
			'existencias as total_productos' => function ($query) {
				$query->where('sk_almacen_id', 1);
			}])->get();
		return $almacenes;
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		//
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
}
