<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="{{ public_path('css/wkhtml.css') }}" media="all" />
    <title>Boleta de Asignacion Logistica</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
        }

        .cabecera {
            border-right: 1px solid black;
        }

        .datos {
            padding: 0 10px;
        }

        .personal {
            padding: 0 10px;
        }

        .transporte {
            padding: 0 10px;
        }

        .producto {
            border: 1px solid #909497;
            border-radius: 5px;
            margin: 0 auto;
            background: #F8F9F9;
        }

        .producto_cabecera {
            background: #CCD1D1;
            border-radius: 3px;
            margin: 0 auto;
            border: 0px solid #ffffff;
        }

        p {
            margin: 2px 1px;
            padding: 5PX
        }

        .columna {
            flex: 1;
            height: 610px;
        }
    </style>
</head>

<body style="border:none">
    <div class="columna">
        <div class="page-break">
            <table class="w-100">
                <tr>
                    <th class="w-20 text-left no-padding no-margins align-middle text-center">
                        <img src="{{ public_path('img/bicentenarioLogo.png') }}" style=" width: 148px; height: 25px;">
                        <img src="{{ public_path('img/logoEmapa2.png') }}" style=" width: 148px; height: 42px;">
                    </th>
                    <th class="w-55 align-center text-center">
                        <span class="font-semibold uppercase leading-tight " style="font-size:14px">
                            {{ $institution ?? 'EMPRESA DE APOYO A LA PRODUCCION DE ALIMENTOS' }} <br>
                            {{ $direccion_unidad ?? 'UNIDAD DE LOGISTICA' }} <br>
                        </span>
                    </th>
                    <th class="w-25 no-padding  align-center">
                        <table class="table-code align-top no-padding no-margins">
                            <tbody>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Nº de Codigo</td>
                                    <td class="text-xs text-center">{!! $datosImpresion->codigo_boleta_detalle !!}</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Fecha Asignacion</td>
                                    <td class="text-xs text-center">{{ $datosImpresion->asignacion }}</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Sistema</td>
                                    <td class="text-xs text-center">SIE-SIGAPE</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Nº Boleta</td>
                                    <td class="text-xs text-center">{{ $datosImpresion->numero_detalle }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </th>
                </tr>
            </table>
            <table>
                <tr class="px-15 py text-center text-m">
                    <td class="font-bold text-center text-m" colspan="6">BOLETA DE
                        ASIGNACION DE TRANSPORTE - LOGISTICA</td>
                </tr>
            </table>
            <p class="font-semibold uppercase" style="font-size:14px;">DATOS DE LA SOLICITUD:</p>
            <table class="table-info align-top no-padding no-margins border">
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Fecha de Solicitud</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->fecha_solicitud }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Codigo de Solicitud</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->codigo_solicitud }}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Origen</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->origen }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Resp. de Envio</td>
                    <td class="text-xs uppercase text-center">---</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Destino</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->destino }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Resp. de Recepcion</td>
                    <td class="text-xs uppercase text-center">---</td>
                </tr>
            </table>
            <p class="font-semibold uppercase" style="font-size:14px; ">DATOS DEL TRANSPORTE:</p>
            <table class="table-info align-top no-padding no-margins border">
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Transportadora</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->nombre_distribuidora }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Conductor</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->nombre_conductor }}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">C.I. Conductor</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->identificacion_conductor }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Tel. Conductor</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->telefono_conductor }}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Placa del Vehiculo</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->placa_vehiculo }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Tipo Vehiculo</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->tipo_vehiculo }}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Marca del Vehiculo</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->marca_vehiculo }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Color del Vehiculo</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->color_vehiculo }}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Cantidad de Carga</td>

                    @if ($datosImpresion->cantidad_detalle == '0')
                    @else
                        <td colspan="3" class="text-xs uppercase text-center"> {{ $datosImpresion->cantidad_detalle }}</td>
                    @endif
                </tr>
            </table>
            <table>
                <tr class="px-15 py text-center text-xxs">
                    <td class="font-bold text-center text-xs" colspan="6">DETALLE DE PRODUCTOS SOLICITADOS</td>
                </tr>
            </table>
            <table class="table-info w-100">
                <thead class="bg-grey-darker">
                    <tr class="font-medium text-white text-sm">
                        <td class="px-15 py text-center text-xs ">
                            Nro.
                        </td>
                        <td class="px-15 py text-center text-xs">
                            Nombre del Producto
                        </td>
                        <td class="px-15 py text-center text-xs">
                            Unidad de Medida
                        </td>
                        <td class="px-15 py text-center text-xs">
                            Cantidad Solicitado
                        </td>
                    </tr>
                </thead>
                <tbody>
                    @foreach (json_decode($datosImpresion->detalles) as $index => $item)
                        <tr class="text-sm">
                            <td class="text-center text-xs uppercase font-bold px-5 py-3">{{ $index + 1 }}</td>
                            <td class="text-center text-xs uppercase font-bold px-5 py-3">
                                {{ $item->nombre }}</td>
                            <td class="text-center text-xs uppercase font-bold px-5 py-3">
                                {{ $item->unidad_med }}</td>
                            <td class="text-center text-xs uppercase font-bold px-5 py-3">
                                {{ $item->cantidad_solicitada }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <br>
            <table class="table-info w-100">
                <tr class="px-15 py text-center text-xxs">
                    <td class="text-center bg-grey-darker text-xs text-white ">OBSERVACIONES</td>
                    <td class="text-xs uppercase text-center">{{$datosImpresion->observacion}}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
<br>
<br>
<br>
<br>
<br>
<table class="w-100">
    <tr>
        <td class="w-50 text-center text-xxs subtitulo">Firma:
            ............................................................</td>
        <td class="w-50 text-center text-xxs subtitulo">Firma:
            ............................................................</td>
    </tr>
    <tr>
        <td class="w-50 text-center text-xxs subtitulo font-bold">ASIGNADOR</td>
        <td class="w-50 text-center text-xxs subtitulo font-bold">RESPONSABLE DE UNIDAD</td>
    </tr>

</table>

</html>
