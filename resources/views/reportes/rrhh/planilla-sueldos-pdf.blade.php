<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla de Sueldos y Salarios — {{ $mes_nombre }} / {{ $anio }}</title>
    <style>
        @page {
            size: letter landscape;
            margin: 7mm 8mm 6mm 8mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8px;
            color: #0f172a;
            margin: 0;
            padding: 0;
            line-height: 1.25;
            background: #ffffff;
            width: 100%;
        }

        /* ── CABECERA INSTITUCIONAL ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-box {
            width: 220px;
        }
        .logo-img {
            max-height: 48px;
            max-width: 210px;
            display: block;
        }
        .logo-fallback {
            font-size: 15px;
            font-weight: 900;
            color: #0f2942;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .institution-box {
            text-align: center;
            padding: 0 10px;
        }
        .institution-country {
            font-size: 7.5px;
            letter-spacing: 1.5px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
        }
        .institution-name {
            font-size: 11px;
            font-weight: 900;
            color: #0f2942;
            text-transform: uppercase;
            margin: 1px 0;
            letter-spacing: 0.3px;
            line-height: 1.15;
        }
        .institution-subname {
            font-size: 8px;
            font-weight: 700;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .institution-meta {
            font-size: 7.5px;
            color: #64748b;
            line-height: 1.2;
            margin-top: 1px;
        }
        .cite-box {
            width: 220px;
            text-align: right;
        }
        .doc-certificate {
            border: 1.5px solid #0f2942;
            background: #f8fafc;
            padding: 4px 8px;
            display: inline-block;
            text-align: center;
            min-width: 195px;
            border-radius: 4px;
        }
        .doc-certificate-title {
            font-size: 8px;
            font-weight: 900;
            color: #0f2942;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .doc-certificate-periodo {
            font-size: 11px;
            font-weight: 900;
            color: #0284c7;
            text-transform: uppercase;
        }
        .doc-certificate-cite {
            font-size: 7.5px;
            color: #334155;
            margin: 2px 0;
        }
        .badge-declarada {
            background: #dcfce7;
            border: 1px solid #16a34a;
            color: #15803d;
            font-size: 7px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 8px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-borrador {
            background: #fef9c3;
            border: 1px solid #ca8a04;
            color: #a16207;
            font-size: 7px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 8px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .doc-certificate-emision {
            font-size: 6.5px;
            color: #64748b;
            margin-top: 2px;
        }

        /* ── BANDA TITULAR ── */
        .banda-table {
            width: 100%;
            border-collapse: collapse;
            background: #0f2942;
            color: #ffffff;
            margin-bottom: 5px;
            border-radius: 3px;
        }
        .banda-table td {
            padding: 4px 8px;
            vertical-align: middle;
        }
        .banda-title {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .banda-meta {
            font-size: 7.5px;
            color: #93c5fd;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ── TABLA DE PLANILLA ── */
        .planilla-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            font-size: 8px;
            page-break-inside: auto;
        }
        .planilla-table thead {
            display: table-header-group;
        }
        .planilla-table thead tr th {
            background: #1e3a5f;
            color: #ffffff;
            padding: 5px 3px;
            border: 1px solid #0f2942;
            text-align: center;
            font-weight: 800;
            font-size: 7.8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.2;
            overflow: hidden;
        }
        .planilla-table thead tr th.left { text-align: left; }
        .planilla-table thead tr th.right { text-align: right; }

        .planilla-table tbody tr {
            page-break-inside: avoid;
        }
        .planilla-table tbody tr td {
            padding: 4.5px 3px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            line-height: 1.2;
            word-wrap: break-word;
            overflow: hidden;
        }
        .planilla-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        .planilla-table tbody tr td.right { text-align: right; }
        .planilla-table tbody tr td.center { text-align: center; }
        .planilla-table tbody tr td.bold { font-weight: 700; }

        .cargo-text {
            font-size: 7.5px;
            color: #475569;
            text-transform: uppercase;
        }
        .firma-box {
            font-size: 6.8px;
            color: #64748b;
            text-align: center;
            border-bottom: 1px dotted #94a3b8;
            padding-bottom: 1px;
        }

        /* ── FILA TOTALES ── */
        .total-row td {
            font-weight: 900;
            background: #0f2942 !important;
            color: #ffffff !important;
            border: 1px solid #0f2942 !important;
            font-size: 8.5px;
            padding: 6px 4px;
            vertical-align: middle;
        }
        .total-row td.amount-ganado {
            color: #fde047 !important;
        }
        .total-row td.amount-gestora {
            color: #fca5a5 !important;
        }
        .total-row td.amount-liquido {
            color: #86efac !important;
            font-size: 9px;
        }

        /* ── SECCIÓN RESUMEN FINANCIERO Y PATRONAL ── */
        .summary-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            page-break-inside: avoid;
        }
        .summary-wrapper td.col-half {
            width: 50%;
            vertical-align: top;
            padding: 0 4px;
        }
        .summary-card {
            border: 1.2px solid #0f2942;
            border-radius: 4px;
            background: #f8fafc;
            padding: 7px 10px;
        }
        .summary-header {
            font-size: 8px;
            font-weight: 900;
            color: #0f2942;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        .summary-table td {
            padding: 2.5px 2px;
            vertical-align: middle;
        }
        .summary-table td.lbl {
            color: #334155;
            font-weight: 600;
        }
        .summary-table td.val {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
        }
        .summary-highlight {
            background: #e2e8f0;
            border-top: 1px solid #0f2942;
            border-bottom: 1px solid #0f2942;
        }
        .summary-highlight td {
            padding: 3.5px 2px;
            font-weight: 900;
            color: #0f2942;
            font-size: 8.5px;
        }
        .literal-badge {
            margin-top: 4px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-left: 3px solid #0284c7;
            padding: 3px 6px;
            font-size: 7.2px;
            color: #0369a1;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.2;
        }

        /* ── SECCIÓN FIRMAS ── */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 33.33%;
            vertical-align: top;
            text-align: center;
            padding: 0 8px;
        }
        .sign-box {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #ffffff;
            padding: 6px 8px;
            text-align: center;
        }
        .sign-seal-space {
            height: 48px;
        }
        .sign-line {
            border-top: 1.2px solid #475569;
            margin: 0 auto 3px auto;
            width: 85%;
        }
        .sign-title {
            font-size: 7.8px;
            font-weight: 800;
            color: #0f2942;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .sign-role {
            font-size: 7px;
            color: #475569;
            line-height: 1.15;
            margin-top: 1px;
        }
        .sign-badge {
            font-size: 6.2px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        /* ── PIE INSTITUCIONAL ── */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            border-top: 1px solid #cbd5e1;
            padding-top: 2px;
            font-size: 6.8px;
            color: #64748b;
            page-break-inside: avoid;
        }
        .footer-table td {
            padding-top: 3px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

@php
    $logoBase64 = $logo_base64 ?? null;
    if (!$logoBase64) {
        $logoPath = public_path('images/emapa_logo_horizontal.png');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    }
@endphp

{{-- ── CABECERA INSTITUCIONAL ── --}}
<table class="header-table">
    <tr>
        <td class="logo-box">
            @if($logoBase64)
                <img src="data:image/png;base64,{{ $logoBase64 }}" class="logo-img" alt="EMAPAP Patacamaya">
            @else
                <div class="logo-fallback">EMAPAP</div>
            @endif
        </td>
        <td class="institution-box">
            <div class="institution-country">ESTADO PLURINACIONAL DE BOLIVIA</div>
            <div class="institution-name">EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO PATACAMAYA</div>
            <div class="institution-subname">GOBIERNO AUTÓNOMO MUNICIPAL DE PATACAMAYA</div>
            <div class="institution-meta">
                Nº EMPLEADOR MIN. TRABAJO: {{ $configLaboral->nro_patronal_min_trabajo ?? '1002393029-1' }} &nbsp;•&nbsp;
                Nº EMPLEADOR C.N.S.: {{ $configLaboral->nro_patronal_cns ?? '01-521-00002' }} &nbsp;•&nbsp;
                NIT: {{ $configLaboral->nit_institucional ?? '1002393029' }}<br>
                {{ $configLaboral->ubicacion_geografica ?? 'PATACAMAYA-LA PAZ-BOLIVIA' }} &nbsp;•&nbsp;
                {{ $configLaboral->direccion_institucional ?? 'PLAZA BOLIVAR - ZONA ESTACION' }}
            </div>
        </td>
        <td class="cite-box">
            <div class="doc-certificate">
                <div class="doc-certificate-title">PLANILLA SALARIAL OFICIAL</div>
                <div class="doc-certificate-periodo">{{ strtoupper($mes_nombre) }} / {{ $anio }}</div>
                <div class="doc-certificate-cite">CITE: <strong>{{ $cite }}</strong></div>
                @if($es_declarada)
                    <span class="badge-declarada">✔ OFICIAL DECLARADA</span>
                @else
                    <span class="badge-borrador">⚙ BORRADOR DE TRABAJO</span>
                @endif
                <div class="doc-certificate-emision">Emitido: {{ $fecha_emision }}</div>
            </div>
        </td>
    </tr>
</table>

{{-- ── BANDA DE CONTROL Y RÉGIMEN ── --}}
<table class="banda-table">
    <tr>
        <td class="banda-title">
            PLANILLA OFICIAL DE HABERES — RÉGIMEN: {{ strtoupper($tipo_planilla_nombre) }} &nbsp;({{ count($items) }} SERVIDORES PÚBLICOS)
        </td>
        <td class="banda-meta">
            VALORES EXPRESADOS EN BOLIVIANOS (Bs.) &nbsp;|&nbsp; LEY GENERAL DEL TRABAJO
        </td>
    </tr>
</table>

{{-- ── TABLA PRINCIPAL DE SUELDOS ── --}}
<table class="planilla-table">
    <thead>
        <tr>
            <th style="width:3.5%;">Ítem</th>
            <th style="width:8.5%;">C.I. / DOC</th>
            <th class="left" style="width:20%;">Nombres y Apellidos</th>
            <th class="left" style="width:13%;">Cargo Institucional</th>
            <th style="width:3%;">Días</th>
            <th class="right" style="width:7.5%;">Haber Básico</th>
            <th style="width:3.5%;">% Ant.</th>
            <th class="right" style="width:7%;">Bono Antig.</th>
            <th class="right" style="width:9%;">Total Ganado</th>
            <th class="right" style="width:8.5%;">Gestora 12.71%</th>
            <th class="right" style="width:9.5%;">Líquido Pagable</th>
            <th style="width:7%;">Firma / Abono</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $idx => $item)
        <tr>
            <td class="center bold">{{ $item['item'] ?? ($idx + 1) }}</td>
            <td class="center">{{ $item['ci'] ?? '-' }}</td>
            <td>
                <strong>{{ $item['funcionario'] }}</strong>
            </td>
            <td>
                <span class="cargo-text">{{ $item['cargo'] ?? 'Personal' }}</span>
            </td>
            <td class="center">{{ $item['dias_trabajados'] ?? 30 }}</td>
            <td class="right">{{ number_format((float)$item['haber_basico'], 2) }}</td>
            <td class="center">{{ number_format((float)($item['porcentaje_bono'] ?? 0), 0) }}%</td>
            <td class="right">{{ number_format((float)($item['bono_antiguedad'] ?? 0), 2) }}</td>
            <td class="right bold" style="color:#0f2942;">{{ number_format((float)$item['total_ganado'], 2) }}</td>
            <td class="right bold" style="color:#b91c1c;">{{ number_format((float)$item['gestora_12_71'], 2) }}</td>
            <td class="right bold" style="color:#047857;">{{ number_format((float)$item['liquido_salarial'], 2) }}</td>
            <td class="center">
                <div class="firma-box">Abono Bco. Unión</div>
            </td>
        </tr>
        @endforeach

        {{-- FILA DE TOTALES GENERALES --}}
        <tr class="total-row">
            <td colspan="5" style="text-align:right; padding-right:8px; font-weight:900;">
                TOTALES GENERALES DE PLANILLA (Bs.):
            </td>
            <td class="right bold">{{ number_format($totales['total_haber_basico'], 2) }}</td>
            <td class="center">—</td>
            <td class="right bold">{{ number_format($totales['total_bono'], 2) }}</td>
            <td class="right bold amount-ganado">Bs. {{ number_format($totales['total_ganado'], 2) }}</td>
            <td class="right bold amount-gestora">Bs. {{ number_format($totales['total_gestora'], 2) }}</td>
            <td class="right bold amount-liquido">Bs. {{ number_format($totales['total_liquido'], 2) }}</td>
            <td class="center" style="font-size:7px; color:#cbd5e1;">CONFORME</td>
        </tr>
    </tbody>
