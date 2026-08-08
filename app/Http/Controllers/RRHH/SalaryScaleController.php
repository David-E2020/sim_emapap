<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\SalaryScale;
use Illuminate\Http\Request;

class SalaryScaleController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		//
		$salaries = SalaryScale::with('category')->get();
		return response()->json(compact('salaries'));
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
			$salary = SalaryScale::find($request->id);
		} else {
			$salary = new SalaryScale;
		}
		$salary->category_id = $request->category['id'];
		$salary->name = $request->name;
		$salary->class = $request->class;
		$salary->level = $request->level;
		$salary->number = $request->number;
		$salary->salary = $request->salary;
		$salary->cost = $request->cost;
		$salary->colateral = $request->colateral;
		$salary->total = $request->total;
		$salary->save();
		return response()->json(compact('salary'));
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
		$salary = SalaryScale::with('category')->find($id);
		return response()->json(compact('salary'));
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
