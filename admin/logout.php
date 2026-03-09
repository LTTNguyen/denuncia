<?php
require_once __DIR__ . '/_admin_bootstrap.php';
$db = db_conn();
$admin = admin_current_user($db);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  admin_flash_set('warn', 'Usa el formulario de salida para cerrar la sesión.');
  redirect('/admin/index.php');
  exit;
}

if (!admin_csrf_check($_POST['csrf'] ?? null)) {
  admin_flash_set('warn', 'Solicitud inválida para cerrar la sesión.');
  redirect('/admin/index.php');
  exit;
}

if ($admin) {
  admin_audit($db, null, 'ADMIN_LOGOUT', 'INVESTIGATOR', (string)($admin['email'] ?? ''), []);
}

admin_logout(true);
admin_flash_set('ok', 'Sesión cerrada correctamente.');
redirect('/admin/login.php');
