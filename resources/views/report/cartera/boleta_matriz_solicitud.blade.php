@extends('layouts.print')

@section('content')
<br>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white ">Fecha Reporte:</td>
        <td class="text-xs uppercase text-center"></td>
        <td class="text-center bg-grey-darker text-xs text-white">Gestion Consulta:</td>
        <td class="text-xs uppercase text-center">{{ $solicitud_id }}</td>

    </tr>
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white">
            <td class="py text-center "style="font-size: 8px;" >
                Nro.
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Organizacion
            </td>
            <td class="py text-center "style="font-size: 8px;">
                Beneficiario
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Banco
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Nro. Cuenta
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Monto (Bs)
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Tipo Solicitud
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Nro Cheque
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Fecha Cheque
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Deposito Bs
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Fecha Deposito
            </td>
            <td class="px-15 py text-center "style="font-size: 8px;">
                Observacion
            </td>
        </tr>
    </thead>
    <tbody>
        @php
        $nro = 1;
        $total = 0;
        @endphp
        @foreach( $solicitudes as $value)
        <tr class="text-sm">
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px; width: 5px">{{ $nro++ }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$value->organizacion }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$value->beneficiario }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$value->banco }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$value->nro_cuenta }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$value->monto_bs }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$value->tipo_solicitud }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$value->nro_cheque }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$value->fecha_cheque }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$value->deposito_bs }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$value->fecha_deposito }}</td>
            <td class="text-center uppercase font-bold px-1 py-1" style="font-size: 8px;">{{$value->observacion }}</td>
        </tr>
        @php
        $total += floatval($value->monto_bs);

        @endphp
        @endforeach
        /*
        <tr class="font-medium text-white text-sm"></tr>
        <tr>
            <td colspan="5" class="text-center bg-grey-darker text-xs text-white">
                Total
            </td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$total}}</td>
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
<table class="saltopagina">
    <tr>
        <td class="text-right text-xxs"></td>
        <td class="text-left text-xxs"></td>
        <td class="text-right text-xxs"></td>
        <td class="text-right text-xxs"><b>Fecha Impresion:</b></td>
    </tr>
    <tr>
        <td class="text-right text-xxs"></td>
        <td class="text-left text-xxs"></td>
        <td class="text-right text-xxs"></td>
        <td class="text-right text-xxs"><b>Usuario:</b> {{ Auth::user()->name }}</td>
    </tr>
</table>

@endsection
<style>
    .saltopagina{page-break-after:always;}
</style>
