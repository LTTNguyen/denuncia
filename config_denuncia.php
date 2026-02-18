<?php
$DENUNCIA_DB = [
  'host' => '127.0.0.1',
  'user' => 'root',
  'pass' => '',
  'name' => 'denuncias_portal',
  'port' => 3307,
  'charset' => 'utf8mb4',
];

$DENUNCIA_PORTAL = [
  // TyM = 1
  'single_company_id' => 1,
];

$DENUNCIA_MAIL = [
  'enabled' => true,

  // Sender shown in email
  'from_email' => 'no-responder@tymelectricos.cl',
  'from_name'  => 'Canal de Denuncias - TyM',

  // IMPORTANT: smtp | file | mail
  'mode' => 'file',

  // (OPTIONAL) dùng để tạo link tuyệt đối trong email (khuyến nghị)
  // Ví dụ: 'https://www.tymelectricos.cl/denuncias'
  // Khi test local có thể để ''.
  'public_base_url' => '',

  // Nội bộ (admin) luôn nhận được mail khi có denuncia mới
  'fallback_recipients' => ['denuncias@tymelectricos.cl'],

  // Controls
  'include_description' => true,
  'description_max_chars' => 700,

  // SMTP settings (để sẵn, sau này đổi mode='smtp' là chạy)
  'smtp' => [
    'host' => 'tymelectricos.cl',
    'port' => 465,          // 465=ssl | 587=tls
    'secure' => 'ssl',      // 'ssl' hoặc 'tls'
    'auth' => true,
    'username' => 'no-responder@tymelectricos.cl',
    'password' => getenv('DENUNCIA_SMTP_PASS') ?: '',
    'debug' => 0, // 0=off, 2=verbose
  ],

  // Gửi mail cho người report khi họ nhập email (và không anonymous)
  'send_reporter_copy' => true,
];
