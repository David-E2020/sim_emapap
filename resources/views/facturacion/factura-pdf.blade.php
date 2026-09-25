<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura SIAT N° {{ $factura->numero_factura }}</title>
    <style>
        @page {
            margin: 12mm 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1.25;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .company-box {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding-right: 15px;
        }
        .company-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .company-sub {
            font-size: 9px;
            line-height: 1.25;
        }
        .company-sub-bold {
            font-weight: bold;
            font-size: 9.5px;
        }
        .fiscal-box {
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        .fiscal-card {
            border: 1px solid #333;
            border-radius: 4px;
            padding: 6px 12px;
            display: inline-block;
            text-align: left;
            width: 88%;
            background-color: #fff;
        }
        .fiscal-card table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        .fiscal-card td {
            padding: 1.5px 0;
        }
        .cuf-code {
            word-break: break-all;
            font-family: "Courier New", Courier, monospace;
            font-size: 8px;
            line-height: 1.15;
            margin-top: 2px;
        }
        .title-factura {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-top: 6px;
            text-transform: uppercase;
        }
        .subtitle-factura {
            text-align: center;
            font-size: 8.5px;
            color: #222;
            margin-bottom: 10px;
        }
        .client-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .client-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #333;
        }
        .items-table th {
            border: 1px solid #333;
            padding: 4px 3px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            background-color: #f7f7f7;
        }
        .items-table td {
            border: 1px solid #333;
            padding: 4px 4px;
            font-size: 8.5px;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .subrow-tax td {
            padding: 2.5px 4px;
            font-size: 8px;
        }
        .totals-section {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .literal-cell {
            vertical-align: top;
            font-size: 8.5px;
            padding-top: 5px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            border: 1px solid #333;
        }
        .totals-table td {
            padding: 2px 5px;
            border: 1px solid #333;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .footer-legends {
            vertical-align: middle;
            font-size: 7.5px;
            line-height: 1.25;
            padding-right: 15px;
            text-align: center;
        }
        .footer-qr {
            width: 110px;
            vertical-align: middle;
            text-align: right;
        }
        .footer-qr img {
            width: 95px;
            height: 95px;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO OFICIAL SECTOR 13 -->
    <table class="header-table">
        <tr>
            <td class="company-box">
                <div class="company-title">{{ !empty($empresa->nombre_comercial) ? $empresa->nombre_comercial : 'EMAPA' }}</div>
                <div class="company-sub-bold">CASA MATRIZ</div>
                <div class="company-sub">No. Punto de Venta: {{ $factura->puntoVenta->codigo_punto_venta ?? 0 }}</div>
                <div class="company-sub">{{ !empty($empresa->direccion) ? $empresa->direccion : 'PLAZA BOLIVAR NRO S/N ZONA ESTACION' }}</div>
                <div class="company-sub">Teléfono: {{ !empty($empresa->telefono) ? $empresa->telefono : '76750201' }}</div>
                <div class="company-sub-bold">{{ strtoupper(!empty($empresa->municipio) ? $empresa->municipio : 'PATACAMAYA') }}</div>
            </td>
            <td class="fiscal-box">
                <div class="fiscal-card">
                    <table>
                        <tr>
                            <td style="width: 45%;"><strong>NIT:</strong></td>
                            <td class="text-right">{{ !empty($empresa->nit) ? $empresa->nit : '1002393029' }}</td>
                        </tr>
                        <tr>
                            <td><strong>FACTURA N°:</strong></td>
                            <td class="text-right font-bold">{{ $factura->numero_factura }}</td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>CÓD. AUTORIZACIÓN:</strong></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="cuf-code">{{ $factura->cuf }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="title-factura">FACTURA</div>
    <div class="subtitle-factura">(Con Derecho a Crédito Fiscal)</div>

    <!-- DATOS DEL ABONADO / SERVICIO BÁSICO -->
    <table class="client-table">
        <tr>
            <td style="width: 52%;"><strong>Fecha:</strong> {{ $factura->fecha_emision ? \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y h:i A') : date('d/m/Y h:i A') }}</td>
            <td style="width: 48%;"><strong>NIT/CI/CEX:</strong> {{ $factura->numero_documento }} {{ $factura->complemento ? '- ' . $factura->complemento : '' }}</td>
        </tr>
        <tr>
            <td><strong>Nombre/Razón Social:</strong> {{ $factura->nombre_razon_social }}</td>
            <td><strong>Cod. Cliente:</strong> {{ $codCliente }}</td>
        </tr>
        <tr>
            <td><strong>Dirección:</strong> {{ $direccion }}</td>
            <td><strong>Beneficiario Ley 1886:</strong> {{ $beneficiarioLeyTexto }}</td>
        </tr>
        <tr>
            <td><strong>Consumo Periodo:</strong> {{ number_format((float)$consumoPeriodo, 1) }}</td>
            <td>
                <strong>Periodo Facturado:</strong> {{ $periodoFacturado }}<br>
                <strong>Nro Medidor:</strong> {{ $nroMedidor }}
            </td>
        </tr>
    </table>

    <!-- DETALLE DE SERVICIOS Y TASAS (SECTOR 13) -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 14%;">CÓDIGO SERVICIO</th>
                <th style="width: 9%;">CANTIDAD</th>
                <th style="width: 16%;">UNIDAD DE MEDIDA</th>
                <th style="width: 33%;">DESCRIPCIÓN</th>
                <th style="width: 10%;">PRECIO UNITARIO</th>
                <th style="width: 9%;">DESCUENTO</th>
                <th style="width: 9%;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $det)
            <tr>
                <td class="text-center">{{ $det->codigo_producto_empresa ?? '52DW30267' }}</td>
                <td class="text-center">{{ number_format((float)$det->cantidad, 2) }}</td>
                <td class="text-center">{{ $det->unidad_medida ?? 'Unidad (Servicios)' }}</td>
                <td class="text-left">{{ $det->descripcion }}</td>
                <td class="text-right">{{ number_format((float)$det->precio_unitario, 2) }}</td>
                <td class="text-right">{{ number_format((float)($det->monto_descuento ?? 0), 2) }}</td>
                <td class="text-right">{{ number_format((float)$det->subtotal, 2) }}</td>
            </tr>
            @endforeach

            <!-- FILAS FISCALES REGLAMENTARIAS DE SERVICIOS BÁSICOS -->
            <tr class="subrow-tax">
                <td colspan="6" class="text-left" style="padding-left: 10px;">Ajustes sujetos a IVA</td>
                <td class="text-right">{{ number_format((float)($factura->ajuste_sujeto_iva ?? 0), 2) }}</td>
            </tr>
            <tr class="subrow-tax">
                <td colspan="6" class="text-left" style="padding-left: 10px;">Tasa Aseo Urbano</td>
                <td class="text-right">{{ number_format((float)($factura->tasa_aseo ?? 0), 2) }}</td>
            </tr>
            <tr class="subrow-tax">
                <td colspan="6" class="text-left" style="padding-left: 10px;">Tasa Alumbrado</td>
                <td class="text-right">{{ number_format((float)($factura->tasa_alumbrado ?? 0), 2) }}</td>
            </tr>
            <tr class="subrow-tax">
                <td colspan="6" class="text-left" style="padding-left: 10px;">Otras Tasas</td>
                <td class="text-right">{{ number_format((float)($factura->otras_tasas ?? 0), 2) }}</td>
            </tr>
            <tr class="subrow-tax">
                <td colspan="6" class="text-left" style="padding-left: 10px;">Otros Pagos (pago de cuotas etc)</td>
                <td class="text-right">{{ number_format((float)($factura->otros_pagos_no_sujeto_iva ?? 0), 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- TOTALES Y LIQUIDACIÓN FISCAL -->
    <table class="totals-section">
        <tr>
            <td class="literal-cell" style="width: 50%;">
                <div><strong>Son:</strong> {{ $literal }}</div>
            </td>
            <td style="width: 50%;">
                <table class="totals-table">
                    <tr>
                        <td class="text-right font-bold" style="width: 65%;">TOTAL Bs</td>
                        <td class="text-right font-bold" style="width: 35%;">{{ number_format((float)$factura->monto_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">(-) DESCUENTO Bs</td>
                        <td class="text-right">{{ number_format((float)($factura->monto_descuento ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right font-bold">SUBTOTAL A PAGAR Bs</td>
                        <td class="text-right font-bold">{{ number_format((float)$factura->monto_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">(-) AJUSTES NO SUJETOS A IVA Bs</td>
                        <td class="text-right">{{ number_format((float)($factura->ajuste_no_sujeto_iva ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right font-bold">MONTO TOTAL A PAGAR Bs</td>
                        <td class="text-right font-bold">{{ number_format((float)$factura->monto_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">(-) TASAS Bs</td>
                        <td class="text-right">{{ number_format((float)(($factura->tasa_aseo ?? 0) + ($factura->tasa_alumbrado ?? 0) + ($factura->otras_tasas ?? 0)), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">(-) OTROS PAGOS NO SUJETO A IVA Bs</td>
                        <td class="text-right">{{ number_format((float)($factura->otros_pagos_no_sujeto_iva ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">(+) AJUSTES NO SUJETOS A IVA Bs</td>
                        <td class="text-right">0.00</td>
                    </tr>
                    <tr>
                        <td class="text-right font-bold">IMPORTE BASE CRÉDITO FISCAL</td>
                        <td class="text-right font-bold">{{ number_format((float)$factura->monto_total_sujeto_iva, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- PIE DE PÁGINA Y QR -->
    <table class="footer-table">
        <tr>
            <td class="footer-legends">
                <div class="font-bold" style="margin-bottom: 4px;">
                    "ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY"
                </div>
                <div style="margin-bottom: 4px;">
                    Ley N° 453: La interrupción del servicio debe comunicarse con anterioridad a las Autoridades que correspondan y a los usuarios afectados.
                </div>
                <div style="font-size: 7px; color: #444; font-style: italic;">
                    "Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en línea"
                </div>
            </td>
            <td class="footer-qr">
                @if(!empty($qrBase64))
                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="QR SIAT Oficial">
                @endif
            </td>
        </tr>
    </table>

</body>
</html>
