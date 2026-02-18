<?php
/** @var string $page_title */
/** @var array|null $admin */
$admin = $admin ?? null;
$flash = admin_flash_get();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h($page_title ?? 'Admin') ?></title>
  <link rel="stylesheet" href="<?= base_url() ?>/assets/css/portal.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/css/admin.css">
</head>
<body>
<header class="topbar">
  <div class="container" style="display:flex;align-items:center;justify-content:space-between;gap:16px">
    <div class="brand">
      <div class="logo" aria-hidden="true">🛡️</div>
      <div>
        <div style="font-weight:800;line-height:1">Canal de Denuncias</div>
        <div class="small">Panel Admin</div>
      </div>
    </div>

    <nav class="navlinks">
      <a class="btn btn-ghost" href="<?= admin_link('/admin/index.php') ?>">Bandeja</a>
      <a class="btn btn-ghost" href="<?= admin_link('/reportar.php') ?>">Reportar</a>
      <?php if ($admin): ?>
        <?php if (admin_is_admin($admin)): ?>
          <a class="btn btn-ghost" href="<?= admin_link('/admin/backup.php') ?>">Backup</a>
        <?php endif; ?>
        <span class="tag <?= h(admin_status_tag_class('IN_REVIEW')) ?>"><?= h($admin['role']) ?></span>
        <span class="small"><?= h($admin['email']) ?></span>
        <a class="btn" href="<?= admin_link('/admin/logout.php') ?>">Salir</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="page">
  <div class="container">

<?php if ($flash): ?>
  <div class="card" style="border-left:6px solid <?= $flash['type']==='ok' ? '#16a34a' : '#f59e0b' ?>;">
    <div class="small"><?= h($flash['msg']) ?></div>
  </div>
<?php endif; ?>
