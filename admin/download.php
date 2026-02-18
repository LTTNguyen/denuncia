<?php
require_once __DIR__ . "/_admin_bootstrap.php";

$db = db_conn();
$admin = admin_require_login($db);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo "Bad request";
  exit;
}

$st = $db->prepare("SELECT id, report_id, stored_path, original_name, mime_type, size_bytes
                    FROM portal_report_attachment WHERE id = ? LIMIT 1");
$st->bind_param("i", $id);
$st->execute();
$res = $st->get_result();
$att = $res->fetch_assoc();
$st->close();

if (!$att) {
  http_response_code(404);
  echo "Not found";
  exit;
}

$rel = (string)$att['stored_path'];
$rootUploads = realpath(__DIR__ . '/../uploads');
$full = realpath(__DIR__ . '/../' . $rel);

if (!$rootUploads || !$full || strpos($full, $rootUploads) !== 0 || !is_file($full)) {
  http_response_code(403);
  echo "Forbidden";
  exit;
}

$filename = (string)$att['original_name'];
$mime = (string)$att['mime_type'];

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($full));
header('Content-Disposition: attachment; filename="' . str_replace('"', '', $filename) . '"');
header('X-Content-Type-Options: nosniff');

readfile($full);
exit;
