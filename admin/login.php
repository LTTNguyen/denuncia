<?php
$page_title = 'Admin - Login';
require_once __DIR__ . '/_admin_bootstrap.php';

$db = db_conn();
$currentAdmin = admin_current_user($db);
if ($currentAdmin) {
  redirect('/admin/index.php');
  exit;
}

$error = '';
$emailValue = strtolower(trim((string)($_POST['email'] ?? '')));
$ip = portal_client_ip();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $password = (string)($_POST['password'] ?? '');

  if (!admin_csrf_check($_POST['csrf'] ?? null)) {
    $error = 'Sesión inválida. Recarga la página e intenta nuevamente.';
  } elseif ($emailValue === '' || $password === '') {
    $error = 'Completa email y contraseña.';
  } elseif (!filter_var($emailValue, FILTER_VALIDATE_EMAIL)) {
    $error = 'Ingresa un email válido.';
  } elseif (admin_is_rate_limited($db, $emailValue, $ip)) {
    admin_audit($db, null, 'ADMIN_LOGIN_RATE_LIMIT', 'SYSTEM', $emailValue, []);
    $error = 'Demasiados intentos fallidos. Intenta nuevamente más tarde.';
  } else {
    $sql = "SELECT id, email, full_name, password_hash, role, is_active
            FROM portal_admin_user
            WHERE email = ?
            LIMIT 1";
    $st = $db->prepare($sql);
    $st->bind_param('s', $emailValue);
    $st->execute();
    $res = $st->get_result();
    $user = $res ? $res->fetch_assoc() : null;
    $st->close();

    $ok = $user
      && (int)($user['is_active'] ?? 0) === 1
      && admin_password_verify($password, (string)($user['password_hash'] ?? ''));

    admin_log_login_attempt($db, $emailValue, (bool)$ok);

    if ($ok) {
      admin_rotate_session();
      $_SESSION['admin_user_id'] = (int)$user['id'];
      $_SESSION['admin_last_activity'] = time();
      admin_csrf_token();

      $st2 = $db->prepare('UPDATE portal_admin_user SET last_login_at = NOW() WHERE id = ?');
      if ($st2) {
        $uid = (int)$user['id'];
        $st2->bind_param('i', $uid);
        $st2->execute();
        $st2->close();
      }

      admin_audit($db, null, 'ADMIN_LOGIN_OK', 'INVESTIGATOR', (string)$user['email'], [
        'role' => (string)($user['role'] ?? ''),
      ]);

      admin_flash_set('ok', 'Bienvenido al panel admin.');
      redirect('/admin/index.php');
      exit;
    }

    admin_audit($db, null, 'ADMIN_LOGIN_FAIL', 'INVESTIGATOR', $emailValue, []);
    $error = 'Credenciales inválidas.';
  }
}

require __DIR__ . '/_layout_top.php';
?>

<div class="admin-page-head">
  <div>
    <div class="admin-page-head__eyebrow">Acceso interno</div>
    <h1 class="admin-page-head__title">Ingreso al panel administrativo</h1>
    <p class="admin-page-head__desc">Usa tu cuenta interna para revisar denuncias, responder mensajes, mover estados y mantener trazabilidad auditada.</p>
  </div>
  <div class="admin-chiprow">
    <span class="admin-chip">Roles: Administrador · Investigador · Solo lectura</span>
    <span class="admin-chip">Sesión separada del portal público</span>
  </div>
</div>

<div class="grid" style="grid-template-columns:minmax(320px,560px) minmax(260px,1fr);align-items:stretch">
  <div class="card" style="padding:26px 26px 24px">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px">
      <div class="admin-avatar" style="width:48px;height:48px;border-radius:16px;font-size:16px">AD</div>
      <div>
        <div style="font-size:20px;font-weight:900;color:#0f172a">Iniciar sesión</div>
        <div class="small">Acceso restringido al equipo autorizado.</div>
      </div>
    </div>

    <?php if ($error): ?>
      <div class="admin-flash admin-flash--warn" style="margin:0 0 14px">
        <div class="admin-flash__icon">!</div>
        <div>
          <div style="font-weight:900;color:#0f172a;margin-bottom:3px">No fue posible entrar</div>
          <div class="small" style="font-size:13px;color:#475569"><?= h($error) ?></div>
        </div>
      </div>
    <?php endif; ?>

    <form method="post" class="form-grid" autocomplete="on">
      <input type="hidden" name="csrf" value="<?= h(admin_csrf_token()) ?>">

      <div>
        <label>Email</label>
        <input type="email" name="email" required placeholder="admin@empresa.cl" autocomplete="username" value="<?= h($emailValue) ?>">
      </div>

      <div>
        <label>Contraseña</label>
        <input type="password" name="password" required placeholder="••••••••" autocomplete="current-password">
      </div>

      <div style="display:flex;gap:10px;align-items:center;margin-top:4px;flex-wrap:wrap">
        <button class="btn" type="submit">Entrar</button>
        <a class="btn btn-ghost" href="<?= admin_link('/reportar.php') ?>">Ir a Reportar</a>
      </div>
    </form>
  </div>

  <div class="card" style="padding:26px 24px">
    <h3 style="margin:0 0 12px;color:#0f172a">Buenas prácticas del acceso</h3>
    <ul class="list" style="margin:0;padding-left:18px;line-height:1.7;color:#475569">
      <li>Las acciones sensibles quedan registradas en audit log.</li>
      <li>El bloqueo temporal se activa tras varios intentos fallidos.</li>
      <li>La sesión administrativa usa un espacio separado del portal de denunciante.</li>
      <li>Los perfiles de solo lectura pueden revisar casos, pero no alterarlos.</li>
    </ul>
    <div class="hr"></div>
    <div class="small">Si tu cuenta está deshabilitada o no recuerdas tus credenciales, solicita apoyo al administrador del sistema.</div>
  </div>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
