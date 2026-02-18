<?php
$body_class = "report";
$page_title = "Reportar";
require_once __DIR__ . "/_header.php";

$db = db_conn();
$company_id = current_company_id(); // single-company

// PHP < 8 fallback (just in case)
if (!function_exists('str_starts_with')) {
  function str_starts_with($haystack, $needle) {
    return $needle === '' || strpos((string)$haystack, (string)$needle) === 0;
  }
}

// Load company
$companies = get_companies($db);
$company = portal_find_company($companies, $company_id);
if (!$company) {
  die("Empresa no configurada. Revisa config_denuncia.php + portal_company.");
}

// CANAL (COMPLIANCE / KARIN)

$canal = strtoupper((string)($_POST['canal'] ?? ($_GET['canal'] ?? 'COMPLIANCE')));
if (!in_array($canal, ['COMPLIANCE','KARIN'], true)) $canal = 'COMPLIANCE';


// Categories (need code for grouping)

$categories = [];
$stmt = $db->prepare("SELECT id, code, name FROM portal_category WHERE company_id=? AND is_active=1 ORDER BY sort_order ASC, name ASC");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) $categories[] = $row;
$stmt->close();

$cats_compliance = [];
$cats_karin = [];
foreach ($categories as $c) {
  $code = (string)($c['code'] ?? '');
  if (str_starts_with($code, 'LK_')) $cats_karin[] = $c;
  else $cats_compliance[] = $c;
}
$cats_for_canal = ($canal === 'KARIN') ? $cats_karin : $cats_compliance;

// Evidence types from DB
$evidence_types = [];
$res = $db->query("SELECT id, code, name FROM portal_evidence_type ORDER BY id");
if ($res) {
  while ($r = $res->fetch_assoc()) $evidence_types[] = $r;
  $res->free();
}

// upload helpers
function upload_dir_for_report(string $report_key): string {
  return __DIR__ . "/uploads/" . preg_replace('/[^a-zA-Z0-9_-]+/', '_', $report_key);
}
function allowed_mime(string $mime): bool {
  return in_array($mime, ['application/pdf','image/jpeg','image/png','video/mp4'], true);
}
function ext_from_mime(string $mime): string {
  // PHP 8+
  return match($mime) {
    'application/pdf' => 'pdf',
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'video/mp4' => 'mp4',
    default => ''
  };
}

// Normalize $_FILES for single/multi
function normalize_files_array(array $files): array {
  // returns list of items: [name,tmp_name,error,size,type?]
  if (!isset($files['name'])) return [];

  $names = $files['name'];
  $tmps  = $files['tmp_name'] ?? null;
  $errs  = $files['error'] ?? null;
  $sizes = $files['size'] ?? null;

  if (!is_array($names)) {
    $names = [$names];
    $tmps  = [$tmps];
    $errs  = [$errs];
    $sizes = [$sizes];
  }

  $out = [];
  $count = count($names);
  for ($i=0; $i<$count; $i++) {
    $out[] = [
      'name'     => (string)($names[$i] ?? ''),
      'tmp_name' => (string)($tmps[$i] ?? ''),
      'error'    => (int)($errs[$i] ?? UPLOAD_ERR_NO_FILE),
      'size'     => (int)($sizes[$i] ?? 0),
    ];
  }
  return $out;
}

$errors = [];
$success = null;


// UI Texts by CANAL

$ui = [];
if ($canal === 'COMPLIANCE') {
  $ui['title'] = "Canal de Denuncias – Modelo de Prevención de Delitos";
  $ui['subtitle'] = "Este canal forma parte del Modelo de Prevención de Delitos implementado conforme a la Ley 20.393 y su reforma por Ley 21.595, garantizando confidencialidad, trazabilidad y protección contra represalias.";
  $ui['what'] = [
    "Cohecho",
    "Administración desleal",
    "Lavado de activos",
    "Receptación",
    "Corrupción entre privados",
    "Fraude contable",
    "Conflictos de interés",
    "Infracciones ambientales",
    "Delitos tributarios",
    "Vulneraciones al Código de Ética",
  ];
} else {
  $ui['title'] = "Canal de Denuncias – Ley Karin (Ley 21.643)";
  $ui['subtitle'] = "Este canal aplica al Protocolo Ley Karin, para prevenir, detectar y gestionar acoso laboral, acoso sexual y violencia en el trabajo, con confidencialidad, trazabilidad y protección contra represalias.";
  $ui['what'] = [
    "Acoso laboral",
    "Acoso sexual",
    "Violencia en el trabajo",
    "Otro (relacionado al Protocolo Ley Karin)",
  ];
}

$ui['guarantees'] = [
  "Confidencialidad",
  "Posibilidad de anonimato",
  "Prohibición de represalias",
  "Protección de datos (Ley 19.628)",
  "Investigación independiente según procedimiento interno",
];

$ui['links'] = [
  ["Biblioteca del Congreso Nacional (BCN)", "https://www.bcn.cl"],
  ["Dirección del Trabajo", "https://www.dt.gob.cl"],
  ["Unidad de Análisis Financiero (UAF)", "https://www.uaf.cl"],
  ["Ministerio del Trabajo y Previsión Social", "https://www.mintrab.gob.cl"],
  ["Consejo para la Transparencia", "https://www.consejotransparencia.cl"],
  ["OCDE – Directrices sobre compliance corporativo", "https://www.oecd.org"],
];

