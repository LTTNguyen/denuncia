<?php
$DENUNCIA_DB = [
  'host' => '127.0.0.1',
  'user' => 'root',
  'pass' => '',
  'name' => 'denuncias_portal',
  'port' => 3307,
  'charset' => 'utf8mb4',
];


$DENUNCIA_MAIL = [
  'enabled' => true,

  // Sender shown in email
  'from_email' => 'no-reply@localhost',
  'from_name'  => 'Canal de Denuncias',

  // 'mail' = PHP mail(); 'smtp' reserved for future upgrade
  'mode' => 'file',

  'fallback_recipients' => ['thuy.nguyen@tymelectricos.cl'],

  // Content controls
  'include_description' => true,
  'description_max_chars' => 700,
];
