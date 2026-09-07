<?php

declare(strict_types=1);

namespace App\Mail\Facturacion;

use App\Models\Facturacion\Factura;
use App\Services\Facturacion\RepresentacionGraficaService;
use App\Services\Facturacion\XmlFacturaService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FacturaEmitidaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Factura $factura;

    public function __construct(Factura $factura)
    {
        $this->factura = $factura;
    }

    public function build(RepresentacionGraficaService $graficaService, XmlFacturaService $xmlService): self
    {
        $this->factura->loadMissing(['detalles', 'cliente', 'sucursal', 'puntoVenta']);

        $nombreCliente = $this->factura->cliente->nombre_razon_social ?? 'Estimado(a) Abonado(a)';
        $nroFactura = $this->factura->numero_factura;

        $subject = "Factura Digital N° {$nroFactura} - EMAPAP Patacamaya";

        // 1. Generar o recuperar contenido PDF
        $pdfContent = $graficaService->generarPdfFactura($this->factura);

        // 2. Generar o recuperar contenido XML firmado
        $xmlContent = $xmlService->construirXml($this->factura);

        $mail = $this->subject($subject)
            ->view('emails.facturacion.factura-emitida')
            ->with([
                'factura' => $this->factura,
                'nombreCliente' => $nombreCliente,
            ]);

        // Adjuntar PDF oficial
        if (!empty($pdfContent)) {
            $mail->attachData($pdfContent, "Factura_{$nroFactura}_EMAPAP.pdf", [
                'mime' => 'application/pdf',
            ]);
        }

        // Adjuntar XML normativo
        if (!empty($xmlContent)) {
            $mail->attachData($xmlContent, "Factura_{$nroFactura}_EMAPAP.xml", [
                'mime' => 'application/xml',
            ]);
        }

        return $mail;
    }
}
