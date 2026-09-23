<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>{{ $tituloReporte }} - {{ $codigoSocio }}</title>
  <style>
    @page {
      margin: 15mm 20mm;
      size: letter portrait;
    }
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      margin: 0;
      padding: 0;
      color: #1a1a1a;
      font-size: 11pt;
      line-height: 1.5;
    }
    .top-bar {
      width: 100%;
      margin-bottom: 25px;
    }
    .top-bar td {
      vertical-align: top;
    }
    .empresa-nombre {
      font-size: 12pt;
      font-weight: bold;
      color: #0d47a1;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .empresa-sub {
      font-size: 8.5pt;
      color: #555;
    }
    .codigo-box {
      text-align: right;
      font-size: 11pt;
      font-weight: bold;
      color: #222;
    }
    .doc-titulo {
      text-align: center;
      font-size: 13pt;
      font-weight: bold;
      color: #111;
      text-transform: uppercase;
      margin: 15px 0 25px 0;
      letter-spacing: 0.5px;
      line-height: 1.3;
    }
    .texto-cuerpo {
      text-align: justify;
      margin-bottom: 16px;
      text-indent: 0;
    }
    .clausula-titulo {
      font-weight: bold;
    }
    .distribucion-tabla {
      width: 100%;
      margin: 12px 0 20px 0;
      border-collapse: collapse;
      font-size: 10.5pt;
    }
    .distribucion-tabla td {
      padding: 4px 8px;
    }
    .cronograma-contenedor {
      margin: 15px 0 20px 0;
    }
    .cronograma-titulo {
      text-align: center;
      font-weight: bold;
      font-size: 10.5pt;
      text-transform: uppercase;
      margin-bottom: 8px;
      letter-spacing: 0.5px;
    }
    .cronograma-tabla {
      width: 85%;
      margin: 0 auto;
      border-collapse: collapse;
      font-size: 10pt;
    }
    .cronograma-tabla th {
      border-top: 1px solid #333;
      border-bottom: 1px solid #333;
      padding: 5px 8px;
      font-weight: bold;
      text-align: center;
    }
    .cronograma-tabla td {
      padding: 4px 8px;
      text-align: center;
    }
    .cronograma-tabla tr.total-row td {
      border-top: 1px solid #333;
      border-bottom: 1px solid #333;
      font-weight: bold;
    }
    .monto-literal {
      margin: 14px 0 20px 0;
      font-size: 10pt;
      font-weight: bold;
      text-transform: uppercase;
    }
    .fecha-lugar {
      text-align: right;
      margin: 35px 0 50px 0;
      font-size: 10.5pt;
      font-weight: bold;
      text-transform: uppercase;
    }
    .firmas-tabla {
      width: 100%;
      margin-top: 40px;
      border-collapse: collapse;
    }
    .firmas-tabla td {
      width: 50%;
      text-align: center;
      vertical-align: top;
      font-size: 10pt;
    }
    .linea-firma {
      width: 200px;
      border-top: 1px solid #333;
      margin: 0 auto 8px auto;
    }
    .marca-agua {
      position: absolute;
      top: 40%;
      left: 20%;
      font-size: 60pt;
      color: rgba(200, 200, 200, 0.15);
      transform: rotate(-30deg);
      z-index: -1;
      text-transform: uppercase;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <!-- MARCA DE AGUA INSTITUCIONAL -->
  <div class="marca-agua">EMAPAP</div>

  <!-- ENCABEZADO -->
  <table class="top-bar">
    <tr>
      <td style="width: 70%;">
        <div class="empresa-nombre">EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO</div>
        <div class="empresa-sub">EMAPAP - PATACAMAYA · DEPARTAMENTO DE LA PAZ - BOLIVIA</div>
        <div class="empresa-sub">Suministro de Servicios Básicos y Micromedición</div>
      </td>
      <td style="width: 30%;" class="codigo-box">
        Código: {{ $codigoSocio }}
      </td>
    </tr>
  </table>

  <!-- TÍTULO DEL DOCUMENTO -->
  <div class="doc-titulo">
    {{ $tituloReporte }}
  </div>

  <!-- CUERPO PRINCIPAL DEL CONTRATO -->
  <div class="texto-cuerpo">
    Conste por el presente documento privado que en su caso será elevado a instrumento público, suscrito entre la <strong>Empresa Municipal de Agua Potable y Alcantarillado (EMAPA)</strong>, y por otra parte El(La) Sr(Sra) <strong>{{ $nombreSocio }}</strong> con C.I. Nº <strong>{{ $ci }}</strong> con el Código <strong>{{ $codigoSocio }}</strong>.
  </div>

  <div class="texto-cuerpo">
    Con domicilio en la zona: <strong>{{ $zona }}</strong> Calle/Av: <strong>{{ $calle }}</strong> de Patacamaya, que en adelante se denominará USUARIO, se ha convenido en suscribir el presente Contrato de Servicio de {{ $tipoServicioNombre }} y el pago del costo de conexión domiciliaria en cuotas mensuales de acuerdo al cronograma de pagos, bajo las siguientes cláusulas:
  </div>

  <!-- CLÁUSULA PRIMERA -->
  <div class="texto-cuerpo">
    <span class="clausula-titulo">PRIMERO.-</span> Por la conexión domiciliaria el usuario se compromete a efectuar la modalidad de pago diferido del costo de la conexión domiciliaria de acuerdo a la siguiente distribución:
  </div>

  <table class="distribucion-tabla">
    <tr>
      <td style="width: 25%;"><strong>Aporte (Bs):</strong></td>
      <td style="width: 25%;">{{ number_format($aporte->aporte, 2) }}</td>
      <td style="width: 25%;"><strong>Instalación (Bs):</strong></td>
      <td style="width: 25%;">{{ number_format($aporte->instalacion, 2) }}</td>
    </tr>
    <tr>
      <td><strong>Total (Bs):</strong></td>
      <td><strong>{{ number_format($aporte->total, 2) }}</strong></td>
      <td><strong>Plazo (Meses):</strong></td>
      <td><strong>{{ $aporte->plazo }}</strong></td>
    </tr>
  </table>

  <!-- CRONOGRAMA DE PAGOS -->
  <div class="cronograma-contenedor">
    <div class="cronograma-titulo">CRONOGRAMA DE PAGOS</div>
    <table class="cronograma-tabla">
      <thead>
        <tr>
          <th>Periodo</th>
          <th>Fecha de Pago</th>
          <th style="text-align: right;">Importe (Bs)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($cuotas as $c)
          <tr>
            <td>{{ $c['periodo'] }}</td>
            <td>{{ $c['fecha_pago'] }}</td>
            <td style="text-align: right;">{{ number_format($c['importe'], 2) }}</td>
          </tr>
        @endforeach
        <tr class="total-row">
          <td colspan="2" style="text-align: right;">TOTAL:</td>
          <td style="text-align: right;">{{ number_format($aporte->total, 2) }}</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- MONTO LITERAL -->
  <div class="monto-literal">
    SON: {{ $numeroLiteral }}
  </div>

  <!-- CLÁUSULA SEGUNDA -->
  <div class="texto-cuerpo">
    <span class="clausula-titulo">SEGUNDO.-</span> Pasado los 30 días en mora, se procederá al Retiro de la conexión sin reclamo alguno.
  </div>

  <!-- CLÁUSULA TERCERA -->
  <div class="texto-cuerpo">
    <span class="clausula-titulo">TERCERO.-</span> Ambas partes contratantes declaran su conformidad con las cláusulas del presente documento reconociendo en el mismo, Contrato de Adhesión, quedando el USUARIO obligado a someterse a los reglamentos y todas las normas que rigen para la prestación de estos servicios.
  </div>

  <!-- LUGAR Y FECHA -->
  <div class="fecha-lugar">
    PATACAMAYA, {{ $fechaTexto }}
  </div>

  <!-- FIRMAS -->
  <table class="firmas-tabla">
    <tr>
      <td>
        <div class="linea-firma"></div>
        <strong>{{ $nombreSocio }}</strong><br>
        C.I. Nº {{ $ci }}<br>
        <strong>USUARIO</strong>
      </td>
      <td>
        <div class="linea-firma"></div>
        <strong>EMAPAP</strong><br>
        Administración y Dirección Técnica<br>
        <strong>p/ EMAPA</strong>
      </td>
    </tr>
  </table>

</body>
</html>
