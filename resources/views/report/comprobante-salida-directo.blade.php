@extends('layouts.print')

@section('content')
<style type="text/css">
table.table-reporte {
    border-width: 1px;
    border-spacing: 0px;
    border-style: outset;
    border-color: black;
    border-collapse: collapse;
    background-color: white;
}
table.table-reporte th {
    border-width: 1px;
    padding: 1px;
    border-style: inset;
    border-color: gray;
    background-color: white;
    
}
table.table-reporte td {
    border-width: 1px;
    padding: 2px;
    border-style: inset;
    border-color: gray;
    background-color: white;
    
}
</style>
<br>
<table class="table-reporte" >
    <tr> 
        <td class="text-center font-bold" rowspan="2">#</td>
        <td  rowspan="2" class="text-xs font-bold" >DETALLE</td>
        <td  rowspan="2" class="font-bold text-center text-xs" >UNIDAD MEDIDA</td>
        <td class="text-center text-xs font-bold" rowspan="2">NRO. <br> LOTE</td>
        <td class="text-center text-xs font-bold" colspan="2">PRESENTACIÓN</td>
        <td class="text-center text-xs font-bold" colspan="2">TOTAL</td>
    </tr>

    <tr>
        <td class="text-center text-xs font-bold" > Caja / Paq </td>
        <td class="text-center text-xs font-bold"> Medida</td>
        <td class="text-center text-xs font-bold"> Caja / Paq </td>
        <td class="text-center text-xs font-bold"> Medida</td>
    </tr>

    @php
        $nro = 1;
        $total_presentacion = 0;
        $total_medida = 0;
    @endphp
    @foreach($movimiento->detalles as $det)
        @php
        $total_presentacion = $total_presentacion + $det->mvd_cantidad_present; 
        $total_medida = $total_medida + $det->mvd_cantidad_medida;
        @endphp
    <tr >

        <td class=" text-center text-xs uppercase font-bold px-1 py-1">{{ $nro++ }}</td>
        <td class=" text-xs uppercase ">
        {{ $det->producto->prod_desc}} 
        {{ $det->producto->tipo->param_nombre}} 
        {{ $det->producto->calidad->param_nombre}}

        </td>

        <td class="text-xs text-center" >
        {{ $det->producto->unidadMedida->param_nombre}}
        </td>

        <td class=" text-center text-xs uppercase  ">{{ $det->mvd_nro_lote }}</td>
        <td class=" text-center text-xs uppercase ">{{ $det->mvd_presentacion}}</td>
        <td class=" text-center text-xs uppercase ">{{ number_format($det->mvd_presentacion_medida,2,'.',',')}}  </td>
        <td class=" text-center text-xs uppercase ">{{ number_format($det->mvd_cantidad_present,2,'.',',')}}</td>
        <td class=" text-center text-xs uppercase ">{{ number_format($det->mvd_cantidad_medida,2,'.',',')}}  </td>
    </tr>
    @endforeach
    <tr class="text-sm">
        <td colspan="6" class="text-center font-bold" ><strong> TOTAL:</strong> </td>
        <td class="text-center font-bold" > <strong>{{ number_format($total_presentacion,2,'.',',')}}</strong> </td>
        <td class="text-center font-bold" > <strong>{{ number_format($total_medida,2,'.',',')}}</strong> </td>
    </tr>

    <tr>
        <td colspan="8">
        <strong class="text-xs" >OBSERVACIONES: </strong>  <span class="text-xs" >{{$movimiento->mv_observaciones}}</span> 
        </td>
    </tr>




</table>


<br> <br>

<table class="table-reporte">
    <tr>
        <td class="text-xxs">
            ACEPTO HABER RECIBIDO ESTA CARTA EN PERFECTAS CONDICIONES
        </td>
        <td rowspan="5" style="vertical-align: bottom; text-align: center;">
            
        </td>
    </tr>
    <tr style="height: 150px;">
        <td class="text-xs" style="vertical-align: bottom;">
            FIRMA:
        </td>
    </tr>
    <tr>
        <td class="text-xs">
            NOMBRE:
        </td>
    </tr>
    <tr>
        <td class="text-xs">
            CARGO:
        </td>
    </tr>
    <tr>
        <td class="text-xs">
            C.I.:
        </td>
    </tr>
    <tr>
        <td class="text-center text-xs font-bold">
            RECIBI CONFORME
        </td>
        <td class="text-center text-xs font-bold">
            ENTREGUE CONFORME
        </td>

    </tr>
</table>




@endsection