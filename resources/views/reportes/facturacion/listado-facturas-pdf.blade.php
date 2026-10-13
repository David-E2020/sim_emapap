<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla Oficial de Facturas Emitidas - EMAPAP</title>
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
            border-bottom: 2px solid #00695c;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h1 {
            font-size: 14px;
            margin: 0;
            color: #004d40;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title h2 {
            font-size: 11px;
            margin: 2px 0 0 0;
            color: #00796b;
            font-weight: bold;
        }
        .header-title p {
            margin: 2px 0 0 0;
            font-size: 8.5px;
            color: #555;
        }
        .periodo-badge {
            background-color: #e0f2f1;
            border: 1px solid #00796b;
            border-radius: 4px;
            padding: 4px 8px;
            font-weight: bold;
            color: #004d40;
            text-align: right;
            font-size: 9px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .kpi-card {
            border: 1px solid #b2dfdb;
            border-radius: 4px;
            padding: 4px 6px;
            text-align: center;
            background-color: #fcfdfe;
        }
        .kpi-title {
            font-size: 7.5px;
            text-transform: uppercase;
            font-weight: bold;
            color: #546e7a;
            margin-bottom: 2px;
        }
        .kpi-value {
            font-size: 12px;
            font-weight: bold;
            color: #004d40;
        }
        .kpi-value.success {
            color: #2e7d32;
        }
        .kpi-value.danger {
            color: #c62828;
        }
        .kpi-value.warning {
            color: #f57f17;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .data-table th {
            background-color: #eceff1;
            padding: 4px 5px;
            text-align: left;
            font-size: 8px;
            border: 1px solid #cfd8dc;
            color: #37474f;
            font-weight: bold;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 3px 5px;
            border: 1px solid #e0e0e0;
            font-size: 8px;
        }
        .data-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .data-table tfoot td {
            font-weight: bold;
            background-color: #f5f5f5;
            border-top: 2px solid #90a4ae;
            border-bottom: 2px solid #90a4ae;
            font-size: 8.5px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }
        .badge-success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .badge-warning { background-color: #fff8e1; color: #f57f17; border: 1px solid #ffe082; }
        .badge-danger { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .badge-info { background-color: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
        .signatures-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .signatures-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 15px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>

    <!-- CABECERA INSTITUCIONAL -->
    <table class="header-table">
        <tr>
            <td style="width: 22%; vertical-align: middle;">
                <div style="font-weight: bold; font-size: 13px; color: #004d40;">EMAPAP</div>
                <div style="font-size: 8px; color: #666;">Sistema SIAT - Facturación Electrónica</div>
                <div style="font-size: 8px; color: #666;">NIT: {{ $empresa->nit ?? '384910023' }}</div>
            </td>
            <td style="width: 53%;" class="header-title">
                <h1>{{ $empresa->razon_social ?? 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO DE POOPÓ' }}</h1>
                <h2>PLANILLA OFICIAL DE FACTURAS EMITIDAS</h2>
                <p>
                    @if(!empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin']))
                        RANGO: <strong>{{ $filtros['fecha_inicio'] ?? 'Inicio' }}</strong> al <strong>{{ $filtros['fecha_fin'] ?? 'Hoy' }}</strong>
                    @else
                        PERÍODO: <strong>Histórico General</strong>
                    @endif
                    @if(!empty($filtros['estado']) && $filtros['estado'] !== 'TODOS')
                        | Estado: <strong>{{ $filtros['estado'] }}</strong>
                    @endif
                    @if(!empty($filtros['search']))
                        | Criterio: <strong>{{ $filtros['search'] }}</strong>
                    @endif
                </p>
            </td>
            <td style="width: 25%; vertical-align: middle;">
                <div class="periodo-badge">
                    <div>FECHA DE REPORTE</div>
                    <div style="font-size: 10px; margin-top: 2px;">{{ $fechaImpresion }}</div>
                    <div style="font-size: 7.5px; color: #00796b; margin-top: 1px;">Usuario: {{ $generadoPor }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- TARJETAS DE RESUMEN KPI -->
    <table class="kpi-table">
        <tr>
            <td style="width: 14%; padding-right: 3px;">
                <div class="kpi-card">
                    <div class="kpi-title">Total Facturas</div>
                    <div class="kpi-value">{{ count($facturas) }}</div>
                </div>
            </td>
            <td style="width: 14%; padding: 0 3px;">
                <div class="kpi-card">
                    <div class="kpi-title">Validadas SIN</div>
                    <div class="kpi-value success">{{ $totalValidadas }}</div>
                </div>
            </td>
            <td style="width: 14%; padding: 0 3px;">
                <div class="kpi-card">
                    <div class="kpi-title">Contingencia</div>
                    <div class="kpi-value warning">{{ $totalContingencia }}</div>
                </div>
            </td>
            <td style="width: 14%; padding: 0 3px;">
                <div class="kpi-card">
                    <div class="kpi-title">Anuladas</div>
                    <div class="kpi-value danger">{{ $totalAnuladas }}</div>
                </div>
            </td>
            <td style="width: 15%; padding: 0 3px;">
                <div class="kpi-card">
                    <div class="kpi-title">Total Venta</div>
                    <div class="kpi-value success">Bs {{ number_format($montoTotalValidadas, 2) }}</div>
                </div>
            </td>
            <td style="width: 14%; padding: 0 3px;">
                <div class="kpi-card">
                    <div class="kpi-title">Tasas No IVA</div>
                    <div class="kpi-value" style="color: #0277bd;">Bs {{ number_format($totalTasasValidas ?? 0, 2) }}</div>
                </div>
            </td>
            <td style="width: 15%; padding-left: 3px;">
                <div class="kpi-card">
                    <div class="kpi-title">Base Débito (13%)</div>
                    <div class="kpi-value success">Bs {{ number_format($totalBaseDebitoValidas ?? 0, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    @if(!empty($totalDuplicadas) && $totalDuplicadas > 0)
        <div style="background-color: #ffebee; border: 1px solid #ef5350; border-left: 4px solid #c62828; padding: 5px 8px; margin-bottom: 6px; border-radius: 3px; font-size: 8.5px; color: #b71c1c;">
            <strong>⚠ ALERTA FISCAL:</strong> Se han detectado <strong>{{ $totalDuplicadas }} registros de facturas duplicadas</strong> en esta planilla. Las filas señaladas con la etiqueta <strong>[⚠ DUPLICADA]</strong> corresponden a números de factura correlativos repetidos en la gestión y deben ser auditadas para anulación manual por el operador.
        </div>
    @endif

    @if(isset($totalRegistrosEnBd) && $totalRegistrosEnBd > count($facturas))
        <div style="background-color: #fff8e1; border: 1px solid #ffe082; border-left: 4px solid #f57f17; padding: 4px 6px; margin-bottom: 6px; border-radius: 3px; font-size: 8px; color: #5d4037;">
            <strong>Nota de Paginación:</strong> Mostrando las primeras {{ number_format(count($facturas)) }} facturas de un total de {{ number_format($totalRegistrosEnBd) }} registros en base de datos. Para volúmenes históricos masivos sin límite de páginas, utilice la exportación oficial a <strong>Excel (.xlsx)</strong>.
        </div>
    @endif

    <!-- TABLA DE DOCUMENTOS CON DESGLOSE TRIBUTARIO -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22px;" class="text-center">#</th>
                <th style="width: 75px;">N° FACTURA</th>
                <th style="width: 75px;">FECHA EMISIÓN</th>
                <th style="width: 45px;" class="text-center">ABONADO</th>
                <th style="width: 60px;">DOC / NIT</th>
                <th>CLIENTE / RAZÓN SOCIAL</th>
                <th style="width: 55px;" class="text-center">ESTADO SIN</th>
                <th style="width: 55px;" class="text-right">TOTAL (BS)</th>
                <th style="width: 50px;" class="text-right">TASAS NO IVA</th>
                <th style="width: 55px;" class="text-right">BASE DÉBITO</th>
                <th style="width: 50px;" class="text-right">IVA 13%</th>
                <th style="width: 100px;">C.U.F.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($facturas as $index => $f)
                @php
                    $esAnulada = in_array($f->estado_factura, ['ANULADA', 'CANCELLED'], true);
                    $esContingencia = in_array($f->estado_factura, ['CONTINGENCIA', 'OFFLINE_PENDIENTE', 'OFFLINE_REGULARIZADA'], true) || (int) $f->tipo_emision === 2;
                    $esDup = (bool) ($f->es_duplicada ?? false);
                    $badgeClass = 'badge-success';
                    if ($esAnulada) {
                        $badgeClass = 'badge-danger';
                    } elseif ($esContingencia) {
                        $badgeClass = 'badge-warning';
                    } elseif ($f->estado_factura === 'OBSERVADA') {
                        $badgeClass = 'badge-danger';
                    }
                    $cufTruncado = $f->cuf ? (substr($f->cuf, 0, 8) . '...' . substr($f->cuf, -6)) : '-';

                    $tot = $esAnulada ? 0.00 : (float) $f->monto_total;
                    $base = $esAnulada ? 0.00 : (float) $f->monto_total_sujeto_iva;
                    $desc = $esAnulada ? 0.00 : (float) $f->monto_descuento;
                    $tasas = $esAnulada ? 0.00 : max(0.00, round($tot - $base - $desc, 2));
                    $debito = round($base * 0.13, 2);
                @endphp
                <tr style="{{ $esDup ? 'background-color: #fff0f0;' : '' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold" style="{{ $esDup ? 'color: #c62828;' : 'color: #004d40;' }}">
                        #{{ $f->numero_factura }}
                        @if($esDup)
                            <span class="badge badge-danger" style="margin-left: 2px;">⚠ DUP</span>
                        @endif
                    </td>
                    <td>{{ $f->fecha_emision ? \Carbon\Carbon::parse($f->fecha_emision)->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-center font-bold">
                        {{ $f->abonado ? $f->abonado->codigo : '-' }}
                    </td>
                    <td>{{ $f->numero_documento }}{{ $f->complemento ? '-' . $f->complemento : '' }}</td>
                    <td class="font-bold">{{ $f->nombre_razon_social }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $f->estado_factura }}</span>
                        @if($esDup)
                            <span class="badge badge-danger" style="display: block; margin-top: 1px; font-size: 6.5px;">DUPLICADA</span>
                        @endif
                    </td>
                    <td class="text-right font-bold" style="{{ $esAnulada ? 'color: #999; text-decoration: line-through;' : '' }}">
                        {{ number_format($tot, 2) }}
                    </td>
                    <td class="text-right" style="color: #0277bd;">
                        {{ number_format($tasas, 2) }}
                    </td>
                    <td class="text-right font-bold" style="color: #2e7d32;">
                        {{ number_format($base, 2) }}
                    </td>
                    <td class="text-right font-bold" style="color: #4527a0;">
                        {{ number_format($debito, 2) }}
                    </td>
                    <td style="font-family: monospace; font-size: 6.5px; color: #555;">{{ $cufTruncado }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 15px; color: #666;">
                        No se encontraron facturas con los criterios de búsqueda seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right font-bold">TOTALES VÁLIDOS COMPUTADOS (BS):</td>
                <td class="text-right font-bold" style="color: #004d40; font-size: 8.5px;">
                    {{ number_format($montoTotalValidadas, 2) }}
                </td>
                <td class="text-right font-bold" style="color: #0277bd; font-size: 8.5px;">
                    {{ number_format($totalTasasValidas ?? 0, 2) }}
                </td>
                <td class="text-right font-bold" style="color: #2e7d32; font-size: 8.5px;">
                    {{ number_format($totalBaseDebitoValidas ?? 0, 2) }}
                </td>
                <td class="text-right font-bold" style="color: #4527a0; font-size: 8.5px;">
                    {{ number_format($totalDebitoValidas ?? 0, 2) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- PIE DE FIRMAS -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div style="font-weight: bold;">Operador de Caja / Facturación</div>
                <div style="color: #666;">Firma y Sello</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div style="font-weight: bold;">Responsable Comercial SIAT</div>
                <div style="color: #666;">Firma y Sello</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div style="font-weight: bold;">V°B° Administración / Gerencia</div>
                <div style="color: #666;">EMAPAP</div>
            </td>
        </tr>
    </table>

</body>
</html>
