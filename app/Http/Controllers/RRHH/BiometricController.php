<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\AttendanceEmployee;
use App\Models\RRHH\Biometric;
use App\Models\RRHH\BiometricoEmployee;
use App\Models\RRHH\Employee;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Rats\Zkteco\Lib\ZKTeco;

class BiometricController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {

		$biometrics = Biometric::where('state', 'A')->get();
		return response()->json(compact('biometrics'));
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
			$biometric = Biometric::find($request->id);
		} else {
			$biometric = new Biometric;
		}
		$biometric->name = $request->name;
		$biometric->address = $request->address;
		$biometric->ip = $request->ip;
		$biometric->port = $request->port;
		$biometric->save();

		return response()->json(compact('biometric'));
	}

	public function sync(Request $request) {
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', '-1');
		ini_set('max_input_time', '-1');
		set_time_limit('-1');
		set_time_limit(200);
		ini_set("memory_limit", "10056M");
		$biometric = Biometric::find($request->id);
		$zk = new ZKTeco($biometric->ip, $biometric->port);
		$ret = $zk->connect();

		$attendances = $zk->getAttendance();
		//return $vector;
		$sync_count = 0;
		$emple = array();
		foreach ($attendances as $attendance) {
			$employee = Employee::where('biometric_code', $attendance['id'])->where('status_employee', 'A')->first();
			array_push($emple, $employee->id ?? 0);
			//rray_push($emple, $employee->id);
			if ($employee) {
				$date = date("Y-m-d", strtotime($attendance['timestamp']));
				$time = date("H:i:s", strtotime($attendance['timestamp']));
				$attendance_employee = AttendanceEmployee::where('date', $date)->where('time', $time)->where('employee_id', $employee->id)->first();
				//return $attendance_employee;
				if (!$attendance_employee) {

					$attendance_employee = new AttendanceEmployee;
					$attendance_employee->employee_id = $employee->id;
					$attendance_employee->biometric_id = $biometric->id;
					$attendance_employee->biometric_code = $attendance['id'];
					$attendance_employee->date = $date;
					$attendance_employee->time = $time;
					$attendance_employee->type_module = "BIOMETRICO";
					$attendance_employee->delayed = "00:00:00";
					$attendance_employee->estado_inicio = 'A';
					$attendance_employee->ip_biometrico = $biometric->ip;
					$attendance_employee->save();
					$sync_count++;
				}
			}
		}
		return response()->json(compact('biometric', 'attendance', 'sync_count', 'emple'));

	}

	public function syncDiario(Request $request) {
		$date = Carbon::today()->format("Y-m-d");
		$fecha_ini = Carbon::createFromFormat('Y-m-d H:i:s', $date . " 00:00:00");
		$fecha_fin = Carbon::createFromFormat('Y-m-d H:i:s', $date . " 23:59:00");
		//return $fecha_fin->format('Y-m-d H:i:s');
		$biometric = Biometric::find($request->id);
		//return $biometric;

		$zk = new ZKTeco($biometric->ip, $biometric->port);
		// $zk = new ZKLib("172.16.1.234", 4370);
		$ret = $zk->connect();
		$zk->enableDevice();
		$attendances = $zk->getAttendance();
		$consulta = collect($attendances);
		$data_sql = $consulta->where('timestamp', '>=', $fecha_ini)->where('timestamp', '<=', $fecha_fin)->toArray();
		//$users = $zk->getUser();
		//return $data_sql;
		$vector = Array();
		foreach ($data_sql as $key => $value) {
			array_push($vector, $value);
		}
		//return $vector;
		$sync_count = 0;
		$emple = array();
		foreach ($vector as $attendance) {
			$employee = Employee::where('biometric_code', $attendance['id'])->where('status_employee', 'A')->first();
			array_push($emple, $employee->id ?? 0);
			//rray_push($emple, $employee->id);
			if ($employee) {
				$date = date("Y-m-d", strtotime($attendance['timestamp']));
				$time = date("H:i:s", strtotime($attendance['timestamp']));
				$attendance_employee = AttendanceEmployee::where('date', $date)->where('time', $time)->where('employee_id', $employee->id)->first();
				//return $attendance_employee;
				if (!$attendance_employee) {

					$attendance_employee = new AttendanceEmployee;
					$attendance_employee->employee_id = $employee->id;
					$attendance_employee->biometric_id = $biometric->id;
					$attendance_employee->biometric_code = $attendance['id'];
					$attendance_employee->date = $date;
					$attendance_employee->time = $time;
					$attendance_employee->type_module = "BIOMETRICO";
					$attendance_employee->delayed = "00:00:00";
					$attendance_employee->estado_inicio = 'A';
					$attendance_employee->ip_biometrico = $biometric->ip;
					$attendance_employee->save();
					$sync_count++;
				}
			}
		}
		return response()->json(compact('biometric', 'attendance', 'sync_count', 'emple'));

	}

	public function syncDiarioFecha(Request $request) {
		//return $request->all();
		$date = Carbon::today()->format("Y-m-d");
		$fecha_ini = Carbon::createFromFormat('Y-m-d H:i:s', $request->fecha_general . " 00:00:00");
		$fecha_fin = Carbon::createFromFormat('Y-m-d H:i:s', $request->fecha_general . " 23:59:00");
		//return $fecha_fin->format('Y-m-d H:i:s');
		$biometric = Biometric::find($request->id);
		$zk = new ZKTeco($biometric->ip, $biometric->port);
		// $zk = new ZKLib("172.16.1.234", 4370);
		$ret = $zk->connect();

		$attendances = $zk->getAttendance();
		$consulta = collect($attendances);
		$data_sql = $consulta->where('timestamp', '>=', $fecha_ini)->where('timestamp', '<=', $fecha_fin)->toArray();
		//$users = $zk->getUser();
		//return $data_sql;
		$vector = Array();
		foreach ($data_sql as $key => $value) {
			array_push($vector, $value);
		}
		//return $vector;
		$sync_count = 0;
		$emple = array();
		foreach ($vector as $attendance) {
			$employee = Employee::where('biometric_code', $attendance['id'])->where('status_employee', 'A')->first();
			array_push($emple, $employee->id ?? 0);
			//rray_push($emple, $employee->id);
			if ($employee) {
				$date = date("Y-m-d", strtotime($attendance['timestamp']));
				$time = date("H:i:s", strtotime($attendance['timestamp']));
				$attendance_employee = AttendanceEmployee::where('date', $date)->where('time', $time)->where('employee_id', $employee->id)->first();
				//return $attendance_employee;
				if (!$attendance_employee) {

					$attendance_employee = new AttendanceEmployee;
					$attendance_employee->employee_id = $employee->id;
					$attendance_employee->biometric_id = $biometric->id;
					$attendance_employee->biometric_code = $attendance['id'];
					$attendance_employee->date = $date;
					$attendance_employee->time = $time;
					$attendance_employee->type_module = "BIOMETRICO";
					$attendance_employee->delayed = "00:00:00";
					$attendance_employee->estado_inicio = 'A';
					$attendance_employee->ip_biometrico = $biometric->ip;
					$attendance_employee->save();
					$sync_count++;
				}
			}
		}
		return response()->json(compact('biometric', 'attendance', 'sync_count', 'emple'));
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

	public function getInfo($biometric_id) {
		try {
			$biometric = Biometric::find($biometric_id);
			$zk = new ZKLib($biometric->ip, $biometric->port);
			// $zk = new ZKLib("172.16.1.234", 4370);
			$ret = $zk->connect();
			sleep(1);
			$name = $zk->deviceName();
			$face = $zk->faceFunctionOn();
			sleep(1);
			$time = $zk->getTime();
			sleep(1);
			return response()->json(["success" => "true", "biometrico_data" => $biometric, "name" => $name, "time" => $time, 'face' => $face]);
		} catch (ErrorException $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		} catch (exception $e) {

			return response()->json(["success" => "false", "mensaje" => $ex]);
		}

		return response()->json(compact('name', 'time'));
	}
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		//
		$biometric = Biometric::find($id);

		return response()->json(compact('biometric'));
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
		$biometric = Biometric::find($id);
		$name = $biometric->name;
		$biometric->delete();
		return response()->json(compact('name'));
	}

	public function list_user_biometric($biometric_id) {
		try {
			$biometric = Biometric::find($biometric_id);
			$zk = new ZKLib($biometric->ip, $biometric->port);
			//$zk = new ZKTeco($biometric->ip, $biometric->port);
			$ret = $zk->connect();
			sleep(1);
			$data = $zk->getUser();
			sleep(1);
			$users = collect($data);
			//return $users;
			$vector = Array();
			foreach ($users as $value) {
				array_push($vector,
					["id_biometrico" => $value[0],
						"nombre_completo" => $value[1],
						"rf_id" => $value[2],
						"uid" => $value[3],
						"rol" => $value[4],
						"password" => $value[5],
					]);
			}
			//return json_encode($users);
			$ret = $zk->disconnect();
			sleep(1);
			return response()->json(["success" => "true", "data" => $vector, "biometrico_data" => $biometric]);
		} catch (ErrorException $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		} catch (exception $e) {

			return response()->json(["success" => "false", "mensaje" => $ex]);
		}

	}

	public function DesactivarBiometrico($biometric_id) {
		try {
			$biometric = Biometric::find($biometric_id);
			$zk = new ZKLib($biometric->ip, $biometric->port);
			$ret = $zk->connect();
			sleep(1);

			$data = $zk->disable();
			sleep(1);

			$ret = $zk->disconnect();
			sleep(1);

			return response()->json(["success" => "true", "data" => $data, "biometrico_data" => $biometric]);

		} catch (ErrorException $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		} catch (exception $e) {

			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}
	public function ReiniciarBiometrico($biometric_id) {
		try {
			$biometric = Biometric::find($biometric_id);
			$zk = new ZKLib($biometric->ip, $biometric->port);
			$ret = $zk->connect();
			sleep(1);

			$data = $zk->restart();
			sleep(1);

			$ret = $zk->disconnect();
			sleep(1);

			return response()->json(["success" => "true", "data" => $data, "biometrico_data" => $biometric]);
		} catch (ErrorException $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		} catch (exception $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}
	public function ActivarBiometrico($biometric_id) {
		try {
			$biometric = Biometric::find($biometric_id);
			$zk = new ZKLib($biometric->ip, $biometric->port);
			$ret = $zk->connect();
			sleep(1);

			$data = $zk->enable();
			sleep(1);

			$ret = $zk->disconnect();
			sleep(1);

			return response()->json(["success" => "true", "data" => $data, "biometrico_data" => $biometric]);

		} catch (ErrorException $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		} catch (exception $e) {

			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}
	public function DesbloquearBiometrico($biometric_id) {
		try {
			$biometric = Biometric::find($biometric_id);
			$zk = new ZKLib($biometric->ip, $biometric->port);
			$ret = $zk->connect();
			sleep(1);
			$data = $zk->unlock();
			sleep(1);
			$ret = $zk->disconnect();
			sleep(1);
			return response()->json(["success" => "true", "data" => $data, "biometrico_data" => $biometric]);
		} catch (ErrorException $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		} catch (exception $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}

	public function sincronizarUsers(Request $request) {
		try {
			$biometric = Biometric::find($request->biometrico);
			$zk = new ZKLib($biometric->ip, $biometric->port);
			$ret = $zk->connect();
			sleep(1);
			$data = $zk->getUser();
			sleep(1);
			$ret = $zk->disconnect();
			sleep(1);

			$users = collect($data);
			$vector = Array();
			foreach ($users as $value) {
				$employee = Employee::where('biometric_code', $value[0])->first();
				if (empty($employee)) {
					# code...
				} else {
					$employee_biometrico = BiometricoEmployee::where('biometric_id', $biometric->id)->where('employee_id', $employee->id)->first();
					if (empty($employee_biometrico)) {
						$attendance_employee = new BiometricoEmployee;
						$attendance_employee->employee_id = $employee->id;
						$attendance_employee->biometric_id = $biometric->id;
						$attendance_employee->user_id = Auth::user()->usr_id;
						$attendance_employee->state = 'A';
						$attendance_employee->code_biometric = $employee->biometric_code;
						$attendance_employee->rf_id = $value[2];
						$attendance_employee->rol = $value[4];
						$attendance_employee->password = $value[5];
						$attendance_employee->save();
					} else {

					}
				}

			}
			return response()->json(["success" => "true", "mensaje" => "Se asigno los puntos de distribucion a los estudiantes", "data" => $users]);

		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}

	}

	public function listUserBiometrico($biometric_id) {
		try {
			$employee = BiometricoEmployee::with('employee', 'biometric')->where('biometric_id', $biometric_id)->get();
			foreach ($employee as $key => $value) {
				$value->full_name = $value->employee->first_name . ' ' . $value->employee->second_name . ' ' . $value->employee->last_name . ' ' . $value->employee->mother_last_name;
				if ($value->state == 'A') {
					$value->state = 'ACTIVO';
				} else {
					$value->state = 'INACTIVO';
				}
			}
			return response()->json(["success" => "true", "mensaje" => "Se asigno los puntos de distribucion a los estudiantes", "data" => $employee]);

		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}

	public function registrarbiouser(Request $request, $biometric_id) {
		//return $request->all();
		try {
			$biometric = Biometric::find($biometric_id);
			$zk = new ZKLib($biometric->ip, $biometric->port);
			$ret = $zk->connect();
			sleep(1);
			$data = $zk->setUser($request->biometrico, $request->biometrico, $request->text, $request->ci, 0);
			sleep(1);
			$ret = $zk->disconnect();
			sleep(1);
			$users = collect($data);
			$vector = Array();

			$employee_biometrico = BiometricoEmployee::where('biometric_id', $biometric_id)->where('employee_id', $request->value)->first();
			if (empty($employee_biometrico)) {
				$attendance_employee = new BiometricoEmployee;
				$attendance_employee->employee_id = $request->value;
				$attendance_employee->biometric_id = $biometric_id;
				$attendance_employee->user_id = Auth::user()->usr_id;
				$attendance_employee->state = 'A';
				$attendance_employee->code_biometric = $request->biometrico;
				$attendance_employee->rf_id = "0000000000";
				$attendance_employee->rol = 0;
				$attendance_employee->password = $request->ci;
				$attendance_employee->save();
			} else {

			}
			return response()->json(["success" => "true", "mensaje" => "Se asigno los puntos de distribucion a los estudiantes", "data" => $biometric]);
		} catch (ErrorException $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		} catch (exception $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}

	public function listEmployeBio() {
		$persona = Employee::select(DB::raw("CONCAT(first_name,' ',last_name) as full_name"), 'rrhh.employees.id', 'identity_card', 'address', 'position_id', 'c.name as cargo', 'management_id', 'm.name as gerencia', 'account_number', 'biometric_code')
			->leftjoin('rrhh.positions as c', 'c.id', '=', 'position_id')
			->leftjoin('rrhh.managements as m', 'm.id', '=', 'management_id')
			->get();
		return response()->json(["empleados" => $persona]);
	}

	public function DeletUserBiometric($biometric_id, $user_id) {
		try {
			$biometric = Biometric::find($biometric_id);
			$zk = new ZKTeco($biometric->ip, $biometric->port);
			$ret = $zk->connect();
			sleep(1);
			$data = $zk->removeUser($user_id);
			sleep(1);
			$zk->enableDevice();
			sleep(1);
			$ret = $zk->disconnect();
			sleep(1);
			return response()->json(["success" => "true", "mensaje" => "Se elimino el usuario", "data" => $biometric]);
		} catch (ErrorException $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		} catch (exception $e) {
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}

	}
}
