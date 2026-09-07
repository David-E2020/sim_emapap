<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Ticket - EMAPAP Patacamaya</title>
    <style>
        @page {
            margin: 3mm 4mm;
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9px;
            color: #000;
            line-height: 1.2;
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
            margin: 4px 0;
        }
        .divider-solid {
            border-top: 1px solid #000;
            margin: 4px 0;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }
        .table-data th {
            border-bottom: 1px dashed #000;
            padding: 2px 0;
            text-align: left;
        }
        .table-data td {
            padding: 2px 0;
            vertical-align: top;
        }
        .cuf-code {
            font-size: 7.5px;
            word-break: break-all;
            font-family: monospace;
            text-align: center;
            margin: 3px 0;
        }
        .qr-box {
            text-align: center;
            margin: 6px 0;
        }
        .qr-box img {
            width: 100px;
            height: 100px;
        }
        .leyenda {
            font-size: 7px;
            text-align: center;
            line-height: 1.15;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="header-title">EMAPAP - PATACAMAYA</div>
        <div class="header-sub font-bold">EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO SANITARIO</div>
        <div class="header-sub">Oficina: {{ $factura->sucursal->nombre ?? 'Oficina Central Patacamaya' }}</div>
        <div class="header-sub">Punto de Cobro: {{ $factura->puntoVenta->nombre ?? 'Caja Central Recaudaciones' }}</div>
        <div class="header-sub">Dir: Av. Panamericana s/n, Plaza 15 de Agosto</div>
        <div class="header-sub">Tel: 2-8147000 | Patacamaya - La Paz</div>
    </div>

    <div class="divider"></div>

    <div class="text-center">
        <div class="font-bold" style="font-size: 10px;">FACTURA</div>
        <div style="font-size: 8px;">(Con Derecho a Crédito Fiscal)</div>
        <div class="header-sub font-bold mt-1">NIT: {{ config('siat.nit_emisor', '123456789') }}</div>
        <div class="header-sub font-bold">FACTURA N°: {{ $factura->numero_factura }}</div>
        <div class="header-sub">CÓD. AUTORIZACIÓN:</div>
        <div class="cuf-code">{{ $factura->cuf }}</div>
    </div>

    <div class="divider"></div>

    <div>
        <div><span class="font-bold">FECHA:</span> {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y H:i:s') }}</div>
        <div><span class="font-bold">SEÑOR(ES):</span> {{ $factura->nombre_razon_social }}</div>
        <div><span class="font-bold">NIT/CI:</span> {{ $factura->numero_documento }} {{ $factura->complemento }}</div>
        <div><span class="font-bold">PAGO:</span> {{ $factura->codigo_metodo_pago == 1 ? 'EFECTIVO' : 'TRANSFERENCIA / QR' }}</div>
    </div>

    <div class="divider"></div>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 15%;">CANT</th>
                <th style="width: 55%;">DETALLE</th>
                <th style="width: 30%;" class="text-right">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->detalles as $det)
            <tr>
                <td>{{ number_format((float)$det->cantidad, 0) }}</td>
                <td>{{ $det->descripcion }}</td>
                <td class="text-right">{{ number_format((float)$det->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider-solid"></div>

    <table style="width: 100%; font-size: 9px;">
        @if((float)$factura->monto_descuento > 0)
        <tr>
            <td class="text-right font-bold">SUBTOTAL BS:</td>
            <td class="text-right" style="width: 30%;">{{ number_format((float)$factura->monto_total + (float)$factura->monto_descuento, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right font-bold">DESCUENTO BS:</td>
            <td class="text-right">-{{ number_format((float)$factura->monto_descuento, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td class="text-right font-bold" style="font-size: 10px;">TOTAL A PAGAR BS:</td>
            <td class="text-right font-bold" style="font-size: 10px;">{{ number_format((float)$factura->monto_total, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right font-bold">IMPORTE BASE CRÉDITO FISCAL:</td>
            <td class="text-right font-bold">{{ number_format((float)$factura->monto_total_sujeto_iva, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div style="font-size: 8px;">
        <span class="font-bold">SON:</span> {{ $literal }}
    </div>

    <div class="qr-box">
        @if(!empty($qrBase64))
            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="QR Fiscal SIAT">
        @endif
    </div>

    <div class="leyenda font-bold">
        "ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY"
    </div>

    <div class="leyenda">
        {{ $factura->leyenda }}
    </div>

    <div class="leyenda" style="margin-top: 4px;">
        {{ $factura->tipo_emision == 1 ? 'EMISIÓN EN LÍNEA' : 'EMISIÓN FUERA DE LÍNEA' }}
    </div>

    <div class="divider"></div>
    <div class="text-center" style="font-size: 7px; color: #333;">
        ¡Cuide el agua, es vida para Patacamaya!
    </div>
</body>
</html>
