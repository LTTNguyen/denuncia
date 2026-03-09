<?php
/** @var string $page_title */
/** @var array|null $admin */
$admin = $admin ?? null;
$flash = admin_flash_get();
$currentPath = basename((string)($_SERVER['SCRIPT_NAME'] ?? ''));
$logoCandidates = [
  '/images/tym_logo.png',
  '/images/LOGO_TYM.png',
  '/images/LOGOFULL.png',
];
$logoUrl = admin_link($logoCandidates[0]);
foreach ($logoCandidates as $candidate) {
  $abs = realpath(__DIR__ . '/..' . $candidate);
  if ($abs && is_file($abs)) {
    $logoUrl = admin_link($candidate);
    break;
  }
}
$adminName = admin_user_display_name($admin);
$adminRoleLabel = admin_role_label($admin['role'] ?? null);
$adminInitials = admin_initials($adminName);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h($page_title ?? 'Admin') ?></title>
  <link rel="stylesheet" href="<?= base_url() ?>/assets/css/portal.css">
  <link rel="stylesheet" href="<?= base_url() ?>/assets/css/admin.css">
  <link rel="stylesheet" href="<?= admin_link('/admin/_admin_ui.css?v=4') ?>">
  <style>
    body.admin-shell{background:
      radial-gradient(920px 560px at 0% 0%, rgba(11,61,145,.09), transparent 55%),
      radial-gradient(700px 480px at 100% 0%, rgba(215,25,32,.07), transparent 48%),
      #f5f7fb;}
    .admin-shell .page{padding-bottom:24px}
    .admin-topbar{
      position:sticky;top:0;z-index:20;
      backdrop-filter:saturate(160%) blur(10px);
      background:rgba(255,255,255,.84);
      border-bottom:1px solid rgba(226,232,240,.95);
    }
    .admin-topbar .container-admin{
      width:min(1180px,calc(100% - 56px));
      margin:0 auto;
      display:flex;align-items:center;justify-content:space-between;gap:14px;
      padding:10px 0;
    }
    .admin-brand{display:flex;align-items:center;gap:12px;min-width:250px}
    .admin-brand__logo{
      width:50px;height:50px;border-radius:16px;background:#fff;border:1px solid #dbe3ee;
      box-shadow:0 10px 22px rgba(15,23,42,.07);display:grid;place-items:center;overflow:hidden;padding:6px;
    }
    .admin-brand__logo img{max-width:100%;max-height:100%;object-fit:contain;display:block}
    .admin-brand__eyebrow{font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:#64748b;font-weight:800}
    .admin-brand__title{font-size:16px;font-weight:900;line-height:1.08;color:#0f172a}
    .admin-brand__sub{font-size:11px;color:#64748b;margin-top:2px}
    .admin-nav{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .admin-nav__link{
      display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:12px;
      border:1px solid #e2e8f0;background:#fff;color:#0f172a;font-weight:800;
      box-shadow:0 6px 16px rgba(15,23,42,.05);text-decoration:none;font-size:13px;
    }
    .admin-nav__link:hover{text-decoration:none;transform:translateY(-1px)}
    .admin-nav__link.is-active{background:#0b3d91;color:#fff;border-color:#0b3d91}
    .admin-userbox{
      display:flex;align-items:center;gap:10px;padding:7px 10px;border-radius:14px;
      border:1px solid #dbe3ee;background:linear-gradient(180deg,#fff,#f8fbff);
      box-shadow:0 8px 20px rgba(15,23,42,.05);
    }
    .admin-avatar{
      width:38px;height:38px;border-radius:12px;background:linear-gradient(135deg,#0b3d91,#1e63d6);
      color:#fff;display:grid;place-items:center;font-weight:900;font-size:12px;letter-spacing:.03em;
    }
    .admin-userbox__meta{display:grid;gap:1px}
    .admin-userbox__name{font-size:12px;font-weight:900;color:#0f172a}
    .admin-userbox__sub{font-size:10px;color:#64748b}
    .admin-page-head{display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap;margin:2px 0 14px}
    .admin-page-head__eyebrow{font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:#64748b;font-weight:800;margin-bottom:6px}
    .admin-page-head__title{margin:0;font-size:24px;line-height:1.05;color:#0f172a}
    .admin-page-head__desc{margin:6px 0 0;color:#475569;max-width:760px;line-height:1.5;font-size:14px}
    .admin-chiprow{display:flex;gap:8px;flex-wrap:wrap}
    .admin-chip{
      display:inline-flex;align-items:center;gap:8px;padding:7px 11px;border-radius:999px;
      background:rgba(11,61,145,.08);border:1px solid rgba(11,61,145,.12);color:#0b3d91;font-size:11px;font-weight:800;
    }
    .admin-flash{display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:16px;margin:0 0 14px;
      border:1px solid #dbeafe;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,.05)}
    .admin-flash--ok{border-color:rgba(22,163,74,.20);background:linear-gradient(180deg,rgba(22,163,74,.06),#fff)}
    .admin-flash--warn{border-color:rgba(245,158,11,.25);background:linear-gradient(180deg,rgba(245,158,11,.07),#fff)}
    .admin-flash__icon{width:30px;height:30px;border-radius:10px;display:grid;place-items:center;font-size:16px;background:#eff6ff}
    .admin-flash--ok .admin-flash__icon{background:rgba(22,163,74,.12)}
    .admin-flash--warn .admin-flash__icon{background:rgba(245,158,11,.14)}
    .admin-footer-note{margin-top:18px;color:#64748b;font-size:11px;text-align:center}
    .topbar{display:none}
    .admin-logout-form{display:inline-flex;margin:0}
    .admin-logout-btn{min-height:36px;padding:0 12px;border-radius:12px;border:1px solid #e2e8f0;background:#fff;color:#0f172a;font-weight:800;cursor:pointer}
    .admin-logout-btn:hover{transform:translateY(-1px)}
    @media (max-width: 980px){
      .admin-topbar .container-admin{width:calc(100% - 24px);padding:10px 0}
      .admin-brand{min-width:unset}
      .admin-nav{width:100%;justify-content:flex-start}
      .admin-userbox{width:100%;justify-content:space-between}
      .admin-page-head__title{font-size:22px}
    }
  </style>
</head>
<body class="admin-shell">
<header class="admin-topbar">
  <div class="container-admin">
    <div class="admin-brand">
      <div class="admin-brand__logo">
        <img src="<?= h($logoUrl) ?>" alt="TyM logo" onerror="this.style.display='none'">
      </div>
      <div>
        <div class="admin-brand__eyebrow">Canal de Denuncias</div>
        <div class="admin-brand__title">Panel Admin</div>
        <div class="admin-brand__sub">Gestión segura de casos, mensajes y trazabilidad interna.</div>
      </div>
    </div>

    <nav class="admin-nav">
      <a class="admin-nav__link <?= $currentPath === 'index.php' ? 'is-active' : '' ?>" href="<?= admin_link('/admin/index.php') ?>">Bandeja</a>
      <a class="admin-nav__link" href="<?= admin_link('/reportar.php') ?>">Reportar</a>
      <?php if ($admin && admin_is_admin($admin)): ?>
        <a class="admin-nav__link <?= $currentPath === 'backup.php' ? 'is-active' : '' ?>" href="<?= admin_link('/admin/backup.php') ?>">Backup</a>
      <?php endif; ?>
      <?php if ($admin): ?>
        <div class="admin-userbox">
          <div style="display:flex;align-items:center;gap:10px">
            <div class="admin-avatar"><?= h($adminInitials) ?></div>
            <div class="admin-userbox__meta">
              <div class="admin-userbox__name"><?= h($adminName) ?></div>
              <div class="admin-userbox__sub"><?= h($admin['email'] ?? '') ?></div>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end">
            <span class="tag <?= h(admin_role_badge_class($admin['role'] ?? null)) ?>"><?= h($adminRoleLabel) ?></span>
            <form class="admin-logout-form" method="post" action="<?= admin_link('/admin/logout.php') ?>">
              <input type="hidden" name="csrf" value="<?= h(admin_csrf_token()) ?>">
              <button class="admin-logout-btn" type="submit">Salir</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="page">
  <div class="container-admin" style="margin:18px auto 34px; width:min(1180px,calc(100% - 56px));">

<?php if ($flash): ?>
  <?php $flashOk = (($flash['type'] ?? '') === 'ok'); ?>
  <div class="admin-flash <?= $flashOk ? 'admin-flash--ok' : 'admin-flash--warn' ?>">
    <div class="admin-flash__icon"><?= $flashOk ? '✓' : '!' ?></div>
    <div>
      <div style="font-weight:900;color:#0f172a;margin-bottom:3px"><?= $flashOk ? 'Operación completada' : 'Atención' ?></div>
      <div class="small" style="font-size:12px;color:#475569"><?= h($flash['msg']) ?></div>
    </div>
  </div>
<?php endif; ?>
