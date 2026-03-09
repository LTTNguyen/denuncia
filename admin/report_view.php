<?php
$page_title = 'Admin - Caso';
require_once __DIR__ . '/_admin_bootstrap.php';

$db = db_conn();
$admin = admin_require_login($db);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  redirect('/admin/index.php');
  exit;
}

function admin_fetch_report(mysqli $db, int $reportId): ?array {
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
          WHERE r.id = ?
          LIMIT 1";
  $st = $db->prepare($sql);
  if (!$st) {
    return null;
  }
  $st->bind_param('i', $reportId);
  $st->execute();
  $res = $st->get_result();
  $row = $res ? $res->fetch_assoc() : null;
  $st->close();
  return $row ?: null;
}

$report = admin_fetch_report($db, $id);
if (!$report) {
  admin_flash_set('warn', 'Caso no encontrado.');
  redirect('/admin/index.php');
  exit;
}

$currentStatus = strtoupper((string)($report['status'] ?? 'NEW'));
$allowedNextStatuses = admin_allowed_next_statuses($currentStatus, $admin);
$canEdit = admin_can_edit($admin);
$canMessage = admin_can_message($admin) && $currentStatus !== 'ARCHIVED';
$canDownloadAttachments = admin_can_download_attachments($admin);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!admin_csrf_check($_POST['csrf'] ?? null)) {
    admin_flash_set('warn', 'Sesión inválida. Recarga e intenta nuevamente.');
    redirect('/admin/report_view.php?id=' . $id);
    exit;
  }

  $action = (string)($_POST['action'] ?? '');

  if ($action === 'update_status') {
    if (!$canEdit) {
      admin_flash_set('warn', 'Tu rol no permite cambiar estados.');
      redirect('/admin/report_view.php?id=' . $id);
      exit;
    }

    $newStatus = strtoupper(trim((string)($_POST['new_status'] ?? '')));
    $note = trim((string)($_POST['note'] ?? ''));
    if (mb_strlen($note) > 500) {
      $note = mb_substr($note, 0, 500);
    }

    if (!admin_transition_allowed($currentStatus, $newStatus, $admin)) {
      admin_flash_set('warn', 'Transición de estado no permitida para tu rol o para el estado actual.');
      redirect('/admin/report_view.php?id=' . $id);
      exit;
    }

    $db->begin_transaction();
    try {
      $st = $db->prepare('UPDATE portal_report SET status = ?, updated_at = NOW() WHERE id = ?');
      if (!$st) {
        throw new RuntimeException('No fue posible actualizar el estado.');
      }
      $st->bind_param('si', $newStatus, $id);
      $st->execute();
      $st->close();

      if (portal_db_table_exists($db, 'portal_report_status_history')) {
        $st2 = $db->prepare('INSERT INTO portal_report_status_history (report_id, old_status, new_status, note, changed_by_admin_id) VALUES (?, ?, ?, ?, ?)');
        if ($st2) {
          $adminId = (int)$admin['id'];
          $st2->bind_param('isssi', $id, $currentStatus, $newStatus, $note, $adminId);
          $st2->execute();
          $st2->close();
        }
      }

      admin_audit($db, $id, 'ADMIN_STATUS_CHANGE', 'INVESTIGATOR', (string)$admin['email'], [
        'old' => $currentStatus,
        'new' => $newStatus,
        'note' => $note,
      ]);

      $db->commit();
      admin_flash_set('ok', 'Estado actualizado a ' . admin_status_label($newStatus) . '.');
    } catch (Throwable $e) {
      $db->rollback();
      admin_flash_set('warn', 'No fue posible actualizar el estado.');
    }

    redirect('/admin/report_view.php?id=' . $id);
    exit;
  }

  if ($action === 'send_message') {
    if (!$canMessage) {
      admin_flash_set('warn', 'Tu rol o el estado actual no permiten enviar mensajes.');
      redirect('/admin/report_view.php?id=' . $id);
      exit;
    }

    $message = trim((string)($_POST['message'] ?? ''));
    if ($message === '') {
      admin_flash_set('warn', 'Mensaje vacío.');
      redirect('/admin/report_view.php?id=' . $id);
      exit;
    }
    if (mb_strlen($message) > 8000) {
      $message = mb_substr($message, 0, 8000);
    }

    $db->begin_transaction();
    try {
      $st = $db->prepare("INSERT INTO portal_report_message (report_id, sender_type, message) VALUES (?, 'INVESTIGATOR', ?)");
      if (!$st) {
        throw new RuntimeException('No fue posible enviar el mensaje.');
      }
      $st->bind_param('is', $id, $message);
      $st->execute();
      $st->close();

      $st2 = $db->prepare('UPDATE portal_report SET updated_at = NOW() WHERE id = ?');
      if ($st2) {
        $st2->bind_param('i', $id);
        $st2->execute();
        $st2->close();
      }

      admin_audit($db, $id, 'INVESTIGATOR_MESSAGE', 'INVESTIGATOR', (string)$admin['email'], [
        'length' => mb_strlen($message),
      ]);

      $db->commit();
      admin_flash_set('ok', 'Mensaje enviado al denunciante.');
    } catch (Throwable $e) {
      $db->rollback();
      admin_flash_set('warn', 'No fue posible enviar el mensaje.');
    }

    redirect('/admin/report_view.php?id=' . $id);
    exit;
  }
}

