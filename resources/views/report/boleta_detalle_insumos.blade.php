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

    .espacio_texto {
        padding: 3px 0px 3px 0px;
    }
</style>

<br>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white espacio_texto">Planta:</td>
        <td class="text-xs uppercase text-center">
        141

            
        </td>
        <td class="text-center bg-grey-darker text-xs text-white">Fecha Consulta:</td>
        <td class="text-xs uppercase text-center">
         {{$date}}
        </td>
    </tr>
</table>
<br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="px-15 py text-center text-xxs">
            <td class="font-bold text-center text-xs text-white" colspan="4">Nuevo</td>
        </tr>
        <tr class="px-15 py text-center text-xxs">
            <th class="font-bold text-center text-xs">Nro</th>
            <th class="font-bold text-center text-xs">Artículo</th>
            <th class="font-bold text-center text-xs">Cantidad</th>
        </tr>
    </thead>
    <tbody>
        @foreach($boleta as $item)
        <tr>
            <td class="text-center text-xxs">{{$item->id }}</td>
            <td class="text-center text-xxs"> @if ($item->articulos)
                {{ $item->articulos->nombre_producto }}
                @else
                Sin artículo asociado
                @endif</td>
            <td class="text-center text-xxs">{{ $item->cantidad}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="text-sm">
            <td colspan="2" class="text-center text-xxs uppercase font-bold px-4 py-3 bg-grey-darker text-white"><strong> TOTAL:</strong> </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3"> <b></B> </td>
        </tr>
    </tfoot>
</table>
</table>
<table>
    <tr style="height: 150px;">
        <td class="text-xs" style="vertical-align: bottom; text-align: center" rowspan="2">
            ................................
            <br>
            SELLO Y FIRMA:
        </td>
        <td class="text-xs" style="vertical-align: bottom; text-align: center" rowspan="2">
            ...............................
            <br>
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
@endsection