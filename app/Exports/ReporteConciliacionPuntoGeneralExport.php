<?php

namespace App\Exports;

use App\Models\Insumos\Articulos;
use App\Models\Inventario\StockExistenciaInventario;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class ReporteConciliacionPuntoGeneralExport implements FromView {
	public function __construct(string $fechaInicio, string $fechaFin, int $tipoLineaId, int $tipoProductoId, int $tipoCasoId, int $tipoReporteId, int $tipoSolicitudId, int $puntoId, int $gestion, int $solicitudId) {
        $this->fechaInicio      = $fechaInicio;
        $this->fechaFin         = $fechaFin;
        $this->tipoLineaId      = $tipoLineaId;
        $this->tipoProductoId   = $tipoProductoId;
        $this->tipoCasoId       = $tipoCasoId;
        $this->tipoReporteId    = $tipoReporteId;
        $this->tipoSolicitudId  = $tipoSolicitudId;
        $this->puntoId          = $puntoId;
        $this->gestion          = $gestion;
		$this->solicitudId      = $solicitudId;

	}
	public function view(): View {
		try {
			//$datosSucursal = Sucursal::find($this->almacen);
			//$storage = $datosSucursal->nombre;
			//$history = \DB::select('select * from inventario.sp_calcular_kardex_valorado(?,?)', array($this->almacen, $this->producto));
			$username = Auth::user()->name;
			$date = Carbon::now();
			$quantity = 0;
			$count = 1;
			switch ($this->tipoSolicitudId) {
				case 3: //ORDEN DESPACHO
					switch ($this->tipoReporteId) {
						case 3: //REPORTE COMPLETO
							if($this->tipoCasoId == 1){
								$conciliacion = \DB::select('select * from inventario.sp_reporte_conciliacion_despacho_general_punto(?)', array($this->puntoId));
							} else {
								$conciliacion = \DB::select('select * from inventario.sp_reporte_verificacion_conciliacion_despacho_punto_fechas(?,?,?)', array($this->puntoId, $this->fechaInicio, $this->fechaFin));
							}
							break;
						case 1: //REPORTE POR LINEA
							$conciliacion = \DB::select('select * from inventario.sp_reporte_verificacion_conciliacion_despacho_linea(?,?,?,?,?,?)', array($this->tipoProductoId, $this->tipoLineaId, $this->gestion, $this->fechaInicio, $this->fechaFin, $this->puntoId));
							break;
						case 2: //REPORTE POR SOLICITUD
							$conciliacion = \DB::select('select * from inventario.sp_reporte_conciliacion_despacho_general_solicitud(?)', array($this->solicitudId));
							break;
					}
					break;
				case 7: //ORDEN CARGA
					switch ($this->tipoReporteId) {
						case 3: //REPORTE COMPLETO
							if($this->tipoCasoId == 1){
								$conciliacion = \DB::select('select * from inventario.sp_reporte_conciliacion_carga_general_punto(?)', array($this->puntoId));
							} else {
								$conciliacion = \DB::select('select * from inventario.sp_reporte_verificacion_conciliacion_carga_punto_fechas(?,?,?)', array($this->puntoId, $this->fecha_ini, $this->fecha_fin));
							}
							break;
						case 1: //REPORTE POR LINEA
							$conciliacion = \DB::select('select * from inventario.sp_reporte_verificacion_conciliacion_carga_linea(?,?,?,?,?,?)', array($this->tipoProductoId, $this->tipoLineaId, $this->gestion, $this->fechaInicio, $this->fechaFin, $this->puntoId));
							break;
						case 2: //REPORTE POR SOLICITUD
							$conciliacion = \DB::select('select * from inventario.sp_reporte_conciliacion_carga_general_solicitud(?)', array($this->solicitudId));
							break;
					}
					break;
				case 4: //ORDEN TRASLADO
					switch ($this->tipoReporteId) {
						case 3: //REPORTE COMPLETO
							if($this->tipoCasoId == 1){
								$conciliacion = \DB::select('select * from inventario.sp_reporte_conciliacion_traslado_general_punto(?)', array($this->puntoId));
							} else {
								$conciliacion = \DB::select('select * from inventario.sp_reporte_verificacion_conciliacion_traslado_punto_fechas(?,?,?)', array($this->puntoId, $this->fechaInicio, $this->fechaFin));
							}
						break;
						case 1: //REPORTE POR LINEA
							$conciliacion = \DB::select('select * from inventario.sp_reporte_verificacion_conciliacion_traslado_linea(?,?,?,?,?,?)', array($this->tipoProductoId, $this->tipoLineaId, $this->gestion, $this->fechaInicio, $this->fechaFin, $this->puntoId));
							break;
						case 2: //REPORTE POR SOLICITUD
							$conciliacion = \DB::select('select * from inventario.sp_reporte_conciliacion_traslado_general_solicitud(?)', array($this->solicitudId));
							break;
					}
					break;
				case 8: //ORDEN ENVIO
					switch ($this->tipoReporteId) {
						case 3: //REPORTE COMPLETO
							if($this->tipoCasoId == 1){
								$conciliacion = \DB::select('select * from inventario.sp_reporte_conciliacion_envio_general_punto(?)', array($this->puntoId));
							} else {
								$conciliacion = \DB::select('select * from inventario.sp_reporte_verificacion_conciliacion_envio_punto_fechas(?,?,?)', array($this->puntoId, $this->fechaInicio, $this->fechaFin));
							}
						case 1: //REPORTE POR LINEA
							$conciliacion = \DB::select('select * from inventario.sp_reporte_verificacion_conciliacion_envio(?,?,?,?,?)', array($this->tipoProductoId, $this->tipoLineaId, $this->gestion, $this->fechaInicio, $this->fechaFin, $this->puntoId));
							break;
						case 2: //REPORTE POR SOLICITUD
							$conciliacion = \DB::select('select * from inventario.sp_reporte_conciliacion_envio_general_solicitud(?)', array($this->solicitudId));
							break;
					}
					break;
			}
			switch ($this->tipoSolicitudId){
				case 3: //ORDEN DESPACHO
					return view('reportExcel.reporte_conciliacion_despacho_punto_export', [
						'date' => $date, 'username' => $username, 'conciliacion' => $conciliacion,
					]);
					break;
				case 7: //ORDEN CARGA
					return view('reportExcel.reporte_conciliacion_carga_punto_export', [
						'date' => $date, 'username' => $username, 'conciliacion' => $conciliacion,
					]);
					break;
				case 4: //ORDEN TRASLADO
					return view('reportExcel.reporte_conciliacion_traslado_punto_export', [
						'date' => $date, 'username' => $username, 'conciliacion' => $conciliacion,
					]);
					break;
				case 8: //ORDEN ENVIO
					return view('reportExcel.reporte_conciliacion_envio_punto_export', [
						'date' => $date, 'username' => $username, 'conciliacion' => $conciliacion,
					]);
					break;		
			}

		} catch (\Illuminate\Database\QueryException $ex) {
			var_dump($ex);
			return response()->json(["success" => "false", "mensaje" => $ex]);
		}
	}
}
