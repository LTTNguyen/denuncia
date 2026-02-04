<?php
// denuncia/api/report_login.php
require_once dirname(__DIR__) . "/_bootstrap.php";
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();

$report_key = strtoupper(trim($_POST['report_key'] ?? ""));
$password   = $_POST['password'] ?? "";

if ($report_key === "" || $password === "") {
  $_SESSION['portal_err'] = "Falta Clave o contraseña.";
  header("Location: ../seguimiento.php");
  exit;
}

$db = db_conn();
$stmt = $db->prepare("SELECT id, password_hash FROM portal_report WHERE report_key=? LIMIT 1");
$stmt->bind_param("s", $report_key);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || !password_verify($password, $row['password_hash'])) {
  $_SESSION['portal_err'] = "Clave o contraseña incorrectas.";
  header("Location: ../seguimiento.php");
  exit;
}

$_SESSION['portal_report_id'] = (int)$row['id'];
header("Location: ../caso.php");
