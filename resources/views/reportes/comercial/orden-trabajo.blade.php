<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Orden de Trabajo - {{ $orden->numero_orden }}</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      margin: 0;
      padding: 15px;
      color: #111;
      font-size: 11px;
    }
    .orden-card {
      border: 2px dashed #334155;
      padding: 12px;
      margin-bottom: 15px;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
      border-bottom: 2px solid #334155;
      padding-bottom: 6px;
      margin-bottom: 10px;
    }
    .empresa-title {
      font-size: 14px;
      font-weight: bold;
      color: #1e293b;
    }
    .empresa-sub {
      font-size: 9px;
      color: #64748b;
    }
    .orden-title {
      text-align: right;
      font-size: 13px;
      font-weight: bold;
      color: #0f172a;
    }
    .badge-tipo {
      background-color: #1e293b;
      color: #fff;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: bold;
      display: inline-block;
      margin-top: 4px;
    }
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .info-table td {
      padding: 4px 6px;
      font-size: 11px;
    }
    .label {
      font-weight: bold;
      color: #475569;
      width: 22%;
    }
    .val {
      color: #0f172a;
      width: 28%;
    }
    .section-title {
      background-color: #f1f5f9;
      padding: 4px 8px;
      font-weight: bold;
      font-size: 11px;
      border-left: 4px solid #1e293b;
      margin: 10px 0 6px 0;
    }
    .campo-llenar {
      height: 25px;
      border-bottom: 1px dotted #94a3b8;
    }
    .firmas-table {
      width: 100%;
      margin-top: 30px;
      border-collapse: collapse;
    }
    .firmas-table td {
      width: 50%;
      text-align: center;
      vertical-align: top;
      padding: 0 15px;
    }
    .linea-firma {
      border-top: 1px solid #334155;
      margin-top: 40px;
      padding-top: 4px;
      font-size: 10px;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <div class="orden-card">
    <table class="header-table">
      <tr>
        <td style="width: 55%;">
          <div class="empresa-title">EMAPAP - PATACAMAYA</div>
          <div class="empresa-sub">Unidad de Operaciones Comerciales y Mantenimiento</div>
          <div class="empresa-sub">Patacamaya - La Paz - Bolivia</div>
        </td>
        <td style="width: 45%;" class="orden-title">
          ORDEN DE TRABAJO TÉCNICA<br>
          <span style="font-size: 12px; color: #2563eb;">{{ $orden->numero_orden }}</span><br>
          <div class="badge-tipo">{{ str_replace('_', ' ', $orden->tipo_orden) }}</div>
        </td>
      </tr>
    </table>

    <div class="section-title">1. DATOS DEL ABONADO Y UBICACIÓN</div>
    <table class="info-table">
      <tr>
        <td class="label">Código Abonado:</td>
        <td class="val"><strong>{{ $orden->abonado?->codigo }}</strong></td>
        <td class="label">Categoría:</td>
        <td class="val">{{ $orden->abonado?->categoria?->nombre ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td class="label">Nombre / Razón Social:</td>
        <td class="val" colspan="3"><strong>{{ $orden->abonado?->nombre_completo }}</strong></td>
      </tr>
      <tr>
        <td class="label">Zona / Barrio:</td>
        <td class="val">{{ $orden->abonado?->zona?->nombre ?? 'N/A' }}</td>
        <td class="label">Calle / Dirección:</td>
        <td class="val">{{ $orden->abonado?->calle?->nombre ?? '' }} {{ $orden->abonado?->numero_vivienda ? 'N° ' . $orden->abonado?->numero_vivienda : '' }}</td>
      </tr>
      <tr>
        <td class="label">Medidor Actual:</td>
        <td class="val">{{ $orden->abonado?->medidorActual?->numero_serie ?? 'SIN MEDIDOR' }} ({{ $orden->abonado?->medidorActual?->diametro ?? '1/2"' }})</td>
        <td class="label">Meses Deuda / Mora:</td>
        <td class="val"><strong>{{ $orden->abonado?->meses_mora }} mes(es) - Bs {{ number_format($orden->abonado?->saldo_deudor, 2) }}</strong></td>
      </tr>
      <tr>
        <td class="label">Fecha Programada:</td>
        <td class="val">{{ $orden->fecha_programada }}</td>
        <td class="label">Técnico Asignado:</td>
        <td class="val">{{ $orden->tecnico?->usr_nombre_completo ?? 'Cuadrilla de Guardia' }}</td>
      </tr>
      <tr>
        <td class="label">Motivo / Instrucción:</td>
        <td class="val" colspan="3">{{ $orden->motivo ?? 'Cumplimiento de corte por mora / orden técnica' }}</td>
      </tr>
    </table>

    <div class="section-title">2. DATOS DE EJECUCIÓN EN CAMPO (LLENADO POR EL TÉCNICO)</div>
    <table class="info-table">
      <tr>
        <td class="label">Fecha y Hora Ejecución:</td>
        <td class="val"><div class="campo-llenar">{{ $orden->fecha_ejecucion ?? '' }}</div></td>
        <td class="label">Lectura al Corte / Reconexión:</td>
        <td class="val"><div class="campo-llenar">{{ $orden->lectura_en_corte ? $orden->lectura_en_corte . ' m³' : '' }}</div></td>
      </tr>
      <tr>
        <td class="label">N° Precinto / Sello Instalado:</td>
        <td class="val"><div class="campo-llenar">{{ $orden->numero_precinto ?? '' }}</div></td>
        <td class="label">Estado Medidor Encontrado:</td>
        <td class="val"><div class="campo-llenar"></div></td>
      </tr>
      <tr>
        <td class="label">Observaciones de Campo:</td>
        <td class="val" colspan="3">
          <div class="campo-llenar" style="height: 35px;">{{ $orden->informe_tecnico ?? '' }}</div>
        </td>
      </tr>
    </table>

    <table class="firmas-table">
      <tr>
        <td>
          <div class="linea-firma">
            Firma Técnico de Campo<br>
            <span style="font-size: 8px; font-weight: normal; color: #64748b;">EMAPAP Patacamaya</span>
          </div>
        </td>
        <td>
          <div class="linea-firma">
            Firma Abonado / Testigo en Predio<br>
            <span style="font-size: 8px; font-weight: normal; color: #64748b;">C.I.: .................................................</span>
          </div>
        </td>
      </tr>
    </table>
  </div>

</body>
</html>
