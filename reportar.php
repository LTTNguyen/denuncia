<?php
$page_title = "Reportar";
require_once __DIR__ . "/_header.php";

$db = db_conn();
$companies = get_companies($db);

// --- Company is chosen in Inicio, so we LOCK it here ---
$company_id_get  = isset($_GET['company_id']) ? (int)$_GET['company_id'] : 0;
$company_id_post = (int)($_POST['company_id'] ?? 0);
$company_locked  = ($company_id_get > 0);

$company_id = $company_locked ? $company_id_get : $company_id_post;
if ($company_id <= 0 && !empty($companies)) $company_id = (int)$companies[0]['id'];

$company = portal_find_company($companies, $company_id);
if (!$company && !empty($companies)) {
  $company_id = (int)$companies[0]['id'];
  $company = portal_find_company($companies, $company_id);
}

$company_logo = $company ? portal_company_logo_path($company) : '';
$company_logo_url = $company_logo ? (rtrim(base_url(), '/') . '/' . ltrim($company_logo, '/')) : '';

$categories = $company_id > 0 ? get_categories($db, $company_id) : [];

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Enforce locked company on server side (avoid tampering)
  $company_id = $company_locked ? $company_id_get : (int)($_POST['company_id'] ?? 0);

  $category_id = (int)($_POST['category_id'] ?? 0);

  // DB column is "subject" (not title). Accept both to be safe.
  $subject = trim((string)($_POST['subject'] ?? ($_POST['title'] ?? '')));

  $description = trim((string)($_POST['description'] ?? ''));
  $location    = trim((string)($_POST['location'] ?? ''));

  // UI input name can stay "event_date", but DB column is occurred_at (datetime)
  $event_date  = trim((string)($_POST['event_date'] ?? '')); // YYYY-MM-DD optional
  $occurred_at = null; // datetime or null

  $is_anonymous   = isset($_POST['is_anonymous']) ? 1 : 0;
  $reporter_name  = trim((string)($_POST['reporter_name'] ?? ''));
  $reporter_email = trim((string)($_POST['reporter_email'] ?? ''));

  $pw  = (string)($_POST['password'] ?? '');
  $pw2 = (string)($_POST['password2'] ?? '');

  if ($company_id <= 0)  $errors[] = "Por favor selecciona una empresa.";
  if ($category_id <= 0) $errors[] = "Por favor selecciona una categoría.";
  if ($subject === '')   $errors[] = "Por favor ingresa un título.";

  if (mb_strlen($description) < 15)   $errors[] = "La descripción es demasiado corta (mínimo 15 caracteres).";
  if (mb_strlen($description) > 1000) $errors[] = "La descripción no puede superar 1000 caracteres.";

  if (strlen($pw) < 6) $errors[] = "La contraseña debe tener al menos 6 caracteres.";
  if ($pw !== $pw2)    $errors[] = "La confirmación de contraseña no coincide.";

  if ($event_date !== '') {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
      $errors[] = "La fecha del evento debe estar en formato AAAA-MM-DD (o dejarse en blanco).";
    } else {
      $occurred_at = $event_date . " 00:00:00";
    }
  }

  if ($is_anonymous === 0) {
    if ($reporter_name === '') $errors[] = "Si no es anónima, por favor ingresa tu nombre.";
    if ($reporter_email !== '' && !filter_var($reporter_email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = "El correo no es válido.";
    }
  } else {
    $reporter_name = '';
    $reporter_email = '';
  }

  // Validate category belongs to selected company
  if (empty($errors)) {
    $stmt = $db->prepare("SELECT 1 FROM portal_category WHERE id=? AND company_id=? AND is_active=1");
    if (!$stmt) {
      $errors[] = "DB prepare error (validate category): " . $db->error;
    } else {
      $stmt->bind_param("ii", $category_id, $company_id);
      $stmt->execute();
      $ok = (bool)$stmt->get_result()->fetch_row();
      $stmt->close();
      if (!$ok) $errors[] = "La categoría no es válida para la empresa seleccionada.";
    }
  }

  if (empty($errors)) {
    // Unique report_key
    $report_key = "";
    for ($i=0; $i<5; $i++) {
      $cand = gen_report_key(10);
      $stmt = $db->prepare("SELECT id FROM portal_report WHERE report_key=? LIMIT 1");
      if (!$stmt) { $errors[] = "DB prepare error (check clave): " . $db->error; break; }
      $stmt->bind_param("s", $cand);
      $stmt->execute();
      $exists = $stmt->get_result()->fetch_row();
      $stmt->close();
      if (!$exists) { $report_key = $cand; break; }
    }
    if ($report_key === "" && empty($errors)) $errors[] = "No se pudo generar la clave (intenta nuevamente).";
  }

  if (empty($errors)) {
    $hash = password_hash($pw, PASSWORD_DEFAULT);

    $sql = "
      INSERT INTO portal_report
        (company_id, category_id, report_key, password_hash, is_anonymous,
         reporter_name, reporter_email, subject, description, location, occurred_at, status)
      VALUES (?,?,?,?,?,?,?,?,?,?,?, 'NEW')
    ";

    $stmt = $db->prepare($sql);
    if (!$stmt) {
      $errors[] = "DB prepare error (insert report): " . $db->error;
    } else {
      $occurred_at_str = $occurred_at; // string or null

      $stmt->bind_param(
        "iississssss",
        $company_id,
        $category_id,
        $report_key,
        $hash,
        $is_anonymous,
        $reporter_name,
        $reporter_email,
        $subject,
        $description,
        $location,
        $occurred_at_str
      );

      if (!$stmt->execute()) {
        $errors[] = "DB error (insert report): " . $stmt->error;
      }
      $new_id = (int)$stmt->insert_id;
      $stmt->close();

      if (empty($errors)) {
        $stmt = $db->prepare("
          INSERT INTO portal_report_message (report_id, sender_type, message)
          VALUES (?, 'REPORTER', ?)
        ");
        if (!$stmt) {
          $errors[] = "DB prepare error (insert message): " . $db->error;
        } else {
          $stmt->bind_param("is", $new_id, $description);
          if (!$stmt->execute()) $errors[] = "DB error (insert message): " . $stmt->error;
          $stmt->close();
        }

        if (empty($errors)) {
          $success = ['report_key'=>$report_key, 'company_id'=>$company_id];
        }
      }
    }
  }
}
?>

<style>
/* FIX 2 + FIX 3: checkbox xuống dưới text + đồng bộ nút + hover */
.checkline{
  display:flex;
  align-items:center;
  gap:10px;
  margin-top:10px;
  font-weight:600;
}
.checkline input{
  width:18px; height:18px;
  margin:0;
}
.btnrow .btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  font: inherit;
  font-weight: 600;
  transition: background-color .15s ease, box-shadow .15s ease, transform .05s ease, filter .15s ease;
}
.btnrow .btn:hover{
  transform: translateY(-1px);
  box-shadow: 0 10px 22px rgba(0,0,0,.08);
  filter: brightness(.98);
}
.btnrow .btn:active{ transform: translateY(0); }
</style>

