<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\Course;
use Auth;
use Illuminate\Http\Request;

class CourseController extends Controller {
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
		//return $data_document->name;
		if ($request->has('id')) {
			$course = Course::find($request->id);
		} else {
			$course = new Course;
		}
		$course->employee_id = $request->employee_id;
		$course->date = $request->date;
		$course->institution = $request->institution;
		$course->name = $request->name;
		$course->hours = $request->hours;
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
				$nombreImagen = 'cource_document_' . Auth::user()->usr_usuario . time() . '_' . $data_document->name;
				\Storage::disk('courses')->put($nombreImagen, $data);
				$course->file_path = $nombreImagen;

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
				$nombreImagen = 'cource_document_' . Auth::user()->usr_usuario . time() . '_' . $data_document->name;
				\Storage::disk('courses')->put($nombreImagen, $data);
				$course->file_path = $nombreImagen;
			}

		}
		$course->save();
		return response()->json(compact('course'));
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
		$course = Course::find($id);
		return response()->json(compact('course'));
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
		$course = Course::find($id);
		$name = $course->name;
		$course->delete();
		return response()->json(compact('name'));
	}
}
