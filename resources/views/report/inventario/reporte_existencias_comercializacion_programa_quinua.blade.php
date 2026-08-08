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
                FIDEO DE QUINUA ECOLOGICO BOL 400 G EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                FIDEO DE QUINUA ECOLOGICO BOL 5 KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                HARINA DE QUINUA BOL 1 KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                HOJUELA DE QUINUA BOL 1/2 KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                QUINUA DE CUARTA QQ EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                QUINUA JACHA GRANO BOL 46 KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                QUINUA PERLADA BOL 1 KG EMAPA
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
        </tr>
        
    </thead>
    <tbody>
        @php
        $nro = 1;
        $total_entrada = 0;
        $total_salida = 0;
        
        $total_7208 = 0;
        $total_7187 = 0;
        $total_3968 = 0;
        $total_3966 = 0;
        $total_7271 = 0;
        $total_6893 = 0;
        $total_3967 = 0;

        $total_precio_7208 = 0;
        $total_precio_7187 = 0;
        $total_precio_3968 = 0;
        $total_precio_3966 = 0;
        $total_precio_7271 = 0;
        $total_precio_6893 = 0;
        $total_precio_3967 = 0;

        $total_datos_raw = 0;


        $departamento = '';
        @endphp
        
        @foreach( $departamentos as $value)
            @php
                $total_parcial_7208 = 0;
                $total_parcial_7187 = 0;
                $total_parcial_3968 = 0;
                $total_parcial_3966 = 0;
                $total_parcial_7271 = 0;
                $total_parcial_6893 = 0;
                $total_parcial_3967 = 0;

                $total_parcial_precio_7208 = 0;
                $total_parcial_precio_7187 = 0;
                $total_parcial_precio_3968 = 0;
                $total_parcial_precio_3966 = 0;
                $total_parcial_precio_7271 = 0;
                $total_parcial_precio_6893 = 0;
                $total_parcial_precio_3967 = 0;

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
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_7208, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_7208, 2, ',', '.') ?? '-'}}</td>
                        
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_7187, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_7187, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3968, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3968, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3966, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3966, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_7271, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_7271, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_6893, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_6893, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3967, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3967, 2, ',', '.') ?? '-'}}</td>

                        @php
                        $total_data = $data->producto_7208 
                                    + $data->producto_7187
                                    + $data->producto_3968 
                                    + $data->producto_3966 
                                    + $data->producto_7271 
                                    + $data->producto_6893 
                                    + $data->producto_3967
                                    ; 
                        @endphp

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{number_format((float)$total_data, 2, ',', '.')}}</td>
                    </tr>
                    @php
                        $departamento = $data->region;
                        //$total_entrada += floatval($insumo->o_entrada);
                        //$total_salida += floatval($insumo->o_salida);
                        $total_7208         += floatval($data->producto_7208);
                        $total_7187         += floatval($data->producto_7187);
                        $total_3968         += floatval($data->producto_3968);
                        $total_3966         += floatval($data->producto_3966);
                        $total_7271         += floatval($data->producto_7271);
                        $total_6893         += floatval($data->producto_6893);
                        $total_3967         += floatval($data->producto_3967);

                        $total_datos_raw    += floatval($total_data);
    
                        $total_parcial_7208  += floatval($data->producto_7208);
                        $total_parcial_7187  += floatval($data->producto_7187);
                        $total_parcial_3968  += floatval($data->producto_3968);
                        $total_parcial_3966  += floatval($data->producto_3966);
                        $total_parcial_7271  += floatval($data->producto_7271);
                        $total_parcial_6893  += floatval($data->producto_6893);
                        $total_parcial_3967  += floatval($data->producto_3967);

                        $total_parcial_datos_raw += floatval($total_data);

                    @endphp
                @endif
            @endforeach
            @if($value->dep_nombre  ==  $departamento)
            <tr class="text-sm">
                <td colspan="4" class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">SUB TOTAL</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_7208, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_7187, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3968, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3966, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_7271, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_6893, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3967, 2, ',', '.') }}</td>
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
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_7208, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_7187, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3968, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3966, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_7271, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_6893, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3967, 2, ',', '.')}}</td>
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
