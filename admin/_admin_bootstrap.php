<?php

declare(strict_types=1);

/**
 * Admin bootstrap:
 * - Isolates admin session from reporter/public pages
 * - Centralizes auth, CSRF, flash and admin helpers
 */
if (!defined('DENUNCIA_SESSION_NAME')) {
  define('DENUNCIA_SESSION_NAME', 'DENUNCIAADMINSESSID');
}

require_once __DIR__ . '/../_bootstrap.php';

const ADMIN_IDLE_TIMEOUT_SECONDS = 1800;
const ADMIN_MAX_LOGIN_ATTEMPTS = 5;
const ADMIN_LOGIN_WINDOW_MINUTES = 15;

admin_send_nocache_headers();
admin_touch_session();

function admin_link(string $path): string {
  return rtrim(base_url(), '/') . $path;
}

function admin_send_nocache_headers(): void {
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Pragma: no-cache');
  header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

function admin_touch_session(): void {
  $userId = (int)($_SESSION['admin_user_id'] ?? 0);
  $now = time();

  if ($userId > 0) {
    $last = (int)($_SESSION['admin_last_activity'] ?? 0);
    if ($last > 0 && ($now - $last) > ADMIN_IDLE_TIMEOUT_SECONDS) {
      admin_logout(false);
      admin_flash_set('warn', 'Tu sesión de administrador expiró por inactividad.');
      redirect('/admin/login.php');
      exit;
    }
  }

  $_SESSION['admin_last_activity'] = $now;
}

function admin_rotate_session(): void {
  if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
  }
}

