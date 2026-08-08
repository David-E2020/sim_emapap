<?php

namespace App\Http\Controllers\RRHH;

use App;
use App\Http\Controllers\Controller;
use App\Models\RRHH\AttendanceEmployee;
use App\Models\RRHH\Employee;
use App\Models\RRHH\EmployeeRequest;
use App\Models\RRHH\Location;
use App\Models\RRHH\Management;
use App\Models\RRHH\Position;
use App\Models\RRHH\Refreshment;
use App\Models\RRHH\Sanction;
use App\Models\RRHH\TypeHour;
use App\Models\RRHH\Unity;
use App\Models\RRHH\User;
use App\Models\RRHH\Vacation;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Util;

class ReportController extends Controller {
	public function ficha_personal($id) {
		$title = "FICHA PERSONAL";
		$area = 'DEPARTAMENTO DE GESTIÓN DEL TALENTO HUMANO';
		$date = date('d-m-Y');
		$username = User::where('usr_prs_id', $id)->first();
		$type_report = "FICHA TECNICA";
		$employee = Employee::with('families', 'courses', 'languages', 'packages', 'academic_trainings')->find($id);
		$view = \View::make('report.ficha_personal', compact('title', 'date', 'username', 'area', 'type_report', 'employee'));
		$html_content = $view->render();
		$pdf = App::make('snappy.pdf.wrapper');
		$pdf->loadHTML($html_content);
		$pdf->setOption('disable-javascript', true);
		$pdf->setOption('images', true);
		$pdf->stream();
		return $pdf->inline();
	}

