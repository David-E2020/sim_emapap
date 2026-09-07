<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Entorno de Facturación SIAT
    |--------------------------------------------------------------------------
    | 1 = Producción
    | 2 = Pruebas / Piloto
    */
    'ambiente' => (int) env('SIAT_AMBIENTE', 2),

    /*
    |--------------------------------------------------------------------------
    | Modalidad de Facturación
    |--------------------------------------------------------------------------
    | 1 = Facturación Electrónica en Línea (Firma Digital XMLDSig con certificado .p12)
    | 2 = Facturación Computarizada en Línea (Sin firma digital, validación por hash)
    */
    'modalidad' => (int) env('SIAT_MODALIDAD', 1),

    'nit_emisor' => (string) env('SIAT_NIT_EMISOR', '123456789'),
    'codigo_sistema' => (string) env('SIAT_CODIGO_SISTEMA', 'EMAPA_SISTEMA'),
    'token_delegado' => (string) env('SIAT_TOKEN_DELEGADO', ''),

    // Firma digital
    'cert_path' => env('SIAT_CERT_PATH', storage_path('app/siat/certs/certificado.p12')),
    'cert_pass' => env('SIAT_CERT_PASS', ''),

    // URLs de Servicios Web WSDL (Piloto vs Producción)
    'wsdl' => [
        'piloto' => [
            'codigos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos?wsdl',
            'operaciones' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionOperaciones?wsdl',
            'sincronizacion' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl',
            'compra_venta' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?wsdl',
            'qr' => 'https://pilotosiat.impuestos.gob.bo/consulta/QR?',
        ],
        'produccion' => [
            'codigos' => 'https://siatrest.impuestos.gob.bo/v2/FacturacionCodigos?wsdl',
            'operaciones' => 'https://siatrest.impuestos.gob.bo/v2/FacturacionOperaciones?wsdl',
            'sincronizacion' => 'https://siatrest.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl',
            'compra_venta' => 'https://siatrest.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?wsdl',
            'qr' => 'https://siat.impuestos.gob.bo/consulta/QR?',
        ],
    ],
];
