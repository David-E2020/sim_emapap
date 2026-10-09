<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletas Masivas de Pago — {{ $periodo ?? 'Planilla' }}</title>
    <style>
        @page {
            margin: 3.5mm 6mm;
            size: letter portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 7px;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.15;
            background: #ffffff;
        }
        .boleta-page-sheet {
            page-break-after: always;
            box-sizing: border-box;
            padding: 0;
            margin: 0;
        }
        .boleta-page-sheet:last-child {
            page-break-after: auto;
        }

        /* ── MEDIA CARTA (HALF-SHEET) ── */
        .boleta-half {
            border: 1.2px solid #0f2942;
            padding: 5px 8px;
            background: #ffffff;
            box-sizing: border-box;
            position: relative;
            border-radius: 3px;
        }

        /* ── LÍNEA DE CORTE CON TIJERAS ── */
        .cut-divider {
            text-align: center;
            font-size: 6px;
            color: #64748b;
            margin: 2mm 0;
            line-height: 1;
            letter-spacing: 1.5px;
            font-weight: bold;
        }
        .cut-scissors {
            font-size: 8px;
            color: #0f2942;
            font-style: normal;
        }

        /* ── CABECERA ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-box {
            width: 135px;
        }
        .logo-img {
            max-height: 32px;
            max-width: 130px;
            display: block;
        }
        .logo-fallback {
            font-size: 12px;
            font-weight: 900;
            color: #0f2942;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .institution-box {
            text-align: center;
            padding: 0 4px;
        }
        .institution-country {
            font-size: 5.8px;
            letter-spacing: 1px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
        }
        .institution-name {
            font-size: 8.5px;
            font-weight: 800;
            color: #0f2942;
            text-transform: uppercase;
            margin: 0.5px 0;
            line-height: 1.1;
        }
        .institution-meta {
            font-size: 5.8px;
            color: #64748b;
            line-height: 1.15;
        }
        .cite-box {
            width: 145px;
            text-align: right;
        }
        .doc-certificate {
            border: 1px solid #0f2942;
            background: #f8fafc;
            padding: 2.5px 5px;
            display: inline-block;
            text-align: center;
            min-width: 135px;
            border-radius: 3px;
        }
        .doc-certificate-title {
            font-size: 6.5px;
            font-weight: 800;
            color: #0f2942;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-certificate-num {
            font-size: 8.5px;
            font-weight: 900;
            color: #0369a1;
            font-family: monospace;
            line-height: 1.1;
        }
        .doc-certificate-period {
            font-size: 6px;
            font-weight: 700;
            color: #334155;
        }
        .badge-copia {
            display: inline-block;
            margin-top: 1.5px;
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 5.8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-beneficiario {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .badge-contabilidad {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        /* ── BANDA DE IDENTIFICACIÓN ── */
        .banda-titulo {
            background: #0f2942;
            color: #ffffff;
            font-size: 6.8px;
            font-weight: 800;
            text-align: center;
            padding: 2px 0;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 3px;
            border-radius: 2px;
        }

        /* ── DATOS DEL TRABAJADOR ── */
        .worker-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
            font-size: 6.6px;
        }
        .worker-table td {
            border: 1px solid #cbd5e1;
            padding: 1.5px 3.5px;
        }
        .worker-table .lbl {
            background: #f1f5f9;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            width: 13%;
        }
        .worker-table .val {
            color: #0f172a;
            font-weight: 600;
            width: 20%;
        }
        .worker-table .val-highlight {
            font-weight: 800;
            color: #0f2942;
        }

        /* ── TABLA COMPARATIVA INGRESOS / DESCUENTOS ── */
        .two-columns {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        .two-columns > tbody > tr > td {
            vertical-align: top;
            width: 50%;
            padding: 0 2px;
        }
        .section-box {
            border: 1px solid #cbd5e1;
            border-radius: 2px;
            overflow: hidden;
        }
        .section-header {
            font-size: 6.6px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 2px 4px;
            letter-spacing: 0.4px;
            border-bottom: 1px solid #cbd5e1;
        }
        .section-header-ingresos {
            background: #e0f2fe;
            color: #0369a1;
        }
        .section-header-descuentos {
            background: #fee2e2;
            color: #b91c1c;
        }
        .calc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.2px;
        }
        .calc-table td {
            padding: 1.2px 4px;
            border-bottom: 1px solid #f1f5f9;
        }
        .calc-table td:last-child {
            text-align: right;
            font-family: monospace;
            font-size: 6.4px;
            font-weight: 600;
        }
        .calc-table .sub-item {
            padding-left: 10px;
            color: #64748b;
        }
        .calc-table .subtotal-row td {
            border-top: 1px solid #cbd5e1;
            border-bottom: none;
            padding: 2.2px 4px;
            font-size: 6.8px;
            font-weight: 800;
            background: #f8fafc;
        }
        .calc-table .subtotal-row-ingresos td {
            color: #0369a1;
        }
        .calc-table .subtotal-row-descuentos td {
            color: #b91c1c;
        }

        /* ── LÍQUIDO PAGABLE ── */
        .liquido-banner {
            border: 1px solid #0f2942;
            background: #f8fafc;
            border-radius: 3px;
            padding: 2.5px 6px;
            text-align: center;
            margin-bottom: 3px;
        }
        .liquido-row {
            display: table;
            width: 100%;
        }
        .liquido-cell-left {
            display: table-cell;
            text-align: left;
            vertical-align: middle;
            width: 60%;
        }
        .liquido-cell-right {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            width: 40%;
        }
        .liquido-label {
            font-size: 6.2px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
        }
        .liquido-literal {
            font-size: 5.8px;
            font-weight: 700;
            color: #0369a1;
            text-transform: uppercase;
        }
        .liquido-amount {
            font-size: 13px;
            font-weight: 900;
            color: #0f2942;
            letter-spacing: 0.5px;
            font-family: monospace;
        }

        /* ── APORTES PATRONALES (INFORMATIVO) ── */
        .patronal-box {
            border: 1px solid #e2e8f0;
            background: #fafafa;
            border-radius: 2px;
            padding: 1.5px 4px;
            margin-bottom: 4px;
            font-size: 5.6px;
            color: #475569;
        }
        .patronal-table {
            width: 100%;
            border-collapse: collapse;
        }
        .patronal-table td {
            padding: 0.5px 2px;
        }
        .patronal-title {
            font-weight: 800;
            color: #0f2942;
            text-transform: uppercase;
            font-size: 5.6px;
            margin-bottom: 1px;
        }

        /* ── FIRMAS ── */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .signatures-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 4px;
        }
        .sign-line {
            border-top: 0.8px solid #475569;
            width: 105px;
            margin: 0 auto 1.5px auto;
        }
        .sign-name {
            font-size: 6.2px;
            font-weight: 800;
            color: #0f2942;
            text-transform: uppercase;
        }
        .sign-role {
            font-size: 5.6px;
            color: #64748b;
            line-height: 1.1;
        }
        .sign-receipt-note {
            font-size: 5.2px;
            font-style: italic;
            color: #94a3b8;
        }

        /* ── PIE LEGAL ── */
        .footer-note {
            border-top: 0.8px solid #f1f5f9;
            margin-top: 3px;
            padding-top: 1.5px;
            font-size: 5.2px;
            color: #94a3b8;
            text-align: center;
            line-height: 1.15;
        }
    </style>
