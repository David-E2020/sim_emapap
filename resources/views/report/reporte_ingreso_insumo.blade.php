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
    .texto-vertical-2 {
    writing-mode: vertical-lr;
    transform: rotate(180deg);
    } 
</style>

<br>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Fecha Consulta:</td>
        <td class="text-xs uppercase">{{$date}}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Hora:</td>
        <td class="text-xs uppercase">
            {{ date('H:i:s') }}
        </td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Planta:</td>
        <td class="text-xs uppercase">{{$planta_nombre }}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Responsable:</td>
        <td class="text-xs uppercase">
            {{ Auth::user()->name }}
        </td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Punto Ingreso:</td>
        <td class="text-xs uppercase">{{$planta_nombre }}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Punto Salida:</td>
        <td class="text-xs uppercase">
            {{$planta_nombre }}
        </td>
    </tr>
    <!---->
</table>

<br>
<span><h6>DATOS DEL TRANSPORTE: </h6></span>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Tipo Vehiculo:</td>
        <td class="text-xs uppercase text-center">NISSAN</td>

        <td class="text-center bg-grey-darker text-xs text-white">Placa:</td>
        <td class="text-xs uppercase text-center">WEQW333</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Conductor:</td>
        <td class="text-xs uppercase text-center">Pedro Quispe</td>

        <td class="text-center bg-grey-darker text-xs text-white">C.I.:</td>
        <td class="text-xs uppercase text-center">68852158</td>
    </tr>
</table>

<br>

<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="px-15 py text-center text-xxs">
            <td class="font-bold text-center text-xs" colspan="5" style="color: white; padding:10px;">DETALLE DE PRODUCTOS DESPACHADOS</td>
        </tr>
        <tr>
            <td class="text-center bg-grey-darker text-xs text-white">Correlativo Salida:</td>
            <td class="text-xs uppercase text-center" colspan="4">{{$code_salida}} </td>
        </tr>
        <tr class="px-15 py text-center text-xxs">
            <td class="font-bold text-center text-xs" style="color: white;">Nro</td>
            <td class="font-bold text-center text-xs" style="color: white;">Codigo Articulo</td>
            <td class="font-bold text-center text-xs" style="color: white;">Articulo</td>
            <td class="font-bold text-center text-xs" style="color: white;">Unidad</td>
            <td class="font-bold text-center text-xs" style="color: white;">Cantidad</td>
            
            
        </tr>
    </thead>
    <tbody>
        <?php
        $total = 0;
        ?>
        @foreach($movimientos_salida->movimiento_detalle as $value)
        <tr class="text-sm">
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $count++ }}</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->codigo_alterno}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->nombre_producto}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->unidad_medida->nombre ?? ''}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->mvd_cantidad}} </td>
            
            
        </tr>
        <?php
        $total += $value->mvd_cantidad;
        ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="text-sm">
            <td colspan="4" class="text-center text-xxs uppercase font-bold px-4 py-3 bg-grey-darker text-white"><strong> TOTAL:</strong> </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3"> <B>{{ number_format((float)$total, 2, '.', '')}}</B> </td>
        </tr>
    </tfoot>


    @php
    $nro = 1;
    $total_presentacion = 0;
    $total_medida = 0;
    @endphp

    


</table>


<br> <br>
<br><br>
<table class="table-reporte" >
    <tr style="height: 150px;">
        <td class="text-xs" style="vertical-align: bottom; text-align: center" rowspan="2">
            SELLO Y FIRMA: 
        </td>
        <td class="text-xs" style="vertical-align: bottom; text-align: center" rowspan="2">
            SELLO Y FIRMA: 
        </td>
    </tr>
    <tr>
    </tr>
    <tr>
        <td class="text-xs" style="text-align: center">
            NOMBRE: 
        </td>
        <td class="text-xs" style="text-align: center">
            NOMBRE: 
        </td>
    </tr>
    <tr>
        <td class="text-center text-xs font-bold" style="text-align: center">
            ELABORADO - PERSONAL EMAPA
        </td>
        <td class="text-center text-xs font-bold" style="text-align: center">
            AUTORIZADO - PERSONAL EMAPA
        </td>
    </tr>
</table>

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<table class="saltopagina">
<tr>
    <td class="text-right text-xxs"></td>
    <td class="text-left text-xxs"></td>
    <td class="text-right text-xxs"></td>
    <td class="text-right text-xxs"><b>Fecha Impresion:</b> {{ $date }}</td>
</tr>
<tr>
    <td class="text-right text-xxs"></td>
    <td class="text-left text-xxs"></td>
    <td class="text-right text-xxs"></td>
    <td class="text-right text-xxs"><b>Usuario:</b> {{ Auth::user()->name }}</td>
</tr>
</table>



@endsection