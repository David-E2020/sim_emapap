<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla de Campo - Toma de Lecturas</title>
    <style>
        @page {
            margin: 8mm;
            size: letter landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #222;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #1976D2;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h1 {
            font-size: 14px;
            margin: 0;
            color: #0D47A1;
            text-transform: uppercase;
        }
        .header-title h2 {
            font-size: 11px;
            margin: 2px 0 0 0;
            color: #1976D2;
        }
        .badge-periodo {
            background-color: #E3F2FD;
            border: 1px solid #1976D2;
            border-radius: 4px;
            padding: 4px 8px;
            font-weight: bold;
            color: #0D47A1;
            text-align: right;
            font-size: 10px;
        }
        .info-bar {
            width: 100%;
            margin-bottom: 8px;
            font-size: 9px;
            background-color: #F5F5F5;
            padding: 4px 6px;
            border-radius: 4px;
            border: 1px solid #E0E0E0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #1976D2;
            color: white;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 3px;
            border: 1px solid #0D47A1;
            text-align: center;
        }
        .data-table td {
            border: 1px solid #CCCCCC;
            padding: 4px 3px;
            vertical-align: middle;
            font-size: 8.5px;
        }
        .data-table tr:nth-child(even) {
            background-color: #FAFAFA;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .field-box {
            height: 18px;
            background-color: #FFFFFF;
            border: 1px dashed #9E9E9E;
            border-radius: 2px;
        }
        .ciegas-tag {
            color: #757575;
            font-style: italic;
            font-size: 8px;
        }
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
                <strong style="font-size: 11px; color: #0D47A1;">{{ $empresa?->razon_social ?? 'EMAPAP' }}</strong><br>
                <span style="font-size: 8px; color: #666;">Sistema Comercial Integrado</span><br>
                <span style="font-size: 8px; color: #666;">Catastro y Facturación de Agua</span>
            </td>
            <td style="width: 50%; vertical-align: top;" class="header-title">
                <h1>PLANILLA DE CAMPO - TOMA DE LECTURAS</h1>
                <h2>{{ $zonaNombre }} {{ $aCiegas ? '(MODALIDAD A CIEGAS)' : '(MODALIDAD CONTROLADA)' }}</h2>
            </td>
            <td style="width: 25%; vertical-align: top; text-align: right;">
                <div class="badge-periodo">
                    PERÍODO: {{ $periodo->periodo }}<br>
                    <span style="font-size: 8px; font-weight: normal; color: #333;">
                        Desde: {{ $periodo->fecha_inicio_consumo?->format('d/m/Y') }}<br>
                        Hasta: {{ $periodo->fecha_fin_consumo?->format('d/m/Y') }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-bar">
        <tr>
            <td style="width: 33%;"><strong>Zona:</strong> {{ $zonaNombre }}</td>
            <td style="width: 33%; text-align: center;"><strong>Total Abonados Asignados:</strong> {{ $lecturas->count() }}</td>
            <td style="width: 33%; text-align: right;"><strong>Fecha de Emisión:</strong> {{ $fechaImpresion }}</td>
        </tr>
    </table>

    <!-- Tabla de Abonados y Captura de Lectura -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">N°</th>
                <th style="width: 55px;">Código</th>
                <th style="width: 140px;">Titular / Abonado</th>
                <th style="width: 140px;">Dirección / Calle</th>
                <th style="width: 65px;">Categoría</th>
                <th style="width: 70px;">N° Medidor</th>
                <th style="width: 65px;">Lect. Ant. (m³)</th>
                <th style="width: 75px;">Lect. Actual (m³)</th>
                <th style="width: 90px;">Novedad / Obs.</th>
                <th style="width: 55px;">Firma V°B°</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lecturas as $idx => $l)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center text-bold">{{ $l->abonado?->codigo ?? 'S/C' }}</td>
                    <td>{{ $l->abonado?->nombre_completo ?? 'Sin Nombre' }}</td>
                    <td>
                        {{ $l->abonado?->calle?->nombre ?? 'Calle Principal' }}
                        @if($l->abonado?->numero_vivienda) #{{ $l->abonado->numero_vivienda }} @endif
                    </td>
                    <td class="text-center">{{ $l->abonado?->categoria?->nombre ?? 'DOMESTICO' }}</td>
                    <td class="text-center text-bold">{{ $l->medidor?->numero_serie ?? $l->abonado?->numero_medidor ?? 'S/M' }}</td>
                    <td class="text-center">
                        @if($aCiegas)
                            <span class="ciegas-tag">[A CIEGAS]</span>
                        @else
                            <strong>{{ number_format((float) $l->lectura_anterior, 0) }}</strong>
                        @endif
                    </td>
                    <td>
                        <div class="field-box"></div>
                    </td>
                    <td>
                        <div class="field-box"></div>
                    </td>
                    <td>
                        <div class="field-box"></div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px; color: #777;">
                        No existen abonados registrados para la toma de lecturas en este período y filtro.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Firmas -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-line">
                    LECTURADOR DE CAMPO<br>
                    Nombre y Firma
                </div>
            </td>
            <td>
                <div class="signature-line">
                    SUPERVISOR DE REDES Y CATASTRO<br>
                    Firma y Sello
                </div>
            </td>
            <td>
                <div class="signature-line">
                    RESPONSABLE COMERCIAL<br>
                    Vo.Bo. Recepción
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Planilla oficial para cuadrilla de campo — Impreso por: {{ $generadoPor }} — Fecha: {{ $fechaImpresion }}
    </div>

</body>
</html>
