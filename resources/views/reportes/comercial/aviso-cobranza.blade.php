<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Aviso de Cobranza - {{ $abonado->codigo }}</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      margin: 0;
      padding: 15px;
      color: #222;
      font-size: 11px;
    }
    .aviso-container {
      border: 2px solid #1976D2;
      border-radius: 8px;
      padding: 12px;
      margin-bottom: 20px;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .empresa-title {
      font-size: 14px;
      font-weight: bold;
      color: #1976D2;
    }
    .empresa-sub {
      font-size: 9px;
      color: #666;
    }
    .badge-periodo {
      background-color: #1976D2;
      color: white;
      padding: 4px 8px;
      font-weight: bold;
      font-size: 12px;
      border-radius: 4px;
      text-align: center;
      display: inline-block;
    }
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .info-table td {
      padding: 3px 4px;
      vertical-align: top;
    }
    .label {
      font-weight: bold;
      color: #444;
      width: 18%;
    }
    .val {
      color: #111;
      width: 32%;
    }
    .medicion-box {
      background-color: #F0F4F8;
      border: 1px solid #D1D5DB;
      border-radius: 6px;
      padding: 6px 10px;
      margin-bottom: 10px;
    }
    .medicion-table {
      width: 100%;
      text-align: center;
      border-collapse: collapse;
    }
    .medicion-table th {
      font-size: 9px;
      color: #555;
      text-transform: uppercase;
    }
    .medicion-table td {
      font-size: 13px;
      font-weight: bold;
      color: #1976D2;
    }
    .detalles-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .detalles-table th, .detalles-table td {
      border-bottom: 1px solid #E5E7EB;
      padding: 4px;
    }
    .detalles-table th {
      background-color: #F9FAFB;
      text-align: left;
      font-size: 10px;
    }
    .total-box {
      background-color: #FEF3C7;
      border: 1px solid #F59E0B;
      border-radius: 6px;
      padding: 8px;
      text-align: right;
      margin-bottom: 10px;
    }
    .total-val {
      font-size: 16px;
      font-weight: bold;
      color: #B45309;
    }
    .historico-title {
      font-size: 10px;
      font-weight: bold;
      color: #4B5563;
      margin-bottom: 4px;
      text-transform: uppercase;
    }
    .historico-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9px;
    }
    .historico-table th, .historico-table td {
      border: 1px solid #E5E7EB;
      padding: 2px 4px;
      text-align: center;
    }
    .historico-table th {
      background-color: #F3F4F6;
    }
    .footer-nota {
      font-size: 8px;
      color: #6B7280;
      text-align: center;
      margin-top: 8px;
    }
  </style>
