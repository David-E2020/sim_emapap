@extends('layouts.print_inventario')

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
<table class="table-reporte align-top">
    <tr>
        <td class="text-center bg-grey-lightest"><b>CODIGO SOLICITUD:</b></td>
        <td class="text-center" style="font-size: 14px" colspan="4"><b>{{$solicitud_registro->solicitud_id }}</b></td>
    </tr>
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>FECHA REGISTRO:</b></td>
        <td class="text-center" width='20%'>{{$movimientos_salida->created_at }}</td>
        <td class="text-center bg-grey-lightest text-xs text-black" width='20%'><b>PLANTA:</b></td>
        <td class="text-center" width='20%'>{{$solicitud_registro->planta_origen->nombre ?? '-'}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>ORIGEN:</b></td>
        <td class="text-center" width='20%'>{{$solicitud_registro->origen->nombre}}</td>

        <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>RESPONSABLE ORIGEN:</b></td>
        <td class="text-center" width='20%'>{{$datos['nombre_responsable_origen']}}</td>
    </tr>
    @if($solicitud_registro->tipo_solicitud_id < 9)
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>DESTINO:</b></td>
        <td class="text-center" width='20%'>{{$solicitud_registro->destino->nombre}}</td>
        <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>RESPONSABLE DESTINO:</b></td>
        <td class="text-center" width='20%'>{{$datos['nombre_responsable_destino']}}</td>
    </tr>
    @endif
    <!---->
    </table>
        @if($datos['tipo_transporte_id']== 2 && $solicitud_registro->tipo_solicitud_id < 9)
            <?php
$logistica_id = $datos_movimiento['logistica_id'] ?? 0;

$datos_vehiculo = App\Models\Logistica\MovimientoDetalleLogistica::find($logistica_id);
$distribuidora = App\Models\Inventario\Distribuidora::find($datos_vehiculo->distribuidora_id);
$transporte = App\Models\Inventario\Transporte::find($datos_vehiculo->vehiculo_id);
$conductor = App\Models\Inventario\Conductor::find($datos_vehiculo->conductor_id);

$nombre_cliente = $datos['cli_razon_social'] ?? 0;
$numero_documento_cliente = $datos['cliente_numero_identificacion'] ?? '-';
?>

            <table class="table-reporte align-top color">
                <tr>
                    <td colspan="4" class="text-center" style="border: medium transparent"><b>DATOS DE TRANSPORTE</b></td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>CODIGO LOGISTICA:<b></td>
                    <td class="text-center" width='20%'>{{$datos_vehiculo->codigo_boleta ?? '-'}}</td>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>TRANSPORTADORA:<b></td>
                    <td class="text-center" width='20%'>{{$distribuidora->nombre ?? '-'}}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>PLACA:</b></td>
                    <td class="text-center" width='20%'>{{$transporte->placa ?? '-'}}</td>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>MARCA:</b></td>
                    <td class="text-center" width='20%'>{{$transporte->marca ?? '-'}}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>TIPO:</b></td>
                    <td class="text-center" width='20%'>{{$transporte->type ?? '-'}}</td>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>COLOR:</b></td>
                    <td class="text-center" width='20%'>{{$transporte->color ?? '-'}}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>NOMBRE CONDUCTOR:</b></td>
                    <td class="text-center" width='20%'>{{$conductor->nombre_completo ?? '-'}}</td>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>NUMERO IDENTIFICACION CONDUCTOR:</b></td>
                    <td class="text-center" width='20%'>{{$conductor->numero_identificacion ?? '-'}}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>TELEFONO CONDUCTOR:</b></td>
                    <td class="text-center" width='20%'>{{ $conductor->telefono ?? '-'  }}</td>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'><b>CATEGORIA:</b></td>
                    <td class="text-center" width='20%'>{{ $conductor->categoria ?? '-' }}</td>
                </tr>
            </table>
            @if ($movimientos_salida->mv_tipo_movimiento_id == 7 || $movimientos_salida->mv_tipo_movimiento_id == 8 || $movimientos_salida->mv_tipo_movimiento_id == 6 || $movimientos_salida->mv_tipo_movimiento_id == 11)
                @if($datos['tipo_producto_id']!=2)
                <table class="table-info align-top no-padding no-margins border">
                        <tr>
                            <td class="text-center bg-grey-darker text-xs text-white">Peso Bruto:</td>
                            <td class="text-xs uppercase text-center">{{ $datos_movimiento['peso_bruto'] ?? 0 }}</td>
                            <td class="text-center bg-grey-darker text-xs text-white">Peso Tara:</td>
                            <td class="text-xs uppercase text-center">{{ $datos_movimiento['peso_tara'] ?? 0 }}</td>
                            <td class="text-center bg-grey-darker text-xs text-white">Peso Neto:</td>
                            <td class="text-xs uppercase text-center">{{ $datos_movimiento['peso_neto'] ?? 0}}</td>
                        </tr>
                </table>
                @endif
            @endif
        @endif

        @if ($solicitud_registro->tipo_solicitud_id == 3)
            <table class="table-reporte align-top color">
                <tr>
                    <td colspan="7" class="text-center" style="border: medium transparent"><b>INFORMACION PARA LA VENTA</b></td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>TIPO TRANSPORTE:</td>
                    <td class="text-center" width='20%'>{{ $tipo_transporte }}</td>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>TIPO VENTA:</td>
                    <td class="text-center" width='20%'>{{ $solicitud_registro->tipo_venta ?? '-' }}</td>
                </tr>
                @if ($datos['tipo_venta_id'] == 2 || $datos['tipo_venta_id'] == 3)
                    <tr>
                        <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>CLIENTE: </td>
                        <td class="text-center" width='20%'>{{ $datos['cli_razon_social'] ?? '-' }}</td>
                        <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>CLIENTE NUMERO DE IDENTIFICACION:</td>
                        <td class="text-center" width='20%'>{{ $datos['cliente_numero_identificacion'] ?? '-' }}</td>
                    </tr>
                @endif
                @if ($datos['tipo_venta_id'] == 1 || $datos['tipo_venta_id'] == 2 )
                    <tr>
                        <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>ASOCIACION:</td>
                        <td class="text-center" width='20%' colspan="3">{{ $datos['asociacion'] ?? '-'}}</td>
                    </tr>
                @endif
            </table>

            <table  class="table-reporte align-top color">
                @if ($datos['tipo_transporte_id'] == 1)
                <tr>
                    <td colspan="7" class="text-center" style="border: medium transparent"><b>INFORMACIÓN DE TRANSPORTE</b></td>
                </tr>
                <?php
//CLIENTE PRODUCTOR
//$vehiculo = $datos['vehiculo'] ?? 0;
//$conductor = $datos['conductor'] ?? 0;
//$datos_vehiculo = App\Models\ProductoPrivado\Vehiculo::find($vehiculo);
//$datos_conductor = App\Models\ProductoPrivado\Conductor::find($conductor);

$vehiculo_id = $datos_movimiento['vehiculo_id'] ?? 0;
$conductor_id = $datos_movimiento['conductor_id'] ?? 0;

$datos_vehiculo = App\Models\ProductoPrivado\Vehiculo::find($vehiculo_id);
$datos_conductor = App\Models\ProductoPrivado\Conductor::find($conductor_id);
//$nombre_cliente = $datos['cli_razon_social'] ?? 0;
//$numero_documento_cliente = $datos['cliente_numero_identificacion'] ?? '';
?>
                <tr>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>NOMBRE CONDUCTOR:</td>
                    <td class="text-center" width='20%'>{{ $datos_conductor->nombre_completo ?? '-'}}</td>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>NUMERO DE IDENTIFICACION CONDUCTOR:</td>
                    <td class="text-center" width='20%'>{{ $datos_conductor->numero_identificacion ?? '-'}}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>TELEFONO CONDUCTOR:</td>
                    <td class="text-center" width='20%'>{{ $datos_conductor->telefono ?? '-'  }}</td>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>CATEGORIA:</td>
                    <td class="text-center" width='20%'>{{ $datos_conductor->categoria ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>PLACA:</td>
                    <td class="text-center" width='20%'>{{ $datos_vehiculo->placa ?? '-'}}</td>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>MARCA:</td>
                    <td class="text-center" width='20%'>{{ $datos_vehiculo->marca ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>TIPO:</td>
                    <td class="text-center" width='20%'>{{ $datos_vehiculo->type ?? '-' }}</td>
                    <td class="text-center bg-grey-lightest text-sm text-black" width='20%'>COLOR:</td>
                    <td class="text-center" width='20%'>{{ $datos_vehiculo->color ?? '-' }}</td>
                </tr>
            @endif
            </table>
        @endif
<table class="table-reporte align-top">
    <thead>
        <tr>
            <td colspan="7" class="text-center" style="border: medium transparent"><b>DETALLE DE PRODUCTOS</b></td>
        </tr>
        <tr>
            <td rowspan="2" class="text-center bg-grey-lightest text-sm font-bold text-black">Nº</td>
            <td rowspan="2" class="text-center bg-grey-lightest text-sm font-bold text-black">PRODUCTO COMERCIAL</td>
            <td rowspan="2" class="text-center bg-grey-lightest text-sm font-bold text-black">PRODUCTO INVENTARIO</td>
            <td rowspan="2" class="text-center bg-grey-lightest text-sm font-bold text-black">CODIGO PRODUCTO</td>
            <td rowspan="2" class="text-center bg-grey-lightest text-sm font-bold text-black">LOTE</td>
            @if($solicitud_registro->tipo_solicitud_id < 9)
            <td colspan="3" class="text-center bg-grey-lightest text-sm font-bold text-black">CANTIDAD</td>
            <td rowspan="2" class="text-center bg-grey-lightest text-sm font-bold text-black">SALDO</td>
            @else
            <td colspan="2" class="text-center bg-grey-lightest text-sm font-bold text-black">CANTIDAD</td>
            @endif
        </tr>
        <tr>
            <td class="text-center bg-grey-lightest text-sm font-bold text-black">SOLICITADA</td>
            @if($solicitud_registro->tipo_solicitud_id < 9)
            <td class="text-center bg-grey-lightest text-sm font-bold text-black">ACUMULADA</td>
            @endif
            <td class="text-center bg-grey-lightest text-sm font-bold text-black">ENTREGADA</td>
        </tr>
    </thead>
    <tbody>
        <?php
$total_solicitud = 0;
$total = 0;
$total_acumulado = 0;
$total_saldo = 0;
$count = 1;
?>
        @foreach($movimientos_salida->movimiento_detalle as $value)
        <tr class="text-sm">
            <?php
$detalles = App\Models\Inventario\SolicitudDetalleInventario::with('producto')->where('articulo_id', $value->mvd_articulo_id)->where('solicitud_id', $solicitud_registro->id)->first();
$saldo = \DB::select('select * from inventario.sp_get_saldo_articulo_boleta(?,?,?)', array($movimientos_salida->mv_solicitud_id, $movimientos_salida->mv_id, $value->mvd_articulo_id));
$saldo_acumulado = $saldo[0]->saldo - $value->mvd_cantidad;
$saldo_total = $detalles->cantidad - $saldo[0]->saldo;
?>

            <td class="text-center text-xxs uppercase px-5 py-3">{{ $count++ }}</td>
            <td class="text-center text-xxs uppercase px-5 py-3">{{ $detalles->producto->nombre ?? '' }} </td>
            <td class="text-center text-xxs uppercase px-5 py-3">{{ $value->articulo->nombre_producto ?? '' }} </td>
            <td class="text-center text-xxs uppercase px-5 py-3">{{ $value->articulo->codigo_alterno ?? ''}} </td>
            <td class="text-center text-xxs uppercase px-5 py-3">{{ $value->lote->nombre ?? ''}} </td>
            <td class="text-center text-xxs uppercase px-5 py-3">{{ number_format($detalles->cantidad , 2)?? '' }} </td>
            @if($solicitud_registro->tipo_solicitud_id < 9)
            <td class="text-center text-xxs uppercase px-5 py-3">{{ number_format($saldo_acumulado, 2) ?? 0 }} </td>
            <td class="text-center text-xxs uppercase px-5 py-3 bg-grey-lightest">{{ number_format($value->mvd_cantidad , 2)}} </td>
            <td class="text-center text-xxs uppercase px-5 py-3">{{ number_format($saldo_total, 2) ?? 0 }} </td>
            @else
            <td class="text-center text-xxs uppercase px-5 py-3 bg-grey-lightest">{{ number_format($value->mvd_cantidad , 2)}} </td>
            @endif
        </tr>
        <?php
$total_solicitud += $detalles->cantidad;
$total += $value->mvd_cantidad;
$total_acumulado += $saldo_acumulado;
$total_saldo += $saldo_total;
?>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="text-sm">
            <td colspan="5" class="text-center bg-grey-lightest text-sm font-bold text-black"><strong> TOTAL:</strong> </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3  bg-grey-lightest"> <B>{{ number_format((float)$total_solicitud, 2, '.', '')}}</B> </td>
            @if($solicitud_registro->tipo_solicitud_id < 9)
            <td class="text-center text-xxs uppercase font-bold px-5 py-3  bg-grey-lightest"> <B>{{ number_format((float)$total_acumulado, 2, '.', '')}}</B> </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3  bg-grey-lightest"> <B>{{ number_format((float)$total, 2, '.', '')}}</B> </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3  bg-grey-lightest"> <B>{{ number_format((float)$total_saldo, 2, '.', '')}}</B> </td>
            @else
            <td class="text-center text-xxs uppercase font-bold px-5 py-3  bg-grey-lightest"> <B>{{ number_format((float)$total, 2, '.', '')}}</B> </td>
            @endif
        </tr>
        <tr>
            <td  class="text-center bg-grey-lightest text-sm font-bold text-black" colspan="2">
                OBSERVACIÓN:</td>
          
                <?php
$observacion_movimiento = $datos_movimiento['observacion_movimiento'] ?? $observacion;
?>
            <td  class="text-center text-xxs uppercase font-bold px-5 py-3" colspan="7">
                {{$observacion_movimiento ?? '-' }}
            </td>
        </tr>
    </tfoot>


    @php
    $nro = 1;
    $total_presentacion = 0;
    $total_medida = 0;
    @endphp




</table>
<table class="table-reporte">
    <tr style="height: 80px;">
        <td class="text-xs" style="vertical-align: bottom; text-align: center; width: 33%" rowspan="2">
            SELLO Y FIRMA:
        </td>
        <td class="text-xs" style="vertical-align: bottom; text-align: center; width: 33%" rowspan="2">
            SELLO Y FIRMA:
        </td>
        <td class="text-xs" style="vertical-align: bottom; text-align: center; width: 33%" rowspan="2">
            SELLO Y FIRMA:
        </td>
        <td>
            {!! QrCode::format('svg')->size(100)->errorCorrection('H')->color(0, 0, 0)->generate( $solicitud_registro->solicitud_id . ' ' . $solicitud_registro->id . ' ' . $solicitud_registro->dataQr . ' ' . $solicitud_registro->origen_id . ' ' . $solicitud_registro->destino_id . ' ' . $solicitud_registro->planta_origen_id . ' ' . $solicitud_registro->planta_destino_id . ' ' . $solicitud_registro->tipo_venta . ' ' . $solicitud_registro->tipo_solicitud_id . ' ' . $solicitud_registro->created_at ) !!}
        </td>
    </tr>
    <tr>
    </tr>
    <tr>
        <td class="text-center text-xs font-bold" style="text-align: center">
            ELABORADO - PERSONAL EMAPA
        </td>
        @if($solicitud_registro->tipo_solicitud_id < 9)
        <td class="text-center text-xs font-bold" style="text-align: center">
            CONDUCTOR
        </td>
        @else
        <td class="text-center text-xs font-bold" style="text-align: center">
            APROBADO - PERSONAL EMAPA
        </td>
        @endif
        <td class="text-center text-xs font-bold" style="text-align: center">
            AUTORIZADO - PERSONAL EMAPA
        </td>
         <td class="text-center text-xs font-bold" style="text-align: center">
            QR DE AUTENTICIDAD
        </td>
    </tr>
</table>

<table class="saltopagina">
    <tr>
        <td class="text-left text-xxs">
            Fecha Impresión: {{ $date }} <br>
            Usuario:{{ Auth::user()->name }}
        </td>
        <td class="text-left text-xxs"></td>
        <td class="text-right text-xxs"></td>
        <td class="text-right text-xxs">

        </td>
    </tr>
</table>



@endsection