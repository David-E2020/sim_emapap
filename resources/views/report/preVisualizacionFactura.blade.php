@extends('layouts.printComex')

@section('content')

<br>
<table class="w-100">
    <tr>
        <th class="text-left">Fecha (Date):</th>
        <th class="text-left" style="font-weight: 100;" >{{$factura->fecha_factura}}</th>
        <th class="text-left">NIT/CI/CEX:</th>
        <th class="text-left" style="font-weight: 100;" >{{$comex->nit}}</th>
    </tr>

    <tr>
        <th class="text-left">Nombre/Razón Social:</th>
        <th class="text-left" style="font-weight: 100;" >{{$comex->razon_social}}</th>
        <th class="text-left">Cod. Cliente:</th>
        <th class="text-left" style="font-weight: 100;" >{{$factura->cliente->id}}</th>
    </tr>

    <tr>
        <th class="text-left">INCOTERM:</th>
        <th class="text-left" style="font-weight: 100;" >{{$factura->incoterm->incoterm}}</th>
        <th class="text-left">Lugar Destino:</th>
        <th class="text-left" style="font-weight: 100;" >{{$fleteDetalleExterno->pais}}</th>
    </tr>

    <tr>
        <th class="text-left">Tipo de Cambio:</th>
        <th class="text-left" style="font-weight: 100;" >6.96</th>
        <th class="text-left">Dirección Comprador:</th>
        <th class="text-left" style="font-weight: 100;" >{{$factura->cliente->direccion}}</th>
    </tr>

    <tr>
        <th class="text-left">Moneda de la Transaccción Comercial:</th>
        <th class="text-left" style="font-weight: 100;" >{{$factura->moneda->param_nombre}}</th>
        <th class="text-left">Puerto Destino:</th>
        <th class="text-left" style="font-weight: 100;" >{{$fleteDetalleExterno->lugar}}</th>
    </tr>


</table>

<br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <th class="px-15 py text-center text-xs ">Nandina</th>
            <th class="px-15 py text-center text-xs ">Calidad (Quantity)</th>
            <th class="px-15 py text-center text-xs ">DESCRIPCIÓN <br> (Description)</th>
            <th class="px-15 py text-center text-xs ">UNIDAD MEDIDA <br>  (Unit of Measurement)</th>
            <th class="px-15 py text-center text-xs ">PRECIO UNITARIO <br>  (Unit Value)</th>
            <th class="px-15 py text-center text-xs ">SUBTOTAL</th>
        </tr>

    </thead>
    <tbody>

        @foreach($factura->detalles as $det)

        <tr class="text-sm">
            <td class="text-center text-xs uppercase font-bold px-1 py-1">0801220000</td>
            <td class="text-center text-xs uppercase font-bold px-1 py-1">{{ $det->producto->prod_calidad??''}}</td>
            <td class="text-center text-xs uppercase font-bold px-1 py-1">{{ $det->cantidad}} {{ $det->detalle }} {{ $det->producto->prod_desc??''}} {{ $det->producto->prod_calidad??''}} {{ $det->producto->prod_tipo??''}}</td>
            <td class="text-center text-xs uppercase font-bold px-1 py-1">{{ $det->unidadMedida->param_nombre??'' }}</td>
            <td class="text-center text-xs uppercase font-bold px-1 py-1">{{ $det->precio_initario??''}}</td>
            <td class="text-center text-xs uppercase font-bold px-1 py-1">{{ $det->total}}</td>
        </tr>
        @endforeach
        <tr class="text-sm">
            <td colspan="5" class="text-xs">
                TOTAL DETALLE (DÓLAR ESTADOUNIDENSE) (Total Detail)
            </td>
            <td class="text-xs">
                {{$montoDetalle}}
            </td>
        </tr>

        <tr class="text-sm">
            <td colspan="5" class="text-xs" >
                INCOTERM y alcance del Total detalle de la transaccion (INCOTERM and scope of the Total Transaction Details) 
            </td>
            <td class="text-xs">
                {{$factura->incoterm->detalle}}
            </td>
        </tr>


    </tbody>
</table>

<br><br>
Desglose de Costos y Gastos Nacionales <br>
(National Costs and Expenses Detail) <br>
<table  class="table-info align-top no-padding no-margins border">
<tr class="text-sm">
    <td>TRANSPORTE NACIONAL</td>
    <td> {{$factura->flete_interno}} </td>
    
</tr>

<tr class="text-sm">
    <td> <strong>SUBTOTAL FOB</strong> </td>
    <td> {{$montos->totalGastosNacionalesFob}} </td>
    
</tr>
</table>
<br>
Desglose de Costos y Gastos Internacionales <br>
(International Costs and Expenses Detail) <br>

<table class="table-info align-top no-padding no-margins border w-100">
<tr class="text-sm">
    <td colspan="2">BONIFICACIONES Y TRANSPORTE INTERNACIONAL</td>
    <td>{{$factura->flete_externo}}</td>
    
</tr>

<tr class="text-sm">
    <td colspan="2"><strong>TOTAL</strong> </td>
    <td>{{$factura->flete_externo}}</td>
    
</tr>


<tr class="text-sm">
    <td class="w-40" ></td>
    <td>SUBTOTAL (DÓLAR ESTADOUNIDENSE)</td>
    <td> {{$montos->montoTotalMoneda}} </td>
</tr>

<tr class="text-sm">
    <td class="w-40"></td>
    <td>DESCUENTO (DÓLAR ESTADOUNIDENSE)</td>
    <td> 0.00 </td>
</tr>

<tr class="text-sm">
    <td class="w-40"></td>
    <td> <strong>TOTAL GENERAL</strong>  (DÓLAR ESTADOUNIDENSE)</td>
    <td> {{$montos->montoTotal}} </td>
</tr>

<tr class="text-sm">
    <td class="w-40"></td>
    <td> <strong>TOTAL GENERAL </strong> (BOLIVIANOS)</td>
    <td> {{$montos->montoTotalMoneda}} </td>
</tr>


</table>
<br>
Son: {{$montoLiteralDolares}} <br>

Son: {{$montoLiteralMoneda}} <br>

<br>
<table>
    <tr>
        <td>
        ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY <br>

        {{$leyenda}}

        </td>
        <td>
        {!!QrCode::size(150)->generate($urlQR) !!}
        </td>
    </tr>
</table>





@endsection