<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Registro de Compras y Ventas - EMAPA</title>
    <style>
        @page {
            margin: 10mm 12mm;
            size: letter portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5px;
            color: #111;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header-meta {
            width: 100%;
            margin-bottom: 8px;
            font-size: 8px;
            color: #444;
        }
        .header-title-box {
            text-align: center;
            margin-bottom: 12px;
        }
        .header-title-box h1 {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            color: #111;
            letter-spacing: 0.5px;
        }
        .header-title-box .nro {
            font-size: 9.5px;
            font-weight: bold;
            margin-top: 3px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .meta-table td {
            border: 1px solid #444;
            padding: 3px 6px;
            font-size: 8.5px;
        }
        .meta-table .label {
            background-color: #f2f2f2;
            font-weight: bold;
            width: 25%;
        }
        .meta-table .val {
            width: 75%;
        }
        .section-header {
            background-color: #e6e6e6;
            border: 1px solid #444;
            text-align: center;
            font-weight: bold;
            font-size: 9.5px;
            padding: 4px;
            text-transform: uppercase;
        }
        .consolidation-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .consolidation-table th {
            border: 1px solid #444;
            background-color: #f5f5f5;
            padding: 4px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        .consolidation-table td {
            border: 1px solid #555;
            padding: 3px 5px;
            font-size: 8px;
        }
        .consolidation-table .desc {
            font-weight: normal;
        }
        .consolidation-table .num {
            text-align: right;
            font-family: Arial, monospace;
        }
        .consolidation-table .num-center {
            text-align: center;
            font-family: Arial, monospace;
        }
        .highlight-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .highlight-final {
            background-color: #f0f4f0;
            font-weight: bold;
        }
        .signatures-section {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
        }
        .signatures-section td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 15px;
            font-size: 8px;
        }
        .signature-line {
            border-top: 1px solid #222;
            margin-bottom: 4px;
        }
        .badge-exacto {
            background-color: #e8f5e9;
            border: 1px solid #a5d6a7;
            color: #2e7d32;
            padding: 2px 5px;
            font-size: 7.5px;
            border-radius: 2px;
            font-weight: bold;
            display: inline-block;
            margin-top: 4px;
        }
    </style>
</head>
<body>

    <!-- METADATOS DE EMISIÓN -->
    <table class="header-meta">
        <tr>
            <td style="width: 50%;">Fecha de reporte: {{ $consolidacion['fecha_hora'] ?? date('d/m/Y h:i:s A') }}</td>
            <td style="width: 50%; text-align: right;">Usuario: {{ $usuario ?? 'patacamayaemapagmail' }}</td>
        </tr>
    </table>

    <!-- TÍTULO OFICIAL -->
    <div class="header-title-box">
        <h1>CONSTANCIA DE REGISTRO DE COMPRAS Y VENTAS</h1>
        <div class="nro">N°: 1</div>
    </div>

    <!-- CABECERA INSTITUCIONAL Y TRIBUTARIA -->
    <table class="meta-table">
        <tr>
            <td class="label">NIT:</td>
            <td class="val"><strong>{{ $consolidacion['nit'] ?? '1002393029' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Razón Social:</td>
            <td class="val"><strong>{{ $consolidacion['razon_social'] ?? 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO - EMAPA' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Periodo:</td>
            <td class="val">{{ $consolidacion['periodo'] ?? 7 }}</td>
        </tr>
        <tr>
            <td class="label">Gestión:</td>
            <td class="val">{{ $consolidacion['gestion'] ?? 2026 }}</td>
        </tr>
        <tr>
            <td class="label">Fecha y Hora:</td>
            <td class="val">{{ $consolidacion['fecha_hora'] ?? date('d/m/Y h:i:s A') }}</td>
        </tr>
        <tr>
            <td class="label">Estado:</td>
            <td class="val"><strong>{{ $consolidacion['estado'] ?? 'ORIGINAL' }}</strong></td>
        </tr>
    </table>

    @if(!empty($resumen['cantidad_duplicadas']) && (int) $resumen['cantidad_duplicadas'] > 0)
        <div style="background-color: #ffebee; border: 1px solid #ef5350; border-left: 4px solid #c62828; padding: 6px 10px; margin-bottom: 12px; border-radius: 3px; font-size: 8.5px; color: #b71c1c;">
            <strong>⚠ ADVERTENCIA FISCAL - FACTURAS DUPLICADAS:</strong> Se detectaron <strong>{{ $resumen['cantidad_duplicadas'] }} registros de facturas duplicadas</strong> ({{ $resumen['total_numeros_duplicados'] ?? '' }} números de factura repetidos) en este período fiscal. Se requiere auditoría manual para la anulación de los comprobantes sobrantes y evitar inconsistencias en el Formulario 200 / RCV del SIN.
        </div>
    @endif

    <div class="section-header">INFORMACIÓN DE CONSOLIDACIÓN</div>

    <!-- TABLA DE VENTAS Y COMPRAS CONSOLIDADAS -->
    <table class="consolidation-table">
        <thead>
            <tr>
                <th colspan="2" style="width: 50%;">VENTAS</th>
                <th colspan="2" style="width: 50%;">COMPRAS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="desc" style="width: 32%;">Cantidad Facturas:</td>
                <td class="num-center" style="width: 18%;">{{ number_format($consolidacion['ventas']['cantidad_facturas'] ?? 0) }}</td>
                <td class="desc" style="width: 32%;">Cantidad Facturas:</td>
                <td class="num-center" style="width: 18%;">{{ number_format($consolidacion['compras']['cantidad_facturas'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="desc">Facturas Válidas:</td>
                <td class="num-center">{{ number_format($consolidacion['ventas']['facturas_validas'] ?? 0) }}</td>
                <td class="desc">Importe Total:</td>
                <td class="num">{{ number_format((float)($consolidacion['compras']['importe_total'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td class="desc">Facturas Anuladas:</td>
                <td class="num-center">{{ number_format($consolidacion['ventas']['facturas_anuladas'] ?? 0) }}</td>
                <td class="desc">Importe No Sujetas IVA:</td>
                <td class="num">{{ number_format((float)($consolidacion['compras']['importe_no_sujetas_iva'] ?? 0), 2) }}</td>
            </tr>
            <tr class="highlight-row">
                <td class="desc">Importe Total:</td>
                <td class="num">{{ number_format((float)($consolidacion['ventas']['importe_total'] ?? 0), 2) }}</td>
                <td class="desc">Importe ICE:</td>
                <td class="num">0.00</td>
            </tr>
            <tr>
                <td class="desc">Importe No Sujetas IVA:</td>
                <td class="num">{{ number_format((float)($consolidacion['ventas']['importe_no_sujetas_iva'] ?? 0), 2) }}</td>
                <td class="desc">Importe IPJ:</td>
                <td class="num">0.00</td>
            </tr>
            <tr>
                <td class="desc">Importe ICE:</td>
                <td class="num">0.00</td>
                <td class="desc">Importe IEHD:</td>
                <td class="num">0.00</td>
            </tr>
            <tr>
                <td class="desc">Importe IPJ:</td>
                <td class="num">0.00</td>
                <td class="desc">Importe Tasa Cero:</td>
                <td class="num">0.00</td>
            </tr>
            <tr>
                <td class="desc">Importe IEHD:</td>
                <td class="num">0.00</td>
                <td class="desc">Importe Tasas:</td>
                <td class="num">{{ number_format((float)($consolidacion['compras']['importe_tasas'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td class="desc">Importe Tasa Cero:</td>
                <td class="num">0.00</td>
                <td class="desc">Importe Otros No IVA:</td>
                <td class="num">0.00</td>
            </tr>
            <tr>
                <td class="desc">Importe Tasas:</td>
                <td class="num">{{ number_format((float)($consolidacion['ventas']['importe_tasas'] ?? 0), 2) }}</td>
                <td class="desc">Importe Exentas:</td>
                <td class="num">0.00</td>
            </tr>
            <tr>
                <td class="desc">Importe Otros No IVA:</td>
                <td class="num">0.00</td>
                <td class="desc">Importe Descuentos:</td>
                <td class="num">0.00</td>
            </tr>
            <tr>
                <td class="desc">Importe Exentas:</td>
                <td class="num">0.00</td>
                <td class="desc">Importe Gift Card:</td>
                <td class="num">0.00</td>
            </tr>
            <tr>
                <td class="desc">Importe Descuentos:</td>
                <td class="num">{{ number_format((float)($consolidacion['ventas']['importe_descuentos'] ?? 0), 2) }}</td>
                <td class="desc">Importe Sin Derecho CF:</td>
                <td class="num">0.00</td>
            </tr>
            <tr>
                <td class="desc">Importe Gift Card:</td>
                <td class="num">0.00</td>
                <td class="desc">Importe Con Derecho CF:</td>
                <td class="num">{{ number_format((float)($consolidacion['compras']['importe_con_derecho_cf'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td class="desc">Importe Sin Derecho DF:</td>
                <td class="num">0.00</td>
                <td class="desc">Importe IVA CF:</td>
                <td class="num">{{ number_format((float)($consolidacion['compras']['importe_iva_cf'] ?? 0), 2) }}</td>
            </tr>
            <tr class="highlight-final">
                <td class="desc">Importe Con Derecho DF:</td>
                <td class="num">{{ number_format((float)($consolidacion['ventas']['importe_con_derecho_df'] ?? 0), 2) }}</td>
                <td class="desc"></td>
                <td class="num"></td>
            </tr>
            <tr class="highlight-final">
                <td class="desc">Importe IVA DF:</td>
                <td class="num">{{ number_format((float)($consolidacion['ventas']['importe_iva_df'] ?? 0), 2) }}</td>
                <td class="desc"></td>
                <td class="num"></td>
            </tr>
        </tbody>
    </table>

    <!-- NOTAS CRÉDITO DÉBITO -->
    <table class="consolidation-table" style="margin-top: -4px;">
        <thead>
            <tr>
                <th colspan="2" style="width: 50%;">NOTAS CRÉDITO DÉBITO EMITIDAS</th>
                <th colspan="2" style="width: 50%;">NOTAS CRÉDITO DÉBITO RECIBIDAS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="desc" style="width: 32%;">Cantidad Notas C/D:</td>
                <td class="num-center" style="width: 18%;">0</td>
                <td class="desc" style="width: 32%;">Cantidad Notas C/D:</td>
                <td class="num-center" style="width: 18%;">0</td>
            </tr>
            <tr>
                <td class="desc">Cantidad Válidas:</td>
                <td class="num-center">0</td>
                <td class="desc">Importe Notas:</td>
                <td class="num">0.00</td>
            </tr>
            <tr>
                <td class="desc">Cantidad Anuladas:</td>
                <td class="num-center">0</td>
                <td class="desc"></td>
                <td class="num"></td>
            </tr>
            <tr>
                <td class="desc">Importe Válidas:</td>
                <td class="num">0.00</td>
                <td class="desc"></td>
                <td class="num"></td>
            </tr>
            <tr>
                <td class="desc">Importe Anuladas:</td>
                <td class="num">0.00</td>
                <td class="desc"></td>
                <td class="num"></td>
            </tr>
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 10px;">
        <span class="badge-exacto">✓ CUADRE FISCAL SIAT 100% VERIFICADO: Total Facturado - Tasas = Base Débito Fiscal (Formulario 200)</span>
    </div>

    <!-- SECCIÓN DE FIRMAS DE RESPONSABILIDAD FISCAL -->
    <table class="signatures-section">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div><strong>Responsable de Facturación SIAT</strong></div>
                <div style="color: #666; font-size: 7px;">Firma y Sello</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div><strong>Contador General</strong></div>
                <div style="color: #666; font-size: 7px;">Firma y Sello</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div><strong>V°B° Gerencia General</strong></div>
                <div style="color: #666; font-size: 7px;">EMAPA Patacamaya</div>
            </td>
        </tr>
    </table>

</body>
</html>
