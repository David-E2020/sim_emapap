<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\Language;
use Auth;
use Illuminate\Http\Request;

class LanguageController extends Controller {
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

		// return $request->all();
		if ($request->has('id')) {
			$language = Language::find($request->id);
		} else {
			$language = new Language;
		}
		$language->employee_id = $request->employee_id;
		$language->date = $request->date;
		$language->institution = $request->institution;
		$language->name = $request->name;
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
				$nombreImagen = 'language_' . Auth::user()->usr_usuario . time() . '_' . $data_document->name;
				\Storage::disk('language')->put($nombreImagen, $data);
				$language->file_path = $nombreImagen;

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
				$nombreImagen = 'language_' . Auth::user()->usr_usuario . time() . '_' . $data_document->name;
				\Storage::disk('language')->put($nombreImagen, $data);
				$language->file_path = $nombreImagen;
			}

		}
		$language->save();
		return response()->json(compact('language'));
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
		$language = Language::find($id);
		return response()->json(compact('language'));
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
		$language = Language::find($id);
		$name = $language->name;
		$language->delete();
		return response()->json(compact('name'));
	}
}
