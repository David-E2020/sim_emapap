<?php

namespace App\Exports;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteMovimientoInventariosExport implements FromCollection, WithHeadings
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $tipoPlantaId;
    protected $puntoId;
    protected $gestion;
    protected $tipoMovimientoId;
    protected $tipoProgramaId;

    public function __construct($fechaInicio, $fechaFin, $tipoPlantaId, $puntoId, $gestion, $tipoMovimientoId, $tipoProgramaId)
    {
        $this->fechaInicio      = $fechaInicio;
        $this->fechaFin         = $fechaFin;
        $this->tipoPlantaId     = $tipoPlantaId;
        $this->puntoId          = $puntoId;
        $this->gestion          = $gestion;
        $this->tipoMovimientoId = $tipoMovimientoId;
        $this->tipoProgramaId   = $tipoProgramaId;




    }
    public function headings(): array
    {
        return [
            'TIPO PLANTA',
            'PLANTAS',
            'PUNTO(ALMACEN/MOLINO)',
            'TIPO MOVIMIENTO',
            'TIPO',
            'CODIGO',
            'PRODUCTO',
            'LOTE',
            'CANTIDAD SOLICITADA',
            'NUMERO BOLETA',
            'FECHA MOVIMIENTO',
            'CANTIDAD MOVIMIENTO',
            'CANTIDAD ACUMULADO SALDO',
            'SALDO', 
            'OBSERVACION'
        ];
    }
    public function collection()
    {
        ini_set('memory_limit', '-1'); 

        $reporteMovimiento = DB::select("SELECT * FROM inventario.sp_reporte_movimientos_inventarios('" . $this->fechaInicio . "','" . $this->fechaFin . "' , " . $this->tipoPlantaId . "," . $this->puntoId . "," . $this->gestion . ",". $this->tipoMovimientoId .",". $this->tipoProgramaId .")");

        return collect($reporteMovimiento);
    }
}