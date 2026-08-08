<?php namespace App\Helpers;

/**
 * class Helper
 */

use App\Models\RRHH\AttendanceEmployee;
use App\Models\RRHH\EmployeeRequest;
use App\Models\RRHH\EventualSchedule;
use App\Models\RRHH\Holyday;
use App\Models\RRHH\Vacation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Util {
	public static function removeSpaces($text) {
		$re = '/\s+/';
		$subst = ' ';
		$result = preg_replace($re, $subst, $text);
		return $result ? trim($result) : null;
	}
	public static function formatMoney($value, $prefix = false) {
		if ($value) {
			$value = number_format($value, 2, '.', ',');
			if ($prefix) {
				return 'Bs ' . $value;
			}
			return $value;
		}
		return null;
	}
	public static function parseMoney($value) {
		$value = str_replace("Bs", "", $value);
		$value = str_replace(",", "", $value);
		return floatval(self::removeSpaces($value));
	}
	public static function parseBarDate($value) {
		if (!$value) {
			return null;
		}
		if (self::verifyBarDate($value)) {
			return Carbon::createFromFormat('d/m/Y', $value)->toDateString();
		} elseif (self::verifyDashDate($value)) {
			return $value;
		}
		return 'invalid date';
	}
	public static function verifyBarDate($value) {
		$re = $re = '/^\d{1,2}\/\d{1,2}\/\d{4}$/m';
		preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);
		return (sizeOf($matches) > 0);
	}
	public static function verifyDashDate($value) {
		$re = $re = '/([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))/m';
		preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);
		return (sizeOf($matches) > 0);
	}

	public static function parseNumber($input) {
		$val = str_replace(',', '', $input);
		return $val;
		// $next_val = str_replace('.', '', $input);
	}

	public static function timeString($hour) {
		if ($hour) {
			$time = explode('.', $hour);
			$h = $time[0];
			$m = ((float) $time[1] / 100) * 60;
			return $h . " horas y " . explode('.', $m)[0] . " minutos.";
		} else {
			return '0 horas';
		}
	}

	public static function validDay($date, $type_hour) {

		$day = (new Carbon($date))->dayOfWeek;
		$valid = true;
		switch ($day) {
		case 1:
			# lunes
			$valid = $type_hour->monday;
			break;
		case 2:
			# martes
			$valid = $type_hour->tuesday;
			break;
		case 3:
			# miercoles
			$valid = $type_hour->wednesday;
			break;
		case 4:
			# jueves
			$valid = $type_hour->thursday;
			break;
		case 5:
			# viernes
			$valid = $type_hour->friday;
			break;
		case 6:
			# sabado
			$valid = $type_hour->saturday;
			break;
		case 0:
			# domingo
			$valid = $type_hour->sunday;
			break;

		}

		return $valid;
	}

	public static function getAttendance($employee, $date) {
		$attendances = [];
		//$fecha_format = $date;
		$fecha_format = Carbon::parse($date);
		$datos = $fecha_format->format('Y-m-d');
		//var_dump($datos);
		foreach ($employee->type_hours as $type_hour) {
			$fecha_fin = Carbon::parse($type_hour->date_finish);
			$fecha_final = $fecha_fin->format('Y-m-d');
			$fecha_ini = Carbon::parse($type_hour->date_start);
			$fecha_inicial = $fecha_ini->format('Y-m-d');
			//var_dump($fecha_ini->format('Y-m-d'));
			if ($fecha_final >= $datos && $fecha_inicial <= $datos) {

				$eventual_schedule = EventualSchedule::where('employee_id', $employee->id)
					->where('estado', '<>', 'B')
					->where('date', $datos)
					->first();
				// en caso de tener una fecha asignada
				if ($eventual_schedule) {
					//Log::info($date . 'con horario ' . $eventual_schedule->type_hour->name);
					$type_hour = $eventual_schedule->type_hour;
					$attendance = json_decode(json_encode(array('date' => $date,
						'entry' => $type_hour->entry,
						'attendance_entry' => '00:00:00',
						'output' => $type_hour->output,
						'attendance_output' => '00:00:00',
						'title_entry' => '',
						'title_output' => '',
						'delay' => 0,
						'hours_worked' => "00:00:00",
						'surplus' => 0,
						'state_entry' => 'warning',
						'state_output' => 'danger',
						'horario' => $eventual_schedule->type_hour->hours,
						'type_module' => '')));
					$attendance_entry = AttendanceEmployee::where('date', $date)
					// ->whereBetween('time',[$type_hour->start_of_entry, $type_hour->end_of_entry])
						->where('time', '>=', $type_hour->start_of_entry)
						->where('time', '<=', $type_hour->end_of_entry)
						->where('employee_id', $employee->id)
						->orderBy('time', 'ASC')
						->first();

					if ($attendance_entry) {
						$attendance->attendance_entry = $attendance_entry->time;
						$tolerance = Carbon::parse($date . ' ' . $type_hour->tolerance_entry);
						$tolerance->addSecond(59);
						$entry_tolerance = Carbon::parse($date . ' ' . $type_hour->entry);
						$entry_tolerance->addHour($tolerance->hour);
						$entry_tolerance->addMinute($tolerance->minute);
						$entry_tolerance->addSecond($tolerance->second);
						$time = $entry_tolerance->toTimeString();
						// Log::info('Entry Tolerance :'.$time);
						// Log::info($attendance_entry->time.' >= '.$type_hour->start_of_entry);
						// Log::info($attendance_entry->time.' <= '.$time);
						if ($attendance_entry->time >= $type_hour->start_of_entry && $attendance_entry->time <= $time) {
							$attendance->state_entry = 'success';
							$attendance->title_entry = 'Normal';
							$attendance->type_module_entry = $attendance_entry->type_module;

							$attendance->delay = 0;
						} else {
							$attendance->state_entry = 'warning';
							$attendance->title_entry = 'Retraso';
							$attendance->type_module_entry = $attendance_entry->type_module;
							$entry = Carbon::parse($date . ' ' . $attendance_entry->time);
							$type_hour_entry = Carbon::parse($date . ' ' . $type_hour->entry);
							$attendance->delay = $type_hour_entry->diffInMinutes($entry);
							//crear registros temporales
						}
						//  $attendance_entry->entry = $type_hour->entry;
						// array_push($attendances,$attendance);
					} else {
						$attendance->title_entry = 'Sin Marcado';
						$attendance->type_module_entry = '';
						//$attendance_entry = array('date'=>$date,'time'=> '00:00:00','title'=> 'Sin Marcado','state'=>'error','entry');
						// array_push($attendances,$attendance_entry);
					}

					$attendance_output = AttendanceEmployee::where('date', $date)
					// ->whereBetween('time',[$type_hour->start_of_output, $type_hour->end_of_output])
						->where('time', '>=', $type_hour->start_of_output)
						->where('time', '<=', $type_hour->end_of_output)
						->where('employee_id', $employee->id)
						->orderBy('time', 'ASC')
						->first();
					if ($attendance_output) {
						$attendance->attendance_output = $attendance_output->time;
						if ($attendance_output->time >= $type_hour->output && $attendance_output->time <= $type_hour->end_of_output) {
							$attendance->state_output = 'success';
							$attendance->title_output = 'Normal';
							$attendance->type_module_output = $attendance_output->type_module;
						} else {
							$attendance->state_output = 'warning';
							$attendance->title_output = 'Retraso';
							$attendance->type_module_output = $attendance_output->type_module;
						}
						// array_push($attendances,$attendance_output);
					} else {
						$attendance->title_output = 'Sin Marcado';
						$attendance->type_module_output = '';
						// $attendance_entry = array('date'=>$date,'time'=> '00:00:00','title'=> 'Sin Marcado','state'=>'error');
						// array_push($attendances,$attendance_entry);
					}

					//colocar exedente y horas trabajadas //
					if ($attendance->attendance_entry != '00:00:00' && $attendance->attendance_output != '00:00:00') {
						//
						$entry = Carbon::parse($date . ' ' . $attendance->attendance_entry);
						$output = Carbon::parse($date . ' ' . $attendance->attendance_output);
						$minutes_worked = $output->diffInMinutes($entry);
						$hours_worked = Carbon::create(0, 0, 0, 0, 0, 0);
						$hours_worked->addMinutes($minutes_worked);
						$attendance->hours_worked = $hours_worked->toTimeString();

						$entry_hour = Carbon::parse($date . ' ' . $attendance->entry);
						$attendance->surplus = $output->diffInMinutes($entry_hour);
					}

					array_push($attendances, $attendance);
					break;
					//fin de horario especial XD

				} else {
					//en caso de no haber un horario especial asignado
					if (self::validDay($date, $type_hour)) {

						$holyday = Holyday::where('date', $date)->first();
						$attendance = json_decode(json_encode(array('date' => $date,
							'entry' => $type_hour->entry,
							'attendance_entry' => '00:00:00',
							'output' => $type_hour->output,
							'attendance_output' => '00:00:00',
							'title_entry' => '',
							'title_output' => '',
							'delay' => 0,
							'hours_worked' => "00:00:00",
							'surplus' => 0,
							'state_entry' => 'danger',
							'state_output' => 'danger',
							'type_module' => '',
							'id_boleta' => 0,
							'id_request' => 0,
							'horario' => $type_hour->hours,
						)));
						//dd( $attendance->date);
						if ($holyday) //si existe la fecha festiva
						{
							//se omite el marcado al ser feriado  XD
							//en este caso cuenta todo el dia
							$attendance = (object) array('date' => $date,
								'entry' => $type_hour->entry,
								'attendance_entry' => '00:00:00',
								'output' => $type_hour->output,
								'attendance_output' => '00:00:00',
								'title_entry' => $holyday->name,
								'title_output' => $holyday->name,
								'delay' => 0,
								'hours_worked' => "00:00:00",
								'surplus' => 0,
								'state_entry' => 'primary',
								'state_output' => 'primary',
								'type_module' => '',
								'id_boleta' => 0,
								'id_request' => 0,
								'horario' => $type_hour->hours,
							);
							array_push($attendances, $attendance);
							// $attendance_entry = array('date'=>$date,'time'=> '00:00:00','title'=>$holyday->name,'state'=>'primary');
						} else {
							//seteando tipo de hora entrada
							// $attendance->entry =
							// en caso de no encontrar fecha festiva
							$attendance_entry = AttendanceEmployee::where('date', $date)
							// ->whereBetween('time',[$type_hour->start_of_entry, $type_hour->end_of_entry])
								->where('time', '>=', $type_hour->start_of_entry)
								->where('time', '<=', $type_hour->end_of_entry)
								->where('employee_id', $employee->id)
								->orderBy('time', 'ASC')
								->first();
							// Log::info(json_encode($attendance_entry));
							if ($attendance_entry) {

								if ($type_hour->hours == 'CONTINUO') {
									$employee_request_demo = EmployeeRequest::where('employee_id', $employee->id)->where('date', $date)
										->where('state', 'Aprobado')
										->first();
									if ($employee_request_demo) {
										$employee_request = $employee_request_demo;
									} else {
										$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
											->whereIn('request_type_id', [9, 10, 3, 6])
											->where('state', 'Aprobado')
											->first();
									}
									if ($employee_request) {
										switch ($employee_request->request_type_id) {
										case 1:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
											//->where('hour_in', '>=', "07:00:00")
											//->where('hour_out', '<=', "12:00:00")
												->where('state', 'Aprobado')
												->first();
											break;

										case 2:
											$registro_hora = explode(':', $type_hour->output);
											$type_hour->output = $registro_hora[0] . ':' . $registro_hora[1] . ':59';
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', $type_hour->entry)
												->where('hour_out', '<=', $type_hour->output)
												->where('state', 'Aprobado')
												->first();
											$attendance_entry = AttendanceEmployee::where('date', $date)
											// ->whereBetween('time',[$type_hour->start_of_entry, $type_hour->end_of_entry])
												->where('time', '>=', $type_hour->start_of_entry)
												->where('time', '<=', $type_hour->end_of_entry)
												->where('employee_id', $employee->id)
												->orderBy('time', 'ASC')
												->first();
											if ($attendance_entry) {
												$attendance->attendance_entry = $attendance_entry->time;
												$tolerance = Carbon::parse($date . ' ' . $type_hour->tolerance_entry);
												$tolerance->addSecond(59);
												$entry_tolerance = Carbon::parse($date . ' ' . $type_hour->entry);
												$entry_tolerance->addHour($tolerance->hour);
												$entry_tolerance->addMinute($tolerance->minute);
												$entry_tolerance->addSecond($tolerance->second);
												$time = $entry_tolerance->toTimeString();
												// Log::info('Entry Tolerance :'.$time);
												// Log::info($attendance_entry->time.' >= '.$type_hour->start_of_entry);
												Log::info($attendance_entry->time . 'datos entrada' . $date);
												if ($attendance_entry->time >= $type_hour->start_of_entry && $attendance_entry->time <= $time) {
												} else {
													Log::info("la boleta ingreso por aca");
													if ($employee_request->hour_in <= $attendance_entry->time && $employee_request->hour_out >= $attendance_entry->time) {

													} else {

													}

												}

											} else {

											}
											break;
										case 3:

											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
												->where('state', 'Aprobado')
												->first();
											break;
										case 4:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', "07:00:00")
											//->where('hour_out', '<=', "12:00:00")
												->where('state', 'Aprobado')
												->first();
											break;
										case 5:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', "07:00:00")
												->where('hour_out', '<=', "12:00:00")
												->where('state', 'Aprobado')
												->first();
											break;
										case 6:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
												->where('state', 'Aprobado')
												->first();
											break;
										case 7:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', $type_hour->entry)
												->where('hour_out', '<=', $type_hour->output)
												->where('state', 'Aprobado')
												->first();
											break;
										case 8:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
											//->where('hour_in', '>=', $type_hour->entry)
											//->where('hour_out', '<=', $type_hour->output)
												->where('state', 'Aprobado')
												->first();
											break;
										case 9:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
											//->where('date', '<=', $date)
											//->where('todate', '>=', $date)
												->where('state', 'Aprobado')
											//->where('request_type_id', 9)
												->first();
											break;
										case 10:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
											//->whereDate('todate', '>=', $date)
											//->where('request_type_id', 10)
												->where('state', 'Aprobado')
												->first();
											break;

										}

									} else {

									}
								} else {
									$employee_request = EmployeeRequest::where('employee_id', $employee->id)
										->where('date', $date)
										->where('hour_in', '>=', $type_hour->entry)
										->where('hour_out', '<=', $type_hour->output)
										->where('state', 'Aprobado')
										->first();
								}
								if ($employee_request) {
									$attendance->attendance_entry = $employee_request->hour_in;
									$attendance->title_entry = $employee_request->request_type->code;
									$attendance->id_boleta = $employee_request->id;
									$attendance->id_request = $employee_request->request_type_id;
									switch ($employee_request->request_type_id) {
									case 1:
										$attendance->state_entry = 'comision';
										break;
									case 2:
										$attendance->state_entry = 'grey-lightest';
										break;
									case 3:
										$attendance->state_entry = 'primary';
										break;
									case 4:
										$attendance->state_entry = 'primary';
										break;
									case 5:
										$attendance->state_entry = 'primary';
										break;
									case 6:
										$attendance->state_entry = 'sin_gose';
										break;
									case 7:

										break;
									case 8:
										$attendance->state_entry = 'licencia';
										break;
									case 9:
										$attendance->state_entry = 'fucov';
										break;
									case 10:
										$attendance->state_entry = 'baja_medica';
										break;
									}
									$attendance->type_module_entry = $attendance_entry->type_module ?? 'BIOMETRICO';

								} else {
									$attendance->attendance_entry = $attendance_entry->time;
									$tolerance = Carbon::parse($date . ' ' . $type_hour->tolerance_entry);
									$tolerance->addSecond(0);
									$entry_tolerance = Carbon::parse($date . ' ' . $type_hour->entry);
									$entry_tolerance->addHour($tolerance->hour);
									$entry_tolerance->addMinute($tolerance->minute);
									$entry_tolerance->addSecond($tolerance->second);
									$time = $entry_tolerance->toTimeString();
									Log::info('Entry Tolerance :' . $time);
									// Log::info($attendance_entry->time.' >= '.$type_hour->start_of_entry);
									// Log::info($attendance_entry->time.' <= '.$time);
									if ($attendance_entry->time >= $type_hour->start_of_entry && $attendance_entry->time <= $time) {
										$attendance->state_entry = 'success';
										$attendance->title_entry = 'Normal';
										$attendance->type_module_entry = $attendance_entry->type_module;
										$attendance->delay = 0;
									} else {
										$attendance->state_entry = 'warning';
										$attendance->title_entry = 'Retraso';
										$attendance->type_module_entry = $attendance_entry->type_module;
										$entry = Carbon::parse($date . ' ' . $attendance_entry->time);
										$type_hour_entry = Carbon::parse($date . ' ' . $type_hour->entry);
										$entry->addSecond(59);
										$attendance->delay = $type_hour_entry->diffInMinutes($entry);
										//crear registros temporales
									}
								}

								//  $attendance_entry->entry = $type_hour->entry;
								// array_push($attendances,$attendance);
							} else {
								//entrada sin marcado buscar en boletas
								if ($type_hour->hours == 'CONTINUO') {
									$employee_request_demo = EmployeeRequest::where('employee_id', $employee->id)->where('date', $date)
										->where('state', 'Aprobado')
										->first();
									if ($employee_request_demo) {
										$employee_request = $employee_request_demo;
									} else {
										$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
											->whereIn('request_type_id', [9, 10, 3, 6])
											->where('state', 'Aprobado')
											->first();
									}
									if ($employee_request) {
										switch ($employee_request->request_type_id) {
										case 1:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
											//->where('hour_in', '>=', "07:00:00")
											//->where('hour_out', '<=', "12:00:00")
												->where('state', 'Aprobado')
												->first();
											break;

										case 2:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', "07:00:59")
												->where('hour_out', '<=', "16:30:59")
												->where('state', 'Aprobado')
												->first();
											$attendance_entry = AttendanceEmployee::where('date', $date)
											// ->whereBetween('time',[$type_hour->start_of_entry, $type_hour->end_of_entry])
												->where('time', '>=', $employee_request->hour_in)
											//->where('time', '<=', $employee_request->hour_out)
												->where('employee_id', $employee->id)
												->orderBy('time', 'ASC')
												->first();
											Log::info('probando entrada de diciembre' . $attendance_entry);
											if ($attendance_entry) {
												if ($employee_request->hour_in <= $attendance_entry->time && $employee_request->hour_out >= $attendance_entry->time) {

												} else {
													$entry = Carbon::parse($date . ' ' . $attendance_entry->time);
													$output = Carbon::parse($date . ' ' . $employee_request->hour_out);
													$data = (object) array(
														"date" => $date,
														"entry" => $employee_request->hour_in,
														"attendance_entry" => $attendance_entry->time,
														"attendance_output" => '',
														"output" => $employee_request->hour_out,
														"title_entry" => 'Retraso',
														"title_output" => '',
														"delay" => $output->diffInMinutes($entry),
														"surplus" => 0,
														"state_entry" => "warning",
														"type_module" => "",
														"hours_worked" => "00:00:00",
														"id_boleta" => 0,
														"horario" => "CONTINUO",
														"type_module_entry" => "BIOMETRICO",
													);
													array_push($attendances, $data);
												}
											} else {

											}
											break;
										case 3:
											/*$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', $type_hour->entry)
												->where('hour_out', '<=', $type_hour->output)
												->where('state', 'Aprobado')
												->first();*/
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
												->where('state', 'Aprobado')
												->first();
											break;
										case 4:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', "07:00:00")
											//->where('hour_out', '<=', "12:00:00")
												->where('state', 'Aprobado')
												->first();
											break;
										case 5:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', "07:00:00")
												->where('hour_out', '<=', "12:00:00")
												->where('state', 'Aprobado')
												->first();
											break;
										case 6:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
											//->where('date', $date)
											//	->where('hour_in', '>=', $type_hour->entry)
											//->where('hour_out', '<=', $type_hour->output)
												->where('state', 'Aprobado')
												->first();
											break;
										case 7:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', $type_hour->entry)
												->where('hour_out', '<=', $type_hour->output)
												->where('state', 'Aprobado')
												->first();
											break;
										case 8:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
											//->where('hour_in', '>=', $type_hour->entry)
											//->where('hour_out', '<=', $type_hour->output)
												->where('state', 'Aprobado')
												->first();
											break;
										case 9:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
												->where('state', 'Aprobado')
												->first();
											break;
										case 10:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
											//->where('todate', '>=', $date)
												->where('state', 'Aprobado')
												->first();
											break;
										}
									} else {

									}
								} else {
									$employee_request = EmployeeRequest::where('employee_id', $employee->id)
										->where('date', $date)
										->where('hour_in', '>=', $type_hour->entry)
										->where('hour_out', '<=', $type_hour->output)
										->where('state', 'Aprobado')
										->first();
								}

								if ($employee_request) {
									$attendance->attendance_entry = $employee_request->hour_in;
									$attendance->title_entry = $employee_request->request_type->code;
									$attendance->id_boleta = $employee_request->id;
									$attendance->id_request = $employee_request->request_type_id;
									switch ($employee_request->request_type_id) {
									case 1:
										$attendance->state_entry = 'comision';
										break;
									case 2:
										$attendance->state_entry = 'grey-lightest';
										break;
									case 3:
										$attendance->state_entry = 'grey-darker';
										break;
									case 4:
										$attendance->state_entry = 'primary';
										break;
									case 5:
										$attendance->state_entry = 'primary';
										break;
									case 6:
										$attendance->state_entry = 'sin_gose';
										break;
									case 7:

										break;
									case 8:
										$attendance->state_entry = 'licencia';
										break;
									case 9:
										$attendance->state_entry = 'fucov';
										break;
									case 10:
										$attendance->state_entry = 'baja_medica';
										break;
									}
									$attendance->type_module_entry = $attendance_entry->type_module ?? 'BIOMETRICO';

								} else {
									$attendance->title_entry = 'Sin Marcado';
									$attendance->type_module_entry = '';
								}
								//$attendance_entry = array('date'=>$date,'time'=> '00:00:00','title'=> 'Sin Marcado','state'=>'error','entry');
								// array_push($attendances,$attendance_entry);
							}

							$employee_request_render = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
								->whereIn('request_type_id', [9, 10, 3, 6])
								->where('state', 'Aprobado')
							//->orderBy('todate', 'asc')
								->first();
							if ($employee_request_render) {
								$attendance_output = null;
							} else {
								$attendance_output = AttendanceEmployee::where('date', $date)
								// ->whereBetween('time',[$type_hour->start_of_output, $type_hour->end_of_output])
									->where('time', '>=', $type_hour->start_of_output)
									->where('time', '<=', $type_hour->end_of_output)
									->where('employee_id', $employee->id)
									->orderBy('time', 'ASC')
									->first();
							}

							if ($attendance_output) {
								$attendance->attendance_output = $attendance_output->time;
								if ($attendance_output->time >= $type_hour->output && $attendance_output->time <= $type_hour->end_of_output) {
									$attendance->state_output = 'success';
									$attendance->title_output = 'Normal';
									$attendance->type_module_output = $attendance_output->type_module;
								} else {
									$attendance->state_output = 'warning';
									$attendance->title_output = 'Retraso';
									$attendance->type_module_output = $attendance_output->type_module;
								}
								// array_push($attendances,$attendance_output);
							} else {
								if ($type_hour->hours == 'CONTINUO') {
									//Log::info($date . 'con horario probando salida demo rene toshiro xxx salida');
									$employee_request_demo = EmployeeRequest::where('employee_id', $employee->id)->where('date', $date)
										->where('state', 'Aprobado')
										->orderby('id', 'desc')->first();
									if ($employee_request_demo) {
										$employee_request = $employee_request_demo;
									} else {
										$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
											->whereIn('request_type_id', [9, 10, 3, 6])
											->where('state', 'Aprobado')
										//->orderBy('todate', 'asc')
											->first();
									}
									if ($employee_request) {
										switch ($employee_request->request_type_id) {
										case 1:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
											//->where('hour_in', '>=', "07:00:00")
											//->where('hour_out', '<=', "12:00:00")
												->where('state', 'Aprobado')
												->first();
											break;
										case 2:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
											//->where('hour_in', '>=', "12:00:00")
												->where('hour_out', '<=', "16:35:00")
												->where('state', 'Aprobado')
												->orderby('id', 'desc')->first();
											$attendance_output = AttendanceEmployee::where('date', $date)
											// ->whereBetween('time',[$type_hour->start_of_output, $type_hour->end_of_output])
												->where('time', '>=', $type_hour->start_of_output)
												->where('time', '<=', $type_hour->end_of_output)
												->where('employee_id', $employee->id)
												->orderBy('time', 'ASC')
												->first();
											if ($attendance_output) {
												$attendance->attendance_output = $attendance_output->time;
												if ($attendance_output->time >= $type_hour->output && $attendance_output->time <= $type_hour->end_of_output) {
													$data = (object) array(
														"date" => $date,
														"entry" => "08:00:00",
														"attendance_output" => $attendance_output->time,
														"output" => "16:00:00",
														"title_entry" => 'Boleta',
														"title_output" => 'Normal',
														"delay" => 0,
														"surplus" => 0,
														"state_entry" => "blue",
														"state_output" => "success",
														"type_module" => "",
														"hours_worked" => "00:00:00",
														"id_boleta" => 0,
														"horario" => "CONTINUO",
														"type_module_entry" => "BIOMETRICO",
														"type_module_output" => "BIOMETRICO",
													);
													array_push($attendances, $data);
												} else {
													array_push($attendances, [
														"date" => $date,
														"entry" => "08:00:00",
														"output" => "16:00:00",
														"title_entry" => "",
														"attendance_output" => $attendance_output->time,
														"state_entry" => "dark",
														"title_output" => "Retraso",
														"delay" => 0,
														"state_output" => "warning",
														"type_module" => "",
														"id_boleta" => 0,
														"id_request" => 2,
														"horario" => "CONTINUO",
														"type_module_entry" => "BIOMETRICO",
														"type_module_output" => $attendance_output->type_module,
													]);
												}
											} else {

											}
											break;
										case 3:
											/*$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', $type_hour->entry)
												->where('hour_out', '<=', $type_hour->output)
												->where('state', 'Aprobado')
												->first();*/
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
												->where('state', 'Aprobado')
												->first();
											break;
										case 4:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
											//->where('hour_in', '>=', "12:00:00")
												->where('hour_out', '<=', "16:30:00")
												->where('state', 'Aprobado')
												->first();
											break;
										case 5:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', "12:00:00")
												->where('hour_out', '<=', "16:30:00")
												->where('state', 'Aprobado')
												->first();
											break;
										case 6:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
											//->where('date', $date)
											//->where('hour_in', '>=', $type_hour->entry)
											//->where('hour_out', '<=', $type_hour->output)
												->where('state', 'Aprobado')
												->first();
											break;
										case 7:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
												->where('hour_in', '>=', $type_hour->entry)
												->where('hour_out', '<=', $type_hour->output)
												->where('state', 'Aprobado')
												->first();
											break;
										case 8:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)
												->where('date', $date)
											//->where('hour_in', '>=', $type_hour->entry)
											//->where('hour_out', '<=', $type_hour->output)
												->where('state', 'Aprobado')
												->first();
											break;
										case 9:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
												->where('state', 'Aprobado')
												->first();
											break;
										case 10:
											$employee_request = EmployeeRequest::where('employee_id', $employee->id)->where('date', '<=', $date)->where('todate', '>=', $date)
												->where('state', 'Aprobado')
												->first();
											break;
										}
									} else {

									}
								} else {
									$employee_request = EmployeeRequest::where('employee_id', $employee->id)
										->where('date', $date)
										->where('hour_in', '>=', $type_hour->entry)
										->where('hour_out', '<=', $type_hour->output)
										->where('state', 'Aprobado')
										->first();
								}

								if ($employee_request) {
									$attendance->attendance_output = $employee_request->hour_out;
									$attendance->title_output = $employee_request->request_type->code;
									$attendance->id_boleta = $employee_request->id;
									$attendance->id_request = $employee_request->request_type_id;
									switch ($employee_request->request_type_id) {
									case 1:
										$attendance->state_output = 'comision';
										break;
									case 2:
										$attendance->state_output = 'comision';
										break;
									case 3:
										$attendance->state_output = 'grey-darker';
										break;
									case 4:
										$attendance->state_output = 'primary';
										break;
									case 5:
										$attendance->state_output = 'primary';
										break;
									case 6:
										$attendance->state_output = 'sin_gose';
										break;
									case 7:

										break;
									case 8:
										$attendance->state_output = 'licencia';
										break;
									case 9:
										$attendance->state_output = 'fucov';
										break;
									case 10:
										$attendance->state_output = 'baja_medica';
										break;
									}
									$attendance->type_module_output = $attendance_output->type_module ?? 'BIOMETRICO';

								} else {
									//MENSAJE SIN MARCADO
									$attendance->title_output = 'Sin Marcado';
									$attendance->type_module_output = '';
								}
								// $attendance_entry = array('date'=>$date,'time'=> '00:00:00','title'=> 'Sin Marcado','state'=>'error');
								// array_push($attendances,$attendance_entry);
							}

							//colocar exedente y horas trabajadas //
							if ($attendance->attendance_entry != '00:00:00' && $attendance->attendance_output != '00:00:00') {
								//
								$entry = Carbon::parse($date . ' ' . $attendance->attendance_entry);
								$output = Carbon::parse($date . ' ' . $attendance->attendance_output);
								$minutes_worked = $output->diffInMinutes($entry);
								$hours_worked = Carbon::create(0, 0, 0, 0, 0, 0);
								$hours_worked->addMinutes($minutes_worked);
								$attendance->hours_worked = $hours_worked->toTimeString();
								$entry_hour = Carbon::parse($date . ' ' . $attendance->entry);
								if ($type_hour->id == 6) {
									if ($attendance->attendance_entry >= "08:00:00") {
										$entry = Carbon::parse($date . '08:00:00');
									} else {
										$entry = Carbon::parse($date . ' ' . $attendance->attendance_entry);
									}
									//$entry = Carbon::parse($date . ' ' . $attendance->attendance_entry);
									$output = Carbon::parse($date . ' ' . $attendance->attendance_output);
									$hora_inicio_hora = Carbon::parse($date . ' 08:00:59');
									$data_inicio = $entry->diffInMinutes($hora_inicio_hora);
									$data_fin_hora = Carbon::parse($date . '16:00:00');
									$data_fin = $output->diffInMinutes($data_fin_hora);
									$attendance->surplus = $data_inicio + $data_fin;
								} else {
									$attendance->surplus = $output->diffInMinutes($entry_hour);
								}
							}
							//omision
							if ($attendance->id_request == 1 || $attendance->id_request == 9 || $attendance->id_request == 10) {
								# code...
							} else {
								if ($attendance->attendance_entry != '00:00:00' || $attendance->attendance_output != '00:00:00') {
									if ($attendance->attendance_entry == '00:00:00') {
										$attendance->title_entry = 'Omision';
										$attendance->type_module_entry = '';
									}
									if ($attendance->attendance_output == '00:00:00') {
										$attendance->title_output = 'Omision';
										$attendance->type_module_output = '';
									}
								}
							}

							array_push($attendances, $attendance);
						}
					}
				}
			} else {

			}
		}
		return $attendances;
	}

	public static function array_merge_array($array_income, $array_to_merge) {
		foreach ($array_to_merge as $item) {
			array_push($array_income, $array_to_merge);
		}
	}

	public static function getDayString($date) {
		$day = Carbon::parse($date)->dayOfWeek;
		$string_day = '';

		switch ($day) {
		case '1':
			# code...
			$string_day = 'Lunes';
			break;
		case '2':
			# code...
			$string_day = 'Martes';
			break;
		case '3':
			# code...
			$string_day = 'Miercoles';
			break;
		case '4':
			# code...
			$string_day = 'Jueves';
			break;
		case '5':
			# code...
			$string_day = 'Viernes';
			break;
		case '6':
			# code...
			$string_day = 'Sabado';
			break;
		case '0':
			# code...
			$string_day = 'Domingo';
			break;
		}
		// Log::info('dia: '.$day.' '.$string_day);
		return $string_day;
	}

	public static function checkVacations($employee) {
		$entry_date = Carbon::parse($employee->entry_date);
		$today = Carbon::now();
		$date_entry = $entry_date->year;
		$date_today = $today->year;
		$diff = $date_entry - $date_today;

		if ($diff > 0) {
			while ($date_entry <= $date_today) {
				$vacation = Vacation::where('employee_id', $employee->id)->where('year', $date_entry)->first();
				if (!$vacation) {
					$vacation = new Vacation;
					$vacation->employee_id = $employee->id;
					$vacation->year = $date_entry;
					$vacation->days = 15; //  segun reglamento XD
					$vacation->save();
				}
				$date_entry++;
			}
		} else {
			$vacation = new Vacation;
			$vacation->employee_id = $employee->id;
			$vacation->year = $date_entry;
			$vacation->days = 15; //  segun reglamento XD
			$vacation->save();
		}
	}
}
