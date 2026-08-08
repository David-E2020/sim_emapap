@extends('layouts.print')

@section('content')
<br>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white ">Producto</td>
        
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

</table>
</br>
    <h1 class="px-15 py text-center text-xxs">Resumen de Saldos</h1>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td class="px-15 py text-center  text-xxs">
                Total
            </td>
        </tr>
    </thead>

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
<div class="footer">
    <img src="data:image/png;base64,{{ $qrCode->toBase64() }}" alt="Código QR" style="position: absolute; bottom: 20px; right: 20px;">
</div>
<table class="saltopagina">
<tr>
    <td class="text-right text-xxs"></td>
    <td class="text-left text-xxs"></td>
    <td class="text-right text-xxs"></td>
    
</tr>
<tr>
    <td class="text-right text-xxs"></td>
    <td class="text-left text-xxs"></td>
    <td class="text-right text-xxs"></td>
    
</tr>
</table>


@endsection

