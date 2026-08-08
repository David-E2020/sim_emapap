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
</table>
<br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td rowspan="3" class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td rowspan="3" class="px-15 py text-center  text-xxs uppercase">
                Area
            </td>
            <td rowspan="3" class="px-15 py text-center  text-xxs uppercase">
                Región
            </td>
            <td rowspan="3" class="px-15 py text-center  text-xxs uppercase">
                Sucursales
            </td>
            <td colspan="4" class="text-center text-xxs">
                ARROZ ENTERO
            </td>
            <td colspan="4" class="text-center text-xxs">
                ARROZ K´AJA
            </td>
            <td colspan="10" class="text-center text-xxs">
                ARROZ SELECCIONADO
            </td>
            <td rowspan="3" class="text-center  text-xxs uppercase">
                Total
            </td>
        </tr>
        <tr class="font-medium text-white text-sm">
            <td colspan="2" class="text-center  text-xxs uppercase">
                AB QQ
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                AD QQ
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                @
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                QQ
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                @
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                1 KG
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                2 KG
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                5 KG
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                QQ
            </td>
        </tr>
        <tr class="font-medium text-white text-sm">
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
        </tr>
        
    </thead>
    <tbody>
        @php
        $nro = 1;
        $total_entrada = 0;
        $total_salida = 0;
        

        $total_6672 = 0;
        $total_3922 = 0;
        $total_3921 = 0;
        $total_3919 = 0;
        $total_3913 = 0;
        $total_3914 = 0;
        $total_3916 = 0;
        $total_3918 = 0;
        $total_3973 = 0;

        $total_precio_6672 = 0;
        $total_precio_3922 = 0;
        $total_precio_3921 = 0;
        $total_precio_3919 = 0;
        $total_precio_3913 = 0;
        $total_precio_3914 = 0;
        $total_precio_3916 = 0;
        $total_precio_3918 = 0;
        $total_precio_3973 = 0;
        
        $total_datos_raw = 0;
        
        $departamento = '';
        @endphp
        
        @foreach( $departamentos as $value)
            @php

                $total_parcial_6672 = 0;
                $total_parcial_3922 = 0;
                $total_parcial_3921 = 0;
                $total_parcial_3919 = 0;
                $total_parcial_3913 = 0;
                $total_parcial_3914 = 0;
                $total_parcial_3916 = 0;
                $total_parcial_3918 = 0;
                $total_parcial_3973 = 0;
                

                $total_parcial_precio_6672 = 0;
                $total_parcial_precio_3922 = 0;
                $total_parcial_precio_3921 = 0;
                $total_parcial_precio_3919 = 0;
                $total_parcial_precio_3913 = 0;
                $total_parcial_precio_3914 = 0;
                $total_parcial_precio_3916 = 0;
                $total_parcial_precio_3918 = 0;
                $total_parcial_precio_3973 = 0;


                $total_parcial_datos_raw = 0;
                $total_parcial_datos_raw_tn = 0;
            @endphp
            @foreach( $existencias as $data)
                @if($value->dep_nombre  ==  $data->region)
                    <tr class="text-sm">
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $nro++ }}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->area ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->region ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->sucursales ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_6672, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_6672, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3922, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3922, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3921, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3921, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3919, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3919, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3913, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3913, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3914, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3914, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3916, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3916, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3918, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3918, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3973, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3973, 2, ',', '.') ?? '-'}}</td>

                        
                        @php
                        $total_data = $data->producto_6672 
                                    + $data->producto_3922
                                    + $data->producto_3921 
                                    + $data->producto_3919
                                    + $data->producto_3913
                                    + $data->producto_3914
                                    + $data->producto_3916
                                    + $data->producto_3918
                                    + $data->producto_3973
                                    ; 
                                        
                        @endphp

                        <td class="text-center text-xxs uppercase font-bold">{{number_format((float)$total_data, 2, ',', '.')}}</td>
                    </tr>
                    @php
                        $departamento = $data->region;
                        //$total_entrada += floatval($insumo->o_entrada);
                        //$total_salida += floatval($insumo->o_salida);
                        $total_6672 += floatval($data->producto_6672);
                        $total_3922 += floatval($data->producto_3922);
                        $total_3921 += floatval($data->producto_3921);
                        $total_3919 += floatval($data->producto_3919);
                        $total_3913 += floatval($data->producto_3913);
                        $total_3914 += floatval($data->producto_3914);
                        $total_3916 += floatval($data->producto_3916);
                        $total_3918 += floatval($data->producto_3918);
                        $total_3973 += floatval($data->producto_3973);

                        $total_datos_raw                += floatval($total_data);
    
                        $total_parcial_6672 += floatval($data->producto_6672);
                        $total_parcial_3922 += floatval($data->producto_3922);
                        $total_parcial_3921 += floatval($data->producto_3921);
                        $total_parcial_3919 += floatval($data->producto_3919);
                        $total_parcial_3913 += floatval($data->producto_3913);
                        $total_parcial_3914 += floatval($data->producto_3914);
                        $total_parcial_3916 += floatval($data->producto_3916);
                        $total_parcial_3918 += floatval($data->producto_3918);
                        $total_parcial_3973 += floatval($data->producto_3973);

                        $total_parcial_datos_raw                += floatval($total_data);

                    @endphp
                @endif
            @endforeach
            @if($value->dep_nombre  ==  $departamento)
            <tr class="text-sm">
                <td colspan="4" class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">SUB TOTAL</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_6672, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3922, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3921, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3919, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3913, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3914, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3916, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3918, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3973, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_datos_raw, 2, ',', '.') }}</td>

            </tr>
            @endif
        @endforeach
            

        <tr class="font-medium text-white text-sm"></tr>
        <tr>
            <td colspan="4" class="text-center bg-grey-darker text-xs text-white">
                TOTAL
            </td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_6672, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3922, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3921, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3919, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3913, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3914, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3916, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3918, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3973, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>

            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{number_format((float)$total_datos_raw, 2, ',', '.') }}</td>
        </tr>
    </tbody>
