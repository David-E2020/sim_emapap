@extends('layouts.print_inventario')

@section('content')
<style type="text/css">
    table.color {
        border-width: 1px;
        border-spacing: 0px;
        border-color: #E8E8E8;
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
        border-width: 1px;
        padding: 1px;
        border-style: inset;
        border-color: #E8E8E8;
        background:#E8E8E8;
        font-size:10px;

    }

    table.table-reporte td {
        border-width: 1px;
        padding: 2px;
        border-style: inset;
        border-color: #E8E8E8;
        font-size:9px;
    }

</style>
<table class="table-reporte align-top">
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Planta Origen:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->planta_origen->nombre ?? '' }}</td>

        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Punto Origen:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->origen->nombre ?? '' }}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Responsable:</td>
        <td class="text-center" width='20%'>{{$datos['nombre_responsable_origen'] ?? ''}}</td>

        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Contrato:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->contratos->nro_proceso_contrato}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Solicitante:</td>
        <td class="text-center" width='20%'>{{$solicitante->name }}</td>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Fecha Solicitud:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->created_at }}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Camapaña:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->lotes->campania->camp_nombre ?? '' }}</td>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Programa:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->lotes->programa->prog_nombre ?? '' }}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Lote:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->lotes->nombre ?? ''}}</td>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Fecha Inicio:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->fecha_documento ?? '' }}</td>
    </tr>
    <!---->
</table>
<table class="table-reporte align-top">
    <thead>
        <tr>
            <td class="font-bold text-center text-xs" colspan="6">ESTIMACIÓN DE PRODUCTOS</td>
        </tr>
        <tr class="text-sm">
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">Nro</td>
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">Codigo Articulo</td>
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">Articulo</td>
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">Unidad</td>
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">Tipo</td>
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">Cantidad</td>
        </tr>
    </thead>
    <tbody>
        <?php
$total = 0;
?>
        @foreach($solicitud_registro->solicitud_detalles as $value)
        <tr class="text-sm">
            <td class="text-center text-xxs uppercase px-5 py-3">{{ $count++ }}</td>
            <td class="text-center text-xxs uppercase px-5 py-3">{{ $value->articulo->codigo_alterno}} </td>
            <td class="text-center text-xxs uppercase px-5 py-3">{{ $value->articulo->nombre_producto}} </td>
            <td class="text-center text-xxs uppercase px-5 py-3">{{ $value->articulo->unidad_medida->nombre}} </td>
            <td class="text-center text-xxs uppercase px-5 py-3">{{ $value->tipo}} </td>
            <td class="text-center text-xxs uppercase px-5 py-3">{{ $value->cantidad}} </td>

        </tr>
        <?php
$total += $value->cantidad;
?>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="text-sm">
            <td colspan="5" class="text-center text-xxs uppercase font-bold px-4 py-3 bg-grey-darker text-white"><strong> TOTAL:</strong> </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3"> <B>{{ number_format((float)$total, 6, '.', '')}}</B> </td>
        </tr>
    </tfoot>


    @php
    $nro = 1;
    $total_presentacion = 0;
    $total_medida = 0;
    @endphp




</table>

<table class="table-reporte" >
    <tr style="height: 80px;">
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
<table class="saltopagina">
    <tr>
        <td class="text-left text-xxs">
            Fecha Impresión: {{ $date }} <br>
            Usuario:{{ Auth::user()->name }}
        </td>
        <td class="text-left text-xxs"></td>
        <td class="text-right text-xxs"></td>
        <td class="text-right text-xxs">

        </td>
    </tr>
</table>


@endsection