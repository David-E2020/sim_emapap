<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class ReporteAcopioExport2 implements FromView
{   use Exportable;
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

    public function view():view{
/******* */
        $acopio = DB::select("SELECT * FROM siemc.f_reporte_acopio(".$this->campania_id.",".$this->programa_id.",'".$this->buscar_id."','".$this->consulta_id."')");

        $boletas= array();
        $i=0;
        foreach ($acopio as $item) {
            $boleta = array('Nro'=>$i+1);
            foreach ($item as $clave => $valor) {
                if($clave <> 'parametros') $boleta[$clave]=$valor;
            }    
            foreach (json_decode($item->parametros,true) as $param) {
                $key = $param['acp_nombre'];
                $valor=$param['cantidad'];
                $boleta[$key] =$valor;
            }
         
            array_push($boletas,$boleta); $i++;
        }


         return view('reportexcel.reporteacopioparam',['acopio' =>$boletas]);
    }
    

}
