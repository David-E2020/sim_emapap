<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteProductorExport implements FromCollection, WithHeadings
{
    private $campania_id;
    private $programa_id;
    private $consulta_id;

    public function __construct($campania, $programa,$consulta)
    {
        $this->campania_id = $campania;
        $this->programa_id= $programa;
        $this->consulta_id = $consulta;
    }

    public function headings(): array
    {
        return [
            ['REPORTE GENERAL DEL PRODUCTOR'], [
                'REGION' , 
                'PROGRAMA',
                'CAMPAÑA', 
                'SEGUIMIENTO' , 
                'NOMBRES',
                'PRIMER_APE',
                'SEGUNDO_APE', 
                'CI', 
                'EXPEDIDO', 
                'DEPARTAMENTO', 
                'PROVINCIA', 
                'MUNICIPIO' , 
                'COMUNIDAD' , 
                'ORGANIZACION' , 
                'TIPO_APOYO' , 
                'TIPO_PRODUCTOR' , 
                'SUPERFICIE', 
                'RENDIMIENTO' , 
                'CUPO' , 
                'SALDO_CUPO', 
                'ESTADO_ACOPIO' , 
                'PESO_ACOPIO',
                'DEUDA_CARTERA', 
                'OBSERVACIONES',
            ]
        ];
    }
 

    public function collection()
    {
        ini_set('memory_limit', '-1');
        $data = DB::select("SELECT * FROM siemc.f_reporte_productor(".$this->campania_id.",".$this->programa_id.",'".$this->consulta_id."')");
                return collect($data);
    }


}
