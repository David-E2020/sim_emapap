<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Electrónica en Línea - EMAPAP Patacamaya</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f9;
            color: #333333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e0e0e0;
        }
        .header {
            background-color: #0d47a1;
            color: #ffffff;
            padding: 25px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 25px;
        }
        .greeting {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #0d47a1;
        }
        .details-box {
            background-color: #f8fafc;
            border: 1px solid #cfd8dc;
            border-radius: 6px;
            padding: 15px 20px;
            margin: 20px 0;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #e0e0e0;
            font-size: 14px;
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #546e7a;
        }
        .value {
            color: #263238;
            font-weight: 600;
        }
        .total-row {
            font-size: 16px;
            color: #0d47a1;
            font-weight: bold;
            padding-top: 10px;
        }
        .cuf-box {
            background-color: #e3f2fd;
            border: 1px solid #90caf9;
            border-radius: 4px;
            padding: 10px;
            margin-top: 15px;
            word-break: break-all;
            font-family: monospace;
            font-size: 11px;
            color: #0d47a1;
        }
        .footer {
            background-color: #f1f3f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #78909c;
            border-top: 1px solid #e0e0e0;
        }
        .btn {
            display: inline-block;
            background-color: #1565c0;
            color: #ffffff !important;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>EMAPAP - PATACAMAYA</h1>
            <p>Empresa Municipal de Agua Potable y Alcantarillado Sanitario</p>
        </div>
        <div class="content">
            <div class="greeting">Estimado(a) {{ $nombreCliente }}:</div>
            <p>Le informamos que se ha emitido satisfactoriamente su <strong>Factura Electrónica en Línea</strong> correspondiente a los servicios municipales de agua potable y alcantarillado sanitario.</p>
            
            <div class="details-box">
                <div class="details-row">
                    <span class="label">N° de Factura:</span>
                    <span class="value">{{ $factura->numero_factura }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Fecha de Emisión:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y H:i') }}</span>
                </div>
                <div class="details-row">
                    <span class="label">NIT / CI Abonado:</span>
                    <span class="value">{{ $factura->numero_documento }} {{ $factura->complemento }}</span>
                </div>
                <div class="details-row">
                    <span class="label">Método de Pago:</span>
                    <span class="value">{{ $factura->codigo_metodo_pago == 1 ? 'Efectivo' : 'Transferencia / QR' }}</span>
                </div>
                <div class="details-row total-row">
                    <span class="label">Total Pagado:</span>
                    <span class="value">Bs {{ number_format((float)$factura->monto_total, 2) }}</span>
                </div>
            </div>

            <p style="font-size: 13px; color: #546e7a;">
                <strong>Código Único de Facturación (CUF):</strong>
            </p>
            <div class="cuf-box">
                {{ $factura->cuf }}
            </div>

            <p style="margin-top: 20px; font-size: 13px;">
                Adjuntos a este mensaje encontrará la <strong>Representación Gráfica Oficial en formato PDF</strong> y el <strong>documento XML firmado digitalmente</strong> con plena validez tributaria ante el Servicio de Impuestos Nacionales.
            </p>

            <center>
                <a href="https://siat.impuestos.gob.bo/consulta/QR?nit={{ config('siat.nit_emisor') }}&cuf={{ $factura->cuf }}&numero={{ $factura->numero_factura }}&t=1" class="btn" target="_blank">
                    Verificar en Portal SIAT del SIN
                </a>
            </center>
        </div>
        <div class="footer">
            <p><strong>EMAPAP - Patacamaya</strong></p>
            <p>Av. Panamericana s/n, Plaza 15 de Agosto | Patacamaya - La Paz - Bolivia</p>
            <p>Este es un correo automático, por favor no responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>
