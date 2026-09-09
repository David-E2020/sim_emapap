<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla de Arqueo de Caja - {{ $sesion->numero_sesion }}</title>
    <style>
        @page {
            margin: 10mm;
            size: letter portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #222;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #00695c;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h1 {
            font-size: 16px;
            margin: 0;
            color: #004d40;
            text-transform: uppercase;
        }
        .header-title h2 {
            font-size: 13px;
            margin: 3px 0 0 0;
            color: #00796b;
            font-weight: bold;
        }
        .header-title p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #555;
        }
        .turno-badge {
            background-color: #e0f2f1;
            border: 1px solid #00796b;
            border-radius: 4px;
            padding: 4px 8px;
            font-weight: bold;
            color: #004d40;
            text-align: right;
            font-size: 12px;
        }
        .section-box {
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            overflow: hidden;
        }
        .section-header {
            background-color: #eceff1;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 11px;
            color: #37474f;
            border-bottom: 1px solid #ccc;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            background-color: #f5f5f5;
            padding: 5px 6px;
            text-align: left;
            font-size: 10px;
            border-bottom: 1px solid #ddd;
            color: #333;
        }
        .data-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .total-row {
            background-color: #fafafa;
            font-weight: bold;
            border-top: 1px solid #bbb;
        }
        .resultado-box {
            padding: 8px;
            border-radius: 4px;
            margin-top: 6px;
            font-weight: bold;
            text-align: center;
            font-size: 13px;
        }
        .cuadrado {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #81c784;
        }
        .sobrante {
            background-color: #e3f2fd;
            color: #1565c0;
            border: 1px solid #90caf9;
        }
        .faltante {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        .firmas-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .firmas-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 10px 30px;
        }
        .linea-firma {
            border-top: 1px dashed #555;
            padding-top: 5px;
            font-weight: bold;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <!-- CABECERA INSTITUCIONAL -->
    <table class="header-table">
        <tr>
            <td style="width: 20%;">
                <strong style="color: #00695c; font-size: 14px;">EMAPAP</strong><br>
                <span style="font-size: 9px; color: #666;">Patacamaya - La Paz</span>
            </td>
            <td class="header-title" style="width: 55%;">
                <h1>EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO</h1>
                <h2>PLANILLA OFICIAL DE ARQUEO Y CIERRE DE CAJA</h2>
                <p>Gestión Comercial, Recaudaciones y Facturación Electrónica SIAT</p>
            </td>
            <td style="width: 25%; text-align: right;">
                <div class="turno-badge">
                    {{ $sesion->numero_sesion }}<br>
                    <span style="font-size: 9px; font-weight: normal;">Estado: {{ $sesion->estado }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- INFORMACIÓN DE LA SESIÓN -->
    <div class="section-box">
        <div class="section-header">1. DATOS GENERALES DEL TURNO Y VENTANILLA FISCAL</div>
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <strong>Cajero(a) Responsable:</strong> {{ $sesion->cajero ? $sesion->cajero->name : 'N/A' }}<br>
                    <strong>Usuario del Sistema:</strong> {{ $sesion->cajero ? $sesion->cajero->usr_usuario : 'N/A' }}<br>
                    <strong>Sucursal:</strong> {{ $sesion->sucursal ? $sesion->sucursal->nombre : 'Casa Matriz (0)' }}
                </td>
                <td style="width: 50%;">
                    <strong>Caja / Punto de Venta SIAT:</strong> Punto {{ $sesion->puntoVenta ? $sesion->puntoVenta->codigo_punto_venta : 0 }} - {{ $sesion->puntoVenta ? $sesion->puntoVenta->nombre : 'Ventanilla Central' }}<br>
                    <strong>Apertura:</strong> {{ $sesion->fecha_apertura ? $sesion->fecha_apertura->format('d/m/Y H:i:s') : '-' }}<br>
                    <strong>Cierre:</strong> {{ $sesion->fecha_cierre ? $sesion->fecha_cierre->format('d/m/Y H:i:s') : 'EN CURSO' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- RESUMEN DE COBRANZAS POR RUBRO Y FORMA DE PAGO -->
    <table style="width: 100%; margin-bottom: 10px;" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width: 49%; vertical-align: top;">
                <div class="section-box" style="margin-bottom: 0;">
                    <div class="section-header">2. RESUMEN DE RECAUDACIÓN DEL SISTEMA</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Rubro / Concepto</th>
                                <th class="text-right">Efectivo</th>
                                <th class="text-right">QR / Banco</th>
                                <th class="text-right">Total (Bs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Consumo de Agua y Alcantarillado</td>
                                <td class="text-right">{{ number_format($totalesRubro['agua_efectivo'] ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($totalesRubro['agua_qr'] ?? 0, 2) }}</td>
                                <td class="text-right font-bold">{{ number_format(($totalesRubro['agua_efectivo'] ?? 0) + ($totalesRubro['agua_qr'] ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td>Cuotas de Convenio de Pago</td>
                                <td class="text-right">{{ number_format($totalesRubro['cuotas_efectivo'] ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($totalesRubro['cuotas_qr'] ?? 0, 2) }}</td>
                                <td class="text-right font-bold">{{ number_format(($totalesRubro['cuotas_efectivo'] ?? 0) + ($totalesRubro['cuotas_qr'] ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td>Recibos de Caja (Conexiones/Multas)</td>
                                <td class="text-right">{{ number_format($totalesRubro['recibos_efectivo'] ?? 0, 2) }}</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right font-bold">{{ number_format($totalesRubro['recibos_efectivo'] ?? 0, 2) }}</td>
                            </tr>
                            <tr class="total-row">
                                <td>TOTALES RECAUDADOS</td>
                                <td class="text-right text-teal">{{ number_format($sesion->monto_ventas_efectivo, 2) }}</td>
                                <td class="text-right text-teal">{{ number_format($sesion->monto_ventas_qr_banco, 2) }}</td>
                                <td class="text-right text-teal">{{ number_format($sesion->monto_ventas_efectivo + $sesion->monto_ventas_qr_banco, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="section-box" style="margin-top: 10px;">
                    <div class="section-header">3. BALANCE DE CAJA FÍSICA (GAVETA)</div>
                    <table class="data-table">
                        <tr>
                            <td>(+) Fondo de Apertura (Sencillo Inicial)</td>
                            <td class="text-right font-bold">Bs {{ number_format($sesion->monto_apertura, 2) }}</td>
                        </tr>
                        <tr>
                            <td>(+) Cobranzas en Efectivo</td>
                            <td class="text-right font-bold">Bs {{ number_format($sesion->monto_ventas_efectivo, 2) }}</td>
                        </tr>
                        <tr>
                            <td>(+) Entradas Extraordinarias de Caja Chica</td>
                            <td class="text-right">Bs {{ number_format($sesion->monto_ingresos_extra, 2) }}</td>
                        </tr>
                        <tr>
                            <td>(-) Salidas / Egresos Menores de Caja</td>
                            <td class="text-right" style="color: #c62828;">- Bs {{ number_format($sesion->monto_egresos_extra, 2) }}</td>
                        </tr>
                        <tr class="total-row" style="background-color: #e0f2f1; font-size: 11px;">
                            <td>(=) EFECTIVO TOTAL ESPERADO EN GAVETA</td>
                            <td class="text-right text-teal font-bold">Bs {{ number_format($sesion->monto_esperado_efectivo, 2) }}</td>
                        </tr>
                        <tr>
                            <td>(=) EFECTIVO FÍSICO REAL DECLARADO</td>
                            <td class="text-right font-bold">Bs {{ number_format($sesion->monto_cierre_declarado ?? 0, 2) }}</td>
                        </tr>
                    </table>

                    @php
                        $dif = (float) ($sesion->diferencia ?? 0);
                    @endphp
                    @if(abs($dif) < 0.01)
                        <div class="resultado-box cuadrado">
                            ✓ CAJA CUADRADA EXACTA (Diferencia: Bs 0.00)
                        </div>
                    @elseif($dif > 0)
                        <div class="resultado-box sobrante">
                            ▲ SOBRANTE DE CAJA: + Bs {{ number_format($dif, 2) }}
                        </div>
                    @else
                        <div class="resultado-box faltante">
                            ▼ FALTANTE DE CAJA: - Bs {{ number_format(abs($dif), 2) }}
                        </div>
                    @endif
                </div>
            </td>

            <td style="width: 2%;"></td>

            <!-- ARQUEO DETALLADO DE CORTES DE MONEDA BOLIVIANA -->
            <td style="width: 49%; vertical-align: top;">
                <div class="section-box">
                    <div class="section-header">4. ARQUEO FÍSICO: CONTEO DE CORTES (BOLIVIANOS)</div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Corte / Denominación</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-right">Subtotal (Bs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $cortes = [
                                    ['corte' => 'Billete Bs. 200', 'valor' => 200, 'key' => 'b200'],
                                    ['corte' => 'Billete Bs. 100', 'valor' => 100, 'key' => 'b100'],
                                    ['corte' => 'Billete Bs. 50', 'valor' => 50, 'key' => 'b50'],
                                    ['corte' => 'Billete Bs. 20', 'valor' => 20, 'key' => 'b20'],
                                    ['corte' => 'Billete Bs. 10', 'valor' => 10, 'key' => 'b10'],
                                    ['corte' => 'Moneda Bs. 5', 'valor' => 5, 'key' => 'm5'],
                                    ['corte' => 'Moneda Bs. 2', 'valor' => 2, 'key' => 'm2'],
                                    ['corte' => 'Moneda Bs. 1', 'valor' => 1, 'key' => 'm1'],
                                    ['corte' => 'Moneda Bs. 0.50', 'valor' => 0.50, 'key' => 'm050'],
                                    ['corte' => 'Moneda Bs. 0.20', 'valor' => 0.20, 'key' => 'm020'],
                                    ['corte' => 'Moneda Bs. 0.10', 'valor' => 0.10, 'key' => 'm010'],
                                ];
                                $desglose = $sesion->desglose_billetes ?? [];
                                $totalConteo = 0.0;
                            @endphp
                            @foreach($cortes as $c)
                                @php
                                    $cant = (int) ($desglose[$c['key']] ?? 0);
                                    $sub = $cant * $c['valor'];
                                    $totalConteo += $sub;
                                @endphp
                                <tr>
                                    <td>{{ $c['corte'] }}</td>
                                    <td class="text-center">{{ $cant }}</td>
                                    <td class="text-right">{{ number_format($sub, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row" style="background-color: #e0f2f1;">
                                <td colspan="2">TOTAL CONTEO FÍSICO DECLARADO</td>
                                <td class="text-right font-bold text-teal">Bs {{ number_format($totalConteo, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if($sesion->observaciones_apertura || $sesion->observaciones_cierre)
                <div class="section-box" style="margin-top: 8px; padding: 6px; font-size: 10px;">
                    <strong>Observaciones:</strong><br>
                    @if($sesion->observaciones_apertura)
                        <em>Apertura:</em> {{ $sesion->observaciones_apertura }}<br>
                    @endif
                    @if($sesion->observaciones_cierre)
                        <em>Cierre:</em> {{ $sesion->observaciones_cierre }}
                    @endif
                </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- FIRMAS DE CONFORMIDAD -->
    <table class="firmas-table">
        <tr>
            <td>
                <div class="linea-firma">
                    {{ $sesion->cajero ? $sesion->cajero->name : 'CAJERO(A) RESPONSABLE' }}<br>
                    Cajero(a) Ventanilla Recaudaciones
                </div>
            </td>
            <td>
                <div class="linea-firma">
                    {{ $sesion->supervisor ? $sesion->supervisor->name : 'SUPERVISOR / RESPONSABLE COMERCIAL' }}<br>
                    Vo.Bo. Jefatura Comercial / Tesorería
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
