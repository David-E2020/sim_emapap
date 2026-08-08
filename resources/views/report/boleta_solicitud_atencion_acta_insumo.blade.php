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
        <td class="text-xs">Procedencia Almacen:</td>
        <td class="text-xs uppercase"> {{$datosVector['almacen']}}</td>

        <td class="text-xs">Lugar Procedencia (Ciudad, Provincia, Municipio):</td>
        <td class="text-xs uppercase">{{$username}}</td>
    </tr>
    <tr>
        <td class="text-xs font-bold">Nombre del Funcionario Responsable::</td>
        <td class="text-xs uppercase">{{$datosVector['nombre_encargado']}}</td>

        <td class="text-xs font-bold">Cargo:</td>
        <td class="text-xs uppercase">{{$datosVector['cargo_encargado']}}</td>
    </tr>
    <tr>
        <td class="text-xs font-bold">Cedula de Identidad del Responsable::</td>
        <td class="text-xs uppercase">{{$datosVector['ci_responsable']}}</td>

        <td class="text-xs font-bold">Nombre del Destinatario:</td>
        <td class="text-xs uppercase">{{$datosVector['nombre_recepcion']}} </td>
    </tr>
    <tr>
        <td class="text-xs font-bold">Cedula de Identidad del Destinatario:</td>
        <td class="text-xs uppercase">{{$datosVector['ci_recepcion']}}</td>

        <td class="text-xs font-bold">Gerencia o Unidad Destino:</td>
        <td class="text-xs uppercase">{{$datosVector['cargo_destino']}} </td>
    </tr>  

    <tr>
        <td class="text-xs font-bold">Numero de Solicitud:</td>
        <td class="text-xs uppercase">{{$datosVector['num_solicitud']}}</td>

        <td class="text-xs font-bold"> </td>
        <td class="text-xs uppercase"></td>
    </tr>
    <!-- 
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
    </tr>  -->
</table>
<table>
<tr> <td>En la Localidad {{$datosVector['localidad']}}  a Horas {{$datosVector['horas']}} en fecha {{$datosVector['fecha_atencion']}}, mediante el  presente documento se deja estipulada la entrega de los siguientes materiales:</td></tr>
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
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->cantidad_entregada}}</td>
            
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
            SELLO Y FIRMA RECEPCION: 
        </td>
        <td class="text-xs" style="vertical-align: bottom;" rowspan="2">
            SELLO Y FIRMA ENTREGA: 
        </td>
    </tr>
    <tr>
    </tr>
     
     <tr>
        <td class="text-xs" style="text-align: center">
            NOMBRE:{{$datosVector['nombre_recepcion']}} 
        </td>
        <td class="text-xs">
            NOMBRE: {{$datosVector['nombre_encargado']}}
        </td>
    </tr>
    <tr>
        <td class="text-center text-xs font-bold">
            RECIBIDO POR
        </td>
        <td class="text-center text-xs font-bold">
            ENTREGADO POR : RESPONSABLE ALMACEN
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