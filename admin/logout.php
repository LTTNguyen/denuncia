<?php
require_once __DIR__ . '/_admin_bootstrap.php';
unset($_SESSION['admin_id']);
redirect('/admin/login.php');
