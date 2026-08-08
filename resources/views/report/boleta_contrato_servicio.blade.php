<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="{{ public_path('css/wkhtml.css') }}" media="all" />
    <title>CONTRATO</title>
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
                        <span>

                        </span>
                        <span class="font-semibold uppercase leading-tight " style="font-size:14px">
                            {{ $institution ?? 'EMPRESA DE APOYO A LA PRODUCCION DE ALIMENTOS' }} <br>
                        </span>
                    </th>
                    <th class="w-25 no-padding  align-center">
                        <table class="table-code align-top no-padding no-margins">
                            <tbody>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Fecha</td>
                                    <td class="text-xs">{{ $date }}</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Sistema</td>
                                    <td class="text-xs">SIE-SIGAPE</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Usuario</td>
                                    <td style="font-size: 8px;">{{$user_->name}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </th>
                </tr>
            </table>
            <table>
                <tr class="px-15 py text-center text-m">
                    <td class="font-bold text-center text-m" colspan="6">{{$title}}</td>
                </tr>
            </table>
            <br>
            <br>
            <table class="text-center table-info align-top no-padding no-margins border">
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Nro de Contrato</td>
                    <td class="text-xs uppercase">{{ $contrato->nro_proceso_contrato ?? '' }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Nro de Proceso Interno</td>
                    <td class="text-xs uppercase">{{ $contrato->nro_proceso_interno ?? '' }}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Fecha Inicio de Solicitud</td>
                    <td class="text-xs uppercase">{{ $contrato->fecha_inicio ?? '' }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Fecha Fin de Solicitud</td>
                    <td class="text-xs uppercase">{{ $contrato->fecha_final ?? '' }}</td>
                </tr>
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white " >Materia Prima</td>
                    <td class="text-xs uppercase" >{{ $tipo_producto->param_nombre ?? '' }}</td>
                    <td class="text-center bg-grey-darker text-xs text-white ">Producto</td>
                    <td class="text-xs uppercase">{{ $articulo->nombre_producto ?? '' }}</td>
                </tr>
                <tr>
                   
                </tr>
            </table>
            <br>
            @if($contrato->tipo_servicio_id == 3)
            <table>
                <tr class="px-15 py text-center text-xxs">
                    <td>
                        <table class="table-info w-100">
                            <thead class="bg-grey-darker">
                                <tr class="font-medium text-white text-sm">
                                    <td class="px-15 py text-center text-xs">TIPO DE CONTRATO</td>
                                    <td class="px-15 py text-center text-xs">ORIGEN</td>
                                    <td class="px-15 py text-center text-xs">DESTINO</td>
                                    <td class="px-15 py text-center text-xs">Cantidad Solicitada</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detalle as $detalle_contrato)
                                <tr class="text-center text-xs">
                                    <td class="px-15 py text-center text-xs">{{ $tipo_contrato->param_nombre ?? '' }}</td>
                                    <td class="px-15 py text-center text-xs">{{ $detalle_contrato->origen_nombre ?? '' }}</td>
                                    <td class="px-15 py text-center text-xs">{{ $detalle_contrato->destino_nombre ?? '' }}</td>
                                    <td class="px-15 py text-center text-xs">{{ $detalle_contrato->cantidad ?? '' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
            @else
            <table>
                <tr class="px-15 py text-center text-xxs">
                    <td>
                        <table class="table-info w-100">
                            <thead class="bg-grey-darker">
                                <tr class="font-medium text-white text-sm">
                                    <td class="px-15 py text-center text-xs">TIPO DE CONTRATO</td>
                                    <td class="px-15 py text-center text-xs">ORIGEN</td>
                                    <td class="px-15 py text-center text-xs">DESTINO</td>
                                    <td class="px-15 py text-center text-xs">Cantidad Solicitada</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-center text-xs">
                                    <td class="px-15 py text-center text-xs">{{ $tipo_contrato->param_nombre ?? '' }}</td>
                                    <td class="px-15 py text-center text-xs">{{ $origen->nombre ?? '' }}</td>
                                    <td class="px-15 py text-center text-xs">{{ $destino->nombre ?? '' }}</td>
                                    <td class="px-15 py text-center text-xs">{{ $contrato->cantidad ?? '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
            @endif
            <br>
            <br>
            <table class="text-center table-info align-top no-padding no-margins border">
                <tr>
                    <td class="text-center bg-grey-darker text-xs text-white ">Observacion </td>
                    <td class="text-xs uppercase text-center">{{ $contrato->observacion ?? '' }}</td>
                </tr>
            </table>
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