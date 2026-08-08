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
        <td class="text-center bg-grey-darker text-xs text-white">Planta:</td>
        <td class="text-xs uppercase">{{$planta_nombre}}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Responsable:</td>
        <td class="text-xs uppercase">{{$reposonsable_nombre}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Fecha Consulta:</td>
        <td class="text-xs uppercase">{{$date }}</td>
    </tr>
    <!---->
</table>

<br>

<!----------->

<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="px-15 py text-center text-xxs">
            <td class="font-bold text-center text-xs">Nro</td>
            <td class="font-bold text-center text-xs">Punto Destino</td>
            <td class="font-bold text-center text-xs">Nro Correlativo</td>
            <td class="font-bold text-center text-xs">Codigo Acopio</td>
            <td class="font-bold text-center text-xs">Codigo Articulo</td>
            <td class="font-bold text-center text-xs">Articulo</td>
            <td class="font-bold text-center text-xs">Cantidad</td>
            
        </tr>
    </thead>
    <tbody>
        <?php
        $total = 0;
        ?>
        @foreach($movimientos as $value)
            <tr class="text-sm">
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $count++ }}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->destino->nombre}}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->mv_nro_correlativo}}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->acopio->aco_codigo}}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->movimiento_detalle_acopio->articulo->id}}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->movimiento_detalle_acopio->articulo->nombre_producto}}</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->movimiento_detalle_acopio->mvd_cantidad}}</td>
                
            </tr>
            <?php
            $total += $value->movimiento_detalle_acopio->mvd_cantidad;
            ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="text-sm">
            <td colspan="6" class="text-center text-xxs uppercase font-bold px-4 py-3 bg-grey-darker text-white"><strong> TOTAL:</strong> </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3"> <B>{{ number_format((float)$total, 6, '.', '')}}</B> </td>
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
        <td class="text-center text-xs font-bold">
            VERIFICADO - PERSONAL EMAPA
        </td>
        <td class="text-center text-xs font-bold">
            APROBADO - PERSONAL EMAPA
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