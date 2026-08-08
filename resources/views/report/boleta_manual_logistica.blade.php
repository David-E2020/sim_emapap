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
        <td class="text-center bg-grey-darker text-xs text-white">Fecha Consulta:</td>
        <td class="text-xs uppercase"></td>

        <td class="text-center bg-grey-darker text-xs text-white">Hora:</td>
        <td class="text-xs uppercase">
            
        </td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Planta:</td>
        <td class="text-xs uppercase"></td>

        <td class="text-center bg-grey-darker text-xs text-white">Responsable:</td>
        <td class="text-xs uppercase">
            
        </td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Punto Ingreso:</td>
        <td class="text-xs uppercase"></td>

        <td class="text-center bg-grey-darker text-xs text-white">Punto Salida:</td>
        <td class="text-xs uppercase">
            
        </td>
    </tr>
    <!---->
</table>

<br>
<span><h6>DATOS DEL TRANSPORTE: </h6></span>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Tipo Vehiculo:</td>
        <td class="text-xs uppercase text-center">NISSAN</td>

        <td class="text-center bg-grey-darker text-xs text-white">Placa:</td>
        <td class="text-xs uppercase text-center">WEQW333</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Conductor:</td>
        <td class="text-xs uppercase text-center">Pedro Quispe</td>

        <td class="text-center bg-grey-darker text-xs text-white">C.I.:</td>
        <td class="text-xs uppercase text-center">68852158</td>
    </tr>
</table>

<br>



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
</tr>
<tr>
    <td class="text-right text-xxs"></td>
    <td class="text-left text-xxs"></td>
    <td class="text-right text-xxs"></td>
</tr>
</table>



@endsection