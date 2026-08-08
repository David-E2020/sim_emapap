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
            <td rowspan="2" class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td rowspan="2" class="px-15 py text-center  text-xxs uppercase">
                Area
            </td>
            <td rowspan="2" class="px-15 py text-center  text-xxs uppercase">
                Región
            </td>
            <td rowspan="2" class="px-15 py text-center  text-xxs uppercase">
                Sucursales
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                ALEVIN PACU - P UNID EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                ALEVIN PRE - JUVENIL TAMBAQUI - P UNID EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                ALEVIN TAMBAQUI-L UNID EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                ALEVIN TAMBAQUI-P UNID EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                TAMBAQUI EN MITADES CON ESCAMAS SELLADO AL VACIO ACOPIO KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                TAMBAQUI FRESCO EVISCERADO KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                TAMBAQUI FRESCO P KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                PACU FRESCO EVISCERADO KG EMAPA
            </td>
            <td rowspan="2" class="px-15 py text-center  text-xxs uppercase">
                Total existencias
            </td>
        </tr>
        <tr class="font-medium text-white text-sm">
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
        </tr>
        
    </thead>
    <tbody>
        @php
        $nro = 1;
        $total_entrada = 0;
        $total_salida = 0;
        
        $total_7640 = 0;
        $total_6385 = 0;
        $total_4834 = 0;
        $total_4835 = 0;
        $total_3903 = 0;
        $total_7278 = 0;
        $total_3902 = 0;
        $total_7003 = 0;

        $total_precio_7640 = 0;
        $total_precio_6385 = 0;
        $total_precio_4834 = 0;
        $total_precio_4835 = 0;
        $total_precio_3903 = 0;
        $total_precio_7278 = 0;
        $total_precio_3902 = 0;
        $total_precio_7003 = 0;

        $total_datos_raw = 0;


        $departamento = '';
        @endphp
        
        @foreach( $departamentos as $value)
            @php
                $total_parcial_7640 = 0;
                $total_parcial_6385 = 0;
                $total_parcial_4834 = 0;
                $total_parcial_4835 = 0;
                $total_parcial_3903 = 0;
                $total_parcial_7278 = 0;
                $total_parcial_3902 = 0;
                $total_parcial_7003 = 0;

                $total_parcial_precio_7640 = 0;
                $total_parcial_precio_6385 = 0;
                $total_parcial_precio_4834 = 0;
                $total_parcial_precio_4835 = 0;
                $total_parcial_precio_3903 = 0;
                $total_parcial_precio_7278 = 0;
                $total_parcial_precio_3902 = 0;
                $total_parcial_precio_7003 = 0;

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
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_7640, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_7640, 2, ',', '.') ?? '-'}}</td>
                        
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_6385, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_6385, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_4834, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_4834, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_4835, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_4835, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3903, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3903, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_7278, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_7278, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3902, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3902, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_7003, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_7003, 2, ',', '.') ?? '-'}}</td>


                        @php
                        $total_data = $data->producto_7640 
                                    + $data->producto_6385
                                    + $data->producto_4834 
                                    + $data->producto_4835
                                    + $data->producto_3903
                                    + $data->producto_7278
                                    + $data->producto_3902
                                    + $data->producto_7003
                                    ; 
                        @endphp

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{number_format((float)$total_data, 2, ',', '.')}}</td>
                    </tr>
                    @php
                        $departamento = $data->region;
                        //$total_entrada += floatval($insumo->o_entrada);
                        //$total_salida += floatval($insumo->o_salida);
                        $total_7640         += floatval($data->producto_7640);
                        $total_6385         += floatval($data->producto_6385);
                        $total_4834         += floatval($data->producto_4834);
                        $total_4835         += floatval($data->producto_4835);
                        $total_3903         += floatval($data->producto_3903);
                        $total_7278         += floatval($data->producto_7278);
                        $total_3902         += floatval($data->producto_3902);
                        $total_7003         += floatval($data->producto_7003);

                        $total_datos_raw    += floatval($total_data);
    
                        $total_parcial_7640  += floatval($data->producto_7640);
                        $total_parcial_6385  += floatval($data->producto_6385);
                        $total_parcial_4834  += floatval($data->producto_4834);
                        $total_parcial_4835  += floatval($data->producto_4835);
                        $total_parcial_3903  += floatval($data->producto_3903);
                        $total_parcial_7278  += floatval($data->producto_7278);
                        $total_parcial_3902  += floatval($data->producto_3902);
                        $total_parcial_7003  += floatval($data->producto_7003);

                        $total_parcial_datos_raw += floatval($total_data);

                    @endphp
                @endif
            @endforeach
            @if($value->dep_nombre  ==  $departamento)
            <tr class="text-sm">
                <td colspan="4" class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">SUB TOTAL</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_7640, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_6385, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_4834, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_4835, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3903, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_7278, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3902, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_7003, 2, ',', '.') }}</td>
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
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_7640, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_6385, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_4834, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_4835, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3903, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_7278, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3902, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_7003, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{number_format((float)$total_datos_raw, 2, ',', '.') }}</td>

        </tr>
    </tbody>
