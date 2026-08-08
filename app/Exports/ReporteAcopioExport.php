<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteAcopioExport implements FromCollection, WithHeadings
{
    private $campania_id;
    private $programa_id;
    private $buscar_id;
    private $consulta_id;

    public function __construct($campania, $programa, $buscar, $consulta)
    {
        $this->campania_id = $campania;
        $this->programa_id= $programa;
        $this->buscar_id = $buscar;
        $this->consulta_id = $consulta;
    }

    public function headings(): array
    {
        return [
            ['REPORTE GENERAL DEL ACOPIO'], [
                'REGION' , 
                'PROGRAMA',
                'CAMPAÑA', 
                'ORGANIZACION' , 
                'NOMBRES',
                'PRIMER_APE',
                'SEGUNDO_APE', 
                'CI', 
                'COMPLE', 
                'EXPEDIDO', 
                'DEPARTAMENTO', 
                'PROVINCIA', 
                'MUNICIPIO' , 
                'COMUNIDAD' , 
                'TIPO_APOYO' , 
                'TIPO_PRODUCTOR' , 
                'NRO_BOLETA', 
                'CODIGO' , 
                'FECHA_ACOPIO' , 
                'PESO_BRUTO', 
                'PESO_TARA' , 
                'PESO_NETO',
                'GRADO', 
                'PESO_LIQUIDO_KG',
                'PESO_LIQUIDO_ACOPIO' , 
                'PESO_LIQUIDO_VARIABLE' , 
                'ESTADO',
                'CENTROREGISTRO' , 
                'CENTROACOPIO', 
                'CALIDAD',
            ]
        ];
    }
 

    public function collection()
    {
        ini_set('memory_limit', '-1');
        $data = DB::select("SELECT * FROM siemc.f_reporte_acopio(".$this->campania_id.",".$this->programa_id.",'".$this->buscar_id."','".$this->consulta_id."')");
                return collect($data);
    }


}
