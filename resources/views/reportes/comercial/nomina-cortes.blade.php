<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla de Cortes Masivos por Mora - EMAPAP</title>
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
            border-bottom: 2px solid #C62828;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h1 {
            font-size: 14px;
            margin: 0;
            color: #B71C1C;
            text-transform: uppercase;
        }
        .header-title h2 {
            font-size: 11px;
            margin: 2px 0 0 0;
            color: #C62828;
        }
        .badge-mora {
            background-color: #FFEBEE;
            border: 1px solid #C62828;
            border-radius: 4px;
            padding: 4px 8px;
            font-weight: bold;
            color: #B71C1C;
            text-align: right;
            font-size: 10px;
        }
        .info-bar {
            width: 100%;
            margin-bottom: 8px;
            font-size: 9px;
            background-color: #FFF3E0;
            padding: 4px 6px;
            border-radius: 4px;
            border: 1px solid #FFE0B2;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #C62828;
            color: white;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 3px;
            border: 1px solid #B71C1C;
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
            border: 1px dashed #B71C1C;
            border-radius: 2px;
        }
        .data-table tfoot td {
            background-color: #FFEBEE;
            font-weight: bold;
            border-top: 2px solid #C62828;
            padding: 6px 4px;
            font-size: 9px;
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
                <strong style="font-size: 11px; color: #B71C1C;">{{ $empresa?->razon_social ?? 'EMAPAP' }}</strong><br>
                <span style="font-size: 8px; color: #666;">Operaciones Técnicas y Cortes</span><br>
                <span style="font-size: 8px; color: #666;">Servicio de Agua Potable y Alcantarillado</span>
            </td>
            <td style="width: 50%; vertical-align: top;" class="header-title">
                <h1>PLANILLA DE CORTES MASIVOS POR MOROSIDAD</h1>
                <h2>NÓMINA DE INTERVENCIÓN TÉCNICA (DEUDA &ge; {{ $mesesMoraMin }} MESES)</h2>
            </td>
            <td style="width: 25%; vertical-align: top; text-align: right;">
                <div class="badge-mora">
                    ZONA: {{ $zonaNombre }}<br>
                    <span style="font-size: 8px; font-weight: normal; color: #333;">
                        Abonados en Mora: {{ $abonados->count() }}<br>
                        Deuda Total: Bs {{ number_format($totalDeuda, 2) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-bar">
        <tr>
            <td style="width: 33%;"><strong>Sector / Zona:</strong> {{ $zonaNombre }}</td>
            <td style="width: 33%; text-align: center;"><strong>Criterio:</strong> Abonados con {{ $mesesMoraMin }} o más facturas impagas</td>
            <td style="width: 33%; text-align: right;"><strong>Fecha de Generación:</strong> {{ $fechaImpresion }}</td>
        </tr>
    </table>

    <!-- Tabla de Abonados a Cortar -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">N°</th>
                <th style="width: 50px;">Código</th>
                <th style="width: 140px;">Titular / Abonado</th>
                <th style="width: 130px;">Dirección / Referencia</th>
                <th style="width: 60px;">Categoría</th>
                <th style="width: 65px;">N° Medidor</th>
                <th style="width: 45px;">Meses</th>
                <th style="width: 70px;">Deuda (Bs)</th>
                <th style="width: 75px;">Lect. Retiro</th>
                <th style="width: 75px;">N° Precinto</th>
                <th style="width: 70px;">Fecha/Hora</th>
                <th style="width: 65px;">Firma Técnico</th>
            </tr>
        </thead>
        <tbody>
            @forelse($abonados as $idx => $a)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center text-bold">{{ $a->codigo }}</td>
                    <td>{{ $a->nombre_completo }}</td>
                    <td>
                        {{ $a->calle?->nombre ?? 'Calle Principal' }}
                        @if($a->numero_vivienda) #{{ $a->numero_vivienda }} @endif
                    </td>
                    <td class="text-center">{{ $a->categoria?->nombre ?? 'DOMESTICO' }}</td>
                    <td class="text-center text-bold">{{ $a->medidorActual?->numero_serie ?? $a->numero_medidor ?? 'S/M' }}</td>
                    <td class="text-center text-bold" style="color: #B71C1C;">{{ $a->meses_mora }}</td>
                    <td class="text-right text-bold" style="color: #B71C1C;">{{ number_format((float) $a->saldo_deuda, 2) }}</td>
                    <td><div class="field-box"></div></td>
                    <td><div class="field-box"></div></td>
                    <td><div class="field-box"></div></td>
                    <td><div class="field-box"></div></td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 15px; color: #777;">
                        No existen abonados en mora que cumplan el criterio de corte en esta zona.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($abonados->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="6" class="text-bold" style="text-align: right;">TOTALES DEUDORES:</td>
                    <td class="text-center text-bold">{{ $abonados->count() }} Abonados</td>
                    <td class="text-right text-bold" style="color: #B71C1C;">Bs {{ number_format($totalDeuda, 2) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Firmas -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-line">
                    JEFE DE CUADRILLA TÉCNICA<br>
                    Nombre y Firma
                </div>
            </td>
            <td>
                <div class="signature-line">
                    SUPERVISOR DE OPERACIONES<br>
                    Firma y Sello
                </div>
            </td>
            <td>
                <div class="signature-line">
                    JEFE COMERCIAL Y COBRANZAS<br>
                    Autorizado por
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Planilla operativa de corte de servicio — Impreso por: {{ $generadoPor }} — Fecha: {{ $fechaImpresion }}
    </div>

</body>
</html>
