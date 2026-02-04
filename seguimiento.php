<?php
$page_title = "Seguimiento";
require_once __DIR__ . "/_header.php";

$db = db_conn();
$errors = [];

// Prefill from URL (?key=XXXX)
$prefill = strtoupper(trim((string)($_GET['key'] ?? '')));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $key = strtoupper(trim((string)($_POST['report_key'] ?? '')));
  $pw  = (string)($_POST['password'] ?? '');

  if ($key === '' || $pw === '') {
    $errors[] = "Por favor ingresa la Clave de Reporte y la contraseña.";
  } else {
    $stmt = $db->prepare("SELECT id, password_hash FROM portal_report WHERE report_key=? LIMIT 1");
    if (!$stmt) {
      $errors[] = "DB prepare error (buscar caso): " . $db->error;
    } else {
      $stmt->bind_param("s", $key);
      $stmt->execute();
      $row = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if (!$row) {
        $errors[] = "No se encontró una denuncia con esta Clave.";
      } elseif (!password_verify($pw, $row['password_hash'])) {
        $errors[] = "La contraseña es incorrecta.";
      } else {
        // OPTIONAL: regenerate session id for safety
        if (session_status() === PHP_SESSION_ACTIVE) {
          @session_regenerate_id(true);
        }

        // Use the SAME keys that caso.php expects
        $_SESSION['denuncia_report_id'] = (int)$row['id'];
        $_SESSION['denuncia_case_ok']   = true;

        // (Optional) Keep backward compatibility with older code
        $_SESSION['report_id']  = (int)$row['id'];
        $_SESSION['report_key'] = $key;

        redirect("/caso.php");
      }
    }
  }

  // keep typed key if there is an error
  $prefill = $key;
}
?>

<div class="card hero">
  <h2>Seguimiento de denuncia</h2>

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
      <button class="btn" type="submit">Ver caso</button>
      <a class="btn secondary" href="<?= h(base_url()) ?>/">Volver</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . "/_footer.php"; ?>
