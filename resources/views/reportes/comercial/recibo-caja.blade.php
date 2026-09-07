<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recibo de Caja - {{ $recibo->numero_recibo }}</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      margin: 0;
      padding: 20px;
      color: #222;
      font-size: 12px;
    }
    .recibo-card {
      border: 2px solid #047857;
      border-radius: 8px;
      padding: 16px;
      margin-bottom: 20px;
      position: relative;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      border-bottom: 2px solid #047857;
      padding-bottom: 8px;
      margin-bottom: 12px;
    }
    .empresa-title {
      font-size: 15px;
      font-weight: bold;
      color: #047857;
    }
    .empresa-sub {
      font-size: 10px;
      color: #555;
    }
    .recibo-num {
      text-align: right;
      font-size: 16px;
      font-weight: bold;
      color: #047857;
    }
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
    }
    .info-table td {
      padding: 5px 8px;
      font-size: 12px;
    }
    .label {
      font-weight: bold;
      color: #374151;
      width: 22%;
    }
    .val {
      color: #111827;
      width: 78%;
    }
    .monto-highlight {
      background-color: #ecfdf5;
      border: 1px solid #a7f3d0;
      border-radius: 6px;
      padding: 10px 14px;
      margin: 15px 0;
      display: flex;
      justify-content: space-between;
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
      padding: 0 20px;
    }
    .linea-firma {
      border-top: 1px solid #374151;
      margin-top: 50px;
      padding-top: 5px;
      font-size: 11px;
      font-weight: bold;
    }
    .watermark {
      position: absolute;
      top: 35%;
      left: 20%;
      font-size: 38px;
      font-weight: bold;
      color: rgba(4, 120, 87, 0.08);
      transform: rotate(-25deg);
      pointer-events: none;
    }
  </style>
</head>
<body>

  <div class="recibo-card">
    <div class="watermark">EMAPAP RECIBO OFICIAL</div>

    <table class="header-table">
      <tr>
        <td style="width: 60%;">
          <div class="empresa-title">EMAPAP - PATACAMAYA</div>
          <div class="empresa-sub">Empresa Municipal de Agua Potable y Alcantarillado Sanitario</div>
          <div class="empresa-sub">Patacamaya - La Paz - Bolivia</div>
        </td>
        <td style="width: 40%;" class="recibo-num">
          RECIBO DE CAJA<br>
          <span style="font-size: 13px; color: #111;">{{ $recibo->numero_recibo }}</span><br>
          <span style="font-size: 10px; font-weight: normal; color: #6b7280;">Fecha: {{ \Carbon\Carbon::parse($recibo->created_at)->format('d/m/Y H:i') }}</span>
        </td>
      </tr>
    </table>

    <table class="info-table">
      <tr>
        <td class="label">Recibí de:</td>
        <td class="val" style="font-size: 13px; font-weight: bold;">{{ $recibo->nombre_cliente }}</td>
      </tr>
      <tr>
        <td class="label">C.I. / NIT:</td>
        <td class="val">{{ $recibo->documento_cliente ?? 'S/N' }}</td>
      </tr>
      @if($recibo->id_abonado)
        <tr>
          <td class="label">Código Abonado:</td>
          <td class="val"><strong>{{ $recibo->abonado?->codigo }}</strong> (Zona: {{ $recibo->abonado?->zona?->nombre ?? 'N/A' }})</td>
        </tr>
      @endif
      <tr>
        <td class="label">Concepto:</td>
        <td class="val">
          <strong>{{ str_replace('_', ' ', $recibo->concepto_tipo) }}</strong>
        </td>
      </tr>
      <tr>
        <td class="label">Por concepto de:</td>
        <td class="val">{{ $recibo->descripcion }}</td>
      </tr>
    </table>

    <div class="monto-highlight">
      <div>
        <span style="font-size: 11px; color: #065f46;">La suma de:</span><br>
        <span style="font-size: 12px; font-weight: bold; color: #047857;">
          Bs {{ number_format($recibo->monto_total, 2) }}
        </span>
      </div>
      <div style="text-align: right;">
        <span style="font-size: 11px; color: #065f46;">Estado:</span><br>
        <span style="font-size: 12px; font-weight: bold; color: #047857;">{{ $recibo->estado }}</span>
      </div>
    </div>

    <table class="firmas-table">
      <tr>
        <td>
          <div class="linea-firma">
            Firma / Aclaración Interesado<br>
            <span style="font-size: 9px; font-weight: normal; color: #6b7280;">C.I.: {{ $recibo->documento_cliente }}</span>
          </div>
        </td>
        <td>
          <div class="linea-firma">
            Cajero / Responsable de Cobranza<br>
            <span style="font-size: 9px; font-weight: normal; color: #6b7280;">EMAPAP Patacamaya</span>
          </div>
        </td>
      </tr>
    </table>
  </div>

</body>
</html>
