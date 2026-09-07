<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Extracto de Cuenta - {{ $abonado->codigo }}</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      margin: 0;
      padding: 15px;
      color: #222;
      font-size: 11px;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
      border-bottom: 2px solid #1976D2;
      padding-bottom: 8px;
    }
    .empresa-title {
      font-size: 15px;
      font-weight: bold;
      color: #1976D2;
    }
    .empresa-sub {
      font-size: 9px;
      color: #555;
    }
    .doc-title {
      text-align: right;
      font-size: 14px;
      font-weight: bold;
      color: #333;
    }
    .box-info {
      background-color: #f8fafc;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      padding: 10px;
      margin-bottom: 15px;
    }
    .info-table {
      width: 100%;
      border-collapse: collapse;
    }
    .info-table td {
      padding: 3px 6px;
      font-size: 11px;
    }
    .label {
      font-weight: bold;
      color: #475569;
      width: 18%;
    }
    .val {
      color: #0f172a;
      width: 32%;
    }
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      margin-bottom: 15px;
    }
    .data-table th {
      background-color: #1e293b;
      color: #ffffff;
      padding: 6px 8px;
      font-size: 10px;
      text-align: center;
      border: 1px solid #1e293b;
    }
    .data-table td {
      padding: 5px 6px;
      font-size: 10px;
      border: 1px solid #e2e8f0;
      text-align: center;
    }
    .data-table tr:nth-child(even) {
      background-color: #f8fafc;
    }
    .text-right {
      text-align: right !important;
    }
    .text-left {
      text-align: left !important;
    }
    .badge {
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 9px;
      font-weight: bold;
      display: inline-block;
    }
    .badge-pagado {
      background-color: #dcfce7;
      color: #166534;
    }
    .badge-pendiente {
      background-color: #fee2e2;
      color: #991b1b;
    }
    .badge-convenio {
      background-color: #fef3c7;
      color: #92400e;
    }
    .totales-box {
      width: 45%;
      margin-left: auto;
      border-collapse: collapse;
      margin-top: 10px;
      background-color: #f1f5f9;
      border: 1px solid #94a3b8;
    }
    .totales-box td {
      padding: 5px 10px;
      font-size: 11px;
    }
    .footer {
      margin-top: 25px;
      text-align: center;
      font-size: 9px;
      color: #64748b;
      border-top: 1px dashed #cbd5e1;
      padding-top: 8px;
    }
  </style>