</head>
<body>
  @foreach ($lecturas as $item)
  @php
    $lec = $item['lectura'];
    $ab = $lec->abonado;
    $historico = $item['historico'];
  @endphp
  <div class="aviso-container">
    <table class="header-table">
      <tr>
        <td style="width: 65%;">
          <div class="empresa-title">EMAPAP - PATACAMAYA</div>
          <div class="empresa-sub">Empresa Municipal de Agua Potable y Alcantarillado Patacamaya</div>
          <div class="empresa-sub">NIT: 1028475024 | Patacamaya, La Paz - Bolivia</div>
        </td>
        <td style="width: 35%; text-align: right;">
          <div class="badge-periodo">PERIODO: {{ $lec->periodo ? $lec->periodo->periodo : 'S/P' }}</div>
          <div style="font-size: 9px; margin-top: 3px; color: #DC2626; font-weight: bold;">
            Vence: {{ $lec->periodo ? $lec->periodo->fecha_vencimiento_pago : 'Inmediato' }}
          </div>
        </td>
      </tr>
    </table>

    <table class="info-table">
      <tr>
        <td class="label">CÓDIGO:</td>
        <td class="val"><strong>{{ $ab->codigo }}</strong></td>
        <td class="label">CATEGORÍA:</td>
        <td class="val">{{ $ab->categoria ? $ab->categoria->nombre : 'DOMICILIARIA' }}</td>
      </tr>
      <tr>
        <td class="label">ABONADO:</td>
        <td class="val" colspan="3"><strong>{{ $ab->nombre_completo }}</strong></td>
      </tr>
      <tr>
        <td class="label">UBICACIÓN:</td>
        <td class="val" colspan="3">
          {{ $ab->zona ? $ab->zona->nombre : 'Zona Central' }}
          {{ $ab->calle ? ' - ' . $ab->calle->nombre : '' }}
          {{ $ab->numero_vivienda ? ' #' . $ab->numero_vivienda : '' }}
        </td>
      </tr>
      <tr>
        <td class="label">MEDIDOR:</td>
        <td class="val">{{ $ab->medidorActual ? $ab->medidorActual->numero_serie : 'SIN MEDIDOR' }}</td>
        <td class="label">ALCANTARILLADO:</td>
        <td class="val">{{ $ab->tiene_alcantarillado ? 'SÍ TIENE' : 'NO TIENE' }}</td>
      </tr>
    </table>

    <!-- CAJA DE MEDICIÓN -->
    <div class="medicion-box">
      <table class="medicion-table">
        <tr>
          <th>Lectura Anterior</th>
          <th>Lectura Actual</th>
          <th>Consumo del Mes</th>
        </tr>
        <tr>
          <td>{{ number_format($lec->lectura_anterior, 1) }} m³</td>
          <td>{{ number_format($lec->lectura_actual, 1) }} m³</td>
          <td style="color: #059669;">{{ number_format($lec->consumo_m3, 1) }} m³</td>
        </tr>
      </table>
    </div>

    <!-- DETALLE DE IMPORTES -->
    <table class="detalles-table">
      <thead>
        <tr>
          <th>Concepto de Cobro</th>
          <th style="text-align: right;">Importe (Bs)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Servicio de Agua Potable (Consumo {{ number_format($lec->consumo_m3, 1) }} m³)</td>
          <td style="text-align: right;">{{ number_format($lec->monto_agua, 2) }}</td>
        </tr>
        <tr>
          <td>Servicio de Alcantarillado Sanitario</td>
          <td style="text-align: right;">{{ number_format($lec->monto_alcantarillado, 2) }}</td>
        </tr>
        @if ($lec->monto_descuento_ley1886 > 0)
        <tr style="color: #059669;">
          <td>Descuento 20% Tercera Edad (Ley 1886)</td>
          <td style="text-align: right;">-{{ number_format($lec->monto_descuento_ley1886, 2) }}</td>
        </tr>
        @endif
        @if ($lec->monto_otros > 0)
        <tr>
          <td>Otros Cargos / Reinstalación / Mora</td>
          <td style="text-align: right;">{{ number_format($lec->monto_otros, 2) }}</td>
        </tr>
        @endif
      </tbody>
    </table>

    <div class="total-box">
      <div style="font-size: 11px;">IMPORTE DEL MES: <strong>Bs {{ number_format($lec->total_facturado, 2) }}</strong></div>
      @if ($ab->saldo_deuda > $lec->total_facturado)
      <div style="font-size: 11px; color: #DC2626;">Deuda Anterior Acumulada: Bs {{ number_format($ab->saldo_deuda - $lec->total_facturado, 2) }}</div>
      @endif
      <div class="total-val">TOTAL A CANCELAR: Bs {{ number_format(max($lec->total_facturado, $ab->saldo_deuda), 2) }}</div>
    </div>

    <!-- HISTORICO CONSUMOS -->
    <div class="historico-title">Histórico de Consumos (Últimos Meses)</div>
    <table class="historico-table">
      <thead>
        <tr>
          <th>Periodo</th>
          <th>Consumo m³</th>
          <th>Agua (Bs)</th>
          <th>Alcantarillado (Bs)</th>
          <th>Total (Bs)</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($historico as $h)
        <tr>
          <td>{{ $h->periodo ? $h->periodo->periodo : '-' }}</td>
          <td>{{ number_format($h->consumo_m3, 1) }}</td>
          <td>{{ number_format($h->monto_agua, 2) }}</td>
          <td>{{ number_format($h->monto_alcantarillado, 2) }}</td>
          <td>{{ number_format($h->total_facturado, 2) }}</td>
          <td>{{ $h->estado_pago }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>

    <div class="footer-nota">
      * Este documento es un Aviso de Cobranza informativo. Al cancelar en ventanilla de EMAPAP se emitirá su Factura Electrónica SIAT válida para crédito fiscal. Evite cortes pagando puntualmente.
    </div>
  </div>
  @if (!$loop->last)
  <div style="page-break-after: always;"></div>
  @endif
  @endforeach
</body>
</html>