$people = [];
$stPeople = $db->prepare('SELECT * FROM portal_report_person WHERE report_id = ? ORDER BY id ASC');
if ($stPeople) {
  $stPeople->bind_param('i', $id);
  $stPeople->execute();
  $resPeople = $stPeople->get_result();
  while ($row = $resPeople->fetch_assoc()) {
    $people[] = $row;
  }
  $stPeople->close();
}

$evidence = [];
$stEvidence = $db->prepare('SELECT et.code, et.name FROM portal_report_evidence re JOIN portal_evidence_type et ON et.id = re.evidence_type_id WHERE re.report_id = ? ORDER BY et.id ASC');
if ($stEvidence) {
  $stEvidence->bind_param('i', $id);
  $stEvidence->execute();
  $resEvidence = $stEvidence->get_result();
  while ($row = $resEvidence->fetch_assoc()) {
    $evidence[] = $row;
  }
  $stEvidence->close();
}

$attachments = [];
$stAttachments = $db->prepare('SELECT * FROM portal_report_attachment WHERE report_id = ? ORDER BY id ASC');
if ($stAttachments) {
  $stAttachments->bind_param('i', $id);
  $stAttachments->execute();
  $resAttachments = $stAttachments->get_result();
  while ($row = $resAttachments->fetch_assoc()) {
    $attachments[] = $row;
  }
  $stAttachments->close();
}

$messages = [];
$stMessages = $db->prepare('SELECT * FROM portal_report_message WHERE report_id = ? ORDER BY created_at ASC, id ASC');
if ($stMessages) {
  $stMessages->bind_param('i', $id);
  $stMessages->execute();
  $resMessages = $stMessages->get_result();
  while ($row = $resMessages->fetch_assoc()) {
    $messages[] = $row;
  }
  $stMessages->close();
}

$history = [];
$hasHistoryTable = portal_db_table_exists($db, 'portal_report_status_history');
if ($hasHistoryTable) {
  $stHistory = $db->prepare('SELECT h.*, u.email AS admin_email FROM portal_report_status_history h LEFT JOIN portal_admin_user u ON u.id = h.changed_by_admin_id WHERE h.report_id = ? ORDER BY h.created_at DESC, h.id DESC');
  if ($stHistory) {
    $stHistory->bind_param('i', $id);
    $stHistory->execute();
    $resHistory = $stHistory->get_result();
    while ($row = $resHistory->fetch_assoc()) {
      $history[] = $row;
    }
    $stHistory->close();
  }
}

$reporterIdentity = ((int)$report['is_anonymous'] === 1)
  ? 'Denuncia anónima'
  : trim((string)($report['reporter_name'] ?? '') . ((string)($report['reporter_email'] ?? '') !== '' ? ' · ' . (string)$report['reporter_email'] : ''));
