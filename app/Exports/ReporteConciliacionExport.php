<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteConciliacionExport implements FromCollection, WithHeadings
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $tipoSolicitudId;
    protected $tipoLineaId;
    protected $gestion;
    protected $tipoProductoId;
    protected $tipoCasoId;

    public function __construct($tipoCasoId, $tipoSolicitudId, $tipoProductoId, $tipoLineaId, $gestion, $fechaInicio, $fechaFin)
    {
        $this->tipoCasoId = $tipoCasoId;
        $this->tipoSolicitudId = $tipoSolicitudId;
        $this->tipoProductoId = $tipoProductoId;
        $this->tipoLineaId = $tipoLineaId;
        $this->gestion = $gestion;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }
    public function headings(): array
    {
        return [
            '#',
            'Tipo Solicitud',
            'Nro. Solicitud',
            'Codigo Solicitud',
            'Estado',
            'Usuario Solicitud',
            'Origen',
            'Destino',
            'Nombre Productor',
            'Identificacion Productor',
            'Asociacion',
            'Id_Logisitica',
            'Nro Logistica',
            'Codigo Logistica',
            'Transportadora',
            'Conductor',
            'Identificacion Conductor',
            'Placa',
            'Codigo Producto',
            'Nombre Producto',
            'Fecha Solicitud',
            'Cantidad Solicitada',
            'Nombre Origen',
            'Nro Boleta Salida',
            'Fecha Salida',
            'Cantidad Salida',
            'Cantidad Salida Acumulada',
            'Saldo',
            'Nombre Destino',
            'Nro Boleta Ingreso',
            'Fecha Ingreso',
            'Cantidad Ingreso',
            'Saldo Faltante Ingreso'
        ];
    }
    public function collection()
    {
        ini_set('memory_limit', '-1');
        $reporteConciliacion = DB::select("SELECT * FROM inventario.sp_reporte_conciliacion_general(" . $this->tipoCasoId . "," . $this->tipoSolicitudId . "," . $this->tipoProductoId . "," . $this->tipoLineaId . "," . $this->gestion . ",'" . $this->fechaInicio . "','" . $this->fechaFin . "')");
        return collect($reporteConciliacion);
    }
}
