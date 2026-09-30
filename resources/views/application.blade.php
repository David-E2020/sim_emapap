<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>EMAPA - Sistema Integrado de Gestión Empresarial</title>

  <!-- Favicons & Mobile Touch Icons -->
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">

  <script src="{{ mix('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
</head>

<body>
  <noscript>
    <strong>Se requiere habilitar JavaScript para acceder a este sistema.</strong>
  </noscript>

  <div id="app">
  </div>
</body>

</html>
