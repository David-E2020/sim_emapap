<?php

namespace App\Exports;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteExistenciaInventariosExport implements FromCollection, WithHeadings
{
    protected $tipoPlantaId;
    protected $puntoId;
    protected $gestion;
    protected $tipoMovimientoId;
    protected $tipoProgramaId;

    public function __construct($tipoPlantaId, $puntoId, $gestion, $tipoMovimientoId, $tipoProgramaId)
    {
        $this->tipoPlantaId     = $tipoPlantaId;
        $this->puntoId          = $puntoId;
        $this->gestion          = $gestion;
        $this->tipoMovimientoId = $tipoMovimientoId;
        $this->tipoProgramaId   = $tipoProgramaId;




    }
    public function headings(): array
    {
        return [
            'REGION',
            'PLANTAS',
            'TIPO PLANTA',
            'PUNTO',
            'PRODUCTO',
            'LOTE',
            'SALDO'
        ];
    }
    public function collection()
    {
        ini_set('memory_limit', '-1'); 

        $reporteMovimiento = DB::select("SELECT * FROM inventario.sp_reporte_existencias_inventarios(" . $this->tipoPlantaId . "," . $this->puntoId . "," . $this->gestion . ",". $this->tipoMovimientoId .",". $this->tipoProgramaId .")");

        return collect($reporteMovimiento);
    }
}