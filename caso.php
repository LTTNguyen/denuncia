<?php
$page_title = "Caso";
require_once __DIR__ . "/_header.php";

$db = db_conn();

// IMPORTANT: Case page should be accessible only after Seguimiento verifies password.
// Expected: seguimiento.php sets $_SESSION['denuncia_report_id'] and $_SESSION['denuncia_case_ok'] = true
$report_id = (int)($_SESSION['denuncia_report_id'] ?? 0);
$case_ok   = (bool)($_SESSION['denuncia_case_ok'] ?? false);

if ($report_id <= 0 || !$case_ok) {
  redirect("/seguimiento.php");
}

// Load report
$sql = "
  SELECT r.*,
         c.name AS company_name, c.logo_path AS company_logo,
         cat.name AS category_name
  FROM portal_report r
  JOIN portal_company c ON c.id = r.company_id
  LEFT JOIN portal_category cat ON cat.id = r.category_id
  WHERE r.id = ?
  LIMIT 1
";
$stmt = $db->prepare($sql);
if (!$stmt) {
  die("DB prepare error (load report): " . h($db->error));
}
$stmt->bind_param("i", $report_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$report) {
  // session points to non-existing report
  redirect("/seguimiento.php");
}

$company_logo_url = '';
if (!empty($report['company_logo'])) {
  $company_logo_url = rtrim(base_url(), '/') . '/' . ltrim($report['company_logo'], '/');
}

// Handle new message from reporter
$errors = [];
$posted_ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $msg = trim((string)($_POST['message'] ?? ''));

  if (mb_strlen($msg) < 5) {
    $errors[] = "El mensaje es demasiado corto.";
  }

  if (empty($errors)) {
    $stmt = $db->prepare("INSERT INTO portal_report_message (report_id, sender_type, message) VALUES (?, 'REPORTER', ?)");
    if (!$stmt) {
      $errors[] = "DB prepare error (insert message): " . $db->error;
    } else {
      $stmt->bind_param("is", $report_id, $msg);
      if (!$stmt->execute()) $errors[] = "DB error (insert message): " . $stmt->error;
      $stmt->close();
    }

    // Update updated_at (optional, but good)
    if (empty($errors)) {
      $stmt = $db->prepare("UPDATE portal_report SET updated_at = NOW() WHERE id = ?");
      if ($stmt) {
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $stmt->close();
      }
      $posted_ok = true;
    }
  }
}

// Load messages
$stmt = $db->prepare("
  SELECT id, sender_type, message, created_at
  FROM portal_report_message
  WHERE report_id = ?
  ORDER BY created_at ASC, id ASC
");
if (!$stmt) {
  die("DB prepare error (load messages): " . h($db->error));
}
$stmt->bind_param("i", $report_id);
$stmt->execute();
$res = $stmt->get_result();
$messages = [];
while ($row = $res->fetch_assoc()) $messages[] = $row;
$stmt->close();
?>

<div class="card hero">
  <h2>Caso</h2>

  <div class="company-chip">
    <?php if ($company_logo_url): ?><img src="<?= h($company_logo_url) ?>" alt="<?= h($report['company_name']) ?>"><?php endif; ?>
    <div class="company-name"><?= h($report['company_name']) ?></div>
  </div>

  <div class="hr"></div>

  <div class="row">
    <div class="field">
      <label>Clave de Reporte</label>
      <div class="small"><b><?= h($report['report_key']) ?></b></div>
    </div>
    <div class="field">
      <label>Estado</label>
      <div class="small"><b><?= h($report['status']) ?></b></div>
    </div>
  </div>

  <div class="row">
    <div class="field">
      <label>Categoría</label>
      <div class="small"><?= h($report['category_name'] ?? '-') ?></div>
    </div>
    <div class="field">
      <label>Fecha del evento</label>
      <div class="small"><?= h($report['occurred_at'] ?? '-') ?></div>
    </div>
  </div>

  <div class="field">
    <label>Título</label>
    <div class="small"><?= h($report['subject']) ?></div>
  </div>

  <div class="field">
    <label>Lugar</label>
    <div class="small"><?= h($report['location'] ?? '-') ?></div>
  </div>

  <div class="field">
    <label>Descripción</label>
    <div class="small" style="white-space:pre-wrap"><?= h($report['description']) ?></div>
  </div>

  <div class="hr"></div>

  <h3>Mensajes</h3>

  <?php if (!empty($errors)): ?>
    <div class="alert">
      <b>No se pudo enviar el mensaje:</b>
      <ul class="list"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php elseif ($posted_ok): ?>
    <div class="alert ok"><b>Mensaje enviado.</b></div>
  <?php endif; ?>

  <?php if (empty($messages)): ?>
    <div class="small">Aún no hay mensajes.</div>
  <?php else: ?>
    <div class="list">
      <?php foreach ($messages as $m): ?>
        <div class="card" style="margin:10px 0; padding:14px;">
          <div class="small" style="display:flex; justify-content:space-between; gap:12px;">
            <span><b><?= h($m['sender_type']) ?></b></span>
            <span><?= h($m['created_at']) ?></span>
          </div>
          <div style="white-space:pre-wrap; margin-top:8px;"><?= h($m['message']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="hr"></div>

  <form method="post" action="">
    <div class="field">
      <label>Agregar información / mensaje</label>
      <textarea name="message" placeholder="Escribe un mensaje..."></textarea>
      <div class="small">No incluyas datos sensibles.</div>
    </div>
    <div class="btnrow">
      <button class="btn" type="submit">Enviar</button>
      <a class="btn secondary" href="<?= h(base_url()) ?>/salir.php">Salir</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . "/_footer.php"; ?>
