<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Pago - {{ $aporte->factura }}</title>
    <style>
        @page {
            margin: 3mm 4mm;
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9px;
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
            margin: 4px 0;
        }
        .double-divider {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin: 5px 0;
        }
        .info-row {
            margin-bottom: 2px;
            font-size: 8.5px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin: 4px 0;
        }
        .table-data td {
            padding: 2px 0;
            vertical-align: top;
        }
        .total-box {
            font-size: 11px;
            font-weight: bold;
            margin: 6px 0;
            text-align: right;
            padding: 3px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }
        .leyenda {
            font-size: 7.5px;
            text-align: center;
            line-height: 1.2;
            margin-top: 6px;
        }
        .firma-box {
            margin-top: 25px;
            text-align: center;
            font-size: 8px;
        }
        .linea-firma {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 3px auto;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO OFICIAL TICKET ROLLO -->
    <div class="text-center">
        <div class="font-bold header-title">EMAPAP - PATACAMAYA</div>
        <div class="header-sub">Empresa Municipal de Agua Potable y Alcantarillado</div>
        <div class="header-sub font-bold">Patacamaya - La Paz - Bolivia</div>
        <div class="header-sub">Suministro de Servicios Básicos y Micromedición</div>
    </div>

    <div class="double-divider"></div>

    <div class="text-center">
        <div class="font-bold" style="font-size: 10px;">COMPROBANTE OFICIAL DE PAGO</div>
        <div style="font-size: 8.5px; font-weight: bold;">CONEXIÓN DOMICILIARIA</div>
        <div class="font-bold" style="font-size: 12px; margin-top: 2px;">FACTURA / RECIBO N° #{{ $aporte->factura }}</div>
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <strong>Fecha:</strong> {{ $fechaEmision->format('d/m/Y H:i') }}
    </div>
    <div class="info-row">
        <strong>Código Socio:</strong> {{ $codigoSocio }}
    </div>
    <div class="info-row">
        <strong>Titular / Razón Social:</strong><br>
        <span class="font-bold">{{ $nombreSocio }}</span>
    </div>
    <div class="info-row">
        <strong>NIT / C.I.:</strong> {{ $ci }}
    </div>
    <div class="info-row">
        <strong>Zona / Dirección:</strong> {{ $zona }}
    </div>
    <div class="info-row">
        <strong>Servicio:</strong> {{ $tipoServicioNombre }}
    </div>
    <div class="info-row">
        <strong>Periodo / Cuota:</strong> {{ $aporte->periodo ?: '-' }} ({{ $aporte->orden ?: ('Plazo: ' . $aporte->plazo . ' mes(es)') }})
    </div>

    <div class="divider"></div>

    <!-- DETALLE DEL PAGO -->
    <table class="table-data">
        <thead>
            <tr style="border-bottom: 1px dashed #000;">
                <th class="text-left">CONCEPTO</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-left">
                    Pago de Cuota de Conexión<br>
                    <span style="font-size: 7.5px; color: #444;">{{ $tipoServicioNombre }} ({{ $aporte->orden ?: 'Cuota Pactada' }})</span>
                </td>
                <td class="text-right font-bold">
                    Bs {{ number_format($montoTotal, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        TOTAL COBRADO: Bs {{ number_format($montoTotal, 2) }}
    </div>

    <div style="font-size: 8px; margin-top: 3px;">
        <strong>SON:</strong> {{ $numeroLiteral }}
    </div>

    <div style="font-size: 8px; margin-top: 4px;">
        <strong>Estado:</strong> <span class="font-bold">{{ $aporte->pagado ? 'CANCELADO / PAGADO' : 'PENDIENTE DE PAGO' }}</span>
    </div>

    @if(!empty($aporte->observaciones))
        <div style="font-size: 7.5px; margin-top: 3px; font-style: italic;">
            <strong>Obs:</strong> {{ $aporte->observaciones }}
        </div>
    @endif

    <div class="firma-box">
        <div class="linea-firma"></div>
        <span>VENTANILLA DE COBRANZAS</span><br>
        <span>EMAPAP - PATACAMAYA</span>
    </div>

    <div class="divider"></div>

    <div class="leyenda">
        Documento de control y recaudación de pagos diferidos.<br>
        Sistema Integrado de Gestión Comercial EMAPAP.
    </div>

</body>
</html>
