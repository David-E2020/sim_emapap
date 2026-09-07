<?php

declare(strict_types=1);

namespace App\Services\Facturacion;

use App\Models\Facturacion\Factura;
use DOMDocument;
use Exception;

class XmlFacturaService
{
    /**
     * Construye la estructura XML oficial según el Documento Sector (1 = Compra-Venta, 13 = Servicios Básicos).
     */
    public function construirXml(Factura $factura): string
    {
        if ((int) $factura->codigo_documento_sector === 13) {
            return $this->construirXmlServicioBasico($factura);
        }

        return $this->construirXmlCompraVenta($factura);
    }

    /**
     * Construye la estructura XML oficial de la Factura Electrónica Compra Venta (Sector 1).
     */
    public function construirXmlCompraVenta(Factura $factura): string
    {
        $factura->loadMissing(['detalles', 'sucursal']);

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('facturaElectronicaCompraVenta');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xsi:noNamespaceSchemaLocation', 'facturaElectronicaCompraVenta.xsd');
        $dom->appendChild($root);

        // --- CABECERA ---
        $cabecera = $dom->createElement('cabecera');
        $root->appendChild($cabecera);

        $nitEmisor = config('siat.nit_emisor', '123456789');
        $razonSocialEmisor = 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO SANITARIO PATACAMAYA - EMAPAP';
        $municipio = $factura->sucursal->municipio ?? 'Patacamaya';
        $telefono = $factura->sucursal->telefono ?? '2-8147000';
        $direccion = $factura->sucursal->direccion ?? 'Av. Panamericana s/n, Plaza 15 de Agosto';
        $codigoSucursal = $factura->sucursal->codigo_sucursal ?? 0;
        $codigoPuntoVenta = $factura->puntoVenta->codigo_punto_venta ?? 0;

        $this->appendElement($dom, $cabecera, 'nitEmisor', (string) $nitEmisor);
        $this->appendElement($dom, $cabecera, 'razonSocialEmisor', $razonSocialEmisor);
        $this->appendElement($dom, $cabecera, 'municipio', $municipio);
        $this->appendElement($dom, $cabecera, 'telefono', $telefono, true);
        $this->appendElement($dom, $cabecera, 'numeroFactura', (string) $factura->numero_factura);
        $this->appendElement($dom, $cabecera, 'cuf', $factura->cuf);
        $this->appendElement($dom, $cabecera, 'cufd', $factura->cufd ?? '');
        $this->appendElement($dom, $cabecera, 'codigoSucursal', (string) $codigoSucursal);
        $this->appendElement($dom, $cabecera, 'direccion', $direccion);
        $this->appendElement($dom, $cabecera, 'codigoPuntoVenta', (string) $codigoPuntoVenta, true);
        $this->appendElement($dom, $cabecera, 'fechaEmision', $factura->fecha_emision ? $factura->fecha_emision->format('Y-m-d\TH:i:s.v') : now()->format('Y-m-d\TH:i:s.v'));
        $this->appendElement($dom, $cabecera, 'nombreRazonSocial', $factura->nombre_razon_social);
        $this->appendElement($dom, $cabecera, 'codigoTipoDocumentoIdentidad', (string) $factura->codigo_tipo_documento_identidad);
        $this->appendElement($dom, $cabecera, 'numeroDocumento', $factura->numero_documento);
        $this->appendElement($dom, $cabecera, 'complemento', $factura->complemento, true);
        $this->appendElement($dom, $cabecera, 'codigoCliente', $factura->numero_documento);
        $this->appendElement($dom, $cabecera, 'codigoMetodoPago', (string) $factura->codigo_metodo_pago);
        $this->appendElement($dom, $cabecera, 'numeroTarjeta', $factura->numero_tarjeta, true);
        $this->appendElement($dom, $cabecera, 'montoTotal', number_format((float) $factura->monto_total, 2, '.', ''));
        $this->appendElement($dom, $cabecera, 'montoTotalSujetoIva', number_format((float) $factura->monto_total_sujeto_iva, 2, '.', ''));
        $this->appendElement($dom, $cabecera, 'codigoMoneda', (string) $factura->codigo_moneda);
        $this->appendElement($dom, $cabecera, 'tipoCambio', number_format((float) $factura->tipo_cambio, 2, '.', ''));
        $this->appendElement($dom, $cabecera, 'montoTotalMoneda', number_format((float) ($factura->monto_total * $factura->tipo_cambio), 2, '.', ''));
        $this->appendElement($dom, $cabecera, 'montoGiftCard', $factura->monto_gift_card > 0 ? number_format((float) $factura->monto_gift_card, 2, '.', '') : null, true);
        $this->appendElement($dom, $cabecera, 'descuentoAdicional', $factura->monto_descuento > 0 ? number_format((float) $factura->monto_descuento, 2, '.', '') : null, true);
        $this->appendElement($dom, $cabecera, 'codigoExcepcion', null, true);
        $this->appendElement($dom, $cabecera, 'cafc', $factura->eventoSignificativo->cafc ?? null, true);
        $this->appendElement($dom, $cabecera, 'leyenda', $factura->leyenda);
        $this->appendElement($dom, $cabecera, 'usuario', $factura->usuario_emision);
        $this->appendElement($dom, $cabecera, 'codigoDocumentoSector', (string) $factura->codigo_documento_sector);

        // --- DETALLES ---
        foreach ($factura->detalles as $detalle) {
            $item = $dom->createElement('detalle');
            $root->appendChild($item);

            $this->appendElement($dom, $item, 'actividadEconomica', $detalle->codigo_actividad);
            $this->appendElement($dom, $item, 'codigoProductoSin', $detalle->codigo_producto_sin);
            $this->appendElement($dom, $item, 'codigoProducto', $detalle->codigo_producto_empresa);
            $this->appendElement($dom, $item, 'descripcion', $detalle->descripcion);
            $this->appendElement($dom, $item, 'cantidad', number_format((float) $detalle->cantidad, 4, '.', ''));
            $this->appendElement($dom, $item, 'unidadMedida', (string) $detalle->codigo_unidad_medida);
            $this->appendElement($dom, $item, 'precioUnitario', number_format((float) $detalle->precio_unitario, 2, '.', ''));
            $this->appendElement($dom, $item, 'montoDescuento', $detalle->monto_descuento > 0 ? number_format((float) $detalle->monto_descuento, 2, '.', '') : null, true);
            $this->appendElement($dom, $item, 'subTotal', number_format((float) $detalle->subtotal, 2, '.', ''));
            $this->appendElement($dom, $item, 'numeroSerie', $detalle->numero_serie, true);
            $this->appendElement($dom, $item, 'numeroImei', $detalle->numero_imei, true);
        }

        return $dom->saveXML();
    }

