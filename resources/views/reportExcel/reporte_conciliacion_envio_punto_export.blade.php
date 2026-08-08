
<html>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td class="px-15 py text-center  text-xxs">
                Tipo Solicitud
            </td>
            <td class="px-15 py text-center  text-xxs">
                Codigo Solicitud
            </td>
            <td class="px-15 py text-center  text-xxs">
                Estado
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Tipo Venta
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Tipo Orden
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Usuario Solicitud
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Origen
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Destino
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Nombre Productor
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Numero Identificacion Productor
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Asociacion
            </td>

            <td colspan="1" class="px-15 py text-center text-xxs">
                Conductor
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Conductor Identificacion
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Placa
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Producto
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Fecha Solicitud
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad Solicitada
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Nombre Salida
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Nro Boleta Salida
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Usuario Salida
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Lote Salida
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Fecha Salida
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad Salida
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad Salida Acumulada
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Saldo
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Producto Comercial
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Nombre Destino
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Nro Boleta Ingreso
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Usuario Ingreso
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Fecha Ingreso
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cantidad Ingreso
            </td>
        </tr>
    </thead>
    <tbody>
        @php
            $saldo=0;
            $count=1;
        @endphp
        @foreach ($conciliacion as $index => $item)
            <tr class="text-sm">
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $count++ }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->tipo_solicitud }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->codigo_solicitud }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->estado }}</td>

                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->tipo_venta }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->tipo_orden }}</td>

                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->usuario_solicitud }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->origen }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->destino }}</td>

                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->nombre_productor }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->numero_identificacion_productor }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->asociacion }}</td>

                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->conductor}}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->conductor_identificacion }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->placa_vehiculo }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->producto }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->fecha_solicitud }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->cantidad_solicitada }}</td>

                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->nombre_origen }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->nro_boleta_salida }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->usuario_origen }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->lote_salida }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->fecha_salida }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->cantidad_salida }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->cantidad_salida_acumulado }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->saldo }}</td>

                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->producto_comercial }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->nombre_destino }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->nro_boleta_ingreso }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->usuario_destino }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->fecha_ingreso }}</td>
                <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $item->cantidad_ingreso }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
</html>