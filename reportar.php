<?php
$page_title = "Reportar";
require_once __DIR__ . "/_header.php";

$db = db_conn();


$company_id = current_company_id(); // fixed by config: single_company_id = 1

// Load company row (branding + name)
$company = null;
try {
  $companies = get_companies($db);               // returns active companies
  $company   = portal_find_company($companies, $company_id);
} catch (Throwable $e) {
  $company = null;
}

if (!$company || $company_id <= 0) {
  die("No hay empresa configurada para este portal (single-company). Revisa config_denuncia.php y portal_company.");
}

$company_logo = portal_company_logo_path($company);
$company_logo_url = $company_logo ? (rtrim(base_url(), '/') . '/' . ltrim($company_logo, '/')) : '';

$categories = get_categories($db, $company_id);

$errors = [];
$success = null;

// HANDLE POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // company_id is fixed (ignore any posted company_id)
  $category_id  = (int)($_POST['category_id'] ?? 0);
  $subject      = trim((string)($_POST['subject'] ?? ($_POST['title'] ?? '')));
  $description  = trim((string)($_POST['description'] ?? ''));
  $location     = trim((string)($_POST['location'] ?? ''));
  $event_date   = trim((string)($_POST['event_date'] ?? '')); // YYYY-MM-DD optional
  $occurred_at  = null;

  $is_anonymous   = isset($_POST['is_anonymous']) ? 1 : 0;
  $reporter_name  = trim((string)($_POST['reporter_name'] ?? ''));
  $reporter_email = trim((string)($_POST['reporter_email'] ?? ''));
  $pw             = (string)($_POST['password'] ?? '');
  $pw2            = (string)($_POST['password2'] ?? '');

  // Basic validations
  if (empty($categories)) $errors[] = "No hay categorías configuradas para TyM. Contacta al administrador.";
  if ($category_id <= 0) $errors[] = "Por favor selecciona una categoría.";
  if ($subject === '') $errors[] = "Por favor ingresa un título.";
  if (mb_strlen($description) < 15) $errors[] = "La descripción es demasiado corta (mínimo 15 caracteres).";
  if (mb_strlen($description) > 1000) $errors[] = "La descripción no puede superar 1000 caracteres.";
  if (strlen($pw) < 6) $errors[] = "La contraseña debe tener al menos 6 caracteres.";
  if ($pw !== $pw2) $errors[] = "La confirmación de contraseña no coincide.";

  // Optional date validation
  if ($event_date !== '') {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
      $errors[] = "La fecha del evento debe estar en formato AAAA-MM-DD (o dejarse en blanco).";
    } else {
      $occurred_at = $event_date . " 00:00:00";
    }
  }

  // Reporter fields
  if ($is_anonymous === 0) {
    if ($reporter_name === '') $errors[] = "Si no es anónima, por favor ingresa tu nombre.";
    if ($reporter_email !== '' && !filter_var($reporter_email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = "El correo no es válido.";
    }
  } else {
    $reporter_name = '';
    $reporter_email = '';
  }

  // Validate category belongs to TyM company
  if (empty($errors)) {
    $stmt = $db->prepare("SELECT 1 FROM portal_category WHERE id=? AND company_id=? AND is_active=1");
    if (!$stmt) {
      $errors[] = "DB prepare error (validate category): " . $db->error;
    } else {
      $stmt->bind_param("ii", $category_id, $company_id);
      $stmt->execute();
      $ok = (bool)$stmt->get_result()->fetch_row();
      $stmt->close();
      if (!$ok) $errors[] = "La categoría no es válida para TyM.";
    }
  }

  // Create report
  if (empty($errors)) {

    // Unique report_key
    $report_key = "";
    for ($i = 0; $i < 5; $i++) {
      $cand = gen_report_key(10);
      $stmt = $db->prepare("SELECT id FROM portal_report WHERE report_key=? LIMIT 1");
      if (!$stmt) {
        $errors[] = "DB prepare error (check clave): " . $db->error;
        break;
      }
      $stmt->bind_param("s", $cand);
      $stmt->execute();
      $exists = $stmt->get_result()->fetch_row();
      $stmt->close();
      if (!$exists) {
        $report_key = $cand;
        break;
      }
    }
    if ($report_key === "" && empty($errors)) {
      $errors[] = "No se pudo generar la clave (intenta nuevamente).";
    }

    if (empty($errors)) {
      $hash = password_hash($pw, PASSWORD_DEFAULT);

      $sql = "
        INSERT INTO portal_report
        (company_id, category_id, report_key, password_hash, is_anonymous, reporter_name, reporter_email, subject, description, location, occurred_at, status)
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

        // Insert initial message
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
        }

        // Notify by email (optional; ignore failures)
        if (empty($errors)) {
          try {
            portal_notify_new_report($db, $new_id);
          } catch (Throwable $e) {
            error_log("portal_notify_new_report failed: " . $e->getMessage());
          }

          $success = ['report_key' => $report_key];
        }
      }
    }
  }
}
?>

