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
        <td class="text-center bg-grey-darker text-xs text-white">Tipo Planta:</td>
        <td class="text-xs uppercase text-center">{{$tipo_planta->param_nombre?? 'TODOS'}}</td>
        <td class="text-center bg-grey-darker text-xs text-white">Punto:</td>
        <td class="text-xs uppercase text-center">{{$punto->nombre ?? 'TODOS'}}</td>
    </tr>
</table>
<br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td class="px-15 py text-center text-xxs">
                Nro.
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                REGION
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                PLANTAS
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                TIPO PLANTAS
            </td>
            <td class="px-15 py text-center  text-xxs">
                PUNTOS (Asociacion/Silo/Molino)
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                PRODUCTO
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                ciclo productivo-2023
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                ciclo productivo-2024
            </td>
            @if($tipo_producto->param_valor == 1)
            <td class="px-15 py text-center  text-xxs uppercase">
                Total Kg
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                Total Tn
            </td>
            @else 
                <td class="px-15 py text-center  text-xxs uppercase">
                    Total
                </td>
            @endif
        </tr>
    </thead>
    <tbody>
        @php
        $nro = 1;
        $total_entrada = 0;
        $total_salida = 0;
        
        $total_ciclo_productivo_2023 = 0;
        $total_ciclo_productivo_2024 = 0;
        $total_datos_raw = 0;
        $total_datos_raw_tn = 0;

        $departamento = '';
        @endphp
        
        @foreach( $departamentos as $value)
            @php

                $total_parcial_ciclo_productivo_2023 = 0;
                $total_parcial_ciclo_productivo_2024 = 0;
                $total_parcial_datos_raw = 0;
                $total_parcial_datos_raw_tn = 0;
            @endphp
            @foreach( $existencias as $data)
                @if($value->dep_nombre  ==  $data->region)
                    <tr class="text-sm">
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $nro++ }}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->region ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->plantas ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->tipo_planta ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->punto ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->producto ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->ciclo_productivo_2023 ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->ciclo_productivo_2024 ?? '-'}}</td>
                        @php
                        $total_data = $data->ciclo_productivo_2023 + $data->ciclo_productivo_2024;
                        $total_data_tn = $total_data/1000;
                        @endphp
                        @if($tipo_producto->param_valor == 1)
                            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$total_data}}</td>
                            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ round($total_data_tn, 2)}}</td>
                        @else 
                            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$total_data}}</td>
                        @endif

                    </tr>
                    @php
                        $departamento = $data->region;
                        //$total_entrada += floatval($insumo->o_entrada);
                        //$total_salida += floatval($insumo->o_salida);

                        $total_ciclo_productivo_2023    += floatval($data->ciclo_productivo_2023);
                        $total_ciclo_productivo_2024    += floatval($data->ciclo_productivo_2024);
                        $total_datos_raw                += floatval($total_data);
                        $total_datos_raw_tn             += floatval($total_data_tn);

                        $total_parcial_ciclo_productivo_2023    += floatval($data->ciclo_productivo_2023);
                        $total_parcial_ciclo_productivo_2024    += floatval($data->ciclo_productivo_2024);
                        $total_parcial_datos_raw                += floatval($total_data);
                        $total_parcial_datos_raw_tn             += floatval($total_data_tn);
                    @endphp
                @endif
            @endforeach
            @if($value->dep_nombre  ==  $departamento)
            <tr class="text-sm">
                <td colspan="6" class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">SUB TOTAL</td>
                <td class="text-center text-xxs uppercase font-bold bg-grey-darker-light px-1 py-1">{{ $total_parcial_ciclo_productivo_2023 }}</td>
                <td class="text-center text-xxs uppercase font-bold bg-grey-darker-light px-1 py-1">{{ $total_parcial_ciclo_productivo_2024 }}</td>
                @if($tipo_producto->param_valor == 1)
                    <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ $total_parcial_datos_raw }}</td>
                    <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ round($total_parcial_datos_raw_tn, 2) }}</td>
                @else 
                    <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ $total_parcial_datos_raw }}</td>
                @endif
            </tr>
            @endif
        @endforeach
            

        <tr class="font-medium text-white text-sm"></tr>
        <tr>
            <td colspan="6" class="text-center bg-grey-darker text-xs text-white">
                TOTAL
            </td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$total_ciclo_productivo_2023}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$total_ciclo_productivo_2024}}</td>
            @if($tipo_producto->param_valor == 1)
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$total_datos_raw }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{round($total_datos_raw_tn, 2) }}</td>
            @else 
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$total_datos_raw }}</td>
            @endif
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
<table>
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
