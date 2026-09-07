<?php

declare(strict_types=1);

namespace App\Services\Facturacion;

use Exception;
use Selective\XmlDSig\DigestAlgorithmType;
use Selective\XmlDSig\XmlSigner;

class FirmaDigitalService
{
    /**
     * Firma un documento XML según el estándar XMLDSig exigido por el SIN (SHA-256).
     *
     * @param string $xmlContent Contenido XML a firmar.
     * @param string|null $certPath Ruta al archivo .p12 / .pfx (si es nulo, usa el configurado en siat.php).
     * @param string|null $certPass Contraseña del certificado digital.
     * @return string Documento XML con la firma digital incrustada en <ds:Signature>.
     * @throws Exception
     */
    public function firmarXml(string $xmlContent, ?string $certPath = null, ?string $certPass = null): string
    {
        $empresa = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('facturacion.configuracion_empresa')) {
                $empresa = \App\Models\Facturacion\ConfiguracionEmpresa::getActiva();
            }
        } catch (\Throwable $e) {
            $empresa = null;
        }

        $defaultPath = $empresa && !empty($empresa->certificado_p12_path)
            ? (str_starts_with($empresa->certificado_p12_path, '/') ? $empresa->certificado_p12_path : base_path($empresa->certificado_p12_path))
            : config('siat.cert_path');

        $defaultPass = $empresa && !empty($empresa->password_p12)
            ? (string) $empresa->password_p12
            : (string) config('siat.cert_pass', '');

        $path = $certPath ?? $defaultPath;
        $pass = $certPass ?? $defaultPass;

        if (!file_exists($path)) {
            // Si aún no se configuró el archivo de certificado real, lanzamos una excepción informativa
            throw new Exception("El archivo de certificado digital .p12 no existe en: {$path}. Por favor configure SIAT_CERT_PATH en el archivo .env");
        }

        try {
            $signer = new XmlSigner();
            $signer->setAlgorithm(DigestAlgorithmType::SHA256);
            $signer->setReferenceUri(''); // Enveloped signature sobre el nodo raíz
            $signer->loadPfxFile($path, $pass);

            return $signer->signXml($xmlContent);
        } catch (Exception $e) {
            throw new Exception("Error al realizar la firma digital XMLDSig: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Comprueba si el certificado digital configurado es accesible y válido.
     */
    public function verificarCertificado(?string $certPath = null, ?string $certPass = null): array
    {
        $empresa = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('facturacion.configuracion_empresa')) {
                $empresa = \App\Models\Facturacion\ConfiguracionEmpresa::getActiva();
            }
        } catch (\Throwable $e) {
            $empresa = null;
        }

        $defaultPath = $empresa && !empty($empresa->certificado_p12_path)
            ? (str_starts_with($empresa->certificado_p12_path, '/') ? $empresa->certificado_p12_path : base_path($empresa->certificado_p12_path))
            : config('siat.cert_path');

        $defaultPass = $empresa && !empty($empresa->password_p12)
            ? (string) $empresa->password_p12
            : (string) config('siat.cert_pass', '');

        $path = $certPath ?? $defaultPath;
        $pass = $certPass ?? $defaultPass;

        if (!file_exists($path)) {
            return [
                'valido' => false,
                'mensaje' => "El archivo de certificado no se encuentra en {$path}",
            ];
        }

        $pfxContent = file_get_contents($path);
        $certs = [];
        if (!openssl_pkcs12_read($pfxContent, $certs, $pass)) {
            return [
                'valido' => false,
                'mensaje' => "Contraseña incorrecta o formato de certificado PKCS#12 inválido.",
            ];
        }

        $certInfo = openssl_x509_parse($certs['cert']);
        $validTo = isset($certInfo['validTo_time_t']) ? date('Y-m-d H:i:s', $certInfo['validTo_time_t']) : 'Desconocido';

        return [
            'valido' => true,
            'sujeto' => $certInfo['subject']['CN'] ?? 'Certificado Digital',
            'emisor' => $certInfo['issuer']['CN'] ?? 'Autoridad Certificadora',
            'vigente_hasta' => $validTo,
            'expirado' => isset($certInfo['validTo_time_t']) && $certInfo['validTo_time_t'] < time(),
        ];
    }
}
