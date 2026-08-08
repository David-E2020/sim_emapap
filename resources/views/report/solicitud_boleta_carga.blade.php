@extends('layouts.printCarta')

@section('content')
<style type="text/css">

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
<span><h6>DATOS DE LA SOLICITUD: </h6></span>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Procedencia:</td>
        <td class="text-xs uppercase text-center">{{$solicitud->origen->nombre }}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Destino:</td>
        <td class="text-xs uppercase text-center">{{$solicitud->destino->nombre }}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Resp. Entrega:</td>
        <td class="text-xs uppercase text-center">anonimo</td>

        <td class="text-center bg-grey-darker text-xs text-white">Resp. Recepcion: </td>
        <td class="text-xs uppercase text-center">anonimo2</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Telf/Cel:</td>
        <td class="text-xs uppercase text-center">111111111</td>

        <td class="text-center bg-grey-darker text-xs text-white">Telf/Cel: </td>
        <td class="text-xs uppercase text-center">67842111</td>
    </tr>
</table>
<span><h6>DATOS DEL TRANSPORTE: </h6></span>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Transportadora:</td>
        <td class="text-xs uppercase text-center">Emapa</td>

        <td class="text-center bg-grey-darker text-xs text-white">Conductor:</td>
        <td class="text-xs uppercase text-center">{{$solicitud->conductor->nombre_conductor ?? ''}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Tipo Vehiculo:</td>
        <td class="text-xs uppercase text-center">{{$solicitud->vehiculo->type ?? ''}}</td>

        <td class="text-center bg-grey-darker text-xs text-white">C.I.:</td>
        <td class="text-xs uppercase text-center">{{$solicitud->conductor->numero_identificacion ?? ''}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Placa:</td>
        <td class="text-xs uppercase text-center">-{{$solicitud->vehiculo->placa ?? ''}}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Celular:</td>
        <td class="text-xs uppercase text-center">{{$solicitud->conductor->telefono ?? ''}}</td>
    </tr>
</table>
<br>

<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="px-15 py text-center text-xxs">
            <td class="font-bold text-center text-xs" colspan="5">DETALLE DE PRODUCTOS</td>
        </tr>
        <tr class="px-5 py text-center text-xxs">
            <td class="font-bold text-center text-xs">Nro</td>
            <td class="font-bold text-center text-xxs">Cod.Catalogo</td>
            <td class="font-bold text-center text-xs">Producto</td>
            <td class="font-bold text-center text-xs">Unidad</td>
            <td class="font-bold text-center text-xs">Cantidad</td>

        </tr>
    </thead>
    <tbody>
        <?php
$total = 0;
$count = 1;
?>
        @foreach($solicitud->solicitud_detalles as $key => $value)

        <tr class="text-sm">
            <td class="text-center text-xs">{{ $count++ }}</td>
            <td class="text-center text-xs">{{$value->articulo->identificador_mapeo}}</td>
            <td class="text-center text-xs">{{$value->articulo->nombre_producto}}</td>
            <td class="text-center text-xs">{{$value->articulo->unidad_medida->nombre}}</td>
            <td class="text-center text-xs">{{$value->cantidad}}</td>
        </tr>
        <?php
$total += $value->cantidad;
?>
        @endforeach
    </tbody>
    @php
    $total_presentacion = 0;
    @endphp


</table>
<?php
$data = json_decode($solicitud->data, true);
if ($data && isset($data[4])) {
	$observaciones = $data[4];
} else {

}
?>

<br>
<br><br>
<table class="table-reporte" >
    <tr style="height: 150px;">
        <td class="text-xs" style="vertical-align: bottom; text-align: center" rowspan="2">
            SELLO Y FIRMA:
        </td>
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
        <td class="text-center text-xs font-bold" style="text-align: center">
            AUTORIZADO - PERSONAL EMAPA
        </td>
    </tr>
</table>

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