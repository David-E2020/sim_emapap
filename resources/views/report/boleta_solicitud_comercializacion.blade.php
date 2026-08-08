<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="{{ public_path('css/wkhtml.css') }}" media="all" />
    <title>Comprobante de Soporte</title>
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
                        <img src="{{ public_path('images/bicentenarioLogo.png') }}" style="width: 165px; height: 25px;">
                        <img src="{{ public_path('images/logoEmapa2.png') }}" style="width: 135px; height: 35px;">
                    </th>
                    <th class="w-55 align-center text-center">
                        <span class="font-semibold uppercase leading-tight " style="font-size:14px">
                            {{ $institution ?? 'EMPRESA DE APOYO A LA PRODUCCION DE ALIMENTOS' }} <br>
                            {{ $direccion_unidad ?? 'GERENCIA DE COMERCIALIZACION' }} <br>
                            {{ 'COMPROBANTE DE SOLICITUD DE ORDEN DE CARGA' }} <br>
                        </span>
                    </th>
                    <th class="w-25 no-padding  align-center">
                        <table class="table-code align-top no-padding no-margins">
                            <tbody>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Codigo</td>
                                    <td class="text-xs">{!! $datosImpresion->op_codigo_solicitud !!}</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Fecha Solicitud</td>
                                    <td class="text-xs">{{ $datosImpresion->fecha_solicitud }}</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Sistema</td>
                                    <td class="text-xs">SIE</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Tipo Solicitud</td>
                                    <td class="text-xs">ORDEN DE CARGA</td>
                                </tr>
                            </tbody>
                        </table>
                    </th>
                </tr>
            </table>
            <br>
            <table class="table-info align-top no-padding no-margins border">
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Origen Solicitud</td>
                    <td class="text-xs uppercase">{{ $datosImpresion->origen }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Destino Solicitud</td>
                    <td class="text-xs uppercase">{{ $datosImpresion->destino }}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Tipo Solicitud de Orden de Carga</td>
                    <td class="text-xs uppercase">{{ $datosImpresion->param_nombre }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Solicitante</td>
                    <td class="text-xs uppercase">{{ $datosImpresion->name }}</td>
                </tr>
            </table>
            <h4 style="text-align: center;"><b>DETALLE DE PRODUCTOS</b></h4>

            <table class="table-info w-100">
                <thead class="bg-grey-darker">
                    <tr class="font-medium text-white text-sm">
                        <td class="px-15 py text-center text-xxs ">
                            Nro.
                        </td>
                        <td class="px-15 py text-center text-xxs">
                            Nombre del Producto
                        </td>
                        <td class="px-15 py text-center text-xxs">
                            Unidad de Medida
                        </td>
                        <td class="px-15 py text-center text-xxs">
                            Cantidad Solicitado
                        </td>
                    </tr>
                </thead>
                <tbody>
                    @foreach (json_decode($datosImpresion->detalles) as $index => $item)
                        <tr class="text-sm">
                            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $index + 1 }}</td>
                            <td class="text-center text-xxs uppercase font-bold px-5 py-3">
                                {{ $item->nombre }}</td>
                            <td class="text-center text-xxs uppercase font-bold px-5 py-3">
                                {{ $item->unidad_med }}</td>
                            <td class="text-center text-xxs uppercase font-bold px-5 py-3">
                                {{ $item->cantidad_solicitada }}</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
