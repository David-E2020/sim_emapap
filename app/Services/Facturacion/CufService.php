<?php

declare(strict_types=1);

namespace App\Services\Facturacion;

use Carbon\CarbonInterface;

class CufService
{
    /**
     * Algoritmo Módulo 11 oficial del SIN.
     * Añade el dígito verificador calculado al final de la cadena de entrada.
     */
    public function calcularModulo11(string $cadena): string
    {
        $mult = 2;
        $suma = 0;
        for ($i = strlen($cadena) - 1; $i >= 0; $i--) {
            $suma += ($mult * (int) $cadena[$i]);
            if (++$mult > 9) {
                $mult = 2;
            }
        }
        $dig = $suma % 11;
        switch ($dig) {
            case 10:
                $cadena .= '1';
                break;
            case 11:
                $cadena .= '0';
                break;
            default:
                $cadena .= (string) $dig;
        }
        return $cadena;
    }

    /**
     * Convierte un número decimal de longitud arbitraria a Hexadecimal en mayúsculas
     * sin desbordamiento de enteros (reproducción exacta del algoritmo de la ADSIB).
     */
    public function dec2hex(string $cadenaDecimal): string
    {
        $dec = str_split($cadenaDecimal);
        $sum = [];
        $hex = [];
        while (count($dec) > 0) {
            $s = 1 * (int) array_shift($dec);
            for ($i = 0; $s || $i < count($sum); $i++) {
                $s += (($sum[$i] ?? 0) * 10);
                $sum[$i] = $s % 16;
                $s = (int) (($s - $sum[$i]) / 16);
            }
        }
        while (count($sum) > 0) {
            $hex[] = dechex(array_pop($sum));
        }
        return strtoupper(implode('', $hex));
    }

    /**
     * Genera el Código Único de Facturación (CUF).
     *
     * @param string|int $nitEmisor NIT del emisor (rellenado a 13 dígitos)
     * @param CarbonInterface|string $fechaHora Timestamp con milisegundos (YYYYMMDDHHmmssSSS)
     * @param int $sucursal Código de sucursal (rellenado a 4 dígitos)
     * @param int $modalidad 1 = Electrónica, 2 = Computarizada
     * @param int $tipoEmision 1 = En Línea, 2 = Fuera de Línea
     * @param int $tipoFactura 1 = Con Crédito Fiscal
     * @param int $documentoSector 1 = Compra Venta estándar (rellenado a 2 dígitos)
     * @param int|string $numeroFactura Número consecutivo de factura (rellenado a 10 dígitos)
     * @param int $puntoVenta Código de punto de venta (rellenado a 4 dígitos)
     * @param string $codigoControl Código de control provisto por el CUFD vigente
     */
    public function generarCuf(
        string|int $nitEmisor,
        $fechaHora,
        int $sucursal,
        int $modalidad,
        int $tipoEmision,
        int $tipoFactura,
        int $documentoSector,
        int|string $numeroFactura,
        int $puntoVenta,
        string $codigoControl
    ): string {
        $strNit = str_pad((string) $nitEmisor, 13, '0', STR_PAD_LEFT);

        if ($fechaHora instanceof CarbonInterface) {
            $strFecha = $fechaHora->format('YmdHisv');
        } else {
            $strFecha = (string) $fechaHora;
        }

        $strSucursal = str_pad((string) $sucursal, 4, '0', STR_PAD_LEFT);
        $strModalidad = (string) $modalidad;
        $strTipoEmision = (string) $tipoEmision;
        $strTipoFactura = (string) $tipoFactura;
        $strDocumentoSector = str_pad((string) $documentoSector, 2, '0', STR_PAD_LEFT);
        $strNumeroFactura = str_pad((string) $numeroFactura, 10, '0', STR_PAD_LEFT);
        $strPuntoVenta = str_pad((string) $puntoVenta, 4, '0', STR_PAD_LEFT);

        $cadenaCompleta = "{$strNit}{$strFecha}{$strSucursal}{$strModalidad}{$strTipoEmision}{$strTipoFactura}{$strDocumentoSector}{$strNumeroFactura}{$strPuntoVenta}";

        $cadenaMod11 = $this->calcularModulo11($cadenaCompleta);
        $hex = $this->dec2hex($cadenaMod11);

        return "{$hex}{$codigoControl}";
    }

