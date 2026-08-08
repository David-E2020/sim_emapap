@inject('reporteFactura', 'App\Http\Controllers\Reporte\ReporteFacturaController')
@extends('layouts.facturaHeadComex')

@section('content')


@php
use App\Models\Util;
@endphp

<style type="text/css">
    #watermark {
        color: rgba(227, 127, 127, 0.4);
        font-size: 150pt;
        /*
  -webkit-transform: rotate(-45deg);
  -moz-transform: rotate(-45deg);
  

transform: rotate(-45deg);
*/
        position: absolute;
        width: 100%;
        height: 100%;

        z-index: 1000;
        text-align: center;
        top: 25%;
    }

    table.table-reporte {
        border-width: 1px;
        border-spacing: 0px;
        border-style: outset;
        border-color: black;
        border-collapse: collapse;
        background-color: white;
    }

    table.table-reporte th {
        border-width: 2px;
        padding: 1px;
        border-style: inset;
        border-color: gray;
        background-color: white;

    }

    table.table-reporte td {
        border-width: 2px;
        padding: 2px;
        border-style: inset;
        border-color: gray;
        background-color: white;

    }
</style>




@if($estadoFactura->code==3 )
<div id="watermark">
    <p>ANULADO</p>
</div>
@endif




<br>
<table class="w-100">
    <tr>
        <th class="text-left" style="font-size: 13px;">Fecha (Date):</th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;">{{$fechaEmision}}</th>
        <th class="text-left" style="font-size: 13px;">NIT/CI/CEX/PO/CNT:</th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;">{{$cabecera->numeroDocumento}}</th> <!-- importante-->
    </tr>

    <tr>
        <th class="text-left" style="font-size: 13px;">NOMBRE/RAZÓN SOCIAL/NAME:</th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;">{{$cabecera->nombreRazonSocial}}</th>
        <th class="text-left" style="font-size: 13px;">Cód. Cliente/CD:</th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;">{{$cabecera->codigoCliente}}</th>
    </tr>

    <tr>
        <th class="text-left" style="font-size: 13px;">INCOTERM:</th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;">{{$cabecera->incoterm}}  {{$cabecera->incotermDetalle}}</th>
        <th class="text-left" style="font-size: 13px;">Lugar Destino:</th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;"> {{$cabecera->lugarDestino}}</th>
    </tr>



    <tr>
        <th class="text-left" style="font-size: 13px;"></th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;"></th>
        <th class="text-left" style="font-size: 13px;">Puerto Destino:
(Destination Port)</th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;">{{$cabecera->puertoDestino}}</th>
    </tr>

    <tr>
        <th class="text-left uppercase" style="font-size: 13px;">Tipo de Cambio (T/C):</th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;">{{$cabecera->tipoCambio}}</th>
        <th class="text-left uppercase" style="font-size: 13px;">Dirección Comprador/Importer Address:</th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;">{{$cabecera->direccionComprador}}</th>
    </tr>

    <tr>
        <th class="text-left" style="font-size: 13px;">Moneda de la Transaccción Comercial: <br>
            (Comercial Transactión Currency)
        </th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;">{{$codigoMonedaNombre->param_nombre}}</th>
        <th class="text-left" style="font-size: 13px;"></th>
        <th class="text-left" style="font-weight: 100; font-size: 13px;"></th>
    </tr>


</table>
<br>
<span class=" text-xs uppercase">
    Total payable under terms PO./CNT. Number: {{$poNumber}}
</span>

<br> <br>
<table class="table-reporte">
    <tr>
        <th class="text-center text-xs" >NANDINA/H.S.</th>
        <th class="text-center text-xs" >CANTIDAD (Quantity)</th>
        <th class="text-center text-xs" >DESCRIPCIÓN <br> (Description)</th>
        <th class="text-center text-xs" >UNIDAD MEDIDA <br> (Unit of Measurement)</th>
        <th class="text-center text-xs" >PRECIO UNITARIO <br> (Unit Value)</th>
        <th class="text-center text-xs" >SUBTOTAL</th>
    </tr>

    @foreach($detalles as $det)
    <tr>
        <td class="text-center text-xs" >{{ $det->codigoNandina}}</td>
        <td class="text-center text-xs" >{{ Util::numberFormatView($det->cantidad) }}</td>
        <td class="text-center text-xs" >{{ $det->descripcion}}</td>
        <td class="text-center text-xs" >{{ $reporteFactura::getUnidadMedida($det->unidadMedida, $unidadesMedidas) }}</td>
        <td class="text-center text-xs" >{{Util::numberFormatView($det->precioUnitario)}}</td>
        <td class="text-center text-xs" >{{Util::numberFormatView($det->subTotal)}}</td>
    </tr>
    @endforeach

    <tr>
        <td colspan="5" class="text-xs" style=" text-align: end; font-weight: bold;">
        TOTAL DETALLE (DOLAR) (Total Detail)
        </td>
        <td class="text-xs text-center text-xs" style="  font-weight: bold;">
            {{Util::numberFormatViewV2($cabecera->montoDetalle)}}
        </td>
    </tr>

