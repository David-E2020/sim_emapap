@extends('layouts.print')

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
<DIV class="text-right w-100 mb-2"><B>Nro.  {{$acopio->acopio_correlativo}}</b></DIV>
<table class="table-reporte align-top color">
    <tr>
        <th class="text-center" width='9%'>Regional:</th>
        <td  width='25%'>{{$acopio->acopio_regional}}</td>
        <th class="text-center" width='9%'>F. Ingreso:</th>
        <td width='22%'>{{$acopio->acopio_fecha_ingreso}}</td>
        <th class="text-center" width='9%'>Conductor:</th>
        <td width='15%' >{{$acopio->acopio_conductor}}</td>
    </tr>
    <tr>
        <th class="text-center">Campaña:</th>
        <td >{{$acopio->acopio_preiodo_agricola}}</td>
        <th class="text-center">F. Salida:</th>
        <td >{{$acopio->acopio_fecha_salida}}</td>
        <th class="text-center">Licencia/CI:</th>
        <td >{{$acopio->acopio_conductor_identificacion}}</td>
    </tr>
    <tr>
        <th class="text-center">Programa:</th>
        <td >{{$acopio->acopio_programa}}</td>
        <th class="text-center">C. Registro:</th>
        <td >{{$acopio->acopio_planta}}</td>
        <th class="text-center">Tipo Vehiculo:</th>
        <td >{{$acopio->acopio_transporte_tipo ?? '-'}}</td>
    </tr>
    <tr>
        <th class="text-center">Productor:</th>
        <td >{{$acopio->acopio_productor_nombre }}</td>
        <th class="text-cente">C. Acopio.:</th>
        <td >{{$acopio->acopio_silo}}</td>
        <th class="text-center">Placa:</th>
        <td >{{$acopio->acopio_transporte_placa}}</td>
    </tr>
    <tr>
        <th class="text-center">CI:</th>
        <td >{{$acopio->acopio_productor_identificacion}}</td>
        <th class="text-center">Variedad:</th>
        <td >{{$acopio->acopio_parametros->variedad ?? '-'}}</td>
        <th class="text-center">Marca/Color:</th>
        <td >{{$acopio->acopio_transporte_marca }} - {{$acopio->acopio_transporte_color}}</td>
    </tr>
    <tr>
        <th class="text-center">Organizacion:</th>
        <td >{{$acopio->acopio_asociacion}}</td>
        <th class="text-center">Usu. Registro:</th>
        <td >{{$acopio->usuario_registro}}</td>
        <th class="text-center ">Usu. Liquida:</th>
        <td >{{$acopio->usuario_liquida}}</td>
    </tr>
    <tr>

        <th class="text-center">Tipo Apoyo:</th>
        <td >{{$acopio->acopio_tipo_apoyo}}</td>
        <th class="text-center">Usu. Analisis:</th>
        <td >{{$acopio->usuario_analisis}}</td>
        <th class="text-center ">F. Liquida:</th>
        <td >{{$acopio->usuario_liquida_fecha}}</td>  
    </tr>
    </tr>
    <!---->
</table>

<!----------->
<table>
<tr><td width = '30%'>
<table class="table-reporte w-100 color mt-5">
    <thead>
        <tr >
            <th class="text-center">
                PESOS
            </th>
            <th class="text-center ">VOLUMEN</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="font-bold text-center ">
                PESO BRUTO:
            </td>
            <td class="text-right text-xs">{{number_format($acopio->aco_peso_bruto, 3)}} Kg.</td>
        </tr>
        <tr>
            <td class="font-bold text-center">
                PESO TARA:
            </td>
            <td class="text-right text-xs">{{number_format($acopio->aco_peso_tara, 3)}}  Kg.</td>
        </tr>
        <tr>
            <td class="font-bold text-center ">
                PESO NETO:
            </td>
            <td class="text-right text-xs">{{number_format($acopio->aco_peso_neto, 3)}} Kg.</td>
        </tr>
        <tr>
            <td class="font-bold text-center">
                PESO LIQUIDO:
            </td>
            <td class="text-right text-xs">{{number_format($acopio->aco_peso_liquido, 3)}}  Kg.</td>
        </tr>
        <tr>
            <td class="font-bold text-center">
                PESO LIQ. ACOPIO:
            </td>
            <td class="text-right text-xs bg-grey-lightest"><b>{{number_format($acopio->aco_peso_liquido_acopio, 3)}} </b>

            @switch($acopio->tipo_programa)
                @case(2)
                    Fn.
                @break
                @case(3)
                    @if($acopio->acopio_preiodo_agricola =='VERANO 2023 - 2024')
                        Kg.
                    @else    
                        Tn.
                    @endif                
                @break
                @case(5)
                    QQ.
                @break
                @case(7)
                    Kg.
                @break                
                @case(8)
                    QQ.
                @break                    
                @default
                    Tn.
            @endswitch
            </td>
        </tr>
    </tbody>
</table>
<div class="col">
</td>
<td width='10%'></td>
<td>
@php
    $total_para = count($acopio->acopio_parametros_parametrica) ;
    $numero_para = 1;
@endphp
<table class="table-reporte w-100 color mt-3" >
    <thead >
        <tr class="bg-grey-lightest">
                <th class="text-center">PARAMETRO</th>
                <th class="text-center">VALOR</th>
                <th class="text-center">DESC.</th>
                <th class="text-center">PARAMETRO</th>
                <th class="text-center">VALOR</th>
                <th class="text-center">DESC.</th>
        </tr>
    </thead>
    </tbody>
            @foreach ($acopio->acopio_parametros_parametrica as $item)
                @if(!($numero_para % 2 == 0))
                <TR>
                @endif
                <td class="text-right">{{$item->acp_nombre}}</td>
                <td class="text-right ">{{$item->cantidad}} </td>
                <td class="text-right ">{{$item->descuento}} %</td>
                @if($numero_para % 2 == 0)
                </TR>
                @endif
                @php
                 $numero_para = $numero_para +1;
                @endphp
            @endforeach
    <tr class="text-sm">
        <th colspan="2" class="text-center "><strong> GRADO:</strong> </th>
        <td class="text-center text-xxs uppercase font-bold px-5 py-3"> <B>{{ number_format($acopio->acopio_parametros->grado,2,'.',',')}}%</B> </td>
        <th colspan="2" class="text-center "><strong> CLASIFICACION:</strong> </th>
        <td class="text-center text-xxs uppercase font-bold px-5 py-3"> <B>{{ $acopio->acopio_parametros->tipograno ?? '-'}}</B> </td>
    </tr>
    </tbody>
</table>
</td>
</tr>
</table>

<table class="table-reporte color mt-3" style ='font-size:8px;' >
    <tr height='100px'>
        <td style="vertical-align: bottom; text-align: center" width='45%'>

        </td>
        <td  style="vertical-align: bottom; text-align: center"  width='25%'>

        </td>
        <td style="vertical-align: bottom;" width='30%'>

        </td>
    </tr>
    <tr>
        <td class="text-center font-bold">
            SELLO Y FIRMA - EMAPA
        </td>
        <td class="text-center  font-bold">
            Huella Digital del Productor(a)
        </td>
        <td class="text-center  font-bold">
           FIRMA PRODUCTOR(A)
        </td>
    </tr>
</table>
<p style="font-size:10px; padding-top:2px">
@if($acopio->acopio_estado_id != 1)
 Esta boleta esta :<b >{{ $acopio->acopio_estado }}</b> -
@endif
<b> Observación: </b>{{$acopio->acopio_parametros->observaciones ?? ''}}</p>
<br><br><br>
@endsection
@extends('layouts.print')