    /**
     * Convierte una cadena Hexadecimal a número decimal arbitrario
     * sin desbordamiento de enteros (operación inversa exacta a dec2hex).
     */
    public function hex2dec(string $hex): string
    {
        $hex = strtoupper(trim($hex));
        $chars = str_split($hex);
        $sum = [];
        while (count($chars) > 0) {
            $s = hexdec(array_shift($chars));
            for ($i = 0; $s || $i < count($sum); $i++) {
                $s += (($sum[$i] ?? 0) * 16);
                $sum[$i] = $s % 10;
                $s = (int) (($s - $sum[$i]) / 10);
            }
        }
        $dec = '';
        while (count($sum) > 0) {
            $dec .= (string) array_pop($sum);
        }
        return $dec ?: '0';
    }

    /**
     * Decodifica un CUF oficial del SIN y extrae sus campos estructurados.
     * Retorna null si la cadena no corresponde a un formato de CUF convertible.
     */
    public function decodificarCuf(string $cuf): ?array
    {
        $cuf = trim($cuf);
        if (strlen($cuf) < 42) {
            return null;
        }

        $cufLen = strlen($cuf);
        $dec54 = null;
        $codigoControl = '';

        // Probar candidatos de longitud del código de control (habitualmente 15 o 16 caracteres hexadecimales)
        $candidatos = [15, 16, $cufLen - 42, $cufLen - 43, $cufLen - 44];
        foreach ($candidatos as $ccLen) {
            if ($ccLen <= 0 || $ccLen >= $cufLen) {
                continue;
            }
            $subHex = substr($cuf, 0, $cufLen - $ccLen);
            $dec = $this->hex2dec($subHex);
            $candidatoDec54 = str_pad($dec, 54, '0', STR_PAD_LEFT);
            $y = (int) substr($candidatoDec54, 13, 4);
            $m = (int) substr($candidatoDec54, 17, 2);
            $d = (int) substr($candidatoDec54, 19, 2);

            if ($y >= 2020 && $y <= 2040 && $m >= 1 && $m <= 12 && $d >= 1 && $d <= 31) {
                $dec54 = $candidatoDec54;
                $codigoControl = substr($cuf, $cufLen - $ccLen);
                break;
            }
        }

        // Si no calzó por detección de fecha, recurrir al método por defecto de 42 hex
        if (!$dec54) {
            $subHex = substr($cuf, 0, min(42, $cufLen));
            $dec = $this->hex2dec($subHex);
            $dec54 = str_pad($dec, 54, '0', STR_PAD_LEFT);
            $codigoControl = substr($cuf, 42);
        }

        $nit = substr($dec54, 0, 13);
        $rawFecha = substr($dec54, 13, 17); // YYYYMMDDHHmmssSSS
        $sucursal = (int) substr($dec54, 30, 4);
        $modalidad = (int) substr($dec54, 34, 1);
        $tipoEmision = (int) substr($dec54, 35, 1);
        $tipoFactura = (int) substr($dec54, 36, 1);
        $documentoSector = (int) substr($dec54, 37, 2);
        $numeroFactura = (int) substr($dec54, 39, 10);
        $puntoVenta = (int) substr($dec54, 49, 4);
        $modulo11 = (int) substr($dec54, 53, 1);

        $fechaCarbon = null;
        if (strlen($rawFecha) === 17 && is_numeric($rawFecha)) {
            $y = (int) substr($rawFecha, 0, 4);
            $m = (int) substr($rawFecha, 4, 2);
            $d = (int) substr($rawFecha, 6, 2);
            $h = (int) substr($rawFecha, 8, 2);
            $i = (int) substr($rawFecha, 10, 2);
            $s = (int) substr($rawFecha, 12, 2);

            if ($y >= 2020 && $m >= 1 && $m <= 12 && $d >= 1 && $d <= 31 && $h <= 23 && $i <= 59 && $s <= 59) {
                $fechaCarbon = \Carbon\Carbon::create($y, $m, $d, $h, $i, $s);
            }
        }

        return [
            'nit' => ltrim($nit, '0'),
            'nit_padded' => $nit,
            'fecha_emision' => $fechaCarbon,
            'fecha_hora_raw' => $rawFecha,
            'sucursal' => $sucursal,
            'modalidad' => $modalidad,
            'tipo_emision' => $tipoEmision,
            'tipo_factura' => $tipoFactura,
            'documento_sector' => $documentoSector,
            'numero_factura' => $numeroFactura,
            'punto_venta' => $puntoVenta,
            'modulo11' => $modulo11,
            'codigo_control' => $codigoControl,
        ];
    }

    /**
     * Extrae de forma directa la fecha y hora exacta de emisión contenida en el CUF.
     */
    public function extraerFechaHoraDesdeCuf(string $cuf): ?\Carbon\Carbon
    {
        $decoded = $this->decodificarCuf($cuf);
        return $decoded['fecha_emision'] ?? null;
    }
}

