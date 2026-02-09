<?php
$DENUNCIA_DB = [
  'host' => '127.0.0.1',
  'user' => 'root',
  'pass' => '',
  'name' => 'denuncias_portal',
  'port' => 3307,
  'charset' => 'utf8mb4',
];

/**
 * Email notifications (new report)
 *
 * IMPORTANT:
 * - Default mode uses PHP mail() which requires mail transport configured.
 * - Recommended for production: use SMTP (PHPMailer is not bundled yet in this demo).
 *
 * Recipients are normally taken from DB table: portal_notify_recipient.
 * If the table does not exist, the system uses fallback_recipients below.
 */
$DENUNCIA_MAIL = [
  'enabled' => true,

  // Sender shown in email
  'from_email' => 'no-reply@localhost',
  'from_name'  => 'Canal de Denuncias',

  // 'mail' = PHP mail(); 'smtp' reserved for future upgrade
  'mode' => 'file',

  // If you haven't created portal_notify_recipient yet, notifications go here.
  'fallback_recipients' => ['thuy.nguyen@tymelectricos.cl'],

  // Content controls
  'include_description' => true,
  'description_max_chars' => 700,
];
