<?php
$page_title = "Caso";
require_once __DIR__ . "/_header.php";

$db = db_conn();

$report_id = (int)($_SESSION['denuncia_report_id'] ?? 0);
$case_ok   = (bool)($_SESSION['denuncia_case_ok'] ?? false);

if ($report_id <= 0 || !$case_ok) {
  redirect("/seguimiento.php");
}

// Session timeout (30 minutes)
// FIX: lần đầu vào case (last_seen=0) không nên bị expire ngay
$now = time();
$last_seen = (int)($_SESSION['denuncia_case_last_seen'] ?? 0);

if ($last_seen > 0 && ($now - $last_seen) > 30 * 60) {
  $_SESSION['denuncia_case_ok'] = false;
  audit_log($db, $report_id, 'CASE_SESSION_EXPIRED', 'REPORTER', null, []);
  redirect("/seguimiento.php");
}
// refresh activity timestamp
$_SESSION['denuncia_case_last_seen'] = $now;

function location_type_label(?string $v): string {
  $v = (string)$v;
  return match($v) {
    'COMPANY' => 'Dependencias empresa',
    'PROJECT' => 'Faena / Proyecto',
    'REMOTE'  => 'Teletrabajo',
    'OTHER'   => 'Otro',
    default   => ($v !== '' ? $v : '-'),
  };
}

// Load report (include category code)
$sql = "
  SELECT r.*,
         c.name AS company_name, c.logo_path AS company_logo,
         cat.name AS category_name,
         cat.code AS category_code
  FROM portal_report r
  JOIN portal_company c ON c.id = r.company_id
  LEFT JOIN portal_category cat ON cat.id = r.category_id
  WHERE r.id = ?
  LIMIT 1
";
$stmt = $db->prepare($sql);
if (!$stmt) die("DB prepare error (load report): " . h($db->error));
$stmt->bind_param("i", $report_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$report) redirect("/seguimiento.php");

// channel meta
$channel = category_channel_from_code($report['category_code'] ?? '');
$meta = channel_meta($channel);

// log view once/session
$view_key = "case_viewed_" . $report_id;
if (empty($_SESSION[$view_key])) {
  $_SESSION[$view_key] = 1;
  audit_log($db, $report_id, 'CASE_VIEW', 'REPORTER', null, ['channel' => $channel]);
}

$company_logo_url = '';
if (!empty($report['company_logo'])) {
  $company_logo_url = rtrim(base_url(), '/') . '/' . ltrim($report['company_logo'], '/');
}