</table>


<br>
<table class="table-reporte w-50">
    <tr>
        <td class="text-xs"> <strong>PRECIO VALOR BRUTO </strong> </td>
        <td  class="w-20 text-xs"> {{Util::numberFormatViewV2($cabecera->precioValorBruto)}} </td>
    </tr>
</table>




@if( (array)($cabecera->costosGastosNacionales))
<br>
<span class="text-xs" style="font-weight: bold;">
    Desglose de Costos y Gastos Nacionales
</span> <br>
 <span class="text-xs" >(National Costs and Expenses Detail)</span>  <br>
<table class="table-reporte w-50">
    <tr>
        @foreach(json_decode($cabecera->costosGastosNacionales, true) as $key => $value)
        <td class="text-xs" >{{ $key }}</td>
        <td class="text-xs w-20 "  >{{ Util::numberFormatViewV2($value)}} </td>
        @endforeach
    </tr>

    <tr>
        <td class="text-xs"> <strong>TOTAL COSTOS Y GASTOS NACIONALES </strong> </td>
        <td  class="w-20 text-xs"> {{Util::numberFormatViewV2($cabecera->totalGastosNacionalesFob)}} </td>
    </tr>

    <tr>
        <td class="text-xs" > <strong>FOB – FRONTERA </strong> </td>
        <td class="text-xs w-20" > 
    
        {{Util::numberFormatViewV2($cabecera->montoTotalMoneda - $cabecera->totalGastosInternacionales)}} 
    
    
    </td>
    </tr>
</table>
<br>


@endif






<span class="text-xs" style="font-weight: bold; ">
    Desglose de Costos y Gastos Internacionales
</span> <br>
<span class="text-xs" >(International Costs and Expenses Detail) </span><br>
<table class="table-reporte w-50">

    @if( (array)($cabecera->costosGastosInternacionales))
    @foreach(json_decode($cabecera->costosGastosInternacionales, true) as $key => $value)
    <tr class="text-sm">
        <td class="text-xs" >{{ $key }}</td>
        <td class="text-xs w-20"  >{{ $value }}</td>
    </tr>
    @endforeach
    @endif



    <tr class="text-sm">

<td class="text-xs" > <strong>TOTAL COSTOS Y GASTOS INTERNACIONALES</strong> </td>
<td class="text-xs w-20" > {{Util::numberFormatViewV2($cabecera->totalGastosInternacionales)}} </td>
</tr>


<tr class="text-sm">

<td class="text-xs uppercase" > <strong>TOTAL {{$cabecera->incoterm}}  {{$cabecera->puertoDestino}} </strong>  </td>
<td class="text-xs w-20" > {{Util::numberFormatViewV2($cabecera->montoTotalMoneda)}}   </td>
</tr>









</table>

<br>
<table class="table-reporte w-100">
    <tr class="text-sm">

        <td class="text-xs"> <strong>TOTAL GENERAL</strong> (DÓLAR ESTADOUNIDENSE)</td>
        <td  class="text-xs w-20 "> {{Util::numberFormatViewV2($cabecera->montoTotalMoneda)}} </td>
    </tr>

    <tr class="text-sm">

        <td class="text-xs"> <strong>TOTAL GENERAL </strong> (BOLIVIANOS)</td>
        <td  class="text-xs w-20 ">{{Util::numberFormatViewV2($cabecera->montoTotal)}} </td>
    </tr>
</table>


<br>
<span class="text-xs" >
    SON: {{$reporteFactura::numtoletras($cabecera->montoTotalMoneda, 'DÓLAR ESTADOUNIDENSE')}} <br>
</span>
<span class="text-xs" >
    SON: {{$reporteFactura::numtoletras($cabecera->montoTotal, 'Bolivianos')}} <br>
</span>




<br>
<table>
    <tr>
        <td class="text-xs">
            ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY.<br>

            {{$cabecera->leyenda}} <br>
            <!-- // 1 Online, 2 Offline, 3 Masivo--->



            @if($codigoEmision==1 )
            “Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en línea”
            @endif

            @if($codigoEmision==2 )
            “Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido fuera de línea, verifique su envío con su proveedor o en la página web www.impuestos.gob.bo”
            @endif



        </td>
        <td>
            {!!QrCode::size(180)->generate($urlQR) !!}
        </td>
    </tr>
</table>





@endsection