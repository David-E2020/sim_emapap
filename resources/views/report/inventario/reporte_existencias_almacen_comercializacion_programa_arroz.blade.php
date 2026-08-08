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
            <td class="text-center  text-xxs uppercase">
                Región
            </td>
            <td class="text-center  text-xxs uppercase">
                Almacen
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ ENTERO AB @
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ ENTERO AB 2 KG
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ ENTERO AB 5 KG
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ ENTERO AB BOL 500 G
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ ENTERO ABI BOL 500 G
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ ENTERO AB 1 KG
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ ENTERO AB QQ
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ ENTERO AC @
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ ENTERO AC QQ
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ ENTERO AD qq
            </td>
            <td class="text-center  text-xxs uppercase">
                AFRECHO DE ARROZ TIPO 1 QQ
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ TRES CUARTOS BA QQ
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROZ TRES CUARTOS BB QQ
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROCILLO CA QQ
            </td>
            <td class="text-center  text-xxs uppercase">
                ARROCILLO CB QQ
            </td>
            <td class="text-center  text-xxs uppercase">
                COLILLA DA QQ
            </td>
            <td class="text-center  text-xxs uppercase">
                COLILLA DB QQ
            </td>
            <td class="text-center  text-xxs uppercase">
                Total
            </td>
        </tr>
    </thead>
    <tbody>
        @php
        $nro = 1;
        $total_entrada = 0;
        $total_salida = 0;
        
        $total_30 = 0;
        $total_31 = 0;
        $total_33 = 0;
        $total_34 = 0;
        $total_36 = 0;
        $total_37 = 0;
        $total_38 = 0;
        $total_39 = 0;
        $total_41 = 0;
        $total_43 = 0;
        $total_51 = 0;
        $total_54 = 0;
        $total_56 = 0;
        $total_60 = 0;
        $total_63 = 0;
        $total_71 = 0;
        $total_73 = 0;

        $total_datos_raw = 0;
        $departamento = '';
        @endphp
        
        @foreach( $departamentos as $value)
            @php
                $total_parcial_30 = 0;
                $total_parcial_31 = 0;
                $total_parcial_33 = 0;
                $total_parcial_34 = 0;
                $total_parcial_36 = 0;
                $total_parcial_37 = 0;
                $total_parcial_38 = 0;
                $total_parcial_39 = 0;
                $total_parcial_41 = 0;
                $total_parcial_43 = 0;
                $total_parcial_51 = 0;
                $total_parcial_54 = 0;
                $total_parcial_56 = 0;
                $total_parcial_60 = 0;
                $total_parcial_63 = 0;
                $total_parcial_71 = 0;
                $total_parcial_73 = 0;
                $total_parcial_datos_raw = 0;
            @endphp
            @foreach( $existencias as $data)
                @if($value->dep_nombre  ==  $data->region)
                    <tr class="text-sm">
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $nro++ }}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->region ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$data->punto ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_30, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_31, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_33, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_34, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_36, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_37, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_38, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_39, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_41, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_43, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_51, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_54, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_56, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_60, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_63, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_71, 2, ',', '.') ?? '-'}}</td>
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$data->producto_73, 2, ',', '.') ?? '-'}}</td>
                        @php
                        $total_data = $data->producto_30 
                                    + $data->producto_31
                                    + $data->producto_33
                                    + $data->producto_34
                                    + $data->producto_36
                                    + $data->producto_37
                                    + $data->producto_38
                                    + $data->producto_39
                                    + $data->producto_41
                                    + $data->producto_43
                                    + $data->producto_51
                                    + $data->producto_54
                                    + $data->producto_56
                                    + $data->producto_60
                                    + $data->producto_63
                                    + $data->producto_71
                                    + $data->producto_73
                                    ;
                        @endphp
                        <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{number_format((float)$total_data, 2, ',', '.')}}</td>
                    </tr>
                    @php
                        $departamento = $data->region;
                        //$total_entrada += floatval($insumo->o_entrada);
                        //$total_salida += floatval($insumo->o_salida);
                        $total_30 += floatval($data->producto_30);
                        $total_31 += floatval($data->producto_31);
                        $total_33 += floatval($data->producto_33);
                        $total_34 += floatval($data->producto_34);
                        $total_36 += floatval($data->producto_36);
                        $total_37 += floatval($data->producto_37);
                        $total_38 += floatval($data->producto_38);
                        $total_39 += floatval($data->producto_39);
                        $total_41 += floatval($data->producto_41);
                        $total_43 += floatval($data->producto_43);
                        $total_51 += floatval($data->producto_51);
                        $total_54 += floatval($data->producto_54);
                        $total_56 += floatval($data->producto_56);
                        $total_60 += floatval($data->producto_60);
                        $total_63 += floatval($data->producto_63);
                        $total_71 += floatval($data->producto_71);
                        $total_73 += floatval($data->producto_73);

                        $total_datos_raw                += floatval($total_data);
    
                        $total_parcial_30 += floatval($data->producto_30);
                        $total_parcial_31 += floatval($data->producto_31);
                        $total_parcial_33 += floatval($data->producto_33);
                        $total_parcial_34 += floatval($data->producto_34);
                        $total_parcial_36 += floatval($data->producto_36);
                        $total_parcial_37 += floatval($data->producto_37);
                        $total_parcial_38 += floatval($data->producto_38);
                        $total_parcial_39 += floatval($data->producto_39);
                        $total_parcial_41 += floatval($data->producto_41);
                        $total_parcial_43 += floatval($data->producto_43);
                        $total_parcial_51 += floatval($data->producto_51);
                        $total_parcial_54 += floatval($data->producto_54);
                        $total_parcial_56 += floatval($data->producto_56);
                        $total_parcial_60 += floatval($data->producto_60);
                        $total_parcial_63 += floatval($data->producto_63);
                        $total_parcial_71 += floatval($data->producto_71);
                        $total_parcial_73 += floatval($data->producto_73);
                        $total_parcial_datos_raw                += floatval($total_data);

                    @endphp
                @endif
            @endforeach
            @if($value->dep_nombre  ==  $departamento)
            <tr class="text-sm">
                <td colspan="3" class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">SUB TOTAL</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_30, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_31, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_33, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_34, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_36, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_37, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_38, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_39, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_41, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_43, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_51, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_54, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_56, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_60, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_63, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_71, 2, ',', '.') }}</td>
                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_73, 2, ',', '.') }}</td>

                <td class="text-center text-xxs uppercase bg-grey-darker-light font-bold px-1 py-1">{{ number_format((float)$total_parcial_datos_raw, 2, ',', '.') }}</td>

            </tr>
            @endif
        @endforeach
            

        <tr class="font-medium text-white text-sm"></tr>
        <tr>
            <td colspan="3" class="text-center bg-grey-darker text-xs text-white">
                TOTAL
            </td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_30, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_31, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_33, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_34, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_36, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_37, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_38, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_39, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_41, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_43, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_51, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_54, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_56, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_60, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_63, 2, ',', '.')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_71, 2, ',', '.') }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ number_format((float)$total_73, 2, ',', '.')}}</td>

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
