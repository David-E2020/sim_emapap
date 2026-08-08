@extends('layouts.printKardex')

@section('content')

<style type="text/css">



.blue{
    background-color: #E3F2FD;
}

.green{
background-color: #E8F5E9;
}

.indigo{
background-color: #e8eaf6;
}

.font-weight-bold{
    font-weight: bold;
}

</style>
<br> <br>


<style type="text/css">
table#table-reg {
    width: 100%;
    border-width: 1px;
    border-spacing: 1px;
    border-style: outset;
    border-color: black;
    border-collapse: collapse;
    


}

table#table-reg th {
    border-width: thin;
    padding: 3px;
    border-style: inset;
    border-color: black;
    
    font-size: 11px;

}

table#table-reg td {
    border-width: thin;
    padding: 3px;
    border-style: inset;
    border-color: black;
  
    font-size: 10px;

}
table#table-reg .cel-total{
    background-color: rgb(240, 240, 240);
}
</style>
<br> 



<strong>FECHA:  </strong> {{$fechas}}
<br>
<br>
<table id="table-reg">
    <thead>
        <tr>
            <th colspan="7"></th>
            <th colspan="7" class="text-center">CONVENCIONAL</th>
            <th colspan="7" class="text-center">ORGANICO</th>
        </tr>
        <tr>
            <td class="text-center">#</td>
            <td>CÓDIGO</td>
            <td>FECHA</td>
            <td class="text-center">NRO FACTURA</td>
            <td>DESTINO</td>
            <td class="text-center">NRO LOTE</td>
            <td>CALIDAD</td>

            <th class="text-center">MEDIUM</th>
            <th class="text-center">MIDGET</th>
            <th class="text-center">LARGE</th>
            <th class="text-center">CHIPPED</th>
            <th class="text-center">BROKEN</th>
            <th class="text-center">BROKEN D</th>
            <th class="text-center">TINY</th>

            <th class="text-center">MEDIUM</th>
            <th class="text-center">MIDGET</th>
            <th class="text-center">LARGE</th>
            <th class="text-center">CHIPPED</th>
            <th class="text-center">BROKEN</th>
            <th class="text-center">BROKEN D</th>
            <th class="text-center">TINY</th>
        </tr>

    </thead>
    <tbody>
        @php
            $nro = 1;
        @endphp

        @foreach($exportacionesLote->data as $item)
        <tr v-for="(item, index) in exportaciones" :key="index">
            <td class="text-center">{{ $nro  + 1 }}</td>
            <td>{{ $item->codigo }}</td>
            <td>{{ $item->fecha }}</td>
            <td class="text-center">{{ $item->nro_factura }}</td>
            <td>{{ $item->destino }}</td>
            <td class="text-center">{{ $item->nro_lote }}</td>
            <td>{{ $item->calidad }}</td>
            <td class="text-center">{{ $item->conventional->medium }}</td>
            <td class="text-center">{{ $item->conventional->midget }}</td>
            <td class="text-center">{{ $item->conventional->large }}</td>
            <td class="text-center">{{ $item->conventional->chipped }}</td>
            <td class="text-center">{{ $item->conventional->broken }}</td>
            <td class="text-center">{{ $item->conventional->broken_d }}</td>
            <td class="text-center">{{ $item->conventional->tinny }}</td>
            <td class="text-center">{{ $item->organic->medium }}</td>
            <td class="text-center">{{ $item->organic->midget }}</td>
            <td class="text-center">{{ $item->organic->large }}</td>
            <td class="text-center">{{ $item->organic->chipped }}</td>
            <td class="text-center">{{ $item->organic->broken }}</td>
            <td class="text-center">{{ $item->organic->broken_d }}</td>
            <td class="text-center">{{ $item->organic->tinny }}</td>
        </tr>
         @endforeach


<tr>
    <td class="text-center cel-total" colspan="7"> Total </td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->conventional->medium }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->conventional->midget }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->conventional->large }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->conventional->chipped }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->conventional->broken }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->conventional->broken_d }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->conventional->tinny }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->organic->medium }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->organic->midget }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->organic->large }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->organic->chipped }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->organic->broken }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->organic->broken_d }}</td>
    <td class="text-center cel-total">{{ $exportacionesLote->total_covencional_organico->organic->tinny }}</td>
</tr>

        <tr>
            <td class="text-center" colspan="7"> <strong>TOTAL SALIDAS A EXPORTACIONES EN CAJAS</strong> </td>
            <td class="text-center cel-total" colspan="14"> <strong>{{$exportacionesLote->cantidad_present_total}}</strong> </td>
        </tr>

        <tr>
            <td class="text-center" colspan="7"> <strong>TOTAL SALIDAS A EXPORTACIONES EN LIBRAS</strong> </td>
            <td class="text-center cel-total" colspan="14"> <strong>{{$exportacionesLote->cantidad_medida_total}}</strong> </td>
        </tr>

    </tbody>
</table>





<br>


                                    <br> <br>

@endsection