<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ public_path('css/wkhtml.css') }}" media="all" />
</head>

<body style="border:none">
    <div style=" padding: 0px;">
        <div class="page-break">
            <table class="w-100">
                <tr>
                    <th class="w-15 text-left no-padding no-margins align-middle">
                        <img src="{{ public_path('img/bicentenarioLogo.png') }}" style=" width: 148px; height: 25px;">
                        <img src="{{ public_path('img/logoEmapa2.png') }}" style=" width: 148px; height: 42px;">
                    </th>
                    <th class="w-50 align-center text-center">
                        <span class="font-semibold uppercase leading-tight text-xs">
                            {{ $institution ?? 'EMPRESA DE APOYO A LA PRODUCCION DE ALIMENTOS - EMAPA' }} <br>
                            {{ $dependencia ?? '' }} <br>
                            {{ $title }}
                        </span>
                    </th>
                    <th class="w-20 no-padding  align-center">

                        <table class="table-code align-top no-padding no-margins">
                            <tbody>
                                @if(isset($date))
                                <tr>

                                    <td class="text-center bg-grey-darker text-xxs text-white">Emisión</td>
                                    <td class="text-xs text-center">{{ $date }}</td>
                                </tr>
                                @endif
                                @if(isset($username))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Usuario</td>
                                    <td class="text-xs text-center">{!! $username !!}</td>
                                </tr>
                                @endif
                                @if(isset($code))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Codigo</td>
                                    <td class="text-xs text-center">{!! $code !!}</td>
                                </tr>
                                @endif
                                @if(isset($serie))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Serie</td>
                                    <td class="text-xs text-center">{!! $serie !!}</td>
                                </tr>
                                @endif
                                @if(isset($numero_serie))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Nº</td>
                                    <td class="text-xs text-center">{!! $numero_serie !!}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">SISTEMA</td>
                                    <td class="text-xs text-center">SIE-GPI</td>
                                </tr>
                            </tbody>
                        </table>

                    </th>
                </tr>
                <tr class="no-border">
                    <td colspan="3" class="no-border" style="border-bottom: 1px solid #22292f;"></td>
                </tr>
            </table>
            <div class="block">
                @yield('content')
            </div>
            <img src="{{ public_path('img/tijera1.png') }}" style=" width: 60px; height: 10px;">
 -----------------------------------------------------------------------------------------------------------------------------------------------
            <br>
            <table class="w-100">
                <tr>
                    <th class="w-15 text-left no-padding no-margins align-middle">
                        <img src="{{ public_path('img/bicentenarioLogo.png') }}" style=" width: 148px; height: 25px;">
                        <img src="{{ public_path('img/logoEmapa2.png') }}" style=" width: 148px; height: 42px;">
                    </th>
                    <th class="w-50 align-center text-center">
                        <span class="font-semibold uppercase leading-tight text-xs">
                            {{ $institution ?? 'EMPRESA DE APOYO A LA PRODUCCION DE ALIMENTOS - EMAPA' }} <br>
                            {{ $dependencia ?? '' }} <br>
                            COPIA DEL ORIGINAL: {{ $title }}
                        </span>
                    </th>
                    <th class="w-20 no-padding  align-center">

                        <table class="table-code align-top no-padding no-margins">
                            <tbody>
                                @if(isset($date))
                                <tr>

                                    <td class="text-center bg-grey-darker text-xxs text-white">Emisión</td>
                                    <td class="text-xs text-center">{{ $date }}</td>
                                </tr>
                                @endif
                                @if(isset($username))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Usuario</td>
                                    <td class="text-xs text-center" style="font-size: 8px">{!! $username !!}</td>
                                </tr>
                                @endif
                                @if(isset($code))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Codigo</td>
                                    <td class="text-xs text-center">{!! $code !!}</td>
                                </tr>
                                @endif
                                @if(isset($serie))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Serie</td>
                                    <td class="text-xs text-center">{!! $serie !!}</td>
                                </tr>
                                @endif
                                @if(isset($numero_serie))
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">Nº</td>
                                    <td class="text-xs text-center">{!! $numero_serie !!}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-center bg-grey-darker text-xxs text-white">SISTEMA</td>
                                    <td class="text-xs text-center">SIE-GPI</td>
                                </tr>
                            </tbody>
                        </table>

                    </th>
                </tr>
                <tr class="no-border">
                    <td colspan="3" class="no-border" style="border-bottom: 1px solid #22292f;"></td>
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