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
     * https://pilotosiat.impuestos.gob.bo/consulta/QR?nit={nit}&cuf={cuf}&numero={nro}&t=2
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

        $ambiente = $empresa && $empresa->codigo_ambiente ? (int) $empresa->codigo_ambiente : (int) config('siat.ambiente', 2);
        $tipoAmbiente = $ambiente === 1 ? 'produccion' : 'piloto';
        $baseUrl = config("siat.wsdl.{$tipoAmbiente}.qr");

        $nit = $empresa && !empty($empresa->nit) ? (string) $empresa->nit : config('siat.nit_emisor', '123456789');
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
        $factura->loadMissing(['detalles', 'sucursal', 'puntoVenta']);

        $urlQr = $this->generarUrlQr($factura);
        $qrBase64 = $this->generarQrBase64($urlQr);
        $literal = $this->convertirMontoALiteral((float) $factura->monto_total);

        $viewName = ($formato === 'rollo') ? 'facturacion.factura-rollo-pdf' : 'facturacion.factura-pdf';

        $pdf = SnappyPdf::loadView($viewName, [
            'factura' => $factura,
            'urlQr' => $urlQr,
            'qrBase64' => $qrBase64,
            'literal' => $literal,
        ]);

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
