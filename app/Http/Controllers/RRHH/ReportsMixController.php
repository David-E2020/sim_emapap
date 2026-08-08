<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use App\Models\RRHH\Employee;
use App\Models\RRHH\Management;
use App\Models\RRHH\Position;
use App\Models\RRHH\Vacation;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportsMixController extends Controller {
	public function reporteExcelDescuentos($id) {
		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		if ($id == 0) {
			$datos = Employee::orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosDescuentos', compact('datos'));
				});
			})->export('xls');
		} else {
			$datos = Employee::where('management_id', '=', $id)->orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosDescuentos', compact('datos'));
				});
			})->export('xls');
		}
	}
	public function reporteExcelSalarios($id) {
		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		if ($id == 0) {
			$datos = Employee::orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosSalarios', compact('datos'));
				});
			})->export('xls');
		} else {
			$datos = Employee::where('management_id', '=', $id)->orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosSalarios', compact('datos'));
				});
			})->export('xls');
		}
	}
	public function reporteExcelROE($id) {
		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		if ($id == 0) {
			$datos = Employee::orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosRoe', compact('datos'));
				});
			})->export('xls');
		} else {
			$datos = Employee::where('management_id', '=', $id)->orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosRoe', compact('datos'));
				});
			})->export('xls');
		}
	}
	public function reporteExcelFuncionarioActivo($id) {
		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		if ($id == 0) {
			$datos = Employee::where('status_employee', '=', 'A')->orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosFuncionarioActivo', compact('datos'));
				});
			})->export('xls');
		} else {
			$datos = Employee::where('management_id', '=', $id)->where('status_employee', '=', 'A')->orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosFuncionarioActivo', compact('datos'));
				});
			})->export('xls');
		}
	}
	public function reporteExcelFuncionarioInActivo($id) {
		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		if ($id == 0) {
			$datos = Employee::where('status_employee', '=', 'D')->orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosFuncionarioActivo', compact('datos'));
				});
			})->export('xls');
		} else {
			$datos = Employee::where('management_id', '=', $id)->where('status_employee', '=', 'D')->orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosFuncionarioActivo', compact('datos'));
				});
			})->export('xls');
		}
	}
	public function reporteExcelFuncionarioTodos($id) {
		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		if ($id == 0) {
			$datos = Employee::orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosFuncionarioActivo', compact('datos'));
				});
			})->export('xls');
		} else {
			$datos = Employee::where('management_id', '=', $id)->orderBy('last_name')->get();
			Excel::create('Planilla Empleados', function ($excel) use ($datos) {
				$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
					$sheet->loadView('reportsMix.pagosFuncionarioActivo', compact('datos'));
				});
			})->export('xls');
		}
	}
	public function reporteExcelGeneralPorCargos() {
		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');

		$employees = DB::select(DB::raw("SELECT p.id, p.name as position_name, m.name as management_name
        , (SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.position_id=p.id AND ee.management_id=m.id) AS conteo
        FROM rrhh.positions p, rrhh.managements m
        ORDER BY p.id, p.name, m.name;"));
		$conti = 0;
		$contj = 0;
		$matriz[0][0] = 0;
		foreach ($employees as $employee) {
			foreach ($employee as $employe) {
				$matriz[$conti][$contj] = $employe;
				$contj++;
			}
			$contj = 0;
			$conti++;
		}
		$cargos = Position::select('rrhh.positions.name')->orderby('id')->orderby('name')->get();
		$cargosCont = $cargos->count();
		$gerencias = Management::select('rrhh.managements.name')->orderby('name')->get();
		$gerenciasCont = $gerencias->count();

		$employeesGenerales = DB::select(DB::raw("SELECT m.name as name2, (SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id) AS conteo
        FROM rrhh.managements m
        ORDER BY m.name;"));

		$contk = 0;
		$contk2 = 0;
		$totalesGenerales[0][0] = "";
		foreach ($employeesGenerales as $employeesGenerale) {
			foreach ($employeesGenerale as $employeesGral) {
				$totalesGenerales[$contk][$contk2] = $employeesGral;
				$contk2++;
			}
			$contk2 = 0;
			$contk++;
		}

		$employeesGeneros = DB::select(DB::raw("SELECT m.name
        , (SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%F%') as conteo_femenino
        , (SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%M%') as conteo_masculino
        , (SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%') as conteo_total
        FROM rrhh.managements m ORDER BY m.name"));

		$contl = 0;
		$contl2 = 0;
		$CantGeneros[0][0] = "";
		foreach ($employeesGeneros as $employeesGenero) {
			foreach ($employeesGenero as $employeesGen) {
				$CantGeneros[$contl][$contl2] = $employeesGen;
				$contl2++;
			}
			$contl2 = 0;
			$contl++;
		}
		$employeesGenerosTotales = DB::select(DB::raw("SELECT 'Totales' AS position_name,
        SUM((SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%F%')) as conteo_femenino
       , SUM((SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%M%')) as conteo_masculino
       , SUM((SELECT COUNT(*) FROM rrhh.employees ee WHERE ee.management_id=m.id AND gender like '%')) as conteo_total
       FROM rrhh.managements m"));
		$contm = 0;
		$contm2 = 0;
		$CantGenerosTotales[0][0] = "";
		foreach ($employeesGenerosTotales as $employeesGenerosTotale) {
			foreach ($employeesGenerosTotale as $employeesGenTotales) {
				$CantGenerosTotales[$contm][$contm2] = $employeesGenTotales;
				$contm2++;
			}
			$contm2 = 0;
			$contm++;
		}

		// $totalesGenerales="";
		Excel::create('Planilla Empleados', function ($excel) use ($matriz, $conti, $cargos, $cargosCont, $gerencias, $gerenciasCont, $CantGeneros, $CantGenerosTotales, $totalesGenerales) {
			$excel->sheet('planilla empleados', function ($sheet) use ($matriz, $conti, $cargos, $cargosCont, $gerencias, $gerenciasCont, $CantGeneros, $CantGenerosTotales, $totalesGenerales) {
				$sheet->loadView('reportsMix.generalPorCargos', compact('matriz', 'conti', 'cargos', 'cargosCont', 'gerencias', 'gerenciasCont', 'CantGeneros', 'CantGenerosTotales', 'totalesGenerales'));
			});
		})->export('xls');
	}
	public function reporteExcelPersonalNuevo($mes, $anio) {
		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		$datos = Employee::whereMonth('entry_date', '=', $mes)
			->whereYear('entry_date', '=', $anio)
			->orderBy('last_name')
			->get();
		Excel::create('Planilla Empleados', function ($excel) use ($datos) {
			$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
				$sheet->loadView('reportsMix.persnoalNuevo', compact('datos'));
			});
		})->export('xls');
	}
	public function reporteExcelPersonalRetirado($mes, $anio) {
		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		$datos = Employee::whereMonth('disengagement_date', '=', $mes)
			->whereYear('disengagement_date', '=', $anio)
			->where('status_employee', '=', 'D')
			->orderBy('last_name')
			->get();
		Excel::create('Planilla Empleados', function ($excel) use ($datos) {
			$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
				$sheet->loadView('reportsMix.persnoalRetirado', compact('datos'));
			});
		})->export('xls');
	}
	public function reporteExcelPersonalConVacaciones($mes, $anio) {

		ini_set('max_execution_time', '300'); //300 seconds = 5 minutes
		ini_set('memory_limit', '2048M');
		$datos = Vacation::whereMonth('updated_at', '=', $mes)
			->whereYear('updated_at', '=', $anio)
			->get();
		Excel::create('Planilla Empleados', function ($excel) use ($datos) {
			$excel->sheet('planilla empleados', function ($sheet) use ($datos) {
				$sheet->loadView('reportsMix.persnoalConVacaciones', compact('datos'));
			});
		})->export('xls');
	}
}
