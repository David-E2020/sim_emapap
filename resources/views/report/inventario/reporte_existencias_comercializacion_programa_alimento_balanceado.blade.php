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
                A.B. AVICOLA PARRILLERO ACABADO BOL 46 KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                A.B. AVICOLA PARRILLERO CRECIMIENTO BOL 46 KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                A.B. AVICOLA PARRILLERO INICIO BOL 46 KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                A.B. VACUNO LECHERO PELETIZADO QQ EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                A.B. VACUNO LECHERO QQ EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                A.B. VACUNO MANTENIMIENTO 45 KG EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                A.B. VACUNO MANTENIMIENTO PELETIZADO QQ EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                A.B. VACUNO MANTENIMIENTO QQ EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                ALIMENTO BALANCEADO MANTENIMIENTO VACUNO QQ EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                ALIMENTO BALANCEADO PISCICOLA F2 CRECIMIENTO 25 KG EMAPA
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
        
        $total_6865 = 0;
        $total_6864 = 0;
        $total_6863 = 0;
        $total_3978 = 0;
        $total_3977 = 0;
        $total_5443 = 0;
        $total_3982 = 0;
        $total_3980 = 0;
        $total_5988 = 0;
        $total_7320 = 0;

        $total_precio_6865 = 0;
        $total_precio_6864 = 0;
        $total_precio_6863 = 0;
        $total_precio_3978 = 0;
        $total_precio_3977 = 0;
        $total_precio_5443 = 0;
        $total_precio_3982 = 0;
        $total_precio_3980 = 0;
        $total_precio_5988 = 0;
        $total_precio_7320 = 0;

        $total_datos_raw = 0;


        $departamento = '';
        @endphp
        
        @foreach( $departamentos as $value)
            @php
                $total_parcial_6865 = 0;
                $total_parcial_6864 = 0;
                $total_parcial_6863 = 0;
                $total_parcial_3978 = 0;
                $total_parcial_3977 = 0;
                $total_parcial_5443 = 0;
                $total_parcial_3982 = 0;
                $total_parcial_3980 = 0;
                $total_parcial_5988 = 0;
                $total_parcial_7320 = 0;

                $total_parcial_precio_6865 = 0;
                $total_parcial_precio_6864 = 0;
                $total_parcial_precio_6863 = 0;
                $total_parcial_precio_3978 = 0;
                $total_parcial_precio_3977 = 0;
                $total_parcial_precio_5443 = 0;
                $total_parcial_precio_3982 = 0;
                $total_parcial_precio_3980 = 0;
                $total_parcial_precio_5988 = 0;
                $total_parcial_precio_7320 = 0;

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
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_6865, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_6865, 2, ',', '.') ?? '-'}}</td>
                        
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_6864, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_6864, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_6863, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_6863, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3978, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3978, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3977, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3977, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_5443, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_5443, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3982, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3982, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3980, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3980, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_5988, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_5988, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_7320, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_7320, 2, ',', '.') ?? '-'}}</td>
                        @php
                        $total_data = $data->producto_6865 
                                    + $data->producto_6864
                                    + $data->producto_6863 
                                    + $data->producto_3978 
                                    + $data->producto_3977 
                                    + $data->producto_5443 
                                    + $data->producto_3982
                                    + $data->producto_3980 
                                    + $data->producto_5988 
                                    + $data->producto_7320
                                    ; 
                        @endphp

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{number_format((float)$total_data, 2, ',', '.')}}</td>
                    </tr>
                    @php
                        $departamento = $data->region;
                        //$total_entrada += floatval($insumo->o_entrada);
                        //$total_salida += floatval($insumo->o_salida);
                        $total_6865         += floatval($data->producto_6865);
                        $total_6864         += floatval($data->producto_6864);
                        $total_6863         += floatval($data->producto_6863);
                        $total_3978         += floatval($data->producto_3978);
                        $total_3977         += floatval($data->producto_3977);
                        $total_5443         += floatval($data->producto_5443);
                        $total_3982         += floatval($data->producto_3982);
                        $total_3980         += floatval($data->producto_3980);
                        $total_5988         += floatval($data->producto_5988);
                        $total_7320         += floatval($data->producto_7320);

                        $total_datos_raw    += floatval($total_data);
    
                        $total_parcial_6865  += floatval($data->producto_6865);
                        $total_parcial_6864  += floatval($data->producto_6864);
                        $total_parcial_6863  += floatval($data->producto_6863);
                        $total_parcial_3978  += floatval($data->producto_3978);
                        $total_parcial_3977  += floatval($data->producto_3977);
                        $total_parcial_5443  += floatval($data->producto_5443);
                        $total_parcial_3982  += floatval($data->producto_3982);
                        $total_parcial_3980  += floatval($data->producto_3980);
                        $total_parcial_5988  += floatval($data->producto_5988);
                        $total_parcial_7320  += floatval($data->producto_7320);

                        $total_parcial_datos_raw += floatval($total_data);

                    @endphp
                @endif
            @endforeach
            @if($value->dep_nombre  ==  $departamento)
            <tr class="text-sm">
                <td colspan="4" class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">SUB TOTAL</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_6865, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_6864, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_6863, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3978, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3977, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_5443, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3982, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3980, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_5988, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_7320, 2, ',', '.') }}</td>
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
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_6865, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_6864, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_6863, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3978, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3977, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_5443, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3982, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3980, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_5988, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_7320, 2, ',', '.')}}</td>
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
