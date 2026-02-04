<?php
require_once __DIR__ . "/_bootstrap.php";

$page = basename($_SERVER['SCRIPT_NAME'] ?? '');
$cid  = current_company_id();

// Load current company for branding (safe fallback if DB not ready)
$company = null;
try {
  $db = db_conn();
  $companies = get_companies($db);
  if ($cid <= 0 && !empty($companies)) {
    $cid = (int)$companies[0]['id'];
    $_SESSION['company_id'] = $cid;
  }
  $company = ($cid > 0) ? portal_find_company($companies, $cid) : null;
} catch (Throwable $e) {
  $companies = [];
}

$brand_title = $company['name'] ?? "Canal de Denuncias";
$brand_logo  = $company ? portal_company_logo_path($company) : "";
$brand_logo_url = $brand_logo ? (rtrim(base_url(), '/') . '/' . ltrim($brand_logo, '/')) : "";

function nav_active(string $file, string $current): string {
  return ($file === $current) ? 'active' : '';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= h($page_title ?? "Denuncias Portal") ?></title>

  <link rel="stylesheet" href="<?= h(base_url()) ?>/assets/css/portal.css">
  <script>window.PORTAL_BASE = <?= json_encode(base_url(), JSON_UNESCAPED_SLASHES) ?>;</script>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

</head>
<body>
  <div class="container">
    <div class="header">
      <div class="brand">
        <?php if ($brand_logo_url): ?>
          <img class="brand-logo" src="<?= h($brand_logo_url) ?>" alt="<?= h($brand_title) ?>">
        <?php else: ?>
          <div class="brand-badge" aria-hidden="true"></div>
        <?php endif; ?>

        <div>
          <div class="brand-title"><?= h($brand_title) ?></div>
          <div class="sub">Canal de denuncias - demo interna</div>
        </div>
      </div>

      <div class="topnav">
        <a class="pill <?= nav_active('index.php', $page) ?>" href="<?= h(base_url()) ?>/">Inicio</a>
        <a class="pill <?= nav_active('reportar.php', $page) ?>" href="<?= h(portal_link('/reportar.php')) ?>">Reportar</a>
        <a class="pill <?= nav_active('seguimiento.php', $page) ?>" href="<?= h(portal_link('/seguimiento.php', false)) ?>">Seguimiento</a>
        <a class="pill <?= nav_active('faq.php', $page) ?>" href="<?= h(portal_link('/faq.php', false)) ?>">FAQ</a>

        <?php if (!empty($_SESSION['report_id'])): ?>
          <a class="pill <?= nav_active('caso.php', $page) ?>" href="<?= h(portal_link('/caso.php', false)) ?>">Case</a>
          <a class="pill" href="<?= h(base_url()) ?>/logout.php">Salir</a>
        <?php endif; ?>
      </div>
    </div>
