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
            <td class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                Región
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                Almacen
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                ARROZ SELECCIONADO @ EMAPA 
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                ARROZ SELECCIONADO QQ EMAPA 
            </td>
            <td class="px-15 py text-center  text-xxs uppercase">
                Total
            </td>
        </tr>
    </thead>
    <tbody>
        @php
        $nro = 1;
        $total_entrada = 0;
        $total_salida = 0;
        
        $total_arroz_3913 = 0;
        $total_arroz_3973 = 0;

        $total_datos_raw = 0;
        $departamento = '';
        @endphp
        
        @foreach( $departamentos as $value)
            @php
                $total_parcial_arroz_3913 = 0;;
                $total_parcial_arroz_3973 = 0;

                $total_parcial_datos_raw = 0;
            @endphp
            @foreach( $existencias as $data)
                @if($value->dep_nombre  ==  $data->region)
                    <tr class="text-sm">
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $nro++ }}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->region ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->punto ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->arroz_seleccionado_arroba_emapa, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->arroz_seleccionado_qq_emapa, 2, ',', '.') ?? '-'}}</td>
                        @php
                        $total_data = $data->arroz_seleccionado_arroba_emapa + 
                                        $data->arroz_seleccionado_qq_emapa;
                                        
                        @endphp

                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{number_format((float)$total_data, 2, ',', '.')}}</td>
                    </tr>
                    @php
                        $departamento = $data->region;
                        //$total_entrada += floatval($insumo->o_entrada);
                        //$total_salida += floatval($insumo->o_salida);
                        $total_arroz_3913         += floatval($data->arroz_seleccionado_arroba_emapa);
                        $total_arroz_3973         += floatval($data->arroz_seleccionado_qq_emapa);

                        $total_datos_raw                += floatval($total_data);
    

                        $total_parcial_arroz_3913         += floatval($data->arroz_seleccionado_arroba_emapa);
                        $total_parcial_arroz_3973         += floatval($data->arroz_seleccionado_qq_emapa);

                        $total_parcial_datos_raw                += floatval($total_data);

                    @endphp
                @endif
            @endforeach
            @if($value->dep_nombre  ==  $departamento)
            <tr class="text-sm">
                <td colspan="3" class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">SUB TOTAL</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_arroz_3913, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_arroz_3973, 2, ',', '.') }}</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_datos_raw, 2, ',', '.') }}</td>

            </tr>
            @endif
        @endforeach
            

        <tr class="font-medium text-white text-sm"></tr>
        <tr>
            <td colspan="3" class="text-center bg-grey-darker text-xs text-white">
                TOTAL
            </td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_arroz_3913, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_arroz_3973, 2, ',', '.')}}</td>

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
