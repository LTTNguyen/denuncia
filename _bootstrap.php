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
  $pass = $DENUNCIA_DB['pass'] ?? 'quz@*W7Yaxb9[sUU';
  $name = $DENUNCIA_DB['name'] ?? 'denuncias_portal';
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

// =========================================================
// EMAIL NOTIFICATIONS (new report)
// =========================================================

function portal_mail_cfg(): array {
  global $DENUNCIA_MAIL;
  return is_array($DENUNCIA_MAIL ?? null) ? $DENUNCIA_MAIL : [];
}

function portal_mail_enabled(): bool {
  $cfg = portal_mail_cfg();
  return (bool)($cfg['enabled'] ?? false);
}

function portal_db_table_exists(mysqli $db, string $table): bool {
  static $cache = [];
  $key = strtolower($table);
  if (array_key_exists($key, $cache)) return (bool)$cache[$key];

  $safe = $db->real_escape_string($table);
  $sql = "SHOW TABLES LIKE '{$safe}'";
  $res = $db->query($sql);
  $exists = ($res && $res->num_rows > 0);
  if ($res) $res->free();
  $cache[$key] = $exists;
  return $exists;
}

/**
 * Returns recipient emails for notifications.
 * Priority:
 * 1) DB: portal_notify_recipient (company + category specific or company-wide)
 * 2) config_denuncia.php: $DENUNCIA_MAIL['fallback_recipients']
 */
function portal_get_notify_recipients(mysqli $db, int $company_id, int $category_id): array {
  $emails = [];

  if ($company_id > 0 && portal_db_table_exists($db, 'portal_notify_recipient')) {
    $sql = "
      SELECT DISTINCT email
      FROM portal_notify_recipient
      WHERE is_active=1
        AND company_id=?
        AND (category_id IS NULL OR category_id=?)
      ORDER BY email
    ";
    $stmt = $db->prepare($sql);
    if ($stmt) {
      $stmt->bind_param('ii', $company_id, $category_id);
      $stmt->execute();
      $res = $stmt->get_result();
      while ($r = $res->fetch_assoc()) {
        $e = strtolower(trim((string)($r['email'] ?? '')));
        if ($e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL)) $emails[] = $e;
      }
      $stmt->close();
    }
  }

  if (empty($emails)) {
    $cfg = portal_mail_cfg();
    $fallback = $cfg['fallback_recipients'] ?? [];
    if (is_array($fallback)) {
      foreach ($fallback as $e) {
        $e = strtolower(trim((string)$e));
        if ($e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL)) $emails[] = $e;
      }
    }
  }

  // unique
  $emails = array_values(array_unique($emails));
  return $emails;
}

/**
 * Send HTML email using PHP mail().
 * (SMTP support can be added later if you want.)
 */
function portal_send_mail_native(string $to, string $subject, string $html, string $from_email, string $from_name): bool {
  $from_email = trim($from_email);
  $from_name  = trim($from_name);

  // Encode subject for UTF-8
  $enc_subject = mb_encode_mimeheader($subject, 'UTF-8', 'B');
  $enc_from_name = mb_encode_mimeheader($from_name, 'UTF-8', 'B');

  $headers = [];
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-type: text/html; charset=UTF-8';
  if ($from_email !== '') {
    $headers[] = 'From: ' . ($enc_from_name !== '' ? $enc_from_name . ' ' : '') . '<' . $from_email . '>';
    $headers[] = 'Reply-To: ' . $from_email;
  }
  $headers[] = 'X-Mailer: PHP/' . phpversion();

  return @mail($to, $enc_subject, $html, implode("\r\n", $headers));
}

/**
 * Dev helper: write the outgoing email as an .eml file (no real sending).
 * Set config_denuncia.php: $DENUNCIA_MAIL['mode'] = 'file'
 */
function portal_send_mail_file(string $to, string $subject, string $html, string $from_email, string $from_name): bool {
  $dir = __DIR__ . '/outbox';
  if (!is_dir($dir)) {
    @mkdir($dir, 0777, true);
  }
  if (!is_dir($dir) || !is_writable($dir)) return false;

  $date = date('r');
  $from = trim($from_name) !== ''
    ? mb_encode_mimeheader($from_name, 'UTF-8', 'B') . " <{$from_email}>"
    : "<{$from_email}>";

  $headers = [];
  $headers[] = "Date: {$date}";
  $headers[] = "To: {$to}";
  $headers[] = "From: {$from}";
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-Type: text/html; charset=UTF-8';
  $headers[] = 'Subject: ' . mb_encode_mimeheader($subject, 'UTF-8', 'B');

  $eml = implode("\r\n", $headers) . "\r\n\r\n" . $html;

  $safe_to = preg_replace('/[^a-z0-9._@-]+/i', '_', $to);
  $fname = date('Ymd_His') . '_' . $safe_to . '.eml';
  return (bool)@file_put_contents($dir . '/' . $fname, $eml);
}

/**
 * Build and send notification email for a newly created report.
 * Returns true if at least one email was sent successfully.
 */