</table>
<br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td rowspan="2" class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td rowspan="2" class="px-15 py text-center  text-xxs uppercase">
                Area
            </td>
            <td rowspan="2" class="px-15 py text-center  text-xxs uppercase">
                Región
            </td>
            <td rowspan="2" class="px-15 py text-center  text-xxs uppercase">
                Sucursales
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                CORTE LONGITUDINAL DE PACU CON ESCAMA AL VACIO KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                CORTE LONGITUDINAL DE TAMBAQUI CON ESCAMA AL VACIO KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                FILETE DE TAMBAQUI SIN ESPINAS CON PIEL SIN ESCAMAS AL VACIO KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                FILETE DE TAMBAQUI SIN ESPINAS ENVASADO AL VACIO KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                LOMITO DE TAMBAQUI ENLATADO AL AGUA LAT 195 G EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                LOMITO DE TAMBAQUI ENLATADO AL LIMON LAT 195 G EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                POSTAS DE PACU AL VACIO KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                POSTAS DE TAMBAQUI AL VACIO KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                SUBSIDIO UNIVERSAL PRENATAL POR LA VIDA LOMITOS DE PESCADO AL AGUA O AL LIMON ENLATADO LAT 234 GR EMAPA
            </td>
            <td rowspan="2" class="px-15 py text-center  text-xxs uppercase">
                Total existencias
            </td>
        </tr>
        <tr class="font-medium text-white text-sm">
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Precio Venta.
            </td>
        </tr>
        
    </thead>
    <tbody>
        @php
        $nro = 1;
        $total_entrada = 0;
        $total_salida = 0;
        
        $total_3905 = 0;
        $total_3904 = 0;
        $total_7323 = 0;
        $total_6854 = 0;
        $total_6617 = 0;
        $total_6618 = 0;
        $total_7322 = 0;
        $total_7321 = 0;
        $total_6659 = 0;

        $total_precio_3905 = 0;
        $total_precio_3904 = 0;
        $total_precio_7323 = 0;
        $total_precio_6854 = 0;
        $total_precio_6617 = 0;
        $total_precio_6618 = 0;
        $total_precio_7322 = 0;
        $total_precio_7321 = 0;
        $total_precio_6659 = 0;

        $total_datos_raw = 0;


        $departamento = '';
        @endphp
        
        @foreach( $departamentos as $value)
            @php
                $total_parcial_3905 = 0;
                $total_parcial_3904 = 0;
                $total_parcial_7323 = 0;
                $total_parcial_6854 = 0;
                $total_parcial_6617 = 0;
                $total_parcial_6618 = 0;
                $total_parcial_7322 = 0;
                $total_parcial_7321 = 0;
                $total_parcial_6659 = 0;

                $total_parcial_precio_3905 = 0;
                $total_parcial_precio_3904 = 0;
                $total_parcial_precio_7323 = 0;
                $total_parcial_precio_6854 = 0;
                $total_parcial_precio_6617 = 0;
                $total_parcial_precio_6618 = 0;
                $total_parcial_precio_7322 = 0;
                $total_parcial_precio_7321 = 0;
                $total_parcial_precio_6659 = 0;

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
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3905, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3905, 2, ',', '.') ?? '-'}}</td>
                        
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3904, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3904, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_7323, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_7323, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_6854, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_6854, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_6617, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_6617, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_6618, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_6618, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_7322, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_7322, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_7321, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_7321, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_6659, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_6659, 2, ',', '.') ?? '-'}}</td>


                        @php
                        $total_data = $data->producto_3905 
                                    + $data->producto_3904
                                    + $data->producto_7323 
                                    + $data->producto_6854
                                    + $data->producto_6617
                                    + $data->producto_6618
                                    + $data->producto_7322
                                    + $data->producto_7321
                                    + $data->producto_6659
                                    ; 
                        @endphp

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{number_format((float)$total_data, 2, ',', '.')}}</td>
                    </tr>
                    @php
                        $departamento = $data->region;
                        //$total_entrada += floatval($insumo->o_entrada);
                        //$total_salida += floatval($insumo->o_salida);
                        $total_3905         += floatval($data->producto_3905);
                        $total_3904         += floatval($data->producto_3904);
                        $total_7323         += floatval($data->producto_7323);
                        $total_6854         += floatval($data->producto_6854);
                        $total_6617         += floatval($data->producto_6617);
                        $total_6618         += floatval($data->producto_6618);
                        $total_7322         += floatval($data->producto_7322);
                        $total_7321         += floatval($data->producto_7321);
                        $total_6659         += floatval($data->producto_6659);

                        $total_datos_raw    += floatval($total_data);
    
                        $total_parcial_3905  += floatval($data->producto_3905);
                        $total_parcial_3904  += floatval($data->producto_3904);
                        $total_parcial_7323  += floatval($data->producto_7323);
                        $total_parcial_6854  += floatval($data->producto_6854);
                        $total_parcial_6617  += floatval($data->producto_6617);
                        $total_parcial_6618  += floatval($data->producto_6618);
                        $total_parcial_7322  += floatval($data->producto_7322);
                        $total_parcial_7321  += floatval($data->producto_7321);
                        $total_parcial_6659  += floatval($data->producto_6659);

                        $total_parcial_datos_raw += floatval($total_data);

                    @endphp
                @endif
            @endforeach
            @if($value->dep_nombre  ==  $departamento)
            <tr class="text-sm">
                <td colspan="4" class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">SUB TOTAL</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3905, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3904, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_7323, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_6854, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_6617, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_6618, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_7322, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_7321, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_6659, 2, ',', '.') }}</td>
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
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3905, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3904, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_7323, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_6854, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_6617, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_6618, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_7322, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_7321, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_6659, 2, ',', '.')}}</td>
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
