<?php
// denuncia/api/report_create.php
require_once dirname(__DIR__) . "/_bootstrap.php";
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();

function fail(string $msg) {
  http_response_code(400);
  echo "<h3>Lỗi</h3><p>".h($msg)."</p><p><a href='../reportar.php'>Volver</a></p>";
  exit;
}

$db = db_conn();

$company_id  = (int)($_POST['company_id'] ?? 0);
$category_id = (int)($_POST['category_id'] ?? 0); // 0 => NULL

$subject     = trim($_POST['subject'] ?? "");
$description = trim($_POST['description'] ?? "");
$location    = trim($_POST['location'] ?? "");
$occurred_at = trim($_POST['occurred_at'] ?? ""); // '' => NULL

$is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
$reporter_name  = trim($_POST['reporter_name'] ?? "");
$reporter_email = trim($_POST['reporter_email'] ?? "");
$reporter_phone = trim($_POST['reporter_phone'] ?? "");

$pass1 = $_POST['password'] ?? "";
$pass2 = $_POST['password2'] ?? "";

if ($company_id <= 0) fail("Công ty không hợp lệ.");
if ($subject === "" || $description === "") fail("Thiếu tiêu đề hoặc mô tả.");
if (strlen($pass1) < 6 || $pass1 !== $pass2) fail("Mật khẩu không hợp lệ hoặc không khớp (tối thiểu 6 ký tự).");

if ($occurred_at !== "") {
  // input datetime-local: 2026-02-03T12:30
  $occurred_at = date("Y-m-d H:i:s", strtotime($occurred_at));
}

$pwd_hash = password_hash($pass1, PASSWORD_DEFAULT);

// Generate unique report key
$report_key = null;
for ($i=0; $i<12; $i++) {
  $k = gen_report_key(10);
  $stmt = $db->prepare("SELECT id FROM portal_report WHERE report_key=? LIMIT 1");
  $stmt->bind_param("s", $k);
  $stmt->execute();
  $exists = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$exists) { $report_key = $k; break; }
}
if (!$report_key) fail("No se pudo generar la clave de seguimiento. Intenta nuevamente.");

// Insert report
$sql = "INSERT INTO portal_report
  (company_id, category_id, report_key, password_hash, is_anonymous,
   reporter_name, reporter_email, reporter_phone,
   subject, description, location, occurred_at, status)
  VALUES (?, NULLIF(?,0), ?, ?, ?,
          NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), 
          ?, ?, NULLIF(?,''), NULLIF(?,''), 'NEW')";

$stmt = $db->prepare($sql);
if (!$stmt) fail("DB prepare error.");

$stmt->bind_param(
  "iississsssss",
  $company_id,
  $category_id,
  $report_key,
  $pwd_hash,
  $is_anonymous,
  $reporter_name,
  $reporter_email,
  $reporter_phone,
  $subject,
  $description,
  $location,
  $occurred_at
);

if (!$stmt->execute()) {
  $stmt->close();
  fail("DB insert error.");
}
$report_id = $stmt->insert_id;
$stmt->close();

// Optional attachment (basic, adjust as needed)
if (!empty($_FILES['attachment']['name']) && is_uploaded_file($_FILES['attachment']['tmp_name'])) {
  $max_bytes = 10 * 1024 * 1024; // 10MB
  if ((int)$_FILES['attachment']['size'] > $max_bytes) {
    // ignore attachment if too big
  } else {
    $orig = basename($_FILES['attachment']['name']);
    $mime = $_FILES['attachment']['type'] ?? "application/octet-stream";
    $size = (int)$_FILES['attachment']['size'];

    $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig);
    $dir = realpath(__DIR__ . "/../uploads");
    if (!$dir) {
      @mkdir(__DIR__ . "/../uploads", 0775, true);
      $dir = realpath(__DIR__ . "/../uploads");
    }
    if ($dir) {
      $subdir = $dir . DIRECTORY_SEPARATOR . (string)$report_id;
      @mkdir($subdir, 0775, true);
      $stored = $subdir . DIRECTORY_SEPARATOR . time() . "_" . $safe;

      if (move_uploaded_file($_FILES['attachment']['tmp_name'], $stored)) {
        // store relative path from /denuncias
        $rel = "uploads/" . $report_id . "/" . basename($stored);

        $stmt = $db->prepare("INSERT INTO portal_report_attachment (report_id, stored_path, original_name, mime_type, size_bytes)
                              VALUES (?,?,?,?,?)");
        $stmt->bind_param("isssi", $report_id, $rel, $orig, $mime, $size);
        $stmt->execute();
        $stmt->close();
      }
    }
  }
}

// Show success (no session created; user must use seguimiento)
$base = base_url();
echo "<!doctype html><html lang='vi'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'>";
echo "<title>Enviado correctamente</title>";
echo "<link rel='stylesheet' href='{$base}/css/portal.css'></head><body>";
echo "<div class='container'><div class='panel'>";
echo "<h1 class='h1'>Denuncia enviada</h1>";
echo "<div class='note'>Guarda la <b>Clave de Reporte</b> y la <b>contraseña</b> en un lugar seguro. El sistema no puede recuperar la contraseña.</div>";
echo "<div class='kv' style='margin-top:14px;'><div class='key'>Clave de Reporte</div><div class='val'>".h($report_key)."</div></div>";
echo "<div class='btnbar' style='margin-top:14px;'>";
echo "<a class='btn green' href='{$base}/seguimiento.php'>Đi tới Seguimiento</a>";
echo "<a class='btn' href='{$base}/'>Inicio</a>";
echo "</div>";
echo "</div></div></body></html>";
