<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\ContractType;
use Illuminate\Http\Request;

class ContractTypeController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		//
		$contract_types = ContractType::all();
		return response()->json($contract_types);
	}
	public function index2() {
		$contract_types = ContractType::with('Contract_modality')->get();
		return response()->json($contract_types);
	}

	public function selectContrPlantillas() {
		$contract_types = ContractType::where('url_contrato', '<>', '')->get();
		// $contract_types = ContractType::all();
		return response()->json($contract_types);
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
			$contract_type = ContractType::find($request->id);
		} else {

			$contract_type = new ContractType;
		}
		$contract_type->name = $request->name;
		$contract_type->id_modalidad = $request->id_modalidad;
		if ($request->hasFile('image_file')) {
			$contract_type->url_contrato = $request->file('image_file')->store('public/plantillas_contratos');
		}
		$contract_type->contrato = '';
		$contract_type->save();

		return $contract_type;
	}

	public function guarda_con_imagen(Request $request) {
		if ($request->has('id')) {
			$contract_type = ContractType::find($request->id);
		} else {

			$contract_type = new ContractType;
		}
		$contract_type->name = $request->name;
		$contract_type->id_modalidad = $request->id_modalidad;
		if ($request->hasFile('image_file')) {
			$contract_type->url_contrato = $request->file('image_file')->store('public/plantillas_contratos');
		}

		$contract_type->contrato = '';
		$contract_type->save();

		return response()->json(["success" => "true", "data" => $contract_type]);
	}

	public function upload_plantilla(Request $request) {
		$employee_request = ContractType::find($request->id);

		if ($request->hasFile('image_file')) {
			//
			$employee_request->url_contrato = $request->file('image_file')->store('public/plantillas_contratos');
		}
		// \Storage::disk('local')->put('ferdy.docx', \File());
		$employee_request->save();
		return response()->json(compact('employee_request'));
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
		$contract_type = ContractType::find($id);
		return response()->json(compact('contract_type'));
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
		$contract_type = ContractType::find($id);
		$name = $contract_type->name;
		$contract_type->delete();
		return response()->json(compact('name'));
	}
}
