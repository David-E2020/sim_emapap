@extends('layouts.printKardex')

@section('content')

<style type="text/css">

table.table-reporte {
    border-width: 1px;
    border-spacing: 0px;
    border-style: outset;
    border-color: black;
    border-collapse: collapse;
    
}
table.table-reporte th {
    border-width: 1px;
    padding: 1px;
    border-style: inset;
    border-color: gray;
  
    -moz-border-radius: ;
}
table.table-reporte td {
    border-width: 1px;
    padding: 6px;
    border-style: inset;
    border-color: gray;
   
    -moz-border-radius: ;
}

.blue{
    background-color: #E3F2FD !important;
}

.green{
background-color: #E8F5E9 !important;
}

.indigo{
background-color: #e8eaf6 !important;
}


</style>
<br> 
<strong>FECHA REPORTE: </strong> {{$fechas}}
<br>

<br>
<table class="table-reporte" >
    <thead>
        <tr>
            <th  rowspan="3"></th>
            <th colspan="14" class="text-center border-th">EXPRESADO EN CAJAS DE 44 LIBRAS</th>
            <th rowspan="3">TOTAL EN CAJAS DE 44 LIBRAS</th>
        </tr>
        <tr>
            <th colspan="7" class="text-center border-th">ALMENDRA CONVENCIONAL</th>
            <th colspan="7" class="text-center border-th">ALMENDRA ORGANICO</th>
        </tr>
        <tr>
            <th class="text-center border-th">MEDIUM</th>
            <th class="border-th" >MIDGET</th>
            <th class="border-th">LARGE</th>
            <th class="border-th" >CHIPPED</th>
            <th class="border-th" >BROKEN</th>
            <th class="border-th" >BROKEN D</th>
            <th class="border-th" >TINY</th>

            <th class="border-th" >MEDIUM</th>
            <th class="border-th" >MIDGET</th>
            <th class="border-th" >LARGE</th>
            <th class="border-th" >CHIPPED</th>
            <th class="border-th" >BROKEN</th>
            <th class="border-th" >BROKEN D</th>
            <th class="border-th" >TINY</th>
        </tr>
    </thead>
    <tbody>
        @foreach($kardexAlmacen as $item)
        <tr class="{{$item->classColor}} border-th">
            <td class="border-th"> <strong style="font-size: 12px;">{{ $item->nombre }}</strong> </td>

            <td class="text-center border-th">{{ $item->conventional->medium }}</td>
            <td class="text-center border-th">{{ $item->conventional->midget }}</td>
            <td class="text-center border-th">{{ $item->conventional->large }}</td>
            <td class="text-center border-th">{{ $item->conventional->chipped }}</td>
            <td class="text-center border-th">{{ $item->conventional->broken }}</td>
            <td class="text-center border-th">{{ $item->conventional->broken_d }}</td>
            <td class="text-center border-th">{{ $item->conventional->tinny }}</td>
            <td class="text-center border-th">{{ $item->organic->medium }}</td>
            <td class="text-center border-th">{{ $item->organic->midget }}</td>
            <td class="text-center border-th">{{ $item->organic->large }}</td>
            <td class="text-center border-th">{{ $item->organic->chipped }}</td>
            <td class="text-center border-th">{{ $item->organic->broken }}</td>
            <td class="text-center border-th">{{ $item->organic->broken_d }}</td>
            <td class="text-center border-th">{{ $item->organic->tinny }}</td>

            <td class="text-center border-th">{{ $item->suma_total }}</td>
        </tr>
        @endforeach


    </tbody>
</table>

@endsection