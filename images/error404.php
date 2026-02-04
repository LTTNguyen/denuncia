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
	<title>No Autorizado</title>
</head>
<body>
<div class="Div8">
	<span>Página en Construcción</span>
	<br>
	<span>Consulte con su Administrador. Lo sentimos :´(</span>
	<br>
	<input type="button" class="button3D" id="close" value="Cerrar X">
</div>
<div class="Div4" align="center">
<img src="../images/error-404.png" alt="Error 404" width="600" height="400">	
</div>
</body>
<script type="text/javascript">
document.getElementById('close').onclick = function(){open(location, '_self').close();}
</script>
</html>
