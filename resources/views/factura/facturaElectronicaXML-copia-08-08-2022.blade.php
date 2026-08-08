<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<facturaElectronicaComercialExportacion xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                        xsi:noNamespaceSchemaLocation="facturaElectronicaComercialExportacion.xsd">
    <cabecera>
        <nitEmisor>{{$comexData->nit}}</nitEmisor>
        <razonSocialEmisor>{{$comexData->razon_social}}</razonSocialEmisor>
        <municipio>{{$puntoventaUser->puntoventa->sucursal->municipio}}</municipio>
        <telefono>{{$puntoventaUser->puntoventa->sucursal->telefono}}</telefono>
        <numeroFactura>{{$factura->id}}</numeroFactura>
        <cuf>{{$cuf}}</cuf>
        <cufd>{{$puntoventaUser->puntoventa->cufd}}</cufd>
        <codigoSucursal>{{$puntoventaUser->puntoventa->sucursal->codigo}}</codigoSucursal>
        <direccion>{{$puntoventaUser->puntoventa->sucursal->direccion}}</direccion>
        <codigoPuntoVenta>{{$puntoventaUser->puntoventa->codigo}}</codigoPuntoVenta>
        <fechaEmision>{{$fechaEmision}}</fechaEmision>
        <nombreRazonSocial>{{$factura->cliente->cliente}}</nombreRazonSocial>
        <codigoTipoDocumentoIdentidad>{{$factura->cliente->tipoDocumentoIdentidad->param_codigo}}</codigoTipoDocumentoIdentidad>
        <numeroDocumento>{{$factura->cliente->nro_identificacion}}</numeroDocumento>
        @if($factura->cliente->complemento=='' || $factura->cliente->complemento==null )         
        <complemento xsi:nil="true"/>          
        @else
        <complemento>{{$factura->cliente->complemento}}</complemento>      
        @endif
        <direccionComprador>{{$factura->cliente->direccion}}</direccionComprador>
        <codigoCliente>{{$factura->cliente->nro_identificacion}}</codigoCliente>
        <incoterm>{{$factura->incoterm->incoterm}}</incoterm>
        <incotermDetalle>{{$factura->incoterm->detalle}}</incotermDetalle>
        <puertoDestino>{{$fleteDetalleExterno->lugar}}</puertoDestino>
        <lugarDestino>{{$fleteDetalleExterno->pais}}</lugarDestino>
        <codigoPais>{{$comexData->pais->param_codigo}}</codigoPais>
        <codigoMetodoPago>{{$factura->metodoPago->param_codigo}}</codigoMetodoPago>
        <numeroTarjeta xsi:nil="true"/>
        <montoTotal>{{$montos->montoTotal}}</montoTotal>
        <costosGastosNacionales>{{json_encode($montos->costosGastosNacionales)}}</costosGastosNacionales>
        <totalGastosNacionalesFob>{{$montos->totalGastosNacionalesFob}}</totalGastosNacionalesFob>
        <costosGastosInternacionales>{{json_encode($montos->costosGastosInternacionales)}}</costosGastosInternacionales>
        <totalGastosInternacionales>{{$montos->totalGastosInternacionales}}</totalGastosInternacionales>
        <montoDetalle>{{$montos->montoDetalle}}</montoDetalle>
        <montoTotalSujetoIva>0</montoTotalSujetoIva>
        <codigoMoneda>{{$factura->moneda->param_codigo}}</codigoMoneda>
        <tipoCambio>{{$comexData->tipo_cambio}}</tipoCambio>
        <montoTotalMoneda>{{$montos->montoTotalMoneda}}</montoTotalMoneda>
        <numeroDescripcionPaquetesBultos xsi:nil="true"/>
        <informacionAdicional xsi:nil="true"/>
        <descuentoAdicional xsi:nil="true"/>
        @if($factura->cliente->tipoDocumentoIdentidad->param_codigo==5 )    
        <codigoExcepcion>{{$codigoExcepcion}}</codigoExcepcion>             
        @else
        <codigoExcepcion xsi:nil="true"/> 
        @endif
        <cafc xsi:nil="true"/>
        <leyenda>{{$leyenda}}</leyenda>
        <usuario>{{ $factura->userRegister->usr_usuario }}</usuario>
        <codigoDocumentoSector>{{$comexData->documentoSector->param_codigo}}</codigoDocumentoSector>
    </cabecera>
    @foreach ($factura->detalles as $detalle)
    <detalle>
        <actividadEconomica>{{ $comexData->actividad->param_codigo }}</actividadEconomica>
        <codigoProductoSin>{{ $detalle->producto->productoSiat->param_codigo }}</codigoProductoSin>
        <codigoProducto>{{ $detalle->producto->prod_id }}</codigoProducto>
        <codigoNandina>{{ $detalle->producto->productoSiat->param_detalle }}</codigoNandina>
        <descripcion>{{$detalle->detalle}}</descripcion>
        <cantidad>{{ (($detalle->cantidad) * 44) }}</cantidad>
        <unidadMedida>{{($detalle->unidadMedida->param_codigo)}}</unidadMedida>
        <precioUnitario>{{ number_format((float)$detalle->precio_initario, 2, '.', '') }}</precioUnitario>
        <montoDescuento xsi:nil="true"/>
        <subTotal>{{ number_format((float)$detalle->total, 2, '.', '') }}</subTotal>
    </detalle>
    @endforeach  
</facturaElectronicaComercialExportacion>