    /**
     * Construye la estructura XML oficial de la Factura Electrónica de Servicios Básicos (Sector 13).
     * Conforme a facturaElectronicaServicioBasico.xsd.
     */
    public function construirXmlServicioBasico(Factura $factura): string
    {
        $factura->loadMissing(['detalles', 'sucursal', 'abonado.medidorActual']);

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('facturaElectronicaServicioBasico');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xsi:noNamespaceSchemaLocation', 'facturaElectronicaServicioBasico.xsd');
        $dom->appendChild($root);

        // --- CABECERA ---
        $cabecera = $dom->createElement('cabecera');
        $root->appendChild($cabecera);

        $nitEmisor = config('siat.nit_emisor', '123456789');
        $razonSocialEmisor = 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO SANITARIO PATACAMAYA - EMAPAP';
        $municipio = $factura->sucursal->municipio ?? 'Patacamaya';
        $telefono = $factura->sucursal->telefono ?? '2-8147000';
        $direccion = $factura->sucursal->direccion ?? 'Av. Panamericana s/n, Plaza 15 de Agosto';
        $codigoSucursal = $factura->sucursal->codigo_sucursal ?? 0;
        $codigoPuntoVenta = $factura->puntoVenta->codigo_punto_venta ?? 0;

        $numeroMedidor = $factura->numero_medidor 
            ?? $factura->abonado?->medidorActual?->numero_serie 
            ?? '0';

        $this->appendElement($dom, $cabecera, 'nitEmisor', (string) $nitEmisor);
        $this->appendElement($dom, $cabecera, 'razonSocialEmisor', $razonSocialEmisor);
        $this->appendElement($dom, $cabecera, 'municipio', $municipio);
        $this->appendElement($dom, $cabecera, 'telefono', $telefono, true);
        $this->appendElement($dom, $cabecera, 'numeroFactura', (string) $factura->numero_factura);
        $this->appendElement($dom, $cabecera, 'cuf', $factura->cuf);
        $this->appendElement($dom, $cabecera, 'cufd', $factura->cufd ?? '');
        $this->appendElement($dom, $cabecera, 'codigoSucursal', (string) $codigoSucursal);
        $this->appendElement($dom, $cabecera, 'direccion', $direccion);
        $this->appendElement($dom, $cabecera, 'codigoPuntoVenta', (string) $codigoPuntoVenta, true);

        // Campos específicos Sector 13
        $this->appendElement($dom, $cabecera, 'mes', $factura->mes, true);
        $this->appendElement($dom, $cabecera, 'gestion', $factura->gestion ? (string) $factura->gestion : null, true);
        $this->appendElement($dom, $cabecera, 'ciudad', $factura->ciudad ?? 'Patacamaya', true);
        $this->appendElement($dom, $cabecera, 'zona', $factura->zona, true);
        $this->appendElement($dom, $cabecera, 'numeroMedidor', $numeroMedidor);
        $this->appendElement($dom, $cabecera, 'fechaEmision', $factura->fecha_emision ? $factura->fecha_emision->format('Y-m-d\TH:i:s.v') : now()->format('Y-m-d\TH:i:s.v'));
        $this->appendElement($dom, $cabecera, 'nombreRazonSocial', $factura->nombre_razon_social, true);
        $this->appendElement($dom, $cabecera, 'domicilioCliente', $factura->domicilio_cliente, true);
        $this->appendElement($dom, $cabecera, 'codigoTipoDocumentoIdentidad', (string) $factura->codigo_tipo_documento_identidad);
        $this->appendElement($dom, $cabecera, 'numeroDocumento', $factura->numero_documento);
        $this->appendElement($dom, $cabecera, 'complemento', $factura->complemento, true);
        $this->appendElement($dom, $cabecera, 'codigoCliente', $factura->abonado?->codigo ?? $factura->numero_documento);
        $this->appendElement($dom, $cabecera, 'codigoMetodoPago', (string) $factura->codigo_metodo_pago);
        $this->appendElement($dom, $cabecera, 'numeroTarjeta', $factura->numero_tarjeta, true);
        $this->appendElement($dom, $cabecera, 'montoTotal', number_format((float) $factura->monto_total, 2, '.', ''));
        $this->appendElement($dom, $cabecera, 'montoTotalSujetoIva', number_format((float) $factura->monto_total_sujeto_iva, 2, '.', ''));
        
        $this->appendElement($dom, $cabecera, 'consumoPeriodo', $factura->consumo_periodo !== null ? number_format((float) $factura->consumo_periodo, 2, '.', '') : null, true);
        $this->appendElement($dom, $cabecera, 'beneficiarioLey1886', $factura->beneficiario_ley_1886 ? '1' : null, true);
        $this->appendElement($dom, $cabecera, 'montoDescuentoLey1886', $factura->monto_descuento_ley_1886 > 0 ? number_format((float) $factura->monto_descuento_ley_1886, 2, '.', '') : null, true);
        $this->appendElement($dom, $cabecera, 'montoDescuentoTarifaDignidad', $factura->monto_descuento_tarifa_dignidad > 0 ? number_format((float) $factura->monto_descuento_tarifa_dignidad, 2, '.', '') : null, true);
        $this->appendElement($dom, $cabecera, 'tasaAseo', $factura->tasa_aseo > 0 ? number_format((float) $factura->tasa_aseo, 2, '.', '') : null, true);
        $this->appendElement($dom, $cabecera, 'tasaAlumbrado', $factura->tasa_alumbrado > 0 ? number_format((float) $factura->tasa_alumbrado, 2, '.', '') : null, true);
        $this->appendElement($dom, $cabecera, 'ajusteNoSujetoIva', $factura->ajuste_no_sujeto_iva > 0 ? number_format((float) $factura->ajuste_no_sujeto_iva, 2, '.', '') : null, true);
        $this->appendElement($dom, $cabecera, 'detalleAjusteNoSujetoIva', $factura->detalle_ajuste_no_sujeto_iva, true);
        $this->appendElement($dom, $cabecera, 'ajusteSujetoIva', $factura->ajuste_sujeto_iva > 0 ? number_format((float) $factura->ajuste_sujeto_iva, 2, '.', '') : null, true);
        $this->appendElement($dom, $cabecera, 'detalleAjusteSujetoIva', $factura->detalle_ajuste_sujeto_iva, true);
        $this->appendElement($dom, $cabecera, 'otrosPagosNoSujetoIva', $factura->otros_pagos_no_sujeto_iva > 0 ? number_format((float) $factura->otros_pagos_no_sujeto_iva, 2, '.', '') : null, true);
        $this->appendElement($dom, $cabecera, 'detalleOtrosPagosNoSujetoIva', $factura->detalle_otros_pagos_no_sujeto_iva, true);
        $this->appendElement($dom, $cabecera, 'otrasTasas', $factura->otras_tasas > 0 ? number_format((float) $factura->otras_tasas, 2, '.', '') : null, true);

        $this->appendElement($dom, $cabecera, 'codigoMoneda', (string) $factura->codigo_moneda);
        $this->appendElement($dom, $cabecera, 'tipoCambio', number_format((float) $factura->tipo_cambio, 2, '.', ''));
        $this->appendElement($dom, $cabecera, 'montoTotalMoneda', number_format((float) ($factura->monto_total * $factura->tipo_cambio), 2, '.', ''));
        $this->appendElement($dom, $cabecera, 'descuentoAdicional', $factura->monto_descuento > 0 ? number_format((float) $factura->monto_descuento, 2, '.', '') : null, true);
        $this->appendElement($dom, $cabecera, 'codigoExcepcion', null, true);
        $this->appendElement($dom, $cabecera, 'cafc', $factura->eventoSignificativo->cafc ?? null, true);
        $this->appendElement($dom, $cabecera, 'leyenda', $factura->leyenda);
        $this->appendElement($dom, $cabecera, 'usuario', $factura->usuario_emision);
        $this->appendElement($dom, $cabecera, 'codigoDocumentoSector', '13');

        // --- DETALLES ---
        foreach ($factura->detalles as $detalle) {
            $item = $dom->createElement('detalle');
            $root->appendChild($item);

            $this->appendElement($dom, $item, 'actividadEconomica', $detalle->codigo_actividad);
            $this->appendElement($dom, $item, 'codigoProductoSin', $detalle->codigo_producto_sin);
            $this->appendElement($dom, $item, 'codigoProducto', $detalle->codigo_producto_empresa);
            $this->appendElement($dom, $item, 'descripcion', $detalle->descripcion);
            $this->appendElement($dom, $item, 'cantidad', number_format((float) $detalle->cantidad, 4, '.', ''));
            $this->appendElement($dom, $item, 'unidadMedida', (string) $detalle->codigo_unidad_medida);
            $this->appendElement($dom, $item, 'precioUnitario', number_format((float) $detalle->precio_unitario, 2, '.', ''));
            $this->appendElement($dom, $item, 'montoDescuento', $detalle->monto_descuento > 0 ? number_format((float) $detalle->monto_descuento, 2, '.', '') : null, true);
            $this->appendElement($dom, $item, 'subTotal', number_format((float) $detalle->subtotal, 2, '.', ''));
        }

        return $dom->saveXML();
    }