</head>
<body>

  <!-- ENCABEZADO -->
  <table class="header-table">
    <tr>
      <td style="width: 60%;">
        <div class="empresa-title">EMAPAP - PATACAMAYA</div>
        <div class="empresa-sub">Empresa Municipal de Agua Potable y Alcantarillado Sanitario</div>
        <div class="empresa-sub">Patacamaya - La Paz - Bolivia</div>
      </td>
      <td style="width: 40%;" class="doc-title">
        EXTRACTO DE CUENTA HISTÓRICO<br>
        <span style="font-size: 10px; font-weight: normal; color: #64748b;">Fecha Emisión: {{ date('d/m/Y H:i') }}</span>
      </td>
    </tr>
  </table>

  <!-- DATOS DEL ABONADO -->
  <div class="box-info">
    <table class="info-table">
      <tr>
        <td class="label">Código Abonado:</td>
        <td class="val" style="font-size: 13px; font-weight: bold; color: #1976D2;">{{ $abonado->codigo }}</td>
        <td class="label">Estado Servicio:</td>
        <td class="val">
          <strong style="color: {{ $abonado->estado_servicio === 'ACTIVO' ? '#166534' : ($abonado->estado_servicio === 'CORTADO' ? '#dc2626' : '#64748b') }};">
            {{ $abonado->estado_servicio }}
          </strong>
        </td>
      </tr>
      <tr>
        <td class="label">Nombre / Razón Social:</td>
        <td class="val"><strong>{{ $abonado->nombre_completo }}</strong></td>
        <td class="label">Categoría Tarifaria:</td>
        <td class="val">{{ $abonado->categoria?->nombre ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td class="label">C.I. / NIT:</td>
        <td class="val">{{ $abonado->numero_documento ?? 'S/N' }}</td>
        <td class="label">Zona / Barrio:</td>
        <td class="val">{{ $abonado->zona?->nombre ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td class="label">Dirección:</td>
        <td class="val">{{ $abonado->calle?->nombre ?? '' }} {{ $abonado->numero_vivienda ? 'N° ' . $abonado->numero_vivienda : '' }}</td>
        <td class="label">N° Medidor / Diám.:</td>
        <td class="val">{{ $abonado->medidorActual?->numero_serie ?? 'SIN MEDIDOR' }} ({{ $abonado->medidorActual?->diametro ?? '1/2"' }})</td>
      </tr>
      <tr>
        <td class="label">Alcantarillado:</td>
        <td class="val">{{ $abonado->tiene_alcantarillado ? 'SÍ (Activo)' : 'NO' }}</td>
        <td class="label">Meses en Mora:</td>
        <td class="val"><strong style="color: {{ $abonado->meses_mora > 1 ? '#dc2626' : '#166534' }};">{{ $abonado->meses_mora }} mes(es)</strong></td>
      </tr>
    </table>
  </div>

  <h4 style="margin: 12px 0 4px 0; color: #1e293b; font-size: 11px;">HISTORIAL DE FACTURACIÓN Y LECTURAS MENSUALES</h4>
  <table class="data-table">
    <thead>
      <tr>
        <th>Periodo</th>
        <th>F. Lectura</th>
        <th>Lec. Ant.</th>
        <th>Lec. Act.</th>
        <th>Consumo</th>
        <th>Agua (Bs)</th>
        <th>Alcant. (Bs)</th>
        <th>Desc. Ley 1886</th>
        <th>Total (Bs)</th>
        <th>Estado</th>
        <th>N° Factura / Pago</th>
      </tr>
    </thead>
    <tbody>
      @php
        $totalConsumo = 0;
        $totalFacturado = 0;
        $totalPendiente = 0;
        $totalPagado = 0;
      @endphp
      @forelse($lecturas as $lec)
        @php
          $totalConsumo += $lec->consumo_m3;
          $totalFacturado += $lec->total_facturado;
          if ($lec->estado_pago === 'PAGADO') {
            $totalPagado += $lec->total_facturado;
          } elseif ($lec->estado_pago === 'PENDIENTE') {
            $totalPendiente += $lec->total_facturado;
          }
        @endphp
        <tr>
          <td><strong>{{ $lec->periodo?->periodo ?? 'N/A' }}</strong></td>
          <td>{{ $lec->fecha_lectura ? \Carbon\Carbon::parse($lec->fecha_lectura)->format('d/m/Y') : '-' }}</td>
          <td class="text-right">{{ number_format($lec->lectura_anterior, 2) }}</td>
          <td class="text-right">{{ number_format($lec->lectura_actual, 2) }}</td>
          <td class="text-right"><strong>{{ number_format($lec->consumo_m3, 2) }} m³</strong></td>
          <td class="text-right">{{ number_format($lec->monto_agua, 2) }}</td>
          <td class="text-right">{{ number_format($lec->monto_alcantarillado, 2) }}</td>
          <td class="text-right">{{ number_format($lec->monto_descuento_ley1886, 2) }}</td>
          <td class="text-right"><strong>{{ number_format($lec->total_facturado, 2) }}</strong></td>
          <td>
            @if($lec->estado_pago === 'PAGADO')
              <span class="badge badge-pagado">PAGADO</span>
            @elseif($lec->estado_pago === 'EN_CONVENIO')
              <span class="badge badge-convenio">EN CONVENIO</span>
            @else
              <span class="badge badge-pendiente">PENDIENTE</span>
            @endif
          </td>
          <td style="font-size: 9px;">
            @if($lec->id_factura)
              Factura #{{ $lec->facturaSiat?->numero_factura ?? $lec->id_factura }}
            @elseif($lec->fecha_pago)
              {{ \Carbon\Carbon::parse($lec->fecha_pago)->format('d/m/Y') }}
            @else
              -
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="11" style="padding: 15px; color: #64748b;">No existen lecturas registradas en el historial.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <!-- CONVENIOS SI HUBIERAN -->
  @if(count($convenios) > 0)
    <h4 style="margin: 15px 0 4px 0; color: #1e293b; font-size: 11px;">CONVENIOS DE PAGO SUSCRITOS</h4>
    <table class="data-table">
      <thead>
        <tr>
          <th>N° Convenio</th>
          <th>Fecha Suscripción</th>
          <th>Monto Total (Bs)</th>
          <th>Cuota Inicial (Bs)</th>
          <th>Saldo Financiado</th>
          <th>Cuotas</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        @foreach($convenios as $c)
          <tr>
            <td><strong>{{ $c->numero_convenio }}</strong></td>
            <td>{{ \Carbon\Carbon::parse($c->fecha_suscripcion)->format('d/m/Y') }}</td>
            <td class="text-right">{{ number_format($c->monto_total_deuda, 2) }}</td>
            <td class="text-right">{{ number_format($c->monto_cuota_inicial, 2) }}</td>
            <td class="text-right">{{ number_format($c->saldo_financiar, 2) }}</td>
            <td>{{ $c->numero_cuotas }} cuotas</td>
            <td><strong>{{ $c->estado }}</strong></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  <!-- RESUMEN DE TOTALES -->
  <table class="totales-box">
    <tr>
      <td style="font-weight: bold; color: #334155;">Total Volumen Consumido:</td>
      <td class="text-right"><strong>{{ number_format($totalConsumo, 2) }} m³</strong></td>
    </tr>
    <tr>
      <td style="font-weight: bold; color: #334155;">Total Histórico Facturado:</td>
      <td class="text-right">Bs {{ number_format($totalFacturado, 2) }}</td>
    </tr>
    <tr>
      <td style="font-weight: bold; color: #166534;">Total Recaudado / Pagado:</td>
      <td class="text-right" style="color: #166534;"><strong>Bs {{ number_format($totalPagado, 2) }}</strong></td>
    </tr>
    <tr style="border-top: 2px solid #cbd5e1; background-color: #e2e8f0;">
      <td style="font-weight: bold; color: #b91c1c; font-size: 12px;">SALDO PENDIENTE ACTUAL:</td>
      <td class="text-right" style="color: #b91c1c; font-size: 12px;"><strong>Bs {{ number_format($totalPendiente, 2) }}</strong></td>
    </tr>
  </table>

  <!-- FOOTER -->
  <div class="footer">
    Extracto emitido oficialmente por el Sistema Integrado Municipal EMAPAP Patacamaya.<br>
    Válido para trámites internos, aclaración de consumos y transferencias de inmuebles.
  </div>

</body>
</html>
