<?php
$page_title = "FAQ";
require_once __DIR__ . "/_header.php";
?>

<div class="card hero">
  <h2>FAQ</h2>

  <h3 style="margin:14px 0 6px 0;font-size:15px;color:var(--text)">1) ¿Se puede denunciar de forma anónima?</h3>
  <p class="small">Sí. Tick “Denuncia anónima”.</p>

  <h3 style="margin:14px 0 6px 0;font-size:15px;color:var(--text)">2) Seguimiento như thế nào?</h3>
  <p class="small">Usa la Clave de Reporte + la contraseña que definiste al enviar.</p>

  <h3 style="margin:14px 0 6px 0;font-size:15px;color:var(--text)">3) ¿Demo o producción?</h3>
  <p class="small">Demo. En producción: subdominio + HTTPS + hardening.</p>

  <div class="btnrow">
    <a class="btn" href="<?= h(base_url()) ?>/reportar.php">Reportar</a>
    <a class="btn secondary" href="<?= h(base_url()) ?>/">Inicio</a>
  </div>
</div>

<?php require_once __DIR__ . "/_footer.php"; ?>
