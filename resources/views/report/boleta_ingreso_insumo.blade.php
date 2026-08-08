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
    .texto-vertical-2 {
    writing-mode: vertical-lr;
    transform: rotate(180deg);
    }   
</style>

<br>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Fecha Consulta:</td>
        <td class="text-xs uppercase">{{$date }}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Hora:</td>
        <td class="text-xs uppercase">-</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Planta:</td>
        <td class="text-xs uppercase">{{$planta_nombre }}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Responsable:</td>
        <td class="text-xs uppercase">--</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Punto Ingreso:</td>
        <td class="text-xs uppercase">{{$planta_nombre }}</td>

        <td class="text-center bg-grey-darker text-xs text-white">Punto Salida:</td>
        <td class="text-xs uppercase">--</td>
    </tr>
    <!---->
</table>

<br>
<span><h6>DATOS DEL PROCESO: </h6></span>
<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Tipo de Ingreso:</td>
        <td class="text-xs uppercase">ORDEN DE COMPRA</td>

        <td class="text-center bg-grey-darker text-xs text-white">Proveedor:</td>
        <td class="text-xs uppercase">NOEL HAYDER</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Numero del Proceso:</td>
        <td class="text-xs uppercase">NI/EMAPA/GG/ALMA N° 069/2023</td>

        <td class="text-center bg-grey-darker text-xs text-white">C31 / Numero Preventivo:</td>
        <td class="text-xs uppercase">6699</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white">Fecha de Registro de Ingreso:</td>
        <td class="text-xs uppercase">09//02/2023</td>

        <td class="text-center bg-grey-darker text-xs text-white">Descripcion del proceso:</td>
        <td class="text-xs uppercase">Compra AMPE - CUCE 120-654-65454-55-4546-45  </td>
    </tr>    
</table>

<!----------->
<br>

<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="px-15 py text-center text-xxs">
            <td class="font-bold text-center text-xs" colspan="9">DETALLE DE PRODUCTOS INGRESADOS</td>
        </tr>
        <tr>
            <td class="text-center bg-grey-darker text-xs text-white">Correlativo Ingreso:</td>
            <td class="text-xs uppercase" colspan="8">{{$code_salida}} </td>
        </tr>
        <tr class="px-15 py text-center text-xxs">
            <td class="font-bold text-center text-xs">Nro</td>
            <td class="font-bold text-center text-xs">Codigo Articulo</td>
            <td class="font-bold text-center text-xs">Lote</td>            
            <td class="font-bold text-center text-xs">Articulo</td>
            <td class="font-bold text-center text-xs">Unidad</td>
            <td class="font-bold text-center text-xs">Fecha Vencimiento</td>
            <td class="font-bold text-center text-xs">Cantidad</td>
            <td class="font-bold text-center text-xs">Precio Unitario</td>
            <td class="font-bold text-center text-xs">Precio Total</td>            
                        
        </tr>
    </thead>
    <tbody>
        <?php
        $total = 0;
        ?>
        @foreach($movimientos_salida->movimiento_detalle as $value)
        <tr class="text-sm">
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $count++ }}</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->codigo_alterno}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->mvd_lote}} </td>            
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->nombre_producto}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->articulo->unidad_medida->nombre ?? ''}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->mvd_fecha_vencimiento}} </td>                  
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->mvd_cantidad}} </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->mvd_precio_unitario}} </td>            
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $value->mvd_cantidad * $value->mvd_precio_unitario}} </td>                        
                        
        </tr>
        <?php
        $total += $value->mvd_cantidad;
        ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="text-sm">
            <td colspan="8" class="text-center text-xxs uppercase font-bold px-4 py-3 bg-grey-darker text-white"><strong> TOTAL:</strong> </td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3"> <B>{{ number_format((float)$total, 2, '.', '')}}</B> </td>
        </tr>
    </tfoot>


    @php
    $nro = 1;
    $total_presentacion = 0;
    $total_medida = 0;
    @endphp

    


</table>


<br> <br>
<br><br>
<table class="table-reporte" >
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