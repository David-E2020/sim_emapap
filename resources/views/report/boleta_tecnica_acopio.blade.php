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
    .custom-checkbox {
        width: 50px;
        height: 50px;
        border: 1px solid black;
        text-align: center;
        font-size: 30px;
        line-height: 50px;
    }
</style>
<br>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Planta:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_planta}}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Centro de Acopio:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_silo}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Numero de Boleta:</td>
        <td class="text-xs uppercase text-center"> <b>{{$acopio->acopio_correlativo}}</b></td>

        <td class="text-center bg-grey-darker text-xs text-white">Fecha Ingreso y hora:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_fecha_ingreso}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Programa:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_programa}}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Campaña:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_preiodo_agricola}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Tipo Vehiculo:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_transporte_modelo}}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Placa:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_transporte_placa}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Conductor:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_conductor}}</td>

        <td class="text-center bg-grey-darker text-xs text-white">C.I.:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_conductor_identificacion}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Productor:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_productor_nombre}}</td>

        <td class="text-center bg-grey-darker text-xs text-white">C.I.:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_productor_identificacion}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Regional:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_regional   }}</td>
        <td class="text-center bg-grey-darker text-xs text-white">Tipo Apoyo:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_tipo_apoyo}}</td>
    </tr>    
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Asociacion:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_asociacion}}</td>
        <td class="text-center bg-grey-darker text-xs text-white">Localidad:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_localidad}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Registro Sanitario:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_numero_registro_sanitario}}</td> 

        <td class="text-center bg-grey-darker text-xs text-white">NIT:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_numero_rau}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Peso Bruto (Kg):</td>
        <td class="text-xs uppercase text-center">{{number_format($acopio->acopio_peso_bruto, 3)}}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Unidades:</td>
        <td class="text-xs uppercase text-center">{{$acopio->acopio_unidades}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Peso Liquido:</td>
        <td class="text-xs uppercase text-center"><b>{{number_format($acopio->aco_peso_liquido,3)}}</b></td>

        <td class="text-center bg-grey-darker text-xs text-white">Especie (pescado):</td>
        <td class="text-xs uppercase text-center">{{$aco_articulo_nombre}}</td>
    </tr>
    <!---->
</table>
<br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td class="font-bold text-center text-xs" colspan="3">PARAMETROS FISICO QUIMICOS</td>
            <td class="font-bold text-center text-xs" colspan="6">CONTROL ORGANOLEPTICO</td>
            <td class="font-bold text-center text-xs" colspan="3">BIOMETRIA</td>
            <td class="font-bold text-center text-xs" colspan="2">OTROS</td>
        </tr>
    </thead>
    <tr class="bg-grey-lightest">
        <td class="text-center text-xxs uppercase">PH (6-7)</td>
        <td class="text-center text-xxs uppercase">Temperatura Interna (0-4)</td>
        <td class="text-center text-xxs uppercase">Temperatura Externa (0-5)</td>
        <td class="text-center text-xxs uppercase">PRESENCIA DE OFF FLAVOR</td>
        <td class="text-center text-xxs uppercase">OLOR (CARACTERÍSTICO)</td>
        <td class="text-center text-xxs uppercase">OJOS (CARACTERÍSTICO BRILLANTE)</td>
        <td class="text-center text-xxs uppercase">PIEL (TORNASOLADO CON MUCOSIDAD ACUOSA)</td>
        <td class="text-center text-xxs uppercase">BRANQUIAS (COLOR BRILLANTE SIN MUCOSIDAD)</td>
        <td class="text-center text-xxs uppercase">CARNE (FIRME Y ELÁSTICA)</td>
        <td class="text-center text-xxs uppercase">PESO DE LA MUESTRA (Kg)</td>
        <td class="text-center text-xxs uppercase">LONGITUD ESTÁNDAR (cm)</td>
        <td class="text-center text-xxs uppercase">LONGITUD TOTAL (cm)</td>
        <td class="text-center text-xxs uppercase">PRESENCIA DE ALIMENTO </td>
        <td class="text-center text-xxs uppercase">EXCESO DE GRASA VISCERAL (PARAMETRO PERMISIBLE >5%)</td>
    </tr>
    <tr class="bg-grey-lightest">
        @foreach ($acopio->acopio_parametros_parametrica as $item)
        <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{$item->acp_ponderacion}}%</td>
        @endforeach
    </tr>
    <tr>
        @foreach ($acopio->acopio_parametros_parametrica as $item)
            @if($item->acp_id==4 && $item->cantidad == 30)
                <td class="text-center font-bold text-xs">NO</td>
            @endif
            @if($item->acp_id==4 && $item->cantidad == 0)
                <td class="text-center font-bold text-xs">SI</td>
                
            @endif

            @if($item->acp_id==5 && $item->cantidad == 5)
            <td class="text-center font-bold text-xs">SI</td>
            @endif
            @if($item->acp_id==5 && $item->cantidad == 0)
                <td class="text-center font-bold text-xs">NO</td>
            @endif

            @if($item->acp_id==6 && $item->cantidad == 3)
            <td class="text-center font-bold text-xs">SI</td>
            @endif
            @if($item->acp_id==6 && $item->cantidad == 0)
                <td class="text-center font-bold text-xs">NO</td>
            @endif

            @if($item->acp_id==7 && $item->cantidad == 3)
            <td class="text-center font-bold text-xs">SI</td>
            @endif
            @if($item->acp_id==7 && $item->cantidad == 0)
                <td class="text-center font-bold text-xs">NO</td>
            @endif

            @if($item->acp_id==8 && $item->cantidad == 3)
            <td class="text-center font-bold text-xs">SI</td>
            @endif
            @if($item->acp_id==8 && $item->cantidad == 0)
                <td class="text-center font-bold text-xs">NO</td>
            @endif

            @if($item->acp_id==9 && $item->cantidad == 5)
            <td class="text-center font-bold text-xs">SI</td>
            @endif
            @if($item->acp_id==9 && $item->cantidad == 0)
                <td class="text-center font-bold text-xs">NO</td>
            @endif
            @if($item->acp_id !=4 && $item->acp_id!=5 && $item->acp_id!=6 && $item->acp_id!=7 && $item->acp_id!=8 && $item->acp_id!=9)
                <td class="text-center font-bold text-xs">{{$item->cantidad}}</td>
            @endif
        @endforeach
    </tr>
    <tr>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_piscicola_ph}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_temperatura_interna}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_temperatura_externa}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_organoleptico}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_organoleptico_olor}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_organoleptico_ojos}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_organoleptico_piel}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_organoleptico_branquias}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_organoleptico_carne}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_peso_muestra}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_longitud_estandar}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_longitud_total}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_presencia_alimento}}%</td>
        <td class="text-center font-bold text-xs">{{$acopio->acopio_parametros->calculo_grasa_visceral}}%</td>
    </tr>

    @php
    $nro = 1;
    $total_presentacion = 0;
    $total_medida = 0;
    @endphp

    <tr class="text-sm">
        <td colspan="13" class="text-center text-xxs uppercase font-bold px-4 py-3 bg-grey-darker text-white"><strong> CALIFICACION:</strong> </td>
        <td class="text-center text-xxs uppercase font-bold px-5 py-3"> <B>{{ number_format($acopio->acopio_parametros->calificacion,2,'.',',')}}%</B> </td>
    </tr>


