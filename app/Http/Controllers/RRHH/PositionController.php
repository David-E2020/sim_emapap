<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\Employee;
use App\Models\RRHH\Position;
use Illuminate\Http\Request;

class PositionController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		//
		$positions = Position::with('salary_scale', 'unity', 'management')->get();
		return response()->json($positions);
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
		//
		if ($request->has('id')) {
			$position = Position::find($request->id);
		} else {
			$position = new Position;
			$last = Position::max('id');
			$position->id = $last + 1;
		}
		$position->name = $request->name;
		// $position->description = $request->description;
		$position->type_dependency = $request->type_dependency;
		if ($request->has('unit_id')) {
			$position->unit_id = $request->unit_id;
		} else {
			$position->unit_id = null;
		}
		if ($request->has('managament_id')) {
			$position->managament_id = $request->managament_id;
		} else {
			$position->managament_id = null;
		}
		$position->salary_scale_id = $request->salary_scale['id'];

		$position->save();

		return $position;
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
		$position = Position::with('salary_scale')->find($id);
		return response()->json($position);
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
		$position = Position::find($id);
		$count = Employee::where('position_id', $position->id)->count();
		if ($count == 0) {
			$name = "Se Elimino el Cargo " . $position->name;
			$position->delete();
		} else {
			$name = "No se pudo Eliminar Debido a que este cargo debido a que se encuentra asignado a un empleado actualmente";
		}
		return response()->json(compact('name'));
	}
}
