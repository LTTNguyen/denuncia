<?php
date_default_timezone_set('America/Santiago');
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//required files
require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

//Create an instance; passing `true` enables exceptions
//if (isset($_POST["email"])) {

  $correo = "denuncias@tymelectricos.cl";
  $remitente = "victor.pbravo@gmail.com";//$_POST["email"] no@mail.cl

$valid = PHPMailer::ValidateAddress($correo);
  if ($valid == false) {
    die(":P");
  }

  $mail = new PHPMailer(true);
  $nombres = "Denuncias Servicios Eléctricos TyM LTDA"; //strtoupper($_POST['nombre']." ".$_POST['apellido'])
  $seguimiento = "123456";//strtoupper($_POST['detalle']);
  $descripcion = "SOLO PRUEBA LOCAL"; //strtoupper($_POST['descripcion'])
  $fecha = date('Y-m-d H:i:s');
  $categoria = "Ley 23.333 2026";
  $lugar = "Oficina Bombero Villalobos 660";
  $titulo = "Denuncia Generadao Por mal trato en Oficina";


    //Server settings
    $mail->isSMTP();                              //Send using SMTP
    $mail->Host       = 'tymelectricos.cl';       //Set the SMTP server to send through
    $mail->SMTPAuth   = true;             //Enable SMTP authentication
    $mail->Username   = 'no-responder@tymelectricos.cl';   //SMTP write your email
    $mail->Password   = 'AVLim~yVD{#*';      //SMTP password
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->CharSet = 'UTF-8';
    $mail->addEmbeddedImage(__DIR__ . '/images/LOGO_TYM.jpg', 'logo_cid', 'LOGO_TYM.jpg');

$message = '
<html>
<head>
    <style>body, table, td { font-family: Arial, sans-serif; }.button { background-color: #5A5EF5; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin-top: 10px; border-radius: 5px; }.price { color: #000000; font-size: 12px; font-weight: bold; text-align: left;}.link {height: 48px;}.titulos4 {width: 50%;border-collapse: collapse;border-spacing: 0;text-align: center;white-space: nowrap;background-color: #FFFFFF;overflow-y: scroll;border-radius: 25px;margin-bottom: 3px}.titulos4 th {text-align: left;position: sticky;top: 0;z-index: 0;width: 10%;height: 3%;text-align: center;font-size: 10px;font-weight: 100;background-color: #D0D4D7;color:#3E2F2F;padding:2px; border:#D8DCE1 1px solid;}.titulos4 td{width: 10%;font-size: 10px;padding: 3px;color: #000000;border-left: solid 1px #f00; background-color: #E9E4E4; border-bottom: solid 1px #FFFFFF;text-align: left;}
    </style>
</head>
<body>
    <table width="100%" cellspacing="0" cellpadding="20" bgcolor="#f4f4f4">
        <tr>
            <th style="text-align: center; padding: 20px;"><h1>Se ha recibido su Denuncia!</h1></th>
        </tr>
        <tr>
            <td><p style="text-align: center; padding: 20px;">No olvide Guardar su Clave y Número de Reporte para Seguimiento</p></td>
        </tr>
        <tr>
            <td align="center"><img src="cid:logo_cid" alt="Logo Empresa" style="width:100px; height:80px;"></td>
        </tr>
    </table>

    <table width="100%" class="titulos4" align="center" cellpadding="20" bgcolor="#f4f4f4">
        <tr>
            <th><p class="price">Título:</p></th>
            <td>'.$titulo.'</td>
        </tr>
        <tr>
            <th><p class="price">Lugar:</p></th>
            <td>'.$lugar.'</td>
        </tr>
        <tr>
            <th><p class="price">Fecha Denuncia:</p></th>
            <td>'.$fecha.'</td>
        </tr>
        <tr>
            <th><p class="price">Categoría Denunciada:</p></th>
            <td>'.$categoria.'</td>
        </tr>
        <tr>
            <th><p class="price">Descripción Detallada:</p></th>
            <td>'.$descripcion.'</td>
        </tr>
        <tr>
            <th><p class="price">Número de Seguimiento:</p></th>
            <td>'.$seguimiento.'</td>
        </tr>
        <tr>
            <th align="center" colspan="2" style="background-color: #E2E2E2;"><a href="https://www.tymelectricos.cl/denuncias/seguimiento.php" class="button">Página Seguimiento</a></th>
        </tr>
    </table>
</body>
</html>
';

    

    //Recipients
    $mail->setFrom('no-responder@tymelectricos.cl','Denuncias No responder');
    $mail->addAddress($correo, $nombres);

if($remitente !== "no@mail.cl"){
    $mail->addAddress($remitente,'Sistema de Reportabilidad y Denuncias');
}

    //Content
    $mail->isHTML(true);               //Set email format to HTML
    $subject = "Denuncias Servicios Eléctricos TyM LTDA";
    $mail->Subject = $subject;   // email subject headings
    $mail->Body    = $message; //email message

    // Success sent message
if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo '<p>Message sent!</p>';
}
?>