    /**
     * Valida la estructura del XML contra el esquema XSD oficial del SIN.
     *
     * @throws Exception Si el XML no es válido según la normativa del SIN.
     */
    public function validarContraXsd(string $xmlContent, ?string $rutaXsd = null, int $codigoDocumentoSector = 1): bool
    {
        if ($rutaXsd === null) {
            $schemaPath = $codigoDocumentoSector === 13
                ? storage_path('app/siat/schemas/facturaElectronicaServicioBasico.xsd')
                : storage_path('app/siat/schemas/facturaElectronicaCompraVenta.xsd');
        } else {
            $schemaPath = $rutaXsd;
        }

        if (!file_exists($schemaPath)) {
            throw new Exception("El esquema XSD no se encuentra en: {$schemaPath}");
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        if (!$dom->loadXML($xmlContent)) {
            $errors = $this->formatLibXmlErrors();
            throw new Exception("Error al cargar la sintaxis XML: " . implode('; ', $errors));
        }

        if (!$dom->schemaValidate($schemaPath)) {
            $errors = $this->formatLibXmlErrors();
            throw new Exception("El XML no cumple el esquema XSD del SIN: " . implode('; ', $errors));
        }

        libxml_clear_errors();
        return true;
    }

    /**
     * Helper para añadir nodos con soporte de atributo xsi:nil="true" cuando el valor es nulo.
     */
    private function appendElement(DOMDocument $dom, \DOMElement $parent, string $name, ?string $value, bool $nullable = false): void
    {
        $element = $dom->createElement($name);
        if ($value === null || $value === '') {
            if ($nullable) {
                $element->setAttribute('xsi:nil', 'true');
            }
        } else {
            $element->textContent = htmlspecialchars($value, ENT_XML1, 'UTF-8');
        }
        $parent->appendChild($element);
    }

    private function formatLibXmlErrors(): array
    {
        $messages = [];
        foreach (libxml_get_errors() as $error) {
            $messages[] = sprintf('Línea %d: %s', $error->line, trim($error->message));
        }
        return $messages;
    }
}
