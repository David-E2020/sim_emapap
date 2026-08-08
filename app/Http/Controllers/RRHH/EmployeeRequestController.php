<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\Approve;
use App\Models\RRHH\Employee;
use App\Models\RRHH\EmployeeRequest;
use App\Models\RRHH\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeRequestController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		ini_set('memory_limit', '-1');
		$employee = Auth::user()->employee;
		$user = User::find(Auth::user()->usr_id);
		if ($user->hasRole('Administrador') || $user->hasRole('Tecnico Recursos Humanos') || $user->hasRole('Recursos Humanos')) {
			$employee_requests = EmployeeRequest::with([
				'request_type' => function ($query) {
					$query->select('id', 'name', 'description');
				},
				'approves' => function ($query) {
					$query->select('id', 'employee_request_id', 'position_id', 'state', 'date');
				},
				'employee' => function ($query) {
					$query->select('id', 'first_name', 'second_name', 'last_name', 'mother_last_name');
				},
			])
				->where('employee_approve_id', 61)
				->whereIn('state', ['Aprobado', 'Pendiente'])
			//->where('employee_id', '!=', $employee->id)
				->where('planta_id', Auth::user()->getSellingPoint()->id)
				->whereYear('rrhh.employee_requests.created_at', Carbon::now()->format('Y'))
				->where('is_archived', '=', false)
				->orderBy('id', 'Desc')
				->get();
		} else {
			$employee_requests = EmployeeRequest::with([
				'request_type' => function ($query) {
					$query->select('id', 'name', 'description', 'version', 'code', 'label1', 'label2', 'label3', 'label4', 'label5');
				},
				'approves' => function ($query) {
					$query->select('id', 'employee_request_id', 'position_id', 'state', 'date');
				},
				'employee' => function ($query) {
					$query->select('id', 'first_name', 'second_name', 'last_name', 'mother_last_name');
				},
			])
				->where('employee_approve_id', $employee->id)
				->where('planta_id', Auth::user()->getSellingPoint()->id)
				->whereYear('created_at', Carbon::now()->format('Y'))
				->where('employee_id', '!=', $employee->id)
				->where('is_archived', '=', false)
				->orderBy('id', 'Desc')
				->get();
		}

		return response()->json(compact('employee', 'employee_requests'));
	}

	public function employee_request_fucov($employee_id) {
		$employee = Auth::user()->employee;
		$employee_requests = EmployeeRequest::with([
			'request_type' => function ($query) {
				$query->select('id', 'name', 'description');
			},
			'approves' => function ($query) {
				$query->select('id', 'employee_request_id', 'position_id', 'state', 'date');
			},
			'employee' => function ($query) {
				$query->select('id', 'first_name', 'second_name', 'last_name', 'mother_last_name');
			},
		])
			->where('employee_id', $employee_id)
			->whereIn('request_type_id', [9, 10])
			->where('employee_approve_id', 61)
		//->where('employee_id', '!=', $employee->id)
			->where('is_archived', '=', false)
			->where('planta_id', Auth::user()->getSellingPoint()->id)
			->whereYear('created_at', Carbon::now()->format('Y'))
			->orderBy('id', 'Desc')
			->get();
		return response()->json(compact('employee', 'employee_requests'));

	}
	public function index_employee() {
		try {
			$employee = Auth::user()->employee;
			$employee_requests = EmployeeRequest::with('request_type', 'approves')
				->where('employee_id', $employee->id)
				->where('planta_id', Auth::user()->getSellingPoint()->id)
				->whereYear('created_at', Carbon::now()->format('Y'))
				->orderBy('id', 'Desc')
				->get();
			$mensaje = "ejecucion exitosa";
			return response()->json(compact('employee', 'employee_requests', 'mensaje'));
		} catch (\Illuminate\Database\QueryException $ex) {
			$status = 'error';
			$message = 'No se puso enviar la solicitud';
			return response()->json(compact('status', 'message', 'ex'));
		}
	}

	public function index_archived() {
		$employee = Auth::user()->employee;
		$user = User::find(Auth::user()->usr_id);

		if ($user->hasRole('Administrador') || $user->hasRole('Tecnico Recursos Humanos') || $user->hasRole('Recursos Humanos')) {
			$employee_requests = EmployeeRequest::with('request_type', 'approves')
			//->where('employee_approve_id', $employee->id)
			//->where('employee_id', '!=', $employee->id)
				->where('planta_id', Auth::user()->getSellingPoint()->id)
				->whereYear('rrhh.employee_requests.created_at', Carbon::now()->format('Y'))
				->where('is_archived', '=', true)
				->orderBy('id', 'Desc')
				->get();
		} else {
			$employee_requests = EmployeeRequest::with('request_type', 'approves')
				->where('employee_approve_id', $employee->id)
				->where('employee_id', '!=', $employee->id)
				->where('planta_id', Auth::user()->getSellingPoint()->id)
				->whereYear('created_at', Carbon::now()->format('Y'))
				->where('is_archived', '=', true)
				->orderBy('id', 'Desc')
				->get();
		}
		return response()->json(compact('employee', 'employee_requests'));
	}
	public function index_rechazos() {
		$employee = Auth::user()->employee;
		$user = User::find(Auth::user()->usr_id);

		if ($user->hasRole('Administrador') || $user->hasRole('Tecnico Recursos Humanos') || $user->hasRole('Recursos Humanos')) {
			$employee_requests = EmployeeRequest::with('request_type', 'approves', 'employee')
			//->where('employee_approve_id', $employee->id)
			//->where('employee_id', '!=', $employee->id)
				->where('state', '=', 'Rechazado')
			//->where('is_archived', '=', true)
				->orderBy('id', 'Desc')
				->where('planta_id', Auth::user()->getSellingPoint()->id)
				->whereYear('created_at', Carbon::now()->format('Y'))
				->get();
		} else {
			$employee_requests = EmployeeRequest::with('request_type', 'approves')
				->where('employee_approve_id', $employee->id)
				->where('employee_id', '!=', $employee->id)
				->where('state', '=', 'Rechazado')
				->where('planta_id', Auth::user()->getSellingPoint()->id)
				->whereYear('created_at', Carbon::now()->format('Y'))
				->orderBy('id', 'Desc')
				->get();
		}
		return response()->json(compact('employee', 'employee_requests'));
	}
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	public function upload_image(Request $request) {
		$employee_request = EmployeeRequest::find($request->id);

		if ($request->hasFile('image_file')) {
			//
			$employee_request->image_path = $request->file('image_file')->store('public/request_images');
		}

		$employee_request->save();
		return response()->json(compact('employee_request'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$employee = Employee::with('type_hours_employee')->find(Auth::user()->employee->id);
		//return $employee;
		$fecha_format = Carbon::now();
		$datos = $fecha_format->format('Y-m-d');
		if ($request->request_type['id'] == 2) {
			if ($request->has('id')) {
				$hora_update_inicio = explode(':', $request->hour_in);
				//return count($hora_update_inicio);
				if (count($hora_update_inicio) == 3) {

				} else {
					$segundo = '59';
					$hora_inicio = $request->hour_in . ':' . $segundo;
					$hora_fin = $request->hour_out . ':' . $segundo;
					$request->hour_in = $hora_inicio;
					$request->hour_out = $hora_fin;
				}
			} else {
				foreach ($employee->type_hours_employee as $type_hour) {
					$segundo = '';
					$hora_inicio = $request->hour_in;
					$hora_fin = $request->hour_out;
					$fecha_fin = Carbon::parse($type_hour->date_finish);
					$fecha_final = $fecha_fin->format('Y-m-d');
					$fecha_ini = Carbon::parse($type_hour->date_start);
					$fecha_inicial = $fecha_ini->format('Y-m-d');
					//var_dump($fecha_ini->format('Y-m-d'));
					if ($fecha_final >= $datos && $fecha_inicial <= $datos) {
						//return $request->hour_in;
						if ($type_hour->type_date->entry <= $hora_fin) {

						} else {
							return response()->json(["success" => "false", "mensaje" => "DEBE VERIFICAR SU HORARIO ASIGNADO. CONTACTESE CON EL ADMINISTRADOR DE RECURSOS HUMANOS", "hora" => $type_hour->type_date->entry]);
						}
						$hora_salida_inicial = explode(':', $type_hour->type_date->output);
						$hora_registro = $hora_salida_inicial[0] . ':' . $hora_salida_inicial[1];
						$hora_salida_registro = explode(':', $hora_fin);
						$hora_registro_boleta = $hora_salida_registro[0] . ':' . $hora_salida_registro[1];
						if ($hora_registro >= $request->hora_registro_boleta) {

						} else {
							return response()->json(["success" => "false", "mensaje" => "DEBE VERIFICAR SU HORARIO ASIGNADO. CONTACTESE CON EL ADMINISTRADOR DE RECURSOS HUMANOS", "hora" => $type_hour->type_date->output]);
						}

					}
				}
				$segundo = '59';
				$hora_inicio = $request->hour_in . ':' . $segundo;
				$hora_fin = $request->hour_out . ':' . $segundo;
				$request->hour_in = $hora_inicio;
				$request->hour_out = $hora_fin;
			}
		}
		//return $request->hour_out;
		$last_employee_request = EmployeeRequest::where('employee_id', Auth::user()->employee->id)->orderBy('correlative', 'DESC')->first();
		$count = 1;
		if ($last_employee_request) {
			$count = $last_employee_request->correlative + 1;
		}

		if ($request->has('id')) {
			$employee_request = EmployeeRequest::find($request->id);
		} else {
			$employee_request = new EmployeeRequest;
		}
		$employee_request->date = $request->date;
		$employee_request->todate = $request->todate;
		$employee_request->authorized_name = $request->authorized_name;

		$employee_request->reason = $request->reason;
		$employee_request->request_type_id = $request->request_type['id'];
		$employee_request->destiny_place = $request->destiny_place;
		$employee_request->employee_id = Auth::user()->employee->id; //$request->employee_id;
		$employee_request->state = 'Pendiente';
		//adicionando correlativo
		$employee_request->correlative = $count;
		foreach ($employee->type_hours_employee as $type_hour) {
			$fecha_fin = Carbon::parse($type_hour->date_finish);
			$fecha_final = $fecha_fin->format('Y-m-d');
			$fecha_ini = Carbon::parse($type_hour->date_start);
			$fecha_inicial = $fecha_ini->format('Y-m-d');
			if ($fecha_final >= $datos && $fecha_inicial <= $datos) {
				$hora_inicio = $type_hour->type_date->entry;
				$hora_fin = $type_hour->type_date->output;
			}
		}
		if ($request->request_type['id'] == 8) {
			$employee_request->request_day = $request->request_day['id'];
			if ($request->request_day['id'] == 2) {
				$employee_request->hour_in = $hora_inicio;
				$employee_request->hour_out = $hora_fin;
			} else {
				if ($request->has("value1")) {
					$employee_request->hour_in = $hora_inicio;
					$employee_request->hour_out = "12:00:00";
				}
				if ($request->has("value2")) {
					$employee_request->hour_in = "12:00:00";
					$employee_request->hour_out = $hora_fin;
				}
			}
		} else {
			$employee_request->hour_in = $request->hour_in ?? '00:00:00';
			$employee_request->hour_out = $request->hour_out ?? '00:00:00';
		}
		if ($request->request_type['id'] == 10 || $request->request_type['id'] == 9) {
			$employee_request->employee_id = $request->employee['value'];
			$employee_request->value1 = $request->interval;
			$employee_request->hour_in = "00:00:00";
			$employee_request->hour_out = "00:00:00";
			$employee_request->employee_approve_id = 61; // quien tiene la boleta
		} else {
			if ($request->has("value1")) {
				$employee_request->value1 = $request->value1;
			}
			$employee_request->employee_approve_id = $request->employee_id; // quien tiene la boleta
		}

		if ($request->has("value2")) {
			$employee_request->value2 = $request->value2;
		}
		if ($request->has("value3")) {
			$employee_request->value3 = $request->value3;
		}
		if ($request->has("value4")) {
			$employee_request->value4 = $request->value4;
		}
		if ($request->has("value5")) {
			$employee_request->value5 = $request->value5;
		}
		$employee_request->planta_id = Auth::user()->getSellingPoint()->id;
		$employee_request->save();

		//hasta aqui lo de la boleta
		$approves = Approve::where('employee_request_id', $employee_request->id)->get();
		if (!$approves->count() > 0) {
			$position = Position::find(Auth::user()->employee->position->id);
			$rrhh_position = Position::whereIn('name', ['JEFE I DE LA UNIDAD DE RECURSOS HUMANOS', 'PROFESIONAL VI - ENCARGADA DE DESARROLLO', 'PROFESIONAL VI - ENCARGADO DE SEGURIDAD Y REDES', 'TECNICO I EN DESARROLLO', 'AUXILIAR III DE RECURSOS HUMANOS'])->first();
			$approve = new Approve;
			$approve->employee_request_id = $employee_request->id;
			$approve->position_id = 1020;
			$approve->state = 'Pendiente';
			$approve->date = Carbon::now();
			$approve->save();
		}
		//

		$name = $employee_request->date;
		$request = $employee_request;
		//return response()->json(compact('name', 'request'));
		return response()->json(["success" => "true", "mensaje" => "SE REGISTRO CORRECTAMENTE", "name" => $name, "request" => $request]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//
		$employee_request = EmployeeRequest::with('approves')->find($id);
		return response()->json(compact('employee_request'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		//
		$employee_request = EmployeeRequest::with('request_type', 'employee')->find($id);
		return response()->json(compact('employee_request'));
	}

	public function send($employee_request_id) {
		try {
			$employee_request = EmployeeRequest::find($employee_request_id);
			$approve = Approve::where('employee_request_id', $employee_request->id)->where('state', 'Pendiente')->first();
			$employee_request->employee_approve_id = 61;
			$employee_request->save();
			$status = 'success';
			$message = 'Solicitud Enviada';
			return response()->json(compact('status', 'message'));
		} catch (\Illuminate\Database\QueryException $ex) {
			$status = 'error';
			$message = 'No se puso enviar la solicitud';
			return response()->json(compact('status', 'message', 'ex'));
		}

		return $employee_request;
	}

	public function archived($id) {
		$employee_request = EmployeeRequest::find($id);
		if ($employee_request) {
			$employee_request->is_archived = true;
			$employee_request->save();
		}
		return response()->json($employee_request);
	}

	public function approve(Request $request) {
		$employee = Employee::where('id', Auth::user()->employee->id)->first();
		$employee_request = EmployeeRequest::find($request->id);
		if ($employee_request) {
			$employee_request->state = $request->approve_state;
			$employee_request->save();
		}
		$approve = Approve::where('employee_request_id', $request->id)->first();
		if ($approve) {
			$approve->state = $request->approve_state;
			$approve->save();

			$status = 'success';
			$message = 'Solicitud Enviada';
			return response()->json(compact('status', 'message', 'employee_request'));
		} else {
			$status = 'error';
			$message = 'No se puso enviar la solicitud';
			return response()->json(compact('status', 'message', 'employee_request'));
		}
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

		$employee_request = EmployeeRequest::find($id);
		$approves = Approve::where('employee_request_id', $employee_request->id)->get();
		foreach ($approves as $approve) {
			$approve->delete();
		}
		$name = $employee_request->date;
		$employee_request->delete();
		return response()->json(compact('name'));
	}

	public function validation() {
		try {
			$date = Carbon::now();
			$day = explode('-', $date)[2];
			$days = cal_days_in_month(CAL_GREGORIAN, $date->month, $date->year);
			$month = $date->month;
			$year = $date->year;
			$mes = $month;
			$fecha_inicio = Carbon::now()->startofMonth()->format('Y-m-d');
			$fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
			$from_date = Carbon::parse($fecha_inicio);
			$to_date = Carbon::parse($fecha_fin);
			$employee = Auth::user()->employee;
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
					$value->consumido = $totalDuration . " Munitos";
					array_push($tiempo_push, ["tiempo" => $totalDuration]);
				}
			} else {

			}
			if ($suma_tiempo <= 120) {
				$total = 120 - $suma_tiempo;
				if ($total == 0) {
					return response()->json(["success" => "false", "mensaje" => "Ya no puede sacar boleta de este tipo se le acabo los minutos de permiso", "data" => $employee_request, "tiempo" => $tiempo_push, "fecha_inicio" => $fecha_inicio, "fecha_fin" => $fecha_fin]);

				} else {
					return response()->json(["success" => "true", "mensaje" => "Le quedan " . $total . " minutos", "data" => $employee_request, "tiempo" => $tiempo_push, "fecha_inicio" => $fecha_inicio, "fecha_fin" => $fecha_fin]);
				}
			} else {
				return response()->json(["success" => "false", "mensaje" => "Ya no puede sacar boleta de este tipo se le acabo los minutos de permiso", "data" => $employee_request, "tiempo" => $tiempo_push, "fecha_inicio" => $fecha_inicio, "fecha_fin" => $fecha_fin]);
			}
		} catch (\Illuminate\Database\QueryException $ex) {
			$status = 'error';
			$message = 'No se puso enviar la solicitud';
			return response()->json(compact('status', 'message', 'ex'));
		}
	}

	public function validacion_licencia() {
		try {
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
			$employee = Auth::user()->employee;
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
					return response()->json(["success" => "false", "mensaje" => "Ya no puede sacar boleta de este tipo se le acabo los horas de licencia", "data" => $employee_request, "tiempo" => $tiempo_push, "fecha_inicio" => $fecha_inicio, "fecha_fin" => $fecha_fin, "total" => $total]);

				} else {
					return response()->json(["success" => "true", "mensaje" => "Le quedan " . $total . " horas", "data" => $employee_request, "tiempo" => $tiempo_push, "fecha_inicio" => $fecha_inicio, "fecha_fin" => $fecha_fin, "total" => $total]);
				}
			} else {
				return response()->json(["success" => "false", "mensaje" => "Ya no puede sacar boleta de este tipo se le acabo los horas de licencia", "data" => $employee_request, "tiempo" => $tiempo_push, "fecha_inicio" => $fecha_inicio, "fecha_fin" => $fecha_fin]);
			}
		} catch (\Illuminate\Database\QueryException $ex) {
			$status = 'error';
			$message = 'No se puso enviar la solicitud';
			return response()->json(compact('status', 'message', 'ex'));
		}
	}

	public function anular_boleta(Request $request) {
		$employee_request = EmployeeRequest::find($request->id);
		if ($employee_request) {
			$employee_request->state = 'Rechazado';
			$employee_request->save();
		}
		$status = 'success';
		$message = 'Se anulo la boleta correctamente';
		return response()->json(compact('status', 'message', 'employee_request'));
	}
}
