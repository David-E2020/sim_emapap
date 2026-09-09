<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla Consolidada de Recaudación - EMAPAP</title>
    <style>
        @page {
            margin: 8mm;
            size: letter landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
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
            font-size: 15px;
            margin: 0;
            color: #004d40;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title h2 {
            font-size: 12px;
            margin: 2px 0 0 0;
            color: #00796b;
            font-weight: bold;
        }
        .header-title p {
            margin: 2px 0 0 0;
            font-size: 9px;
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
            font-size: 10px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .kpi-card {
            border: 1px solid #b2dfdb;
            border-radius: 4px;
            padding: 5px 8px;
            text-align: center;
            background-color: #fcfdfe;
        }
        .kpi-title {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            color: #546e7a;
            margin-bottom: 2px;
        }
        .kpi-value {
            font-size: 13px;
            font-weight: bold;
            color: #004d40;
        }
        .kpi-value.success {
            color: #2e7d32;
        }
        .kpi-value.primary {
            color: #1565c0;
        }
        .kpi-value.danger {
            color: #c62828;
        }
        .section-header {
            background-color: #00695c;
            color: #ffffff;
            padding: 3px 6px;
            font-weight: bold;
            font-size: 10px;
            border-radius: 3px 3px 0 0;
            margin-top: 6px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .data-table th {
            background-color: #eceff1;
            padding: 4px 6px;
            text-align: left;
            font-size: 9px;
            border: 1px solid #cfd8dc;
            color: #37474f;
            font-weight: bold;
        }
        .data-table td {
            padding: 3px 6px;
            border: 1px solid #e0e0e0;
            font-size: 9px;
        }
        .data-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .data-table tfoot td {
            font-weight: bold;
            background-color: #f5f5f5;
            border-top: 2px solid #90a4ae;
            border-bottom: 2px solid #90a4ae;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .badge-warning { background-color: #fff8e1; color: #f57f17; border: 1px solid #ffe082; }
        .badge-danger { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .badge-info { background-color: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
        .signatures-table {
            width: 100%;
            margin-top: 25px;
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
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

    <!-- CABECERA INSTITUCIONAL -->
    <table class="header-table">
        <tr>
            <td style="width: 20%; vertical-align: middle;">
                <div style="font-weight: bold; font-size: 13px; color: #004d40;">EMAPAP</div>
                <div style="font-size: 8px; color: #666;">Patacamaya - La Paz</div>
                <div style="font-size: 8px; color: #666;">NIT: {{ $empresa->nit ?? '384910023' }}</div>
            </td>
            <td style="width: 55%;" class="header-title">
                <h1>{{ $empresa->razon_social ?? 'EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO PATACAMAYA' }}</h1>
                <h2>PLANILLA CONSOLIDADA DE RECAUDACIÓN Y CUADRE DE CAJA</h2>
                <p>PERÍODO: <strong>{{ $datos['periodo']['etiqueta'] ?? 'Consolidado' }}</strong> | Generado por: {{ $generadoPor }} el {{ $fechaImpresion }}</p>
            </td>
            <td style="width: 25%; vertical-align: middle;">
                <div class="periodo-badge">
                    <div>PERÍODO AUDITADO</div>
                    <div style="font-size: 11px; margin-top: 2px;">{{ $datos['periodo']['desde'] }} al {{ $datos['periodo']['hasta'] }}</div>
                    <div style="font-size: 8px; color: #00796b; margin-top: 1px;">Modalidad: {{ strtoupper($datos['periodo']['tipo'] ?? 'Rango') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- TARJETAS RESUMEN DE RECAUDACIÓN (KPIS) -->
    <table class="kpi-table">
        <tr>
            <td style="padding: 2px;">
                <div class="kpi-card">
                    <div class="kpi-title">TOTAL RECAUDADO</div>
                    <div class="kpi-value success">Bs {{ number_format((float) ($datos['metricas']['total_recaudado'] ?? 0), 2) }}</div>
                    <div style="font-size: 8px; color: #666;">{{ $datos['metricas']['total_transacciones'] ?? 0 }} transacciones</div>
                </div>
            </td>
            <td style="padding: 2px;">
                <div class="kpi-card">
                    <div class="kpi-title">EFECTIVO FÍSICO GAVETA</div>
                    <div class="kpi-value">Bs {{ number_format((float) ($datos['metricas']['total_efectivo'] ?? 0), 2) }}</div>
                    <div style="font-size: 8px; color: #666;">Cobros en ventanilla</div>
                </div>
            </td>
            <td style="padding: 2px;">
                <div class="kpi-card">
                    <div class="kpi-title">COBROS QR / BANCO</div>
                    <div class="kpi-value primary">Bs {{ number_format((float) ($datos['metricas']['total_qr_banco'] ?? 0), 2) }}</div>
                    <div style="font-size: 8px; color: #666;">Directo a cuenta fiscal</div>
                </div>
            </td>
            <td style="padding: 2px;">
                <div class="kpi-card">
                    <div class="kpi-title">FONDO INICIAL CAJAS</div>
                    <div class="kpi-value">Bs {{ number_format((float) ($datos['metricas']['total_fondo_inicial'] ?? 0), 2) }}</div>
                    <div style="font-size: 8px; color: #666;">Sencillos de apertura</div>
                </div>
            </td>
            <td style="padding: 2px;">
                <div class="kpi-card">
                    <div class="kpi-title">CAJA CHICA (NETO)</div>
                    <div class="kpi-value">
                        Bs {{ number_format((float) (($datos['metricas']['total_ingresos_extra'] ?? 0) - ($datos['metricas']['total_egresos_extra'] ?? 0)), 2) }}
                    </div>
                    <div style="font-size: 8px; color: #666;">+{{ number_format((float) ($datos['metricas']['total_ingresos_extra'] ?? 0), 2) }} / -{{ number_format((float) ($datos['metricas']['total_egresos_extra'] ?? 0), 2) }}</div>
                </div>
            </td>
            <td style="padding: 2px;">
                <div class="kpi-card">
                    <div class="kpi-title">TOTAL FISICO DECLARADO</div>
                    <div class="kpi-value">Bs {{ number_format((float) ($datos['metricas']['total_declarado_fisico'] ?? 0), 2) }}</div>
                    <div style="font-size: 8px; color: #666;">En arqueos cerrados</div>
                </div>
            </td>
            <td style="padding: 2px;">
                @php
                    $dif = (float) ($datos['metricas']['diferencia_neta'] ?? 0);
                @endphp
                <div class="kpi-card" style="{{ $dif < 0 ? 'border-color:#ef9a9a; background-color:#ffebee;' : ($dif > 0 ? 'border-color:#ffe082; background-color:#fffde7;' : 'border-color:#a5d6a7; background-color:#f1f8e9;') }}">
                    <div class="kpi-title">DIFERENCIA ARQUEO</div>
                    <div class="kpi-value {{ $dif < 0 ? 'danger' : ($dif > 0 ? 'primary' : 'success') }}">
                        Bs {{ number_format($dif, 2) }}
                    </div>
                    <div style="font-size: 8px; font-weight: bold;">
                        {{ $dif == 0 ? 'CUADRADO' : ($dif > 0 ? 'SOBRANTE' : 'FALTANTE') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <!-- COLUMNA 1: RESUMEN POR RUBRO CONTABLE -->
            <td style="width: 50%; vertical-align: top; padding-right: 5px;">
                <div class="section-header">1. Distribución por Rubros Contables</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rubro / Concepto de Ingreso</th>
                            <th class="text-center" style="width: 50px;">Trans.</th>
                            <th class="text-right" style="width: 80px;">Efectivo</th>
                            <th class="text-right" style="width: 80px;">QR / Banco</th>
                            <th class="text-right" style="width: 85px;">Subtotal (Bs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($datos['por_rubro'] as $r)
                        <tr>
                            <td>{{ $r['nombre'] }}</td>
                            <td class="text-center">{{ $r['cantidad'] }}</td>
                            <td class="text-right">Bs {{ number_format((float) $r['efectivo'], 2) }}</td>
                            <td class="text-right">Bs {{ number_format((float) $r['qr'], 2) }}</td>
                            <td class="text-right font-bold">Bs {{ number_format((float) $r['total'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>TOTAL RUBROS</td>
                            <td class="text-center">{{ $datos['metricas']['total_transacciones'] ?? 0 }}</td>
                            <td class="text-right">Bs {{ number_format((float) ($datos['metricas']['total_efectivo'] ?? 0), 2) }}</td>
                            <td class="text-right">Bs {{ number_format((float) ($datos['metricas']['total_qr_banco'] ?? 0), 2) }}</td>
                            <td class="text-right font-bold" style="color: #2e7d32;">Bs {{ number_format((float) ($datos['metricas']['total_recaudado'] ?? 0), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>

            <!-- COLUMNA 2: RESUMEN POR VENTANILLA / CAJA -->
            <td style="width: 50%; vertical-align: top; padding-left: 5px;">
                <div class="section-header">2. Recaudación por Caja / Ventanilla Física</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Caja / Punto de Venta</th>
                            <th class="text-center" style="width: 50px;">Turnos</th>
                            <th class="text-center" style="width: 50px;">Trans.</th>
                            <th class="text-right" style="width: 80px;">Efectivo</th>
                            <th class="text-right" style="width: 85px;">Total Cobrado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datos['por_caja'] as $c)
                        <tr>
                            <td class="font-bold">{{ $c['caja'] }}</td>
                            <td class="text-center">{{ $c['turnos'] }}</td>
                            <td class="text-center">{{ $c['transacciones'] }}</td>
                            <td class="text-right">Bs {{ number_format((float) $c['efectivo'], 2) }}</td>
                            <td class="text-right font-bold">Bs {{ number_format((float) $c['total'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">Sin recaudación registrada</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>TOTAL CAJAS</td>
                            <td class="text-center">{{ count($datos['turnos'] ?? []) }}</td>
                            <td class="text-center">{{ $datos['metricas']['total_transacciones'] ?? 0 }}</td>
                            <td class="text-right">Bs {{ number_format((float) ($datos['metricas']['total_efectivo'] ?? 0), 2) }}</td>
                            <td class="text-right font-bold" style="color: #2e7d32;">Bs {{ number_format((float) ($datos['metricas']['total_recaudado'] ?? 0), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="section-header" style="margin-top: 4px;">3. Recaudación por Cajero / Operador</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Cajero(a)</th>
                            <th class="text-center" style="width: 50px;">Turnos</th>
                            <th class="text-center" style="width: 50px;">Trans.</th>
                            <th class="text-right" style="width: 80px;">Efectivo</th>
                            <th class="text-right" style="width: 85px;">Total Cobrado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datos['por_cajero'] as $cj)
                        <tr>
                            <td class="font-bold">{{ $cj['cajero'] }}</td>
                            <td class="text-center">{{ $cj['turnos'] }}</td>
                            <td class="text-center">{{ $cj['transacciones'] }}</td>
                            <td class="text-right">Bs {{ number_format((float) $cj['efectivo'], 2) }}</td>
                            <td class="text-right font-bold">Bs {{ number_format((float) $cj['total'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">Sin datos de cajero</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- SECCIÓN 4: DETALLE DE TURNOS Y ARQUEOS DEL PERÍODO -->
    <div class="section-header">4. Detalle de Turnos y Arqueos de Caja en el Período</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 90px;">N° Turno</th>
                <th style="width: 140px;">Caja / Ventanilla</th>
                <th>Cajero(a)</th>
                <th style="width: 100px;">Apertura</th>
                <th style="width: 100px;">Cierre</th>
                <th class="text-right" style="width: 65px;">Fondo</th>
                <th class="text-right" style="width: 70px;">Cobros Ef.</th>
                <th class="text-right" style="width: 65px;">Cobros QR</th>
                <th class="text-right" style="width: 70px;">Esperado</th>
                <th class="text-right" style="width: 70px;">Declarado</th>
                <th class="text-right" style="width: 60px;">Diferencia</th>
                <th class="text-center" style="width: 70px;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($datos['turnos'] as $t)
            <tr>
                <td class="font-bold">{{ $t['numero_sesion'] }}</td>
                <td>{{ $t['caja_nombre'] }}</td>
                <td>{{ $t['cajero_nombre'] }}</td>
                <td>{{ $t['fecha_apertura'] }}</td>
                <td>{{ $t['fecha_cierre'] ?? 'EN CURSO' }}</td>
                <td class="text-right">{{ number_format((float) $t['monto_apertura'], 2) }}</td>
                <td class="text-right">{{ number_format((float) $t['monto_ventas_efectivo'], 2) }}</td>
                <td class="text-right">{{ number_format((float) $t['monto_ventas_qr_banco'], 2) }}</td>
                <td class="text-right font-bold">{{ number_format((float) $t['monto_esperado_efectivo'], 2) }}</td>
                <td class="text-right font-bold">{{ number_format((float) $t['monto_cierre_declarado'], 2) }}</td>
                <td class="text-right font-bold {{ $t['diferencia'] < 0 ? 'danger' : ($t['diferencia'] > 0 ? 'primary' : '') }}">
                    {{ number_format((float) $t['diferencia'], 2) }}
                </td>
                <td class="text-center">
                    @if($t['estado'] === 'CERRADA')
                        @if($t['diferencia'] == 0)
                            <span class="badge badge-success">CUADRADO</span>
                        @elseif($t['diferencia'] > 0)
                            <span class="badge badge-warning">SOBRANTE</span>
                        @else
                            <span class="badge badge-danger">FALTANTE</span>
                        @endif
                    @else
                        <span class="badge badge-info">ABIERTO</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="12" class="text-center">No se encontraron turnos en este período</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- FIRMAS DE RESPONSABILIDAD -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div style="font-weight: bold;">Cajero(a) Responsable</div>
                <div style="font-size: 8px; color: #555;">Operador de Ventanilla</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div style="font-weight: bold;">Encargado de Cobranzas</div>
                <div style="font-size: 8px; color: #555;">Supervisión y Control de Ingresos</div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div style="font-weight: bold;">Dirección Administrativa Financiera</div>
                <div style="font-size: 8px; color: #555;">Visto Bueno y Aprobación Institucional</div>
            </td>
        </tr>
    </table>

</body>
</html>