$ui['docs'] = [
  "Política del Canal de Denuncias",
  "Reglamento del Comité de Ética",
  "Procedimiento de Investigación",
  "Matriz de Riesgos Asociada",
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


  // I. Tipo de denuncia

  $category_id = (int)($_POST['category_id'] ?? 0);
  $secondary_classification = trim((string)($_POST['secondary_classification'] ?? ''));


  // Good-faith / terms

  $terms_accepted = isset($_POST['terms_accepted']) ? 1 : 0;


  // II. Lugar / área

  $location = trim((string)($_POST['location'] ?? ''));
  $location_type = (string)($_POST['location_type'] ?? '');
  $valid_loc = ['COMPANY','PROJECT','REMOTE','OTHER'];
  if (!in_array($location_type, $valid_loc, true)) $location_type = null;

  $area_unit = trim((string)($_POST['area_unit'] ?? ''));


  // III. Fecha / tipo evento

  $event_kind = (string)($_POST['event_kind'] ?? 'SINGLE');
  $event_kind = ($event_kind === 'REITERATED') ? 'REITERATED' : 'SINGLE';

  $occurred_at = null;
  $event_period = null;

  $is_date = fn($d) => (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$d);

  if ($event_kind === 'SINGLE') {
    $d = trim((string)($_POST['occurred_date'] ?? ''));
    if ($d === '' || !$is_date($d)) {
      $errors[] = "Debes indicar la fecha del hecho (Hecho único).";
    } else {
      $occurred_at = $d . " 00:00:00";
      $event_period = null;
    }
  } else {
    $from = trim((string)($_POST['period_from'] ?? ''));
    $to   = trim((string)($_POST['period_to'] ?? ''));
    $txt  = trim((string)($_POST['event_period_text'] ?? ''));

    if (($from === '' || !$is_date($from)) && ($txt === '')) {
      $errors[] = "Para 'Hecho reiterado', indica al menos una fecha de inicio o una descripción del período.";
    }

    if ($from !== '' && !$is_date($from)) $errors[] = "Fecha 'Desde' inválida.";
    if ($to !== '' && !$is_date($to)) $errors[] = "Fecha 'Hasta' inválida.";
    if ($from !== '' && $to !== '' && $to < $from) $errors[] = "El período es inválido: 'Hasta' no puede ser anterior a 'Desde'.";

    $pieces = [];
    if ($from !== '') $pieces[] = "Desde {$from}";
    if ($to !== '')   $pieces[] = "Hasta {$to}";
    if ($txt !== '')  $pieces[] = $txt;

    $event_period = trim(implode(" | ", $pieces));
    if ($event_period !== '' && mb_strlen($event_period) > 160) {
      $event_period = mb_substr($event_period, 0, 160);
    }

    if ($from !== '') $occurred_at = $from . " 00:00:00";
  }


  // IV. Relato

  $subject = trim((string)($_POST['subject'] ?? ''));
  $description = trim((string)($_POST['description'] ?? ''));


  // V. Superior informado / protección

  $reported_to_superior = (string)($_POST['reported_to_superior'] ?? 'NA');
  $valid_sup = ['YES','NO','NA'];
  if (!in_array($reported_to_superior, $valid_sup, true)) $reported_to_superior = 'NA';

  $protection_requested = (int)($_POST['protection_requested'] ?? 0);
  $protection_requested = ($protection_requested === 1) ? 1 : 0;

  $protection_detail = trim((string)($_POST['protection_detail'] ?? ''));
  if ($protection_detail === '') $protection_detail = null;
  if ($protection_detail !== null && mb_strlen($protection_detail) > 15) {
    $protection_detail = mb_substr($protection_detail, 0, 15);
  }


  // VI. Identificación denunciante

  $is_anonymous = (int)($_POST['is_anonymous'] ?? 1);
  $is_anonymous = ($is_anonymous === 0) ? 0 : 1;

  $reporter_name  = trim((string)($_POST['reporter_name'] ?? ''));
  $reporter_rut   = trim((string)($_POST['reporter_rut'] ?? ''));
  $reporter_email = trim((string)($_POST['reporter_email'] ?? ''));
  $reporter_cargo = trim((string)($_POST['reporter_cargo'] ?? ''));
  $reporter_phone = trim((string)($_POST['reporter_phone'] ?? ''));

  if ($is_anonymous === 1) {
    $reporter_name = '';
    $reporter_rut = '';
    $reporter_email = '';
    $reporter_cargo = '';
    $reporter_phone = '';
  }


  // VII. Evidence

  $evidence_ids = $_POST['evidence_type_id'] ?? [];
  if (!is_array($evidence_ids)) $evidence_ids = [];
  $evidence_ids = array_values(array_unique(array_map('intval', $evidence_ids)));

  $evidence_other_detail = trim((string)($_POST['evidence_other_detail'] ?? ''));
  if ($evidence_other_detail === '') $evidence_other_detail = null;
  if ($evidence_other_detail !== null && mb_strlen($evidence_other_detail) > 15) {
    $evidence_other_detail = mb_substr($evidence_other_detail, 0, 15);
  }

  // Persons
  $accused_name = $_POST['accused_name'] ?? [];
  $accused_pos  = $_POST['accused_position'] ?? [];
  $accused_comp = $_POST['accused_company'] ?? [];
  $accused_note = $_POST['accused_notes'] ?? [];

  $w_name = $_POST['witness_name'] ?? [];
  $w_pos  = $_POST['witness_position'] ?? [];
  $w_comp = $_POST['witness_company'] ?? [];
  $w_note = $_POST['witness_notes'] ?? [];

  // Password for tracking
  $pw  = (string)($_POST['password'] ?? '');
  $pw2 = (string)($_POST['password2'] ?? '');


  // VALIDATE

  if ($category_id <= 0) $errors[] = "Selecciona una categoría.";
  if ($terms_accepted !== 1) $errors[] = "Debes aceptar la Declaración de Responsabilidad y Buena Fe.";
  if ($area_unit === '') $errors[] = "Área / Unidad es obligatorio.";
  if ($subject === '') $errors[] = "Título es obligatorio.";

  if (mb_strlen($description) < 15) $errors[] = "Relato demasiado corto (recomendado mínimo 15 caracteres).";
  if (mb_strlen($description) > 5000) $errors[] = "Relato no puede superar 5000 caracteres.";

  if (strlen($pw) < 6) $errors[] = "La contraseña debe tener al menos 6 caracteres.";
  if ($pw !== $pw2) $errors[] = "La confirmación de contraseña no coincide.";

  if ($is_anonymous === 0) {
    if ($reporter_name === '') $errors[] = "Nombre completo es obligatorio (denuncia identificada).";
    if ($reporter_rut === '') $errors[] = "RUT es obligatorio (denuncia identificada).";
    if ($reporter_cargo === '') $errors[] = "Cargo es obligatorio (denuncia identificada).";
    if ($reporter_email === '' || !filter_var($reporter_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Correo inválido (denuncia identificada).";
    if ($reporter_phone === '') $errors[] = "Teléfono es obligatorio (denuncia identificada).";
  }

  // Category belongs to company and active + matches canal group
  if (empty($errors)) {
    $stmt = $db->prepare("SELECT code FROM portal_category WHERE id=? AND company_id=? AND is_active=1");
    $stmt->bind_param("ii", $category_id, $company_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
      $errors[] = "Categoría inválida para esta empresa.";
    } else {
      $code = (string)($row['code'] ?? '');
      $is_karin = str_starts_with($code, 'LK_');
      if ($canal === 'KARIN' && !$is_karin) $errors[] = "La categoría seleccionada no corresponde al canal Ley Karin.";
      if ($canal === 'COMPLIANCE' && $is_karin) $errors[] = "La categoría seleccionada no corresponde al canal Compliance.";
    }
  }

  // Validate evidence ids exist
  if (empty($errors) && !empty($evidence_ids)) {
    $in = implode(',', array_fill(0, count($evidence_ids), '?'));
    $types = str_repeat('i', count($evidence_ids));
    $sql = "SELECT id FROM portal_evidence_type WHERE id IN ($in)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$evidence_ids);
    $stmt->execute();
    $found = [];
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) $found[] = (int)$row['id'];
    $stmt->close();
    sort($found);
    $check = $evidence_ids; sort($check);
    if ($found !== $check) $errors[] = "Evidencia seleccionada inválida (IDs no existen).";
  }


  // INSERT

  if (empty($errors)) {

    // unique report key
    $report_key = '';
    for ($i=0; $i<5; $i++) {
      $cand = gen_report_key(10);
      $st = $db->prepare("SELECT 1 FROM portal_report WHERE report_key=? LIMIT 1");
      $st->bind_param("s", $cand);
      $st->execute();
      $ex = (bool)$st->get_result()->fetch_row();
      $st->close();
      if (!$ex) { $report_key = $cand; break; }
    }
    if ($report_key === '') $errors[] = "No se pudo generar clave de seguimiento. Intenta otra vez.";

    if (empty($errors)) {
      $hash = password_hash($pw, PASSWORD_DEFAULT);

      $terms_accepted_at = date('Y-m-d H:i:s');
      $terms_ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
      $terms_ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
      if (strlen($terms_ua) > 255) $terms_ua = substr($terms_ua, 0, 255);

      $status = 'PENDING';

      // Normalize empties to NULL where DB allows
      $secondary_classification = ($secondary_classification === '') ? null : $secondary_classification;
      $location = ($location === '') ? null : $location;
      $location_type = ($location_type === '' ? null : $location_type);

      $db->begin_transaction();

      try {
        $sql = "
          INSERT INTO portal_report
          (company_id, category_id, secondary_classification,
           report_key, password_hash, is_anonymous,
           reporter_name, reporter_rut, reporter_email, reporter_cargo, reporter_phone,
           subject, description,
           location, location_type, area_unit,
           occurred_at, event_kind, event_period,
           reported_to_superior,
           protection_requested, protection_detail,
           status,
           terms_accepted, terms_accepted_at, terms_accepted_ip, terms_accepted_ua,
           evidence_other_detail)
          VALUES
          (?,?,?,
           ?,?,?,
           ?,?,?,?,?,
           ?,?,
           ?,?,?,
           ?,?,?,
           ?,
           ?,?,
           ?,
           ?,?,?,?,
           ?)
        ";
        $stmt = $db->prepare($sql);
        if (!$stmt) throw new Exception("DB prepare error: " . $db->error);

        // 28 params total
        $types = "iisssi" . str_repeat("s", 14) . "ississss";

        $stmt->bind_param(
          $types,
          $company_id,
          $category_id,
          $secondary_classification,

          $report_key,
          $hash,
          $is_anonymous,

          $reporter_name,
          $reporter_rut,
          $reporter_email,
          $reporter_cargo,
          $reporter_phone,

          $subject,
          $description,

          $location,
          $location_type,
          $area_unit,

          $occurred_at,
          $event_kind,
          $event_period,

          $reported_to_superior,

          $protection_requested,
          $protection_detail,

          $status,

          $terms_accepted,
          $terms_accepted_at,
          $terms_ip,
          $terms_ua,

          $evidence_other_detail
        );

        if (!$stmt->execute()) throw new Exception("DB insert report error: " . $stmt->error);
        $report_id = (int)$stmt->insert_id;
        $stmt->close();

        // initial message
        $stmt = $db->prepare("INSERT INTO portal_report_message (report_id, sender_type, message) VALUES (?, 'REPORTER', ?)");
        $stmt->bind_param("is", $report_id, $description);
        if (!$stmt->execute()) throw new Exception("DB insert message error: " . $stmt->error);
        $stmt->close();

        // evidence mapping
        if (!empty($evidence_ids)) {
          $stmt = $db->prepare("INSERT INTO portal_report_evidence (report_id, evidence_type_id) VALUES (?, ?)");
          foreach ($evidence_ids as $eid) {
            $stmt->bind_param("ii", $report_id, $eid);
            if (!$stmt->execute()) throw new Exception("DB insert evidence error: " . $stmt->error);
          }
          $stmt->close();
        }

        // persons
        $insP = $db->prepare("
          INSERT INTO portal_report_person (report_id, role, full_name, position, company, notes)
          VALUES (?, ?, ?, ?, ?, ?)
        ");

        $role = 'ACCUSED';
        if (is_array($accused_name)) {
          $n = count($accused_name);
          for ($i=0; $i<$n; $i++) {
            $fn = trim((string)($accused_name[$i] ?? ''));
            $po = trim((string)($accused_pos[$i] ?? ''));
            $co = trim((string)($accused_comp[$i] ?? ''));
            $no = trim((string)($accused_note[$i] ?? ''));
            if ($fn === '' && $po === '' && $co === '' && $no === '') continue;
            $insP->bind_param("isssss", $report_id, $role, $fn, $po, $co, $no);
            if (!$insP->execute()) throw new Exception("DB insert accused error: " . $insP->error);
          }
        }

        $role = 'WITNESS';
        if (is_array($w_name)) {
          $n = count($w_name);
          for ($i=0; $i<$n; $i++) {
            $fn = trim((string)($w_name[$i] ?? ''));
            $po = trim((string)($w_pos[$i] ?? ''));
            $co = trim((string)($w_comp[$i] ?? ''));
            $no = trim((string)($w_note[$i] ?? ''));
            if ($fn === '' && $po === '' && $co === '' && $no === '') continue;
            $insP->bind_param("isssss", $report_id, $role, $fn, $po, $co, $no);
            if (!$insP->execute()) throw new Exception("DB insert witness error: " . $insP->error);
          }
        }
        $insP->close();


        // attachments (FIXED: supports single + multiple reliably)

        if (!empty($_FILES['attachments']) && isset($_FILES['attachments']['name'])) {
          $max_bytes = 25 * 1024 * 1024; // 25MB/file
          $dir = upload_dir_for_report($report_key);
          if (!is_dir($dir)) @mkdir($dir, 0777, true);
          if (!is_dir($dir) || !is_writable($dir)) {
            throw new Exception("No se pudo crear carpeta uploads (permiso).");
          }

          $items = normalize_files_array($_FILES['attachments']);
          if (!empty($items)) {

            // ini limit helper (optional)
            $ini_max = (int)ini_get('max_file_uploads');
            if ($ini_max > 0 && count($items) > $ini_max) {
              throw new Exception("Demasiados archivos: seleccionaste " . count($items) . " pero el servidor permite max_file_uploads={$ini_max}.");
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);

            $stmtA = $db->prepare("
              INSERT INTO portal_report_attachment (report_id, stored_path, original_name, mime_type, size_bytes, sha256)
              VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($items as $it) {
              $err  = (int)$it['error'];
              $tmp  = (string)$it['tmp_name'];
              $orig = (string)$it['name'];
              $size = (int)$it['size'];

              if ($err === UPLOAD_ERR_NO_FILE) continue;
              if ($err !== UPLOAD_ERR_OK) throw new Exception("Error al subir archivo: {$orig} (código {$err}).");

              if ($size <= 0) continue;
              if ($size > $max_bytes) throw new Exception("Archivo demasiado grande: {$orig} (máx 25MB).");

              $mime = $finfo->file($tmp) ?: 'application/octet-stream';
              if (!allowed_mime($mime)) throw new Exception("Tipo de archivo no permitido: {$orig} ({$mime}).");

              $ext = ext_from_mime($mime);
              if ($ext === '') throw new Exception("No se pudo determinar extensión: {$orig}.");

              $sha = hash_file('sha256', $tmp);

              $safe = bin2hex(random_bytes(10)) . "." . $ext;
              $dest_abs = $dir . "/" . $safe;

              if (!move_uploaded_file($tmp, $dest_abs)) throw new Exception("No se pudo guardar archivo: {$orig}.");

              $stored_rel = "uploads/" . basename($dir) . "/" . $safe;

              $stmtA->bind_param("isssis", $report_id, $stored_rel, $orig, $mime, $size, $sha);
              if (!$stmtA->execute()) throw new Exception("DB insert attachment error: " . $stmtA->error);
            }

            $stmtA->close();
          }
        }

        $db->commit();


        // EMAILS (Case B ready)

        $sent_internal = false;
        $sent_receipt  = false;

        try { $sent_internal = portal_notify_new_report($db, $report_id); }
        catch (Throwable $e) { error_log("[notify_internal] " . $e->getMessage()); }

        try { $sent_receipt = portal_notify_reporter_receipt($db, $report_id); }
        catch (Throwable $e) { error_log("[notify_reporter] " . $e->getMessage()); }

        $success = [
          'report_key' => $report_key,
          'is_anonymous' => $is_anonymous,
          'sent_internal' => $sent_internal,
          'sent_receipt' => $sent_receipt,
        ];

      } catch (Throwable $e) {
        $db->rollback();
        $errors[] = $e->getMessage();
      }
    }
  }
}
?>

<style>
.section-title{ margin:18px 0 8px; font-size:16px; font-weight:900; }
.kpi-note{ font-size:12px; color:var(--muted); line-height:1.5; }
.person-card{
  border:1px solid var(--line);
  border-radius: 16px;
  padding:12px 12px;
  background:#fff;
  box-shadow: var(--shadow-sm);
  margin-top:10px;
}
.channel-switch{ display:flex; gap:10px; flex-wrap:wrap; margin:10px 0 6px; }
.channel-switch a{ text-decoration:none; }
.channel-hint{ margin-top:6px; color:var(--muted); font-size:13px; line-height:1.55; }
</style>

<div class="report-wrap">
  <div class="grid">

    <!-- LEFT: FORM -->
    <div class="card">

      <div class="channel-switch">
        <a class="pill <?= $canal==='COMPLIANCE'?'active':'' ?>" href="<?= h(base_url()) ?>/reportar.php?canal=COMPLIANCE">COMPLIANCE</a>
        <a class="pill <?= $canal==='KARIN'?'active':'' ?>" href="<?= h(base_url()) ?>/reportar.php?canal=KARIN">LEY KARIN</a>
      </div>

      <h2 style="margin:10px 0 6px"><?= h($ui['title']) ?></h2>
      <div class="channel-hint"><?= h($ui['subtitle']) ?></div>

      <?php if (!empty($errors)): ?>
        <div class="alert" style="margin-top:14px">
          <b>No se pudo enviar la denuncia:</b>
          <ul class="list"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert ok" style="margin-top:14px">
          <b>¡Enviado correctamente!</b><br>
          Clave de Reporte: <b style="font-size:18px"><?= h($success['report_key']) ?></b>

          <div class="small" style="margin-top:6px">
            Guarda la clave y la contraseña para seguimiento.
            <?php if (($success['is_anonymous'] ?? 1) === 0): ?>
              <br>
              <?php if (!empty($success['sent_receipt'])): ?>
                 Se envió un comprobante al correo registrado.
              <?php else: ?>
                 Si no recibes el comprobante, revisa Spam / Promociones o confirma la configuración de correo del servidor.
              <?php endif; ?>
            <?php endif; ?>
          </div>

          <div class="btnrow" style="margin-top:10px">
            <a class="btn primary" href="<?= h(base_url()) ?>/seguimiento.php?key=<?= h($success['report_key']) ?>">Ir a Seguimiento</a>
            <a class="btn secondary" href="<?= h(base_url()) ?>/reportar.php?canal=<?= h($canal) ?>">Crear otra denuncia</a>
          </div>
        </div>
      <?php endif; ?>

      <div class="hr"></div>

      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="canal" value="<?= h($canal) ?>">

        <div class="section-title">I. Identificación del Tipo de Denuncia</div>
        <div class="row">
          <div class="field">
            <label>Categoría principal (obligatorio)</label>
            <select name="category_id" required>
              <option value="0">— Selecciona —</option>
              <?php foreach ($cats_for_canal as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= ((int)($_POST['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '') ?>>
                  <?= h($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="kpi-note">Canal seleccionado: <b><?= h($canal) ?></b></div>
          </div>

          <div class="field">
            <label>Clasificación secundaria (opcional)</label>
            <input name="secondary_classification" value="<?= h($_POST['secondary_classification'] ?? '') ?>"
                   placeholder="Ej.: Ley Karin / MDP / Cumplimiento..." />
          </div>
        </div>

        <div class="section-title">II. Antecedentes del Hecho Denunciado</div>
        <div class="row">
          <div class="field">
            <label>Tipo de lugar</label>
            <select name="location_type" id="location_type">
              <option value="">— Selecciona —</option>
              <option value="COMPANY" <?= (($_POST['location_type'] ?? '')==='COMPANY'?'selected':'') ?>>Dependencias empresa</option>
              <option value="PROJECT" <?= (($_POST['location_type'] ?? '')==='PROJECT'?'selected':'') ?>>Faena / Proyecto</option>
              <option value="REMOTE"  <?= (($_POST['location_type'] ?? '')==='REMOTE'?'selected':'') ?>>Teletrabajo</option>
              <option value="OTHER"   <?= (($_POST['location_type'] ?? '')==='OTHER'?'selected':'') ?>>Otro</option>
            </select>
          </div>

          <div class="field">
            <label>Detalle lugar (opcional)</label>
            <input name="location" value="<?= h($_POST['location'] ?? '') ?>" placeholder="Ej.: Oficina técnica, Mina, Patio..." />
          </div>
        </div>

        <div class="field">
          <label>Área / Unidad involucrada (obligatorio)</label>
          <input name="area_unit" value="<?= h($_POST['area_unit'] ?? '') ?>" placeholder="Ej.: Operaciones, RRHH, Finanzas..." required />
        </div>

        <div class="section-title">III. Fecha del hecho</div>
        <div class="field">
          <div class="checkline">
            <label style="display:flex; gap:10px; align-items:center; font-weight:700;">
              <input type="radio" name="event_kind" value="SINGLE" <?= (($_POST['event_kind'] ?? 'SINGLE')!=='REITERATED'?'checked':'') ?>>
              Hecho único
            </label>
            <label style="display:flex; gap:10px; align-items:center; font-weight:700;">
              <input type="radio" name="event_kind" value="REITERATED" <?= (($_POST['event_kind'] ?? '')==='REITERATED'?'checked':'') ?>>
              Hecho reiterado
            </label>
          </div>

          <div id="singleBox" style="margin-top:10px">
            <label>Fecha</label>
            <input type="date" name="occurred_date" value="<?= h($_POST['occurred_date'] ?? '') ?>" />
          </div>

          <div id="reitBox" style="margin-top:10px; display:none">
            <div class="row">
              <div class="field" style="margin:0">
                <label>Desde (opcional)</label>
                <input type="date" name="period_from" value="<?= h($_POST['period_from'] ?? '') ?>" />
              </div>
              <div class="field" style="margin:0">
                <label>Hasta (opcional)</label>
                <input type="date" name="period_to" value="<?= h($_POST['period_to'] ?? '') ?>" />
              </div>
            </div>
            <label style="margin-top:8px">Descripción período (opcional, máx 160)</label>
            <input name="event_period_text" value="<?= h($_POST['event_period_text'] ?? '') ?>"
                   placeholder="Ej.: durante enero-febrero / reiterado semanalmente / etc." />
          </div>
        </div>

        <div class="section-title">IV. Personas involucradas (opcional)</div>

        <div class="field">
          <label>Persona(s) denunciada(s)</label>
          <div id="accusedWrap"></div>
          <div class="btnrow" style="margin-top:10px">
            <button type="button" class="btn secondary" onclick="addRow('accusedWrap','accused')">+ Agregar denunciado</button>
          </div>
        </div>

        <div class="field">
          <label>Testigo(s)</label>
          <div id="witnessWrap"></div>
          <div class="btnrow" style="margin-top:10px">
            <button type="button" class="btn secondary" onclick="addRow('witnessWrap','witness')">+ Agregar testigo</button>
          </div>
        </div>

        <div class="section-title">V. Descripción circunstanciada</div>
        <div class="field">
          <label>Título (obligatorio)</label>
          <input name="subject" value="<?= h($_POST['subject'] ?? '') ?>" required />
        </div>

        <div class="field">
          <label>Relato detallado de los hechos (obligatorio)</label>
          <div class="small" style="margin-bottom:6px">
            Describa cronológicamente los hechos, indicando conductas específicas, fechas, modo de ejecución y cualquier antecedente relevante.
            Se recomienda mínimo 15 caracteres.
          </div>
          <textarea name="description" id="description" maxlength="5000" required><?= h($_POST['description'] ?? '') ?></textarea>
          <div class="small"><span id="descCount">0</span>/5000</div>
        </div>

        <div class="section-title">VI. Evidencia</div>
        <div class="field">
          <div class="checkline" style="flex-wrap:wrap; gap:12px">
            <?php
              $chosen = $_POST['evidence_type_id'] ?? [];
              if (!is_array($chosen)) $chosen = [];
              $chosen = array_map('intval', $chosen);
            ?>
            <?php foreach ($evidence_types as $t): ?>
              <label style="display:flex; gap:10px; align-items:center; font-weight:700;">
                <input type="checkbox" name="evidence_type_id[]" value="<?= (int)$t['id'] ?>"
                  <?= in_array((int)$t['id'], $chosen, true) ? 'checked' : '' ?>>
                <?= h($t['name']) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field">
          <label>Detalle evidencia “Otro” (opcional, máx 15)</label>
          <input name="evidence_other_detail" value="<?= h($_POST['evidence_other_detail'] ?? '') ?>" />
        </div>

        <div class="field">
          <label>Adjuntar archivos (PDF/JPG/PNG/MP4)</label>
          <input id="attachments" type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.mp4" />
          <div class="small">
            Puedes seleccionar múltiples archivos (Ctrl/Shift).
            Si falla por tamaño, revisa php.ini: upload_max_filesize / post_max_size / max_file_uploads.
          </div>
          <div class="small" id="attachInfo" style="margin-top:6px"></div>
        </div>

        <div class="section-title">VII. Superior informado y medidas de protección</div>
        <div class="row">
          <div class="field">
            <label>¿Informado a superior?</label>
            <select name="reported_to_superior">
              <option value="NA"  <?= (($_POST['reported_to_superior'] ?? 'NA')==='NA'?'selected':'') ?>>No aplica</option>
              <option value="YES" <?= (($_POST['reported_to_superior'] ?? '')==='YES'?'selected':'') ?>>Sí</option>
              <option value="NO"  <?= (($_POST['reported_to_superior'] ?? '')==='NO'?'selected':'') ?>>No</option>
            </select>
          </div>

          <div class="field">
            <label>¿Solicita medidas cautelares inmediatas?<?= $canal==='KARIN' ? ' (Ley Karin)' : '' ?></label>
            <select name="protection_requested" id="protection_requested">
              <option value="0" <?= ((int)($_POST['protection_requested'] ?? 0)===0?'selected':'') ?>>No</option>
              <option value="1" <?= ((int)($_POST['protection_requested'] ?? 0)===1?'selected':'') ?>>Sí</option>
            </select>
          </div>
        </div>

        <div class="field" id="protBox" style="display:none">
          <label>Detalle (opcional, máx 15)</label>
          <input name="protection_detail" value="<?= h($_POST['protection_detail'] ?? '') ?>" placeholder="Ej.: riesgo actual / medida solicitada..." />
        </div>

        <div class="hr"></div>

        <div class="section-title">VIII. Identificación del denunciante</div>
        <div class="field">
          <div class="checkline">
            <label style="display:flex; gap:10px; align-items:center; font-weight:800;">
              <input type="radio" name="is_anonymous" value="1" <?= ((int)($_POST['is_anonymous'] ?? 1)===1?'checked':'') ?>>
              Anónima
            </label>
            <label style="display:flex; gap:10px; align-items:center; font-weight:800;">
              <input type="radio" name="is_anonymous" value="0" <?= ((int)($_POST['is_anonymous'] ?? 1)===0?'checked':'') ?>>
              Identificada
            </label>
          </div>
          <div class="small" style="margin-top:6px">
            La identidad será tratada bajo estricta confidencialidad conforme a la Ley 19.628 sobre Protección de Datos Personales.
          </div>
        </div>

        <div id="identifiedBox">
          <div class="row">
            <div class="field">
              <label>Nombre completo</label>
              <input name="reporter_name" id="reporter_name" value="<?= h($_POST['reporter_name'] ?? '') ?>" />
            </div>
            <div class="field">
              <label>RUT</label>
              <input name="reporter_rut" id="reporter_rut" value="<?= h($_POST['reporter_rut'] ?? '') ?>" />
            </div>
          </div>

          <div class="row">
            <div class="field">
              <label>Cargo</label>
              <input name="reporter_cargo" id="reporter_cargo" value="<?= h($_POST['reporter_cargo'] ?? '') ?>" />
            </div>
            <div class="field">
              <label>Correo</label>
              <input name="reporter_email" id="reporter_email" value="<?= h($_POST['reporter_email'] ?? '') ?>" />
            </div>
          </div>

          <div class="field">
            <label>Teléfono</label>
            <input name="reporter_phone" id="reporter_phone" value="<?= h($_POST['reporter_phone'] ?? '') ?>" />
          </div>
        </div>

        <div class="hr"></div>

        <div class="section-title">Declaración de responsabilidad y buena fe</div>
        <div class="small" style="line-height:1.6">
          Declaro bajo mi responsabilidad que los antecedentes proporcionados en esta denuncia corresponden a hechos que estimo veraces,
          relatados de buena fe y sin intención de causar daño injustificado a persona alguna.<br><br>
          Declaro asimismo que comprendo que la presentación de denuncias falsas, maliciosas o realizadas con ánimo de perjudicar
          podrá dar lugar a responsabilidades disciplinarias, civiles o penales conforme a la normativa vigente.<br><br>
          Autorizo el tratamiento de los datos personales contenidos en este formulario exclusivamente para efectos de la investigación interna,
          conforme a la Ley N° 19.628 sobre Protección de la Vida Privada.<br><br>
          Comprendo que la información será evaluada conforme a los procedimientos internos establecidos en el Modelo de Prevención de Delitos
          y/o Protocolo Ley Karin, según corresponda.
        </div>

        <div class="field" style="margin-top:10px">
          <label class="checkline">
            <input type="checkbox" name="terms_accepted" <?= isset($_POST['terms_accepted']) ? 'checked' : '' ?> required>
            <span>Acepto y confirmo la declaración anterior.</span>
          </label>
        </div>

        <div class="row">
          <div class="field">
            <label>Contraseña para seguimiento</label>
            <input type="password" name="password" />
          </div>
          <div class="field">
            <label>Repetir contraseña</label>
            <input type="password" name="password2" />
          </div>
        </div>

        <div class="btnrow" style="margin-top:14px">
          <button class="btn primary" type="submit">Enviar denuncia</button>
          <a class="btn secondary" href="<?= h(base_url()) ?>/">Cancelar</a>
        </div>

      </form>
    </div>

    <!-- RIGHT: SIDEBAR -->
    <div class="card">
      <div class="badge"><?= $canal==='COMPLIANCE' ? 'COMPLIANCE • Ley 20.393 / 21.595' : 'LEY KARIN • Ley 21.643' ?></div>

      <div class="section-title" style="margin-top:12px">¿Qué se puede denunciar?</div>
      <ul class="list">
        <?php foreach ($ui['what'] as $it): ?><li><?= h($it) ?></li><?php endforeach; ?>
      </ul>

      <div class="section-title">Garantías del denunciante</div>
      <ul class="list">
        <?php foreach ($ui['guarantees'] as $it): ?><li><?= h($it) ?></li><?php endforeach; ?>
      </ul>

      <div class="section-title">Marco legal aplicable</div>
      <ul class="list">
        <?php foreach ($ui['links'] as $ln): ?>
          <li><a href="<?= h($ln[1]) ?>" target="_blank" rel="noopener"><?= h($ln[0]) ?></a></li>
        <?php endforeach; ?>
      </ul>

      <div class="section-title">Documentos sugeridos</div>
      <ul class="list">
        <?php foreach ($ui['docs'] as $it): ?><li><?= h($it) ?></li><?php endforeach; ?>
      </ul>

      <div class="hr"></div>
      <div class="small">
        Consejo: guarda la <b>Clave</b> y tu <b>Contraseña</b> para revisar el estado en “Seguimiento”.
      </div>
    </div>

  </div>
</div>

<script>
  // counter
  (function(){
    const ta = document.getElementById('description');
    const out = document.getElementById('descCount');
    if(!ta || !out) return;
    const upd = () => out.textContent = String(ta.value.length);
    ta.addEventListener('input', upd); upd();
  })();

  // event kind toggle
  (function(){
    const radios = document.querySelectorAll('input[name="event_kind"]');
    const single = document.getElementById('singleBox');
    const reit = document.getElementById('reitBox');
    const apply = () => {
      const v = (document.querySelector('input[name="event_kind"]:checked')||{}).value || 'SINGLE';
      single.style.display = (v === 'SINGLE') ? '' : 'none';
      reit.style.display   = (v === 'REITERATED') ? '' : 'none';
    };
    radios.forEach(r => r.addEventListener('change', apply));
    apply();
  })();

  // protection toggle
  (function(){
    const sel = document.getElementById('protection_requested');
    const box = document.getElementById('protBox');
    if(!sel || !box) return;
    const apply = () => box.style.display = (sel.value === '1') ? '' : 'none';
    sel.addEventListener('change', apply); apply();
  })();

  // anonymous toggle
  (function(){
    const radios = document.querySelectorAll('input[name="is_anonymous"]');
    const box = document.getElementById('identifiedBox');
    const ids = ['reporter_name','reporter_rut','reporter_cargo','reporter_email','reporter_phone']
      .map(id => document.getElementById(id)).filter(Boolean);

    const apply = () => {
      const v = (document.querySelector('input[name="is_anonymous"]:checked')||{}).value || '1';
      const anon = (v === '1');
      box.style.display = anon ? 'none' : '';
      ids.forEach(el => el.disabled = anon);
    };
    radios.forEach(r => r.addEventListener('change', apply));
    apply();
  })();

  // file info (selected files)
  (function(){
    const inp = document.getElementById('attachments');
    const out = document.getElementById('attachInfo');
    if(!inp || !out) return;
    const upd = () => {
      const n = (inp.files && inp.files.length) ? inp.files.length : 0;
      if (!n) { out.textContent = ""; return; }
      const names = Array.from(inp.files).slice(0,6).map(f => f.name);
      out.textContent = `${n} archivo(s) seleccionado(s): ` + names.join(', ') + (n > 6 ? '…' : '');
    };
    inp.addEventListener('change', upd);
    upd();
  })();

  // dynamic person rows
  function addRow(wrapId, prefix){
    const wrap = document.getElementById(wrapId);
    const div = document.createElement('div');
    div.className = "person-card";
    div.innerHTML = `
      <div class="row">
        <div class="field" style="margin:0">
          <label>Nombre</label>
          <input name="${prefix}_name[]" />
        </div>
        <div class="field" style="margin:0">
          <label>Cargo</label>
          <input name="${prefix}_position[]" />
        </div>
      </div>
      <div class="row">
        <div class="field" style="margin:0">
          <label>Empresa</label>
          <input name="${prefix}_company[]" />
        </div>
        <div class="field" style="margin:0">
          <label>Notas</label>
          <input name="${prefix}_notes[]" />
        </div>
      </div>
      <div class="btnrow" style="margin-top:10px">
        <button type="button" class="btn secondary" onclick="this.closest('.person-card').remove()">Eliminar</button>
      </div>
    `;
    wrap.appendChild(div);
  }

  // add one row by default
  (function(){
    const a = document.getElementById('accusedWrap');
    const w = document.getElementById('witnessWrap');
    if (a && !a.children.length) addRow('accusedWrap','accused');
    if (w && !w.children.length) addRow('witnessWrap','witness');
  })();
</script>

<?php require_once __DIR__ . "/_footer.php"; ?>