function portal_notify_new_report(mysqli $db, int $report_id): bool {
  if (!portal_mail_enabled() || $report_id <= 0) return false;

  // Load report details
  $sql = "
    SELECT r.id, r.report_key, r.subject, r.description, r.location, r.occurred_at, r.created_at,
           c.id AS company_id, c.name AS company_name,
           cat.id AS category_id, cat.name AS category_name
    FROM portal_report r
    JOIN portal_company c ON c.id = r.company_id
    LEFT JOIN portal_category cat ON cat.id = r.category_id
    WHERE r.id = ?
    LIMIT 1
  ";
  $stmt = $db->prepare($sql);
  if (!$stmt) return false;
  $stmt->bind_param('i', $report_id);
  $stmt->execute();
  $rep = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$rep) return false;

  $company_id = (int)($rep['company_id'] ?? 0);
  $category_id = (int)($rep['category_id'] ?? 0);

  $recipients = portal_get_notify_recipients($db, $company_id, $category_id);
  if (empty($recipients)) return false;

  $cfg = portal_mail_cfg();
  $from_email = (string)($cfg['from_email'] ?? '');
  $from_name  = (string)($cfg['from_name'] ?? '');
  $include_desc = (bool)($cfg['include_description'] ?? true);
  $max_chars = (int)($cfg['description_max_chars'] ?? 700);
  if ($max_chars <= 0) $max_chars = 700;

  $company_name  = (string)($rep['company_name'] ?? '');
  $category_name = (string)($rep['category_name'] ?? '-');
  $report_key    = (string)($rep['report_key'] ?? '');
  $title         = (string)($rep['subject'] ?? '');
  $location      = (string)($rep['location'] ?? '-');
  $occurred_at   = (string)($rep['occurred_at'] ?? '-');
  $created_at    = (string)($rep['created_at'] ?? '');

  // Description trimmed
  $desc = (string)($rep['description'] ?? '');
  $desc = trim($desc);
  if (!$include_desc) $desc = '';
  if ($desc !== '' && mb_strlen($desc) > $max_chars) {
    $desc = mb_substr($desc, 0, $max_chars) . "…";
  }

  $subject_mail = "[Canal de Denuncias] Nuevo reporte - {$company_name}";
  if ($category_name && $category_name !== '-') $subject_mail .= " - {$category_name}";

  // Build URLs
  $base = rtrim((string)base_url(), '/');
  // seguimiento.php can receive key param (if you want to pre-fill)
  $seguimiento_url = $base . "/seguimiento.php?key=" . rawurlencode($report_key);

  $html = "";
  $html .= "<div style=\"font-family:Arial,Helvetica,sans-serif; color:#111827; line-height:1.5\">";
  $html .= "<h2 style=\"margin:0 0 10px\">Nuevo reporte recibido</h2>";
  $html .= "<p style=\"margin:0 0 10px\">Se ha recibido un nuevo reporte en el Canal de Denuncias.</p>";
  $html .= "<table cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse:collapse; width:100%; max-width:720px\">";
  $row = function($k, $v){
    $k = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
    $v = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    return "<tr><td style=\"padding:6px 8px; border:1px solid #e5e7eb; width:220px; background:#f8fafc\"><b>{$k}</b></td><td style=\"padding:6px 8px; border:1px solid #e5e7eb\">{$v}</td></tr>";
  };
  $html .= $row('Número de seguimiento', $report_key);
  $html .= $row('Empresa', $company_name);
  $html .= $row('Categoría', $category_name);
  $html .= $row('Título', $title);
  $html .= $row('Lugar', $location);
  $html .= $row('Fecha del evento', $occurred_at);
  $html .= $row('Fecha de envío', $created_at);
  $html .= "</table>";

  if ($desc !== '') {
    $safe_desc = htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
    $html .= "<h3 style=\"margin:14px 0 8px\">Descripción (resumen)</h3>";
    $html .= "<div style=\"white-space:pre-wrap; padding:10px 12px; border:1px solid #e5e7eb; border-radius:10px; background:#fff\">{$safe_desc}</div>";
  }

  $html .= "<p style=\"margin:14px 0 0\">Link (requiere clave + contraseña del reportante para ver el caso):<br>";
  $html .= "<a href=\"" . htmlspecialchars($seguimiento_url, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($seguimiento_url, ENT_QUOTES, 'UTF-8') . "</a></p>";
  $html .= "<p style=\"margin:14px 0 0; color:#6b7280; font-size:12px\">Este correo es automático. No responder.</p>";
  $html .= "</div>";

  $sent_any = false;
  foreach ($recipients as $to) {
    $ok = false;
    $mode = strtolower((string)($cfg['mode'] ?? 'mail'));
    if ($mode === 'file') {
      $ok = portal_send_mail_file($to, $subject_mail, $html, $from_email, $from_name);
    } elseif ($mode === 'mail' || $mode === '') {
      $ok = portal_send_mail_native($to, $subject_mail, $html, $from_email, $from_name);
    } else {
      // reserved for future SMTP upgrade
      $ok = portal_send_mail_native($to, $subject_mail, $html, $from_email, $from_name);
    }
    if ($ok) $sent_any = true;
  }

  return $sent_any;
}
