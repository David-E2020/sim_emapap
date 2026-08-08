<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\Planta;
use Illuminate\Http\Request;

class LocationController extends Controller {
	public function index() {
		$locations = Planta::get();
		return response()->json(compact('locations'));
	}

	public function create() {
	}

	public function store(Request $request) {
		//
		if ($request->has('id')) {
			$Location = Planta::find($request->id);
		} else {
			$Location = new Planta;
		}
		$Location->name = $request->name;
		$Location->city_id = $request->city_id;
		$Location->save();
		return $Location;
	}

	public function show($id) {
	}

	public function edit($id) {
		$Location = Planta::find($id);
		return response()->json(compact('Location'));
	}

	public function update(Request $request, $id) {

	}

	public function destroy($id) {
		//
		$Location = Planta::find($id);
		$name = $Location->name;
		$Location->delete();
		return response()->json(compact('name'));
	}
}
