<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<facturaElectronicaComercialExportacionPVenta xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                        xsi:noNamespaceSchemaLocation="facturaElectronicaComercialExportacionPVenta.xsd">
    <cabecera>
        <nitEmisor>{{$comexData->nit}}</nitEmisor>
        <razonSocialEmisor>{{$comexData->razon_social}}</razonSocialEmisor>
        <municipio>{{$puntoventaUser->puntoventa->municipio}}</municipio>
        <telefono>{{$puntoventaUser->puntoventa->telefono}}</telefono>
        <numeroFactura>{{$factura->nro_factura}}</numeroFactura>
        <cuf>{{$cuf}}</cuf>
        <cufd>{{$puntoventaUser->puntoventa->cufd}}</cufd>
        <codigoSucursal>{{$puntoventaUser->puntoventa->sucursal->codigo}}</codigoSucursal>
        <direccion>{{$puntoventaUser->puntoventa->direccion}}</direccion>
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
        <direccionComprador>{{$factura->direccion_importador}}</direccionComprador>
        <codigoCliente>{{$factura->cliente->nro_identificacion}}</codigoCliente>
        <incoterm>{{$factura->fleteExterno->incoterm->sigla}}</incoterm>
        <incotermDetalle>{{$factura->fleteExterno->lugar}}</incotermDetalle>
        <puertoDestino>{{$factura->destinoFinal->param_codigo}}</puertoDestino>
        <lugarDestino>{{$factura->destinoFinal->param_detalle}}</lugarDestino>
        <codigoPais>{{$comexData->pais->param_codigo}}</codigoPais>
        <codigoMetodoPago>{{$factura->metodoPago->param_codigo}}</codigoMetodoPago>
        <numeroTarjeta xsi:nil="true"/>
        <montoTotal>{{$montos->montoTotal}}</montoTotal>
        <costosGastosNacionales>{{json_encode($montos->costosGastosNacionales)}}</costosGastosNacionales>
        <totalGastosNacionalesFob>{{$montos->totalGastosNacionalesFob}}</totalGastosNacionalesFob>
        <costosGastosInternacionales>{{json_encode($montos->costosGastosInternacionales)}}</costosGastosInternacionales>
        <totalGastosInternacionales>{{$montos->totalGastosInternacionales}}</totalGastosInternacionales>
        <precioValorBruto>{{$montos->precioValorBruto}}</precioValorBruto>
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
        <usuario>{{$factura->userRegister->usr_usuario }}</usuario>
        <codigoDocumentoSector>{{$comexData->documentoSector->param_codigo}}</codigoDocumentoSector>
    </cabecera>
    @foreach ($factura->detalles as $detalle)
    <detalle>
        <actividadEconomica>{{ $comexData->actividad->codigoCaeb }}</actividadEconomica>
        <codigoProductoSin>{{ $detalle->producto->productoSiat->codigoProducto }}</codigoProductoSin>
        <codigoProducto>{{ $detalle->producto->prod_id }}</codigoProducto>
        <codigoNandina>{{ ($detalle->producto->productoSiat->nandinaProducto->nandina) }}</codigoNandina>
        <descripcion>{{$detalle->detalle}}</descripcion>
        <cantidad>{{ (($detalle->cantidad) * 44) }}</cantidad>
        <unidadMedida>{{($detalle->unidadMedida->param_codigo)}}</unidadMedida>
        <precioUnitario>{{ number_format((float)$detalle->precio_initario, 2, '.', '') }}</precioUnitario>
        <montoDescuento xsi:nil="true"/>
        <subTotal>{{ number_format((float)$detalle->total, 2, '.', '') }}</subTotal>
    </detalle>
    @endforeach
</facturaElectronicaComercialExportacionPVenta>
