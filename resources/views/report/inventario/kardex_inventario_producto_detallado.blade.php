@extends('layouts.print')

@section('content')
<br>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white ">Producto:</td>
        <td class="text-xs uppercase text-center" colspan="3"><b>{{$product->nombre_producto?? ''}}</b></td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Lote:</td>
        <td class="text-xs uppercase text-center">{{$lote->nombre?? ''}}</td>
        <td class="text-center bg-grey-darker text-xs text-white">Fecha Consulta:</td>
        <td class="text-xs uppercase text-center">{{$dateImp ?? ''}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Programa:</td>
        <td class="text-xs uppercase text-center">{{$programa->prog_nombre ?? ''}}</td>
        <td class="text-center bg-grey-darker text-xs text-white">Campania:</td>
        <td class="text-xs uppercase text-center">{{$campania->camp_nombre ?? ''}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Fecha Inicio:</td>
        <td class="text-xs uppercase text-center">{{$fecha_ini ?? ''}}</td>
        <td class="text-center bg-grey-darker text-xs text-white">Fecha Fin:</td>
        <td class="text-xs uppercase text-center">{{$fecha_fin ?? ''}}</td>
    </tr>
</table>
<br>
<table class="table-info align-top no-padding no-margins border">
    <td class="text-center bg-grey-darker text-xs text-white ">Saldo a fecha: {{$dateImp ?? ''}}</td>
    <td class="text-xs uppercase text-center" colspan="3"><b>{{$saldo_inicial_acumulado ?? ''}}</b></td>
</table>
<br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white">
            <td class="py text-center "style="font-size: 8px;" >
                Nro.
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Fecha
            </td>
            <td class="py text-center "style="font-size: 8px;">
                Nro. Boleta
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Tipo
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Movimiento
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Origen
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Destino
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Productor
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Asociacion
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Transportadora
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Nombre
                <br>
                Conductor
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Placa
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Entrada
                <br>
                Cant
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Salida
                <br>
                Cant
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Saldo
                <br>
                Cant
            </td>
        </tr>
    </thead>
    <tbody>
        @php
        $nro = 1;
        $total_entrada = 0;
        $total_salida = 0;
        $total_saldo = 0;
        @endphp
        @foreach( $movimientos as $insumo)
        <tr class="text-sm">
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px; width: 5px">{{ $nro++ }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_fecha ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_correlativo ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_tipo ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_tipo_detalle ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_nombre_origen ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_nombre_destino ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_productor ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_asociacion ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_distribuidora ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_conductor ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_placa ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_entrada ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_salida ?? '-'}}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$insumo->o_saldo ?? '-'}}</td>
        </tr>
        @php
        $total_entrada += floatval($insumo->o_entrada);
        $total_salida += floatval($insumo->o_salida);
        $total_saldo = floatval($insumo->o_saldo);
        @endphp
        @endforeach
        <tr class="font-medium text-white text-sm"></tr>
        <tr>
            <td colspan="12" class="text-center bg-grey-darker text-xs text-white">
                Total
            </td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$total_entrada ?? '-'}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$total_salida ?? '-'}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$total_saldo ?? '-'}}</td>
        </tr>
    </tbody>
</table>
</br>
</br>
</br>
</br>
</br>
</br>
<table>
    <tr>
        <td class="text-center text-xxs">Revisado por firma: ............................................</td>
        <td class="text-center text-xxs">Verificado por firma: ............................................</td>
    </tr>
    {{-- <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr> --}}
    <tr>
        <td class="text-center text-xxs">Nombre: ......................................................</td>
        <td class="text-center text-xxs">Nombre: ......................................................</td>
    </tr>
</table>
<br>
<table>
    <tr>
        <td class="text-right text-xxs"></td>
        <td class="text-left text-xxs"></td>
        <td class="text-right text-xxs"></td>
        <td class="text-right text-xxs"><b>Fecha Impresion:</b> {{ $dateImp }}</td>
    </tr>
    <tr>
        <td class="text-right text-xxs"></td>
        <td class="text-left text-xxs"></td>
        <td class="text-right text-xxs"></td>
        <td class="text-right text-xxs"><b>Usuario:</b> {{ Auth::user()->name }}</td>
    </tr>
</table>

@endsection
