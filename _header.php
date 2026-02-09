<?php
require_once __DIR__ . "/_bootstrap.php";

$page = basename($_SERVER['SCRIPT_NAME'] ?? '');
$is_home = ($page === 'index.php');

// Public pages use partner-logo topbar
$is_public = in_array($page, ['reportar.php','seguimiento.php','faq.php','caso.php'], true);

$no_default_header = $no_default_header ?? false;
$use_public_topbar = $use_public_topbar ?? $is_public;

$body_class_extra = $body_class_extra ?? '';
$body_class = trim(($is_home ? 'home' : '') . ($is_public ? ' report' : '') . ' ' . $body_class_extra);

// company id: prefer URL company_id
$cid = (int)($_GET['company_id'] ?? current_company_id());
if ($cid > 0) $_SESSION['company_id'] = $cid;

// Load current company branding (fallback safe)
$company = null;
$companies = [];
try {
  $db = db_conn();
  $companies = get_companies($db);

  if ($cid <= 0 && !empty($companies)) {
    $cid = (int)$companies[0]['id'];
    $_SESSION['company_id'] = $cid;
  }

  $company = ($cid > 0) ? portal_find_company($companies, $cid) : null;
} catch (Throwable $e) {
  // ignore if DB not ready
}

$brand_title = $company['name'] ?? "Canal de Denuncias";
$brand_logo  = $company ? portal_company_logo_path($company) : "";
$brand_logo_url = $brand_logo ? (rtrim(base_url(), '/') . '/' . ltrim($brand_logo, '/')) : "";

function nav_active(string $file, string $current): string {
  return ($file === $current) ? 'active' : '';
}

// Partner logos (same as index.php)
$base = rtrim(base_url(), '/');
$logo_tym   = $base . "/images/tym_logo.png";
$logo_andes = $base . "/images/logo_andes_pic.png";
$logo_rk    = $base . "/images/logo_rk.png";

// External websites (same as index.php)
$link_tym   = "https://www.tymelectricos.cl/";
$link_andes = "https://andessuministros.cl/";
$link_rk    = "https://www.rkmaestranza.cl/";

// Nav links keep company_id
$home_href        = $base . "/?company_id=" . (int)$cid;
$reportar_href    = $base . "/reportar.php?company_id=" . (int)$cid;
$seguimiento_href = $base . "/seguimiento.php?company_id=" . (int)$cid;
$faq_href         = $base . "/faq.php?company_id=" . (int)$cid;
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

<body class="<?= h($body_class) ?>">

<?php if (!$is_home && !$no_default_header && $use_public_topbar): ?>
  <div class="public-topbar">
    <div class="home-topbar">
      <div class="home-logos">
        <a class="home-logo-link" href="<?= h($link_tym) ?>" target="_blank" rel="noopener">
          <img src="<?= h($logo_tym) ?>" alt="T&M">
        </a>
        <a class="home-logo-link" href="<?= h($link_andes) ?>" target="_blank" rel="noopener">
          <img src="<?= h($logo_andes) ?>" alt="Andes">
        </a>
        <a class="home-logo-link" href="<?= h($link_rk) ?>" target="_blank" rel="noopener">
          <img src="<?= h($logo_rk) ?>" alt="RK">
        </a>
      </div>

      <div class="topbar-right">
        <div class="topbar-nav">
          <a class="pill <?= nav_active('index.php', $page) ?>" data-nav-company href="<?= h($home_href) ?>">Inicio</a>
          <a class="pill <?= nav_active('reportar.php', $page) ?>" data-nav-company href="<?= h($reportar_href) ?>">Reportar</a>
          <a class="pill <?= nav_active('seguimiento.php', $page) ?>" data-nav-company href="<?= h($seguimiento_href) ?>">Seguimiento</a>
          <a class="pill <?= nav_active('faq.php', $page) ?>" data-nav-company href="<?= h($faq_href) ?>">FAQ</a>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="container">

<?php if (!$is_home && !$no_default_header && !$use_public_topbar): ?>
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
    </div>
  </div>
<?php endif; ?>
