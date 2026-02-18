<?php
$page_title = "Admin - Caso";
require_once __DIR__ . "/_admin_bootstrap.php";

$db = db_conn();
$admin = admin_require_login($db);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  redirect('/admin/index.php');
  exit;
}

function fetch_report(mysqli $db, int $id): ?array {
  $sql = "SELECT
            r.*,
            c.name AS company_name,
            cat.name AS category_name,
            g.name AS group_name,
            g.law_ref AS law_ref
          FROM portal_report r
          JOIN portal_company c ON c.id = r.company_id
          LEFT JOIN portal_category cat ON cat.id = r.category_id
          LEFT JOIN portal_category_group g ON g.id = cat.group_id
          WHERE r.id = ? LIMIT 1";
  $st = $db->prepare($sql);
  $st->bind_param("i", $id);
  $st->execute();
  $res = $st->get_result();
  $row = $res->fetch_assoc();
  $st->close();
  return $row ?: null;
}

$report = fetch_report($db, $id);
if (!$report) {
  admin_flash_set('warn', 'Caso no encontrado.');
  redirect('/admin/index.php');
  exit;
}

/** Actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!admin_csrf_check($_POST['csrf'] ?? null)) {
    admin_flash_set('warn', 'Sesión inválida. Recarga e intenta nuevamente.');
    redirect('/admin/report_view.php?id='.$id);
    exit;
  }

  $action = (string)($_POST['action'] ?? '');

  if ($action === 'update_status') {
    if (!admin_can_edit($admin)) {
      admin_flash_set('warn', 'Tu rol no permite cambiar estados.');
      redirect('/admin/report_view.php?id='.$id);
      exit;
    }

    $new_status = strtoupper(trim((string)($_POST['new_status'] ?? '')));
    $note = trim((string)($_POST['note'] ?? ''));

    if (!in_array($new_status, admin_statuses(), true)) {
      admin_flash_set('warn', 'Estado inválido.');
      redirect('/admin/report_view.php?id='.$id);
      exit;
    }

    $old_status = strtoupper((string)$report['status']);

    if ($new_status !== $old_status) {
      // Update report
      $st = $db->prepare("UPDATE portal_report SET status = ?, updated_at = NOW() WHERE id = ?");
      $st->bind_param("si", $new_status, $id);
      $st->execute();
      $st->close();

      // Insert history
      $admin_id = (int)$admin['id'];
      $st2 = $db->prepare("INSERT INTO portal_report_status_history (report_id, old_status, new_status, note, changed_by_admin_id)
                           VALUES (?, ?, ?, ?, ?)");
      $st2->bind_param("isssi", $id, $old_status, $new_status, $note, $admin_id);
      $st2->execute();
      $st2->close();

      admin_audit($db, $id, 'ADMIN_STATUS_CHANGE', 'INVESTIGATOR', (string)$admin['email'], [
        'old' => $old_status,
        'new' => $new_status,
        'note' => $note
      ]);

      admin_flash_set('ok', 'Estado actualizado.');
    } else {
      admin_flash_set('warn', 'El estado no cambió.');
    }

    redirect('/admin/report_view.php?id='.$id);
    exit;
  }

  if ($action === 'send_message') {
    if (!admin_can_edit($admin)) {
      admin_flash_set('warn', 'Tu rol no permite enviar mensajes.');
      redirect('/admin/report_view.php?id='.$id);
      exit;
    }

    $msg = trim((string)($_POST['message'] ?? ''));
    if ($msg === '') {
      admin_flash_set('warn', 'Mensaje vacío.');
      redirect('/admin/report_view.php?id='.$id);
      exit;
    }

    $st = $db->prepare("INSERT INTO portal_report_message (report_id, sender_type, message) VALUES (?, 'INVESTIGATOR', ?)");
    $st->bind_param("is", $id, $msg);
    $st->execute();
    $st->close();

    admin_audit($db, $id, 'INVESTIGATOR_MESSAGE', 'INVESTIGATOR', (string)$admin['email'], ['len' => mb_strlen($msg)]);

    admin_flash_set('ok', 'Mensaje enviado.');
    redirect('/admin/report_view.php?id='.$id);
    exit;
  }
}

/** Load related */
$people = [];
$stp = $db->prepare("SELECT * FROM portal_report_person WHERE report_id = ? ORDER BY id ASC");
$stp->bind_param("i", $id);
$stp->execute();
$rsp = $stp->get_result();
while ($row = $rsp->fetch_assoc()) $people[] = $row;
$stp->close();

