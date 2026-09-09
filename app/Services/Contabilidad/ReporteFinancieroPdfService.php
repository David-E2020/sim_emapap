<?php

declare(strict_types=1);

namespace App\Services\Contabilidad;

use App\Models\Contabilidad\Comprobante;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\View;

class ReporteFinancieroPdfService
{
    /**
     * Genera el Comprobante Oficial en PDF (Carta Portrait).
     */
    public function generarComprobantePdf(Comprobante $comprobante): string
    {
        $comprobante->load(['detalles.cuenta', 'detalles.centroCosto', 'usuarioElaboracion', 'usuarioAprobacion', 'gestion']);

        $literal = self::convertirNumeroALetras((float) $comprobante->total_debe);

        $html = View::make('reportes.contabilidad.comprobante', [
            'comprobante' => $comprobante,
            'literal' => $literal,
        ])->render();

        return SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('portrait')
            ->setOption('margin-bottom', 10)
            ->setOption('margin-top', 10)
            ->setOption('margin-left', 12)
            ->setOption('margin-right', 12)
            ->setOption('encoding', 'utf-8')
            ->setOption('enable-local-file-access', true)
            ->output();
    }

    /**
     * Convierte un monto numérico a su representación literal en letras para Bolivia.
     */
    public static function convertirNumeroALetras(float $monto): string
    {
        $partes = explode('.', number_format($monto, 2, '.', ''));
        $entero = (int) $partes[0];
        $centavos = $partes[1] ?? '00';

        $literal = self::numeroALetras($entero);

        return "{$literal} {$centavos}/100 BOLIVIANOS";
    }

    private static function numeroALetras(int $numero): string
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
            $resultado .= ($millones === 1 ? 'UN MILLÓN ' : self::numeroALetras($millones) . ' MILLONES ');
        }

        if ($numero >= 1000) {
            $miles = (int) ($numero / 1000);
            $numero %= 1000;
            $resultado .= ($miles === 1 ? 'MIL ' : self::numeroALetras($miles) . ' MIL ');
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
