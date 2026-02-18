<?php
if (defined('DENUNCIA_BOOTSTRAP_LOADED')) { return; }
define('DENUNCIA_BOOTSTRAP_LOADED', 1);

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Santiago');

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function base_url(): string {
  $doc = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
  $dir = rtrim(str_replace('\\', '/', __DIR__), '/');

  if ($doc !== '' && strpos($dir, $doc) === 0) {
    $rel = substr($dir, strlen($doc));
    return $rel === '' ? '' : $rel;
  }
  return rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
}

function redirect(string $path): void {
  header("Location: " . base_url() . $path);
  exit;
}


// LOAD CONFIG

$cfg = __DIR__ . '/config_denuncia.php';
if (!is_file($cfg)) {
  http_response_code(500);
  echo "<h3>Missing config_denuncia.php</h3>";
  exit;
}
require_once $cfg;

function current_company_id(): int {
  global $DENUNCIA_PORTAL;
  $cid = (int)($DENUNCIA_PORTAL['single_company_id'] ?? 0);
  return ($cid > 0) ? $cid : 1;
}


// SESSION ISOLATION

if (session_status() === PHP_SESSION_NONE) {

  $sessName = defined('DENUNCIA_SESSION_NAME') ? DENUNCIA_SESSION_NAME : 'DENUNCIASESSID';
  session_name($sessName);

  $cookie_path = base_url();
  if ($cookie_path === '') $cookie_path = '/';

  $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

  session_set_cookie_params([
    'lifetime' => 0,
    'path' => $cookie_path,
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => $is_https,
  ]);

  session_start();
}

$_SESSION['company_id'] = current_company_id();


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
  $alphabet = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
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
  $stmt = $db->prepare("
    SELECT id, code, name, sort_order
    FROM portal_category
    WHERE company_id=? AND is_active=1
    ORDER BY sort_order, name
  ");
  $stmt->bind_param("i", $company_id);
  $stmt->execute();
  $res = $stmt->get_result();
  while ($r = $res->fetch_assoc()) $rows[] = $r;
  $stmt->close();
  return $rows;
}


// CHANNEL (CMP vs LK) helpers

function category_channel_from_code(?string $code): string {
  $code = (string)$code;
  if ($code !== '' && str_starts_with($code, 'CMP_')) return 'CMP';
  if ($code !== '' && str_starts_with($code, 'LK_'))  return 'LK';
  return 'GEN';
}

function channel_meta(string $channel): array {
  if ($channel === 'CMP') {
    return [
      'badge'   => 'COMPLIANCE (Ley 20.393 / 21.595)',
      'title'   => 'Canal de Denuncias – Modelo de Prevención de Delitos',
      'message' => 'Este canal forma parte del Modelo de Prevención de Delitos implementado conforme a la Ley 20.393 y su reforma por Ley 21.595, garantizando confidencialidad, trazabilidad y protección contra represalias.',
    ];
  }
  if ($channel === 'LK') {
    return [
      'badge'   => 'LEY KARIN (Ley 21.643)',
      'title'   => 'Canal de Denuncias – Protocolo Ley Karin',
      'message' => 'Este canal aplica para denuncias bajo Ley 21.643 (Ley Karin): acoso laboral, acoso sexual y violencia en el trabajo, con resguardo de confidencialidad y protección de datos.',
    ];
  }
  return [
    'badge'   => 'GENERAL',
    'title'   => 'Canal de Denuncias',
    'message' => 'Canal institucional de denuncias con confidencialidad y trazabilidad.',
  ];
}

function get_categories_by_channel(mysqli $db, int $company_id, string $channel): array {
  $all = get_categories($db, $company_id);
  return array_values(array_filter($all, function($c) use ($channel){
    $ch = category_channel_from_code($c['code'] ?? '');
    return $ch === $channel;
  }));
}

