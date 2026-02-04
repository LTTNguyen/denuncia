<?php
// denuncia/api/report_message.php
require_once dirname(__DIR__) . "/_bootstrap.php";
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['portal_report_id'])) {
  header("Location: ../seguimiento.php");
  exit;
}

$report_id = (int)$_SESSION['portal_report_id'];
$message = trim($_POST['message'] ?? "");

if ($message === "") {
  $_SESSION['portal_flash'] = "Nội dung trống.";
  header("Location: ../caso.php");
  exit;
}

$db = db_conn();
$stmt = $db->prepare("INSERT INTO portal_report_message (report_id, sender_type, message) VALUES (?, 'REPORTER', ?)");
$stmt->bind_param("is", $report_id, $message);
$stmt->execute();
$stmt->close();

$_SESSION['portal_flash'] = "Đã gửi bổ sung thông tin.";
header("Location: ../caso.php");
