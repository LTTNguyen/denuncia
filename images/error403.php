<!DOCTYPE html>
<?php
// Initialize the session
session_start();
$newDate = "";
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){

    header("location: ../index.php");
    exit;
	}
?>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="../css/styles.css">
	<link rel="stylesheet" href="../css/bootstrap.min.css">
	<title>No Autorizado</title>
</head>
<body>
<div class="Div12">
	<span>No está autorizado para ingresar a este formulario</span>
	<br>
	<span>Consulte con su Administrador</span>
	<br>
	<input type="button" class="btn btn-success" id="close" value="Cerrar X">
</div>
<div class="DivNav" align="center">
<img src="../images/error-403.jpg" alt="Error 403" width="600" height="400">	
</div>
</body>
<script src="../funciones/error-403.js"></script>
</html>
