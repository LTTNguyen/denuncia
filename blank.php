<?php
$doc = (string)($_GET['doc'] ?? 'documento');
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Demo - Documento</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;margin:40px;color:#111827}
    .box{max-width:720px}
    .muted{color:#6b7280}
  </style>
</head>
<body>
  <div class="box">
    <h2>Documento no disponible (demo)</h2>
    <p class="muted">Has abierto: <b><?= htmlspecialchars($doc, ENT_QUOTES, 'UTF-8') ?></b></p>
    <p>En producción, este enlace apuntaría a un PDF o a una página real.</p>
  </div>
</body>
</html>
