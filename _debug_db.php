<?php
require_once __DIR__ . "/_bootstrap.php";

header("Content-Type: text/plain; charset=utf-8");

try {
  $db = db_conn();
  $port = null;
  $dbn = null;

  if ($res = $db->query("SELECT @@port AS port, DATABASE() AS dbname")) {
    $row = $res->fetch_assoc();
    $port = $row['port'] ?? '';
    $dbn  = $row['dbname'] ?? '';
    $res->free();
  }

  echo "OK\n";
  echo "Server port: {$port}\n";
  echo "Database: {$dbn}\n\n";

  $companies = get_companies($db);
  echo "Active companies: " . count($companies) . "\n";
  foreach ($companies as $c) {
    echo "- id={$c['id']} | name={$c['name']} | slug={$c['slug']} | logo={$c['logo_path']}\n";
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo "ERROR: " . $e->getMessage() . "\n";
}