</table>

{{-- ── RESUMEN EJECUTIVO / PRESUPUESTARIO Y APORTES PATRONALES ── --}}
<table class="summary-wrapper">
    <tr>
        {{-- COLUMNA 1: RESUMEN DE DESEMBOLSO --}}
        <td class="col-half" style="padding-left:0;">
            <div class="summary-card">
                <div class="summary-header">1. Resumen de Desembolso Salarial y Retenciones</div>
                <table class="summary-table">
                    <tr>
                        <td class="lbl">Total Haber Básico Planilla:</td>
                        <td class="val">Bs. {{ number_format($totales['total_haber_basico'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">(+) Total Bono de Antigüedad Consolidado:</td>
                        <td class="val">Bs. {{ number_format($totales['total_bono'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl"><strong>(=) TOTAL GANADO BRUTO IMPONIBLE:</strong></td>
                        <td class="val"><strong>Bs. {{ number_format($totales['total_ganado'], 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td class="lbl">(-) Aporte Laboral Gestora Pública (12.71%):</td>
                        <td class="val" style="color:#b91c1c;">- Bs. {{ number_format($totales['total_gestora'], 2) }}</td>
                    </tr>
                    <tr class="summary-highlight">
                        <td><strong>LÍQUIDO PAGABLE TOTAL A DESEMBOLSAR:</strong></td>
                        <td style="text-align:right; color:#047857; font-size:10px;">
                            <strong>Bs. {{ number_format($totales['total_liquido'], 2) }}</strong>
                        </td>
                    </tr>
                </table>
                <div class="literal-badge">
                    <strong>SON:</strong> {{ $total_liquido_literal ?? 'MONTO EN BOLIVIANOS' }}
                </div>
            </div>
        </td>

        {{-- COLUMNA 2: APORTES PATRONALES (17.21%) --}}
        <td class="col-half" style="padding-right:0;">
            <div class="summary-card">
                <div class="summary-header">2. Aportes Patronales de Ley (Costo Entidad - 17.21%)</div>
                <table class="summary-table">
                    <tr>
                        <td class="lbl">Caja Nacional de Salud (C.N.S. - 10.00%):</td>
                        <td class="val">Bs. {{ number_format($patronal['cns_10_bs'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Riesgo Profesional Patronal (Gestora - 1.71%):</td>
                        <td class="val">Bs. {{ number_format($patronal['riesgo_profesional_1_71_bs'] ?? round($totales['total_ganado'] * 0.0171, 2), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Aporte Patronal Solidario (Gestora - 3.00%):</td>
                        <td class="val">Bs. {{ number_format($patronal['solidario_patronal_3_bs'] ?? round($totales['total_ganado'] * 0.03, 2), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Aporte Patronal Pro-Vivienda (2.00%):</td>
                        <td class="val">Bs. {{ number_format($patronal['pro_vivienda_2_bs'] ?? round($totales['total_ganado'] * 0.02, 2), 2) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Comisión Gestora Patronal (0.50%):</td>
                        <td class="val">Bs. {{ number_format($patronal['comision_patronal_0_5_bs'] ?? round($totales['total_ganado'] * 0.005, 2), 2) }}</td>
                    </tr>
                    <tr class="summary-highlight">
                        <td><strong>TOTAL APORTES PATRONALES (17.21%):</strong></td>
                        <td style="text-align:right; color:#0284c7; font-size:9px;">
                            <strong>Bs. {{ number_format($patronal['total_patronal_bs'] ?? round($totales['total_ganado'] * 0.1721, 2), 2) }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl" style="padding-top:3px;"><strong>COSTO TOTAL MENSUAL PARA LA EMPRESA:</strong></td>
                        <td class="val" style="padding-top:3px; color:#0f2942; font-size:9px;">
                            <strong>Bs. {{ number_format($patronal['costo_total_empresa_bs'] ?? round($totales['total_ganado'] * 1.1721, 2), 2) }}</strong>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

{{-- ── BLOQUE DE FIRMAS Y CONFORMIDAD INSTITUCIONAL ── --}}
<table class="signatures-table">
    <tr>
        <td>
            <div class="sign-box">
                <div class="sign-seal-space"></div>
                <div class="sign-line"></div>
                <div class="sign-title">ELABORADO POR</div>
                <div class="sign-role">Responsable de Recursos Humanos y Planillas</div>
                <div class="sign-badge">EMAPAP Patacamaya</div>
            </div>
        </td>
        <td>
            <div class="sign-box">
                <div class="sign-seal-space"></div>
                <div class="sign-line"></div>
                <div class="sign-title">REVISADO Y VERIFICADO</div>
                <div class="sign-role">Dirección Administrativa Financiera (DAF)</div>
                <div class="sign-badge">Control Presupuestario y Financiero</div>
            </div>
        </td>
        <td>
            <div class="sign-box">
                <div class="sign-seal-space"></div>
                <div class="sign-line"></div>
                <div class="sign-title">APROBADO Y AUTORIZADO</div>
                <div class="sign-role">Gerencia General</div>
                <div class="sign-badge">Autorización de Desembolso Oficial</div>
            </div>
        </td>
    </tr>
</table>

{{-- ── PIE INSTITUCIONAL Y SEGURIDAD ── --}}
<table class="footer-table">
    <tr>
        <td style="width:35%;">
            Sistema Integrado EMAPAP Patacamaya · Módulo de Recursos Humanos
        </td>
        <td style="width:35%; text-align:center;">
            @if($es_declarada)
                Planilla Oficial Declarada · Respaldada por la Ley General del Trabajo
            @else
                Documento de Trabajo / Borrador Interno de Simulación Salarial
            @endif
        </td>
        <td style="width:30%; text-align:right;">
            CITE: {{ $cite }} &nbsp;•&nbsp; Fecha Emisión: {{ $fecha_emision }}
        </td>
    </tr>
</table>

</body>
</html>
