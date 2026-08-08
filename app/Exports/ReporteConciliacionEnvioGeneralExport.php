<?php

namespace App\Exports;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteConciliacionEnvioGeneralExport implements FromCollection, WithHeadings
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
            'Usuario Solicitud',
            'Origen',
            'Destino',
            
            'Codigo Logistica',
            'Estado Logistica',
            'Usuario Logistica',
            'Cantidad Asignado Logistica',
            'Contrato',
            'Transportadora',
            'Placa',
            'Marca Vehiculo',
            'Tipo Vehiculo',
            'Color Vihiculo',
            'Conductor',
            'Conductor Identificacion',

            'Codigo Producto',
            'Nombre Producto',
            'Fecha Solicitud',
            'Cantidad Solicitada',
            'Nombre Salida',
            'Usuario Salida',
            'Nro Boleta Salida',
            'Fecha Salida',
            'Cantidad Salida',
            'Cantidad Salida Acumulada',
            'Saldo',
            'Producto Comercial',
            'Nombre Ingreso',
            'Usario Ingreso',
            'Nro Boleta Ingreso',
            'Fecha Ingreso',
            'Cantidad Ingreso',
            'Saldo Faltante Ingreso'
        ];
    }
    public function collection()
    {
        ini_set('memory_limit', '-1');
        switch ($this->tipoReporteId){
            case 1: //REPORTE POR LINEA
                $reporteConciliacion = DB::select("SELECT * FROM inventario.sp_reporte_conciliacion_envio_general(" . $this->tipoProductoId . "," . $this->tipoLineaId . "," . $this->gestion . ",'" . $this->fechaInicio . "','" . $this->fechaFin . "')");
            break;
			case 2: //REPORTE POR SOLICITUD
                $reporteConciliacion = DB::select("SELECT * FROM inventario.sp_reporte_conciliacion_envio_general_solicitud(" . $this->solicitudId . ")");
            break;
            case 3: //REPORTE POR ALMACEN
                $reporteConciliacion = DB::select("SELECT * FROM inventario.sp_reporte_conciliacion_envio_general_punto(" . $this->puntoId . ")");
            break;
        }
        return collect($reporteConciliacion);
    }
}
