<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ public_path('css/wkhtml.css') }}" media="all" />
</head>

<body style="border:none">
    <div style=" padding: 35px;">
        <div class="page-break">
            <table class="w-100 ">
                <tr>
                    <th class="w-15 text-left no-padding no-margins align-middle">
                        <img src="{{ public_path('img/bicentenarioLogo.png') }}" style=" width: 128px; height: 25px;">
                        <img src="{{ public_path('img/logoEmapa2.png') }}" style=" width: 128px; height: 42px;">
                    </th>
                    <th class="w-50 align-center text-center">
                        <span class="font-semibold uppercase leading-tight text-xxs">
                            {{ $institution ?? 'EMPRESA DE APOYO A LA PRODUCCION DE ALIMENTOS - EMAPA' }} <br>
                            {{ $dependencia ?? '' }} <br>
                        </span>
                    </th>
                    <th class="w-20 no-padding  align-center">

                        <table class="table-code align-top no-padding no-margins">
                            <tbody>
                                @if(isset($date))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Fecha de Emisión</td>
                                    <td class="text-xs">{{ $date }}</td>
                                </tr>
                                @endif
                                @if(isset($username))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Usuario</td>
                                    <td class="text-xs">{!! $username !!}</td>
                                </tr>
                                @endif
                                @if(isset($code))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Codigo</td>
                                    <td class="text-xs">{!! $code !!}</td>
                                </tr>
                                @endif
                                @if(isset($serie))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Serie</td>
                                    <td class="text-xs">{!! $serie !!}</td>
                                </tr>
                                @endif
                                @if(isset($numero_serie))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Nº</td>
                                    <td class="text-xs">{!! $numero_serie !!}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">SISTEMA</td>
                                    <td class="text-xs">SIE-SIGAPE</td>
                                </tr>
                            </tbody>
                        </table>
                    </th>
                </tr>
                <tr class="no-border">
                    <td colspan="3" class="no-border" style="border-bottom: 1px solid #22292f;"></td>
                </tr>
                <tr>
                    <td colspan="3" class=" text-center text-xl uppercase">
                        <span class="font-bold" style="font-size: 16px;">{{ $title }}</span>
                        @if (isset($subtitle))
                        <br><span class="font-medium" style="font-size: 14px;">{{ $subtitle ?? '' }}</span>
                        @endif
                        @if (isset($subtitle2))
                        <br><span class="font-medium" style="font-size: 14px;">{{ $subtitle2 ?? '' }}</span>
                        @endif
                    </td>
                </tr>
            </table>
            <div class="block">
                @yield('content')
            </div>
            <footer>
                @yield('footer')
            </footer>
        </div>
    </div>
</body>

</html>