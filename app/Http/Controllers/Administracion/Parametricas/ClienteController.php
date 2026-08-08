<?php

namespace App\Http\Controllers\Administracion\Parametricas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Factura;
use Exception;

class ClienteController extends Controller
{


    public function searchFacturasEbaCompras(Request $request)
    {
        $input = $request->all();

        $identificacion = $input['identificacion'];
        $tipoDocumento = isset($input['tipo_documento'])?$input['tipo_documento']:null ;
        $complemento = isset($input['complemento'])?$input['complemento']:null;


        $facturas=[];
        $cliente=null;
        

        try {

            if ($complemento != null) {
                $whereCliente=[['nro_identificacion', trim($identificacion)], ['complemento',strtoupper($complemento) ]];
            } else {
                $whereCliente=[['nro_identificacion', trim($identificacion)], ['complemento',null ]];
            }


/*
        $cliente= Cliente::with(['tipoDocumentoIdentidad'=>function($query){
                     $query->select('id','param_codigo','param_nombre');
                   }])->select('id',"nombre", "paterno", "materno","celular", "correo", "cliente", "nro_identificacion", "complemento", "direccion","param_tipo_documento_identidad_id")->where($whereCliente)->first();
        
*/


         $cliente= Cliente::with(['tipoDocumentoIdentidad'=>function($query) use ($tipoDocumento){
                             $query->where('param_codigo', $tipoDocumento)->select('id','param_codigo','param_nombre');
                           }])->select('id',"nombre", "paterno", "materno","celular", "correo", "cliente", "nro_identificacion", "complemento", "direccion","param_tipo_documento_identidad_id")->where($whereCliente)->first();

        if ($cliente!=null) {
              $clienteResp=$cliente;
           
            if ($clienteResp->toArray()['tipo_documento_identidad']==null) {
                    $cliente = null;
                 }
        }

         



          
        } catch (Exception $e) {
            $cliente=null;
        }



        if ($cliente!=null) {

            $whereFactura = [['cliente_id', $cliente->id], ['cuf', '<>' ,null],['codigo_estado_sin','<>','902']];
            $withFactura = ['motivoAnulacion:id,param_valor,param_codigo,param_nombre'];

            $facturas = Factura::with($withFactura)->where($whereFactura)->orderBy('created_at', 'desc')->get()->makeHidden(['param_moneda_id','param_calidad_id','param_destino_final_id','param_puerto_salida_id','incoterm_id','direccion_planta','param_tipo_metodo_pago_id','deleted_at','usr_eliminado','usr_modificado','flete_interno','flete_externo','flete_externo_naviero','updated_at','cufd','detalles'])->append('documento_factura');


            $nroFacturas=   count($facturas);

            $cod_ = ($nroFacturas==0)?201:200;
            $resp = array('cliente'=>$cliente,'nro_facturas' => $nroFacturas, 'facturas' => $facturas, 'code' =>  $cod_ );
            return $resp;
            
        }else{
            $compl=  ($complemento != null) ? $complemento : '' ;
            $result= array(
                'code' => 406,
                'message'=> "No existe cliente asociado al : ".$identificacion." ".$compl." "
             );
            return response()->json($result,200);
        }


      

    }

    public function searchNit($nit)
    {
        //$query->where('last_name', 'LIKE', '%' . $request->input('last_name') . '%');
        //$query->where('nit', 'LIKE', '%' . $nit . '%');
        return Cliente::where('nro_identificacion', 'LIKE', '%' . $nit . '%')->limit(8)->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Cliente::with(['tipoDocumentoIdentidad:id,param_codigo,param_nombre'])->withCount('facturas')->orderBy('id', 'desc')->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {



        $input = $request->all();

        $nroIdentificacion = $input['nro_identificacion'];

      $nroClientes=  Cliente::where('nro_identificacion', $nroIdentificacion)->count();

        if ($nroClientes!=0) {
            $message = 'El NIT/CI/CEX '.$nroIdentificacion.' ya se encuentra registrado';
           $result= array('message'=>$message );
           return response()->json($result,406);
        } else {
            $cliente = Cliente::create($input);
             return  $cliente;
        }




return $clientes;
dd($clientes);
dd("dfsd");

        
    }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $cliente = Cliente::find($id);
        $cliente->update($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cliente = Cliente::find($id);
        $cliente->delete();

        return "delete";
    }
}
