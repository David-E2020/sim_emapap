@extends('layouts.print')

@section('content')


@php
 $totalFacturas = count($nroFacturas);
 $indexFacturas=0;
@endphp
<br>

<!------->

<table class="align-top no-padding no-margins ">
    
    <tr>
        <td class="text-xs">POR CUENTA DE:</td>
        <td class="text-xs uppercase">{{$movimiento->mv_entregado_por}}</td>

        <td class="text-xs">RECIBI DE:</td>
        <td class="text-xs uppercase">{{$USER_NAME}}</td>
    </tr>


    <tr>
        <td class="text-xs font-bold">SEÑOR(A):</td>
        <td class="text-xs uppercase"></td>

        <td class="text-xs font-bold">FACTURA{{$totalFacturas==1?'':'S'}}:</td>
        <td class="text-xs uppercase">

        @foreach($nroFacturas as $item)

            @php
             $indexFacturas =  $indexFacturas +1;
            @endphp

            {{$item}} {{($totalFacturas==1 || ($indexFacturas == $totalFacturas ) )?'':','}}
        @endforeach
        </td>
    </tr>

    <tr>
        <td  class="text-xs font-bold">TRANSP:</td>
        <td  class="text-xs uppercase">{{$movimiento->transportadora->param_nombre}}</td>

        <td class="text-xs font-bold">CAMIÓN:</td>
        <td class="text-xs uppercase">{{$movimiento->mv_camion}}</td>
    </tr>

    <tr>
        <td  class="text-xs font-bold">DESTINO:</td>
        <td  class="text-xs uppercase"> {{$movimiento->destino->param_nombre}} </td>

        <td class="text-xs font-bold">PLACA:</td>
        <td class="text-xs uppercase">{{$movimiento->mv_placa}}</td>
    </tr>

     <tr>
        <td  class="text-xs"></td>
        <td  class="text-xs uppercase"></td>

        <td class="text-xs font-bold ">LICENCIA:</td>
        <td class="text-xs uppercase">{{$movimiento->mv_licencia}}</td>
    </tr>

    <tr>
        <td  class="text-xs"></td>
        <td  class="text-xs uppercase"></td>

        <td class="text-xs font-bold">CELULAR:</td>
        <td class="text-xs uppercase">{{$movimiento->mv_celular}}</td>
    </tr>

    <tr>
        <td  class="text-xs"></td>
        <td  class="text-xs uppercase"></td>

        <td class="text-xs font-bold">CONTENEDOR:</td>
        <td class="text-xs uppercase">{{$movimiento->mv_contenedor}}</td>
    </tr>

    <tr>
        <td  class="text-xs"></td>
        <td  class="text-xs uppercase"></td>

        <td class="text-xs font-bold">PRESINTO:</td>
        <td class="text-xs uppercase">{{$movimiento->mv_presinto}}</td>
    </tr>

    <tr>
        <td  class="text-xs"></td>
        <td  class="text-xs uppercase"></td>

        <td class="text-xs font-bold">NRO RESERVA:</td>
        <td class="text-xs uppercase">{{$movimiento->mv_reserva}}</td>
    </tr>

   

</table>

<br>








<table  id="table-border-detalle"  style="border-collapse:collapse;" >
    <tr> 
        <td class="text-center" rowspan="2">#</td>
        <td  rowspan="2">DETALLE</td>

        <td class="text-center" rowspan="2">NRO. <br> LOTE</td>
        <td class="text-center" colspan="2">PRESENTACIÓN</td>
        <td class="text-center" colspan="2">TOTAL</td>
    </tr>

    <tr>
        <td class="text-center" > Caja / Paq </td>
        <td class="text-center"> Medida</td>
        <td class="text-center"> Caja / Paq </td>
        <td class="text-center"> Medida</td>
    </tr>

    @php
        $nro = 1;
        $total_presentacion = 0;
        $total_medida = 0;
    @endphp
    @foreach($movimiento->detalles as $det)
        @php
        $total_presentacion = $total_presentacion + $det->mvd_cantidad_present; 
        $total_medida = $total_medida + $det->mvd_cantidad_medida;
        @endphp
    <tr >

        <td class=" text-center text-xs uppercase font-bold px-1 py-1">{{ $nro++ }}</td>
        <td class=" text-xs uppercase font-bold px-1 py-1">
        {{ $det->producto->prod_desc}} 
        {{ $det->producto->tipo->param_nombre}} 
        {{ $det->producto->calidad->param_nombre}}

        </td>

        <td class=" text-center text-xs uppercase font-bold px-1 py-1">{{ $det->mvd_nro_lote }}</td>
        <td class=" text-center text-xs uppercase font-bold px-1 py-1">{{ $det->mvd_presentacion}}</td>
        <td class=" text-center text-xs uppercase font-bold px-1 py-1">{{ number_format($det->mvd_presentacion_medida,2,'.',',')}} {{ $det->producto->unidadMedida->param_nombre}} </td>
        <td class=" text-center text-xs uppercase font-bold px-1 py-1">{{ number_format($det->mvd_cantidad_present,2,'.',',')}}</td>
        <td class=" text-center text-xs uppercase font-bold px-1 py-1">{{ number_format($det->mvd_cantidad_medida,2,'.',',')}} {{ $det->producto->unidadMedida->param_nombre}} </td>
    </tr>
    @endforeach
    <tr class="text-sm">
        <td colspan="5" class="text-center" ><strong> TOTAL:</strong> </td>
        <td class="text-center" > <strong>{{ number_format($total_presentacion,2,'.',',')}}</strong> </td>
        <td class="text-center" > <strong>{{ number_format($total_medida,2,'.',',')}}</strong> </td>
    </tr>

    <tr>
        <td colspan="7">
        <strong>OBSERVACIONES: </strong> {{$movimiento->mv_observaciones}}
        </td>
    </tr>

    <tr>
        <td colspan="7">
        <strong>DOC. ADJUNTOS: </strong> 
        </td>
    </tr>


</table>

<br> 
<br> 

<table id="table-border-detalle"  style="border-collapse:collapse;" >
        <tr >
            <td rowspan="5"> 
            </td >
            <td class="text-xxs">
                ACEPTO HABER RECIBIDO ESTA CARTA EN PERFECTAS CONDICIONES
            </td>
        </tr>


        <tr style="height: 150px;"  >
            <td class="text-xs" style="vertical-align: bottom;" >
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
            <td class="text-center font-bold" >
                ENTREGUE CONFORME
            </td>
            <td class="text-center font-bold" >
                RECIBI CONFORME
            </td>
        </tr>
    </table>

<style type="text/css">

    #table-border-detalle  td {
        border: 1px solid #020202; 
        padding: 3px;
    }
    
    .th-border{
    border:1px solid black; font-size: 15px;
    }

</style>

@endsection

