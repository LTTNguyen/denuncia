<?php
if (defined('DENUNCIA_BOOTSTRAP_LOADED')) { return; }
define('DENUNCIA_BOOTSTRAP_LOADED', 1);


ini_set('display_errors', 1);
error_reporting(E_ALL);

// Escape HTML
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Base URL of this portal (works in /denuncia or deeper)
function base_url(): string {
  $doc = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
  $dir = rtrim(str_replace('\\', '/', __DIR__), '/');

  if ($doc !== '' && strpos($dir, $doc) === 0) {
    $rel = substr($dir, strlen($doc));
    return $rel === '' ? '' : $rel;  // e.g. "/denuncia"
  }
  // fallback
  return rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
}

function redirect(string $path): void {
  header("Location: " . base_url() . $path);
  exit;
}

// Session isolation
if (session_status() === PHP_SESSION_NONE) {
  session_name('DENUNCIASESSID');

  $cookie_path = base_url();
  if ($cookie_path === '') $cookie_path = '/';

  session_set_cookie_params([
    'lifetime' => 0,
    'path' => $cookie_path,
    'httponly' => true,
    'samesite' => 'Lax',
  ]);

  session_start();

// Remember company context (keep selection across pages)
$__cid = (int)($_GET['company_id'] ?? ($_POST['company_id'] ?? ($_SESSION['company_id'] ?? 0)));
if ($__cid > 0) {
  $_SESSION['company_id'] = $__cid;
}
function current_company_id(): int {
  return (int)($_SESSION['company_id'] ?? 0);
}

}

//Load DB config
$cfg = __DIR__ . '/config_denuncia.php';
if (!is_file($cfg)) {
  http_response_code(500);
  echo "<h3>Missing config_denuncia.php</h3>";
  exit;
}
require_once $cfg;

// DB connection
function db_conn(): mysqli {
  if (isset($GLOBALS['denuncia_db']) && $GLOBALS['denuncia_db'] instanceof mysqli) {
    return $GLOBALS['denuncia_db'];
  }

  global $DENUNCIA_DB;
  $host = $DENUNCIA_DB['host'] ?? 'localhost';
  $user = $DENUNCIA_DB['user'] ?? 'root';
  $pass = $DENUNCIA_DB['pass'] ?? '';
  $name = $DENUNCIA_DB['name'] ?? '';
  $port = (int)($DENUNCIA_DB['port'] ?? 3306);
  $charset = $DENUNCIA_DB['charset'] ?? 'utf8mb4';

  $db = @new mysqli($host, $user, $pass, $name, $port);
  if ($db->connect_error) {
    throw new Exception('DB connection failed: ' . $db->connect_error);
  }
  $db->set_charset($charset);

  $GLOBALS['denuncia_db'] = $db;
  return $db;
}

function gen_report_key(int $len = 10): string {
  $alphabet = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789"; // avoid 0/O/I/1
  $out = "";
  for ($i = 0; $i < $len; $i++) {
    $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
  }
  return $out;
}

function status_label(string $s): string {
  $s = strtolower(trim($s));
  if ($s === 'pending') return 'Pending';
  if ($s === 'in_review') return 'In review';
  if ($s === 'closed') return 'Closed';
  return ucfirst($s);
}

function get_companies(mysqli $db): array {
  $rows = [];
  $sql = "SELECT id, name, slug, logo_path FROM portal_company WHERE is_active=1 ORDER BY name";
  if ($res = $db->query($sql)) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $res->free();
  }
  return $rows;
}

function get_categories(mysqli $db, int $company_id): array {
  $rows = [];
  $stmt = $db->prepare("SELECT id, name FROM portal_category WHERE company_id=? AND is_active=1 ORDER BY sort_order, name");
  $stmt->bind_param("i", $company_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) $rows[] = $r;
  $stmt->close();
  return $rows;
}

if (!function_exists('portal_get_resources')) {
  function portal_get_resources(mysqli $db, int $company_id): array {
    $rows = [];
    $stmt = $db->prepare("SELECT title, url FROM portal_resource WHERE company_id=? AND is_active=1 ORDER BY sort_order, title");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $stmt->close();
    return $rows;
  }
}


//Branding helpers
function portal_company_logo_path(array $c): string {
  $p = trim((string)($c['logo_path'] ?? ''));
  if ($p !== '') return $p;

  $slug = strtolower(trim((string)($c['slug'] ?? '')));
  $name = strtolower(trim((string)($c['name'] ?? '')));
  $hay  = $slug . ' ' . $name;

  if (strpos($hay, 'tym') !== false || strpos($hay, 't&m') !== false || strpos($hay, 't & m') !== false) {
    return 'images/LOGO_TYM.png';
  }
  if (preg_match('/\brk\b/', $hay) || strpos($hay, 'maestranza') !== false) {
    return 'images/logo_rk.png';
  }
  if (strpos($hay, 'andes') !== false) {
    return 'images/logo_andes_pic.png';
  }
  return 'images/LOGOFULL.png';
}

function portal_find_company(array $companies, int $company_id): ?array {
  foreach ($companies as $c) {
    if ((int)($c['id'] ?? 0) === $company_id) return $c;
  }
  return null;
}

function portal_link(string $path, bool $with_company = true): string {
  $url = rtrim(base_url(), '/') . $path;
  if ($with_company) {
    $cid = current_company_id();
    if ($cid > 0) {
      $sep = (strpos($url, '?') === false) ? '?' : '&';
      $url .= $sep . 'company_id=' . $cid;
    }
  }
  return $url;
}
