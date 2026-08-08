<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Administracion\Parametricas\DatosRegistroController;
use App\Models\Movimiento;
use App\Models\Documento;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class MovimientoController extends Controller
{
    public function saldosAlmacen(Request $request)
    {

        $input = $request->all();
        $from = date($input['fechaInicio']);
        $to = date($input['fechaFin']);


        $fromDatetime=  $from.' 00:00:00';
        $toDatetime=  $to.' 23:59:59';



        $resp=array();

        $queryIngreso=  Movimiento::with(['detalles'=>function($query){
            $query->with(['producto'=>function($query){
                    $query->with(['tipo:id,param_codigo,param_nombre','calidad:id,param_codigo,param_nombre','unidadMedida:id,param_nombre']);
                  }]);
                }]);


        $querySalidas=  Movimiento::with(['detalles'=>function($query){
            $query->with(['producto'=>function($query){
                    $query->with(['tipo:id,param_codigo,param_nombre','calidad:id,param_codigo,param_nombre','unidadMedida:id,param_nombre']);
                  }]);
                }]);
/*
        if ($from == $to ) {
            $movimientoIngresos= $queryIngreso->where('mv_tipo','INGRESO')->whereDate('created_at', $from)->get();
            $movimientoSalidas=$querySalidas->where('mv_tipo','SALIDA')->whereDate('created_at', $from)->get();
        }else{
            $movimientoIngresos= $queryIngreso->where('mv_tipo','INGRESO')->whereBetween('created_at', [$from, $to])->get();
            $movimientoSalidas=$querySalidas->where('mv_tipo','SALIDA')->whereBetween('created_at', [$from, $to])->get();
        }
*/
        $movimientoIngresos= $queryIngreso->where('mv_tipo','INGRESO')->get();//->whereBetween('created_at', [$fromDatetime, $toDatetime])->get();
        $movimientoSalidas=$querySalidas->where('mv_tipo','SALIDA')->whereBetween('created_at', [$fromDatetime, $toDatetime])->get();






        $datosRegistroController = new DatosRegistroController();
        $tipos_= $datosRegistroController->findTabla('tipo');
        $calidad_= $datosRegistroController->findTabla('calidad');


        #salidas

        $movimientoSalidaEmisionFactura = $movimientoSalidas->filter(function ($value, $key) {
            return $value->mv_tipo_det == "EMISION_FACTURA"; // DIRECTO EMISION_FACTURA TRASPASO
        });

        $movimientoSalidaDirecto = $movimientoSalidas->filter(function ($value, $key) {
            return $value->mv_tipo_det == "DIRECTO"; // DIRECTO EMISION_FACTURA TRASPASO
        });

        $movimientoSalidaTraspaso = $movimientoSalidas->filter(function ($value, $key) {
            return $value->mv_tipo_det == "TRASPASO"; // DIRECTO EMISION_FACTURA TRASPASO
        });









        $sumaDetalleAlmacen = $this->sumaDetalleAlmacen($movimientoIngresos, $tipos_, $calidad_);
        $sumaDetalleAlmacen->nombre="INGRESOS A ALMACEN";
        $sumaDetalleAlmacen->classColor = "blue lighten-5";

        $sumaDetalleEmisionFactura = $this->sumaDetalleAlmacen($movimientoSalidaEmisionFactura, $tipos_, $calidad_);
        $sumaDetalleEmisionFactura->nombre="EMISOR DE FACTURA";
        $sumaDetalleEmisionFactura->classColor = "";

        $sumaDetalleIngresoDirecto = $this->sumaDetalleAlmacen($movimientoSalidaDirecto, $tipos_, $calidad_);
        $sumaDetalleIngresoDirecto->nombre="SALIDA DIRECTA";
        $sumaDetalleIngresoDirecto->classColor = "";

        $sumaDetalleTraspaso = $this->sumaDetalleAlmacen($movimientoSalidaTraspaso, $tipos_, $calidad_);
        $sumaDetalleTraspaso->nombre="TRASPASO";
        $sumaDetalleTraspaso->classColor = "";

        $sumaTotalSalida = $this->sumaDetalleAlmacen($movimientoSalidas, $tipos_, $calidad_);
        $sumaTotalSalida->nombre=" TOTAL SALIDA";
        $sumaTotalSalida->classColor = "green lighten-5";

        $saltoTotal = $this->SaldoTotalV2($sumaDetalleAlmacen,  $sumaTotalSalida,$tipos_, $calidad_);
        $saltoTotal->nombre = "TOTAL SALDO";
        $saltoTotal->classColor = "indigo lighten-5";

 
        
        $resp[]= $sumaDetalleAlmacen;
        $resp[]= $sumaDetalleEmisionFactura;
        $resp[]= $sumaDetalleIngresoDirecto;
        $resp[]= $sumaDetalleTraspaso;
        $resp[]= $sumaTotalSalida;
        $resp[]= $saltoTotal;


      return $resp;
    }

    private function SaldoTotalV2($totalIngresos,$totalSalidas, $tipos, $calidades)
    {

        
        $resp=(object) array();
        $sumTotal=0;
        foreach ($calidades as $key => $value) {
            

            $keyCalidad = (string) Str::of($value->param_nombre)->slug('_');
            $respTipos =  array();
            
            foreach ($tipos as $keyT => $valueT) {
                
                $keyTipo =  (string) Str::of($valueT->param_nombre)->slug('_');
                $total_= $totalIngresos->{$keyCalidad }->{$keyTipo}  - $totalSalidas->{$keyCalidad }->{$keyTipo};
                $sumTotal = $sumTotal + $total_;
                $respTipos[$keyTipo]= $total_;
                    
                 }
                 $resp->{strtolower($value->param_nombre)}= (object) $respTipos;
        }
        $resp->suma_total = $sumTotal;
        return $resp;

    }

        private function SaldoTotal($totalIngresos,$totalSalidas)
    {

        
        $resp= $totalIngresos;


        foreach ($resp as $key => $value) {
 
            if (is_object($value)) {
                 foreach ($value as $keyT => $valueT) {
                    $salida = $totalSalidas->{$key}->{$keyT};
                    $resp->{$key}->{$keyT}=$valueT - $salida ;

                    }
                
            } else {
                
                if (is_numeric($value)) {
                    $resp->{$key} = $value - $totalSalidas->{$key};
                } else {
                    $resp->{$key} = "TOTAL SALDO 1";
                }
            }
        }


        return $resp;

    }


    private function sumaDetalleAlmacen($movimientoIngresos,$tipos, $calidades)
    {
        $resp=(object) array();
        $sumTotal=0;
        foreach ($calidades as $key => $value) {
            //$resp->calidad= $value->param_nombre;
            $respTipos =  array();
            
            foreach ($tipos as $keyT => $valueT) {
                $sumaTotal =  $this->sumaTotalTipoCalidad($movimientoIngresos, $valueT->id, $value->id);
                $sumTotal= $sumTotal + $sumaTotal;
                $name_=  (string) Str::of($valueT->param_nombre)->slug('_');

                    $respTipos[$name_]= $sumaTotal;
                    //$respTipos[strtolower($valueT->param_nombre)]= $sumaTotal;
                 }
                 $resp->{strtolower($value->param_nombre)}= (object) $respTipos;
        }
        $resp->suma_total =$sumTotal;
        return $resp;
    }



    private function sumaTotalTipoCalidad($movimientoIngresos, $tipoId=27,$calidadId=54)
    {
        $suma=0;

        foreach ($movimientoIngresos as $key => $value) {
           $detalles= $value->detalles;
            foreach ($detalles as $keyd => $valued) {
                if ($valued->producto->param_tipo_id == $tipoId && $valued->producto->param_calidad_id == $calidadId) {
                   $suma = $suma + $valued->mvd_cantidad_present;
                }
            }
        }

        return $suma;
    }


    public function uploadFiles(Request $request)
    {

$firl="ok";
            foreach($request->file('files') as $file)
            {

                $extension = $file->extension();
                $originalName =$file->getClientOriginalName();
                $mimeType = $file->getClientMimeType();
                $fileName = $file->hashName();// time().rand(1,99).'.'.$file->extension(); 

                $size = $file->getSize();
               
                $file->move(public_path().'/files/', $originalName);

                 $firl = $firl.' - '. $size;

                $documento = new Documento();
                $documento->tipo = "INGRESO";

                $documento->name = $fileName;
                $documento->original_name = $originalName;
                $documento->extension = $extension;
                $documento->size = $size;
                $documento->movimiento_id = 1;

                $documento->save();
/*
                $name = time().rand(1,100).'.'.$extension;
                $file->move(public_path('files'), $name);  
                $firl = $firl.' - '. $name;
                $files[] = $name;  
                */
            }
         


        return $firl;

    }
    

}
