<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen de Operaciones y Facturación por Zonas - EMAPAP</title>
    <style>
        @page {
            margin: 8mm;
            size: letter landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #222;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2E7D32;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h1 {
            font-size: 14px;
            margin: 0;
            color: #1B5E20;
            text-transform: uppercase;
        }
        .header-title h2 {
            font-size: 11px;
            margin: 2px 0 0 0;
            color: #2E7D32;
        }
        .badge-periodo {
            background-color: #E8F5E9;
            border: 1px solid #2E7D32;
            border-radius: 4px;
            padding: 4px 8px;
            font-weight: bold;
            color: #1B5E20;
            text-align: right;
            font-size: 10px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .kpi-box {
            border: 1px solid #C8E6C9;
            background-color: #F1F8E9;
            border-radius: 4px;
            padding: 6px;
            text-align: center;
        }
        .kpi-title {
            font-size: 8px;
            color: #33691E;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kpi-val {
            font-size: 12px;
            font-weight: bold;
            color: #1B5E20;
            margin-top: 2px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #2E7D32;
            color: white;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 4px;
            border: 1px solid #1B5E20;
            text-align: center;
        }
        .data-table td {
            border: 1px solid #CCCCCC;
            padding: 5px 4px;
            vertical-align: middle;
            font-size: 8.5px;
        }
        .data-table tr:nth-child(even) {
            background-color: #FAFAFA;
        }
        .data-table tfoot td {
            background-color: #E8F5E9;
            font-weight: bold;
            border-top: 2px solid #2E7D32;
            padding: 6px 4px;
            font-size: 9px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .signatures-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 15px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 35px;
            padding-top: 4px;
            font-size: 8.5px;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #777;
            text-align: center;
            border-top: 1px solid #DDD;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    <!-- Membrete -->
    <table class="header-table">
        <tr>
            <td style="width: 25%; vertical-align: top;">
                <strong style="font-size: 11px; color: #1B5E20;">{{ $empresa?->razon_social ?? 'EMAPAP' }}</strong><br>
                <span style="font-size: 8px; color: #666;">Dirección Comercial y Financiera</span><br>
                <span style="font-size: 8px; color: #666;">Liquidación Mensual de Facturación</span>
            </td>
            <td style="width: 50%; vertical-align: top;" class="header-title">
                <h1>RESUMEN DE OPERACIONES Y FACTURACIÓN POR ZONAS</h1>
                <h2>(LIQUIDACIÓN OFICIAL DE CONSUMOS E INGRESOS)</h2>
            </td>
            <td style="width: 25%; vertical-align: top; text-align: right;">
                <div class="badge-periodo">
                    PERÍODO: {{ $periodo->periodo }}<br>
                    <span style="font-size: 8px; font-weight: normal; color: #333;">
                        Estado: {{ $periodo->estado_label }}<br>
                        Vcto: {{ $periodo->fecha_vencimiento_pago?->format('d/m/Y') }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Tarjetas de Resumen KPI -->
    <table class="kpi-table">
        <tr>
            <td style="width: 20%; padding: 3px;">
                <div class="kpi-box">
                    <div class="kpi-title">Total Abonados</div>
                    <div class="kpi-val">{{ number_format($totales['abonados'], 0) }}</div>
                </div>
            </td>
            <td style="width: 20%; padding: 3px;">
                <div class="kpi-box">
                    <div class="kpi-title">Volumen Agua Facturado</div>
                    <div class="kpi-val">{{ number_format($totales['consumo_m3'], 0) }} m³</div>
                </div>
            </td>
            <td style="width: 20%; padding: 3px;">
                <div class="kpi-box">
                    <div class="kpi-title">Total Facturado Mes</div>
                    <div class="kpi-val">Bs {{ number_format($totales['facturado_bs'], 2) }}</div>
                </div>
            </td>
            <td style="width: 20%; padding: 3px;">
                <div class="kpi-box">
                    <div class="kpi-title">Cobrado en Ventanilla</div>
                    <div class="kpi-val">Bs {{ number_format($totales['cobrado_bs'], 2) }}</div>
                </div>
            </td>
            <td style="width: 20%; padding: 3px;">
                <div class="kpi-box">
                    <div class="kpi-title">% Recaudación</div>
                    <div class="kpi-val">
                        {{ $totales['facturado_bs'] > 0 ? number_format(($totales['cobrado_bs'] / $totales['facturado_bs']) * 100, 1) : 0 }}%
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabla Detalle por Zona -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">N°</th>
                <th style="width: 50px;">Código</th>
                <th style="width: 130px;">Zona Comercial</th>
                <th style="width: 55px;">Abonados</th>
                <th style="width: 65px;">Consumo (m³)</th>
                <th style="width: 75px;">Imp. Agua (Bs)</th>
                <th style="width: 75px;">Alcantarillado</th>
                <th style="width: 65px;">Otros Cargos</th>
                <th style="width: 65px;">Desc. Ley 1886</th>
                <th style="width: 80px;">Total Facturado</th>
                <th style="width: 75px;">Recaudado (Bs)</th>
                <th style="width: 50px;">% Cobro</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filas as $idx => $f)
                @php
                    $pct = (float) $f->total_facturado_bs > 0 ? ((float) $f->total_cobrado_bs / (float) $f->total_facturado_bs) * 100 : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center text-bold">{{ $f->zona_codigo }}</td>
                    <td>{{ $f->zona_nombre }}</td>
                    <td class="text-right">{{ number_format($f->total_abonados, 0) }}</td>
                    <td class="text-right">{{ number_format((float) $f->consumo_total_m3, 0) }}</td>
                    <td class="text-right">{{ number_format((float) $f->total_agua_bs, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $f->total_alcantarillado_bs, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $f->total_otros_cargos_bs, 2) }}</td>
                    <td class="text-right" style="color: #C62828;">-{{ number_format((float) $f->total_ley1886_bs, 2) }}</td>
                    <td class="text-right text-bold" style="color: #1B5E20;">{{ number_format((float) $f->total_facturado_bs, 2) }}</td>
                    <td class="text-right text-bold" style="color: #0D47A1;">{{ number_format((float) $f->total_cobrado_bs, 2) }}</td>
                    <td class="text-center">{{ number_format($pct, 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 15px; color: #777;">
                        No existen lecturas ni facturación registradas para este período.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-bold" style="text-align: right;">TOTALES GENERALES:</td>
                <td class="text-right">{{ number_format($totales['abonados'], 0) }}</td>
                <td class="text-right">{{ number_format($totales['consumo_m3'], 0) }}</td>
                <td class="text-right">{{ number_format($totales['agua_bs'], 2) }}</td>
                <td class="text-right">{{ number_format($totales['alcantarillado_bs'], 2) }}</td>
                <td class="text-right">{{ number_format($totales['otros_cargos_bs'], 2) }}</td>
                <td class="text-right" style="color: #C62828;">-{{ number_format($totales['ley1886_bs'], 2) }}</td>
                <td class="text-right text-bold" style="color: #1B5E20;">{{ number_format($totales['facturado_bs'], 2) }}</td>
                <td class="text-right text-bold" style="color: #0D47A1;">{{ number_format($totales['cobrado_bs'], 2) }}</td>
                <td class="text-center text-bold">
                    {{ $totales['facturado_bs'] > 0 ? number_format(($totales['cobrado_bs'] / $totales['facturado_bs']) * 100, 1) : 0 }}%
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Firmas Oficiales -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-line">
                    RESPONSABLE DE FACTURACIÓN<br>
                    Elaborado por
                </div>
            </td>
            <td>
                <div class="signature-line">
                    JEFE COMERCIAL Y FINANCIERO<br>
                    Revisado por
                </div>
            </td>
            <td>
                <div class="signature-line">
                    GERENTE GENERAL EMAPAP<br>
                    Aprobado por
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Resumen financiero y volumétrico por zonas — Impreso por: {{ $generadoPor }} — Fecha: {{ $fechaImpresion }}
    </div>

</body>
</html>
