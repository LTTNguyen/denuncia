<?php
require_once __DIR__ . '/_admin_bootstrap.php';

$db = db_conn();
$admin = admin_require_login($db);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo 'Bad request';
  exit;
}

$sql = "SELECT id, report_id, stored_path, original_name, mime_type, size_bytes
        FROM portal_report_attachment
        WHERE id = ?
        LIMIT 1";
$st = $db->prepare($sql);
$st->bind_param('i', $id);
$st->execute();
$res = $st->get_result();
$attachment = $res ? $res->fetch_assoc() : null;
$st->close();

if (!$attachment) {
  http_response_code(404);
  echo 'Not found';
  exit;
}

$storedPath = trim((string)$attachment['stored_path']);
$uploadsRoot = realpath(__DIR__ . '/../uploads');
$fullPath = realpath(__DIR__ . '/../' . ltrim($storedPath, '/\\'));

if (!$uploadsRoot || !$fullPath || strpos($fullPath, $uploadsRoot) !== 0 || !is_file($fullPath)) {
  http_response_code(403);
  echo 'Forbidden';
  exit;
}

$filename = trim((string)$attachment['original_name']);
if ($filename === '') {
  $filename = 'attachment_' . (int)$attachment['id'];
}
$mime = trim((string)$attachment['mime_type']);
if ($mime === '') {
  $mime = 'application/octet-stream';
}
$fileSize = filesize($fullPath);

admin_audit($db, (int)$attachment['report_id'], 'ADMIN_ATTACHMENT_DOWNLOAD', 'INVESTIGATOR', (string)$admin['email'], [
  'attachment_id' => (int)$attachment['id'],
  'original_name' => $filename,
  'size_bytes' => (int)$attachment['size_bytes'],
]);

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)$fileSize);
header('Content-Disposition: attachment; filename="' . str_replace('"', '', $filename) . '"');
header('X-Content-Type-Options: nosniff');

readfile($fullPath);
exit;
