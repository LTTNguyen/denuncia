<?php
require_once dirname(__DIR__) . "/_bootstrap.php";

header('Content-Type: application/json; charset=utf-8');

try {
  $db = db_conn();
  $company_id = (int)($_GET['company_id'] ?? 0);
  if ($company_id <= 0) { echo json_encode(['categories'=>[]]); exit; }

  $cats = get_categories($db, $company_id);
  echo json_encode(['categories'=>$cats], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error'=>'server_error'], JSON_UNESCAPED_UNICODE);
}