</table>
<br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td rowspan="3" class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td rowspan="3" class="px-15 py text-center  text-xxs uppercase">
                Area
            </td>
            <td rowspan="3" class="px-15 py text-center  text-xxs uppercase">
                Región
            </td>
            <td rowspan="3" class="px-15 py text-center  text-xxs uppercase">
                Sucursales
            </td>
            <td colspan="2" class="text-center text-xxs">
                AFRECHO DE ARROZ 
            </td>
            <td colspan="4" class="text-center text-xxs">
                ARROCILLO
            </td>
            <td colspan="4" class="text-center text-xxs">
                ARROZ TRES CUARTOS 
            </td>
            <td colspan="6" class="text-center text-xxs">
                COLILLA
            </td>
            <td rowspan="3" class="text-center  text-xxs uppercase">
                Total
            </td>
        </tr>
        <tr class="font-medium text-white text-sm">
            <td colspan="2" class="text-center  text-xxs uppercase">
                QQ 
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                CA QQ
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                CB QQ
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                BA (ECONOMICO) QQ
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                BB QQ
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                (SUPER ECONOMICO) QQ
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                DA QQ
            </td>
            <td colspan="2" class="text-center  text-xxs uppercase">
                DB QQ
            </td>

        </tr>
        <tr class="font-medium text-white text-sm">
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
            <td colspan="1" class="text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="text-center text-xxs">
                P.V.
            </td>
        </tr>
        
    </thead>
    <tbody>
        @php
        $nro = 1;
        $total_entrada = 0;
        $total_salida = 0;
        
        $total_3892 = 0;
        $total_3893 = 0;
        $total_3894 = 0;
        $total_3923 = 0;
        $total_4681 = 0;
        $total_3924 = 0;
        $total_3926 = 0;
        $total_3927 = 0;


        $total_precio_3892 = 0;
        $total_precio_3893 = 0;
        $total_precio_3894 = 0;
        $total_precio_3923 = 0;
        $total_precio_4681 = 0;
        $total_precio_3924 = 0;
        $total_precio_3926 = 0;
        $total_precio_3927 = 0;


        $total_datos_raw = 0;


        $departamento = '';
        @endphp
        
        @foreach( $departamentos as $value)
            @php
                $total_parcial_3892 = 0;
                $total_parcial_3893 = 0;
                $total_parcial_3894 = 0;
                $total_parcial_3923 = 0;
                $total_parcial_4681 = 0;
                $total_parcial_3924 = 0;
                $total_parcial_3926 = 0;
                $total_parcial_3927 = 0;


                $total_parcial_precio_3892 = 0;
                $total_parcial_precio_3893 = 0;
                $total_parcial_precio_3894 = 0;
                $total_parcial_precio_3923 = 0;
                $total_parcial_precio_4681 = 0;
                $total_parcial_precio_3924 = 0;
                $total_parcial_precio_3926 = 0;
                $total_parcial_precio_3927 = 0;

                $total_parcial_datos_raw = 0;
                $total_parcial_datos_raw_tn = 0;
            @endphp
            @foreach( $existencias as $data)
                @if($value->dep_nombre  ==  $data->region)
                    <tr class="text-sm">
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $nro++ }}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->area ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->region ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->sucursales ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3892, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3892, 2, ',', '.') ?? '-'}}</td>
                        
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3893, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3893, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3894, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3894, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3923, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3923, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_4681, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_4681, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3924, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3924, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3926, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3926, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3927, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3927, 2, ',', '.') ?? '-'}}</td>

                        
                        @php
                        $total_data = $data->producto_3892 
                                    + $data->producto_3893
                                    + $data->producto_3894
                                    + $data->producto_3923
                                    + $data->producto_4681
                                    + $data->producto_3924
                                    + $data->producto_3926 
                                    + $data->producto_3927
                                    ; 
                                        
                        @endphp

                        <td class="text-center text-xxs uppercase font-bold">{{number_format((float)$total_data, 2, ',', '.')}}</td>
                    </tr>
                    @php
                        $departamento = $data->region;
                        //$total_entrada += floatval($insumo->o_entrada);
                        //$total_salida += floatval($insumo->o_salida);
                        $total_3892 += floatval($data->producto_3892);
                        $total_3893 += floatval($data->producto_3893);
                        $total_3894 += floatval($data->producto_3894);
                        $total_3923 += floatval($data->producto_3923);
                        $total_4681 += floatval($data->producto_4681);
                        $total_3924 += floatval($data->producto_3924);
                        $total_3926 += floatval($data->producto_3926);
                        $total_3927 += floatval($data->producto_3927);


                        $total_datos_raw                += floatval($total_data);
    

                        $total_parcial_3892 += floatval($data->producto_3892);
                        $total_parcial_3893 += floatval($data->producto_3893);
                        $total_parcial_3894 += floatval($data->producto_3894);
                        $total_parcial_3923 += floatval($data->producto_3923);
                        $total_parcial_4681 += floatval($data->producto_4681);
                        $total_parcial_3924 += floatval($data->producto_3924);
                        $total_parcial_3926 += floatval($data->producto_3926);
                        $total_parcial_3927 += floatval($data->producto_3927);

                        $total_parcial_datos_raw                += floatval($total_data);

                    @endphp
                @endif
            @endforeach
            @if($value->dep_nombre  ==  $departamento)
            <tr class="text-sm">
                <td colspan="4" class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">SUB TOTAL</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3892, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3893, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3894, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>
                
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3923, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_4681, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3924, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3926, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3927, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_datos_raw, 2, ',', '.') }}</td>

            </tr>
            @endif
        @endforeach
            

        <tr class="font-medium text-white text-sm"></tr>
        <tr>
            <td colspan="4" class="text-center bg-grey-darker text-xs text-white">
                TOTAL
            </td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3892, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3893, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3894, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3923, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_4681, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3924, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3926, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3927, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>

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