$evidence = [];
$rse = $db->prepare("SELECT et.code, et.name
                     FROM portal_report_evidence re
                     JOIN portal_evidence_type et ON et.id = re.evidence_type_id
                     WHERE re.report_id = ?
                     ORDER BY et.id ASC");
$rse->bind_param("i", $id);
$rse->execute();
$rese = $rse->get_result();
while ($row = $rese->fetch_assoc()) $evidence[] = $row;
$rse->close();

$attachments = [];
$rsa = $db->prepare("SELECT * FROM portal_report_attachment WHERE report_id = ? ORDER BY id ASC");
$rsa->bind_param("i", $id);
$rsa->execute();
$resa = $rsa->get_result();
while ($row = $resa->fetch_assoc()) $attachments[] = $row;
$rsa->close();

$messages = [];
$rsm = $db->prepare("SELECT * FROM portal_report_message WHERE report_id = ? ORDER BY created_at ASC");
$rsm->bind_param("i", $id);
$rsm->execute();
$resm = $rsm->get_result();
while ($row = $resm->fetch_assoc()) $messages[] = $row;
$rsm->close();

$history = [];
// Requires the new table created in SQL step
$rsh = $db->prepare("SELECT h.*, u.email AS admin_email
                     FROM portal_report_status_history h
                     LEFT JOIN portal_admin_user u ON u.id = h.changed_by_admin_id
                     WHERE h.report_id = ?
                     ORDER BY h.created_at DESC, h.id DESC");
$rsh->bind_param("i", $id);
$rsh->execute();
$resh = $rsh->get_result();
while ($row = $resh->fetch_assoc()) $history[] = $row;
$rsh->close();

require __DIR__ . "/_layout_top.php";
?>

<section class="hero" style="padding-top:26px">
  <h1>Caso <?= h($report['report_key']) ?></h1>
  <p class="small">
    <?= h($report['company_name']) ?> ·
    <?= h($report['group_name'] ?? '-') ?> ·
    <?= h($report['category_name'] ?? '-') ?>
    <?php if (!empty($report['law_ref'])): ?> (<?= h($report['law_ref']) ?>)<?php endif; ?>
  </p>
  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">
    <span class="tag <?= h(admin_status_tag_class((string)$report['status'])) ?>"><?= h(status_label((string)$report['status'])) ?></span>
    <?php if ((int)$report['is_anonymous'] === 1): ?>
      <span class="tag tag-neutral">Anónimo</span>
    <?php endif; ?>
    <span class="tag tag-neutral">Creado: <?= h($report['created_at']) ?></span>
  </div>
</section>

<div class="grid" style="grid-template-columns: 1.2fr .8fr; align-items:start">
  <div class="card">
    <h3 style="margin:0 0 10px">Detalle</h3>
    <div class="kv">
      <div class="k">Asunto</div><div class="v"><?= h($report['subject']) ?></div>
      <div class="k">Descripción</div><div class="v"><?= nl2br(h($report['description'])) ?></div>
      <div class="k">Lugar</div><div class="v"><?= h($report['location'] ?? '-') ?></div>
      <div class="k">Tipo de lugar</div><div class="v"><?= h($report['location_type'] ?? '-') ?></div>
      <div class="k">Área / Unidad</div><div class="v"><?= h($report['area_unit'] ?? '-') ?></div>
      <div class="k">Ocurrió</div><div class="v"><?= h($report['occurred_at'] ?? '-') ?></div>
      <div class="k">Evento</div><div class="v"><?= h($report['event_kind'] ?? '-') ?> <?= !empty($report['event_period']) ? ' · '.h($report['event_period']) : '' ?></div>
      <div class="k">Reportado a superior</div><div class="v"><?= h($report['reported_to_superior'] ?? '-') ?></div>
      <div class="k">Protección solicitada</div><div class="v"><?= ((int)$report['protection_requested']===1) ? 'Sí' : 'No' ?></div>
      <div class="k">Aceptó términos</div><div class="v"><?= ((int)$report['terms_accepted']===1) ? 'Sí' : 'No' ?></div>
    </div>
  </div>

  <div class="card">
    <h3 style="margin:0 0 10px">Acciones</h3>

    <form method="post" class="form-grid" style="margin-bottom:10px">
      <input type="hidden" name="csrf" value="<?= h(admin_csrf_token()) ?>">
      <input type="hidden" name="action" value="update_status">

      <div>
        <label>Cambiar estado</label>
        <select name="new_status" <?= admin_can_edit($admin) ? '' : 'disabled' ?>>
          <?php foreach (admin_statuses() as $s): ?>
            <option value="<?= h($s) ?>" <?= strtoupper((string)$report['status'])===$s ? 'selected' : '' ?>>
              <?= h(status_label($s)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label>Nota interna (opcional)</label>
        <input name="note" placeholder="Motivo / referencia interna..." <?= admin_can_edit($admin) ? '' : 'disabled' ?>>
      </div>

      <button class="btn" type="submit" <?= admin_can_edit($admin) ? '' : 'disabled' ?>>Guardar</button>
    </form>

    <a class="btn btn-ghost" href="<?= admin_link('/admin/index.php') ?>">← Volver</a>
  </div>
</div>

<div class="grid" style="grid-template-columns: 1fr 1fr; align-items:start">
  <div class="card">
    <h3 style="margin:0 0 10px">Personas</h3>
    <?php if (!$people): ?>
      <div class="small">Sin registros.</div>
    <?php else: ?>
      <?php foreach ($people as $p): ?>
        <div class="card" style="padding:12px;margin:0 0 10px">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center">
            <div style="font-weight:800"><?= h($p['full_name'] ?? '-') ?></div>
            <span class="tag tag-neutral"><?= h($p['role']) ?></span>
          </div>
          <div class="small">
            <?= h($p['position'] ?? '-') ?>
            <?php if (!empty($p['company'])): ?> · <?= h($p['company']) ?><?php endif; ?>
          </div>
          <?php if (!empty($p['notes'])): ?>
            <div class="small" style="margin-top:6px"><?= h($p['notes']) ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3 style="margin:0 0 10px">Evidencias / Adjuntos</h3>

    <div style="margin-bottom:10px">
      <div class="small" style="margin-bottom:6px">Evidencias seleccionadas</div>
      <?php if (!$evidence): ?>
        <span class="tag tag-neutral">Ninguna</span>
      <?php else: ?>
        <?php foreach ($evidence as $e): ?>
          <span class="tag tag-neutral"><?= h($e['name']) ?></span>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div>
      <div class="small" style="margin-bottom:6px">Archivos adjuntos</div>
      <?php if (!$attachments): ?>
        <div class="small">Sin archivos.</div>
      <?php else: ?>
        <?php foreach ($attachments as $a): ?>
          <div class="card" style="padding:12px;margin:0 0 10px">
            <div style="font-weight:800"><?= h($a['original_name']) ?></div>
            <div class="small"><?= h($a['mime_type']) ?> · <?= h(admin_format_bytes((int)$a['size_bytes'])) ?></div>
            <div style="margin-top:8px">
              <a class="btn btn-ghost" href="<?= admin_link('/admin/download.php?id='.(int)$a['id']) ?>">Descargar</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="grid" style="grid-template-columns: 1fr 1fr; align-items:start">
  <div class="card">
    <h3 style="margin:0 0 10px">Mensajes</h3>

    <?php if (!$messages): ?>
      <div class="small">Aún no hay mensajes.</div>
    <?php else: ?>
      <?php foreach ($messages as $m): ?>
        <?php $fromInv = ($m['sender_type'] ?? '') === 'INVESTIGATOR'; ?>
        <div class="card" style="padding:12px;margin:0 0 10px;border-left:6px solid <?= $fromInv ? '#4f46e5' : '#16a34a' ?>;">
          <div class="small" style="display:flex;justify-content:space-between;gap:10px">
            <span><?= $fromInv ? 'Investigador' : 'Denunciante' ?></span>
            <span><?= h($m['created_at']) ?></span>
          </div>
          <div style="margin-top:6px"><?= nl2br(h($m['message'])) ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" class="form-grid" style="margin-top:10px">
      <input type="hidden" name="csrf" value="<?= h(admin_csrf_token()) ?>">
      <input type="hidden" name="action" value="send_message">
      <div>
        <label>Enviar mensaje al denunciante</label>
        <textarea name="message" rows="4" placeholder="Escribe aquí..." <?= admin_can_edit($admin) ? '' : 'disabled' ?>></textarea>
      </div>
      <button class="btn" type="submit" <?= admin_can_edit($admin) ? '' : 'disabled' ?>>Enviar</button>
    </form>
  </div>

  <div class="card">
    <h3 style="margin:0 0 10px">Historial de Estado</h3>

    <?php if (!$history): ?>
      <div class="small">Sin historial (verifica que creaste la tabla portal_report_status_history).</div>
    <?php else: ?>
      <div class="timeline">
        <?php foreach ($history as $hrow): ?>
          <div class="item">
            <div class="meta">
              <?= h($hrow['created_at']) ?>
              <?php if (!empty($hrow['admin_email'])): ?>
                · <?= h($hrow['admin_email']) ?>
              <?php endif; ?>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
              <span class="tag <?= h(admin_status_tag_class((string)$hrow['old_status'])) ?>"><?= h(status_label((string)$hrow['old_status'])) ?></span>
              <span class="small">→</span>
              <span class="tag <?= h(admin_status_tag_class((string)$hrow['new_status'])) ?>"><?= h(status_label((string)$hrow['new_status'])) ?></span>
            </div>
            <?php if (!empty($hrow['note'])): ?>
              <div class="small" style="margin-top:8px"><?= h($hrow['note']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . "/_layout_bottom.php"; ?>
