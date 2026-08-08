@extends('layouts.printKardex')

@section('content')




<style type="text/css">
table.table-reg {
    width: 100%;
    border-width: 1px;
    border-spacing: 1px;
   border-style: inset;
    border-color: black;
    border-collapse: collapse;
   


}

table.table-reg th {
    border-width: 1px;
    padding: 3px;
    border-style: inset;
    border-color: black;
  
    font-size: 8px;

}

table.table-reg td {
    border-width: 1px;
    padding: 3px;
    border-style: inset;
    border-color: black;

    font-size: 8px;

}

table.table-reg .cel-total {
    background-color: rgb(240, 240, 240);
}
</style>
<br> 



<strong>PERIODO FISCAL:  </strong> {{$fechas}}
<br>
<br>


<table style="width:100%; text-align: left;" >
    <tr>
        <td class="font-bold" >NOMBRE O RAZON SOCIAL:</td>
        <td >EMPRESA BOLIVIANA DE ALIMENTOS Y DERIVADOS - EBA</td>
        <td class="font-bold">NIT:</td>
        <td >368406024</td>
    </tr>

    <tr>
        <td  class="font-bold">NUMERO DE SUCURSAL: </td>
        <td> {{$puntoVenta->codigo}} {{$puntoVenta->nombre}}</td>
        <td  class="font-bold">DIRECCIÓN:</td>
        <td> {{$puntoVenta->direccion}} </td>
    </tr>
</table> 
<br>


 @php
        $nro = 1;
        
    @endphp

<table class="table-reg">
                            <thead>
                                <tr>
                                    <th class="text-center">N°</th>
                                    <th>FECHA DE LA FACTURA</th>
                                    <th class="text-center">N° DE LA FACTURA</th>
                                    <th class="text-center">CÓDIGO DE AUTORIZACIÓN</th>
                                    <th>NIT / CI CLIENTE</th>
                                    <th>COMPLEMENTO</th>
                                    <th>NOMBRE O RAZÓN SOCIAL</th>
                                    <th class="text-center">IMPORTE TOTAL DE LA VENTA</th>
                                    <th class="text-center">IMPORTE ICE</th>
                                    <th class="text-center">IMPORTE IEHD</th>
                                    <th class="text-center">IMPORTE IPJ </th>
                                    <th class="text-center">TASAS</th>
                                    <th class="text-center">OTROS NO SUJETOS AL IVA</th>
                                    <th class="text-center">EXPORTACIONES Y OPERACIONES EXENTAS</th>
                                    <th class="text-center">VENTAS GRAVADAS A TASA CERO</th>
                                    <th class="text-center">SUBTOTAL</th>
                                    <th class="text-center">DESCUENTOS, BONIFICACIONES Y REBAJAS SUJETAS AL IVA</th>
                                    <th class="text-center">IMPORTE GIFT CARD</th>
                                    <th class="text-center">IMPORTE BASE PARA DÉBITO FISCAL </th>
                                    <th class="text-center">DÉBITO FISCAL</th>
                                    <th class="text-center">ESTADO</th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($facturas as $item)
                                <tr>
                                    <td class="text-center">{{$nro++}}</td>
                                    <td>{{ $item->fecha_factura_sin_d }}</td>
                                    <td class="text-center">{{ $item->id }}</td>
                                    <td class="text-center" style="width: 10px;text-size-adjust: auto;">{{ $item->cuf }}
                                    </td>
                                    <td>{{ $item->cliente->nro_identificacion }}</td>
                                    <td>{{ $item->cliente->complemento }}</td>
                                    <td>{{ $item->cliente->cliente }}</td>
                                    <td class="text-center">
                                        
                                    


                                    
                                    {{ ($item->estado_factura->name=='V')?$item->monto_total:0 }}
                                
                                
                                </td>
                                    <td class="text-center">0.00</td>
                                    <td class="text-center">0.00</td>
                                    <td class="text-center">0.00</td>
                                    <td class="text-center">0.00</td>
                                    <td class="text-center">0.00</td>
                                    <td class="text-center">{{ ($item->estado_factura->name=='V')?$item->monto_total:0 }}</td>
                                    <td class="text-center">0.00</td>
                                    <td class="text-center">0.00</td>
                                    <td class="text-center">0.00</td>
                                    <td class="text-center">0.00</td>
                                    <td class="text-center">0.00</td>
                                    <td class="text-center">0.00</td> 
                                    <td class="text-center">{{ $item->estado_factura->name ?? '' }}</td>
                                    
                                </tr>
                                @endforeach

                                <tr>
                                    <th colspan="7" class="text-center cel-total">TOTAL</th>
                                    <th class="text-center cel-total">{{ $sumTotal }}</th>
                                    <th class="text-center cel-total">0.00</th>
                                    <th class="text-center cel-total">0.00</th>
                                    <th class="text-center cel-total">0.00</th>
                                    <th class="text-center cel-total">0.00</th>
                                    <th class="text-center cel-total">0.00</th>
                                    <th class="text-center cel-total">{{ $sumTotal }}</th>
                                    <th class="text-center cel-total">0.00</th>
                                    <th class="text-center cel-total">0.00</th>
                                    <th class="text-center cel-total">0.00</th>
                                    <th class="text-center cel-total">0.00</th>
                                    <th class="text-center cel-total">0.00</th>
                                    <th class="text-center cel-total" >0.00</th>
                                </tr>

                            </tbody>
                        </table>




<br>


                                    <br> <br>

@endsection