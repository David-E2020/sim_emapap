<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteConciliacionDespachoResumenExport implements FromCollection, WithHeadings
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
    protected $tipo_solicitud_id;

    public function __construct($fechaInicio, $fechaFin, $tipoLineaId, $tipoProductoId, $tipoId, $tipoReporteId, $solicitudId, $puntoId, $gestion, $tipo_solicitud_id)
    {
        $this->fechaInicio          = $fechaInicio;
        $this->fechaFin             = $fechaFin;
        $this->tipoLineaId          = $tipoLineaId;
        $this->tipoProductoId       = $tipoProductoId;
        $this->tipoId               = $tipoId;
        $this->tipoReporteId        = $tipoReporteId;
        $this->solicitudId          = $solicitudId;
        $this->puntoId              = $puntoId;
        $this->gestion              = $gestion;
        $this->tipo_solicitud_id    = $tipo_solicitud_id;
    }
    public function headings(): array
    {
        return [
            '#',
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
            'Numero Identificacion Productor', 
            'Asociación',
            'Codigo Unico Producto',
            'Nombre Producto',
            'Fecha Solicitud',
            'Cantidad Solicitada',
            'Nombre Origen',
            'Cantidad Salida',
            'Cantidad Salida Acumulado',
            'Saldo',
            'Producto Comercial',
            'Nombre Ingreso',
            'Cantidad Ingreso',
        ];
    }
    public function collection()
    {
        ini_set('memory_limit', '-1');

        switch ($this->tipoReporteId){
            case 1: //REPORTE POR LINEA
                $reporteConciliacion = DB::select("SELECT * FROM inventario.sp_reporte_conciliacion_despacho_resumen(" . $this->tipoProductoId . "," . $this->tipoLineaId . "," . $this->gestion . ",'" . $this->fechaInicio . "','" . $this->fechaFin . "')");
            break;
            case 2: //REPORTE POR SOLICITUD
                $reporteConciliacion = DB::select("SELECT * FROM inventario.sp_reporte_conciliacion_despacho_resumen_solicitud(" . $this->solicitudId . ")");
            break;
            case 3: //REPORTE POR ALMACEN
                $reporteConciliacion = DB::select("SELECT * FROM inventario.sp_reporte_conciliacion_despacho_resumen_punto(" . $this->puntoId . ")");
            break;
        }
        
        return collect($reporteConciliacion);
    }
}
