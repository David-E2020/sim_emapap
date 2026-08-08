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
                                    <td class="text-xs">{!! $datosImpresion->cod_logistica !!}</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Fecha Asignacion</td>
                                    <td class="text-xs">{{ $datosImpresion->fec_asignacion }}</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Sistema</td>
                                    <td class="text-xs">SIE-SIGAPE</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Tipo Solicitud</td>
                                    <td class="text-xs">{{ $datosImpresion->param_nombre }}</td>
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
            <table class="table-info align-top no-padding no-margins border mx-2">
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
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->resp_origen }}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Destino</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->destino }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Resp. de Recepcion</td>
                    <td class="text-xs uppercase text-center">{{ $datosImpresion->resp_destino }}</td>
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


            <p class="font-semibold uppercase" style="font-size:14px; ">DATOS DEL TRASPORTE:</p>
            <table class="table-info w-100">
                <thead class="bg-grey-darker">
                    <tr class="font-medium text-white text-sm">
                        <td class="px-5 py text-center text-xs ">
                            Nro.
                        </td>
                        <td class="px-15 py text-center text-xs">
                            Codigo Sub Boleta
                        </td>
                        <td class="px-20 py text-center text-xs">
                            Trasnportadora
                        </td>
                        <td class="px-15 py text-center text-xs">
                            Placa de Vehiculo
                        </td>
                        <td class="px-15 py text-center text-xs">
                            CI Conductor
                        </td>
                        <td class="px-20 py text-center text-xs">
                            Nombre Conductor
                        </td>
                        <td class="px-10 py text-center text-xs">
                            Cantidad de Carga
                        </td>

                    </tr>
                </thead>
                <tbody>
                    @php
                    $totalCantidadCarga = 0;
                    @endphp
                    @foreach (json_decode($datosImpresion->transporte) as $index => $item)
                        <tr class="text-sm">
                            <td class="text-center text-xs uppercase font-bold px-5 py-3">{{ $index + 1 }}</td>
                            <td class="text-center text-xs uppercase font-bold px-5 py-3">
                                {{ $item->sub_codigo_boleta }}</td>
                            <td class="text-center text-xs uppercase font-bold px-5 py-3">
                                {{ $item->distribuidora }}</td>
                            <td class="text-center text-xs uppercase font-bold px-5 py-3">
                                {{ $item->veh_placa }}</td>
                            <td class="text-center text-xs uppercase font-bold px-5 py-3">
                                {{ $item->con_ci }}</td>
                            <td class="text-center text-xs uppercase font-bold px-5 py-3">
                                {{ $item->con_nombre }}</td>

                            <td class="text-center text-xs uppercase font-bold px-5 py-3">

                                @if ($item->cantidad_distribucion == 0)
                                    ---
                                @else
                                    {{ $item->cantidad_distribucion }}
                                @endif

                            </td>
                        </tr>
                        @php
                        $totalCantidadCarga += $item->cantidad_distribucion;
                        @endphp
                    @endforeach
                </tbody>
            </table>
            <table class="table-info align-top no-padding no-margins border mx-2">
            <tr class="text-sm">
                <td colspan="4" class="text-center bg-grey-darker text-xs text-white ">
                    <strong>OBSERVACIONES:</strong>
                <td class="text-xs uppercase text-center" colspan="8">{{ $datosImpresion->observaciones_log }}</td>
            </tr>
            <tr class="text-sm">
                <td class="text-center bg-grey-darker text-xs text-white " colspan="8">Total Cantidad</td>
                <td class="text-xs uppercase text-center" colspan="4">
                    {{ $totalCantidadCarga }}
                </td>
            </tr>
            </table>
            <table class="text-right">
                <td style="width: 90%">
                    <table class="text-right">
                        <tr>
                            <td class="text-xxs text-right" style="text-align: right; width: 83%"><b>Fecha
                                    Impresion:</b>
                                {{ date('Y-m-d') }}</td>
                        </tr>
                        <tr>
                            <td class="text-xxs text-right" style="text-align: right; width: 83%"><b>Asignador:</b>
                                {{ $datosImpresion->asignador_usu }}</td>
                        </tr>
                        <tr>
                            <td class="text-xxs text-right" style="text-align: right; width: 83%"><b>Impreso por:</b>
                                {{ Auth::user()->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-xxs text-right" style="text-align: right; width: 83%"><b>Nro:</b>
                                {{ $datosImpresion->num_logistica }}</td>
                        </tr>
                    </table>
                </td>
                <td class="align-right" style="width: 10%">
                    {!! QrCode::format('svg')->size(125)->errorCorrection('H')->color(0, 0, 0)->generate(
                            implode(' | ', [
                                $datosImpresion->origen,
                                $datosImpresion->destino,
                                $datosImpresion->num_logistica,
                                $datosImpresion->codigo_solicitud,
                                $datosImpresion->asignador_usu,
                            ]),
                        ) !!}
                </td>
            </table>
            <br>
            <br>
            <br>
            <br>
            <br>
            <table class="w-100">
                <tr>
                    <td class="w-50 text-center text-xxs subtitulo">Firma:
                        .....................................................................................</td>
                    <td class="w-50 text-center text-xxs subtitulo">Firma:
                        .....................................................................................</td>
                </tr>
                <tr>
                    <th class="w-50 text-center text-xxs subtitulo">{{ $datosImpresion->asignador_nombre }}</th>
                    <th class="w-50 text-center text-xxs subtitulo">RESPONSABLE DE UNIDAD</th>
                </tr>
                <tr>
                    <td class="w-50 text-center text-xxs subtitulo">ASIGNADOR</td>
                    <td class="w-50 text-center text-xxs subtitulo"></td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
