<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Factura / Recibo de Pago - {{ $aporte->factura }}</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      margin: 0;
      padding: 15px 25px;
      color: #1f2937;
      font-size: 12px;
    }
    .recibo-card {
      border: 2px solid #0f766e;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 25px;
      position: relative;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      border-bottom: 2px solid #0f766e;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }
    .empresa-title {
      font-size: 16px;
      font-weight: bold;
      color: #0f766e;
    }
    .empresa-sub {
      font-size: 10px;
      color: #4b5563;
      line-height: 1.3;
    }
    .recibo-num {
      text-align: right;
      font-size: 16px;
      font-weight: bold;
      color: #0f766e;
    }
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }
    .info-table td {
      padding: 6px 8px;
      font-size: 12px;
      vertical-align: top;
    }
    .label {
      font-weight: bold;
      color: #374151;
      width: 24%;
    }
    .val {
      color: #111827;
      width: 76%;
    }
    .detalle-table {
      width: 100%;
      border-collapse: collapse;
      margin: 15px 0;
    }
    .detalle-table th {
      background-color: #f0fdfa;
      border-top: 1px solid #0f766e;
      border-bottom: 1px solid #0f766e;
      padding: 8px;
      font-size: 11px;
      font-weight: bold;
      color: #0f766e;
    }
    .detalle-table td {
      padding: 8px;
      border-bottom: 1px solid #e5e7eb;
      font-size: 12px;
    }
    .monto-highlight {
      background-color: #f0fdfa;
      border: 1px solid #99f6e4;
      border-radius: 6px;
      padding: 10px 14px;
      margin: 15px 0;
    }
    .firmas-table {
      width: 100%;
      margin-top: 40px;
      border-collapse: collapse;
    }
    .firmas-table td {
      width: 50%;
      text-align: center;
      vertical-align: top;
      padding: 0 30px;
    }
    .linea-firma {
      border-top: 1px solid #4b5563;
      margin-top: 45px;
      padding-top: 6px;
      font-size: 11px;
      font-weight: bold;
    }
    .watermark {
      position: absolute;
      top: 30%;
      left: 18%;
      font-size: 40px;
      font-weight: bold;
      color: rgba(15, 118, 110, 0.06);
      transform: rotate(-25deg);
      pointer-events: none;
    }
    .badge-estado {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 4px;
      font-weight: bold;
      font-size: 11px;
      background-color: #d1fae5;
      color: #065f46;
    }
  </style>
</head>
<body>

  <div class="recibo-card">
    <div class="watermark">EMAPAP COMPROBANTE OFICIAL</div>

    <!-- ENCABEZADO -->
    <table class="header-table">
      <tr>
        <td style="width: 60%;">
          <div class="empresa-title">EMAPAP - PATACAMAYA</div>
          <div class="empresa-sub">Empresa Municipal de Agua Potable y Alcantarillado Sanitario</div>
          <div class="empresa-sub">Suministro de Servicios Básicos y Micromedición</div>
          <div class="empresa-sub">Patacamaya - La Paz - Bolivia</div>
        </td>
        <td style="width: 40%;" class="recibo-num">
          FACTURA / RECIBO DE PAGO<br>
          <span style="font-size: 16px; color: #111827;">#{{ $aporte->factura }}</span><br>
          <span style="font-size: 11px; font-weight: normal; color: #6b7280;">Fecha: {{ $fechaEmision->format('d/m/Y H:i') }}</span>
        </td>
      </tr>
    </table>

    <!-- DATOS DEL SOCIO Y CONTRATO -->
    <table class="info-table">
      <tr>
        <td class="label">Código de Abonado:</td>
        <td class="val"><strong style="color: #0f766e; font-size: 13px;">{{ $codigoSocio }}</strong></td>
      </tr>
      <tr>
        <td class="label">Titular / Razón Social:</td>
        <td class="val" style="font-size: 13px; font-weight: bold;">{{ $nombreSocio }}</td>
      </tr>
      <tr>
        <td class="label">C.I. / NIT:</td>
        <td class="val">{{ $ci }}</td>
      </tr>
      <tr>
        <td class="label">Zona / Dirección:</td>
        <td class="val">{{ $zona }}</td>
      </tr>
      <tr>
        <td class="label">Tipo de Servicio:</td>
        <td class="val"><strong>{{ $tipoServicioNombre }}</strong></td>
      </tr>
      <tr>
        <td class="label">Periodo / Cuota:</td>
        <td class="val">{{ $aporte->periodo ?: '-' }} &nbsp;|&nbsp; <em>{{ $aporte->orden ?: ('Plazo: ' . $aporte->plazo . ' mes(es)') }}</em></td>
      </tr>
      <tr>
        <td class="label">Estado de la Cuota:</td>
        <td class="val">
          <span class="badge-estado">{{ $aporte->pagado ? 'PAGADO / CANCELADO' : 'PENDIENTE DE PAGO' }}</span>
        </td>
      </tr>
    </table>

    <!-- DETALLE DE COBRO -->
    <table class="detalle-table">
      <thead>
        <tr>
          <th style="width: 15%; text-align: left;">CÓDIGO</th>
          <th style="width: 55%; text-align: left;">DESCRIPCIÓN DEL CONCEPTO</th>
          <th style="width: 15%; text-align: center;">PERIODO</th>
          <th style="width: 15%; text-align: right;">IMPORTE (BS)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>{{ $codigoSocio }}</td>
          <td>
            <strong>Pago de Conexión Domiciliaria - {{ $tipoServicioNombre }}</strong><br>
            <span style="font-size: 10px; color: #6b7280;">
              Modalidad de pago diferido ({{ $aporte->orden ?: ('Cuota de ' . $aporte->plazo . ' mes(es)') }}).
              @if(!empty($aporte->observaciones))
                Obs: {{ $aporte->observaciones }}
              @endif
            </span>
          </td>
          <td style="text-align: center;">{{ $aporte->periodo ?: '-' }}</td>
          <td style="text-align: right; font-weight: bold; color: #0f766e;">
            {{ number_format($montoTotal, 2) }}
          </td>
        </tr>
        <tr style="background-color: #f9fafb; font-weight: bold;">
          <td colspan="3" style="text-align: right;">TOTAL GENERAL (BS):</td>
          <td style="text-align: right; font-size: 13px; color: #0f766e;">
            Bs {{ number_format($montoTotal, 2) }}
          </td>
        </tr>
      </tbody>
    </table>

    <div class="monto-highlight">
      <span style="font-size: 11px; color: #0f766e;">SON:</span>
      <span style="font-size: 12px; font-weight: bold; color: #111827; margin-left: 5px;">{{ $numeroLiteral }}</span>
    </div>

    <!-- FIRMAS -->
    <table class="firmas-table">
      <tr>
        <td>
          <div class="linea-firma">
            {{ $nombreSocio }}<br>
            <span style="font-size: 9px; font-weight: normal; color: #6b7280;">Firma Abonado / Solicitante</span>
          </div>
        </td>
        <td>
          <div class="linea-firma">
            CAJERO(A) / RESPONSABLE COMERCIAL<br>
            <span style="font-size: 9px; font-weight: normal; color: #6b7280;">EMAPAP - Patacamaya</span>
          </div>
        </td>
      </tr>
    </table>
  </div>

  <div style="text-align: center; font-size: 9px; color: #9ca3af; margin-top: 10px;">
    Documento oficial generado por el Sistema Integrado de Gestión EMAPAP · Patacamaya
  </div>

</body>
</html>
