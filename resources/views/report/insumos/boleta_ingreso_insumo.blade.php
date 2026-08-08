<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="{{ public_path('css/wkhtml.css') }}" media="all" />
    <title>Boleta de Ingreso</title>
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
                            {{ $institution ?? 'EMPRESA DE APOYO A LA PRODUCCION DE ALIMENTOS - EMAPA' }} <br>
                            BOLETA DE INGRESO DE INSUMOS
                        </span>
                    </th>
                    <th class="w-25 no-padding  align-center">
                        <table class="table-code align-top no-padding no-margins">
                            <tbody>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Nº de Codigo</td>
                                    <td class="text-xs">#</td>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Fecha Asignacion</td>
                                    <td>
                                        {{ (new DateTime($ingreso->fecha_ingreso))->format('d/m/Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Sistema</td>
                                    <td class="text-xs">SIE-SIGAPE</td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Tipo Solicitud</td>

                                </tr>
                            </tbody>
                        </table>
                    </th>
                </tr>
            </table>
            <table>
                <tr class="px-15 py text-center text-m">
                    <td class="font-bold text-center text-m" colspan="6">BOLETA INGRESO DE INSUMOS</td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>
                        <table class="table-info align-top no-padding no-margins border mx-2">
                            <tr>
                                <td class="w-50 text-center text-xxs subtitulo">NOMBRE DEL SOLICITANTE</td>
                                <td class="w-50 text-center text-xxs subtitulo">CARGO</td>
                            </tr>
                            <tr>

                            </tr>
                        </table>
                    </td>
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
                        <td class="px-15 py text-center text-xs ">Nº</td>
                        <td class="px-15 py text-center text-xs ">NOMBRE DEL PRODUCTO</td>
                        <td class="px-15 py text-center text-xs ">CANTIDAD</td>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $count = 0;
                    $totalCantidad = 0;
                    @endphp
                    @foreach ($ingreso->ingreso_detalle as $detalle)

                    <tr class="text-sm">
                        <td class="text-center text-xs uppercase">{{ $count += 1 }}</td>
                        <td class="text-center text-xs uppercase">{{ $detalle->articulos->nombre_producto }}</td>
                        <td class="text-center text-xs uppercase">{{ $detalle->cantidad }}</td>
                    </tr>
                    @php
                    $totalCantidad += $detalle->cantidad;
                    @endphp
                    @endforeach
                    <table class="table-info align-top no-padding no-margins border mx-2">
                        <tr class="text-sm">
                            <td class="text-center bg-grey-darker text-xs text-white " colspan="3">
                                Total Articulos
                            </td>
                            <td class="text-xs uppercase text-center" colspan="3">
                                <strong>{{ $totalCantidad }}</strong>
                            </td>
                        </tr>
                    </table>
                </tbody>
            </table>
            <br>
            <p>

            </p>
            <br>
            <br>
            <br>
            <br>
            <br>
            <table class="table-reporte">
                <tr style="height: 80px;">
                    <td class="text-xs" style="vertical-align: bottom; text-align: center; width: 25%" rowspan="2">
                        Firma o Sello del Solicitante:
                    </td>
                    <td class="text-xs" style="vertical-align: bottom; text-align: center; width: 25%" rowspan="2">
                        Firma o Sello de Autorizacion
                    </td>
                    <td class="text-xs" style="vertical-align: bottom; text-align: center; width: 25%" rowspan="2">
                        SELLO Y FIRMA:
                    </td>
                    <td class="text-xs" style="vertical-align: bottom; text-align: center; width: 25%" rowspan="2">
                        SELLO Y FIRMA:
                    </td>
                </tr>
                <tr>
                </tr>
                <tr>
                    <td class="text-center text-xs font-bold" style="text-align: center">
                        ELABORADO - PERSONAL EMAPA
                    </td>

                    <td class="text-center text-xs font-bold" style="text-align: center">
                        
                    </td>

                    <td class="text-center text-xs font-bold" style="text-align: center">
                        Entrogado Por:
                    </td>

                    <td class="text-center text-xs font-bold" style="text-align: center">
                        Recibi Conforme:
                    </td>
                </tr>
            </table>

            <table class="saltopagina">
                <tr>
                    <td class="text-left text-xxs">
                        Fecha Impresión: <br>
                        Usuario:
                    </td>
                    <td class="text-left text-xxs"></td>
                    <td class="text-right text-xxs"></td>
                    <td class="text-right text-xxs">

                    </td>
                </tr>
            </table>

        </div>
    </div>
</body>

</html>