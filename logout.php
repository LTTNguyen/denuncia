<?php
require_once __DIR__ . "/_bootstrap.php";
unset($_SESSION['report_id'], $_SESSION['report_key']);
session_regenerate_id(true);
redirect("/");
