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
<table>
    <tr>
        <td class="text-xs">ALMACEN:</td>
        <td class="text-xs uppercase"></td>

        <td class="text-xs">NOMBRE DEL SOLICITANTE:</td>
        <td class="text-xs uppercase">{{$username}}</td>
    </tr>
    <tr>
        <td class="text-xs font-bold">CARGO:</td>
        <td class="text-xs uppercase"></td>

        <td class="text-xs font-bold">GERENCIA:</td>
        <td class="text-xs uppercase"></td>
    </tr>
    <tr>
        <td class="text-xs font-bold">UNIDAD:</td>
        <td class="text-xs uppercase"></td>

        <td class="text-xs font-bold"></td>
        <td class="text-xs uppercase"></td>
    </tr>
    <!-- <tr>
        <td class="text-xs font-bold">PROVEEDOR:</td>
        <td class="text-xs uppercase">EMPRESA XXX</td>

        <td class="text-xs font-bold">PLACA:</td>
        <td class="text-xs uppercase">4441PIL</td>
    </tr> -->

    <!---->
<!-- 
    <tr>
        <td class="text-xs font-bold">ZAFRA:</td>
        <td class="text-xs uppercase"></td>

        <td class="text-xs font-bold">NRO. CFO:</td>
        <td class="text-xs uppercase"></td>
    </tr>

    <tr>
        <td class="text-xs"></td>
        <td class="text-xs uppercase"></td>

        <td class="text-xs font-bold ">LICENCIA:</td>
        <td class="text-xs uppercase"></td>
    </tr>

    <tr>
        <td class="text-xs"></td>
        <td class="text-xs uppercase"></td>

        <td class="text-xs font-bold">CELULAR:</td>
        <td class="text-xs uppercase"></td>
    </tr> -->

</table>

<br>

<!----------->

<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="px-15 py text-center text-xxs">
            <td class="font-bold text-center text-xs">Nro</td>
            <td class="font-bold text-center text-xs">Codigo Producto</td>
            <td class="font-bold text-center text-xs">Materia Prima e Insumo</td>
            <td class="font-bold text-center text-xs">Unidad de Medida</td>
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
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->codigo_alterno}}</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->nombre_producto}}</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->unidad_medida->abreviatura_global}}</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->cantidad_solicitada}}</td>
            
        </tr>
        <?php
        $total += $value->cantidad_solicitada;
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
        <td class="text-xs" style="vertical-align: bottom;" rowspan="2">
            SELLO Y FIRMA: 
        </td>
    </tr>
    <tr>
    </tr>
    <tr>
        <td class="text-xs" style="text-align: center">
            NOMBRE: 
        </td>
        <td class="text-xs">
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