// Load evidence selected
$evidence = [];
$stmt = $db->prepare("
  SELECT et.name
  FROM portal_report_evidence re
  JOIN portal_evidence_type et ON et.id = re.evidence_type_id
  WHERE re.report_id = ?
  ORDER BY et.id
");
if ($stmt) {
  if ($stmt->bind_param("i", $report_id) && $stmt->execute()) {
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $evidence[] = $r['name'];
  }
  $stmt->close();
}

// Load persons
$accused = [];
$witness = [];
$stmt = $db->prepare("
  SELECT role, full_name, position, company, notes
  FROM portal_report_person
  WHERE report_id = ?
  ORDER BY id ASC
");
if ($stmt) {
  if ($stmt->bind_param("i", $report_id) && $stmt->execute()) {
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
      if (($r['role'] ?? '') === 'ACCUSED') $accused[] = $r;
      if (($r['role'] ?? '') === 'WITNESS') $witness[] = $r;
    }
  }
  $stmt->close();
}

// Load attachments (ADD error handling)
$attachments = [];
$attachments_error = '';

$stmt = $db->prepare("
  SELECT id, original_name, mime_type, size_bytes, created_at
  FROM portal_report_attachment
  WHERE report_id = ?
  ORDER BY id ASC
");
if (!$stmt) {
  $attachments_error = "DB prepare error (load attachments): " . $db->error;
} else {
  $stmt->bind_param("i", $report_id);
  if (!$stmt->execute()) {
    $attachments_error = "DB execute error (load attachments): " . $stmt->error;
  } else {
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $attachments[] = $r;
  }
  $stmt->close();
}

// Handle new message
$errors = [];
$posted_ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $msg = trim((string)($_POST['message'] ?? ''));

  if (mb_strlen($msg) < 5) $errors[] = "El mensaje es demasiado corto.";
  if (mb_strlen($msg) > 2000) $errors[] = "El mensaje es demasiado largo (máx 2000).";

  if (empty($errors)) {
    $stmt = $db->prepare("INSERT INTO portal_report_message (report_id, sender_type, message) VALUES (?, 'REPORTER', ?)");
    if (!$stmt) {
      $errors[] = "DB prepare error (insert message): " . $db->error;
    } else {
      $stmt->bind_param("is", $report_id, $msg);
      if (!$stmt->execute()) $errors[] = "DB error (insert message): " . $stmt->error;
      $stmt->close();
    }

    if (empty($errors)) {
      $stmt = $db->prepare("UPDATE portal_report SET updated_at = NOW() WHERE id = ?");
      if ($stmt) {
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $stmt->close();
      }
      $posted_ok = true;
      audit_log($db, $report_id, 'REPORTER_MESSAGE', 'REPORTER', null, ['len' => mb_strlen($msg)]);
      $_SESSION['denuncia_case_last_seen'] = time();
    }
  }
}

// Load messages
$messages = [];
$stmt = $db->prepare("
  SELECT id, sender_type, message, created_at
  FROM portal_report_message
  WHERE report_id = ?
  ORDER BY created_at ASC, id ASC
");
if (!$stmt) die("DB prepare error (load messages): " . h($db->error));
$stmt->bind_param("i", $report_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $messages[] = $row;
$stmt->close();

// Webgrafía (from DB if you use portal_resource)
$resources = portal_get_resources($db, (int)$report['company_id']);
?>

<div class="card hero" style="max-width:1040px; margin:0 auto;">
  <h2><?= h($meta['title']) ?></h2>
  <div class="badge" style="margin-top:8px"><?= h($meta['badge']) ?></div>
  <p class="small" style="margin-top:10px"><?= h($meta['message']) ?></p>

  <div class="hr"></div>

  <div class="grid">
    <div class="card" style="box-shadow:none; padding:16px;">
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

      <div class="row">
        <div class="field">
          <label>Tipo de lugar</label>
          <div class="small"><?= h(location_type_label($report['location_type'] ?? '')) ?></div>
        </div>
        <div class="field">
          <label>Área / Unidad</label>
          <div class="small"><?= h($report['area_unit'] ?? '-') ?></div>
        </div>
      </div>

      <div class="field">
        <label>Título</label>
        <div class="small"><?= h($report['subject']) ?></div>
      </div>

      <div class="field">
        <label>Lugar (detalle)</label>
        <div class="small"><?= h($report['location'] ?? '-') ?></div>
      </div>

      <div class="field">
        <label>Descripción</label>
        <div class="small" style="white-space:pre-wrap"><?= h($report['description']) ?></div>
      </div>

      <?php if (!empty($evidence)): ?>
        <div class="field">
          <label>Evidencia seleccionada</label>
          <ul class="list">
            <?php foreach ($evidence as $ev): ?><li><?= h($ev) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (!empty($report['evidence_other_detail'])): ?>
        <div class="field">
          <label>Detalle evidencia “Otro”</label>
          <div class="small" style="white-space:pre-wrap"><?= h($report['evidence_other_detail']) ?></div>
        </div>
      <?php endif; ?>

      <?php if ($attachments_error !== ''): ?>
        <div class="alert" style="margin-top:10px">
          <b>Adjuntos:</b> <?= h($attachments_error) ?>
        </div>
      <?php endif; ?>

      <div class="field">
        <label>Respaldo probatorio (archivos adjuntos)</label>

        <?php if (empty($attachments)): ?>
          <div class="small">No hay archivos adjuntos en este caso.</div>
        <?php else: ?>
          <ul class="list">
            <?php foreach ($attachments as $a): ?>
              <li>
                <a href="<?= h(base_url()) ?>/download.php?id=<?= (int)$a['id'] ?>" target="_blank" rel="noopener">
                  <?= h($a['original_name']) ?>
                </a>
                <span class="small"> (<?= h($a['mime_type']) ?>, <?= (int)$a['size_bytes'] ?> bytes)</span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <div class="card" style="box-shadow:none; padding:16px;">
      <details open>
        <summary><b>Garantías del Denunciante</b></summary>
        <ul class="list" style="margin-top:10px">
          <li>Confidencialidad</li>
          <li>Posibilidad de anonimato</li>
          <li>Prohibición de represalias</li>
          <li>Protección de datos (Ley 19.628)</li>
          <li>Investigación independiente según procedimiento interno</li>
        </ul>
      </details>

      <div class="hr"></div>

      <details open>
        <summary><b>Flujo del Procedimiento</b></summary>
        <ol class="list" style="margin-top:10px">
          <li>Recepción</li>
          <li>Evaluación de admisibilidad</li>
          <li>Investigación formal</li>
          <li>Informe técnico</li>
          <li>Medidas disciplinarias / correctivas</li>
          <li>Registro y cierre</li>
        </ol>
      </details>

      <div class="hr"></div>

      <details>
        <summary><b>Marco Legal Aplicable (Webgrafía)</b></summary>
        <ul class="list" style="margin-top:10px">
          <?php if ($channel === 'CMP'): ?>
            <li>Ley 20.393 / Ley 21.595</li>
            <li>Unidad de Análisis Financiero (UAF)</li>
            <li>ISO 37301 – Compliance Management</li>
            <li>ISO 37001 – Antisoborno</li>
          <?php else: ?>
            <li>Ley 21.643 (Ley Karin)</li>
            <li>Dirección del Trabajo</li>
            <li>Biblioteca del Congreso Nacional</li>
            <li>Ministerio del Trabajo y Previsión Social</li>
          <?php endif; ?>

          <?php if (!empty($resources)): ?>
            <li><b>Recursos internos:</b></li>
            <?php foreach ($resources as $r): ?>
              <li><a href="<?= h($r['url']) ?>" target="_blank" rel="noopener"><?= h($r['title']) ?></a></li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </details>

      <div class="hr"></div>

      <?php if (!empty($accused) || !empty($witness)): ?>
        <details>
          <summary><b>Personas involucradas</b></summary>

          <?php if (!empty($accused)): ?>
            <div class="small" style="margin-top:10px"><b>Denunciado(s)</b></div>
            <ul class="list">
              <?php foreach ($accused as $p): ?>
                <li>
                  <?= h($p['full_name'] ?? '-') ?>
                  <?php if (!empty($p['position'])): ?> — <?= h($p['position']) ?><?php endif; ?>
                  <?php if (!empty($p['company'])): ?> (<?= h($p['company']) ?>)<?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <?php if (!empty($witness)): ?>
            <div class="small" style="margin-top:10px"><b>Testigo(s)</b></div>
            <ul class="list">
              <?php foreach ($witness as $p): ?>
                <li>
                  <?= h($p['full_name'] ?? '-') ?>
                  <?php if (!empty($p['position'])): ?> — <?= h($p['position']) ?><?php endif; ?>
                  <?php if (!empty($p['company'])): ?> (<?= h($p['company']) ?>)<?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </details>
      <?php endif; ?>
    </div>
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
    <?php foreach ($messages as $m): ?>
      <div class="card" style="margin:10px 0; padding:14px; box-shadow:none;">
        <div class="small" style="display:flex; justify-content:space-between; gap:12px;">
          <span><b><?= h($m['sender_type']) ?></b></span>
          <span><?= h($m['created_at']) ?></span>
        </div>
        <div style="white-space:pre-wrap; margin-top:8px;"><?= h($m['message']) ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="hr"></div>

  <form method="post" action="">
    <div class="field">
      <label>Agregar información / mensaje</label>
      <textarea name="message" placeholder="Escribe un mensaje..."></textarea>
      <div class="small">No incluyas datos sensibles.</div>
    </div>
    <div class="btnrow">
      <button class="btn primary" type="submit">Enviar</button>
      <a class="btn secondary" href="<?= h(base_url()) ?>/salir.php">Salir</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . "/_footer.php"; ?>
