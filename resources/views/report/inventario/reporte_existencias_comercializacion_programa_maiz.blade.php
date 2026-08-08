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
                GRANILLO DE MAIZ TIPO 1 TN EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                GRANILLO DE MAIZ TIPO 2 TN EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                GRANILLO DE MAIZ TIPO 3 TN EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                GRANO DE MAIZ QQ EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                GRANO DE MAIZ TN EMAPA
            </td>
            <td colspan="2" class="px-15 py text-center  text-xxs uppercase">
                RESIDUO DE MAIZ TN EMAPA
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
        </tr>
        
    </thead>
    <tbody>
        @php
        $nro = 1;
        $total_entrada = 0;
        $total_salida = 0;
        
        $total_5259 = 0;
        $total_5260 = 0;
        $total_5975 = 0;
        $total_3951 = 0;
        $total_4832 = 0;
        $total_4845 = 0;

        $total_precio_5259 = 0;
        $total_precio_5260 = 0;
        $total_precio_5975 = 0;
        $total_precio_3951 = 0;
        $total_precio_4832 = 0;
        $total_precio_4845 = 0;

        $total_datos_raw = 0;


        $departamento = '';
        @endphp
        
        @foreach( $departamentos as $value)
            @php
                $total_parcial_5259 = 0;
                $total_parcial_5260 = 0;
                $total_parcial_5975 = 0;
                $total_parcial_3951 = 0;
                $total_parcial_4832 = 0;
                $total_parcial_4845 = 0;

                $total_parcial_precio_5259 = 0;
                $total_parcial_precio_5260 = 0;
                $total_parcial_precio_5975 = 0;
                $total_parcial_precio_3951 = 0;
                $total_parcial_precio_4832 = 0;
                $total_parcial_precio_4845 = 0;

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
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_5259, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_5259, 2, ',', '.') ?? '-'}}</td>
                        
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_5260, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_5260, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_5975, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_5975, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_3951, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_3951, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_4832, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_4832, 2, ',', '.') ?? '-'}}</td>

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_4845, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->precio_producto_4845, 2, ',', '.') ?? '-'}}</td>

                        @php
                        $total_data = $data->producto_5259 
                                    + $data->producto_5260 
                                    + $data->producto_5975 
                                    + $data->producto_3951 
                                    + $data->producto_4832 
                                    + $data->producto_4845 
                                    ; 
                        @endphp

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{number_format((float)$total_data, 2, ',', '.')}}</td>
                    </tr>
                    @php
                        $departamento = $data->region;
                        //$total_entrada += floatval($insumo->o_entrada);
                        //$total_salida += floatval($insumo->o_salida);
                        $total_5259         += floatval($data->producto_5259);
                        $total_5260         += floatval($data->producto_5260);
                        $total_5975         += floatval($data->producto_5975);
                        $total_3951         += floatval($data->producto_3951);
                        $total_4832         += floatval($data->producto_4832);
                        $total_4845         += floatval($data->producto_4845);

                        $total_datos_raw    += floatval($total_data);
    
                        $total_parcial_5259  += floatval($data->producto_5259);
                        $total_parcial_5260  += floatval($data->producto_5260);
                        $total_parcial_5975  += floatval($data->producto_5975);
                        $total_parcial_3951  += floatval($data->producto_3951);
                        $total_parcial_4832  += floatval($data->producto_4832);
                        $total_parcial_4845  += floatval($data->producto_4845);

                        $total_parcial_datos_raw += floatval($total_data);

                    @endphp
                @endif
            @endforeach
            @if($value->dep_nombre  ==  $departamento)
            <tr class="text-sm">
                <td colspan="4" class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">SUB TOTAL</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_5259, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_5260, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_5975, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_3951, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_4832, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">-</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_4845, 2, ',', '.') }}</td>
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
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_5259, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_5260, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_5975, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_3951, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_4832, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">-</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_4845, 2, ',', '.')}}</td>
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