function admin_csrf_token(): string {
  if (empty($_SESSION['admin_csrf']) || !is_string($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['admin_csrf'];
}

function admin_csrf_check(?string $token): bool {
  $sessionToken = $_SESSION['admin_csrf'] ?? null;
  return is_string($token) && is_string($sessionToken) && hash_equals($sessionToken, $token);
}

function admin_password_verify(string $password, string $hash): bool {
  return $hash !== '' && password_verify($password, $hash);
}

function admin_flash_set(string $type, string $msg): void {
  $_SESSION['admin_flash'] = ['type' => $type, 'msg' => $msg];
}

function admin_flash_get(): ?array {
  if (empty($_SESSION['admin_flash']) || !is_array($_SESSION['admin_flash'])) {
    return null;
  }
  $flash = $_SESSION['admin_flash'];
  unset($_SESSION['admin_flash']);
  return $flash;
}

function admin_current_user(mysqli $db): ?array {
  $id = (int)($_SESSION['admin_user_id'] ?? 0);
  if ($id <= 0) {
    return null;
  }

  $sql = "SELECT id, email, full_name, role, is_active, last_login_at
          FROM portal_admin_user
          WHERE id = ?
          LIMIT 1";
  $st = $db->prepare($sql);
  if (!$st) {
    return null;
  }
  $st->bind_param('i', $id);
  $st->execute();
  $res = $st->get_result();
  $user = $res ? $res->fetch_assoc() : null;
  $st->close();

  if (!$user || (int)($user['is_active'] ?? 0) !== 1) {
    admin_logout(false);
    return null;
  }

  return $user;
}

function admin_require_login(mysqli $db): array {
  $user = admin_current_user($db);
  if (!$user) {
    admin_flash_set('warn', 'Debes iniciar sesión para entrar al panel admin.');
    redirect('/admin/login.php');
    exit;
  }
  return $user;
}

function admin_role_rank(?string $role): int {
  return match (strtoupper(trim((string)$role))) {
    'ADMIN' => 30,
    'INVESTIGATOR' => 20,
    'READONLY' => 10,
    default => 0,
  };
}

function admin_role_label(?string $role): string {
  return match (strtoupper(trim((string)$role))) {
    'ADMIN' => 'Administrador',
    'INVESTIGATOR' => 'Investigador',
    'READONLY' => 'Solo lectura',
    default => 'Sin rol',
  };
}

function admin_role_badge_class(?string $role): string {
  return match (strtoupper(trim((string)$role))) {
    'ADMIN' => 'tag-ok',
    'INVESTIGATOR' => 'tag-info',
    'READONLY' => 'tag-neutral',
    default => 'tag-neutral',
  };
}

function admin_user_display_name(?array $user): string {
  if (!is_array($user)) {
    return 'Administrador';
  }
  $name = trim((string)($user['full_name'] ?? ''));
  if ($name !== '') {
    return $name;
  }
  $email = trim((string)($user['email'] ?? ''));
  return $email !== '' ? $email : 'Administrador';
}

function admin_initials(?string $name): string {
  $parts = preg_split('/\s+/', trim((string)$name)) ?: [];
  $initials = '';
  foreach ($parts as $part) {
    if ($part === '') continue;
    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    if (mb_strlen($initials) >= 2) break;
  }
  if ($initials === '') {
    $clean = preg_replace('/[^A-Za-z0-9]/', '', (string)$name);
    $initials = strtoupper(substr($clean, 0, 2));
  }
  return $initials !== '' ? $initials : 'AD';
}

function admin_is_admin(?array $user): bool {
  return admin_role_rank($user['role'] ?? null) >= admin_role_rank('ADMIN');
}

function admin_can_edit(?array $user): bool {
  return admin_role_rank($user['role'] ?? null) >= admin_role_rank('INVESTIGATOR');
}

function admin_can_message(?array $user): bool {
  return admin_can_edit($user);
}

function admin_can_download_attachments(?array $user): bool {
  return admin_role_rank($user['role'] ?? null) >= admin_role_rank('READONLY');
}

function admin_require_role(array $user, string $role): void {
  if (admin_role_rank($user['role'] ?? null) < admin_role_rank($role)) {
    admin_flash_set('warn', 'No tienes permisos para acceder a esta sección.');
    redirect('/admin/index.php');
    exit;
  }
}

function admin_logout(bool $destroySession = true): void {
  unset(
    $_SESSION['admin_user_id'],
    $_SESSION['admin_csrf'],
    $_SESSION['admin_last_activity'],
    $_SESSION['admin_flash']
  );

  if ($destroySession && session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
  }
}

function admin_statuses(): array {
  return ['NEW', 'PENDING', 'IN_REVIEW', 'WAITING_REPORTER', 'RESOLVED', 'CLOSED', 'ARCHIVED'];
}

function admin_status_label(string $status): string {
  return match (strtoupper(trim($status))) {
    'NEW' => 'Nuevo',
    'PENDING' => 'Pendiente',
    'IN_REVIEW' => 'En revisión',
    'WAITING_REPORTER' => 'Esperando denunciante',
    'RESOLVED' => 'Resuelto',
    'CLOSED' => 'Cerrado',
    'ARCHIVED' => 'Archivado',
    default => ucfirst(strtolower(trim($status))),
  };
}

function admin_status_tag_class(string $status): string {
  $status = strtoupper(trim($status));
  return match ($status) {
    'NEW', 'PENDING', 'WAITING_REPORTER' => 'tag-warn',
    'IN_REVIEW' => 'tag-info',
    'RESOLVED' => 'tag-ok',
    'CLOSED', 'ARCHIVED' => 'tag-neutral',
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

function admin_status_exists(string $status): bool {
  return in_array(strtoupper(trim($status)), admin_statuses(), true);
}

function admin_status_transition_map(): array {
  return [
    'NEW' => ['PENDING', 'IN_REVIEW', 'WAITING_REPORTER', 'CLOSED'],
    'PENDING' => ['IN_REVIEW', 'WAITING_REPORTER', 'RESOLVED', 'CLOSED'],
    'IN_REVIEW' => ['WAITING_REPORTER', 'RESOLVED', 'CLOSED'],
    'WAITING_REPORTER' => ['IN_REVIEW', 'RESOLVED', 'CLOSED'],
    'RESOLVED' => ['CLOSED', 'IN_REVIEW', 'ARCHIVED'],
    'CLOSED' => ['IN_REVIEW', 'ARCHIVED'],
    'ARCHIVED' => ['IN_REVIEW'],
  ];
}

function admin_allowed_next_statuses(string $currentStatus, ?array $user): array {
  $currentStatus = strtoupper(trim($currentStatus));
  $allowed = admin_status_transition_map()[$currentStatus] ?? [];
  $role = strtoupper(trim((string)($user['role'] ?? '')));

  if ($role === 'ADMIN') {
    return $allowed;
  }

  if ($role === 'INVESTIGATOR') {
    return array_values(array_filter($allowed, static fn(string $status): bool => $status !== 'ARCHIVED'));
  }

  return [];
}

function admin_transition_allowed(string $currentStatus, string $newStatus, ?array $user): bool {
  $currentStatus = strtoupper(trim($currentStatus));
  $newStatus = strtoupper(trim($newStatus));
  if (!admin_status_exists($currentStatus) || !admin_status_exists($newStatus)) {
    return false;
  }
  if ($currentStatus === $newStatus) {
    return false;
  }
  return in_array($newStatus, admin_allowed_next_statuses($currentStatus, $user), true);
}

function admin_relative_time(?string $dateTime): string {
  $dateTime = trim((string)$dateTime);
  if ($dateTime === '') {
    return '-';
  }
  $ts = strtotime($dateTime);
  if ($ts === false) {
    return $dateTime;
  }
  $diff = time() - $ts;
  if ($diff < 60) return 'Hace menos de 1 min';
  if ($diff < 3600) return 'Hace ' . (int)floor($diff / 60) . ' min';
  if ($diff < 86400) return 'Hace ' . (int)floor($diff / 3600) . ' h';
  if ($diff < 172800) return 'Hace 1 día';
  if ($diff < 2592000) return 'Hace ' . (int)floor($diff / 86400) . ' días';
  return date('Y-m-d H:i', $ts);
}

function admin_excerpt(string $text, int $max = 140): string {
  $text = trim(preg_replace('/\s+/', ' ', $text));
  if (mb_strlen($text) <= $max) {
    return $text;
  }
  return rtrim(mb_substr($text, 0, $max - 1)) . '…';
}

function admin_is_rate_limited(mysqli $db, string $email, ?string $ip = null): bool {
  if (!portal_db_table_exists($db, 'portal_admin_login_attempt')) {
    return false;
  }

  $email = strtolower(trim($email));
  $ip = trim((string)$ip);
  $window = ADMIN_LOGIN_WINDOW_MINUTES;

  $sql = "SELECT COUNT(*) AS cnt
          FROM portal_admin_login_attempt
          WHERE success = 0
            AND created_at >= (NOW() - INTERVAL ? MINUTE)
            AND (email = ?" . ($ip !== '' ? " OR ip = ?" : "") . ")";
  $st = $db->prepare($sql);
  if (!$st) {
    return false;
  }

  if ($ip !== '') {
    $st->bind_param('iss', $window, $email, $ip);
  } else {
    $st->bind_param('is', $window, $email);
  }

  $st->execute();
  $row = $st->get_result()->fetch_assoc();
  $st->close();

  return (int)($row['cnt'] ?? 0) >= ADMIN_MAX_LOGIN_ATTEMPTS;
}

function admin_log_login_attempt(mysqli $db, string $email, bool $success): void {
  if (!portal_db_table_exists($db, 'portal_admin_login_attempt')) {
    return;
  }

  $email = strtolower(trim($email));
  $ip = portal_client_ip();
  $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
  $successInt = $success ? 1 : 0;

  $st = $db->prepare('INSERT INTO portal_admin_login_attempt (email, success, ip, user_agent) VALUES (?, ?, ?, ?)');
  if (!$st) {
    return;
  }
  $st->bind_param('siss', $email, $successInt, $ip, $ua);
  $st->execute();
  $st->close();
}

function admin_bind_params(mysqli_stmt $stmt, string $types, array &$params): void {
  if ($types === '') {
    return;
  }

  $bind = [$types];
  foreach ($params as $key => &$value) {
    $bind[] = &$value;
  }
  call_user_func_array([$stmt, 'bind_param'], $bind);
}

function admin_audit(mysqli $db, ?int $reportId, string $action, string $actorType, ?string $actorLabel, array $meta = []): void {
  $actorType = in_array($actorType, ['REPORTER', 'INVESTIGATOR', 'SYSTEM'], true) ? $actorType : 'SYSTEM';
  audit_log($db, $reportId, $action, $actorType, $actorLabel, $meta);
}
