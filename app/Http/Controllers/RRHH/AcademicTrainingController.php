<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\AcademicTraining;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AcademicTrainingController extends Controller {
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
		//dd("bolver");
		$data_document = json_decode($request->file);
		try {
			if ($request->has('id')) {
				$academic_training = AcademicTraining::find($request->id);
			} else {
				$academic_training = new AcademicTraining;
			}
			$academic_training->employee_id = $request->employee_id;
			$academic_training->date = $request->date ?? Carbon::now();
			$academic_training->document = $request->document;
			$academic_training->grade = $request->grade;
			$academic_training->has_title = $request->has_title;
			$academic_training->instituion = $request->instituion;
			$academic_training->name = $request->name;
			$academic_training->state = $request->state;

			if (empty($data_document->name)) {
			} else {
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
					$nombreImagen = 'academic_trainings_' . Auth::user()->usr_usuario . time() . '_' . $data_document->name;
					\Storage::disk('academic_trainings')->put($nombreImagen, $data);
					$academic_training->file_path = $nombreImagen;

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
					$nombreImagen = 'academic_trainings_' . Auth::user()->usr_usuario . time() . '_' . $data_document->name;
					\Storage::disk('academic_trainings')->put($nombreImagen, $data);
					$academic_training->file_path = $nombreImagen;
				}

			}
			$academic_training->save();
			return response()->json(compact('academic_training'));
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json($ex);
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
		$academic_training = AcademicTraining::find($id);
		return response()->json(compact('academic_training'));
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
		$academic_training = AcademicTraining::find($id);
		$name = $academic_training->name;
		$academic_training->delete();
		return response()->json(compact('name'));
	}
}
