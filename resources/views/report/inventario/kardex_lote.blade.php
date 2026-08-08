@extends('layouts.print')

@section('content')
<br>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white ">Producto</td>
        <td colspan="3" class="text-xs uppercase">{{$product->nombre_producto}}</td>
    </tr>
</table>
<br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td class="px-15 py text-center  text-xxs">
                Fecha
            </td>
            <td class="px-15 py text-center  text-xxs">
                Tipo
            </td>
            <td class="px-15 py text-center  text-xxs">
                Movimiento
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Programa
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Campania
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Lote
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Entrada
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Salida
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Saldo
            </td>
        </tr>
        <tr class="font-medium text-white text-sm">
            <td colspan="7" class="px-15 py text-center text-xxs">

            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cant.
            </td>
        </tr>
    </thead>
    <tbody>
    @php
        $nro = 1;
    @endphp
    @foreach( $movimientos as  $insumo)
        <tr class="text-sm">
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $nro++ }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$insumo->o_fecha}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$insumo->o_tipo}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$insumo->o_tipo_detalle}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$insumo->o_programa}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$insumo->o_campania}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$insumo->o_lote}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$insumo->o_entrada}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$insumo->o_salida}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$insumo->o_saldo}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td class="px-15 py text-center  text-xxs">
                Resumen de Saldos
            </td>
        </tr>
        <tr class="font-medium text-white text-sm">
            <td class="px-15 py text-center text-xxs ">

            </td>
            <td class="px-15 py text-center  text-xxs">
                Total
            </td>
        </tr>
    </thead>
    <tbody>
    @php
        $nro = 1;
    @endphp

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