	public function boleta($id) {
		$employee_request = EmployeeRequest::find($id);
		//return $employee_request;
		if ($employee_request->request_type->id == 11) {
			$title = 'FORMULARIO ÚNICO DE SOLICITUD DE VERIFICACION EN CÁMARAS POR OMISIONES DE MARCADO';
		} else {
			$title = $employee_request->request_type->name;
		}
		$date = date('d-m-Y');
		$persona = $employee_request->employee->getFullName();
		$gerencia = $employee_request->employee->management->name ?? '';
		$unidad = Unity::find($employee_request->employee->unit_id)->name ?? '';
		if ($employee_request->request_type->id == 11) {
			$view = \View::make('report.boleta_biometrico', compact('title', 'date', 'persona', 'gerencia', 'unidad', 'employee_request'));
		} else {
			$view = \View::make('report.boleta', compact('title', 'date', 'persona', 'gerencia', 'unidad', 'employee_request'));
		}
		$html_content = $view->render();
		$pdf = App::make('snappy.pdf.wrapper');
		$pdf->loadHTML($html_content);
		return $pdf->inline();

	}
	public function attendance_employee_date($employee_id, $from_date, $to_date) {
		//dd("aa");
		$minutos_atraso = 0;
		$omisiones = 0;
		$dias_haber = 0;
		$horas_trabajadas = 0;
		$horas_adicionales = 0;
		$cantidad_atrasos = 0;
		$cantidad_faltas = 0;
		$entry = null;
		$output = null;

		$employee = Employee::find($employee_id);
		$from_date_inicio = Carbon::parse($from_date);
		$to_date_inicio = Carbon::parse($to_date);
		$title = 'Tarjeta de Asistencia';
		$date = date('d-m-Y');

		$persona = $employee->getFullName();
		//return $persona;
		$cargo = $employee->position->name ?? '';

		$days = cal_days_in_month(CAL_GREGORIAN, $from_date_inicio->month, $from_date_inicio->year);
		$day = $from_date_inicio->day;
		$month = $from_date_inicio->month;
		$year = $from_date_inicio->year;
		$attendances = [];
		//valid
		$diference_day = $to_date_inicio->diffInDays($from_date_inicio);
		if ($diference_day < 0) {
			return 'no se pudo validar las fechas para el calculo favor de verificar';
		}
		$history_hours = Array();

		foreach ($employee->type_hours_employee as $item) {
			$data = TypeHour::where('id', $item->type_hour_id)->first();
			$data->date_start = $item->date_start;
			$data->date_finish = $item->date_finish;
			array_push($history_hours, $data);
		}
		$employee->type_hours = $history_hours;
		while ($to_date_inicio->diffInDays($from_date_inicio) > 0) {
			//verificando cantidad de dias numericos
			//Log::info($from_date->toDateString());
			$from_date_inicio->addDay(1);

			foreach (Util::getAttendance($employee, $from_date_inicio->toDateString()) as $attendance) {
				array_push($attendances, $attendance);
			}

		}
		$tipo_horario = Employee::with('type_hours')->find($employee_id);
		$horario = "";
		if (count($tipo_horario->type_hours) > 0) {
			foreach ($tipo_horario->type_hours as $hora) {
				if ($hora->name == 'GRUPO 1 PRESENCIAL DE 8m A 4pm') {
					$horario = "CONTINUO";
				} else {
					$horario = "NORMAL";
				}
			}
		} else {
			$horario = "SIN_ASIGNAR";
		}
		//return $t_date;
		$minutos_atrasos = 0;
		$omisiones = 0;
		$dias_haber = 0;
		$dias_haber_falta = 0;
		$horas_trabajadas = "";
		$horas_work = Array();
		$horas_adicionales = 0;
		$cantidad_atrasos = 0;
		$cantidad_faltas = 0;
		$atrasos = 0;
		$faltas = 0;
		$employee = Employee::find($employee_id);
		$from_date = Carbon::parse($from_date);
		$to_date = Carbon::parse($to_date);
		$history_hours = Array();
		//return $from_date;
		foreach ($employee->type_hours_employee as $item) {
			$data = TypeHour::where('id', $item->type_hour_id)->first();
			$data->date_start = $item->date_start;
			$data->date_finish = $item->date_finish;
			array_push($history_hours, $data);
		}
		$employee->type_hours = $history_hours;
		//estableciendo metodo de calculo XD
		$diference_day = $to_date->diffInDays($from_date);
		if ($diference_day < 0) {
			return 'no se pudo validar las fechas para el calculo favor de verificar';
		}
		$vector_demo = Array();
		while ($to_date->diffInDays($from_date) > 0) {
			//verificando cantidad de dias numericos
			//Log::info($from_date->toDateString());
			$from_date->addDay(1);

			foreach (Util::getAttendance($employee, $from_date->toDateString()) as $attendance) {
				if ($attendance->title_entry == 'Omision') {
					$omisiones += 1;
				}
				if ($attendance->title_output == 'Omision') {
					$omisiones = $omisiones + 1;
				}
				if ($attendance->delay) {
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

				//return $attendance->hours_worked;
				array_push($horas_work, $attendance->hours_worked);
				$horas_adicionales += $attendance->surplus;
				array_push($vector_demo, $attendance);
			}

		}
		//return $faltas;
		$horas_trabajadas = $this->suma_horas($horas_work);
		//return $horas_trabajadas;
		//return gmdate("H:i:s", $horas_trabajadas);
		$sanction_atraso = 0;
		$sanction = Sanction::where('from', '<=', $minutos_atraso)
			->where('to', '>=', $minutos_atraso)
			->where('type', 'leve')
			->first();
		if ($sanction) {
			$dias_haber += $sanction->days;
			$sanction_atraso += $sanction->days;
			//$sanction_atraso = $sanction->to - $minutos_atraso;
		}
		//return $minutos_atraso;
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
		$discount_day = (float) $employee->salary / 30;
		$discount = $discount_day * $dias_haber;

		$date = Carbon::now();
		$title = 'Tarjeta de Asistencia';
		$persona = $employee->getFullName();
		$gerencia = $employee->management ? $employee->management->name : '';
		$unidad = $employee->unity ? $employee->unity->name : '';
		$horas_adicionales = (float) $horas_adicionales / 60;
		//$horas_trabajadas = (float) $horas_trabajadas / 60;
		$horas_adicionales = Util::formatMoney($horas_adicionales);
		$view = \View::make('report.attendance_kardex_detail', compact(
			'title',
			'date',
			'persona',
			'cargo',
			'attendances',
			'horario',
			'date',
			'persona',
			'employee',
			'unidad',
			'gerencia',
			'minutos_atraso',
			'omisiones',
			'sanction',
			'omision_sanction',
			'faltas',
			'discount',
			'dias_haber',
			'dias_haber_falta',
			'discount_day',
			'sanction_atraso',
			'sanction_omision',
			'cantidad_atrasos',
			'horas_adicionales',
			'sanction_falta',
			'horas_trabajadas'
		));
		$html_content = $view->render();
		$pdf = App::make('snappy.pdf.wrapper');
		$pdf->loadHTML($html_content);
		return $pdf->inline();
	}

	public function attendance_employee_complet($employee_id, $from_date, $to_date) {
		$minutos_atraso = 0;
		$omisiones = 0;
		$dias_haber = 0;
		$horas_trabajadas = 0;
		$horas_adicionales = 0;
		$cantidad_atrasos = 0;
		$cantidad_faltas = 0;
		$entry = null;
		$output = null;

		$employee = Employee::find($employee_id);
		$title = 'Tarjeta de Asistencia Historico Biometrico';
		$date = date('d-m-Y');
		$persona = $employee->getFullName();
		$cargo = $employee->position->name ?? '';
		$attendances = AttendanceEmployee::whereDate('date', '>=', $from_date)->whereDate('date', '<=', $to_date)->where('employee_id', $employee_id)->orderby('date', 'asc')->orderby('time', 'asc')->get();
		$tipo_horario = Employee::with('type_hours')->find($employee_id);
		$horario = "";
		if (count($tipo_horario->type_hours) > 0) {
			foreach ($tipo_horario->type_hours as $hora) {
				if ($hora->name == 'GRUPO 1 PRESENCIAL DE 8m A 4pm') {
					$horario = "CONTINUO";
				} else {
					$horario = "NORMAL";
				}
			}
		} else {
			$horario = "SIN_ASIGNAR";
		}
		$view = \View::make('report.attendance_kardex_complet', compact('title', 'date', 'persona', 'cargo', 'attendances', 'horario'));
		$html_content = $view->render();
		$pdf = App::make('snappy.pdf.wrapper');
		$pdf->loadHTML($html_content);
		return $pdf->inline();
	}

	function suma_horas($arrHoras) {
		$temp = 0;
		$segundos = 0;
		$minutos = 0;
		$horas = 0;
		foreach ($arrHoras as $hora) {
			if ($hora == "00:00:00") {
				# code...
			} else {
				$hora = explode(":", $hora);
				$segundos += $hora[2];
				$minutos += $hora[1];
				if ($hora[0] == 12) {
					$horas += 00;
				} else {
					$horas += $hora[0];
				}
			}
		}

		//sumo segundos
		while ($segundos >= 60) {
			$segundos = $segundos - 60;
			$temp++;
		}
		$minutos += $temp;

		$temp = 0;
		while ($minutos >= 60) {
			$minutos -= 60;
			$temp++;
		}

		//sumo horas
		$horas += $temp;

		if ($horas < 10) {$horas = '0' . $horas;}
		if ($minutos < 10) {$minutos = '0' . $minutos;}
		if ($segundos < 10) {$segundos = '0' . $segundos;}

		$sum_hrs = $horas . "  Horas  " . $minutos . "  Minutos";
		return $sum_hrs;
	}
	public function attendance_employee($employee_id, $f_date, $t_date) {
		//return $t_date;
		$minutos_atraso = 0;
		$omisiones = 0;
		$dias_haber = 0;
		$dias_haber_falta = 0;
		$horas_trabajadas = "";
		$horas_work = Array();
		$horas_adicionales = 0;
		$cantidad_atrasos = 0;
		$cantidad_faltas = 0;
		$atrasos = 0;
		$faltas = 0;
		$employee = Employee::find($employee_id);
		$from_date = Carbon::parse($f_date);
		$to_date = Carbon::parse($t_date);
		$history_hours = Array();
		foreach ($employee->type_hours_employee as $item) {
			$data = TypeHour::where('id', $item->type_hour_id)->first();
			$data->date_start = $item->date_start;
			$data->date_finish = $item->date_finish;
			array_push($history_hours, $data);
		}
		$employee->type_hours = $history_hours;
		//estableciendo metodo de calculo XD
		$diference_day = $to_date->diffInDays($from_date);
		if ($diference_day < 0) {
			return 'no se pudo validar las fechas para el calculo favor de verificar';
		}
		$vector_demo = Array();
		while ($to_date->diffInDays($from_date) > 0) {
			//verificando cantidad de dias numericos
			//Log::info($from_date->toDateString());
			$from_date->addDay(1);

			foreach (Util::getAttendance($employee, $from_date->toDateString()) as $attendance) {
				if ($attendance->title_entry == 'Omision') {
					$omisiones += 1;
				}
				if ($attendance->title_output == 'Omision') {
					$omisiones = $omisiones + 1;
				}
				if ($attendance->delay) {
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

				//return $attendance->hours_worked;
				array_push($horas_work, $attendance->hours_worked);
				$horas_adicionales += $attendance->surplus;
				array_push($vector_demo, $attendance);
			}

		}
		//return $faltas;
		$horas_trabajadas = $this->suma_horas($horas_work);
		//return $horas_trabajadas;
		//return gmdate("H:i:s", $horas_trabajadas);
		$sanction_atraso = 0;
		$sanction = Sanction::where('from', '<=', $minutos_atraso)
			->where('to', '>=', $minutos_atraso)
			->where('type', 'leve')
			->first();
		if ($sanction) {
			$dias_haber += $sanction->days;
			$sanction_atraso += $sanction->days;
			//$sanction_atraso = $sanction->to - $minutos_atraso;
		}
		//return $minutos_atraso;
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
		$discount_day = (float) $employee->salary / 30;
		$discount = $discount_day * $dias_haber;

		$date = Carbon::now();
		$title = 'Tarjeta de Asistencia';
		$persona = $employee->getFullName();
		$gerencia = $employee->management ? $employee->management->name : '';
		$unidad = $employee->unity ? $employee->unity->name : '';
		$horas_adicionales = (float) $horas_adicionales / 60;
		//$horas_trabajadas = (float) $horas_trabajadas / 60;
		$horas_adicionales = Util::formatMoney($horas_adicionales);
		//$horas_trabajadas = Util::formatMoney($horas_trabajadas);
		//$faltas = 3;
		$view = \View::make('report.attendance_kardex',
			compact('title',
				'date',
				'persona',
				'employee',
				'unidad',
				'gerencia',
				'minutos_atraso',
				'omisiones',
				'sanction',
				'omision_sanction',
				'faltas',
				'discount',
				'dias_haber',
				'dias_haber_falta',
				'discount_day',
				'sanction_atraso',
				'sanction_omision',
				'cantidad_atrasos',
				'horas_adicionales',
				'sanction_falta',
				'horas_trabajadas'));
		$html_content = $view->render();

		$pdf = App::make('snappy.pdf.wrapper');
		$pdf->loadHTML($html_content);
		return $pdf->inline();

	}
	public function attendance_employee_date_virtual($employee_id, $from_date, $to_date) {
		$minutos_atraso = 0;
		$omisiones = 0;
		$dias_haber = 0;
		$horas_trabajadas = 0;
		$horas_adicionales = 0;
		$cantidad_atrasos = 0;
		$cantidad_faltas = 0;
		$entry = null;
		$output = null;

		$employee = Employee::find($employee_id);
		$from_date = Carbon::parse($from_date);
		$to_date = Carbon::parse($to_date);

		$title = 'Tarjeta de Asistencia';
		$date = date('d-m-Y');

		$persona = $employee->getFullName();
		//return $persona;
		$cargo = $employee->position->name ?? '';

		$days = cal_days_in_month(CAL_GREGORIAN, $from_date->month, $from_date->year);
		$day = $from_date->day;
		$month = $from_date->month;
		$year = $from_date->year;
		$attendances = [];
		//valid
		$diference_day = $to_date->diffInDays($from_date);
		if ($diference_day < 0) {
			return 'no se pudo validar las fechas para el calculo favor de verificar';
		}

		while ($to_date->diffInDays($from_date) > 0) {
			//verificando cantidad de dias numericos
			//Log::info($from_date->toDateString());
			$from_date->addDay(1);

			foreach (Util::getAttendanceVirtual($employee, $from_date->toDateString()) as $attendance) {
				array_push($attendances, $attendance);
			}

		}
		$tipo_horario = Employee::with('type_hours')->find($employee_id);
		$horario = "";
		if (count($tipo_horario->type_hours) > 0) {
			foreach ($tipo_horario->type_hours as $hora) {
				if ($hora->name == 'GRUPO 1 PRESENCIAL DE 8m A 4pm') {
					$horario = "CONTINUO";
				} else {
					$horario = "NORMAL";
				}
			}
		} else {
			$horario = "SIN_ASIGNAR";
		}
		$view = \View::make('report.attendance_kardex_detail_virtual', compact('title', 'date', 'persona', 'cargo', 'attendances', 'horario'));
		$html_content = $view->render();
		$pdf = App::make('snappy.pdf.wrapper');
		$pdf->loadHTML($html_content);
		return $pdf->inline();

		// return compact('from_date','to_date','employee','attendances');
	}

	public function attendance_employee_virtual($employee_id, $f_date, $t_date) {

		$minutos_atraso = 0;
		$omisiones = 0;
		$dias_haber = 0;
		$horas_trabajadas = 0;
		$horas_adicionales = 0;
		$cantidad_atrasos = 0;
		$cantidad_faltas = 0;
		$atrasos = 0;

		$employee = Employee::find($employee_id);
		$from_date = Carbon::parse($f_date);
		$to_date = Carbon::parse($t_date);
		//return $from_date;
		//estableciendo metodo de calculo XD
		$diference_day = $to_date->diffInDays($from_date);
		if ($diference_day < 0) {
			return 'no se pudo validar las fechas para el calculo favor de verificar';
		}

		while ($to_date->diffInDays($from_date) > 0) {
			//verificando cantidad de dias numericos
			//Log::info($from_date->toDateString());
			$from_date->addDay(1);

			foreach (Util::getAttendanceVirtual($employee, $from_date->toDateString()) as $attendance) {
				if ($attendance->title_entry == 'Omision') {
					$omisiones += 1;
				}
				if ($attendance->delay) {
					$minutos_atraso += $attendance->delay;
				}

			}

		}

		$sanction_atraso = 0;
		$sanction = Sanction::where('from', '<=', $minutos_atraso)
			->where('to', '>=', $minutos_atraso)
			->where('type', 'leve')
			->first();
		if ($sanction) {
			$dias_haber += $sanction->days;
			$sanction_atraso += $sanction->days;
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

		$discount_day = (float) $employee->salary / 30;
		$discount = $discount_day * $dias_haber;

		$date = Carbon::now();
		$title = 'Tarjeta de Asistencia';
		$persona = $employee->getFullName();
		$gerencia = $employee->management ? $employee->management->name : '';
		$unidad = $employee->unity ? $employee->unity->name : '';
		$horas_adicionales = (float) $horas_adicionales / 60;
		$horas_trabajadas = (float) $horas_trabajadas / 60;
		$horas_adicionales = Util::formatMoney($horas_adicionales);
		$horas_trabajadas = Util::formatMoney($horas_trabajadas);
		$view = \View::make('report.attendance_kardex',
			compact('title',
				'date',
				'persona',
				'employee',
				'unidad',
				'gerencia',
				'minutos_atraso',
				'omisiones',
				'sanction',
				'omision_sanction',
				'discount',
				'dias_haber',
				'discount_day',
				'sanction_atraso',
				'sanction_omision',
				'cantidad_atrasos',
				'horas_adicionales',
				'horas_trabajadas'));
		$html_content = $view->render();
		$pdf = App::make('snappy.pdf.wrapper');
		$pdf->loadHTML($html_content);
		return $pdf->inline();

	}

	public function print_demo() {
		$title = "Reporte ";
		$storage = 'almacen isabel';
		$date = date('d-m-Y');
		$username = 'usuario prueba';
		$view = \View::make('layouts.print', compact('title', 'date', 'username', 'storage'));
		$html_content = $view->render();
		$pdf = App::make('snappy.pdf.wrapper');
		$pdf->loadHTML($html_content);
		return $pdf->inline();
	}
	//revisar esto XD
	public function ReportMonth($fecha_entrada, $user_id, $planta) {

		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		$fecha_date = new Carbon($fecha_entrada);
		$day = Carbon::parse($fecha_date)->day;
		$month = Carbon::parse($fecha_date)->month;
		$mes = "";
		$year = Carbon::parse($fecha_date)->year;
		switch ($month) {
		case 1:
			$mes = "ENERO";
			break;
		case 2:
			$mes = "FEBRERO";
			break;
		case 3:
			$mes = "MARZO";
			break;
		case 4:
			$mes = "ABRIL";
			break;
		case 5:
			$mes = "MAYO";
			break;
		case 6:
			$mes = "JUNIO";
			break;
		case 7:
			$mes = "JULIO";
			break;
		case 8:
			$mes = "AGOSTO";
			break;
		case 9:
			$mes = "SEPTIEMBRE";
			break;
		case 10:
			$mes = "OCTUBRE";
			break;
		case 11:
			$mes = "NOVIEMBRE";
			break;
		case 12:
			$mes = "DICIEMBRE";
			break;

		}
		$refrigerio = Refreshment::select(DB::raw("CONCAT(e.first_name,' ',e.second_name,' ',e.last_name,' ',e.mother_last_name) as full_name"), 'e.id', 'e.identity_card', 'e.address', 'e.account_number', 'days_work_month', 'sunday_holiday', 'low_license', 'faults', 'holidays', 'commissions', 'days_subject_to_payment', 'ross_amount', 'invoices_110', 'hold_time', 'total_net_snack', 'commission_for_deposit', 'total_snack_to_deposit', 'date', 'hour_in', 'product_for_consumption', 'telework', 'ma.name as gerencia', 'ps.name as cargo')
			->join('rrhh.employees as e', 'e.id', '=', 'employee_id')
			->leftjoin('rrhh.managements as ma', 'ma.id', '=', 'e.management_id')
			->leftjoin('rrhh.positions as ps', 'ps.id', '=', 'e.position_id')
			->whereYear('date', '=', $year)
			->whereMonth('date', '=', $month)
			->where('e.location_id', $planta)
			->orderBy('e.last_name', 'asc')
			->get();
		//return response()->json($refrigerio);
		$username = User::find($user_id);
		$title = "PLANILLA PARA PAGO DE REFRIGERIO " . $mes;
		$date = Carbon::now();
		$persona = "PROBANSDOOOOOOO NOMBRE";
		$gerencia = "PRUEBA 222";
		$storage = null; //cambiar esto no me acuerdo por que lo deje estatico XD
		$code = 'PRUEBA001';
		$count = 0;
		$total_quantity = 0;
		$location = Location::find($planta);
		$view = \View::make('report.refrigerio', compact('username', 'date', 'title', 'storage', 'refrigerio', 'persona', 'gerencia', 'code', 'count', 'total_quantity', 'location'));
		$html_content = $view->render();
		$pdf = App::make('snappy.pdf.wrapper');
		$pdf->loadHTML($html_content)->setPaper('a4')->setOrientation('landscape')->setOption('margin-bottom', 0);
		return $pdf->inline();
	}

	public function ReportYear($fecha_entrada, $user_id, $planta) {
		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		$fecha_date = new Carbon($fecha_entrada);
		$day = Carbon::parse($fecha_date)->day;
		$month = Carbon::parse($fecha_date)->month;
		$year = Carbon::parse($fecha_date)->year;
		$refrigerio = Refreshment::select(DB::raw("CONCAT(e.first_name,' ',e.second_name,' ',e.last_name,' ',e.mother_last_name) as full_name"), 'e.id', 'e.identity_card', 'e.address', 'e.account_number', 'days_work_month', 'sunday_holiday', 'low_license', 'faults', 'holidays', 'commissions', 'days_subject_to_payment', 'ross_amount', 'invoices_110', 'hold_time', 'total_net_snack', 'commission_for_deposit', 'total_snack_to_deposit', 'date', 'hour_in', 'product_for_consumption', 'telework', 'ma.name as gerencia', 'ps.name as cargo')
			->join('rrhh.employees as e', 'e.id', '=', 'employee_id')
			->leftjoin('rrhh.managements as ma', 'ma.id', '=', 'e.management_id')
			->leftjoin('rrhh.positions as ps', 'ps.id', '=', 'e.position_id')
			->whereYear('date', '=', $year)
			->orderBy('date', 'asc')
			->get();
		$username = User::find($user_id);
		$title = "REPORTE GENERAL PLANILLA REFRIGERIOS GESTION " . $year;
		$date = Carbon::now();
		$persona = "PROBANDO DEMO";
		$gerencia = "PRUEBA 222";
		$storage = null; //cambiar esto no me acuerdo por que lo deje estatico XD
		$code = 'PRUEBA001';
		$count = 0;
		$total_quantity = 0;
		$location = Location::find($planta);
		$view = \View::make('report.refrigerioGeneral', compact('username', 'date', 'title', 'storage', 'refrigerio', 'persona', 'gerencia', 'code', 'count', 'total_quantity', 'location'));
		$html_content = $view->render();
		$pdf = App::make('snappy.pdf.wrapper');
		$pdf->loadHTML($html_content)->setPaper('a4')->setOrientation('landscape')->setOption('margin-bottom', 0);
		return $pdf->inline();
	}

	public function reportMonthExcel($fecha_entrada, $user_id, $tipo_doc, $planta) {
		$location = Location::find($planta);
		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		$fecha_date = new Carbon($fecha_entrada);
		$day = Carbon::parse($fecha_date)->day;
		$month = Carbon::parse($fecha_date)->month;
		$year = Carbon::parse($fecha_date)->year;
		$fecha_date_actual = new Carbon();
		$day_actual = Carbon::parse($fecha_date_actual)->day;
		$month_actual = Carbon::parse($fecha_date_actual)->month;
		$year_actual = Carbon::parse($fecha_date_actual)->year;
		$username = User::find($user_id);
		switch ($month) {
		case 1:
			$mes = "ENERO";
			break;
		case 2:
			$mes = "FEBRERO";
			break;
		case 3:
			$mes = "MARZO";
			break;
		case 4:
			$mes = "ABRIL";
			break;
		case 5:
			$mes = "MAYO";
			break;
		case 6:
			$mes = "JUNIO";
			break;
		case 7:
			$mes = "JULIO";
			break;
		case 8:
			$mes = "AGOSTO";
			break;
		case 9:
			$mes = "SEPTIEMBRE";
			break;
		case 10:
			$mes = "OCTUBRE";
			break;
		case 11:
			$mes = "NOVIEMBRE";
			break;
		case 12:
			$mes = "DICIEMBRE";
			break;

		}
		$refrigerio = Refreshment::select(DB::raw("CONCAT(e.first_name,' ',e.second_name,' ',e.last_name,' ',e.mother_last_name) as full_name"), 'e.id', 'e.identity_card', 'e.address', 'e.account_number', 'days_work_month', 'sunday_holiday', 'low_license', 'faults', 'holidays', 'commissions', 'days_subject_to_payment', 'ross_amount', 'invoices_110', 'hold_time', 'total_net_snack', 'commission_for_deposit', 'total_snack_to_deposit', 'date', 'hour_in', 'product_for_consumption', 'ma.name as gerencia', 'ps.name as cargo', 'telework')
			->join('rrhh.employees as e', 'e.id', '=', 'employee_id')
			->leftjoin('rrhh.managements as ma', 'ma.id', '=', 'e.management_id')
			->leftjoin('rrhh.positions as ps', 'ps.id', '=', 'e.position_id')
			->whereYear('date', '=', $year)
			->whereMonth('date', '=', $month)
			->get();
		$location = Location::find($planta);
		if ($tipo_doc == 'excel') {
			Excel::create('rptRefrigerio', function ($excel) use ($refrigerio, $fecha_date, $username, $mes, $location, $year) {
				$excel->sheet('rptRefrigerio', function ($sheet) use ($refrigerio, $fecha_date, $username, $mes, $location, $year) {
					$sheet->loadView('reportExcel.rptRefrigerio', array('refrigerio' => $refrigerio, "date" => $fecha_date, "usuario" => $username, "mes" => $mes, "location" => $location, "anio" => $year));
				});
			})->export('xls');
		} else if ($tipo_doc == 'csv') {
			Excel::create('rptRefrigerio', function ($excel) use ($refrigerio, $fecha_date, $username, $mes, $location, $year) {
				$excel->sheet('rptRefrigerio', function ($sheet) use ($refrigerio, $fecha_date, $username, $mes, $location, $year) {
					$sheet->loadView('reportExcel.rptRefrigeriocsv', array('refrigerio' => $refrigerio, "date" => $fecha_date, "usuario" => $username, "mes" => $mes, "location" => $location, "anio" => $year));
				});
			})->export('csv');
		} else if ($tipo_doc == 'txt') {
			$name = "refrigerioBanco.txt";
			$handle = fopen($name, "w");
			fwrite($handle, "REFRI" . $mes . $year . $location->name . "xxxxxx00060" . $day_actual . $month_actual . $year_actual . "\n");
			$totalBanco = 0;
			foreach ($refrigerio as $value) {
				$totalBanco += $value->total_snack_to_deposit;
			}
			foreach ($refrigerio as $key => $item) {
				if ($key == 0) {
					fwrite($handle, "100000285372790000" . $totalBanco . "1\n");
					fwrite($handle, $item->id . "100000" . $item->account_number . $item->total_snack_to_deposit . "1\n");
				} else {
					fwrite($handle, $item->id . "100000" . $item->account_number . $item->total_snack_to_deposit . "1\n");
				}
			}
			fclose($handle);
			readfile($name);
			exit;
		}

	}

	public function reportPersonNew(Request $request) {
		//
		$employees = Employee::with('position', 'management', 'city_identity_card', 'contribution')
			->whereMonth('entry_date', '=', $request->mes)
			->whereYear('entry_date', '=', $request->anio)
			->orderBy('last_name')
			->get();
		return response()->json($employees);
	}
	public function reportPersonOld(Request $request) {
		//
		$employees = Employee::with('position', 'management', 'city_identity_card', 'contribution')
			->whereMonth('disengagement_date', '=', $request->mes)
			->whereYear('disengagement_date', '=', $request->anio)
			->where('status_employee', '=', 'D')
			->orderBy('last_name')
			->get();
		return response()->json($employees);
	}
	public function reportPersonVacations(Request $request) {
		//
		$employees = Vacation::with('employee')
		// $employees = Employee::with('position', 'management', 'city_identity_card', 'contribution')
			->whereMonth('updated_at', '=', $request->mes)
			->whereYear('updated_at', '=', $request->anio)
			->get();
		return response()->json($employees);
	}

	public function reportEmployeeActive(Request $request) {
		if ($request->id) {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
				->where('management_id', '=', $request->id)
				->where('status_employee', '=', 'A')
				->orderBy('last_name')
				->get();
		} else {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
				->where('status_employee', '=', 'A')
				->orderBy('last_name')
				->get();
		}

		return response()->json($employees);
	}
	public function reportEmployeeInActive(Request $request) {
		if ($request->id) {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
				->where('management_id', '=', $request->id)
				->where('status_employee', '=', 'D')
				->orderBy('last_name')
				->get();
		} else {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
				->where('status_employee', '=', 'D')
				->orderBy('last_name')
				->get();
		}
		return response()->json($employees);
	}
	public function reportEmployeeFull(Request $request) {
		if ($request->id) {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
				->where('management_id', '=', $request->id)
				->orderBy('last_name')
				->get();
		} else {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
				->orderBy('last_name')
				->get();
		}
		return response()->json($employees);
	}
	public function reportSnacks(Request $request) {
		if ($request->id) {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
				->where('management_id', '=', $request->id)
				->orderBy('last_name')
				->get();
		} else {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
				->orderBy('last_name')
				->get();
		}
		return response()->json($employees);
	}
	public function reportPaymentsDiscounts(Request $request) {
		//return $request->all();
		//{"fecha_inicio":"2021-04-01","fecha_fin":"2021-04-30","namagement_id":{"id":37,"name":"GERENCIA DE GESTI\u00d3N ESTRAT\u00c9GICA","created_at":null,"updated_at":null}}

		$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
			->where('management_id', '=', $request->namagement_id['id'])
			->orderBy('last_name')
			->get();
		$from_date = Carbon::parse($request->fecha_inicio);
		$to_date = Carbon::parse($request->fecha_fin);
		$date = date('d-m-Y');
		$title = 'Tarjeta de Asistencia';
		foreach ($employees as $value) {
			$minutos_atraso = 0;
			$omisiones = 0;
			$dias_haber = 0;
			$horas_trabajadas = 0;
			$horas_adicionales = 0;
			$cantidad_atrasos = 0;
			$cantidad_faltas = 0;
			$atrasos = 0;
			//estableciendo metodo de calculo XD
			$diference_day = $to_date->diffInDays($from_date);
			if ($diference_day < 0) {
				return 'no se pudo validar las fechas para el calculo favor de verificar';
			}

			while ($to_date->diffInDays($from_date) > 0) {
				//verificando cantidad de dias numericos
				//Log::info($from_date->toDateString());
				$from_date->addDay(1);

				foreach (Util::getAttendance($value, $from_date->toDateString()) as $attendance) {
					if ($attendance->title_entry == 'Omision') {
						$omisiones += 1;
					}
					if ($attendance->delay) {
						$minutos_atraso += $attendance->delay;
					}

				}

			}

			$sanction_atraso = 0;
			$sanction = Sanction::where('from', '<=', $minutos_atraso)
				->where('to', '>=', $minutos_atraso)
				->where('type', 'leve')
				->first();
			if ($sanction) {
				$dias_haber += $sanction->days;
				$sanction_atraso += $sanction->days;
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

			$discount_day = (float) $value->salary / 30;
			$discount = $discount_day * $dias_haber;
			$horas_adicionales = (float) $horas_adicionales / 60;
			$horas_trabajadas = (float) $horas_trabajadas / 60;
			$horas_adicionales = Util::formatMoney($horas_adicionales);
			$horas_trabajadas = Util::formatMoney($horas_trabajadas);
			$value->minutos_atraso = $minutos_atraso;
			$value->omisiones = $omisiones;
			$value->dias_haber = $dias_haber;
			$value->horas_trabajadas = $horas_trabajadas;
			$value->horas_adicionales = $horas_adicionales;
			$value->cantidad_atrasos = $cantidad_atrasos;
			$value->cantidad_faltas = $cantidad_faltas;
			$value->atrasos = $atrasos;
			$value->sanction = $sanction;
			$value->omision_sanction = $omision_sanction;
			$value->atrasos = $atrasos;
			$value->discount = $discount;
		}
		return response()->json($employees);
	}
	public function reportPaymentsSalary(Request $request) {
		if ($request->id) {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
				->where('management_id', '=', $request->id)
				->orderBy('last_name')
				->get();
		} else {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
				->orderBy('last_name')
				->get();
		}
		return response()->json($employees);
	}
	public function reportPaymentsROE(Request $request) {
		if ($request->id) {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution')
				->where('management_id', '=', $request->id)
				->orderBy('last_name')
				->get();
		} else {
			$employees = Employee::with('position', 'management', 'unity', 'city_identity_card', 'contribution', 'document_type', 'country', 'health_box', 'location', 'contract_modality', 'contract_type')
				->orderBy('last_name')
				->get();
		}
		return response()->json($employees);
	}
	public function reportGral(Request $request) {
		$employees = DB::select(DB::raw("SELECT p.id, p.name as position_name, m.name as management_name
        , (SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.position_id=p.id AND ee.management_id=m.id and ee.status_employee='A') AS conteo
        FROM rrhh.positions p, rrhh.managements m where m.id>35
        ORDER BY p.id, p.name, m.name;"));
		return response()->json($employees);
	}
	public function reportGral2(Request $request) {
		$employees = DB::select(DB::raw("SELECT m.name
        , (SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%F%') as conteo_femenino
        , (SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%M%') as conteo_masculino
        , (SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%') as conteo_total
        FROM rrhh.managements m where m.id>35"));
		return response()->json($employees);
	}
	public function reportGral3(Request $request) {
		$employees = DB::select(DB::raw("SELECT 'Totales' AS position_name,
        SUM((SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%F%' and ee.status_employee='A')) as conteo_femenino
       , SUM((SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%M%' and ee.status_employee='A')) as conteo_masculino
       , SUM((SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%' and ee.status_employee='A')) as conteo_total
       FROM rrhh.managements m where m.id>35"));
		return response()->json($employees);
	}
	public function reportGral4(Request $request) {
		$employees = DB::select(DB::raw("SELECT m.name as name2, (SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id and ee.status_employee='A') AS conteo
        FROM rrhh.managements m where m.id>35
        ORDER BY m.name;"));
		return response()->json($employees);
	}
}
