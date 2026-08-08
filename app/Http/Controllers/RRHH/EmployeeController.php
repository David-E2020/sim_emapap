<?php

namespace App\Http\Controllers\RRHH;

use App\Helpers\Util;
use App\Http\Controllers\Controller;
use App\Models\RRHH\Employee;
use App\Models\RRHH\EmployeeRequest;
use App\Models\RRHH\Family;
use App\Models\RRHH\HistoricoCargo;
use App\Models\RRHH\Language;
use App\Models\RRHH\Position;
use App\Models\RRHH\Sanction;
use App\Models\RRHH\TypeHour;
use App\Models\RRHH\Vacation;
use App\Models\RRHH\WorkExperience;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployeeController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		//
		$employees = Employee::with('type_hours_employee', 'eventual_schedule')->select('id', 'first_name', 'second_name', 'last_name', 'mother_last_name', 'identity_card', 'position_id', 'management_id', 'city_identity_card_id', 'contribution_id')->with('position', 'management', 'city_identity_card', 'contribution')->orderBy('last_name')->get();
		foreach ($employees as $key => $value) {
			$value->full = $value->first_name . ' ' . $value->second_name . ' ' . $value->last_name . ' ' . $value->mother_last_name;
		}
		return response()->json($employees);
	}
	public function info() {
		$employee = Employee::with('position', 'management', 'families', 'academic_trainings', 'courses', 'languages', 'packages', 'country', 'contribution', 'health_box', 'works')->find(Auth::user()->employee->id);
		return response()->json(compact('employee'));
	}

	public function history($id) {
		$employee = Employee::with('position', 'history')->find($id);
		foreach ($employee->history as $item) {
			$item->user = $this->UserHistory($item->car_usr_id);
		}
		return response()->json(compact('employee'));
	}

	function UserHistory($id) {
		$user = User::find($id);
		return $user->usr_usuario;
	}

	public function dashboard() {
		$employee = Employee::with('position', 'management', 'location')->find(Auth::user()->employee->id);
		$fullname = $employee->getFullName();
		$employee_profile = $employee->img_profile;
		//cheking enabled vacatixons
		Util::checkVacations($employee);
		$vacation = Vacation::where('employee_id', $employee->id)->where('year', Carbon::now()->year)->first();
		$today = Carbon::now();
		$days = cal_days_in_month(CAL_GREGORIAN, $today->month, $today->year);
		$before = Carbon::now();
		if ($days <= 21) {
			//descontando un mes segun reglamento
			$before->subMonth(1);
			$to_date = $before;
			$from_date = $today;
		} else {
			$to_date = $today;
			$from_date = $before;
		}
		$before->day = 21; //seteando en 21 segun reglamento

		// $diference_day=$to_date->diffInDays($from_date);
		// if($diference_day<0)
		// {
		//     return 'no se pudo validar las fechas para el calculo favor de verificar';
		// }
		// $to_date = $before;
		// $from_date = $today;
		$omisiones = 0;
		$minutos_atraso = 0;
		$faltas = 0;
		$dias_haber = 0;
		$dias_haber_falta = 0;
		$attendances = [];
		$value = $to_date->isBefore($from_date);
		if ($to_date->isBefore($from_date)) {
			$temp = $to_date;
			$to_date = $from_date;
			$from_date = $temp;
		}
		$contador = 0;
		$history_hours = Array();
		$date = Carbon::now();
		$day = explode('-', $date)[2];
		$days = cal_days_in_month(CAL_GREGORIAN, $date->month, $date->year);
		$month = $date->month;
		$year = $date->year;
		if ($date->day > 20) {
			$mes = $month + 1;
			$fecha_inicio = $year . '-' . $month . '-' . '21';
			$fecha_fin = $year . '-' . $mes . '-' . '20';
		} else {
			$mes = $month - 1;
			$fecha_inicio = $year . '-' . $mes . '-' . '21';
			$fecha_fin = $year . '-' . $month . '-' . '20';
		}
		$from_date = Carbon::parse($fecha_inicio);
		$to_date = Carbon::parse($date);
		//return $to_date;
		foreach ($employee->type_hours_employee as $item) {
			$data = TypeHour::where('id', $item->type_hour_id)->first();
			$data->date_start = $item->date_start;
			$data->date_finish = $item->date_finish;
			array_push($history_hours, $data);
		}
		$employee->type_hours = $history_hours;
		while ($to_date->diffInDays($from_date) > 0) {
			//verificando cantidad de dias numericos
			$from_date->addDay(1);
			//return $from_date;
			// array_push($attendances,$attendance);
			foreach (Util::getAttendance($employee, $from_date->toDateString()) as $attendance) {
				$contador++;
				if ($attendance->title_entry == 'Omision') {
					$omisiones += 1;
				}

				if ($attendance->title_output == 'Omision') {
					$omisiones += 1;
				}

				if ($attendance->delay) {
					//return $attendance->delay;
					$minutos_atraso += $attendance->delay;
				}

				if ($attendance->title_entry == 'Sin Marcado' && $attendance->horario == 'NORMAL') {
					$faltas += 1;
				} else if ($attendance->title_entry == 'Sin Marcado' && $attendance->horario == 'CONTINUO') {
					$faltas += 0.5;
				}
				if ($attendance->title_output == 'Sin Marcado' && $attendance->horario == 'NORMAL') {
					$faltas += 1;
				} else if ($attendance->title_entry == 'Sin Marcado' && $attendance->horario == 'CONTINUO') {
					$faltas += 0.5;
				}
			}
		}
		//return $minutos_atraso;
		$sanction_atraso = 0;
		$sanction = Sanction::where('from', '<=', $minutos_atraso)
			->where('to', '>=', $minutos_atraso)
			->where('type', 'leve')
			->first();
		if ($sanction) {
			$dias_haber += $sanction->days;
			$sanction_atraso = $sanction->to - $minutos_atraso;
		}
		$omision_sanction = Sanction::where('from', '<=', $omisiones)
			->where('to', '>=', $omisiones)
			->where('type', 'omision')
			->first();
		$sanction_omision = 0;
		if ($omision_sanction) {
			$dias_haber += $omision_sanction->days;
			$sanction_omision += $omision_sanction->days;
		}

		/*************faltas********************************/
		$sanction_falta = 0;
		$falt = Sanction::where('from', '<=', $faltas)
			->where('to', '>=', $faltas)
			->where('type', 'falta')
			->first();
		if ($falt) {
			$dias_haber_falta += $falt->days;
			$sanction_falta += $falt->days;
		}
		//return $falt;
		/****************************************************/
		//return $sanction_omision;
		$fecha_actual = date('d/m/Y');
		$data_boleta = Array();
		try {
			$date = Carbon::now();
			$day = explode('-', $date)[2];
			$days = cal_days_in_month(CAL_GREGORIAN, $date->month, $date->year);
			$month = $date->month;
			$year = $date->year;
			$fecha_inicio = Carbon::now()->startofMonth()->format('Y-m-d');
			$fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
			$from_date = Carbon::parse($fecha_inicio);
			$to_date = Carbon::parse($fecha_fin);
			//$employee = Auth::user()->employee;
			$user = User::find(Auth::user()->usr_id);
			$employee_request = EmployeeRequest::where('employee_id', $employee->id)
				->whereDate('date', '>=', $from_date)
				->whereDate('date', '<=', $to_date)
				->where('employee_approve_id', 61)
				->where('state', 'Aprobado')
				->where('request_type_id', 5)
				->get();
			$tiempo_push = Array();
			$suma_tiempo = 0;
			if (count($employee_request) > 0) {
				foreach ($employee_request as $value) {
					$startTime = Carbon::parse($value->hour_in);
					$finishTime = Carbon::parse($value->hour_out);
					$totalDuration = $finishTime->diffInMinutes($startTime);
					$suma_tiempo += $totalDuration;
					$value->consumido = $totalDuration;
					array_push($tiempo_push, ["tiempo" => $totalDuration]);
				}
			} else {

			}
			if ($suma_tiempo <= 120) {
				$total = 120 - $suma_tiempo;
				if ($total == 0) {
					$contador = $total;
					$mensaje = "Ya no puede sacar boleta de este tipo se le acabo los minutos de permiso";
					$fecha_inicio_data = $fecha_inicio;
					$fecha_fin_data = $fecha_fin;
					array_push($data_boleta, ["contador" => $contador, "mensaje" => $mensaje, "fecha_inicio" => $fecha_inicio_data, "fecha_fin" => $fecha_fin_data, "validacion" => false]);
				} else {
					$contador = $total;
					$mensaje = "Le quedan " . $total . " minutos";
					$fecha_inicio_data = $fecha_inicio;
					$fecha_fin_data = $fecha_fin;
					array_push($data_boleta, ["contador" => $contador, "mensaje" => $mensaje, "fecha_inicio" => $fecha_inicio_data, "fecha_fin" => $fecha_fin_data, "validacion" => true]);
				}
			} else {
				$contador = $suma_tiempo;
				$mensaje = "Ya no puede sacar boleta de este tipo se le acabo los minutos de permiso";
				$fecha_inicio_data = $fecha_inicio;
				$fecha_fin_data = $fecha_fin;
				array_push($data_boleta, ["contador" => $contador, "mensaje" => $mensaje, "fecha_inicio" => $fecha_inicio_data, "fecha_fin" => $fecha_fin_data, "validacion" => false]);
			}
			$attendances = [];
			$marcado = Carbon::now();
			$marcado->subDay(1);
			$to_date = Carbon::parse($fecha_fin);
			$from_date = $marcado;
			$diference_day = $to_date->diffInDays($from_date);
			//return $diference_day;
			if ($diference_day < 0) {
				return 'no se pudo validar las fechas para el calculo favor de verificar';
			}

			while ($to_date->diffInDays($from_date) > 0) {
				//verificando cantidad de dias numericos
				$from_date->addDay(1);

				foreach (Util::getAttendance($employee, $from_date->toDateString()) as $attendance) {
					array_push($attendances, $attendance);
				}

			}
			$data_licencia = Array();
			$date = Carbon::now();
			$day = explode('-', $date)[2];
			$days = cal_days_in_month(CAL_GREGORIAN, $date->month, $date->year);
			$month = $date->month;
			$year = $date->year;
			$fecha_inicio = date("Y-01-01");
			$fecha_fin = date("Y-12-t");
			//return $fecha_fin;
			$from_date = Carbon::parse($fecha_inicio);
			$to_date = Carbon::parse($fecha_fin);
			//$employee = Auth::user()->employee;
			$user = User::find(Auth::user()->usr_id);
			$employee_request = EmployeeRequest::where('employee_id', $employee->id)
				->whereDate('date', '>=', $from_date)
				->whereDate('date', '<=', $to_date)
				->where('employee_approve_id', 61)
				->where('state', 'Aprobado')
				->where('request_type_id', 8)
				->get();
			$tiempo_push = Array();
			$suma_tiempo = 0;
			if (count($employee_request) > 0) {
				foreach ($employee_request as $value) {
					$startTime = Carbon::parse($value->hour_in);
					$finishTime = Carbon::parse($value->hour_out);
					$totalDuration = $finishTime->diffInHours($startTime);
					$suma_tiempo += $totalDuration;
					$value->consumido = $totalDuration . " Horas";
					array_push($tiempo_push, ["tiempo" => $totalDuration]);
				}
			} else {

			}
			if ($suma_tiempo <= 16) {
				$total = 16 - $suma_tiempo;
				if ($total == 0) {
					$mensaje = "Ya no le quedan Horas para sacar licencia";
					array_push($data_licencia, ["contador" => $total, "mensaje" => $mensaje, "fecha_inicio" => $fecha_inicio, "fecha_fin" => $fecha_fin, "validacion" => false]);

				} else {
					$mensaje = "Le quedan " . $total . " horas de licencia";
					array_push($data_licencia, ["contador" => $total, "mensaje" => $mensaje, "fecha_inicio" => $fecha_inicio, "fecha_fin" => $fecha_fin, "validacion" => true]);
				}
			} else {
				$mensaje = "Ya no le quedan Horas para sacar licencia";
				array_push($data_licencia, ["contador" => $total, "mensaje" => $mensaje, "fecha_inicio" => $fecha_inicio, "fecha_fin" => $fecha_fin, "validacion" => false]);
			}

			return response()->json(compact('employee', 'faltas', 'dias_haber_falta', 'sanction_falta', 'fullname', 'vacation', 'sanction_atraso', 'omisiones', 'faltas', 'employee_profile', 'fecha_actual', 'data_boleta', "attendances", 'data_licencia', 'minutos_atraso'));
		} catch (\Illuminate\Database\QueryException $ex) {
			$status = 'error';
			$message = 'No se puso enviar la solicitud';
			return response()->json(compact('status', 'message', 'ex'));
		}
	}
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	public function enabled(Request $request) {
		$employee = Employee::find($request->id);
		$employee->user_edit = $request->user_edit;
		$employee->save();
		return response()->json(compact('employee'));
	}
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$empleado = Employee::where('identity_card', $request->identity_card)->first();
		try {
			if ($request->has("id")) {
				$employee = Employee::find($request->id);
			} else {
				$employee = new Employee;
				$last_employee = Employee::max('id');
				$employee->id = $last_employee + 1;
				$employee->status_employee = 'A';
			}
			$employee->first_name = strtoupper($request->first_name);
			$employee->second_name = strtoupper($request->second_name);
			$employee->military_serial_number = strtoupper($request->military_serial_number);
			$employee->last_name = strtoupper($request->last_name);
			$employee->mother_last_name = strtoupper($request->mother_last_name);
			$employee->biometric_code = $request->biometric_code;
			$employee->identity_card = $request->identity_card;
			$employee->birth_date = $request->birth_date;
			$employee->cellphone = $request->cellphone;
			$employee->city_identity_card_id = $request->city_identity_card_id ?? 1;
			$employee->civil_status = $request->civil_status;
			$employee->contract_type_id = $request->contract_type_id;
			$employee->contract_modality_id = $request->contract_modality_id;
			$employee->contribution_id = $request->contribution_id; //adicionar la contribucion en la tabla XD
			$employee->country_id = $request->country_id;
			$employee->cua_nua = $request->cua_nua;
			$employee->disability = $request->disability ?? false;
			$employee->document_type_id = $request->document_type_id;
			$employee->entry_date = $request->entry_date;
			$employee->gender = $request->gender; //revisar el tipo de dato
			$employee->management_id = $request->management_id;
			$employee->phone = $request->phone;
			$employee->position_id = $request->position_id;
			$employee->profession = $request->profession;
			// $employee->reason = $request->reason;
			$employee->salary = $request->salary; //revisar el tipo de dato
			// $employee->tutor = $request->tutor;
			$employee->retirement_date = $request->retirement_date;
			$employee->unit_id = $request->unit_id;
			$employee->planta_id = $request->planta_id;
			$employee->address = $request->address;
			if ($request->hasFile('curriculum_file')) {
				//
				$employee->path_curriculum = $request->file('curriculum_file')->store('public/curriculums');
			}
			if ($request->hasFile('image_file')) {
				//
				$employee->employee_image_path = $request->file('image_file')->store('public/employee_images');
			}
			$employee->save();
			return response()->json(["success" => "true", "data" => $employee]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
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
		$employee = Employee::with('type_hours', 'type_hours_employee', 'eventual_schedule')->find($id);
		return response()->json(compact('employee'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		$employee = Employee::with('contribution', 'country')->find($id);
		return response()->json(compact('employee'));
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

	public function assign_type_hour(Request $request) {
		$employee = Employee::find($request->id);
		$ids = [];

		foreach ($request->type_hours as $type_hour) {
			array_push($ids, $type_hour['id']);
		}
		$employee->type_hours()->sync($ids);
		$name = $employee->getFullName();
		return response()->json(compact('name'));
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

	public function save_employee(Request $request) {
		try {
			$employee = Employee::find($request->id);
			$employee->first_name = strtoupper($request->first_name);
			$employee->second_name = strtoupper($request->second_name);
			$employee->last_name = strtoupper($request->last_name);
			$employee->mother_last_name = strtoupper($request->mother_last_name);
			$employee->identity_card = $request->identity_card;
			$employee->city_identity_card_id = $request->city_identity_card_id;
			$employee->birth_date = $request->birth_date;
			$employee->country_id = $request->country_id;
			$employee->civil_status = $request->civil_status;
			$employee->gender = $request->gender;
			$employee->has_military_card = $request->has_military_card;
			$employee->military_serial_number = $request->military_serial_number;
			$employee->disability = $request->disability;
			$employee->address = $request->address;
			$employee->phone = $request->phone;
			$employee->cellphone = $request->cellphone;
			$employee->corporate_cell = $request->corporate_cell;
			$employee->corporate_email = $request->corporate_email;
			$employee->personal_email = $request->personal_email;
			$employee->management_id = $request->management_id;
			$employee->position_id = $request->position ? $request->position['id'] : null;
			$employee->unit_id = $request->unit_id;

			//guardando datos referenciales
			$employee->contribution_id = $request->contribution_id;
			$employee->cua_nua = $request->cua_nua;
			$employee->bank = $request->bank;
			$employee->account_number = $request->account_number;
			$employee->healh_box_id = $request->healh_box_id;
			$employee->registration_number_medical = $request->registration_number_medical;
			$employee->blood_type = $request->blood_type;
			$employee->doctor_name = $request->doctor_name;
			$employee->number_dependency = $request->number_dependency;
			$employee->sworn_declaration = $request->sworn_declaration;
			$employee->date_sworn_declaration = $request->date_sworn_declaration;
			$employee->date_reception = $request->date_reception;
			$employee->number_declaration = $request->number_declaration;
			//tallas
			$employee->blouses = $request->blouses;
			$employee->shirt = $request->shirt;
			$employee->t_shirt = $request->t_shirt;
			$employee->jacket = $request->jacket;
			$employee->boots_number = $request->boots_number;

			$employee->user_edit = false;
			$employee->save();
			//guardando families
			//adicionar logica de borrado para cuando se tenga que hacer modificaciones a esto XD
			$families = Family::where('employee_id', $employee->id)->get();
			foreach ($families as $family) {
				$family->delete();
			}

			foreach ($request->families as $a_family) {
				$item = (object) $a_family;
				$family = new Family;
				$family->employee_id = $employee->id;
				$family->first_name = $item->first_name ?? '';
				$family->second_name = $item->second_name ?? '';
				$family->last_name = $item->last_name ?? '';
				$family->mother_last_name = $item->mother_last_name ?? '';
				$family->kinship_id = $item->kinship_id ?? 1;
				$family->age = $item->age;
				$family->birth_date = $item->birth_date ?? Carbon::now();
				$family->phone = $item->phone ?? 0;
				$family->cellphone = $item->cellphone ?? 0;
				$family->is_reference = isset($item->is_reference) ? true : false;
				$family->healh_box_id = $item->healh_box_id ?? 1;
				$family->number_healt_box = $item->number_healt_box ?? '';
				$family->has_vaccine = isset($item->has_vaccine) ? true : false;
				$family->save();

			}

			$cargo = Position::find($request->position ? $request->position['id'] : null);
			$historial = new HistoricoCargo;
			$historial->car_nombre = $cargo->name;
			$historial->car_fecha_modificacion = Carbon::now();
			$historial->car_employee_id = $employee->id;
			$historial->car_observaciones = "ACTUALIZACION DEL EMPLEADO REGISTRADO POR " . Auth::user()->usr_usuario;
			$historial->car_data = json_encode($request->all());
			$historial->car_usr_id = Auth::user()->usr_id;
			$historial->car_estado = 'A';
			$historial->save();

			$employee = Employee::with('position', 'management', 'families', 'academic_trainings', 'courses', 'languages', 'packages', 'country', 'contribution', 'health_box', 'works')->find(Auth::user()->employee->id);
			return response()->json(compact('employee'));
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json($ex);
		}
	}

	public function actualizar_empleado(Request $request) {
		try {
			$employee = Employee::find($request->id);
			$employee->first_name = strtoupper($request->first_name);
			$employee->second_name = $request->second_name ?? '';
			$employee->last_name = strtoupper($request->last_name);
			$employee->mother_last_name = $request->mother_last_name ?? '';
			$employee->identity_card = $request->identity_card;
			$employee->city_identity_card_id = $request->city_identity_card_id;
			$employee->birth_date = $request->birth_date;
			$employee->country_id = $request->country_id;
			$employee->civil_status = $request->civil_status;
			$employee->gender = $request->gender;
			$employee->has_military_card = $request->has_military_card;
			$employee->military_serial_number = $request->military_serial_number;
			$employee->disability = $request->disability;
			$employee->address = $request->address;
			$employee->phone = $request->phone;
			$employee->cellphone = $request->cellphone;
			$employee->corporate_cell = $request->corporate_cell;
			$employee->corporate_email = $request->corporate_email;
			$employee->personal_email = $request->personal_email;
			$employee->management_id = $request->management_id;
			$employee->position_id = $request->position ? $request->position['id'] : null;
			$employee->unit_id = $request->unit_id;

			//guardando datos referenciales
			$employee->contribution_id = $request->contribution_id;
			$employee->cua_nua = $request->cua_nua;
			$employee->bank = $request->bank;
			$employee->account_number = $request->account_number;
			$employee->healh_box_id = $request->healh_box_id;
			$employee->registration_number_medical = $request->registration_number_medical;
			$employee->blood_type = $request->blood_type;
			$employee->doctor_name = $request->doctor_name;
			$employee->number_dependency = $request->number_dependency;
			$employee->sworn_declaration = $request->sworn_declaration;
			$employee->date_sworn_declaration = $request->date_sworn_declaration;
			$employee->date_reception = $request->date_reception;
			$employee->number_declaration = $request->number_declaration;
			//tallas
			$employee->blouses = $request->blouses;
			$employee->shirt = $request->shirt;
			$employee->t_shirt = $request->t_shirt;
			$employee->jacket = $request->jacket;
			$employee->boots_number = $request->boots_number;

			$employee->user_edit = true;
			$employee->save();
			//guardando families
			//adicionar logica de borrado para cuando se tenga que hacer modificaciones a esto XD
			$families = Family::where('employee_id', $employee->id)->get();
			foreach ($families as $family) {
				$family->delete();
			}

			foreach ($request->families as $a_family) {
				$item = (object) $a_family;
				$family = new Family;
				$family->employee_id = $employee->id;
				$family->first_name = $item->first_name ?? '';
				$family->second_name = $item->second_name ?? '';
				$family->last_name = $item->last_name ?? '';
				$family->mother_last_name = $item->mother_last_name ?? '';
				$family->kinship_id = $item->kinship_id ?? 1;
				$family->age = $item->age;
				$family->birth_date = $item->birth_date ?? Carbon::now();
				$family->phone = $item->phone ?? 0;
				$family->cellphone = $item->cellphone ?? 0;
				$family->is_reference = isset($item->is_reference) ? true : false;
				$family->healh_box_id = $item->healh_box_id ?? 1;
				$family->number_healt_box = $item->number_healt_box ?? '';
				$family->has_vaccine = isset($item->has_vaccine) ? true : false;
				$family->save();

			}

			$languages = Language::where('employee_id', $employee->id)->get();
			foreach ($languages as $language) {
				$language->delete();
			}

			foreach ($request->languages as $a_language) {
				$item = (object) $a_language;
				$language = new Language;
				$language->employee_id = $employee->id;
				$language->name = $item->name;
				$language->institution = $item->institution;
				$language->date = $item->date ?? Carbon::now();
				$language->save();
			}

			$works = WorkExperience::where('employee_id', $employee->id)->get();
			foreach ($works as $work) {
				$work->delete();
			}

			foreach ($request->works as $a_work) {
				$item = (object) $a_work;
				$work = new WorkExperience;
				$work->employee_id = $employee->id;
				$work->position = $item->position;
				$work->phone = $item->phone;
				$work->institution = $item->institution;
				$work->date = $item->date ?? Carbon::now();
				$work->save();
			}
			$cargo = Position::find($request->position ? $request->position['id'] : null);
			$historial = new HistoricoCargo;
			$historial->car_nombre = $cargo->name;
			$historial->car_fecha_modificacion = Carbon::now();
			$historial->car_employee_id = $employee->id;
			$historial->car_observaciones = "ACTUALIZACION POR EL EMPLEADO";
			$historial->car_data = json_encode($request->all());
			$historial->car_usr_id = Auth::user()->usr_id;
			$historial->car_estado = 'A';
			$historial->save();
			$employee = Employee::with('position', 'management', 'families', 'academic_trainings', 'courses', 'languages', 'packages', 'country', 'contribution', 'health_box', 'works')->find(Auth::user()->employee->id);
			return response()->json(compact('employee'));
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json($ex);
		}

	}
	public function check() {
		try {
			$username = Auth::user()->usr_usuario;
			return response()->json(["success" => "true", "mensaje" => $username]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json($ex);
		}

	}

	public function updateFile(Request $request) {

		if ($request->img) {
			$logo = $request->img; //Se obtienen los datos de la imagen desde la solicitud.
			$imageName = $request->file('img');
			$nombreImagenfactura = 'foto_empleado_' . time() . '_' . $imageName->getClientOriginalName();

			$ext = explode(";", $logo); // Se dividen los datos, en este caso se obtiene la informacion antes del ";" "data:image/jpeg;".
			$ext = str_replace('data:image/', '', $ext); // Se reemplazan los datos "data:image/" por vacio para generar una nueva cadena y obtener la extension de la imagen "jpeg".
			$ext = $ext[0]; // En este punto $ext es un arreglo de datos, por lo tanto la extension de la imagen se encuentra en la primera posicion "0".
			$logo = str_replace('data:image/' . $ext . ';base64,', '', $logo); // Se elimina la data inicial de la imagen para luego ser decodificada.
			$logo = base64_decode($logo); //Se decodifica la data de la imagen recibida.
			$logoName = Str::random(20) . '.' . $ext; // Se asigna un nombre a la imagen recibida.
			\Storage::disk('employee_images')->put($nombreImagenfactura, \File::get($imageName));

		} else {
			$request->logo = "default-user.png";
		}

		$update_img = Employee::where('id', $request->id)
			->update([
				'employee_image_path' => $nombreImagenfactura,
				'img_profile' => true,
			]);
		$update_profile = Employee::find($request->id);
		return response()->json(["success" => "true", "data" => $update_profile]);

	}

	public function employees_contrac_include() {
		//
		$employees = Employee::with('position', 'management', 'city_identity_card', 'contribution', 'contract_type')->orderBy('last_name')->get();
		return response()->json($employees);
	}

	public function active() {
		$employees = Employee::select('id', 'first_name', 'second_name', 'last_name', 'mother_last_name', 'identity_card', 'position_id', 'management_id', 'city_identity_card_id', 'contribution_id', 'biometric_code', 'status_employee', 'user_edit')->with([
			'position' => function ($query) {
				$query->select('id', 'name');
			},
			'management' => function ($query) {
				$query->select('id', 'name');
			},
			'contract_type' => function ($query) {
				$query->select('id', 'name', 'id_modalidad', 'url_contrato', 'contrato');
			},
			'contribution' => function ($query) {
				$query->select('id', 'afp_name');
			}])
			->where('status_employee', 'A')->orderBy('last_name')->get();
		return response()->json($employees);
	}

	public function inactive() {
		//
		$employees = Employee::select('id', 'first_name', 'second_name', 'last_name', 'mother_last_name', 'identity_card', 'position_id', 'management_id', 'city_identity_card_id', 'contribution_id', 'biometric_code', 'status_employee', 'user_edit')
			->with([
				'position' => function ($query) {
					$query->select('id', 'name');
				},
				'management' => function ($query) {
					$query->select('id', 'name');
				},
				'contract_type' => function ($query) {
					$query->select('id', 'name', 'id_modalidad', 'url_contrato', 'contrato');
				},
				'contribution' => function ($query) {
					$query->select('id', 'afp_name');
				}])

			->where('status_employee', 'D')->orderBy('last_name')->get();
		return response()->json($employees);
	}

	public function desactivar(Request $request) {

		$employee = Employee::find($request->id);
		$employee->disengagement_date = 'now()';
		$employee->status_employee = 'D';
		$employee->save();
		return response()->json(compact('employee'));
	}

	public function activar(Request $request) {
		$employee = Employee::find($request->id);
		$employee->status_employee = 'A';
		$employee->save();
		return response()->json(compact('employee'));
	}

	public function info_to_rrhh(Request $request) {
		$employee = Employee::with('position', 'management', 'families', 'academic_trainings', 'courses', 'languages', 'packages', 'country', 'contribution', 'health_box', 'works')->find($request->id);
		return response()->json(compact('employee'));
	}

	public function curriculum() {
		$employees = Employee::all();
		return response()->json($employees);
	}

	public function matriz() {
		$vector = Array(2, 3, 10, 12);
		$m = 4;
		$n = 5;
		for ($i = 0; $i < $m; $i++) {
			for ($j = 0; $j < $n; $j++) {
				if ($n[$j] == 1) {
					array_push($n[$j] == $vector[$i]);
				}
			}
		}
	}
}