</head>
<body>

@php
    $logoPath = public_path('images/emapa_logo_horizontal.png');
    $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
@endphp

@foreach($boletas as $data)
@php
    $esDeclarada = !empty($data['es_cerrada']);
    $totalGanado = (float)($data['ingresos']['total_ganado'] ?? 0);
    $cnsPatronal = round($totalGanado * 0.10, 2);
    $riesgoPatronal = round($totalGanado * 0.0171, 2);
    $viviendaPatronal = round($totalGanado * 0.02, 2);
    $solidarioPatronal = round($totalGanado * 0.03, 2);
    $comisionPatronal = round($totalGanado * 0.005, 2);
    $totalPatronal = round($cnsPatronal + $riesgoPatronal + $viviendaPatronal + $solidarioPatronal + $comisionPatronal, 2);
@endphp

<div class="boleta-page-sheet">
    @foreach(['BENEFICIARIO', 'CONTABILIDAD'] as $tipoCopia)
    <div class="boleta-half">
        {{-- CABECERA INSTITUCIONAL --}}
        <table class="header-table">
            <tr>
                <td class="logo-box">
                    @if($logoBase64)
                        <img src="data:image/png;base64,{{ $logoBase64 }}" class="logo-img" alt="EMAPAP Patacamaya">
                    @else
                        <div class="logo-fallback">EMAPAP</div>
                        <div style="font-size:5.5px;color:#64748b;font-weight:700;">AGUA POTABLE · S.I.E.</div>
                    @endif
                </td>
                <td class="institution-box">
                    <div class="institution-country">Estado Plurinacional de Bolivia</div>
                    <div class="institution-name">{{ $data['institucion'] ?? 'Empresa Municipal de Agua Potable y Alcantarillado - Patacamaya' }}</div>
                    <div class="institution-meta">
                        NIT: {{ $data['nit'] ?? '1002393029' }} &nbsp;·&nbsp; {{ $data['patronal_cns'] ?? 'C.N.S. Patronal N° 01-521-00002' }} &nbsp;·&nbsp; ROE MTEPS: {{ $data['patronal_min_trabajo'] ?? '1002393029-1' }}<br>
                        {{ $data['direccion'] ?? 'PLAZA BOLIVAR - ZONA ESTACION' }} &nbsp;·&nbsp; {{ $data['ubicacion'] ?? 'PATACAMAYA - LA PAZ - BOLIVIA' }}
                    </div>
                </td>
                <td class="cite-box">
                    <div class="doc-certificate">
                        <div class="doc-certificate-title">Papeleta de Pago</div>
                        <div class="doc-certificate-num">{{ $data['boleta_nro'] }}</div>
                        <div class="doc-certificate-period">{{ strtoupper($data['periodo']) }}</div>
                        @if($tipoCopia === 'BENEFICIARIO')
                            <span class="badge-copia badge-beneficiario">COPIA: BENEFICIARIO / TRABAJADOR</span>
                        @else
                            <span class="badge-copia badge-contabilidad">COPIA: CONTABILIDAD / ARCHIVO RRHH</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- BANDA TITULAR --}}
        <div class="banda-titulo">
            Papeleta Individual Oficial de Pago de Haberes y Salarios
        </div>

        {{-- DATOS DEL FUNCIONARIO --}}
        <table class="worker-table">
            <tr>
                <td class="lbl">Funcionario:</td>
                <td class="val val-highlight" colspan="3">{{ strtoupper($data['funcionario']['nombre_completo']) }}</td>
                <td class="lbl">C.I. / Doc.:</td>
                <td class="val">{{ $data['funcionario']['ci'] }}</td>
            </tr>
            <tr>
                <td class="lbl">Cargo:</td>
                <td class="val" colspan="3">{{ strtoupper($data['funcionario']['cargo']) }}</td>
                <td class="lbl">Ítem / Código:</td>
                <td class="val val-highlight">{{ $data['funcionario']['item'] ?: 'P-01' }}</td>
            </tr>
            <tr>
                <td class="lbl">Unidad Org.:</td>
                <td class="val" colspan="3">{{ strtoupper($data['funcionario']['unidad'] ?? 'ADMINISTRACIÓN Y FINANZAS') }}</td>
                <td class="lbl">Tipo Personal:</td>
                <td class="val">PLANTA PERMANENTE</td>
            </tr>
            <tr>
                <td class="lbl">Antigüedad:</td>
                <td class="val">{{ $data['funcionario']['antiguedad_anios'] }} años ({{ $data['ingresos']['porcentaje_antiguedad'] }}%)</td>
                <td class="lbl">Días Trab.:</td>
                <td class="val">{{ $data['funcionario']['dias_trabajados'] ?? 30 }} días</td>
                <td class="lbl">Fecha Emisión:</td>
                <td class="val">{{ $data['fecha_emision'] }}</td>
            </tr>
        </table>

        {{-- CUERPO: INGRESOS VS DESCUENTOS --}}
        <table class="two-columns">
            <tr>
                {{-- COLUMNA I: INGRESOS --}}
                <td>
                    <div class="section-box">
                        <div class="section-header section-header-ingresos">
                            I. Ingresos y Haberes Computables
                        </div>
                        <table class="calc-table">
                            <tr>
                                <td>Haber Básico Mensual</td>
                                <td>Bs. {{ number_format($data['ingresos']['haber_basico'], 2) }}</td>
                            </tr>
                            @if(($data['ingresos']['bono_antiguedad'] ?? 0) > 0)
                            <tr>
                                <td>Bono de Antigüedad ({{ $data['ingresos']['porcentaje_antiguedad'] }}%)</td>
                                <td>Bs. {{ number_format($data['ingresos']['bono_antiguedad'], 2) }}</td>
                            </tr>
                            @else
                            <tr>
                                <td style="color:#94a3b8">Bono de Antigüedad (0%)</td>
                                <td style="color:#94a3b8">Bs. 0.00</td>
                            </tr>
                            @endif
                            @if(($data['ingresos']['horas_extras'] ?? 0) > 0)
                            <tr>
                                <td>Horas Extraordinarias</td>
                                <td>Bs. {{ number_format($data['ingresos']['horas_extras'], 2) }}</td>
                            </tr>
                            @endif
                            @if(($data['ingresos']['refrigerios_bs'] ?? 0) > 0)
                            <tr>
                                <td>Asignación Refrigerio</td>
                                <td>Bs. {{ number_format($data['ingresos']['refrigerios_bs'], 2) }}</td>
                            </tr>
                            @endif
                            <tr class="subtotal-row subtotal-row-ingresos">
                                <td>TOTAL GANADO IMPONIBLE:</td>
                                <td>Bs. {{ number_format($data['ingresos']['total_ganado'], 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </td>

                {{-- COLUMNA II: DESCUENTOS --}}
                <td>
                    <div class="section-box">
                        <div class="section-header section-header-descuentos">
                            II. Descuentos y Retenciones de Ley
                        </div>
                        <table class="calc-table">
                            <tr>
                                <td>Retención Gestora Pública (12.71%)</td>
                                <td>Bs. {{ number_format($data['descuentos']['gestora_12_71'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="sub-item">· Cotización Vejez (10.00%)</td>
                                <td>{{ number_format($data['descuentos']['vejez_10'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="sub-item">· Riesgo Común (1.71%)</td>
                                <td>{{ number_format($data['descuentos']['riesgo_1_71'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="sub-item">· Comisión Gestora (0.50%)</td>
                                <td>{{ number_format($data['descuentos']['comision_0_5'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="sub-item">· Aporte Solidario Asegurado (0.50%)</td>
                                <td>{{ number_format($data['descuentos']['solidario_0_5'], 2) }}</td>
                            </tr>
                            @if(($data['descuentos']['descuento_atraso'] ?? 0) > 0)
                            <tr>
                                <td>Descuento por Atrasos</td>
                                <td>Bs. {{ number_format($data['descuentos']['descuento_atraso'], 2) }}</td>
                            </tr>
                            @endif
                            <tr class="subtotal-row subtotal-row-descuentos">
                                <td>TOTAL DESCUENTOS:</td>
                                <td>Bs. {{ number_format($data['descuentos']['total_descuentos'], 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        {{-- LÍQUIDO PAGABLE --}}
        <div class="liquido-banner">
            <div class="liquido-row">
                <div class="liquido-cell-left">
                    <div class="liquido-label">Total Líquido Pagable Efectivo Desembolsado:</div>
                    <div class="liquido-literal">SON: {{ strtoupper($data['liquido_literal']) }}</div>
                </div>
                <div class="liquido-cell-right">
                    <div class="liquido-amount">Bs. {{ number_format($data['liquido_pagable'], 2) }}</div>
                </div>
            </div>
        </div>

        {{-- APORTES PATRONALES (INFORMATIVO) --}}
        <div class="patronal-box">
            <div class="patronal-title">Aportes Patronales Institucionales (Carga Social Ley N° 065 — 17.21%):</div>
            <table class="patronal-table">
                <tr>
                    <td><strong>C.N.S. (10%):</strong> Bs. {{ number_format($cnsPatronal, 2) }}</td>
                    <td><strong>Riesgo Prof. (1.71%):</strong> Bs. {{ number_format($riesgoPatronal, 2) }}</td>
                    <td><strong>Pro-Viv. (2%):</strong> Bs. {{ number_format($viviendaPatronal, 2) }}</td>
                    <td><strong>F. Solidario (3%):</strong> Bs. {{ number_format($solidarioPatronal, 2) }}</td>
                    <td><strong>Comisión (0.5%):</strong> Bs. {{ number_format($comisionPatronal, 2) }}</td>
                    <td style="text-align:right"><strong>Total Patronal (17.21%):</strong> Bs. {{ number_format($totalPatronal, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- FIRMAS INSTITUCIONALES --}}
        <table class="signatures-table">
            <tr>
                @if($tipoCopia === 'CONTABILIDAD')
                <td>
                    <div class="sign-line"></div>
                    <div class="sign-name">{{ $data['funcionario']['nombre_completo'] }}</div>
                    <div class="sign-role">C.I.: {{ $data['funcionario']['ci'] }}<br>Firma del Funcionario</div>
                    <div class="sign-receipt-note">"Recibí Conforme el importe líquido indicado"</div>
                </td>
                @else
                <td>
                    <div class="sign-line"></div>
                    <div class="sign-name">{{ $data['funcionario']['nombre_completo'] }}</div>
                    <div class="sign-role">C.I.: {{ $data['funcionario']['ci'] }}<br>Beneficiario Titular</div>
                    <div class="sign-receipt-note">Comprobante Personal del Trabajador</div>
                </td>
                @endif
                <td>
                    <div class="sign-line"></div>
                    <div class="sign-name">Responsable de Recursos Humanos</div>
                    <div class="sign-role">EMAPAP - Patacamaya<br>Firma y Sello Oficial</div>
                </td>
                <td>
                    <div class="sign-line"></div>
                    <div class="sign-name">Gerencia General / Administrativa</div>
                    <div class="sign-role">EMAPAP - Patacamaya<br>Visto Bueno (Vo.Bo.)</div>
                </td>
            </tr>
        </table>

        {{-- PIE DE PÁGINA --}}
        <div class="footer-note">
            @if($tipoCopia === 'BENEFICIARIO')
                <strong>COPIA BENEFICIARIO</strong> · Comprobante oficial personal emitido por EMAPAP Patacamaya conforme a la Ley General del Trabajo · Emisión: {{ $data['fecha_emision'] }}
            @else
                <strong>COPIA CONTABILIDAD</strong> · Comprobante oficial de descargo y archivo contable debidamente suscrito por el funcionario · Emisión: {{ $data['fecha_emision'] }}
            @endif
        </div>
    </div>

    @if($tipoCopia === 'BENEFICIARIO')
    <div class="cut-divider">
        <span class="cut-scissors">✂</span> &nbsp; - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - &nbsp; <strong>CORTAR AQUÍ</strong> &nbsp; - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - &nbsp; <span class="cut-scissors">✂</span>
    </div>
    @endif
    @endforeach
</div>
@endforeach

</body>
</html>
