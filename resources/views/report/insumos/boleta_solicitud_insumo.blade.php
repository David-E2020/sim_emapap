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
                             
                        </span>
                    </th>
                    <th class="w-25 no-padding  align-center">
                        <table class="table-code align-top no-padding no-margins">
                            <tbody>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Nº de Codigo</td>
                                    
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Fecha Asignacion</td>
                                    
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
                <tr class="px-15 py text-center text-xxs">
                    <td class="font-bold text-center text-xs" colspan="6">DETALLE DE PRODUCTOS SOLICITADOS</td>
                </tr>
            </table>
            
            <table class="w-100">
                <tr>
                    <td class="w-50 text-center text-xxs subtitulo">NOMBRE DEL PRODUCTO</td>
                    <td class="w-50 text-center text-xxs subtitulo">CANTIDAD</td>
                </tr>
                @foreach ($detalle as $detalle)
                    <tr>
                        
                        
                    </tr>
                @endforeach
            <br>
        
     
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