# Audit Log
function audit_log(mysqli $db, ?int $report_id, string $action, string $actor_type='SYSTEM', ?string $actor_label=null, array $meta=[]): void {
  if (!portal_db_table_exists($db, 'portal_audit_log')) return;

  $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
  $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
  if (strlen($ua) > 255) $ua = substr($ua, 0, 255);

  $meta_json = !empty($meta) ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null;

  $stmt = $db->prepare("
    INSERT INTO portal_audit_log (report_id, action, actor_type, actor_label, ip, user_agent, meta_json)
    VALUES (?,?,?,?,?,?,?)
  ");
  if (!$stmt) return;

  // report_id can be NULL => use "i" still works if we pass null? better: cast to int and allow 0
  $rid = $report_id ?? null;

  // bind_param doesn't accept null for "i" in old versions reliably; easiest: convert null -> 0
  $rid_int = ($rid === null) ? 0 : (int)$rid;

  $stmt->bind_param("issssss", $rid_int, $action, $actor_type, $actor_label, $ip, $ua, $meta_json);
  $stmt->execute();
  $stmt->close();
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

// =========================================================
// Branding helpers
// =========================================================
function portal_company_logo_path(array $c): string {
  $p = trim((string)($c['logo_path'] ?? ''));
  if ($p !== '') return $p;
  return 'images/LOGO_TYM.png';
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


// EMAIL CONFIG + PUBLIC URL

function portal_mail_cfg(): array {
  global $DENUNCIA_MAIL;
  return is_array($DENUNCIA_MAIL ?? null) ? $DENUNCIA_MAIL : [];
}

function portal_mail_enabled(): bool {
  $cfg = portal_mail_cfg();
  return (bool)($cfg['enabled'] ?? false);
}

function portal_send_reporter_copy_enabled(): bool {
  $cfg = portal_mail_cfg();
  return (bool)($cfg['send_reporter_copy'] ?? true);
}

/**
 * Returns a base URL used for links inside emails.
 * Priority:
 * 1) config: $DENUNCIA_MAIL['public_base_url']
 * 2) http(s)://HTTP_HOST + base_url()
 * 3) base_url() (relative)
 */
function portal_public_base_url(): string {
  $cfg = portal_mail_cfg();
  $p = trim((string)($cfg['public_base_url'] ?? ''));
  if ($p !== '') return rtrim($p, '/');

  $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
  if ($host !== '') {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ((string)($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $is_https ? 'https' : 'http';
    return $scheme . '://' . $host . rtrim(base_url(), '/');
  }

  return rtrim(base_url(), '/');
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

  return array_values(array_unique($emails));
}

// =========================================================
// SENDERS
// =========================================================
function portal_send_mail_native(string $to, string $subject, string $html, string $from_email, string $from_name): bool {
  $from_email = trim($from_email);
  $from_name  = trim($from_name);

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

function portal_send_mail_file(string $to, string $subject, string $html, string $from_email, string $from_name): bool {
  $dir = __DIR__ . '/outbox';
  if (!is_dir($dir)) @mkdir($dir, 0777, true);
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

function portal_send_mail_smtp(string $to, string $subject, string $html, string $from_email, string $from_name): bool {
  $cfg = portal_mail_cfg();
  $smtp = is_array($cfg['smtp'] ?? null) ? $cfg['smtp'] : [];

  $host = (string)($smtp['host'] ?? '');
  $user = (string)($smtp['username'] ?? '');
  $pass = (string)($smtp['password'] ?? '');
  $port = (int)($smtp['port'] ?? 465);
  $secure = strtolower((string)($smtp['secure'] ?? 'ssl'));
  $debug = (int)($smtp['debug'] ?? 0);

  if ($host === '' || $user === '' || $pass === '') {
    error_log("SMTP config missing (host/username/password).");
    return false;
  }

  $exc = __DIR__ . '/src/Exception.php';
  $php = __DIR__ . '/src/PHPMailer.php';
  $smt = __DIR__ . '/src/SMTP.php';

  if (!is_file($exc) || !is_file($php) || !is_file($smt)) {
    error_log("PHPMailer files not found in /src (Exception.php, PHPMailer.php, SMTP.php).");
    return false;
  }

  require_once $exc;
  require_once $php;
  require_once $smt;

  try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();

    $mail->Host = $host;
    $mail->SMTPAuth = (bool)($smtp['auth'] ?? true);
    $mail->Username = $user;
    $mail->Password = $pass;
    $mail->Port = $port;

    if ($secure === 'tls') {
      $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    } else {
      $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    }

    $mail->SMTPDebug = $debug;
    $mail->Debugoutput = 'error_log';

    $mail->setFrom($from_email, $from_name);
    $mail->addAddress($to);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $html;

    return (bool)$mail->send();
  } catch (\Throwable $e) {
    error_log("SMTP send failed: " . $e->getMessage());
    return false;
  }
}

function portal_send_mail(string $to, string $subject, string $html, string $from_email, string $from_name): bool {
  $cfg = portal_mail_cfg();
  $mode = strtolower((string)($cfg['mode'] ?? 'file'));

  if ($mode === 'smtp') return portal_send_mail_smtp($to, $subject, $html, $from_email, $from_name);
  if ($mode === 'mail') return portal_send_mail_native($to, $subject, $html, $from_email, $from_name);
  return portal_send_mail_file($to, $subject, $html, $from_email, $from_name);
}


// NOTIFICATIONS

function portal_notify_new_report(mysqli $db, int $report_id): bool {
  if (!portal_mail_enabled() || $report_id <= 0) return false;

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

  $company_id   = (int)($rep['company_id'] ?? 0);
  $category_id  = (int)($rep['category_id'] ?? 0);
  $company_name = (string)($rep['company_name'] ?? '');
  $category_name= (string)($rep['category_name'] ?? '-');

  $report_key   = (string)($rep['report_key'] ?? '');
  $title        = (string)($rep['subject'] ?? '');
  $location     = (string)($rep['location'] ?? '-');
  $occurred_at  = (string)($rep['occurred_at'] ?? '-');
  $created_at   = (string)($rep['created_at'] ?? '');
  $desc         = trim((string)($rep['description'] ?? ''));

  $cfg = portal_mail_cfg();
  $from_email = (string)($cfg['from_email'] ?? '');
  $from_name  = (string)($cfg['from_name'] ?? 'Canal de Denuncias');

  $include_desc = (bool)($cfg['include_description'] ?? true);
  $max_chars = (int)($cfg['description_max_chars'] ?? 700);
  if ($max_chars <= 0) $max_chars = 700;

  if (!$include_desc) $desc = '';
  if ($desc !== '' && mb_strlen($desc) > $max_chars) {
    $desc = mb_substr($desc, 0, $max_chars) . "…";
  }

  $base = portal_public_base_url();
  $seguimiento_url = $base . "/seguimiento.php?key=" . rawurlencode($report_key);

  $subject_mail = "[Canal de Denuncias] Nuevo reporte - {$company_name}";
  if ($category_name && $category_name !== '-') $subject_mail .= " - {$category_name}";

  $row = function($k, $v){
    $k = htmlspecialchars((string)$k, ENT_QUOTES, 'UTF-8');
    $v = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    return "<tr><td style=\"padding:6px 8px; border:1px solid #e5e7eb; width:220px; background:#f8fafc\"><b>{$k}</b></td><td style=\"padding:6px 8px; border:1px solid #e5e7eb\">{$v}</td></tr>";
  };

  $html  = "<div style=\"font-family:Arial,Helvetica,sans-serif; color:#111827; line-height:1.5\">";
  $html .= "<h2 style=\"margin:0 0 10px\">Nuevo reporte recibido</h2>";
  $html .= "<p style=\"margin:0 0 10px\">Se ha recibido un nuevo reporte en el Canal de Denuncias.</p>";
  $html .= "<table cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse:collapse; width:100%; max-width:720px\">";
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
  $recipients = portal_get_notify_recipients($db, $company_id, $category_id);
  foreach ($recipients as $to) {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) continue;
    if (portal_send_mail($to, $subject_mail, $html, $from_email, $from_name)) {
      $sent_any = true;
    }
  }

  return $sent_any;
}

/**
 * Send receipt to reporter (only if enabled + not anonymous + valid email)
 */
function portal_notify_reporter_receipt(mysqli $db, int $report_id): bool {
  if (!portal_mail_enabled() || $report_id <= 0) return false;
  if (!portal_send_reporter_copy_enabled()) return false;

  $sql = "
    SELECT r.id, r.report_key, r.subject, r.location, r.created_at,
           r.is_anonymous, r.reporter_email,
           c.name AS company_name
    FROM portal_report r
    JOIN portal_company c ON c.id = r.company_id
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

  if ((int)($rep['is_anonymous'] ?? 0) === 1) return false;

  $email = strtolower(trim((string)($rep['reporter_email'] ?? '')));
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

  $cfg = portal_mail_cfg();
  $from_email = (string)($cfg['from_email'] ?? '');
  $from_name  = (string)($cfg['from_name'] ?? 'Canal de Denuncias');

  $company_name = (string)($rep['company_name'] ?? 'Canal de Denuncias');
  $report_key   = (string)($rep['report_key'] ?? '');
  $title        = (string)($rep['subject'] ?? '');
  $location     = (string)($rep['location'] ?? '-');
  $created_at   = (string)($rep['created_at'] ?? '');

  $base = portal_public_base_url();
  $seguimiento_url = $base . "/seguimiento.php?key=" . rawurlencode($report_key);

  $subject_mail = "[{$company_name}] Recibo de denuncia - {$report_key}";

  $html  = "<div style=\"font-family:Arial,Helvetica,sans-serif; color:#111827; line-height:1.5\">";
  $html .= "<h2 style=\"margin:0 0 10px\">Hemos recibido tu denuncia</h2>";
  $html .= "<p style=\"margin:0 0 10px\">Guarda esta información para poder hacer seguimiento.</p>";
  $html .= "<div style=\"padding:10px 12px; border:1px solid #e5e7eb; border-radius:10px; background:#fff\">";
  $html .= "<p style=\"margin:0 0 6px\"><b>Número de seguimiento:</b> " . h($report_key) . "</p>";
  $html .= "<p style=\"margin:0 0 6px\"><b>Título:</b> " . h($title) . "</p>";
  $html .= "<p style=\"margin:0 0 6px\"><b>Lugar:</b> " . h($location) . "</p>";
  $html .= "<p style=\"margin:0\"><b>Fecha de envío:</b> " . h($created_at) . "</p>";
  $html .= "</div>";
  $html .= "<p style=\"margin:14px 0 0\">Puedes hacer seguimiento aquí:<br>";
  $html .= "<a href=\"" . htmlspecialchars($seguimiento_url, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($seguimiento_url, ENT_QUOTES, 'UTF-8') . "</a></p>";
  $html .= "<p style=\"margin:14px 0 0; color:#6b7280; font-size:12px\">Este correo es automático. No responder.</p>";
  $html .= "</div>";

  return portal_send_mail($email, $subject_mail, $html, $from_email, $from_name);
}

