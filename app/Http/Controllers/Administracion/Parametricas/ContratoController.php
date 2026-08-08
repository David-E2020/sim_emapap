<?php

namespace App\Http\Controllers\Administracion\Parametricas;

use App;
use App\Http\Controllers\Controller;
use App\Models\Insumos\Articulos;
use App\Models\Insumos\UnidadMedida;
use App\Models\Inventario\Contrato;
use App\Models\Inventario\ContratoDetalle;
use App\Models\Inventario\Distribuidora;
use App\Models\Inventario\Parametricas;
use App\Models\Logistica\SolicitudMovimiento;
use App\Models\Parametrica;
use App\Models\Planta;
use App\Models\Sucursal;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Yajra\Datatables\Datatables;

class ContratoController extends Controller {

	public function index() {
		$unidad_medida = Contrato::with('detalles')->orderBy('id', 'desc')->get();
		return DataTables::of($unidad_medida)
			->addIndexColumn()
			->editColumn('created_at', function ($fac) {
				return $fac->created_at ? with(new Date($fac->created_at))->format('l j F Y H:m') : '';
			})
			->make(true);
	}

	public function listar_contrato() {
		return Contrato::all();
	}
	public function create() {
	}

	public function store(Request $request) {
		$input = $request->all();
		$fileName = '../sin-doc.jpg';
		if ($request->file) {
			$file_name = time() . '_' . $request->file->getClientOriginalName();
			$file_path = $request->file('file')->move(public_path('upload'), $file_name);
			$fileName = time() . '_' . $request->file->getClientOriginalName();
		}
		$detalles = $input['detalles'];
		$contrato = new Contrato();
		$contrato->nro_contrato = $input['nro_contrato'];
		$contrato->cliente = $input['cliente'];
		$contrato->fecha_salida = $input['fecha_salida'];
		$contrato->incoterm = $input['incoterm'];
		$contrato->doc_salida = $fileName;
		$contrato->forma_pago_id = $input['forma_pago_id'];
		$contrato->save();
		$dets = json_decode($detalles);
		foreach ($dets as $key => $value) {
			$contratoDetalle = new ContratoDetalle();
			$contratoDetalle->contrato_id = $contrato->id;
			$contratoDetalle->tipo_servicio_id = $value->tipo_servicio_id ?? 0;
			$contratoDetalle->linea_id = $value->linea_id ?? 0;
			$contratoDetalle->tipo_almendra_id = $value->tipo_almendra_id;
			$contratoDetalle->cantidad_almendra = $value->cantidad_almendra;
			$contratoDetalle->nro_lote = $value->nro_lote;
			$contratoDetalle->save();
		}
		return $detalles;
	}

	public function show($id) {
		//
	}

	public function edit($id) {
		//
	}

	public function update(Request $request, $id) {
		$data = $request->all();
		$contrato = Contrato::find($id);
		$contrato->update($data);
	}

