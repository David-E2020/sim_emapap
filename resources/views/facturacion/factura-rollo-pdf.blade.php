<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Ticket SIAT - EMAPA</title>
    <style>
        @page {
            margin: 3mm 4mm;
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 8.5px;
            color: #000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            width: 72mm;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        .header-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-sub {
            font-size: 8px;
            margin-top: 1px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .cuf-code {
            font-size: 7.5px;
            word-break: break-all;
            font-family: "Courier New", Courier, monospace;
            text-align: center;
            margin: 2px 0;
            line-height: 1.15;
        }
        .info-row {
            margin-bottom: 2px;
            font-size: 8px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        .table-data td {
            padding: 1.5px 0;
            vertical-align: top;
        }
        .qr-box {
            text-align: center;
            margin: 8px 0;
        }
        .qr-box img {
            width: 110px;
            height: 110px;
        }
        .leyenda {
            font-size: 7.5px;
            text-align: center;
            line-height: 1.2;
            margin-top: 3px;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO OFICIAL ROLLO SECTOR 13 -->
    <div class="text-center">
        <div class="font-bold" style="font-size: 11px;">FACTURA</div>
        <div style="font-size: 8px; text-transform: uppercase;">CON DERECHO A CRÉDITO FISCAL</div>
        <div class="header-title" style="margin-top: 3px;">{{ !empty($empresa->nombre_comercial) ? $empresa->nombre_comercial : 'EMAPA' }}</div>
        <div class="header-sub font-bold">Casa Matriz</div>
        <div class="header-sub">No. Punto de Venta: {{ $factura->puntoVenta->codigo_punto_venta ?? 0 }}</div>
        <div class="header-sub">{{ !empty($empresa->direccion) ? $empresa->direccion : 'PLAZA BOLIVAR NRO S/N ZONA ESTACION' }}</div>
        <div class="header-sub">Tel: {{ !empty($empresa->telefono) ? $empresa->telefono : '76750201' }}</div>
        <div class="header-sub font-bold">{{ strtoupper(!empty($empresa->municipio) ? $empresa->municipio : 'PATACAMAYA') }}</div>
    </div>

    <div class="divider"></div>

    <div class="text-center">
        <div class="font-bold">NIT</div>
        <div>{{ !empty($empresa->nit) ? $empresa->nit : '1002393029' }}</div>
        <div class="font-bold" style="margin-top: 2px;">FACTURA N°</div>
        <div>{{ $factura->numero_factura }}</div>
        <div class="font-bold" style="margin-top: 2px;">CÓD. AUTORIZACIÓN</div>
        <div class="cuf-code">{{ $factura->cuf }}</div>
    </div>

    <div class="divider"></div>

    <!-- DATOS DEL CLIENTE / ABONADO -->
    <div>
        <div class="info-row"><span class="font-bold">NOMBRE/RAZÓN SOCIAL:</span> {{ $factura->nombre_razon_social }}</div>
        <div class="info-row"><span class="font-bold">NIT/CI/CEX:</span> {{ $factura->numero_documento }} {{ $factura->complemento ? '- ' . $factura->complemento : '' }}</div>
        <div class="info-row"><span class="font-bold">NRO. CLIENTE:</span> {{ $codCliente }}</div>
        <div class="info-row"><span class="font-bold">PERIODO FACTURADO:</span> {{ $periodoFacturado }}</div>
        <div class="info-row"><span class="font-bold">NRO. MEDIDOR:</span> {{ $nroMedidor }}</div>
        <div class="info-row"><span class="font-bold">CONSUMO PERIODO:</span> {{ number_format((float)$consumoPeriodo, 1) }}</div>
        <div class="info-row"><span class="font-bold">BENEFICIARIO LEY 1886:</span> {{ $beneficiarioLeyTexto }}</div>
        <div class="info-row"><span class="font-bold">DIRECCIÓN:</span> {{ $direccion }}</div>
        <div class="info-row"><span class="font-bold">FECHA DE EMISIÓN:</span> {{ $factura->fecha_emision ? \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y h:i A') : date('d/m/Y h:i A') }}</div>
    </div>

    <div class="divider"></div>

    <!-- DETALLE DEL SERVICIO Y TASAS -->
    <div>
        <div class="font-bold" style="margin-bottom: 3px;">DETALLE</div>
        @foreach($detalles as $det)
        <div style="font-size: 8px;">
            <span class="font-bold">{{ $det->codigo_producto_empresa ?? '52DW30267' }} - {{ $det->descripcion }}</span><br>
            UNIDAD DE MEDIDA: {{ strtoupper($det->unidad_medida ?? 'UNIDAD (SERVICIOS)') }}
        </div>
        <table class="table-data" style="margin-top: 1px; margin-bottom: 2px;">
            <tr>
                <td style="width: 70%;">{{ number_format((float)$det->cantidad, 2) }} X {{ number_format((float)$det->precio_unitario, 2) }} - {{ number_format((float)($det->monto_descuento ?? 0), 2) }}</td>
                <td style="width: 30%;" class="text-right font-bold">{{ number_format((float)$det->subtotal, 2) }}</td>
            </tr>
        </table>
        @endforeach

        <table class="table-data" style="margin-top: 2px;">
            <tr>
                <td style="width: 75%;">Ajustes sujetos a IVA</td>
                <td style="width: 25%;" class="text-right">{{ number_format((float)($factura->ajuste_sujeto_iva ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Tasa aseo Urbano</td>
                <td class="text-right">{{ number_format((float)($factura->tasa_aseo ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Tasa Alumbrado</td>
                <td class="text-right">{{ number_format((float)($factura->tasa_alumbrado ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Otras Tasas</td>
                <td class="text-right">{{ number_format((float)($factura->otras_tasas ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td>Otros pagos (pago de cuotas)</td>
                <td class="text-right">{{ number_format((float)($factura->otros_pagos_no_sujeto_iva ?? 0), 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <!-- TOTALES Y LIQUIDACIÓN -->
    <table class="table-data font-bold">
        <tr>
            <td style="width: 70%;" class="text-right">TOTAL Bs:</td>
            <td style="width: 30%;" class="text-right">{{ number_format((float)$factura->monto_total, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">(-) DESCUENTO Bs:</td>
            <td class="text-right">{{ number_format((float)($factura->monto_descuento ?? 0), 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">SUBTOTAL A PAGAR Bs:</td>
            <td class="text-right">{{ number_format((float)$factura->monto_total, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">(-) AJUSTES NO SUJETOS A IVA Bs:</td>
            <td class="text-right">{{ number_format((float)($factura->ajuste_no_sujeto_iva ?? 0), 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">MONTO TOTAL A PAGAR Bs:</td>
            <td class="text-right">{{ number_format((float)$factura->monto_total, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">(-) TASAS Bs:</td>
            <td class="text-right">{{ number_format((float)(($factura->tasa_aseo ?? 0) + ($factura->tasa_alumbrado ?? 0) + ($factura->otras_tasas ?? 0)), 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">(-) OTROS PAGOS NO SUJETO A IVA Bs:</td>
            <td class="text-right">{{ number_format((float)($factura->otros_pagos_no_sujeto_iva ?? 0), 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">(+) AJUSTES NO SUJETOS A IVA Bs:</td>
            <td class="text-right">0.00</td>
        </tr>
        <tr>
            <td class="text-right">IMPORTE BASE CRÉDITO FISCAL:</td>
            <td class="text-right">{{ number_format((float)$factura->monto_total_sujeto_iva, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div style="font-size: 8px;">
        <span class="font-bold">Son:</span> {{ $literal }}
    </div>

    <div class="divider"></div>

    <div class="leyenda font-bold">
        ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAIS, EL USO ILICITO SERA SANCIONADO PENALMENTE DE ACUERDO A LEY
    </div>

    <div class="leyenda">
        Ley N° 453: La interrupción del servicio debe comunicarse con anterioridad a las Autoridades que correspondan y a los usuarios afectados.
    </div>

    <div class="leyenda" style="margin-top: 3px; font-style: italic; color: #333;">
        "Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en línea"
    </div>

    <div class="qr-box">
        @if(!empty($qrBase64))
            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="QR SIAT Oficial">
        @endif
    </div>

</body>
</html>
