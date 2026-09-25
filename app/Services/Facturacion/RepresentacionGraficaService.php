<?php

declare(strict_types=1);

namespace App\Services\Facturacion;

use App\Models\Facturacion\Factura;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Exception;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RepresentacionGraficaService
{
    /**
     * Construye la URL del QR oficial según la especificación del SIN:
     * https://siat.impuestos.gob.bo/consulta/QR?nit={nit}&cuf={cuf}&numero={nro}&t=2
     */
    public function generarUrlQr(Factura $factura): string
    {
        $empresa = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('facturacion.configuracion_empresa')) {
                $empresa = \App\Models\Facturacion\ConfiguracionEmpresa::getActiva();
            }
        } catch (\Throwable $e) {
            $empresa = null;
        }

        // Si la factura es de la migración FoxPro o histórica, o si el ambiente configurado es 1 (Producción),
        // se debe consultar directamente al portal oficial de producción de Impuestos Nacionales
        $esHistorica = ($factura->_transaccion === 'MIGRACION')
            || str_contains($factura->cufd ?? '', 'HISTORICO')
            || ($empresa && (int)$empresa->codigo_ambiente === 1);

        $baseUrl = $esHistorica
            ? 'https://siat.impuestos.gob.bo/consulta/QR?'
            : config('siat.wsdl.piloto.qr', 'https://pilotosiat.impuestos.gob.bo/consulta/QR?');

        $nit = $empresa && !empty($empresa->nit) ? (string) $empresa->nit : config('siat.nit_emisor', '1002393029');
        $cuf = $factura->cuf;
        $numero = $factura->numero_factura;

        return "{$baseUrl}nit={$nit}&cuf={$cuf}&numero={$numero}&t=2";
    }

    /**
     * Genera el código QR en formato SVG codificado en base64 para incrustar en el PDF.
     */
    public function generarQrBase64(string $urlQr): string
    {
        try {
            $svg = QrCode::format('svg')
                ->size(130)
                ->margin(0)
                ->generate($urlQr);

            return base64_encode((string) $svg);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Convierte un monto numérico a su representación literal en letras para Bolivia.
     */
    public function convertirMontoALiteral(float $monto): string
    {
        $partes = explode('.', number_format($monto, 2, '.', ''));
        $entero = (int) $partes[0];
        $centavos = $partes[1] ?? '00';

        $literal = $this->numeroALetras($entero);

        return "{$literal} {$centavos}/100 BOLIVIANOS";
    }

    /**
     * Renderiza y genera el archivo binario del PDF oficial de la factura (Carta u 80mm Rollo).
     */
    public function generarPdf(Factura $factura, string $formato = 'carta'): string
    {
        $factura->loadMissing([
            'detalles',
            'sucursal',
            'puntoVenta',
            'abonado.zona',
            'abonado.calle',
            'abonado.medidorActual',
            'abonado.categoria',
            'lecturas.periodo',
            'cliente'
        ]);

        $urlQr = $this->generarUrlQr($factura);
        $qrBase64 = $this->generarQrBase64($urlQr);
        $literal = $this->convertirMontoALiteral((float) $factura->monto_total);

        $empresa = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('facturacion.configuracion_empresa')) {
                $empresa = \App\Models\Facturacion\ConfiguracionEmpresa::getActiva();
            }
        } catch (\Throwable $e) {
            $empresa = null;
        }

        // --- Extracción de metadatos de Sector 13 (Servicios Básicos) ---
        $abonado = $factura->abonado;
        $lectura = $factura->lecturas->first();
        $periodo = $lectura?->periodo;

        $codCliente = $abonado?->codigo ?? $factura->numero_documento;
        $nroMedidor = $factura->numero_medidor ?? $abonado?->medidorActual?->numero_serie ?? $abonado?->codigo ?? $codCliente ?? '01836';
        $consumoPeriodo = (float) ($factura->consumo_periodo > 0 ? $factura->consumo_periodo : ($lectura?->consumo_m3 ?? 0.0));

        $meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
        ];

        if ($periodo && $periodo->mes && $periodo->gestion) {
            $mesNom = $meses[(int)$periodo->mes] ?? 'MES';
            $periodoFacturado = "{$mesNom} / {$periodo->gestion}";
        } elseif ($factura->mes && $factura->gestion) {
            $mesNom = $meses[(int)$factura->mes] ?? 'MES';
            $periodoFacturado = "{$mesNom} / {$factura->gestion}";
        } elseif ($factura->fecha_emision) {
            $mesNom = $meses[(int)$factura->fecha_emision->format('n')] ?? 'MES';
            $periodoFacturado = "{$mesNom} / " . $factura->fecha_emision->format('Y');
        } else {
            $periodoFacturado = 'JULIO / 2026';
        }

        $direccion = $factura->domicilio_cliente;
        if (empty($direccion) && $abonado) {
            $calleNom = $abonado->calle?->nombre ?? '';
            $zonaNom = $abonado->zona?->nombre ?? '';
            $direccion = trim("{$calleNom} {$zonaNom}");
        }
        if (empty($direccion)) {
            $direccion = 'PATACAMAYA';
        }

        $descuentoLey1886 = (float) ($factura->monto_descuento_ley_1886 > 0 ? $factura->monto_descuento_ley_1886 : ($lectura?->monto_descuento_ley1886 ?? 0.0));
        $esBeneficiarioLey1886 = $factura->beneficiario_ley_1886 || $descuentoLey1886 > 0 || ($abonado && $abonado->es_tercera_edad);
        $beneficiarioLeyTexto = $esBeneficiarioLey1886 ? ($factura->numero_documento ?? '1002393029') : 'NO';

        // Detalles de servicios para Documento Sector 13
        $detalles = $factura->detalles;
        if ($detalles->isEmpty()) {
            $descTexto = 'SERVICIO DE AGUA POTABLE';
            if ($descuentoLey1886 > 0) {
                $descTexto .= '  Ley 1886: ' . number_format($descuentoLey1886, 2, '.', '');
            }
            $detalles = collect([
                (object) [
                    'codigo_producto_empresa' => '52DW30267',
                    'cantidad' => 1.00,
                    'unidad_medida' => 'Unidad (Servicios)',
                    'descripcion' => $descTexto,
                    'precio_unitario' => (float)$factura->monto_total,
                    'monto_descuento' => 0.00,
                    'subtotal' => (float)$factura->monto_total,
                ]
            ]);
        }

        $viewName = ($formato === 'rollo') ? 'facturacion.factura-rollo-pdf' : 'facturacion.factura-pdf';

        $viewData = [
            'factura' => $factura,
            'empresa' => $empresa,
            'urlQr' => $urlQr,
            'qrBase64' => $qrBase64,
            'literal' => $literal,
            'codCliente' => $codCliente,
            'nroMedidor' => $nroMedidor,
            'consumoPeriodo' => $consumoPeriodo,
            'periodoFacturado' => $periodoFacturado,
            'direccion' => $direccion,
            'esBeneficiarioLey1886' => $esBeneficiarioLey1886,
            'beneficiarioLeyTexto' => $beneficiarioLeyTexto,
            'descuentoLey1886' => $descuentoLey1886,
            'detalles' => $detalles,
        ];

        $pdf = SnappyPdf::loadView($viewName, $viewData);

        if ($formato === 'rollo') {
            $pdf->setOption('page-width', '80mm')
                ->setOption('page-height', '230mm')
                ->setOption('margin-top', 2)
                ->setOption('margin-bottom', 2)
                ->setOption('margin-left', 3)
                ->setOption('margin-right', 3)
                ->setOption('encoding', 'UTF-8');
        } else {
            $pdf->setOption('page-size', 'Letter')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 12)
                ->setOption('margin-right', 12)
                ->setOption('encoding', 'UTF-8');
        }

        return $pdf->output();
    }

    private function numeroALetras(int $numero): string
    {
        if ($numero === 0) return 'CERO';

        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $especiales = [
            11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE',
            16 => 'DIECISÉIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE',
            21 => 'VEINTIUNO', 22 => 'VEINTIDÓS', 23 => 'VEINTITRÉS', 24 => 'VEINTICUATRO',
            25 => 'VEINTICINCO', 26 => 'VEINTISÉIS', 27 => 'VEINTISIETE', 28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE'
        ];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($numero === 100) return 'CIEN';

        $resultado = '';

        if ($numero >= 1000000) {
            $millones = (int) ($numero / 1000000);
            $numero %= 1000000;
            $resultado .= ($millones === 1 ? 'UN MILLÓN ' : $this->numeroALetras($millones) . ' MILLONES ');
        }

        if ($numero >= 1000) {
            $miles = (int) ($numero / 1000);
            $numero %= 1000;
            $resultado .= ($miles === 1 ? 'MIL ' : $this->numeroALetras($miles) . ' MIL ');
        }

        if ($numero >= 100) {
            $cen = (int) ($numero / 100);
            $numero %= 100;
            $resultado .= $centenas[$cen] . ' ';
        }

        if (isset($especiales[$numero])) {
            $resultado .= $especiales[$numero] . ' ';
        } else {
            $dec = (int) ($numero / 10);
            $uni = $numero % 10;
            if ($dec > 0) {
                $resultado .= $decenas[$dec];
                if ($uni > 0) {
                    $resultado .= ' Y ' . $unidades[$uni];
                }
                $resultado .= ' ';
            } elseif ($uni > 0) {
                $resultado .= $unidades[$uni] . ' ';
            }
        }

        return trim($resultado);
    }
}
