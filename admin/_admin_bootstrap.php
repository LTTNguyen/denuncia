<?php


declare(strict_types=1);

/**
 * Admin bootstrap:
 * - Uses a separate session cookie name from reporter pages
 * - Reuses core helpers from /_bootstrap.php
 */
if (!defined('DENUNCIA_SESSION_NAME')) {
  define('DENUNCIA_SESSION_NAME', 'DENUNCIAADMINSESSID');
}

require_once __DIR__ . '/../_bootstrap.php';

function admin_link(string $path): string {
  return rtrim(base_url(), '/') . $path;
}

function admin_csrf_token(): string {
  if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(16));
  }
  return (string)$_SESSION['admin_csrf'];
}

function admin_csrf_check(?string $token): bool {
  return is_string($token) && isset($_SESSION['admin_csrf']) && hash_equals((string)$_SESSION['admin_csrf'], $token);
}

function admin_password_verify(string $password, string $hash): bool {
  return password_verify($password, $hash);
}

function admin_current_user(mysqli $db): ?array {
  $id = (int)($_SESSION['admin_user_id'] ?? 0);
  if ($id <= 0) return null;

  $sql = "SELECT id, email, full_name, role, is_active, last_login_at
          FROM portal_admin_user
          WHERE id = ? LIMIT 1";
  $st = $db->prepare($sql);
  $st->bind_param("i", $id);
  $st->execute();
  $res = $st->get_result();
  $u = $res->fetch_assoc();
  $st->close();

  if (!$u || (int)$u['is_active'] !== 1) return null;
  return $u;
}

function admin_require_login(mysqli $db): array {
  $u = admin_current_user($db);
  if (!$u) {
    redirect('/admin/login.php');
    exit;
  }
  return $u;
}

function admin_is_admin(?array $u): bool {
  return $u && ($u['role'] ?? '') === 'ADMIN';
}

function admin_can_edit(?array $u): bool {
  if (!$u) return false;
  $role = (string)($u['role'] ?? '');
  return in_array($role, ['ADMIN', 'INVESTIGATOR'], true);
}

function admin_flash_set(string $type, string $msg): void {
  $_SESSION['admin_flash'] = ['type' => $type, 'msg' => $msg];
}

function admin_flash_get(): ?array {
  if (empty($_SESSION['admin_flash'])) return null;
  $f = $_SESSION['admin_flash'];
  unset($_SESSION['admin_flash']);
  return is_array($f) ? $f : null;
}

function admin_statuses(): array {
  return ['NEW','PENDING','IN_REVIEW','WAITING_REPORTER','RESOLVED','CLOSED','ARCHIVED'];
}

function admin_status_tag_class(string $status): string {
  $s = strtoupper(trim($status));
  return match ($s) {
    'NEW' => 'tag-warn',
    'PENDING' => 'tag-warn',
    'WAITING_REPORTER' => 'tag-warn',
    'IN_REVIEW' => 'tag-info',
    'RESOLVED' => 'tag-ok',
    'CLOSED' => 'tag-neutral',
    'ARCHIVED' => 'tag-neutral',
    default => 'tag-neutral',
  };
}

function admin_format_bytes(int $bytes): string {
  $bytes = max(0, $bytes);
  if ($bytes < 1024) return $bytes . ' B';
  $kb = $bytes / 1024;
  if ($kb < 1024) return number_format($kb, 1) . ' KB';
  $mb = $kb / 1024;
  if ($mb < 1024) return number_format($mb, 1) . ' MB';
  $gb = $mb / 1024;
  return number_format($gb, 1) . ' GB';
}

function admin_audit(mysqli $db, ?int $report_id, string $action, string $actor_type, ?string $actor_label, array $meta = []): void {
  $rid = $report_id ? (int)$report_id : null;
  $ip = $_SERVER['REMOTE_ADDR'] ?? null;
  $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
  $meta_json = $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

  $sql = "INSERT INTO portal_audit_log (report_id, action, actor_type, actor_label, ip, user_agent, meta_json)
          VALUES (?, ?, ?, ?, ?, ?, ?)";
  $st = $db->prepare($sql);
  $st->bind_param(
    "issssss",
    $rid,
    $action,
    $actor_type,
    $actor_label,
    $ip,
    $ua,
    $meta_json
  );
  $st->execute();
  $st->close();
}