</table>
<table style="width: 100%;">
    <tr>
        <th style="font-weight: bold; font-size: 18px; text-align: start;">
            Con base de los resultados del control calidad del pescado se concluye:
        </th>
    </tr>
</table>
<br>

<table style="width: 50%; margin:0 auto;">
    <tr>
        <td class="label"><b>Aceptación:</b></td>
        <td class="custom-checkbox" style="font-size: 28px; line-height: 50px; padding:0 -100px 0 0;">X</td>
        <td style="width: 50px;"></td>
        <td class="label" style="padding:20px;"><b>Rechazo:</b></td>
        <td class="custom-checkbox" style="border: 1px solid black; padding:0 -140px 0 0;"></td>
    </tr>
</table>

<br>
<p style="padding: 0 30px;"><b>OBSERVACIONES: </b>{{$acopio->acopio_parametros->observaciones}}</p>
<table class="table-reporte" style="width: 28%; margin:0 auto;">
    <tr style="height: 100px; border:1px solid black">
        <td class="text-xs" style="vertical-align: bottom; text-align: center">
            HUELLA DIGITAL DEL PRODUCTOR:
        </td>
    </tr>
</table>
<br><br>


<table class="table-reporte">
    <tr style="height: 150px;">
        <td class="text-xs" style="vertical-align: bottom; text-align: center">
            FIRMA PRODUCTOR:
        </td>
        <td class="text-xs" style="vertical-align: bottom; text-align: center" rowspan="2">
            SELLO Y FIRMA:
        </td>
        <td class="text-xs" style="vertical-align: bottom;" rowspan="2">
            SELLO Y FIRMA:
        </td>
    </tr>
    <tr>
        <td class="text-xs" style="text-align: center">
            NOMBRE: {{$acopio->acopio_productor_nombre}}
        </td>
    </tr>
    <tr>
        <td class="text-xs" style="text-align: center">
            C.I.: {{$acopio->acopio_productor_identificacion}}
        </td>
        <td class="text-xs" style="text-align: center">
            NOMBRE: {{$USER_NAME}}
        </td>
        <td class="text-xs">
            NOMBRE:
        </td>
    </tr>
    <tr>
        <td class="text-center text-xs font-bold">
            NOMBRE PRODUCTOR
        </td>
        <td class="text-center text-xs font-bold">
            REGISTRADO - PERSONAL EMAPA
        </td>
        <td class="text-center text-xs font-bold">
            APROBADO - PERSONAL EMAPA
        </td>
    </tr>
</table>




@endsection