<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\Employee;
use App\Models\RRHH\Management;
use Illuminate\Http\Request;

class ManagementController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		//
		$magements = Management::get();
		return response()->json($magements);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	public function employees($management_id) {
		if ($management_id == 0) {
			$employees = Employee::with(
				['management' => function ($query) {
					$query->select('id', 'name');
				}, 'type_hours_employee', 'eventual_schedule'])->where('management_id', '>', 35)
				->where('status_employee', '=', 'A')
				->orderBy('last_name')->get();
		} else {
			$employees = Employee::with(['management' => function ($query) {
				$query->select('id', 'name');
			}, 'type_hours_employee', 'eventual_schedule'])->where('management_id', $management_id)
				->where('status_employee', '=', 'A')
				->orderBy('last_name')->get();
		}
		foreach ($employees as $key => $value) {
			$value->full = $value->first_name . ' ' . $value->second_name . ' ' . $value->last_name . ' ' . $value->mother_last_name;
		}
		return response()->json(compact('employees'));
	}
	public function employeesContract($management_id) {
		if ($management_id == 0) {
			$employees = Employee::with('management', 'contract_type')->where('management_id', '>', 35)
				->where('status_employee', '=', 'A')
				->orderBy('last_name')->get();
		} else {
			$employees = Employee::with('management', 'contract_type')->where('management_id', $management_id)
				->where('status_employee', '=', 'A')
				->orderBy('last_name')->get();
		}
		return response()->json(compact('employees'));
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
			$management = Management::find($request->id);
		} else {

			$management = new Management;
		}
		$management->name = $request->name;
		// $management->description = $request->description;
		$management->save();

		return $management;
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
		$management = Management::find($id);
		return response()->json(compact('management'));
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
		$management = Management::find($id);

		$count = Employee::where("management_id", $management->id)->count();
		if ($count > 0) {
			$name = "No se pudo eliminar debido a que la gerencia se encuentra asignado a un funcionario ";
		} else {
			$name = "Se Elimino " . $management->name;
			$management->delete();
		}
		return response()->json(compact('name'));
	}
}
