<?php
require_once __DIR__ . "/_bootstrap.php";

$db = db_conn();

$report_id = (int)($_SESSION['denuncia_report_id'] ?? 0);
$case_ok   = (bool)($_SESSION['denuncia_case_ok'] ?? false);
if ($report_id <= 0 || !$case_ok) {
  http_response_code(403);
  echo "Forbidden";
  exit;
}

// timeout check (same as caso)
$last_seen = (int)($_SESSION['denuncia_case_last_seen'] ?? 0);
if ($last_seen <= 0 || (time() - $last_seen) > 30 * 60) {
  $_SESSION['denuncia_case_ok'] = false;
  http_response_code(403);
  echo "Session expired";
  exit;
}
$_SESSION['denuncia_case_last_seen'] = time();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo "Bad request";
  exit;
}

$stmt = $db->prepare("
  SELECT id, report_id, stored_path, original_name, mime_type, size_bytes
  FROM portal_report_attachment
  WHERE id = ? AND report_id = ?
  LIMIT 1
");
if (!$stmt) {
  http_response_code(500);
  echo "DB error";
  exit;
}
$stmt->bind_param("ii", $id, $report_id);
$stmt->execute();
$a = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$a) {
  http_response_code(404);
  echo "Not found";
  exit;
}

$stored = (string)($a['stored_path'] ?? '');
$orig   = (string)($a['original_name'] ?? 'archivo');
$mime   = (string)($a['mime_type'] ?? 'application/octet-stream');

$abs = __DIR__ . '/' . ltrim($stored, '/');
$abs_real = realpath($abs);

$uploads_root = realpath(__DIR__ . '/uploads');
if (!$abs_real || !$uploads_root || strpos($abs_real, $uploads_root) !== 0) {
  http_response_code(403);
  echo "Forbidden";
  exit;
}

if (!is_file($abs_real)) {
  http_response_code(404);
  echo "File missing";
  exit;
}

audit_log($db, $report_id, 'ATTACHMENT_DOWNLOAD', 'REPORTER', null, ['attachment_id' => $id]);

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . str_replace('"','', $orig) . '"');
header('Content-Length: ' . filesize($abs_real));
header('X-Content-Type-Options: nosniff');

readfile($abs_real);
exit;
