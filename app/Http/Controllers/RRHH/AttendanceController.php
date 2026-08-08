<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\Employee;
use App\Models\RRHH\EmployeeRequest;
use App\Models\RRHH\TypeHour;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Log;
use Util;

class AttendanceController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		$funcionario = Auth::user()->employee;
		$employee = Employee::with('type_hours')->find($funcionario->id);
		$date = Carbon::now();
		$day = explode('-', $date)[2];
		$days = cal_days_in_month(CAL_GREGORIAN, $date->month, $date->year);
		$month = $date->month;
		$year = $date->year;

		$from_date = Carbon::now();
		$from_date->subDays(40);
		$to_date = Carbon::now();
		$to_date->addDay(20);
		//return $from_date->format('Y-m-d');
		//return $employee;
		$history_hours = Array();
		foreach ($employee->type_hours_employee as $item) {
			$data = TypeHour::where('id', $item->type_hour_id)->first();
			$data->date_start = $item->date_start;
			$data->date_finish = $item->date_finish;
			array_push($history_hours, $data);
		}
		$employee->type_hours = $history_hours;
		// $employee = Employee::find(23);
		//return $employee;
		$attendances = [];

		$diference_day = $to_date->diffInDays($to_date);
		if ($diference_day < 0) {
			return 'no se pudo validar las fechas para el calculo favor de verificar';
		}

		while ($to_date->diffInDays($from_date) > 0) {
			//verificando cantidad de dias numericos
			Log::info($from_date->toDateString());
			$from_date->addDay(1);

			Log::info('Imprimiendo resultado');
			foreach (Util::getAttendance($employee, $from_date->toDateString()) as $attendance) {
				array_push($attendances, $attendance);
			}
			// Log::info(json_encode(  ));

		}
		return response()->json(compact('attendances'));
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
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		try {
			$employee_request = EmployeeRequest::with('request_type', 'approves', 'employee')->find($id);
			return response()->json(["success" => "true", "data" => $employee_request]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
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
}