if ($reporterIdentity === '') {
  $reporterIdentity = 'Sin identificación informada';
}
$reporterShort = ((int)$report['is_anonymous'] === 1) ? 'Anónima' : 'Identificada';
$lastActivity = (string)($report['updated_at'] ?: $report['created_at']);
$summaryStats = [
  ['label' => 'Mensajes', 'value' => count($messages), 'helper' => 'Intercambios registrados'],
  ['label' => 'Adjuntos', 'value' => count($attachments), 'helper' => 'Archivos asociados'],
  ['label' => 'Evidencias', 'value' => count($evidence), 'helper' => 'Tipos declarados'],
  ['label' => 'Historial', 'value' => count($history), 'helper' => $hasHistoryTable ? 'Cambios de estado' : 'Tabla no disponible'],
];

require __DIR__ . '/_layout_top.php';
?>

<section class="admin-hero">
  <div>
    <div class="admin-hero__eyebrow">Caso <?= h($report['report_key']) ?></div>
    <h1 class="admin-hero__title"><?= h($report['subject']) ?></h1>
    <p class="admin-hero__desc">
      <?= h($report['company_name']) ?> · <?= h($report['group_name'] ?? '-') ?> · <?= h($report['category_name'] ?? '-') ?>
      <?php if (!empty($report['law_ref'])): ?> (<?= h($report['law_ref']) ?>)<?php endif; ?>
    </p>
  </div>
  <div class="admin-hero__chips">
    <span class="admin-chip">Estado: <?= h(admin_status_label((string)$report['status'])) ?></span>
    <span class="admin-chip">Última actividad: <?= h(admin_relative_time($lastActivity)) ?></span>
    <span class="admin-chip">Identificación: <?= h($reporterShort) ?></span>
  </div>
</section>

<section class="admin-summary-strip">
  <?php foreach ($summaryStats as $item): ?>
    <article class="admin-mini-stat">
      <div class="admin-mini-stat__label"><?= h($item['label']) ?></div>
      <div class="admin-mini-stat__value"><?= (int)$item['value'] ?></div>
      <div class="admin-mini-stat__helper"><?= h($item['helper']) ?></div>
    </article>
  <?php endforeach; ?>
</section>

