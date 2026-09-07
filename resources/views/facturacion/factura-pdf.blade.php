<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura N° {{ $factura->numero_factura }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .emapa-box {
            width: 55%;
            vertical-align: top;
        }
        .emapa-title {
            font-size: 13px;
            font-weight: bold;
            color: #1a4d2e; /* Verde institucional */
            margin-bottom: 4px;
        }
        .emapa-subtitle {
            font-size: 9.5px;
            color: #444;
            line-height: 1.3;
        }
        .fiscal-box {
            width: 45%;
            vertical-align: top;
            text-align: right;
        }
        .fiscal-card {
            border: 1.5px solid #1a4d2e;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: left;
            display: inline-block;
            background-color: #fcfcfc;
            width: 90%;
        }
        .fiscal-row {
            margin-bottom: 4px;
            font-size: 10px;
        }
        .fiscal-row strong {
            display: inline-block;
            width: 100px;
        }
        .title-factura {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 10px 0;
            text-transform: uppercase;
        }
        .subtitle-modalidad {
            text-align: center;
            font-size: 10px;
            color: #555;
            margin-bottom: 12px;
        }
        .client-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        .client-table td {
            padding: 6px 10px;
            font-size: 10.5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #1a4d2e;
            color: #ffffff;
            font-weight: bold;
            padding: 7px 5px;
            font-size: 10px;
            text-align: center;
            border: 1px solid #1a4d2e;
        }
        .items-table td {
            border: 1px solid #ddd;
            padding: 6px 5px;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .totals-table td {
            padding: 4px 6px;
            font-size: 10.5px;
        }
        .total-highlight {
            font-size: 12px;
            font-weight: bold;
            background-color: #f2f7f4;
            color: #1a4d2e;
        }
        .footer-section {
            margin-top: 15px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .qr-cell {
            width: 130px;
            vertical-align: top;
            text-align: center;
        }
        .legends-cell {
            vertical-align: top;
            padding-left: 15px;
            font-size: 9px;
            color: #333;
            text-align: center;
        }
        .legend-bold {
            font-weight: bold;
            margin-bottom: 6px;
        }
        .cuf-code {
            word-break: break-all;
            font-family: "Courier New", Courier, monospace;
            font-size: 8.5px;
            color: #333;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <table class="header-table">
        <tr>
            <td class="emapa-box">
                <div class="emapa-title">EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO SANITARIO</div>
                <div class="emapa-subtitle">
                    <strong>EMAPA - PATACAMAYA</strong><br>
                    Oficina: {{ $factura->sucursal->nombre ?? 'Oficina Central' }}<br>
                    Punto de Cobro: {{ $factura->puntoVenta->nombre ?? 'Caja Central Recaudaciones' }}<br>
                    Dirección: {{ $factura->sucursal->direccion ?? 'Av. Panamericana s/n, Plaza 15 de Agosto' }}<br>
                    Teléfono: {{ $factura->sucursal->telefono ?? '2-8147000' }}<br>
                    {{ $factura->sucursal->municipio ?? 'Patacamaya' }} - La Paz - Bolivia
                </div>
            </td>
            <td class="fiscal-box">
                <div class="fiscal-card">
                    <div class="fiscal-row"><strong>NIT:</strong> {{ config('siat.nit_emisor', '123456789') }}</div>
                    <div class="fiscal-row"><strong>FACTURA N°:</strong> {{ $factura->numero_factura }}</div>
                    <div class="fiscal-row"><strong>CÓD. AUTORIZACIÓN:</strong></div>
                    <div class="cuf-code">{{ $factura->cuf }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="title-factura">FACTURA</div>
    <div class="subtitle-modalidad">(Con Derecho a Crédito Fiscal)</div>

    <!-- DATOS DEL CLIENTE -->
    <table class="client-table">
        <tr>
            <td style="width: 60%;"><strong>Fecha:</strong> {{ $factura->fecha_emision ? $factura->fecha_emision->format('d/m/Y h:i A') : date('d/m/Y h:i A') }}</td>
            <td style="width: 40%;"><strong>NIT / CI / CEX:</strong> {{ $factura->numero_documento }} {{ $factura->complemento ? '- ' . $factura->complemento : '' }}</td>
        </tr>
        <tr>
            <td><strong>Señor(es):</strong> {{ $factura->nombre_razon_social }}</td>
            <td><strong>Cod. Cliente:</strong> {{ $factura->numero_documento }}</td>
        </tr>
    </table>

    <!-- DETALLE DE ÍTEMS -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 12%;">CÓDIGO</th>
                <th style="width: 10%;">CANTIDAD</th>
                <th style="width: 10%;">UNIDAD</th>
                <th style="width: 38%;">DESCRIPCIÓN</th>
                <th style="width: 10%;">P. UNIT</th>
                <th style="width: 10%;">DESC.</th>
                <th style="width: 10%;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->detalles as $det)
            <tr>
                <td class="text-center">{{ $det->codigo_producto_empresa }}</td>
                <td class="text-right">{{ number_format((float)$det->cantidad, 2) }}</td>
                <td class="text-center">UNIDAD</td>
                <td class="text-left">{{ $det->descripcion }}</td>
                <td class="text-right">{{ number_format((float)$det->precio_unitario, 2) }}</td>
                <td class="text-right">{{ number_format((float)$det->monto_descuento, 2) }}</td>
                <td class="text-right">{{ number_format((float)$det->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TOTALES -->
    <table class="totals-table">
        <tr>
            <td style="width: 65%; vertical-align: top;">
                <strong>Son:</strong> {{ $literal ?? 'BOLIVIANOS' }}
            </td>
            <td style="width: 35%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="text-right">SUBTOTAL BS:</td>
                        <td class="text-right" style="width: 90px;">{{ number_format((float)$factura->monto_total + (float)$factura->monto_descuento, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">DESCUENTO BS:</td>
                        <td class="text-right">{{ number_format((float)$factura->monto_descuento, 2) }}</td>
                    </tr>
                    <tr class="total-highlight">
                        <td class="text-right" style="font-weight: bold;">TOTAL BS:</td>
                        <td class="text-right" style="font-weight: bold;">{{ number_format((float)$factura->monto_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right" style="font-size: 9.5px;">MONTO GIFT CARD BS:</td>
                        <td class="text-right" style="font-size: 9.5px;">{{ number_format((float)$factura->monto_gift_card, 2) }}</td>
                    </tr>
                    <tr style="border-top: 1px solid #1a4d2e; font-weight: bold;">
                        <td class="text-right">IMPORTE BASE CRÉDITO FISCAL:</td>
                        <td class="text-right">{{ number_format((float)$factura->monto_total_sujeto_iva, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- PIE DE PÁGINA Y QR -->
    <div class="footer-section">
        <table class="footer-table">
            <tr>
                <td class="qr-cell">
                    @if(!empty($qrBase64))
                        <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" width="115" height="115" alt="Código QR SIAT">
                    @endif
                </td>
                <td class="legends-cell">
                    <div class="legend-bold">
                        "ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY"
                    </div>
                    <div style="margin-bottom: 8px;">
                        {{ $factura->leyenda ?? 'Ley N° 453: El proveedor deberá suministrar el servicio en las condiciones ofertadas o convenidas.' }}
                    </div>
                    <div style="font-size: 8px; color: #666; font-style: italic;">
                        Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en línea.
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
