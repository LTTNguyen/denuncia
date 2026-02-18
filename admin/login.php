<?php
$page_title = "Admin - Login";
require_once __DIR__ . "/_admin_bootstrap.php";

$db = db_conn();
$admin = admin_current_user($db);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!admin_csrf_check($_POST['csrf'] ?? null)) {
    $error = "Sesión inválida. Recarga la página e intenta nuevamente.";
  } else {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
      $error = "Completa email y contraseña.";
    } else {
      $sql = "SELECT id, email, password_hash, role, is_active
              FROM portal_admin_user
              WHERE email = ? LIMIT 1";
      $st = $db->prepare($sql);
      $st->bind_param("s", $email);
      $st->execute();
      $res = $st->get_result();
      $u = $res->fetch_assoc();
      $st->close();

      $ok = $u && (int)$u['is_active'] === 1 && admin_password_verify($password, (string)$u['password_hash']);

      // Log attempt
      $ip = $_SERVER['REMOTE_ADDR'] ?? null;
      $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
      $st2 = $db->prepare("INSERT INTO portal_admin_login_attempt (email, success, ip, user_agent) VALUES (?, ?, ?, ?)");
      $success = $ok ? 1 : 0;
      $st2->bind_param("siss", $email, $success, $ip, $ua);
      $st2->execute();
      $st2->close();

      if ($ok) {
        $_SESSION['admin_user_id'] = (int)$u['id'];

        $db->query("UPDATE portal_admin_user SET last_login_at = NOW() WHERE id = " . (int)$u['id']);

        admin_audit($db, null, 'ADMIN_LOGIN_OK', 'INVESTIGATOR', $email, ['role' => $u['role']]);
        admin_flash_set('ok', 'Login exitoso.');
        redirect('/admin/index.php');
        exit;
      }

      admin_audit($db, null, 'ADMIN_LOGIN_FAIL', 'INVESTIGATOR', $email, []);
      $error = "Credenciales inválidas.";
    }
  }
}

require __DIR__ . "/_layout_top.php";
?>

<section class="hero" style="padding-top:26px">
  <h1>Acceso Administrador</h1>
  <p class="small">Gestiona casos, cambia estados, revisa adjuntos y responde mensajes.</p>
</section>

<div class="grid" style="grid-template-columns: 1fr;max-width:520px;margin:0 auto">
  <div class="card">
    <?php if ($error): ?>
      <div class="tag tag-bad" style="display:inline-flex;margin-bottom:10px"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form-grid">
      <input type="hidden" name="csrf" value="<?= h(admin_csrf_token()) ?>">

      <div>
        <label>Email</label>
        <input type="email" name="email" required placeholder="admin@empresa.cl" autocomplete="username">
      </div>

      <div>
        <label>Contraseña</label>
        <input type="password" name="password" required placeholder="••••••••" autocomplete="current-password">
      </div>

      <div style="display:flex;gap:10px;align-items:center;margin-top:4px">
        <button class="btn" type="submit">Entrar</button>
        <a class="btn btn-ghost" href="<?= admin_link('/reportar.php') ?>">Ir a Reportar</a>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . "/_layout_bottom.php"; ?>
