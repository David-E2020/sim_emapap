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

  <script src="{{ mix('js/app.js') }}?v={{ file_exists(public_path('js/app.js')) ? filemtime(public_path('js/app.js')) : time() }}" defer></script>
</head>

<body>
  <noscript>
    <strong style="color: #ffffff; background-color: #141413; display: block; padding: 20px; text-align: center;">
      Se requiere habilitar JavaScript para acceder a este sistema.
    </strong>
  </noscript>

  <div id="app">
    <style>
      body { margin: 0; background-color: #141413; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; overflow: hidden; }
      .app-splash-loader {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        height: 100vh; width: 100vw; background-color: #141413; color: #f5f4f0;
      }
      .splash-card {
        display: flex; flex-direction: column; align-items: center; gap: 14px;
      }
      .splash-spinner {
        width: 42px; height: 42px; border: 3px solid rgba(255, 255, 255, 0.08);
        border-top-color: #38bdf8; border-radius: 50%;
        animation: splash-spin 0.8s cubic-bezier(0.5, 0.1, 0.5, 0.9) infinite;
      }
      .splash-title {
        font-size: 1.15rem; font-weight: 700; letter-spacing: 0.14em; color: #f7f6f2; text-transform: uppercase;
      }
      .splash-sub {
        font-size: 0.8rem; color: #9c9a92; font-weight: 500; letter-spacing: 0.02em;
      }
      @keyframes splash-spin { to { transform: rotate(360deg); } }
    </style>
    <div class="app-splash-loader">
      <div class="splash-card">
        <div class="splash-spinner"></div>
        <div class="splash-title">EMAPAP</div>
        <div class="splash-sub">Iniciando sistema...</div>
      </div>
    </div>
  </div>
</body>

</html>
