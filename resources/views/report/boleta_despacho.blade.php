@extends('layouts.print')

@section('content')
<style type="text/css">
    table.table-reporte {
        border-width: 1px;
        border-spacing: 0px;
        border-style: outset;
        border-color: black;
        border-collapse: collapse;
        background-color: white;
    }

    table.table-reporte th {
        border-width: 1px;
        padding: 1px;
        border-style: inset;
        border-color: gray;
        background-color: white;

    }

    table.table-reporte td {
        border-width: 1px;
        padding: 2px;
        border-style: inset;
        border-color: gray;
        background-color: white;
    }

    .tablas {
        display: flex;
        justify-content: center;
        width: 100%;
        margin: 0 auto;
        height: 300px;
        display: flex;
        justify-content: center;
    }

</style>

<br>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Fecha Consulta:</td>
        <td class="text-xs uppercase text-center">{{$date }}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Hora:</td>
        <td class="text-xs uppercase text-center">-</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Planta:</td>
        <td class="text-xs uppercase text-center">{{$planta_nombre }}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Responsable:</td>
        <td class="text-xs uppercase text-center">--</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Punto Ingreso:</td>
        <td class="text-xs uppercase text-center">{{$planta_nombre }}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Punto Salida:</td>
        <td class="text-xs uppercase text-center">--</td>
    </tr>
</table>

<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Peso Bruto</td>
        <td class="text-xs uppercase text-center">1000</td>

        <td class="text-center bg-grey-darker text-xs text-white">Peso Tara</td>
        <td class="text-xs uppercase text-center">1412</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Peso Neto</td>
        <td class="text-xs uppercase text-center">100</td>

        <td class="text-center bg-grey-darker text-xs text-white">Peso Liquido</td>
        <td class="text-xs uppercase text-center">12412</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Observacion:</td>
        <td class="text-xs uppercase text-center">nada por observar</td>

        <td class="text-center bg-grey-darker text-xs text-white">Punto Salida:</td>
        <td class="text-xs uppercase text-center">dawdaw</td>
    </tr>
</table>
<br>
    <div class="tablas">
        <table class="table-info border">
            <tr>
                <td class="text-center bg-grey-darker text-xs text-white">HUM</td>
                <td class="text-xs uppercase text-center">:11.3 %</td>
                <td class="text-xs uppercase text-center">:0 %</td>

                <td></td>

                <td class="text-center bg-grey-darker text-xs text-white">IMP</td>
                <td class="text-xs uppercase text-center">:0.3 %</td>
                <td class="text-xs uppercase text-center">:0 %</td>
                
            </tr>
            <tr>
                <td class="text-center bg-grey-darker text-xs text-white">GER</td>
                <td class="text-xs uppercase text-center">:0 %</td>
                <td class="text-xs uppercase text-center">:0 %</td>

                <td></td>

                <td class="text-center bg-grey-darker text-xs text-white">GVE</td>
                <td class="text-xs uppercase text-center">--</td>
                <td class="text-xs uppercase text-center">:0 %</td>
            </tr>
            <tr>
                <td class="text-center bg-grey-darker text-xs text-white">PNE</td>
                <td class="text-xs uppercase text-center">:2 %</td>
                <td class="text-xs uppercase text-center">:0 %</td>

                <td></td>
                

                <td class="text-center bg-grey-darker text-xs text-white">GVA</td>
                <td class="text-xs uppercase text-center">--</td>
                <td class="text-xs uppercase text-center">:0 %</td>
            </tr>
            <tr>
                <td class="text-center bg-grey-darker text-xs text-white">PH</td>
                <td class="text-xs uppercase text-center">:81.1 kg/hl</td>
                <td class="text-xs uppercase text-center">:0 %</td>
                
                <td></td>

                <td class="text-center bg-grey-darker text-xs text-white">HUM</td>
                <td class="text-xs uppercase text-center">:01</td>
                <td class="text-xs uppercase text-center">:0 %</td>
            </tr>
            <tr>
                <td class="text-center bg-grey-darker text-xs text-white">IMP</td>
                <td class="text-xs uppercase text-center">:0 %</td>
                <td class="text-xs uppercase text-center">:0 %</td>

                <td></td>

                <td class="text-center bg-grey-darker text-xs text-white">PNE</td>
                <td class="text-xs uppercase text-center">-</td>
                <td class="text-xs uppercase text-center">:0 %</td>
            </tr>
            <tr>
                <td class="text-center bg-grey-darker text-xs text-white">PBL</td>
                <td class="text-xs uppercase text-center">:0 %</td>
                <td class="text-xs uppercase text-center">:0 %</td>

                <td></td>

                <td class="text-center bg-grey-darker text-xs text-white">GVA</td>
                <td class="text-xs uppercase text-center">78</td>
                <td class="text-xs uppercase text-center">:0 %</td>
            </tr>
            <tr>
                <td class="text-center bg-grey-darker text-xs text-white">GVE</td>
                <td class="text-xs uppercase text-center">:0 %</td>
                <td class="text-xs uppercase text-center">:0 %</td>

                <td></td>

                <td class="text-center bg-grey-darker text-xs text-white">PH</td>
                <td class="text-xs uppercase text-center">:0 kg/hl</td>
                <td class="text-xs uppercase text-center">:0 %</td>
            </tr>
            <tr>
                <td class="text-center bg-grey-darker text-xs text-white">GPA</td>
                <td class="text-xs uppercase text-center">:0 %</td>
                <td class="text-xs uppercase text-center">:0 %</td>

                <td></td>

                <td class="text-center bg-grey-darker text-xs text-white">GRADO</td>
                <td class="text-xs uppercase text-center">--</td>
                <td class="text-xs uppercase text-center">:0 %</td>
            </tr>
            <tr>
                <td class="text-center bg-grey-darker text-xs text-white">Obs:</td>
                <td class="text-xs uppercase">____________________</td>
            </tr>
        </table>
    </div>
