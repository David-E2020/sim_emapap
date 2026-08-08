<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\Employee;
use App\Models\RRHH\EmployeeTypeHour;
use App\Models\RRHH\EventualSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventualScheduleController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		//

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
		foreach ($request->employees as $array) {
			$employee = (object) $array;
			// return $employee->id;
			$eventual_schudel = new EventualSchedule;
			$eventual_schudel->date = $request->date;
			$eventual_schudel->type_hour_id = $request->type_hour_id;
			$eventual_schudel->employee_id = $employee->id;
			$eventual_schudel->estado = 'A';
			$eventual_schudel->storage_id = Auth::user()->getStorage()->id;
			$eventual_schudel->save();
		}
		return $request->all();
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

	public function hour_register(Request $request) {
		try {
			$employee = Employee::where('status_employee', 'A')->where('management_id', $request->management)->get();
			foreach ($employee as $value) {
				$eventual_schudel = new EmployeeTypeHour;
				$eventual_schudel->date_start = $request->date_inicio;
				$eventual_schudel->date_finish = $request->date_fin;
				$eventual_schudel->type_hour_id = $request->type_hour_id;
				$eventual_schudel->employee_id = $value->id;
				$eventual_schudel->storage_id = Auth::user()->getStorage()->id;
				$eventual_schudel->save();
			}
			return response()->json(["success" => "true", "mensaje" => "registro masivo de horario exitoso", "data" => $eventual_schudel]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "registro masivo de horario exitoso", "data" => $ex]);
		}
	}

	public function hour_register_select(Request $request) {
		try {
			foreach ($request->employees as $array) {
				$employee = (object) $array;
				$eventual_schudel = new EmployeeTypeHour;
				$eventual_schudel->date_start = $request->date_inicio;
				$eventual_schudel->date_finish = $request->date_fin;
				$eventual_schudel->type_hour_id = $request->type_hour_id;
				$eventual_schudel->employee_id = $employee->id;
				$eventual_schudel->storage_id = Auth::user()->getStorage()->id;
				$eventual_schudel->save();
			}
			return response()->json(["success" => "true", "mensaje" => "registro masivo de horario exitoso", "data" => $eventual_schudel]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "registro masivo de horario exitoso", "data" => $ex]);
		}
	}
	public function hour_delete(Request $request) {
		//return $request->all();
		try {
			$eventual_schudel = EmployeeTypeHour::find($request->id);
			$eventual_schudel->delete();
			return response()->json(["success" => "true", "mensaje" => "eliminacion de horario", "data" => $eventual_schudel]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "consulta de datos erroneo", "data" => $ex]);
		}
	}

	public function hour_update(Request $request) {
		try {
			$eventual_schudel = EmployeeTypeHour::find($request->id);
			$eventual_schudel->date_start = $request->date_start;
			$eventual_schudel->date_finish = $request->date_finish;
			$eventual_schudel->type_hour_id = $request->type_hour_id;
			$eventual_schudel->employee_id = $request->employee_id;
			$eventual_schudel->save();
			return response()->json(["success" => "true", "mensaje" => "actualizacion de horario", "data" => $eventual_schudel]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "consulta de datos erroneo", "data" => $ex]);
		}

	}
}
