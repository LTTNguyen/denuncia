<?php
$env = static fn(string $name, $default = null) => ($_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?: $default);
$envBool = static fn(string $name, bool $default = false): bool => filter_var($env($name, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
$envInt = static fn(string $name, int $default = 0): int => (int)($env($name, $default));

$DENUNCIA_APP = [
  'env' => strtolower(trim((string)$env('DENUNCIA_ENV', 'production'))),
  'force_https' => $envBool('DENUNCIA_FORCE_HTTPS', false),
  'trust_proxy' => $envBool('DENUNCIA_TRUST_PROXY', false),
  'security_headers' => $envBool('DENUNCIA_SECURITY_HEADERS', true),
  'public_base_url' => rtrim((string)$env('DENUNCIA_PUBLIC_BASE_URL', ''), '/'),
  'cookie_samesite_public' => (string)$env('DENUNCIA_COOKIE_SAMESITE_PUBLIC', 'Lax'),
  'cookie_samesite_admin' => (string)$env('DENUNCIA_COOKIE_SAMESITE_ADMIN', 'Strict'),
];

$DENUNCIA_DB = [
  'host' => (string)$env('DENUNCIA_DB_HOST', '127.0.0.1'),
  'user' => (string)$env('DENUNCIA_DB_USER', 'root'),
  'pass' => (string)$env('DENUNCIA_DB_PASS', ''),
  'name' => (string)$env('DENUNCIA_DB_NAME', 'denuncias_portal'),
  'port' => $envInt('DENUNCIA_DB_PORT', 3307),
  'charset' => (string)$env('DENUNCIA_DB_CHARSET', 'utf8mb4'),
];

$DENUNCIA_PORTAL = [
  'single_company_id' => $envInt('DENUNCIA_SINGLE_COMPANY_ID', 1),
];

$DENUNCIA_MAIL = [
  'enabled' => $envBool('DENUNCIA_MAIL_ENABLED', true),
  'from_email' => (string)$env('DENUNCIA_MAIL_FROM_EMAIL', 'no-responder@tymelectricos.cl'),
  'from_name'  => (string)$env('DENUNCIA_MAIL_FROM_NAME', 'Canal de Denuncias - TyM'),
  'mode' => strtolower((string)$env('DENUNCIA_MAIL_MODE', 'file')), // smtp | file | mail
  'public_base_url' => rtrim((string)$env('DENUNCIA_PUBLIC_BASE_URL', ''), '/'),
  'fallback_recipients' => array_values(array_filter(array_map('trim', explode(',', (string)$env('DENUNCIA_MAIL_FALLBACK_RECIPIENTS', 'denuncias@tymelectricos.cl'))))),
  'include_description' => $envBool('DENUNCIA_MAIL_INCLUDE_DESCRIPTION', true),
  'description_max_chars' => $envInt('DENUNCIA_MAIL_DESCRIPTION_MAX_CHARS', 700),
  'outbox_dir' => (string)$env('DENUNCIA_OUTBOX_DIR', __DIR__ . '/storage/outbox'),
  'smtp' => [
    'host' => (string)$env('DENUNCIA_SMTP_HOST', 'tymelectricos.cl'),
    'port' => $envInt('DENUNCIA_SMTP_PORT', 465),
    'secure' => strtolower((string)$env('DENUNCIA_SMTP_SECURE', 'ssl')),
    'auth' => $envBool('DENUNCIA_SMTP_AUTH', true),
    'username' => (string)$env('DENUNCIA_SMTP_USER', 'no-responder@tymelectricos.cl'),
    'password' => (string)$env('DENUNCIA_SMTP_PASS', ''),
    'debug' => $envInt('DENUNCIA_SMTP_DEBUG', 0),
  ],
  'send_reporter_copy' => $envBool('DENUNCIA_SEND_REPORTER_COPY', true),
];
