<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="{{ public_path('css/wkhtml.css') }}" media="all" />
    <title>Boleta de Pago Logistica</title>
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

        .footer {
            position: fixed;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
        }

        .footer img {
            width: 100px;
            height: auto;
        }
    </style>
</head>

<body style="border:none">
    <div class="columna">
        <div class="page-break">
            <table class="w-100">
                <tr>
                    <th class="w-20 text-left no-padding no-margins align-middle text-center">
                        <img src="{{ public_path('images/bicentenarioLogo.png') }}" style="width: 165px; height: 25px;">
                        <img src="{{ public_path('images/logoEmapa2.png') }}" style="width: 135px; height: 35px;">
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
                                    <td class="text-xs">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Fecha Solicitud</td>
                                    <td class="text-xs"> </td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Sistema</td>
                                    <td class="text-xs">SIE-SIGAPE</td>
                                </tr>
                            </tbody>
                        </table>
                    </th>
                </tr>
            </table>
            <p class="font-semibold uppercase text-center " style="font-size:14px; ">BOLETA DE PAGO - LOGISTICA</p>
            <br>
            <p class="font-semibold uppercase" style="font-size:14px;">DATOS DE LA SOLICITUD</p>
            <table class="table-info align-top no-padding no-margins border">
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white text-center" colspan="2">Codigo Solicitud</td>
                    <td class="text-xs uppercase text-center" colspan="4">{{$solicitud->solicitud_id}}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Fecha Emision</td>
                    <td class="text-xs uppercase text-center">
                    {{ $date}}
                    </td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Planta</td>
                    <td class="text-xs uppercase text-center">{{ $solicitud->planta_origen->nombre ?? '' }}</td>

                    </td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Origen</td>
                    <td class="text-xs uppercase text-center">
                        {{ $solicitud->origen->nombre ?? '' }}
                    </td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Resp. Origen</td>
                    <td class="text-xs uppercase text-center">
                        {{ $nombre_responsable_origen }}
                    </td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Destino</td>
                    <td class="text-xs uppercase text-center">
                        {{ $solicitud->destino->nombre ?? '' }}
                    </td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Resp. Destino</td>
                    <td class="text-xs uppercase text-center">
                        {{ $nombre_responsable_destino }}
                    </td>
                </tr>
            </table>
            <p class="font-semibold uppercase" style="font-size:14px; ">DETALLE DE PRODUCTOS</p>
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
                    <?php
                    $count = 1;
                    ?>
                    @foreach($solicitud_detalle as $solicitud)
                    <tr class="text-xs">
                        <td class="text-xs uppercase text-center">{{$count++}}</td>
                        <td class="text-xs uppercase text-center">{{$solicitud->articulo->nombre_producto ?? ''}}</td>
                        <td class="text-xs uppercase text-center">{{$solicitud->articulo->unidad_medida->nombre ?? ''}}</td>
                        <td class="text-xs uppercase text-center">{{$solicitud->cantidad ?? ''}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="font-semibold uppercase" style="font-size:14px; ">DATOS DEL TRASPORTE</p>
            <table class="table-info align-top no-padding no-margins border">
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white text-center" colspan="2">Distribuidora</td>
                    <td class="text-xs uppercase text-center" colspan="4">{{ $solicitud_logistica->first()->distribuidora->nombre ?? 'N/A' }}</td>
                </tr>
            <table class="table-info border padding">
                <thead class="bg-grey-darker">
                    <tr class="font-medium text-white text-sm">
                        <td class="px-15 py text-center text-xs ">
                            Nro.
                        </td>
                        <td class="px-15 py text-center text-xs">
                            Codigo Logistica
                        </td>
                        <td class="px-15 py text-center text-xs">
                            Placa
                        </td>
                        <td class="px-15 py text-center text-xs">
                            Conductor
                        </td>
                        <td class="px-15 py text-center text-xs">
                            Precio Flete
                        </td>
                        <td class="px-15 py text-center text-xs">
                            Dias Retraso
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    $totalPrecioFlete = 0;
                    ?>
                    @foreach($solicitud_logistica as $solicitud)
                    <tr class="text-xs">
                        <td class="text-xs uppercase text-center">{{$count++}}</td>
                        <td class="text-xs uppercase text-center">{{$solicitud->codigo_boleta ?? ''}}</td>
                        <td class="text-xs uppercase text-center">{{$solicitud->vehiculo->placa ?? ''}}</td>
                        <td class="text-xs uppercase text-center">{{$solicitud->conductor->nombre_completo ?? '' }}</td>
                        <td class="text-xs uppercase text-center">{{$solicitud->precio_flete ?? '0' }}</td>
                        <td class="text-xs uppercase text-center">{{$solicitud->dias_retraso ?? ''}}</td>
                    </tr>
                    <?php
                    $totalPrecioFlete += $solicitud->precio_flete ?? 0;
                    ?>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-medium  text-white text-sm text-center">
                        <td colspan="3" class="text-right px-15 py text-xs bg-grey-darker text-center">
                            Total
                        </td>
                        <td colspan="3" class="text-center px-15 py text-xs text-black">
                            {{ number_format($totalPrecioFlete, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <table class="w-100">
            <tr>
                <td class="w-33 text-center text-xxs subtitulo">Firma:
                    ............................................................</td>
                <td class="w-33 text-center text-xxs subtitulo">Firma:
                    ............................................................</td>
                <td class="w-33 text-center text-xxs subtitulo">Firma:
                    ............................................................</td>
            </tr>
            <tr>
                <th class="w-33 text-center text-xxs subtitulo">RESPONSABLE</th>
                <th class="w-33 text-center text-xxs subtitulo">RESPONSABLE DE UNIDAD LOGISTICA</th>
                <th class="w-33 text-center text-xxs subtitulo">RESPONSABLE</th>
            </tr>
        </table>
    </div>
</body>


</html>