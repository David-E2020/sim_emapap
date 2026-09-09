<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante Contable {{ $comprobante->numero_comprobante }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #00897b;
            padding-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .empresa-title {
            font-size: 13pt;
            font-weight: bold;
            color: #00695c;
            text-transform: uppercase;
        }
        .empresa-sub {
            font-size: 7.5pt;
            color: #64748b;
        }
        .doc-badge {
            text-align: right;
        }
        .comp-title {
            font-size: 14pt;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .comp-num {
            font-size: 11pt;
            font-weight: bold;
            color: #00897b;
        }
        .info-box {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .info-box td {
            padding: 5px 8px;
            font-size: 8.5pt;
        }
        .info-label {
            font-weight: bold;
            color: #475569;
            width: 15%;
            background-color: #f8fafc;
        }
        .table-detalles {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table-detalles th {
            background-color: #00695c;
            color: #ffffff;
            font-size: 8pt;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
        }
        .table-detalles td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8pt;
        }
        .table-detalles tfoot td {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 2px solid #cbd5e1;
            padding: 6px 8px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .firmas-table {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
        }
        .firmas-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 10px;
        }
        .linea-firma {
            border-top: 1px solid #334155;
            margin-bottom: 5px;
        }
        .firma-cargo {
            font-weight: bold;
            font-size: 8pt;
            color: #0f172a;
        }
        .firma-nombre {
            font-size: 7.5pt;
            color: #64748b;
        }
        .watermark-anulado {
            color: #ef4444;
            font-size: 28pt;
            font-weight: bold;
            text-align: center;
            border: 2px dashed #ef4444;
            padding: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    @if($comprobante->estado === 'ANULADO')
        <div class="watermark-anulado">*** COMPROBANTE ANULADO ***</div>
    @endif

    <!-- CABECERA -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="empresa-title">EMAPAP - PATACAMAYA</div>
                <div class="empresa-sub">Empresa Municipal de Agua Potable y Alcantarillado Patacamaya</div>
                <div class="empresa-sub">NIT: 1028475029 | La Paz - Bolivia</div>
                <div class="empresa-sub">Sistema de Contabilidad Integrada (Ley N° 1178 SAFCO)</div>
            </td>
            <td class="doc-badge" style="width: 40%;">
                <div class="comp-title">COMPROBANTE DE {{ $comprobante->tipo }}</div>
                <div class="comp-num">{{ $comprobante->numero_comprobante }}</div>
                <div style="font-size: 8pt; color: #64748b;">Gestión: {{ $comprobante->gestion ? $comprobante->gestion->gestion : date('Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- DATOS GENERALES -->
    <table class="info-box">
        <tr>
            <td class="info-label">Fecha:</td>
            <td style="width: 35%;">{{ \Carbon\Carbon::parse($comprobante->fecha)->format('d/m/Y') }}</td>
            <td class="info-label">Tipo Cambio:</td>
            <td style="width: 35%;">6.96 Bs/USD</td>
        </tr>
        <tr>
            <td class="info-label">Beneficiario:</td>
            <td>{{ $comprobante->beneficiario ?: 'EMAPAP Patacamaya' }}</td>
            <td class="info-label">Doc. Respaldo:</td>
            <td>{{ $comprobante->tipo_documento_respaldo ?: '-' }} #{{ $comprobante->numero_documento_respaldo ?: '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Concepto / Glosa:</td>
            <td colspan="3" style="font-style: italic;">{{ $comprobante->glosa_principal }}</td>
        </tr>
    </table>

    <!-- TABLA DE DETALLES DEL ASIENTO -->
    <table class="table-detalles">
        <thead>
            <tr>
                <th style="width: 14%;">Código</th>
                <th style="width: 36%;">Cuenta Contable</th>
                <th style="width: 20%;">Centro de Costo / Glosa</th>
                <th style="width: 15%; text-align: right;">Debe (Bs)</th>
                <th style="width: 15%; text-align: right;">Haber (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comprobante->detalles as $d)
                <tr>
                    <td style="font-family: monospace; font-weight: bold;">{{ $d->cuenta ? $d->cuenta->codigo : '-' }}</td>
                    <td>
                        <strong>{{ $d->cuenta ? $d->cuenta->nombre : '-' }}</strong>
                        @if($d->glosa_linea && $d->glosa_linea !== $comprobante->glosa_principal)
                            <div style="font-size: 7pt; color: #64748b;">{{ $d->glosa_linea }}</div>
                        @endif
                    </td>
                    <td style="font-size: 7.5pt; color: #475569;">
                        {{ $d->centroCosto ? $d->centroCosto->nombre : '-' }}
                    </td>
                    <td class="text-right font-weight-bold">
                        {{ $d->debe > 0 ? number_format($d->debe, 2, '.', ',') : '-' }}
                    </td>
                    <td class="text-right font-weight-bold">
                        {{ $d->haber > 0 ? number_format($d->haber, 2, '.', ',') : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right; text-transform: uppercase;">TOTALES GENERALES (Bs):</td>
                <td class="text-right">{{ number_format($comprobante->total_debe, 2, '.', ',') }}</td>
                <td class="text-right">{{ number_format($comprobante->total_haber, 2, '.', ',') }}</td>
            </tr>
            @if($comprobante->diferencia > 0)
                <tr style="color: #b91c1c;">
                    <td colspan="3" style="text-align: right;">DIFERENCIA / DESBALANCE:</td>
                    <td colspan="2" class="text-right font-weight-bold">Bs {{ number_format($comprobante->diferencia, 2, '.', ',') }}</td>
                </tr>
            @endif
        </tfoot>
    </table>

    <div style="font-size: 7.5pt; color: #64748b; margin-top: 5px;">
        <strong>Son:</strong> {{ $literal ?? \App\Services\Contabilidad\ReporteFinancieroPdfService::convertirNumeroALetras($comprobante->total_debe) }}
    </div>

    <!-- CUADRO DE FIRMAS REGLAMENTARIAS SAFCO -->
    <table class="firmas-table">
        <tr>
            <td>
                <div class="linea-firma"></div>
                <div class="firma-cargo">ELABORADO POR</div>
                <div class="firma-nombre">{{ $comprobante->usuarioElaboracion ? $comprobante->usuarioElaboracion->name : 'Contabilidad / Finanzas' }}</div>
                <div class="firma-nombre">Operador Contable</div>
            </td>
            <td>
                <div class="linea-firma"></div>
                <div class="firma-cargo">REVISADO POR</div>
                <div class="firma-nombre">{{ $comprobante->usuarioAprobacion ? $comprobante->usuarioAprobacion->name : 'Auditoría Interna' }}</div>
                <div class="firma-nombre">Jefe Administrativo Financiero</div>
            </td>
            <td>
                <div class="linea-firma"></div>
                <div class="firma-cargo">APROBADO POR</div>
                <div class="firma-nombre">Gerencia General</div>
                <div class="firma-nombre">EMAPAP Patacamaya</div>
            </td>
        </tr>
    </table>

</body>
</html>
