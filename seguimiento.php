<?php
$page_title = "Seguimiento";
require_once __DIR__ . "/_header.php";

$db = db_conn();
$errors = [];

// Prefill from URL (?key=XXXX)
$prefill = strtoupper(trim((string)($_GET['key'] ?? '')));

// -------------------------------
// Simple brute-force protection
// -------------------------------
$now = time();
$lock_until = (int)($_SESSION['denuncia_login_lock_until'] ?? 0);
$attempts   = (int)($_SESSION['denuncia_login_attempts'] ?? 0);

if ($lock_until > $now) {
  $errors[] = "Demasiados intentos. Intenta nuevamente más tarde.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
  $key = strtoupper(trim((string)($_POST['report_key'] ?? '')));
  $pw  = (string)($_POST['password'] ?? '');

  // normalize: report_key generated is 10 chars
  $key = preg_replace('/[^A-Z0-9]/', '', $key);
  if (strlen($key) > 32) $key = substr($key, 0, 32);

  if ($key === '' || $pw === '') {
    $errors[] = "Por favor ingresa la Clave de Reporte y la contraseña.";
    audit_log($db, null, 'REPORTER_LOGIN_FAIL', 'REPORTER', null, ['key' => $key, 'reason' => 'MISSING_FIELDS']);
  } elseif (strlen($key) !== 10) {
    $errors[] = "La Clave de Reporte no tiene el formato esperado.";
    audit_log($db, null, 'REPORTER_LOGIN_FAIL', 'REPORTER', null, ['key' => $key, 'reason' => 'BAD_KEY_FORMAT']);
  } else {
    $stmt = $db->prepare("SELECT id, password_hash FROM portal_report WHERE report_key=? LIMIT 1");
    if (!$stmt) {
      $errors[] = "DB prepare error (buscar caso): " . $db->error;
      audit_log($db, null, 'REPORTER_LOGIN_FAIL', 'REPORTER', null, ['key' => $key, 'reason' => 'DB_PREPARE']);
    } else {
      $stmt->bind_param("s", $key);
      $stmt->execute();
      $row = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if (!$row) {
        $errors[] = "No se encontró una denuncia con esta Clave.";
        audit_log($db, null, 'REPORTER_LOGIN_FAIL', 'REPORTER', null, ['key' => $key, 'reason' => 'NOT_FOUND']);
      } elseif (!password_verify($pw, $row['password_hash'])) {
        $errors[] = "La contraseña es incorrecta.";
        audit_log($db, (int)$row['id'], 'REPORTER_LOGIN_FAIL', 'REPORTER', null, ['key' => $key, 'reason' => 'BAD_PASSWORD']);
      } else {
        // ✅ success: reset lock counters
        unset($_SESSION['denuncia_login_attempts'], $_SESSION['denuncia_login_lock_until']);

        if (session_status() === PHP_SESSION_ACTIVE) {
          @session_regenerate_id(true);
        }

        $_SESSION['denuncia_report_id'] = (int)$row['id'];
        $_SESSION['denuncia_case_ok']   = true;

        // ✅ session timing (timeout control)
        $_SESSION['denuncia_case_auth_at'] = time();
        $_SESSION['denuncia_case_last_seen'] = time();

        // backward compatibility
        $_SESSION['report_id']  = (int)$row['id'];
        $_SESSION['report_key'] = $key;

        audit_log($db, (int)$row['id'], 'REPORTER_LOGIN_OK', 'REPORTER', null, ['key' => $key]);

        redirect("/caso.php");
      }
    }
  }

  // if failed -> increase attempts and possibly lock
  if (!empty($errors)) {
    $attempts = (int)($_SESSION['denuncia_login_attempts'] ?? 0);
    $attempts++;
    $_SESSION['denuncia_login_attempts'] = $attempts;

    // lock after 5 fails for 10 minutes
    if ($attempts >= 5) {
      $_SESSION['denuncia_login_lock_until'] = time() + 10 * 60;
      $_SESSION['denuncia_login_attempts'] = 0; // reset counter after locking
    }
  }

  $prefill = $key;
}
?>

<div class="card hero" style="max-width:920px; margin:0 auto;">
  <h2>Seguimiento de denuncia</h2>
  <p class="small">Ingresa tu Clave de Reporte y contraseña para acceder al caso.</p>

  <?php if (!empty($errors)): ?>
    <div class="alert">
      <b>Error:</b>
      <ul class="list"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="">
    <div class="row">
      <div class="field">
        <label>Clave de Reporte</label>
        <input name="report_key" value="<?= h($prefill) ?>" autocomplete="off" />
      </div>
      <div class="field">
        <label>Contraseña</label>
        <input type="password" name="password" autocomplete="current-password" />
      </div>
    </div>

    <div class="btnrow">
      <button class="btn primary" type="submit">Ver caso</button>
      <a class="btn secondary" href="<?= h(base_url()) ?>/">Volver</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . "/_footer.php"; ?>
