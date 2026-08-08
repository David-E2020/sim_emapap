<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\TypeHour;
use Illuminate\Http\Request;

class TypeHourController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		//
		$type_hours = TypeHour::all();
		return response()->json(compact('type_hours'));
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
			$type_hour = TypeHour::find($request->id);
		} else {
			$type_hour = new TypeHour;
		}
		$type_hour->name = $request->name;
		$type_hour->code = $request->code;
		$type_hour->work_shift = $request->work_shift;
		$type_hour->entry = $request->entry;
		$type_hour->start_of_entry = $request->start_of_entry;
		$type_hour->end_of_entry = $request->end_of_entry;
		$type_hour->tolerance_entry = $request->tolerance_entry;
		$type_hour->output = $request->output;
		$type_hour->start_of_output = $request->start_of_output;
		$type_hour->end_of_output = $request->end_of_output;
		$type_hour->tolerance_output = $request->tolerance_output;
		$type_hour->location_id = $request->location ? $request->location['id'] : null;
		$type_hour->monday = $request->monday ? true : false;
		$type_hour->tuesday = $request->tuesday ? true : false;
		$type_hour->wednesday = $request->wednesday ? true : false;
		$type_hour->thursday = $request->thursday ? true : false;
		$type_hour->friday = $request->friday ? true : false;
		$type_hour->saturday = $request->saturday ? true : false;
		$type_hour->sunday = $request->sunday ? true : false;
		$type_hour->hours = $request->hours;
		$type_hour->save();

		return response()->json(compact('type_hour'));

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
		$type_hour = TypeHour::find($id);
		return response()->json(compact('type_hour'));
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
		$type_hour = TypeHour::find($id);
		$name = $type_hour->name;
		$type_hour->delete();
		return response()->json(compact('name'));
	}
}
