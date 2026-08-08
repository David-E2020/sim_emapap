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
        <td class="text-center bg-grey-darker text-xs text-white">Planta Origen:</td>
        <td class="text-xs uppercase text-center">{{$solicitud_registro->planta_origen->nombre ?? '' }}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Punto Origen:</td>
        <td class="text-xs uppercase text-center">{{$solicitud_registro->origen->nombre ?? '' }}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Responsable:</td>
        <td class="text-xs uppercase text-center">{{$datos['nombre_responsable_origen'] ?? ''}}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Contrato:</td>
        <td class="text-xs uppercase text-center">{{$solicitud_registro->contratos->nro_proceso_contrato}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Solicitante:</td>
        <td class="text-xs uppercase text-center">{{$solicitante->name }}</td>
        <td class="text-center bg-grey-darker text-xs text-white">Fecha Solicitud:</td>
        <td class="text-xs uppercase text-center">{{$solicitud_registro->created_at }}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Camapaña:</td>
        <td class="text-xs uppercase text-center">{{$solicitud_registro->lotes->campania->camp_nombre ?? '' }}</td>
        <td class="text-center bg-grey-darker text-xs text-white">Programa:</td>
        <td class="text-xs uppercase text-center">{{$solicitud_registro->lotes->programa->prog_nombre ?? '' }}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Lote:</td>
        <td class="text-xs uppercase text-center">{{$solicitud_registro->lotes->nombre ?? ''}}</td>
        <td class="text-center bg-grey-darker text-xs text-white">Fecha Inicio:</td>
        <td class="text-xs uppercase text-center">{{$solicitud_registro->fecha_documento ?? '' }}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Producto</td>
        <td class="text-xs uppercase text-center">
            {{$solicitud_registro->solicitud_detalles->first()->articulo->nombre_producto ?? ''}}
        </td>
        <td class="text-center bg-grey-darker text-xs text-white">Cantidad</td>
        <!-- redondear a dos decimales -->
        <td class="text-xs uppercase text-center">
            {{ number_format((float)$solicitud_registro->solicitud_detalles->first()->cantidad, 2, '.', '')}}
        </td>
    </tr>
    <!---->
</table>

<br>
<!----------->
<br>

<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="px-15 py text-center text-xxs text-white">
            <td class="font-bold text-center text-xs" colspan="6">ESTIMACIÓN DE PRODUCTOS</td>
        </tr>
        <tr class="px-15 py text-center text-xxs text-white">
            <td class="font-bold text-center text-xs">Nro</td>
            <td class="font-bold text-center text-xs">Codigo Articulo</td>
            <td class="font-bold text-center text-xs">Articulo</td>
            <td class="font-bold text-center text-xs">Unidad</td>
            <td class="font-bold text-center text-xs">Tipo</td>
            <td class="font-bold text-center text-xs">Cantidad</td>
        </tr>
    </thead>
    <tbody>
        <?php
        $total = 0;
        ?>
        @foreach($solicitud_registro->solicitud_detalles as $value)
        @if($loop->first)
        @continue
        @endif
        <tr class="text-sm">
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $count++ }}</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->codigo_alterno}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->nombre_producto}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->unidad_medida->nombre}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->tipo}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ number_format((float)$value->cantidad, 2, '.', '')}}
            </td>
            
        </tr>
        <?php
        $total += $value->cantidad;
        ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="text-sm">
            <td colspan="5" class="text-center text-xxs uppercase font-bold px-4 py-3 bg-grey-darker text-white"><strong> TOTAL:</strong> </td>
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
