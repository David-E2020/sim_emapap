<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\Package;
use App\Models\RRHH\WorkExperience;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PackageController extends Controller {
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
		$data_document = json_decode($request->file);
		if ($request->has('id')) {
			$package = Package::find($request->id);
		} else {
			$package = new Package;
		}
		$package->employee_id = $request->employee_id;
		$package->date = $request->date;
		$package->institution = $request->institution;
		$package->name = $request->name;
		if (empty($data_document->name)) {
			//return "aaa";
		} else {
			//return "bbb";
			$ext = explode(".", $data_document->name);
			if ($ext[1] == 'pdf') {
				// Obtenemos la cadena desde request
				$base64_pdf = $data_document->src;
// Sustraemos todos los caracteres que están despues
				// de la primera coma ',', excluyendo así el comienzo
				// del string
				$data = substr($base64_pdf, strpos($base64_pdf, ',') + 1);
// Se decodifica
				$data = base64_decode($data);
// Y ahora lo guardamos
				$nombreImagen = 'packages_' . Auth::user()->usr_usuario . time() . '_' . $data_document->name;
				\Storage::disk('packages')->put($nombreImagen, $data);
				$package->file_path = $nombreImagen;

			} else {
				// Obtenemos la cadena desde request
				$base64_pdf = $data_document->src;
// Sustraemos todos los caracteres que están despues
				// de la primera coma ',', excluyendo así el comienzo
				// del string
				$data = substr($base64_pdf, strpos($base64_pdf, ',') + 1);
// Se decodifica
				$data = base64_decode($data);
// Y ahora lo guardamos
				$nombreImagen = 'packages_' . Auth::user()->usr_usuario . time() . '_' . $data_document->name;
				\Storage::disk('packages')->put($nombreImagen, $data);
				$package->file_path = $nombreImagen;
			}

			$package->save();
			return response()->json(compact('package'));
		}
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
		$package = Package::find($id);
		return response()->json(compact('package'));
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
		$package = Package::find($id);
		$name = $package->name;
		$package->delete();
		return response()->json(compact('name'));
	}

	public function store_work_experience(Request $request) {
		if ($request->has('id')) {
			$package = WorkExperience::find($request->id);
		} else {
			$package = new WorkExperience;
		}
		$package->employee_id = $request->employee_id;
		$package->mes_inicio = $request->mes_inicio;
		$package->anio_inicio = $request->anio_inicio;
		$package->mes_fin = $request->mes_fin;
		$package->anio_fin = $request->anio_fin;
		$package->date = Carbon::now();
		$package->phone = $request->phone ?? 0;
		$package->institution = $request->institution;
		$package->position = $request->position ?? "";
		$package->save();
		return response()->json(compact('package'));
	}

	public function work_delete($id) {
		//
		$package = WorkExperience::find($id);
		$name = $package->institution;
		$package->delete();
		return response()->json(compact('name'));
	}
}
