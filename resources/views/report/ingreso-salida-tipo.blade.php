@extends('layouts.printKardex')

@section('content')

<style type="text/css">

.border-th{
    border:2px solid black;
    padding-left: 5px;
    padding-right: 5px;
}

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
    -moz-border-radius: ;
}
table.table-reporte td {
    border-width: 1px;
    padding: 6px;
    border-style: inset;
    border-color: gray;
    background-color: white;
    -moz-border-radius: ;
}
</style>
<br> 

<table>
    <tr>
        <td> 
            <strong>FECHA:  </strong> {{$fechas}}
        </td>
        <td>
          
        </td>
    </tr>

</table>




<br>
<table class="table-reporte" >
                                        <thead>
                                            <tr>
                                                <td class="text-center font-weight-bold" rowspan="2">#</td>
                                                <td rowspan="2" class="font-weight-bold">CÓDIGO</td>
                                                <td rowspan="2" class="font-weight-bold">FECHA</td>
                                                <td rowspan="2" class="font-weight-bold">TIPO</td>

                                                <td rowspan="2" class="font-weight-bold">CÓDIGO PRODUCTO</td>
                                                <td rowspan="2" class="font-weight-bold">DESCRIPCIÓN</td>
                                                <td rowspan="2" class="font-weight-bold">UNIDAD MEDIDA</td>
                                                <td class="text-center font-weight-bold" rowspan="2">NRO. <br> LOTE</td>

                                                <td class="text-center font-weight-bold" colspan="2">PRESENTACIÓN</td>
                                                <td class="text-center font-weight-bold" colspan="2">TOTAL</td>
                                            </tr>

                                            <tr>
                                                <td class="text-center font-weight-bold"> Caja / Paq </td>
                                                <td class="text-center font-weight-bold"> Medida</td>
                                                <td class="text-center font-weight-bold"> Caja / Paq </td>
                                                <td class="text-center font-weight-bold"> Medida</td>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @php
                                                $nro = 1; 
                                            @endphp

                                            @foreach($movimientoDetalles as $item)
                                            <tr >
                                                <td class="text-center">
                                                    {{$nro++}}
                                                </td>
                                                <td>{{ $item->movimiento->codigo }}</td>
                                                <td>{{ $item->fecha }}</td>
                                                <td>{{ $item->movimiento->mv_tipo }}</td>

                                                <td>{{ $item->producto->prod_codigo??"" }}</td>
                                                <td>
                                                    {{ $item->producto->prod_desc??"" }}/{{ $item->producto->prod_nombre??"" }} 
                                                    {{ $item->producto->tipo->param_nombre??""}}
                                                    {{ $item->producto->calidad->param_nombre }}
                                                </td>
                                                <td class="text-center">
                                                {{$item->producto->unidadMedida->param_nombre??'nn' }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $item->mvd_nro_lote }}

                                                </td>

                                                <td>{{ $item->mvd_presentacion }}</td>
                                                <td>{{ number_format((float)$item->mvd_presentacion_medida, 2, '.', '') }}</td>
                                                <td>{{ number_format((float)$item->mvd_cantidad_present, 2, '.', '') }}</td>
                                                <td>{{ number_format((float)$item->mvd_cantidad_medida, 2, '.', '')  }}</td>

                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <br> <br>

@endsection