<div class="admin-grid-2">
  <div class="admin-stack">
    <section class="admin-kv-card">
      <div class="admin-panel__head">
        <div>
          <h2 class="admin-panel__title">Detalle del caso</h2>
          <p class="admin-panel__desc">Ficha completa de la denuncia con información declarada por el denunciante.</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <span class="tag <?= h(admin_status_tag_class((string)$report['status'])) ?>"><?= h(admin_status_label((string)$report['status'])) ?></span>
          <?php if ((int)$report['is_anonymous'] === 1): ?><span class="tag tag-neutral">Anónimo</span><?php endif; ?>
        </div>
      </div>

      <div class="admin-kv">
        <div class="admin-kv__label">Key</div><div class="admin-kv__value"><strong><?= h($report['report_key']) ?></strong></div>
        <div class="admin-kv__label">Asunto</div><div class="admin-kv__value"><?= h($report['subject']) ?></div>
        <div class="admin-kv__label">Descripción</div><div class="admin-kv__value"><pre><?= h($report['description']) ?></pre></div>
        <div class="admin-kv__label">Denunciante</div><div class="admin-kv__value"><?= h($reporterIdentity) ?></div>
        <div class="admin-kv__label">Teléfono / cargo</div><div class="admin-kv__value"><?= h(trim((string)($report['reporter_phone'] ?? '') . ((string)($report['reporter_cargo'] ?? '') !== '' ? ' · ' . (string)$report['reporter_cargo'] : '')) ?: '-') ?></div>
        <div class="admin-kv__label">Lugar</div><div class="admin-kv__value"><?= h($report['location'] ?? '-') ?></div>
        <div class="admin-kv__label">Tipo de lugar</div><div class="admin-kv__value"><?= h($report['location_type'] ?? '-') ?></div>
        <div class="admin-kv__label">Área / Unidad</div><div class="admin-kv__value"><?= h($report['area_unit'] ?? '-') ?></div>
        <div class="admin-kv__label">Ocurrió</div><div class="admin-kv__value"><?= h($report['occurred_at'] ?? '-') ?></div>
        <div class="admin-kv__label">Evento</div><div class="admin-kv__value"><?= h($report['event_kind'] ?? '-') ?><?= !empty($report['event_period']) ? ' · ' . h($report['event_period']) : '' ?></div>
        <div class="admin-kv__label">Reportado a superior</div><div class="admin-kv__value"><?= h($report['reported_to_superior'] ?? '-') ?></div>
        <div class="admin-kv__label">Protección solicitada</div><div class="admin-kv__value"><?= ((int)$report['protection_requested'] === 1) ? 'Sí' : 'No' ?></div>
        <div class="admin-kv__label">Detalle protección</div><div class="admin-kv__value"><?= h($report['protection_detail'] ?? '-') ?></div>
        <div class="admin-kv__label">Aceptó términos</div><div class="admin-kv__value"><?= ((int)$report['terms_accepted'] === 1) ? 'Sí' : 'No' ?></div>
        <div class="admin-kv__label">Creado / actualizado</div><div class="admin-kv__value"><?= h($report['created_at']) ?> · <?= h($report['updated_at'] ?? '-') ?></div>
      </div>
    </section>

    <section class="admin-message-box">
      <div class="admin-panel__head">
        <div>
          <h2 class="admin-panel__title">Mensajes</h2>
          <p class="admin-panel__desc">Canal directo entre el equipo investigador y el denunciante.</p>
        </div>
      </div>

      <?php if (!$messages): ?>
        <div class="admin-empty" style="padding:0;box-shadow:none;border:none;background:transparent">
          <div class="admin-empty__title">Aún no hay mensajes.</div>
          <div class="admin-empty__text">Cuando envíes una respuesta desde aquí, aparecerá en esta conversación.</div>
        </div>
      <?php else: ?>
        <div class="admin-message-thread">
          <?php foreach ($messages as $message): ?>
            <?php $fromInvestigator = (($message['sender_type'] ?? '') === 'INVESTIGATOR'); ?>
            <article class="admin-message-item <?= $fromInvestigator ? 'admin-message-item--investigator' : 'admin-message-item--reporter' ?>">
              <div class="admin-message-item__meta">
                <span><?= $fromInvestigator ? 'Investigador' : 'Denunciante' ?></span>
                <span><?= h($message['created_at']) ?></span>
              </div>
              <div class="admin-message-item__body"><?= nl2br(h($message['message'])) ?></div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="admin-message-compose">
        <form method="post" class="admin-stack">
          <input type="hidden" name="csrf" value="<?= h(admin_csrf_token()) ?>">
          <input type="hidden" name="action" value="send_message">
          <div class="admin-field">
            <label>Enviar mensaje al denunciante</label>
            <textarea class="admin-textarea" name="message" rows="5" maxlength="8000" placeholder="Escribe una actualización clara, breve y profesional..." <?= $canMessage ? '' : 'disabled' ?>></textarea>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
            <button class="admin-btn" type="submit" <?= $canMessage ? '' : 'disabled' ?>>Enviar mensaje</button>
            <?php if (!$canMessage): ?><span class="admin-badge-soft">Mensajería deshabilitada para este rol o estado</span><?php endif; ?>
          </div>
        </form>
      </div>
    </section>

    <div class="admin-grid-2 admin-grid-2--equal">
      <section class="admin-section-card">
        <div class="admin-panel__head">
          <div>
            <h2 class="admin-panel__title">Personas vinculadas</h2>
            <p class="admin-panel__desc">Personas informadas dentro del relato o proceso de investigación.</p>
          </div>
        </div>

        <?php if (!$people): ?>
          <div class="admin-empty" style="padding:0;box-shadow:none;border:none;background:transparent">
            <div class="admin-empty__title">Sin registros asociados.</div>
            <div class="admin-empty__text">Este caso no incluye personas vinculadas adicionales.</div>
          </div>
        <?php else: ?>
          <div class="admin-people-list">
            <?php foreach ($people as $person): ?>
              <article class="admin-people-card">
                <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;flex-wrap:wrap">
                  <div class="admin-people-card__name"><?= h($person['full_name'] ?? '-') ?></div>
                  <span class="tag tag-neutral"><?= h($person['role']) ?></span>
                </div>
                <div class="admin-people-card__meta" style="margin-top:8px">
                  <?= h($person['position'] ?? '-') ?>
                  <?php if (!empty($person['company'])): ?> · <?= h($person['company']) ?><?php endif; ?>
                </div>
                <?php if (!empty($person['notes'])): ?>
                  <div class="admin-people-card__meta" style="margin-top:8px"><?= h($person['notes']) ?></div>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <section class="admin-history-box">
        <div class="admin-panel__head">
          <div>
            <h2 class="admin-panel__title">Historial de estado</h2>
            <p class="admin-panel__desc">Trazabilidad de cambios realizados desde el panel administrativo.</p>
          </div>
        </div>

        <?php if (!$hasHistoryTable): ?>
          <div class="admin-empty" style="padding:0;box-shadow:none;border:none;background:transparent">
            <div class="admin-empty__title">Tabla no disponible.</div>
            <div class="admin-empty__text">La tabla <code>portal_report_status_history</code> no existe en esta base de datos.</div>
          </div>
        <?php elseif (!$history): ?>
          <div class="admin-empty" style="padding:0;box-shadow:none;border:none;background:transparent">
            <div class="admin-empty__title">Sin historial todavía.</div>
            <div class="admin-empty__text">Este caso todavía no registra cambios de estado.</div>
          </div>
        <?php else: ?>
          <div class="admin-history-list">
            <?php foreach ($history as $entry): ?>
              <article class="admin-history-item">
                <div class="admin-history-item__meta">
                  <?= h($entry['created_at']) ?><?php if (!empty($entry['admin_email'])): ?> · <?= h($entry['admin_email']) ?><?php endif; ?>
                </div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:8px">
                  <span class="tag <?= h(admin_status_tag_class((string)$entry['old_status'])) ?>"><?= h(admin_status_label((string)$entry['old_status'])) ?></span>
                  <span class="admin-badge-soft">→</span>
                  <span class="tag <?= h(admin_status_tag_class((string)$entry['new_status'])) ?>"><?= h(admin_status_label((string)$entry['new_status'])) ?></span>
                </div>
                <?php if (!empty($entry['note'])): ?>
                  <div class="admin-history-item__meta" style="margin-top:10px"><?= h($entry['note']) ?></div>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </div>

  <aside class="admin-stack admin-sidebar-sticky">
    <section class="admin-panel">
      <div class="admin-panel__head">
        <div>
          <h2 class="admin-panel__title">Acciones del caso</h2>
          <p class="admin-panel__desc">Cambios de estado, control operativo y retorno rápido a la bandeja.</p>
        </div>
      </div>

      <form method="post" class="admin-stack" style="margin-bottom:12px">
        <input type="hidden" name="csrf" value="<?= h(admin_csrf_token()) ?>">
        <input type="hidden" name="action" value="update_status">

        <div class="admin-field">
          <label>Cambiar estado</label>
          <select class="admin-select" name="new_status" <?= $canEdit ? '' : 'disabled' ?>>
            <option value="">Selecciona una transición</option>
            <?php foreach ($allowedNextStatuses as $statusItem): ?>
              <option value="<?= h($statusItem) ?>"><?= h(admin_status_label($statusItem)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="admin-field">
          <label>Nota interna</label>
          <textarea class="admin-textarea" name="note" rows="4" maxlength="500" placeholder="Motivo, referencia o contexto interno..." <?= $canEdit ? '' : 'disabled' ?>></textarea>
        </div>

        <button class="admin-btn" type="submit" <?= ($canEdit && $allowedNextStatuses) ? '' : 'disabled' ?>>Guardar cambio</button>
      </form>

      <div class="admin-divider"></div>
      <div class="admin-field" style="gap:10px">
        <label>Transiciones permitidas</label>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <?php if (!$allowedNextStatuses): ?>
            <span class="tag tag-neutral">Sin transiciones disponibles</span>
          <?php else: ?>
            <?php foreach ($allowedNextStatuses as $statusItem): ?>
              <span class="tag <?= h(admin_status_tag_class($statusItem)) ?>"><?= h(admin_status_label($statusItem)) ?></span>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="admin-divider"></div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a class="admin-btn-secondary" href="<?= admin_link('/admin/index.php') ?>">← Volver a bandeja</a>
      </div>
    </section>

    <section class="admin-section-card">
      <div class="admin-panel__head">
        <div>
          <h2 class="admin-panel__title">Resumen rápido</h2>
          <p class="admin-panel__desc">Contexto legal y operativo para decidir el siguiente paso.</p>
        </div>
      </div>

      <div class="admin-kv" style="grid-template-columns:145px 1fr">
        <div class="admin-kv__label">Empresa</div><div class="admin-kv__value"><?= h($report['company_name']) ?></div>
        <div class="admin-kv__label">Grupo legal</div><div class="admin-kv__value"><?= h($report['group_name'] ?? '-') ?></div>
        <div class="admin-kv__label">Categoría</div><div class="admin-kv__value"><?= h($report['category_name'] ?? '-') ?></div>
        <div class="admin-kv__label">Ley</div><div class="admin-kv__value"><?= h($report['law_ref'] ?? '-') ?></div>
        <div class="admin-kv__label">Evidencias</div><div class="admin-kv__value"><?= $evidence ? count($evidence) . ' seleccionada(s)' : 'Sin evidencias declaradas' ?></div>
        <div class="admin-kv__label">Adjuntos</div><div class="admin-kv__value"><?= count($attachments) ?> archivo(s)</div>
        <div class="admin-kv__label">Mensajes</div><div class="admin-kv__value"><?= count($messages) ?> intercambio(s)</div>
      </div>
    </section>

    <section class="admin-section-card">
      <div class="admin-panel__head">
        <div>
          <h2 class="admin-panel__title">Evidencias y adjuntos</h2>
          <p class="admin-panel__desc">Archivos y evidencias declaradas por el denunciante.</p>
        </div>
      </div>

      <div style="margin-bottom:16px">
        <div class="admin-field" style="gap:10px">
          <label>Evidencias declaradas</label>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <?php if (!$evidence): ?>
              <span class="tag tag-neutral">Ninguna</span>
            <?php else: ?>
              <?php foreach ($evidence as $item): ?>
                <span class="tag tag-neutral"><?= h($item['name']) ?></span>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="admin-field" style="gap:10px">
        <label>Archivos adjuntos</label>
        <?php if (!$attachments): ?>
          <div class="admin-empty" style="padding:0;box-shadow:none;border:none;background:transparent">
            <div class="admin-empty__title">Sin archivos.</div>
            <div class="admin-empty__text">Este caso no contiene adjuntos cargados por el denunciante.</div>
          </div>
        <?php else: ?>
          <div class="admin-attachment-list">
            <?php foreach ($attachments as $attachment): ?>
              <article class="admin-attachment-card">
                <div class="admin-attachment-card__name"><?= h($attachment['original_name']) ?></div>
                <div class="admin-attachment-card__meta" style="margin-top:6px"><?= h($attachment['mime_type']) ?> · <?= h(admin_format_bytes((int)$attachment['size_bytes'])) ?></div>
                <div class="admin-attachment-card__actions">
                  <?php if ($canDownloadAttachments): ?>
                    <a class="admin-btn-secondary" href="<?= admin_link('/admin/download.php?id=' . (int)$attachment['id']) ?>">Descargar</a>
                  <?php else: ?>
                    <button class="admin-btn-secondary" type="button" disabled>Sin permiso</button>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </aside>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
