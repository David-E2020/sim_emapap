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
</style>

<br>
<table>
    <tr>
        <td class="text-xs">POR CUENTA DE:</td>
        <td class="text-xs uppercase"></td>

        <td class="text-xs">RECIBI DE:</td>
        <td class="text-xs uppercase">{{$USER_NAME}}</td>
    </tr>
    <tr>
        <td class="text-xs font-bold">SEÑOR(A):</td>
        <td class="text-xs uppercase"></td>

        <td class="text-xs font-bold">CONTO. DE CARGA:</td>
        <td class="text-xs uppercase"></td>
    </tr>
    <tr>
        <td class="text-xs font-bold">TRANSP:</td>
        <td class="text-xs uppercase"></td>

        <td class="text-xs font-bold">CAMIÓN:</td>
        <td class="text-xs uppercase"></td>
    </tr>
    <tr>
        <td class="text-xs font-bold">PROVEEDOR:</td>
        <td class="text-xs uppercase">EMPRESA XXX</td>

        <td class="text-xs font-bold">PLACA:</td>
        <td class="text-xs uppercase">4441PIL</td>
    </tr>

    <!---->

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
    </tr>

</table>

<br>

<!----------->

<table class="table-reporte">
    <tr>
        <td class="text-center font-bold" rowspan="2">#</td>
        <td rowspan="2" class="font-bold text-xs">DETALLE</td>
        <td rowspan="2" class="font-bold text-center text-xs">UNIDAD MEDIDA</td>
        <td class="text-center font-bold text-xs" rowspan="2">NRO. <br> LOTE</td>
        <td class="text-center font-bold text-xs" colspan="2">PRESENTACIÓN</td>
        <td class="text-center font-bold text-xs" colspan="2">TOTAL</td>
    </tr>

    <tr>
        <td class="text-center font-bold text-xs"> Caja / Paq </td>
        <td class="text-center font-bold text-xs"> Medida</td>
        <td class="text-center font-bold text-xs"> Caja / Paq </td>
        <td class="text-center font-bold text-xs"> Medida</td>
    </tr>

    @php
    $nro = 1;
    $total_presentacion = 0;
    $total_medida = 0;
    @endphp

    <tr class="text-sm">
        <td colspan="6" class="text-center"><strong> TOTAL:</strong> </td>
        <td class="text-center"> <strong>{{ number_format($total_presentacion,2,'.',',')}}</strong> </td>
        <td class="text-center"> <strong>{{ number_format($total_medida,2,'.',',')}}</strong> </td>
    </tr>

    <tr>
        <td colspan="8">
            <strong class="text-xs">OBSERVACIONES: </strong>
        </td>
    </tr>

    <tr>
        <td colspan="8">
            <strong class="text-xs">DOC. ADJUNTOS: </strong>
        </td>
    </tr>


</table>


<br> <br>

<table class="table-reporte">
    <tr>
        <td class="text-xxs">
            ACEPTO HABER RECIBIDO ESTA CARTA EN PERFECTAS CONDICIONES
        </td>
        <td rowspan="5" style="vertical-align: bottom; text-align: center;">
            ---------------------------------------------<br>
            <span class="text-xs"></span> <br>
            <span class="text-xs font-bold">CHOFER</span>
        </td>
    </tr>
    <tr style="height: 150px;">
        <td class="text-xs" style="vertical-align: bottom;">
            FIRMA:
        </td>
    </tr>
    <tr>
        <td class="text-xs">
            NOMBRE:
        </td>
    </tr>
    <tr>
        <td class="text-xs">
            CARGO:
        </td>
    </tr>
    <tr>
        <td class="text-xs">
            C.I.:
        </td>
    </tr>
    <tr>
        <td class="text-center text-xs font-bold">
            RECIBI CONFORME
        </td>
        <td class="text-center text-xs font-bold">
            ENTREGUE CONFORME
        </td>

    </tr>
</table>


@endsection