	public function baja_contrato($id) {
		try {
			$contrato = Contrato::find($id);
			$contrato->estado = 'B';
			$contrato->usr_eliminado = Auth::user()->id;
			$contrato->deleted_at = now();
			$contrato->save();
			return response()->json(["success" => "true", "mensaje" => "Se dio de baja el Contrato correctamente!!!", "data" => $contrato]);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "No se pudo eliminar el contrato, intente mas tarde", "data" => $ex]);
		}
	}

	public function listar_distribuidora_logistica() {
		$distribuidora = Distribuidora::where('estado_registro', 'A')->where('tipo_entrada', 'LOGISTICA')->orderby('id', 'desc')->get();
		return Datatables::of($distribuidora)
			->addIndexColumn()
			->editColumn('created_at', function ($fac) {
				return $fac->created_at ? with(new Date($fac->created_at))->format('l j F Y H:m') : '';;
			})
			->make(true);
	}

	public function listar_unidad_medida_logistica() {
		$unidad_medida = UnidadMedida::get();
		return $unidad_medida;
	}

	public function listar_estibalaje() {
		$estibalaje = Parametricas::where('param_estado', 'A')->where('param_tabla', 'TABLA_ESTIBAJE')->where('param_valor', '<>', 0)->get();
		return $estibalaje;
	}

	public function listar_tipo_contrato() {
		try {
			$tipo_acopio = Parametrica::where('param_tabla', 'TABLA_TIPO_CONTRATO')->where('param_valor', '<>', 0)->where('param_estado', 'A')->get();

			return response()->json(["success" => "true", "mensaje" => "Listo correctamente", "data" => $tipo_acopio]);
		} catch (\Illuminate\Database\QueryException $e) {
			return response()->json(["success" => "false", "mensaje" => "Hubo un error al listar.Intente nuevamente", "data" => $e]);
		}
	}

	public function listar_tipo_contrato_servicio() {
		try {
			$tipo_acopio = Parametrica::where('param_tabla', 'TABLA_TIPO_SERVICIO_CONTRATO')->where('param_valor', '<>', 0)->get();

			return response()->json(["success" => "true", "mensaje" => "Listo correctamente", "data" => $tipo_acopio]);
		} catch (\Illuminate\Database\QueryException $e) {
			return response()->json(["success" => "false", "mensaje" => "Hubo un error al listar.Intente nuevamente", "data" => $e]);
		}
	}

	public function listar_contratos_dia() {
		try {
			$dataResponse = DB::table('inventario.contratos as con')
				->select(
					'con.nro_proceso_contrato as nro_proceso_contrato',
					'con.nro_proceso_interno as nro_proceso_interno',
					'con.id as contrato_id',
					'con.fecha_inicio as fecha_inicio',
					'con.fecha_final as fecha_fin',
					'con.tipo_servicio_id as servicio_id',
					'par.param_nombre as servicio_nombre',
					'par1.param_nombre as producto_tipo',
					'par2.param_valor as tipo_contrato_serv_id',
					'par2.param_nombre as tipo_contrato_serv_nombre',
					'art.nombre_producto as prog_nombre',
					'con.tipo_producto_id',
					'con.archivo',
					DB::raw('(CASE WHEN con.tipo_producto_id = 1 THEN
												(SELECT jsonb_agg(jsonb_build_object(
															\'nombre_producto\', prg.prog_nombre,
															\'codigo_alterno\', \'---\'
														))
												FROM siemc.programa prg
												WHERE prg.programa_id = con.programa_id)
											ELSE con.data END) as detalle_producto')
				)
				->join('acopio.parametricas as par', function ($join) {
					$join->on('con.tipo_servicio_id', '=', 'par.param_valor')
						->where('par.param_tabla', '=', 'TABLA_TIPO_CONTRATO')
						->where('par.param_valor', '<>', 0)
						->where('par.param_estado', '=', 'A');
				})
				->join('acopio.parametricas as par1', function ($join) {
					$join->on('con.tipo_producto_id', '=', 'par1.param_valor')
						->where('par1.param_tabla', '=', 'TABLA_TIPO_PRODUCTO_INSUMOS')
						->where('par1.param_valor', '<>', 0)
						->where('par1.param_estado', '=', 'A');
				})
				->join('acopio.parametricas as par2', function ($join) {
					$join->on('con.tipo_contrato_id', '=', 'par2.param_valor')
						->where('par2.param_tabla', '=', 'TABLA_TIPO_CONTRATO_SERVICIO')
						->where('par2.param_valor', '<>', 0)
						->where('par2.param_estado', '=', 'A');
				})
				->leftJoin('insumos.articulos as art', function ($join) {
					$join->on('con.articulo_id', '=', 'art.id')
						->where('art.estado', '=', 'A');
				})
				->where('con.estado', '=', 'A')
				->whereNull('con.deleted_at')
				->orderBy('contrato_id', 'desc')
				->get();

			return Datatables::of($dataResponse)
				->editColumn('detalle_producto', function ($fac) {
					return json_decode($fac->detalle_producto);
				})
				->addIndexColumn()
				->make(true);
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "No se puedo obtener el listado de los sucursales y almacenes", "data" => $ex]);
		}
	}

	public function registro_contrato(Request $request) {
		try {
			if ($request->has('id') && $request->id !== null) {
				return ($request->id);
			} else {
				switch ($request->tipo_contrato_id) {
				case 1:
					$fecha_inicio = Carbon::parse($request->intervalo[0])->setTimezone('America/La_Paz');
					$fecha_ini = $fecha_inicio->format('Y-m-d');
					$fecha_final = Carbon::parse($request->intervalo[1])->setTimezone('America/La_Paz');
					$fecha_fin = $fecha_final->format('Y-m-d');
					$contrato = new Contrato;
					$contrato->nro_proceso_contrato = strtoupper($request->nro_proceso_contrato);
					$contrato->nro_proceso_interno = strtoupper($request->nro_proceso_interno);
					$contrato->tipo_servicio_id = $request->tipo_contrato_id;
					$contrato->tipo_producto_id = $request->tipo_producto_id;
					$contrato->tipo_contrato_id = $request->tipo_con_servicios;
					if ($request->tipo_producto_id === 1) {
						$contrato->programa_id = $request->programa_id;
						$contrato->articulo_id = 0;
					} else {
						$contrato->programa_id = 0;
						$contrato->articulo_id = $request->articulo_id;
					}

					$contrato->observacion = '';
					$contrato->fecha_inicio = $fecha_ini;
					$contrato->fecha_final = $fecha_fin;
					$contrato->distribuidora_id = 0;
					$contrato->origen = $request->planta_id;
					$contrato->destino = $request->planta_id;
					$contrato->cantidad = $request->cantidad;
					$contrato->saldo = $request->cantidad;
					$contrato->usr_registrado = Auth::user()->id;
					$contrato->estado = 'A';
					$contrato->save();
					$detalle = json_decode($request->detalle, true);
					foreach ($detalle as $value) {
						try {
							$item = new ContratoDetalle;
							$item->tipo_contrato = 'PRODUCCION';
							$item->contrato_id = $contrato->id;
							$item->unidad_medida_id = $value['unidad_medida_id'];
							$item->origen = $request->planta_id;
							$item->destino = $request->planta_id;
							$item->volumen_estimado = $value['numberValue'];
							$item->plazo = 0;
							$item->estado = 'A';
							$item->data = json_encode($request->detalle, true);
							$item->usr_registrado = Auth::user()->id;
							$item->save();
						} catch (\Illuminate\Database\QueryException $ex) {
							return response()->json(["success" => "false", "mensaje" => "No se pudo registrar el contrato pruebe el catch", "data" => json_encode($ex)]);
						}
					}
					break;
				case 3:
					$fecha_inicio = Carbon::parse($request->fecha_inicio)->setTimezone('America/La_Paz');
					$fecha_ini = $fecha_inicio->format('Y-m-d');
					$fecha_final = Carbon::parse($request->fecha_fin)->setTimezone('America/La_Paz');
					$fecha_fin = $fecha_final->format('Y-m-d');
					$articulos = $request->articulo_id;
					if (($request->tipo_producto_id) == 1) {
						$datos = [];
					} else {
						$datos = array();
						foreach ($articulos as $value) {
							$consulta = Articulos::find($value);
							array_push($datos, $consulta);
						}
					}
					//return json_encode($datos);
					$contrato = new Contrato;
					$contrato->nro_proceso_contrato = strtoupper($request->nro_proceso_contrato);
					$contrato->nro_proceso_interno = strtoupper($request->nro_proceso_interno);
					$contrato->tipo_servicio_id = $request->tipo_contrato_id;
					$contrato->tipo_producto_id = $request->tipo_producto_id;
					$contrato->tipo_contrato_id = $request->tipo_con_servicios;
					$contrato->data = json_encode($datos);

					if ($request->tipo_producto_id === 1) {
						$contrato->programa_id = $request->programa_id;
						$contrato->articulo_id = 0;
					} else {
						$contrato->programa_id = 0;
						$contrato->articulo_id = 0;
					}
					$contrato->observacion = strtoupper($request->observacion ?? '');
					$contrato->fecha_inicio = $fecha_ini;
					$contrato->fecha_final = $fecha_fin;
					$contrato->distribuidora_id = $request->distribuidora_id;
					$contrato->usr_registrado = Auth::user()->id;
					$contrato->estado = 'A';
					$contrato->save();
					$detalle = json_decode($request->detalle, true);

					foreach ($detalle as $value) {
						try {
							$item = new ContratoDetalle;
							$item->tipo_contrato = 'LOGISTICA';
							$item->contrato_id = $contrato->id;
							$item->detalle = $value['detalle'] ?? '';
							$item->cantidad = $request->cantidad ?? 0;
							$item->monto_global = number_format((floatval($value['volumen_estimado']) * floatval($value['flete'])), 2, '.', '') ?? 0;
							$item->observaciones = $value['observaciones'] ?? '';
							$item->unidad_medida_id = $value['unidad_medida_id'];
							$item->origen = $value['almacen_origen_id'];
							$item->destino = $value['almacen_destino_id'];
							$item->volumen_estimado = $value['volumen_estimado'];
							$item->flete = $value['flete'];
							$item->estibaje_id = $value['estibalaje_id'];
							$item->plazo = $value['plazo'];
							$item->estado = 'A';
							$item->usr_registrado = Auth::user()->id;
							$item->save();
						} catch (\Illuminate\Database\QueryException $ex) {
							return response()->json(["success" => "false", "mensaje" => "No se pudo registrar el contrato pruebe el catch", "data" => json_encode($ex)]);
						}
					}
					break;
				case 4:
					$fecha_inicio = Carbon::parse($request->intervalo[0])->setTimezone('America/La_Paz');
					$fecha_ini = $fecha_inicio->format('Y-m-d');
					$fecha_final = Carbon::parse($request->intervalo[1])->setTimezone('America/La_Paz');
					$fecha_fin = $fecha_final->format('Y-m-d');
					$contrato = new Contrato;
					$contrato->nro_proceso_contrato = strtoupper($request->nro_proceso_contrato);
					$contrato->nro_proceso_interno = strtoupper($request->nro_proceso_interno);
					$contrato->tipo_servicio_id = $request->tipo_contrato_id;
					$contrato->tipo_producto_id = $request->tipo_producto_id;
					$contrato->tipo_contrato_id = $request->tipo_con_servicios;
					if ($request->tipo_producto_id === 1) {
						$contrato->programa_id = $request->programa_id;
						$contrato->articulo_id = 0;
					} else {
						$contrato->programa_id = 0;
						$contrato->articulo_id = $request->articulo_id;
					}

					$contrato->observacion = '';
					$contrato->fecha_inicio = $fecha_ini;
					$contrato->fecha_final = $fecha_fin;
					$contrato->distribuidora_id = 0;
					$contrato->origen = $request->planta_id;
					$contrato->destino = $request->planta_id;
					$contrato->cantidad = $request->cantidad;
					$contrato->saldo = $request->cantidad;
					$contrato->usr_registrado = Auth::user()->id;
					$contrato->estado = 'A';
					$contrato->save();
					$detalle = json_decode($request->detalle, true);
					foreach ($detalle as $value) {
						try {
							$item = new ContratoDetalle;
							$item->tipo_contrato = 'PRODUCCION';
							$item->contrato_id = $contrato->id;
							$item->unidad_medida_id = $value['unidad_medida_id'];
							$item->origen = $request->planta_id;
							$item->destino = $request->planta_id;
							$item->volumen_estimado = $value['numberValue'];
							$item->plazo = 0;
							$item->estado = 'A';
							$item->data = json_encode($request->detalle, true);
							$item->usr_registrado = Auth::user()->id;
							$item->save();
						} catch (\Illuminate\Database\QueryException $ex) {
							return response()->json(["success" => "false", "mensaje" => "No se pudo registrar el contrato pruebe el catch", "data" => json_encode($ex)]);
						}
					}
					break;
				case 5:
					$fecha_inicio = Carbon::parse($request->intervalo[0])->setTimezone('America/La_Paz');
					$fecha_ini = $fecha_inicio->format('Y-m-d');
					$fecha_final = Carbon::parse($request->intervalo[1])->setTimezone('America/La_Paz');
					$fecha_fin = $fecha_final->format('Y-m-d');
					$contrato = new Contrato;
					$contrato->nro_proceso_contrato = strtoupper($request->nro_proceso_contrato);
					$contrato->nro_proceso_interno = strtoupper($request->nro_proceso_interno);
					$contrato->tipo_servicio_id = $request->tipo_contrato_id;
					$contrato->tipo_producto_id = $request->tipo_producto_id;
					$contrato->tipo_contrato_id = $request->tipo_con_servicios;
					if ($request->tipo_producto_id === 1) {
						$contrato->programa_id = $request->programa_id;
						$contrato->articulo_id = 0;
					} else {
						$contrato->programa_id = 0;
						$contrato->articulo_id = $request->articulo_id;
					}

					$contrato->observacion = '';
					$contrato->fecha_inicio = $fecha_ini;
					$contrato->fecha_final = $fecha_fin;
					$contrato->distribuidora_id = 0;
					$contrato->origen = $request->planta_id;
					$contrato->destino = $request->planta_id;
					$contrato->cantidad = $request->cantidad;
					$contrato->saldo = $request->cantidad;
					$contrato->usr_registrado = Auth::user()->id;
					$contrato->estado = 'A';
					$contrato->save();
					$detalle = json_decode($request->detalle, true);
					foreach ($detalle as $value) {
						try {
							$item = new ContratoDetalle;
							$item->tipo_contrato = 'PRODUCCION';
							$item->contrato_id = $contrato->id;
							$item->unidad_medida_id = $value['unidad_medida_id'];
							$item->origen = $request->planta_id;
							$item->destino = $request->planta_id;
							$item->volumen_estimado = $value['numberValue'];
							$item->plazo = 0;
							$item->estado = 'A';
							$item->data = json_encode($request->detalle, true);
							$item->usr_registrado = Auth::user()->id;
							$item->save();
						} catch (\Illuminate\Database\QueryException $ex) {
							return response()->json(["success" => "false", "mensaje" => "No se pudo registrar el contrato pruebe el catch", "data" => json_encode($ex)]);
						}
					}
					break;
				}
				return response()->json(["success" => "true", "mensaje" => "El contrato se registro correctamente", "data" => $contrato]);
			}
		} catch (\Illuminate\Database\QueryException $ex) {
			return response()->json(["success" => "false", "mensaje" => "No se pudo registrar el contrato, intente mas tarde", "data" => $ex]);
		}
	}

	public function listar_tipo_contrato_porservicios() {
		try {
			$tipo_contrato = Parametrica::where('param_tabla', 'TABLA_TIPO_CONTRATO_SERVICIO')->where('param_valor', '<>', 0)->get();

			return response()->json(["success" => "true", "mensaje" => "Listo correctamente", "data" => $tipo_contrato]);
		} catch (\Illuminate\Database\QueryException $e) {
			return response()->json(["success" => "false", "mensaje" => "Hubo un error al listar.Intente nuevamente", "data" => $e]);
		}
	}

	public function listar_contratos_logistica() {
		try {
			$contratosLogistica = Contrato::select('id', 'nro_proceso_contrato', 'nro_proceso_interno')->where('estado', 'A')->where('tipo_servicio_id', 3)->where('estado_uso', 'A')->orderBy('id', 'desc')->get();
			return DataTables::of($contratosLogistica)
				->addIndexColumn()
				->make(true);
		} catch (\Illuminate\Database\QueryException $e) {
			return response()->json(["success" => "false", "mensaje" => "Hubo un error al listar.Intente nuevamente", "data" => $e]);
		}
	}

	public function regularizacion_boleta_manual(Request $request) {
		try {
			$usuario = Auth::user()->id;
			$existeBoleta = DB::table('logistica.solicitud_logistica')
				->where('id', '=', $request->id)
				->where('estado_registro', '=', 'A')
				->count();
			if ($existeBoleta > 0) {
				$correlativo_regularizacion = DB::table('logistica.solicitud_logistica')
					->where('id', '=', $request->id)
					->where('estado_registro', '=', 'A')
					->update([
						'contrato_id' => $request->nro_contrato_gnrl,
						'fecha_regularizacion' => now(),
						'estado_regularizacion' => 'A',
						'usr_regularizacion' => $usuario,
						'usr_modificado' => $usuario,
						// 'nro_sistema' => DB::raw('nextval(\'boleta_sistema_logistica\')'),
						'proceso_id' => 3,
						'updated_at' => now(),
					]);
				return response()->json(["success" => "true", "mensaje" => "SE ACTUALIZÓ CORRECTAMENTE EL CONTRATO EN LA BOLETA PRELIMINAR", "data" => []]);
			} else {
				return response()->json(["success" => "false", "mensaje" => "No existe la boleta Manual", "data" => []]);
			}
		} catch (\Exception $e) {
			return response()->json(["success" => "false", "mensaje" => "No se pudo regularizar la boleta con el contrato, intente nuevamente", "data" => $e]);
		}
	}

	public function listar_tipo_solicitudes_productos() {
		$tipoSolicitudes = DB::table('acopio.parametricas')
			->select('id', 'param_valor', 'param_nombre', 'param_tabla')
			->where('param_tabla', 'TABLA_TIPO_PRODUCTO_INSUMOS')
			->where('param_valor', '!=', 0)
			->where('param_estado', 'A')
			->get();
		return $tipoSolicitudes;
	}

	public function listar_productos_solicitud($tipo) {
		$tipoSolicitudes = DB::table('insumos.articulos as art')
			->select('art.id as art_id', 'art.identificador_mapeo', 'art.nombre_producto', 'art.codigo_alterno', 'art.estado_id', 'art.tipo_material_id', 'uni.id as uni_id', DB::raw('UPPER(uni.nombre)'), DB::raw('UPPER(uni.abreviatura) AS abreviatura'))
			->join('insumos.unidad_medida as uni', function ($join) {
				$join->on('art.unidad_medida_id', '=', 'uni.id')
					->where('uni.estado', '=', 'A');
			})
			->where('art.tipo_material_id', $tipo)
			->where('art.estado', '=', 'A')
			->get();

		return Datatables::of($tipoSolicitudes)
			->addIndexColumn()
			->make(true);
	}

	public function listar_registros_sin_contrato_logistica() {
		try {
			$resultados = DB::table('inventario.contratos as con')
				->leftJoin(DB::raw('(SELECT sol.contrato_id, jsonb_agg(jsonb_build_object(
                                \'id_log\', sol.id,
                                \'codigo_log\', sol.codigo_solicitud,
                                \'id_inv\', inv.id,
                                \'codigo_solicitud\', inv.solicitud_id,
                                \'tipo_solicitud_id\', par.param_valor,
                                \'tipo_solicitud\', par.param_nombre,
                                \'origen\', suc1.nombre,
                                \'destino\', suc2.nombre
                            )) AS solicitudes
            FROM logistica.solicitudes_movimiento_logistica as sol
            INNER JOIN inventario.solicitud_inventarios as inv ON sol.id_orden = inv.id
            INNER JOIN acopio.parametricas as par ON inv.tipo_solicitud_id = par.param_valor AND par.param_tabla = \'TABLA_TIPO_SOLICITUD\' AND par.param_valor <> 0
            INNER JOIN public.sucursals as suc1 ON inv.origen_id = suc1.id
            INNER JOIN public.sucursals as suc2 ON inv.destino_id = suc2.id
            WHERE sol.estado = \'A\' AND sol.estado_asignacion = \'A\' AND sol.deleted_at IS NULL
            GROUP BY sol.contrato_id) AS detalle'), 'con.id', '=', 'detalle.contrato_id')
				->select(
					'con.id',
					'con.nro_proceso_contrato',
					'con.nro_proceso_interno',
					'con.fecha_inicio',
					'con.fecha_final',
					'con.observacion',
					'con.created_at',
					'detalle.solicitudes AS solicitudes'
				)
				->where('con.tipo_servicio_id', 3)
				->where('con.tipo_contrato_id', 2)
				->where('con.regularizacion', false)
				->whereNull('con.deleted_at')
				->where('con.estado', 'A')
				->whereDate('con.created_at', '=', now()->toDateString())
				->orderBy('con.id', 'ASC')
				->get();
			return Datatables::of($resultados)
				->addIndexColumn()
				->editColumn('solicitudes', function ($data) {
					$solicitudes = str_replace('&quot;', '"', $data->solicitudes);
					$solicitudes_array = json_decode($solicitudes, true);
					return $solicitudes_array;
				})
				->rawColumns([
					'solicitudes',
				])
				->make(true);
		} catch (\Exception $e) {
			return response()->json(["success" => "false", "mensaje" => "No se encontraron datos de los registros, intente mas tarde", "data" => $e]);
		}
	}

	public function regularizacion_contrato_logistica(Request $request) {
		try {
			$det_solicitudes = $request->solicitudes;
			$usuario = Auth::user()->id;
			DB::table('inventario.contratos')
				->where('id', $request->id)
				->update([

					'regularizacion' => true,
					'usr_modificado' => $usuario,
					'updated_at' => now(),
					'estado' => 'B',
					'estado_uso' => 'B',
					'data' => DB::raw("jsonb_build_object(
											'observacion_regularizacion', '" . $request->observaciones_regularizacion . "',
											'usr_regularizacion', " . $usuario . ",
											'fecha_regularizacion', '" . Carbon::now() . "',
											'estado', 'BAJA POR REGULARIZACION DE CONTRATO ',
											'contrato','" . $request->nro_proceso_contrato_regularizado . "'
										)::jsonb"),
				]);

			$solicitud_request = Contrato::find($request->nro_proceso_contrato_regularizado);
			$solicitud_request->estado_uso = 'B';
			$solicitud_request->updated_at = now();
			$solicitud_request->save();
			foreach ($det_solicitudes as $key => $value) {
				$solicitud_request = SolicitudMovimiento::find($value['id_log']);
				$solicitud_request->contrato_id = $request->nro_proceso_contrato_regularizado;
				$solicitud_request->updated_at = now();
				$solicitud_request->save();
			}
			return response()->json(["success" => "true", "mensaje" => "SE ACTUALIZÓ CORRECTAMENTE EL CONTRATO", "data" => []]);
		} catch (\Exception $e) {
			return response()->json(["success" => "false", "mensaje" => "No se encontraron datos de los registros, intente mas tarde", "data" => $e]);
		}
	}

	public function listar_registros_sin_contrato_logistica_intervalo(Request $request) {
		try {
			$fecha_inicio = Carbon::parse($request->intervalo[0])->setTimezone('America/La_Paz');
			$fecha_ini = $fecha_inicio->format('Y-m-d');
			$fecha_final = Carbon::parse($request->intervalo[1])->setTimezone('America/La_Paz');
			$fecha_fin = $fecha_final->format('Y-m-d');
			$resultados = DB::table('inventario.contratos as con')
				->leftJoin(DB::raw('(SELECT sol.contrato_id, jsonb_agg(jsonb_build_object(
                                \'id_log\', sol.id,
                                \'codigo_log\', sol.codigo_solicitud,
                                \'id_inv\', inv.id,
                                \'codigo_solicitud\', inv.solicitud_id,
                                \'tipo_solicitud_id\', par.param_valor,
                                \'tipo_solicitud\', par.param_nombre,
                                \'origen\', suc1.nombre,
                                \'destino\', suc2.nombre
                            )) AS solicitudes
            FROM logistica.solicitudes_movimiento_logistica as sol
            INNER JOIN inventario.solicitud_inventarios as inv ON sol.id_orden = inv.id
            INNER JOIN acopio.parametricas as par ON inv.tipo_solicitud_id = par.param_valor AND par.param_tabla = \'TABLA_TIPO_SOLICITUD\' AND par.param_valor <> 0
            INNER JOIN public.sucursals as suc1 ON inv.origen_id = suc1.id
            INNER JOIN public.sucursals as suc2 ON inv.destino_id = suc2.id
            WHERE sol.estado = \'A\' AND sol.estado_asignacion = \'A\' AND sol.deleted_at IS NULL
            GROUP BY sol.contrato_id) AS detalle'), 'con.id', '=', 'detalle.contrato_id')
				->select(
					'con.id',
					'con.nro_proceso_contrato',
					'con.nro_proceso_interno',
					'con.fecha_inicio',
					'con.fecha_final',
					'con.observacion',
					'con.created_at',
					'detalle.solicitudes AS solicitudes'
				)
				->where('con.tipo_servicio_id', 3)
				->where('con.tipo_contrato_id', 2)
				->where('con.regularizacion', false)
				->whereNull('con.deleted_at')
				->whereBetween('con.created_at', [$fecha_ini, $fecha_fin])
				->where('con.estado', 'A')
				->orderBy('con.id', 'ASC')
				->get();
			return Datatables::of($resultados)
				->addIndexColumn()
				->editColumn('solicitudes', function ($data) {
					$solicitudes = str_replace('&quot;', '"', $data->solicitudes);
					$solicitudes_array = json_decode($solicitudes, true);
					return $solicitudes_array;
				})
				->rawColumns([
					'solicitudes',
				])
				->make(true);
		} catch (\Exception $e) {
			return response()->json(["success" => "false", "mensaje" => "No se encontraron datos de los registros, intente mas tarde", "data" => $e]);
		}
	}

	public function subida_imagen_contrato(Request $request) {
		try {
			$data = json_decode($request->data);
			$contrato_id = $data->contrato_id;
			$usuario = Auth::user()->id;

			$file = $request->file('imagen');
			$nombreImagen = 'LOGISTICA_' . $file->hashName();
			\Storage::disk('contratos')->put($nombreImagen, \File::get($file));
			$request_archivo = $nombreImagen;
			$solicitud_request = Contrato::find($contrato_id);
			$solicitud_request->usr_modificado = $usuario;
			$solicitud_request->archivo = $request_archivo;
			$solicitud_request->updated_at = now();
			$solicitud_request->save();

			return response()->json(["success" => "true", "mensaje" => "Archivo registrado correctamente!!!", "data" => $solicitud_request]);
		} catch (\Exception $e) {
			return response()->json(["success" => "false", "mensaje" => "No se pudo subir la imagen", "data" => $e]);
		}
	}
	public function get_all_contratos() {
		try {
			$contratosLogistica = Contrato::select('id', 'nro_proceso_contrato', 'nro_proceso_interno')->where('estado', 'A')->where('tipo_servicio_id', 3)->where('tipo_contrato_id', 1)->where('estado_uso', 'A')->orderBy('id', 'desc')->get();
			return DataTables::of($contratosLogistica)
				->addIndexColumn()
				->make(true);
		} catch (\Illuminate\Database\QueryException $e) {
			return response()->json(["success" => "false", "mensaje" => "Hubo un error al listar.Intente nuevamente", "data" => $e]);
		}
	}
	public function ver_contrato_generado($contrato_id)
	{
		$resp = $this->get_contrato_generado($contrato_id);
		$cont = base64_encode($resp);
		return response()->json(["data" => $cont]);
	}

	private function get_contrato_generado($contrato_id)
	{
		$contrato = Contrato::find($contrato_id);
		$detalle = ContratoDetalle::where('contrato_id', $contrato_id)->get();
		$articulo = Articulos::find($contrato->articulo_id);
		$tipo_contrato = Parametrica::where('param_tabla', 'TABLA_TIPO_CONTRATO')
        ->where('param_valor', $contrato->tipo_contrato_id)
        ->first();
		
		$tipo_servicio = Parametrica::where('param_tabla', 'TABLA_TIPO_CONTRATO_SERVICIO')
        ->where('param_valor', $contrato->tipo_servicio_id)
        ->first();

		$tipo_producto = Parametrica::where('param_tabla', 'TABLA_TIPO_PRODUCTO_INSUMOS')
        ->where('param_valor', $contrato->tipo_producto_id)
        ->first();

		$user_ = Auth::user();
		$subtitulo = 'Registro';
		$dependencia = 'GERENCIA';
		$origen = Planta::find($contrato->origen);
		$destino = Planta::find($contrato->destino);
		if ($contrato->tipo_servicio_id == 3) {
			foreach ($detalle as $detalle_contrato) {
				$origen_sucursal = Sucursal::find($detalle_contrato->origen);
				$destino_sucursal = Sucursal::find($detalle_contrato->destino);

				$detalle_contrato->origen_nombre = $origen_sucursal->nombre ?? '';
				$detalle_contrato->destino_nombre = $destino_sucursal->nombre ?? '';
			}
		}
		$date = now();
		$count = 0;
		$title = " CONTRATO DE SERVICIO ";
		$view = \View::make('report.boleta_contrato_servicio', 
		compact( 'subtitulo', 'dependencia', 'count', 'user_', 'title', 'date', 'contrato','detalle', 'articulo', 'tipo_contrato', 'tipo_servicio', 'tipo_producto', 'origen', 'destino'));
		$html_content = $view->render();
		$pdf = App::make('snappy.pdf.wrapper');
		$pdf->loadHTML($html_content)->setPaper('Letter')->setOrientation('portrait');
		$pdf->setOption('disable-javascript', true);
		$pdf->setOption('images', true);
		$pdf->stream();
		return $pdf->inline();
	}
	public function edit_contrato_logistica($contrato_id){
		try {
			$contrato = Contrato::with('solicitud_logistica.solicitud_detalle_logistica')->find($contrato_id);
			// $solicitudes = SolicitudInventario::where('contrato_id', $contrato_id)->get();
			// $data_solicitudes = [];
			// foreach ($contrato->solicitud_logistica as $solicitud) {
			// 	foreach ($solicitud->solicitud_detalle_logistica as $detalle) {
			// 		$data_solicitudes[] = [
			// 			'id' => $solicitud->id,
			// 			'codigo_solicitud' => $solicitud->codigo_solicitud,
			// 			'conductor_id' => $solicitud->conductor_id,
			// 			'estado' => $solicitud->estado,
			// 			'id_detalle' => $detalle->id,
			// 			'logistica_id' => $detalle->logistica_id,
			// 			'cantidad' => $detalle->cantidad,
			// 			'codigo_boleta' => $detalle->codigo_boleta,
			// 			'fecha_despacho' => $detalle->fecha_despacho,
			// 		];
			// 	}
			// }
			return response()->json([
				'contrato' => $contrato,
				// 'solicitudes_logistica' => $data_solicitudes,
				// 'solicitud' => $solicitudes
			]);
		} catch (\Illuminate\Database\QueryException $e) {
			return response()->json(["success" => "false", "mensaje" => "Hubo un error al listar. Intente nuevamente", "data" => $e]);
		}
	}
	public function edicion_contrato($numero_contrato){
		try{
			$contrato = Contrato::where('id',$numero_contrato)->where('estado', 'A')->first();
			return $contrato;
		}
		catch (\Illuminate\Database\QueryException $e) {
			return response()->json(["success" => "false", "mensaje" => "Hubo un error al listar.Intente nuevamente", "data" => $e]);
		}
	}
	public function editar_contrato(Request $request , $id){
		try{
			// return $request;
			$data = $request->all();
			$contrato = Contrato::findOrFail($id);
			if (isset($data['nro_proceso_contrato'])) {
				$contrato->nro_proceso_contrato = $data['nro_proceso_contrato'];
			}
			$contrato->save();
			return response()->json(["success" => "true", "mensaje" => "El contrato se actualizó correctamente", "data" => $contrato]);
		}
		catch (\Illuminate\Database\QueryException $e) {
			return response()->json(["success" => "false", "mensaje" => "Hubo un error al listar.Intente nuevamente", "data" => $e]);
		}
	}
}