<style>
  .checkline{ display:flex; align-items:center; gap:10px; margin-top:10px; font-weight:600; }
  .checkline input{ width:18px; height:18px; margin:0; }
  .btnrow .btn{
    display:inline-flex; align-items:center; justify-content:center;
    font: inherit; font-weight: 600;
    transition: background-color .15s ease, box-shadow .15s ease, transform .05s ease, filter .15s ease;
  }
  .btnrow .btn:hover{ transform: translateY(-1px); box-shadow: 0 10px 22px rgba(0,0,0,.08); filter: brightness(.98); }
  .btnrow .btn:active{ transform: translateY(0); }
</style>

<div class="report-wrap">

  <div class="card hero">
    <h2>Reportar</h2>

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
          <a class="btn secondary" href="<?= h(base_url()) ?>/reportar.php">Crear otra denuncia</a>
        </div>
      </div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="row">
        <div class="field">
          <label>Categoría</label>
          <select name="category_id" id="category_id" <?= (empty($categories) ? 'disabled' : '') ?>>
            <?php if (empty($categories)): ?>
              <option value="0" selected>Sin categorías configuradas</option>
            <?php else: ?>
              <option value="0" <?= ((int)($_POST['category_id'] ?? 0) === 0 ? 'selected' : '') ?>>— Selecciona —</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= ((int)($_POST['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '') ?>>
                  <?= h($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="field">
          <label>Fecha del evento (opcional)</label>
          <input type="text" id="event_date" name="event_date" class="js-date"
                 value="<?= h($_POST['event_date'] ?? '') ?>" placeholder="Selecciona fecha…" autocomplete="off" />
        </div>
      </div>

      <div class="field">
        <label>Título</label>
        <input name="subject" value="<?= h($_POST['subject'] ?? ($_POST['title'] ?? '')) ?>"
               placeholder="Ej.: Incumplimiento de un procedimiento de seguridad..." />
      </div>

      <div class="field">
        <label>Lugar</label>
        <input name="location" value="<?= h($_POST['location'] ?? '') ?>" placeholder="Ej.: Planta / Oficina..." />
      </div>

      <div class="field">
        <label>Descripción detallada</label>
        <textarea name="description" id="description" maxlength="1000"
                  placeholder="Describe lo ocurrido..."><?= h($_POST['description'] ?? '') ?></textarea>
        <div class="small">
          <span id="descCount">0</span>/1000 caracteres (mínimo 15).
        </div>
      </div>

      <div class="hr"></div>

      <div class="field">
        <label>Denuncia anónima</label>
        <div class="small">Si es anónima, no es necesario ingresar nombre/correo.</div>
        <label class="checkline">
          <input type="checkbox" id="is_anonymous" name="is_anonymous" <?= isset($_POST['is_anonymous']) ? 'checked' : '' ?> />
          <span>Marcar como anónima</span>
        </label>
      </div>

      <div class="row">
        <div class="field">
          <label>Nombre (si no es anónima)</label>
          <input id="reporter_name" name="reporter_name" value="<?= h($_POST['reporter_name'] ?? '') ?>" />
        </div>
        <div class="field">
          <label>Email (opcional)</label>
          <input id="reporter_email" name="reporter_email" value="<?= h($_POST['reporter_email'] ?? '') ?>" />
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
        <button class="btn" type="submit" <?= (empty($categories) ? 'disabled' : '') ?>>Reportar</button>
        <a class="btn secondary" href="<?= h(base_url()) ?>/">Cancelar</a>
      </div>
    </form>
  </div>

</div><!-- /report-wrap -->

<script>
  // 1000 chars counter
  (function(){
    const ta = document.getElementById('description');
    const out = document.getElementById('descCount');
    if(!ta || !out) return;
    const upd = () => out.textContent = String(ta.value.length);
    ta.addEventListener('input', upd);
    upd();
  })();

  // when anonymous => disable name/email
  (function(){
    const cb = document.getElementById('is_anonymous');
    const name = document.getElementById('reporter_name');
    const email = document.getElementById('reporter_email');
    if(!cb || !name || !email) return;
    const apply = () => {
      const on = cb.checked;
      name.disabled = on;
      email.disabled = on;
      if(on){ name.value = ""; email.value = ""; }
    };
    cb.addEventListener('change', apply);
    apply();
  })();
</script>

<?php require_once __DIR__ . "/_footer.php"; ?>
