<?php

declare(strict_types=1);

namespace App\Services\Correspondencia;

use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\HojaRuta;
use Carbon\Carbon;

class CaratulaPdfService
{
    /**
     * Genera el HTML oficial imprimible de la Carátula de Hoja de Ruta EMAPA con QR y casilla de derivaciones
     */
    public function renderCaratulaHtml(HojaRuta $hojaRuta): string
    {
        $fecha = Carbon::parse($hojaRuta->fecha_solicitud)->format('d/m/Y H:i');
        $remitente = $hojaRuta->personaOrigen ? $hojaRuta->personaOrigen->nombre_completo : ($hojaRuta->remitente_externo ?: 'Ventanilla Central');
        $cargoRemitente = $hojaRuta->cargoOrigen ? $hojaRuta->cargoOrigen->nombre : 'Particular / Externo';
        $unidadRemitente = $hojaRuta->unidadOrigen ? $hojaRuta->unidadOrigen->nombre : 'Entidad Externa';

        $derivacionesHtml = '';
        foreach ($hojaRuta->derivaciones as $index => $der) {
            $num = $index + 1;
            $fecDer = Carbon::parse($der->fecha_derivacion)->format('d/m/Y H:i');
            $dest = $der->funcionarioDestino ? $der->funcionarioDestino->nombre_completo : 'N/A';
            $cargoDest = $der->cargoDestino ? $der->cargoDestino->nombre : 'N/A';
            $unidDest = $der->unidadDestino ? $der->unidadDestino->nombre : 'N/A';
            $orig = $der->funcionarioOrigen ? $der->funcionarioOrigen->nombre_completo : 'N/A';

            $derivacionesHtml .= "
            <div style='border: 1.5px solid #1e3a8a; border-radius: 6px; margin-bottom: 12px; page-break-inside: avoid;'>
                <div style='background-color: #1e3a8a; color: white; padding: 4px 10px; font-weight: bold; font-size: 11px; display: flex; justify-content: space-between;'>
                    <span>DERIVACIÓN #{$num} - PROVEÍDO: {$der->proveido}</span>
                    <span>Fecha: {$fecDer} | Plazo: {$der->dias_plazo} días</span>
                </div>
                <div style='padding: 8px 10px; font-size: 11px; color: #1e293b;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='width: 50%; vertical-align: top;'><strong>De:</strong> {$orig}</td>
                            <td style='width: 50%; vertical-align: top;'><strong>A:</strong> {$dest} ({$cargoDest})</td>
                        </tr>
                        <tr>
                            <td colspan='2' style='padding-top: 4px;'><strong>Destino / Unidad:</strong> {$unidDest}</td>
                        </tr>
                        <tr>
                            <td colspan='2' style='padding-top: 4px;'><strong>Instrucción:</strong> {$der->instruccion_detalle}</td>
                        </tr>
                    </table>
                    <div style='margin-top: 15px; border-top: 1px dashed #cbd5e1; padding-top: 5px; display: flex; justify-content: space-between; font-size: 9px; color: #64748b;'>
                        <span>Firma y Sello de Recepción: _______________________</span>
                        <span>Fecha y Hora de Recepción: ____/____/2026 __:__</span>
                    </div>
                </div>
            </div>";
        }

        if (empty($derivacionesHtml)) {
            $derivacionesHtml = "<div style='text-align: center; color: #64748b; padding: 20px; border: 1px dashed #cbd5e1; border-radius: 6px;'>Sin derivaciones registradas aún.</div>";
        }

        $qrUrl = url('/verificar-hoja-ruta?cite='.urlencode($hojaRuta->nro_hoja_ruta));
        $qrImageSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=110x110&data='.urlencode($qrUrl);

        return "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Carátula Hoja de Ruta - {$hojaRuta->nro_hoja_ruta}</title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 20px; background-color: #f8fafc; color: #0f172a; }
        .document-container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border: 2px solid #0f172a; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header-table { width: 100%; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 15px; }
        .box-title { background: #0f172a; color: white; padding: 6px 12px; font-size: 13px; font-weight: bold; border-radius: 4px 4px 0 0; }
        .box-content { border: 1.5px solid #0f172a; border-top: none; padding: 12px; border-radius: 0 0 4px 4px; margin-bottom: 15px; }
        .cite-badge { font-size: 16px; font-weight: 900; color: #1e3a8a; background: #dbeafe; padding: 6px 14px; border: 2px solid #1e3a8a; border-radius: 6px; display: inline-block; }
        @media print { body { background: white; padding: 0; } .document-container { box-shadow: none; border: none; padding: 10px; } }
    </style>
</head>
<body>
    <div class='document-container'>
        <table class='header-table'>
            <tr>
                <td style='width: 15%; text-align: center;'>
                    <img src='/images/logoEmapa2.png' alt='EMAPA' style='max-height: 60px; max-width: 100px;'>
                </td>
                <td style='width: 65%; text-align: center;'>
                    <h3 style='margin: 0; font-size: 15px; letter-spacing: 0.5px;'>EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS</h3>
                    <h2 style='margin: 4px 0 0 0; font-size: 18px; color: #1e3a8a;'>CARÁTULA OFICIAL DE HOJA DE RUTA</h2>
                    <span style='font-size: 10px; color: #64748b;'>ESTADO PLURINACIONAL DE BOLIVIA</span>
                </td>
                <td style='width: 20%; text-align: center;'>
                    <img src='{$qrImageSrc}' alt='Código QR' style='width: 90px; height: 90px; border: 1px solid #cbd5e1; padding: 2px;'>
                </td>
            </tr>
        </table>

        <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;'>
            <div>
                <span style='font-size: 11px; font-weight: bold; color: #64748b;'>CÓDIGO ÚNICO DE TRÁMITE:</span><br>
                <div class='cite-badge'>{$hojaRuta->nro_hoja_ruta}</div>
            </div>
            <div style='text-align: right; font-size: 11px;'>
                <strong>Fecha y Hora de Ingreso:</strong> {$fecha}<br>
                <strong>Prioridad:</strong> <span style='color: #dc2626; font-weight: bold;'>{$hojaRuta->prioridad}</span> | <strong>Fojas:</strong> {$hojaRuta->nro_fojas} | <strong>Anexos:</strong> {$hojaRuta->nro_anexos}
            </div>
        </div>

        <div class='box-title'>1. DATOS DEL REMITENTE Y ASUNTO</div>
        <div class='box-content' style='font-size: 12px;'>
            <table style='width: 100%;'>
                <tr>
                    <td style='width: 25%; font-weight: bold;'>Remitente:</td>
                    <td style='width: 75%;'>{$remitente}</td>
                </tr>
                <tr>
                    <td style='font-weight: bold;'>Cargo / Institución:</td>
                    <td>{$cargoRemitente} ({$unidadRemitente})</td>
                </tr>
                <tr>
                    <td style='font-weight: bold; vertical-align: top;'>Asunto / Referencia:</td>
                    <td style='font-weight: bold; color: #0f172a;'>{$hojaRuta->asunto}</td>
                </tr>
            </table>
        </div>

        <div class='box-title' style='background: #1e3a8a;'>2. HISTORIAL DE DERIVACIONES Y PROVEÍDOS</div>
        <div class='box-content' style='border-color: #1e3a8a;'>
            {$derivacionesHtml}
        </div>

        <div style='text-align: center; font-size: 9px; color: #64748b; margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 6px;'>
            Documento Oficial generado por el Sistema de Correspondencia Institucional SIM-EMAPA / Londra - Prohibida su alteración o reproducción no autorizada.
        </div>
    </div>
</body>
</html>";
    }

    /**
     * Renderiza el documento oficial con membrete y firmas con QR
     */
    public function renderDocumentoHtml(Documento $documento): string
    {
        $qrUrl = url("/verificar-documento/{$documento->codigo_verificacion}");
        $qrImageSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data='.urlencode($qrUrl);

        $firmasHtml = '';
        foreach ($documento->firmasAprobaciones as $f) {
            $nom = $f->persona ? $f->persona->nombre_completo : 'N/A';
            $ci = $f->persona ? $f->persona->nro_documento : 'N/A';
            $fechaFirma = $f->fecha_firma_aprobacion ? Carbon::parse($f->fecha_firma_aprobacion)->format('d/m/Y H:i:s') : 'Pendiente';
            $hash = $f->hash_documento_sha256 ? substr($f->hash_documento_sha256, 0, 16).'...' : 'N/A';

            $firmasHtml .= "
            <div style='border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; width: 45%; margin-bottom: 8px; font-size: 10px; background: #f8fafc;'>
                <strong style='color: #1e3a8a;'>FIRMADO ELECTRÓNICAMENTE</strong><br>
                <strong>Funcionario:</strong> {$nom}<br>
                <strong>CI:</strong> {$ci}<br>
                <strong>Fecha/Hora:</strong> {$fechaFirma}<br>
                <span style='font-family: monospace; font-size: 8px; color: #64748b;'>Hash: {$hash}</span>
            </div>";
        }

        return "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>{$documento->cite} - {$documento->tipo_documento}</title>
    <style>
        body { font-family: 'Times New Roman', serif; margin: 0; padding: 30px; color: #000; font-size: 14px; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .cite-header { font-weight: bold; font-size: 16px; text-align: right; margin-bottom: 20px; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .meta-table td { padding: 4px 0; vertical-align: top; }
        .content { margin-bottom: 40px; min-height: 250px; text-align: justify; }
        .firmas-container { display: flex; flex-wrap: wrap; justify-content: space-between; border-top: 1px solid #cbd5e1; padding-top: 15px; }
    </style>
</head>
<body>
    <div class='header'>
        <h3 style='margin: 0;'>EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS - EMAPA</h3>
        <h4 style='margin: 4px 0 0 0; color: #475569;'>ESTADO PLURINACIONAL DE BOLIVIA</h4>
    </div>

    <div class='cite-header'>CITE: {$documento->cite}</div>

    <table class='meta-table'>
        <tr>
            <td style='width: 15%; font-weight: bold;'>A:</td>
            <td><strong>DESTINATARIOS INSTITUCIONALES</strong></td>
        </tr>
        <tr>
            <td style='font-weight: bold;'>DE:</td>
            <td><strong>".($documento->creador ? $documento->creador->nombre_completo : 'Autoridad EMAPA')."</strong></td>
        </tr>
        <tr>
            <td style='font-weight: bold;'>REF:</td>
            <td><strong>{$documento->asunto}</strong></td>
        </tr>
        <tr>
            <td style='font-weight: bold;'>FECHA:</td>
            <td>".date('d \d\e F \d\e Y')."</td>
        </tr>
    </table>

    <div class='content'>
        {$documento->contenido_html}
    </div>

    <div style='display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px;'>
        <div style='width: 70%;'>
            <h5 style='margin: 0 0 10px 0; text-transform: uppercase;'>Rúbricas y Sellado Electrónico de Conformidad:</h5>
            <div class='firmas-container'>
                {$firmasHtml}
            </div>
        </div>
        <div style='width: 25%; text-align: center;'>
            <img src='{$qrImageSrc}' style='width: 90px; height: 90px; border: 1px solid #000; padding: 2px;'><br>
            <span style='font-size: 8px; font-family: monospace;'>Token: {$documento->codigo_verificacion}</span>
        </div>
    </div>
</body>
</html>";
    }
}
