@extends('layouts.print')

@section('content')
<br>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white ">Fecha Reporte:</td>
        <td class="text-xs uppercase text-center">{{$dateImp}}</td>
        <td class="text-center bg-grey-darker text-xs text-white">Gestion Consulta:</td>
        <td class="text-xs uppercase text-center">{{$gestion ?? ''}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Tipo Programa:</td>
        <td class="text-xs uppercase text-center"><b>{{$tipo_programa->prog_nombre ?? 'TODOS'}}</b></td>
        <td class="text-center bg-grey-darker text-xs text-white">Tipo Producto:</td>
        <td class="text-xs uppercase text-center">{{$tipo_producto->param_nombre?? ''}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Mes:</td>
        <td class="text-xs uppercase text-center">{{$mes->param_nombre ?? ''}}</td>
        <td class="text-center bg-grey-darker text-xs text-white">Producto:</td>
        <td class="text-xs uppercase text-center">{{$articulos->nombre ?? ''}}</td>
    </tr>
</table>
<br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td class="px-6 py text-center text-xxs ">
                Nro.
            </td>
            <td class="px-6 py text-center  text-xxs uppercase">
                Gestion
            </td>
            <td class="px-6 py text-center  text-xxs uppercase">
                Mes
            </td>
            <td class="px-6 py text-center  text-xxs uppercase">
                Region
            </td>
            <td class="px-6 py text-center  text-xxs">
                Sucursal
            </td>
            @if($diaInicio <= 1 && $diaFin >= 1)
            <td class="px-6 py text-center  text-xxs uppercase">
                01
            </td>
            @endif
            @if($diaInicio <= 2 && $diaFin >= 2)
            <td class="px-6 py text-center  text-xxs uppercase">
                02
            </td>
            @endif
            @if($diaInicio <= 3 && $diaFin >= 3)
            <td class="px-16 py text-center  text-xxs uppercase">
                03
            </td>
            @endif
            @if($diaInicio <= 4 && $diaFin >= 4)
            <td class="px-6 py text-center  text-xxs uppercase">
                04
            </td>
            @endif
            @if($diaInicio <= 5 && $diaFin >= 5)
            <td class="px-6 py text-center  text-xxs uppercase">
                05
            </td>
            @endif
            @if($diaInicio <= 6 && $diaFin >= 6)
            <td class="px-6 py text-center  text-xxs uppercase">
                06
            </td>
            @endif
            @if($diaInicio <= 7 && $diaFin >= 7)
            <td class="px-6 py text-center  text-xxs uppercase">
                07
            </td>
            @endif
            @if($diaInicio <= 8 && $diaFin >= 8)
            <td class="px-6 py text-center  text-xxs uppercase">
                08
            </td>
            @endif
            @if($diaInicio <= 9 && $diaFin >= 9)
            <td class="px-6 py text-center  text-xxs uppercase">
                09
            </td>
            @endif
            @if($diaInicio <= 10 && $diaFin >= 10)
            <td class="px-6 py text-center  text-xxs uppercase">
                10
            </td>
            @endif
            @if($diaInicio <= 11 && $diaFin >= 11)
            <td class="px-6 py text-center  text-xxs uppercase">
                11
            </td>
            @endif
            @if($diaInicio <= 12 && $diaFin >= 12)
            <td class="px-6 py text-center  text-xxs uppercase">
                12
            </td>
            @endif
            @if($diaInicio <= 13 && $diaFin >= 13)
            <td class="px-6 py text-center  text-xxs uppercase">
                13
            </td>
            @endif
            @if($diaInicio <= 14 && $diaFin >= 14)
            <td class="px-6 py text-center  text-xxs uppercase">
                14
            </td>
            @endif
            @if($diaInicio <= 15 && $diaFin >= 15)
            <td class="px-6 py text-center  text-xxs uppercase">
                15
            </td>
            @endif
            @if($diaInicio <= 16 && $diaFin >= 16)
            <td class="px-6 py text-center  text-xxs uppercase">
                16
            </td>
            @endif
            @if($diaInicio <= 17 && $diaFin >= 17)
            <td class="px-6 py text-center  text-xxs uppercase">
                17
            </td>
            @endif
            @if($diaInicio <= 18 && $diaFin >= 18)
            <td class="px-6 py text-center  text-xxs uppercase">
                18
            </td>
            @endif
            @if($diaInicio <= 19 && $diaFin >= 19)
            <td class="px-6 py text-center  text-xxs uppercase">
                19
            </td>
            @endif
            @if($diaInicio <= 20 && $diaFin >= 20)
            <td class="px-6 py text-center  text-xxs uppercase">
                20
            </td>
            @endif
            @if($diaInicio <= 21 && $diaFin >= 21)
            <td class="px-6 py text-center  text-xxs uppercase">
                21
            </td>
            @endif
            @if($diaInicio <= 22 && $diaFin >= 22)
            <td class="px-6 py text-center  text-xxs uppercase">
                22
            </td>
            @endif
            @if($diaInicio <= 23 && $diaFin >= 23)
            <td class="px-6 py text-center  text-xxs uppercase">
                23
            </td>
            @endif
            @if($diaInicio <= 24 && $diaFin >= 24)
            <td class="px-6 py text-center  text-xxs uppercase">
                24
            </td>
            @endif
            @if($diaInicio <= 25 && $diaFin >= 25)
            <td class="px-6 py text-center  text-xxs uppercase">
                25
            </td>
            @endif
            @if($diaInicio <= 26 && $diaFin >= 26)
            <td class="px-6 py text-center  text-xxs uppercase">
                26
            </td>
            @endif
            @if($diaInicio <= 27 && $diaFin >= 27)
            <td class="px-6 py text-center  text-xxs uppercase">
                27
            </td>
            @endif
            @if($diaInicio <= 28 && $diaFin >= 28)
            <td class="px-6 py text-center  text-xxs uppercase">
                28
            </td>
            @endif
            @if($diaInicio <= 29 && $diaFin >= 29)
            <td class="px-6 py text-center  text-xxs uppercase">
                29
            </td>
            @endif
            @if($diaInicio <= 30 && $diaFin >= 30)
            <td class="px-6 py text-center  text-xxs uppercase">
                30
            </td>
            @endif
            @if($diaInicio <= 31 && $diaFin >= 31)
            <td class="px-6 py text-center  text-xxs uppercase">
                31
            </td>
            @endif
                <td class="py text-center  text-xxs uppercase">
                    Total
                </td>

        </tr>
    </thead>
    <tbody>
        @php
        $nro = 1;
        
        $total_01 = 0;
        $total_02 = 0;
        $total_03 = 0;
        $total_04 = 0;
        $total_05 = 0;
        $total_06 = 0;
        $total_07 = 0;
        $total_08 = 0;
        $total_09 = 0;
        $total_10 = 0;
        $total_11 = 0;
        $total_12 = 0;
        $total_13 = 0;
        $total_14 = 0;
        $total_15 = 0;
        $total_16 = 0;
        $total_17 = 0;
        $total_18 = 0;
        $total_19 = 0;
        $total_20 = 0;
        $total_21 = 0;
        $total_22 = 0;
        $total_23 = 0;
        $total_24 = 0;
        $total_25 = 0;
        $total_26 = 0;
        $total_27 = 0;
        $total_28 = 0;
        $total_29 = 0;
        $total_30 = 0;
        $total_31 = 0;
        $total_datos_raw = 0;

        $departamento = '';
        @endphp
        
        @foreach( $departamentos as $value)
            @php
                $total_parcial_01 = 0;
                $total_parcial_02 = 0;
                $total_parcial_03 = 0;
                $total_parcial_04 = 0;
                $total_parcial_05 = 0;
                $total_parcial_06 = 0;
                $total_parcial_07 = 0;
                $total_parcial_08 = 0;
                $total_parcial_09 = 0;
                $total_parcial_10 = 0;
                $total_parcial_11 = 0;
                $total_parcial_12 = 0;
                $total_parcial_13 = 0;
                $total_parcial_14 = 0;
                $total_parcial_15 = 0;
                $total_parcial_16 = 0;
                $total_parcial_17 = 0;
                $total_parcial_18 = 0;
                $total_parcial_19 = 0;
                $total_parcial_20 = 0;
                $total_parcial_21 = 0;
                $total_parcial_22 = 0;
                $total_parcial_23 = 0;
                $total_parcial_24 = 0;
                $total_parcial_25 = 0;
                $total_parcial_26 = 0;
                $total_parcial_27 = 0;
                $total_parcial_28 = 0;
                $total_parcial_29 = 0;
                $total_parcial_30 = 0;
                $total_parcial_31 = 0;
                $total_parcial_datos_raw = 0;
            @endphp
            @foreach( $ventas as $data)
                @if($value->dep_nombre  ==  $data->region)
                    <tr class="text-sm">
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $nro++ }}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->gestion ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->mes ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->region ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->sucursal ?? '-'}}</td>
                        @php 
                            $data01 = $data->d01;
                            $data02 = $data->d02;
                            $data03 = $data->d03;
                            $data04 = $data->d04;
                            $data05 = $data->d05;
                            $data06 = $data->d06;
                            $data07 = $data->d07;
                            $data08 = $data->d08;
                            $data09 = $data->d09;
                            $data10 = $data->d10;
                            $data11 = $data->d11;
                            $data12 = $data->d12;
                            $data13 = $data->d13;
                            $data14 = $data->d14;
                            $data15 = $data->d15;
                            $data16 = $data->d16;
                            $data17 = $data->d17;
                            $data18 = $data->d18;
                            $data19 = $data->d19;
                            $data20 = $data->d20;
                            $data21 = $data->d21;
                            $data22 = $data->d22;
                            $data23 = $data->d23;
                            $data24 = $data->d24;
                            $data25 = $data->d25;
                            $data26 = $data->d26;
                            $data27 = $data->d27;
                            $data28 = $data->d28;
                            $data29 = $data->d29;
                            $data30 = $data->d30;
                            $data31 = $data->d31;
                        @endphp
                        @if($diaInicio <= 1 && $diaFin >= 1)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d01, 2, ',', '.') ?? '-'}}</td> @else @php $data01 = 0 @endphp @endif
                        @if($diaInicio <= 2 && $diaFin >= 2)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d02, 2, ',', '.') ?? '-'}}</td> @else @php $data02 = 0 @endphp @endif
                        @if($diaInicio <= 3 && $diaFin >= 3)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d03, 2, ',', '.') ?? '-'}}</td> @else @php $data02 = 0 @endphp @endif
                        @if($diaInicio <= 4 && $diaFin >= 4)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d04, 2, ',', '.') ?? '-'}}</td> @else @php $data02 = 0 @endphp @endif
                        @if($diaInicio <= 5 && $diaFin >= 5)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d05, 2, ',', '.') ?? '-'}}</td> @else @php $data02 = 0 @endphp @endif
                        @if($diaInicio <= 6 && $diaFin >= 6)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d06, 2, ',', '.') ?? '-'}}</td> @else @php $data02 = 0 @endphp @endif
                        @if($diaInicio <= 7 && $diaFin >= 7)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d07, 2, ',', '.') ?? '-'}}</td> @else @php $data07 = 0 @endphp @endif
                        @if($diaInicio <= 8 && $diaFin >= 8)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d08, 2, ',', '.') ?? '-'}}</td> @else @php $data08 = 0 @endphp @endif
                        @if($diaInicio <= 9 && $diaFin >= 9)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d09, 2, ',', '.') ?? '-'}}</td> @else @php $data09 = 0 @endphp @endif
                        @if($diaInicio <= 10 && $diaFin >= 10)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d10, 2, ',', '.') ?? '-'}}</td> @else @php $data10 = 0 @endphp @endif
                        @if($diaInicio <= 11 && $diaFin >= 11)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d11, 2, ',', '.') ?? '-'}}</td> @else @php $data11 = 0 @endphp @endif
                        @if($diaInicio <= 12 && $diaFin >= 12)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d12, 2, ',', '.') ?? '-'}}</td> @else @php $data12 = 0 @endphp @endif
                        @if($diaInicio <= 13 && $diaFin >= 13)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d13, 2, ',', '.') ?? '-'}}</td> @else @php $data13 = 0 @endphp @endif
                        @if($diaInicio <= 14 && $diaFin >= 14)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d14, 2, ',', '.') ?? '-'}}</td> @else @php $data14 = 0 @endphp @endif
                        @if($diaInicio <= 15 && $diaFin >= 15)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d15, 2, ',', '.') ?? '-'}}</td> @else @php $data15 = 0 @endphp @endif
                        @if($diaInicio <= 16 && $diaFin >= 16)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d16, 2, ',', '.') ?? '-'}}</td> @else @php $data16 = 0 @endphp @endif
                        @if($diaInicio <= 17 && $diaFin >= 17)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d17, 2, ',', '.') ?? '-'}}</td> @else @php $data17 = 0 @endphp @endif
                        @if($diaInicio <= 18 && $diaFin >= 18)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d18, 2, ',', '.') ?? '-'}}</td> @else @php $data18 = 0 @endphp @endif
                        @if($diaInicio <= 19 && $diaFin >= 19)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d19, 2, ',', '.') ?? '-'}}</td> @else @php $data19 = 0 @endphp @endif
                        @if($diaInicio <= 20 && $diaFin >= 20)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d20, 2, ',', '.') ?? '-'}}</td> @else @php $data20 = 0 @endphp @endif
                        @if($diaInicio <= 21 && $diaFin >= 21)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d21, 2, ',', '.') ?? '-'}}</td> @else @php $data21 = 0 @endphp @endif
                        @if($diaInicio <= 22 && $diaFin >= 22)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d22, 2, ',', '.') ?? '-'}}</td> @else @php $data22 = 0 @endphp @endif
                        @if($diaInicio <= 23 && $diaFin >= 23)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d23, 2, ',', '.') ?? '-'}}</td> @else @php $data23 = 0 @endphp @endif
                        @if($diaInicio <= 24 && $diaFin >= 24)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d24, 2, ',', '.') ?? '-'}}</td> @else @php $data24 = 0 @endphp @endif
                        @if($diaInicio <= 25 && $diaFin >= 25)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d25, 2, ',', '.') ?? '-'}}</td> @else @php $data25 = 0 @endphp @endif
                        @if($diaInicio <= 26 && $diaFin >= 26)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d26, 2, ',', '.') ?? '-'}}</td> @else @php $data26 = 0 @endphp @endif
                        @if($diaInicio <= 27 && $diaFin >= 27)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d27, 2, ',', '.') ?? '-'}}</td> @else @php $data27 = 0 @endphp @endif
                        @if($diaInicio <= 28 && $diaFin >= 28)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d28, 2, ',', '.') ?? '-'}}</td> @else @php $data28 = 0 @endphp @endif
                        @if($diaInicio <= 29 && $diaFin >= 29)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d29, 2, ',', '.') ?? '-'}}</td> @else @php $data29 = 0 @endphp @endif
                        @if($diaInicio <= 30 && $diaFin >= 30)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d30, 2, ',', '.') ?? '-'}}</td> @else @php $data30 = 0 @endphp @endif
                        @if($diaInicio <= 31 && $diaFin >= 31)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->d31, 2, ',', '.') ?? '-'}}</td> @else @php $data31 = 0 @endphp @endif
                        @php
                        $total_data = $data01 + $data02 + $data03 + $data04 + $data05 + $data06 + $data07 + $data08 + $data09 + $data10
                                    + $data11 + $data12 + $data13 + $data14 + $data15 + $data16 + $data17 + $data18 + $data19 + $data20
                                    + $data21 + $data22 + $data23 + $data24 + $data25 + $data26 + $data27 + $data28 + $data29 + $data30 + $data31;
                        @endphp
                            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{number_format((float)$total_data, 2, ',', '.')}}</td>
                    </tr>
                    @php
                        $departamento = $data->region;
                        //$total_entrada += floatval($insumo->o_entrada);
                        //$total_salida += floatval($insumo->o_salida);
                        $total_01 += floatval($data->d01);
                        $total_02 += floatval($data->d02);
                        $total_03 += floatval($data->d03);
                        $total_04 += floatval($data->d04);
                        $total_05 += floatval($data->d05);
                        $total_06 += floatval($data->d06);
                        $total_07 += floatval($data->d07);
                        $total_08 += floatval($data->d08);
                        $total_09 += floatval($data->d09);
                        $total_10 += floatval($data->d10);
                        $total_11 += floatval($data->d11);
                        $total_12 += floatval($data->d12);
                        $total_13 += floatval($data->d13);
                        $total_14 += floatval($data->d14);
                        $total_15 += floatval($data->d15);
                        $total_16 += floatval($data->d16);
                        $total_17 += floatval($data->d17);
                        $total_18 += floatval($data->d18);
                        $total_19 += floatval($data->d19);
                        $total_20 += floatval($data->d20);
                        $total_21 += floatval($data->d21);
                        $total_22 += floatval($data->d22);
                        $total_23 += floatval($data->d23);
                        $total_24 += floatval($data->d24);
                        $total_25 += floatval($data->d25);
                        $total_26 += floatval($data->d26);
                        $total_27 += floatval($data->d27);
                        $total_28 += floatval($data->d28);
                        $total_29 += floatval($data->d29);
                        $total_30 += floatval($data->d30);
                        $total_31 += floatval($data->d31);
                        $total_datos_raw                += floatval($total_data);
                        $total_parcial_01 += floatval($data->d01);
                        $total_parcial_02 += floatval($data->d02);
                        $total_parcial_03 += floatval($data->d03);
                        $total_parcial_04 += floatval($data->d04);
                        $total_parcial_05 += floatval($data->d05);
                        $total_parcial_06 += floatval($data->d06);
                        $total_parcial_07 += floatval($data->d07);
                        $total_parcial_08 += floatval($data->d08);
                        $total_parcial_09 += floatval($data->d09);
                        $total_parcial_10 += floatval($data->d10);
                        $total_parcial_11 += floatval($data->d11);
                        $total_parcial_12 += floatval($data->d12);
                        $total_parcial_13 += floatval($data->d13);
                        $total_parcial_14 += floatval($data->d14);
                        $total_parcial_15 += floatval($data->d15);
                        $total_parcial_16 += floatval($data->d16);
                        $total_parcial_17 += floatval($data->d17);
                        $total_parcial_18 += floatval($data->d18);
                        $total_parcial_19 += floatval($data->d19);
                        $total_parcial_20 += floatval($data->d20);
                        $total_parcial_21 += floatval($data->d21);
                        $total_parcial_22 += floatval($data->d22);
                        $total_parcial_23 += floatval($data->d23);
                        $total_parcial_24 += floatval($data->d24);
                        $total_parcial_25 += floatval($data->d25);
                        $total_parcial_26 += floatval($data->d26);
                        $total_parcial_27 += floatval($data->d27);
                        $total_parcial_28 += floatval($data->d28);
                        $total_parcial_29 += floatval($data->d29);
                        $total_parcial_30 += floatval($data->d30);
                        $total_parcial_31 += floatval($data->d31);
                        $total_parcial_datos_raw                += floatval($total_data);
                    @endphp
                @endif
            @endforeach
            @if($value->dep_nombre  ==  $departamento)
            <tr class="text-sm">
                <td colspan="5" class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">SUB TOTAL</td>
                @if($diaInicio <= 1 && $diaFin >= 1)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_01, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 2 && $diaFin >= 2)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_02, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 3 && $diaFin >= 3)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_03, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 4 && $diaFin >= 4)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_04, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 5 && $diaFin >= 5)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_05, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 6 && $diaFin >= 6)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_06, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 7 && $diaFin >= 7)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_07, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 8 && $diaFin >= 8)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_08, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 9 && $diaFin >= 9)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_09, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 10 && $diaFin >= 10)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_10, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 11 && $diaFin >= 11)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_11, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 12 && $diaFin >= 12)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_12, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 13 && $diaFin >= 13)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_13, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 14 && $diaFin >= 14)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_14, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 15 && $diaFin >= 15)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_15, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 16 && $diaFin >= 16)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_16, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 17 && $diaFin >= 17)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_17, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 18 && $diaFin >= 18)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_18, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 19 && $diaFin >= 19)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_19, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 20 && $diaFin >= 20)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_20, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 21 && $diaFin >= 21)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_21, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 22 && $diaFin >= 22)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_22, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 23 && $diaFin >= 23)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_23, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 24 && $diaFin >= 24)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_24, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 25 && $diaFin >= 25)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_25, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 26 && $diaFin >= 26)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_26, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 27 && $diaFin >= 27)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_27, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 28 && $diaFin >= 28)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_28, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 29 && $diaFin >= 29)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_29, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 30 && $diaFin >= 30)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_30, 2, ',', '.') }}</td>@endif
                @if($diaInicio <= 31 && $diaFin >= 31)<td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_31, 2, ',', '.') }}</td>@endif

                    <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_datos_raw, 2, ',', '.') }}</td>

            </tr>
            @endif
        @endforeach
            

        <tr class="font-medium text-white text-sm"></tr>
        <tr>
            <td colspan="5" class="text-center bg-grey-darker text-xs text-white">
                TOTAL
            </td>
            @if($diaInicio <= 1 && $diaFin >= 1)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_01, 2, ',', '.') }}</td>@endif
            @if($diaInicio <= 2 && $diaFin >= 2)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_02, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 3 && $diaFin >= 3)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_03, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 4 && $diaFin >= 4)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_04, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 5 && $diaFin >= 5)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_05, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 6 && $diaFin >= 6)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_06, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 7 && $diaFin >= 7)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_07, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 8 && $diaFin >= 8)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_08, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 9 && $diaFin >= 9)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_09, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 10 && $diaFin >= 10)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_10, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 11 && $diaFin >= 11)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_11, 2, ',', '.') }}</td>@endif
            @if($diaInicio <= 12 && $diaFin >= 12)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_12, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 13 && $diaFin >= 13)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_13, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 14 && $diaFin >= 14)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_14, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 15 && $diaFin >= 15)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_15, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 16 && $diaFin >= 16)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_16, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 17 && $diaFin >= 17)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_17, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 18 && $diaFin >= 18)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_18, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 19 && $diaFin >= 19)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_19, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 20 && $diaFin >= 20)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_20, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 21 && $diaFin >= 21)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_21, 2, ',', '.') }}</td>@endif
            @if($diaInicio <= 22 && $diaFin >= 22)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_22, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 23 && $diaFin >= 23)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_23, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 24 && $diaFin >= 24)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_24, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 25 && $diaFin >= 25)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_25, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 26 && $diaFin >= 26)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_26, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 27 && $diaFin >= 27)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_27, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 30 && $diaFin >= 30)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_28, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 31 && $diaFin >= 31)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_29, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 32 && $diaFin >= 32)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_30, 2, ',', '.')}}</td>@endif
            @if($diaInicio <= 33 && $diaFin >= 33)<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_31, 2, ',', '.')}}</td>@endif

            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{number_format((float)$total_datos_raw, 2, ',', '.') }}</td>

        </tr>
    </tbody>
</table>
</br>
</br>
</br>
</br>
</br>
</br>
<table>
    <tr>
        <td class="text-center text-xxs">Revisado por firma: ............................................</td>
        <td class="text-center text-xxs">Verificado por firma: ............................................</td>
    </tr>
    {{-- <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr> --}}
    <tr>
        <td class="text-center text-xxs">Nombre: ......................................................</td>
        <td class="text-center text-xxs">Nombre: ......................................................</td>
    </tr>
</table>
<br>
<table class="saltopagina">
    <tr>
        <td class="text-right text-xxs"></td>
        <td class="text-left text-xxs"></td>
        <td class="text-right text-xxs"></td>
        <td class="text-right text-xxs"><b>Fecha Impresion:</b> {{ $dateImp }}</td>
    </tr>
    <tr>
        <td class="text-right text-xxs"></td>
        <td class="text-left text-xxs"></td>
        <td class="text-right text-xxs"></td>
        <td class="text-right text-xxs"><b>Usuario:</b> {{ Auth::user()->name }}</td>
    </tr>
</table>

@endsection
<style>
    .saltopagina{page-break-after:always;}
</style>