<br>
<span>
    <h6>DATOS DEL TRANSPORTES: </h6>
</span>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Tipo Vehiculo:</td>
        <td class="text-xs uppercase">NISSAN</td>

        <td class="text-center bg-grey-darker text-xs text-white">Placa:</td>
        <td class="text-xs uppercase">WEQW333</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Conductor:</td>
        <td class="text-xs uppercase">Pedro Quispe</td>

        <td class="text-center bg-grey-darker text-xs text-white">C.I.:</td>
        <td class="text-xs uppercase">68852158</td>
    </tr>
</table>
<br>

<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="px-15 py text-center text-xxs">
            <td class="font-bold text-center text-xs" colspan="5">DETALLE DE PRODUCTOS DESPACHADOS</td>
        </tr>
        <tr>
            <td class="text-center bg-grey-darker text-xs text-white">Correlativo Salida:</td>
            <td class="text-xs uppercase" colspan="4"> </td>
        </tr>
        <tr class="px-15 py text-center text-xxs">
            <td class="font-bold text-center text-xs">Nro</td>
            <td class="font-bold text-center text-xs">Codigo Articulo</td>
            <td class="font-bold text-center text-xs">Articulo</td>
            <td class="font-bold text-center text-xs">Unidad</td>
            <td class="font-bold text-center text-xs">Cantidad</td>


        </tr>
    </thead>
    <tbody>
        <?php
        $total = 0;
        ?>
        @foreach($movimientos_salida->movimiento_detalle ?? [] as $value)
        <tr class="text-sm">
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $count++ }}</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->codigo_alterno}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->nombre_producto}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->unidad_medida->nombre ?? ''}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->mvd_cantidad}} </td>
        </tr>
        <?php
        $total += $value->mvd_cantidad;
        ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="text-sm">
            <td colspan="4" class="text-center text-xxs uppercase font-bold px-4 py-3 bg-grey-darker text-white"><strong> TOTAL:</strong> </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3"> <B>{{ number_format((float)$total, 2, '.', '')}}</B> </td>
        </tr>
    </tfoot>
    @php
    $nro = 1;
    $total_presentacion = 0;
    $total_medida = 0;
    @endphp
</table>
<br><br>
<table class="table-reporte">
    <tr style="height: 150px;">
        <td class="text-xs" style="vertical-align: bottom; text-align: center" rowspan="2">
            SELLO Y FIRMA:
        </td>
        <td class="text-xs" style="vertical-align: bottom; text-align: center" rowspan="2">
            SELLO Y FIRMA:
        </td>
    </tr>
    <tr>
    </tr>
    <tr>
        <td class="text-xs" style="text-align: center">
            NOMBRE:
        </td>
        <td class="text-xs" style="text-align: center">
            NOMBRE:
        </td>
    </tr>
    <tr>
        <td class="text-center text-xs font-bold" style="text-align: center">
            ELABORADO - PERSONAL EMAPA
        </td>
        <td class="text-center text-xs font-bold" style="text-align: center">
            AUTORIZADO - PERSONAL EMAPA
        </td>
    </tr>
</table>

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<table class="saltopagina">
    <tr>
        <td class="text-right text-xxs"></td>
        <td class="text-left text-xxs"></td>
        <td class="text-right text-xxs"></td>
        <td class="text-right text-xxs"><b>Fecha Impresion:</b> {{ $date }}</td>
    </tr>
    <tr>
        <td class="text-right text-xxs"></td>
        <td class="text-left text-xxs"></td>
        <td class="text-right text-xxs"></td>
        <td class="text-right text-xxs"><b>Usuario:</b> {{ Auth::user()->name }}</td>
    </tr>
</table>



@endsection