<div class="card hero">
  <h2>Reportar</h2>

  <?php if ($company): ?>
    <div class="company-chip">
      <?php if ($company_logo_url): ?><img src="<?= h($company_logo_url) ?>" alt="<?= h($company['name']) ?>"><?php endif; ?>
      <div class="company-name"><?= h($company['name']) ?></div>
    </div>
  <?php endif; ?>

  <p class="small">Describe claramente: quién/qué/cuándo/dónde. Demo: no ingreses datos sensibles reales.</p>

  <?php if (!empty($errors)): ?>
    <div class="alert">
      <b>No se pudo enviar la denuncia:</b>
      <ul class="list"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert ok">
      <b>¡Enviado correctamente!</b><br>
      Clave de Reporte: <b style="font-size:18px"><?= h($success['report_key']) ?></b>
      <div class="small" style="margin-top:8px">Guarda la Clave y la contraseña para hacer seguimiento.</div>

      <div class="btnrow">
        <a class="btn" href="<?= h(base_url()) ?>/seguimiento.php?key=<?= h($success['report_key']) ?>">Ir a Seguimiento</a>
        <a class="btn secondary" href="<?= h(base_url()) ?>/reportar.php?company_id=<?= (int)$success['company_id'] ?>">Crear otra denuncia</a>
      </div>
    </div>
  <?php endif; ?>

  <form method="post" action="">
    <input type="hidden" name="company_id" value="<?= (int)$company_id ?>">

    <div class="row">
      <div class="field">
        <label>Empresa</label>

        <?php if ($company_locked): ?>
          <select disabled>
            <option><?= h($company['name'] ?? 'Empresa') ?></option>
          </select>
          <div class="small">La empresa se selecciona en Inicio.</div>
        <?php else: ?>
          <select name="company_id" id="company_id" onchange="loadCategories(this.value,'category_id')">
            <?php foreach ($companies as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= ((int)$c['id']===$company_id?'selected':'') ?>>
                <?= h($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>

      <div class="field">
        <label>Categoría</label>
        <select name="category_id" id="category_id" <?= empty($categories) ? 'disabled' : '' ?>>
          <?php if (empty($categories)): ?>
            <option value="0">Sin categorías para esta empresa</option>
          <?php else: ?>
            <option value="0" <?= ((int)($_POST['category_id'] ?? 0) === 0 ? 'selected' : '') ?>>— Selecciona —</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>" <?= ((int)($_POST['category_id'] ?? 0)===(int)$cat['id']?'selected':'') ?>>
                <?= h($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
    </div> <!-- đóng .row để layout không bị lệch -->

    <div class="field">
      <label>Título</label>
      <input name="subject" value="<?= h($_POST['subject'] ?? ($_POST['title'] ?? '')) ?>" placeholder="Ej.: Incumplimiento de un procedimiento de seguridad..." />
    </div>

    <div class="row">
      <div class="field">
        <label>Lugar</label>
        <input name="location" value="<?= h($_POST['location'] ?? '') ?>" placeholder="Ej.: Planta A / Oficina..." />
      </div>
      <div class="field">
        <label>Fecha del evento (opcional)</label>
        <input type="text"
                id="event_date"
                name="event_date"
                value="<?= h($_POST['event_date'] ?? '') ?>"
                placeholder="Selecciona fecha…"
                autocomplete="off" />

      </div>
    </div>

    <div class="field">
      <label>Descripción detallada</label>
      <!--  max 1000 + để full ngang (nhờ đóng row ở trên) -->
      <textarea
        name="description"
        id="description"
        maxlength="1000"
        placeholder="Describe lo ocurrido..."
      ><?= h($_POST['description'] ?? '') ?></textarea>

      <div class="small">
        <span id="descCount">0</span>/1000 caracteres (mínimo 15).
      </div>
    </div>

    <div class="hr"></div>

    <!--  checkbox đặt dưới phần text -->
    <div class="field">
      <label>Denuncia anónima</label>
      <div class="small">Si es anónima, no es necesario ingresar nombre/correo.</div>
      <label class="checkline">
        <input type="checkbox" name="is_anonymous" <?= isset($_POST['is_anonymous']) ? 'checked' : '' ?> />
        <span>Marcar como anónima</span>
      </label>
    </div>

    <div class="row">
      <div class="field">
        <label>Nombre (si no es anónima)</label>
        <input name="reporter_name" value="<?= h($_POST['reporter_name'] ?? '') ?>" />
      </div>
      <div class="field">
        <label>Email (opcional)</label>
        <input name="reporter_email" value="<?= h($_POST['reporter_email'] ?? '') ?>" />
      </div>
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

    <div class="btnrow">
      <button class="btn" type="submit">Reportar</button>
      <a class="btn secondary" href="<?= h(base_url()) ?>/">Cancelar</a>
    </div>
  </form>
</div>

<script>
// counter 1000 chars
(function(){
  const ta = document.getElementById('description');
  const out = document.getElementById('descCount');
  if(!ta || !out) return;
  const upd = () => out.textContent = String(ta.value.length);
  ta.addEventListener('input', upd);
  upd();
})();
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
  if (!window.flatpickr) return;

  flatpickr("#event_date", {
    locale: flatpickr.l10ns.es,
    dateFormat: "Y-m-d",   // backend của bạn đang dùng YYYY-MM-DD
    altInput: true,
    altFormat: "d/m/Y",
    showMonths: 2
    // KHÔNG set minDate/maxDate => dùng được mọi năm
  });
});
</script>


<?php require_once __DIR__ . "/_footer.php"; ?>
