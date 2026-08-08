<?php

namespace App\Exports;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteConciliacionDespachoGeneralExport implements FromCollection, WithHeadings
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $tipoLineaId;
    protected $tipoProductoId;
    protected $tipoId;
    protected $tipoReporteId;
    protected $solicitudId;
    protected $puntoId;
    protected $gestion;

    public function __construct($fechaInicio, $fechaFin, $tipoLineaId, $tipoProductoId, $tipoId, $tipoReporteId, $solicitudId, $puntoId, $gestion)
    {
        $this->fechaInicio      = $fechaInicio;
        $this->fechaFin         = $fechaFin;
        $this->tipoLineaId      = $tipoLineaId;
        $this->tipoProductoId   = $tipoProductoId;
        $this->tipoId           = $tipoId;
        $this->tipoReporteId    = $tipoReporteId;
        $this->solicitudId      = $solicitudId;
        $this->puntoId          = $puntoId;
        $this->gestion          = $gestion;
    }
    public function headings(): array
    {
        return [
            'Tipo Solicitud',
            'Nro. Solicitud',
            'Codigo Solicitud',
            'Estado',
            'Tipo Venta',
            'Tipo Orden',
            'Usuario Solicitud',
            'Origen',
            'Destino',
            'Nombre Productor',
            'Nombre Identificacion Productor',
            'Asociacion', 
            'Conductor',
            'Conductor Identificacion',
            'Placa Vihiculo',
            'Codigo Unico Producto',
            'Producto',
            'Fecha Solicitud',
            'Cnatidad Solicitada',
            'Nombre Origen',
            'Usuario Origen',
            'Nro Boleta Salida',
            'Lote Salida', 
            'Fehca Salida',
            'Cantidad Salida',
            'Cantidad Salida Acumulada',
            'Saldo',
            'Producto Comercial',
            'Nombre Ingreso',
            'Usuario Ingreso',
            'Nro Boleta Ingreso',
            'Fecha Ingreso',
            'Cantidad Ingreso'
        ];
    }
    public function collection()
    {
        ini_set('memory_limit', '-1');
        switch ($this->tipoReporteId){
            case 1: //REPORTE POR LINEA
                $reporteConciliacion = DB::select("SELECT * FROM inventario.sp_reporte_conciliacion_despacho_general(" . $this->tipoProductoId . "," . $this->tipoLineaId . "," . $this->gestion . ",'" . $this->fechaInicio . "','" . $this->fechaFin . "')");
            break;
			case 2: //REPORTE POR SOLICITUD
                $reporteConciliacion = DB::select("SELECT * FROM inventario.sp_reporte_conciliacion_despacho_general_solicitud(" . $this->solicitudId . ")");
            break;
            case 3: //REPORTE POR ALMACEN
                $reporteConciliacion = DB::select("SELECT * FROM inventario.sp_reporte_conciliacion_despacho_general_punto(" . $this->puntoId . ")");
            break;
        }
        return collect($reporteConciliacion);
    }
}