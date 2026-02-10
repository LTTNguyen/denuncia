<?php

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

  $correo = "victor.pbravo@gmail.com";//$_POST["email"]

$valid = PHPMailer::ValidateAddress($correo);
  if ($valid == false) {
    die(":P");
  }

  $mail = new PHPMailer(true);
  $nombres = "VICTOR PAVEZ"; //strtoupper($_POST['nombre']." ".$_POST['apellido'])
  $detalle = "prueba";//strtoupper($_POST['detalle']);
  $descripcion = "SOLO PRUEBA LOCAL"; //strtoupper($_POST['descripcion'])

    //Server settings
    $mail->isSMTP();                              //Send using SMTP
    $mail->Host       = 'tymelectricos.cl';       //Set the SMTP server to send through
    $mail->SMTPAuth   = true;             //Enable SMTP authentication
    $mail->Username   = 'no-responder@tymelectricos.cl';   //SMTP write your email
    $mail->Password   = 'AVLim~yVD{#*';      //SMTP password
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->CharSet = 'UTF-8';

$message = '
<body">
<div style="background-color: #E9FCFC;color:#5B42F0; font-size: 14px; font-weight: 900;">Se ha recibido su Informe de Error</div>
<div><p style="color:#5B42F0; font-size: 12px;">Se comunicarán con usted de Servicio Técnico TyM</p></div>

<div style="font-size: 15px; color: #17118D; font-weight: 300;">
<span>Nombres: <p style="color: #171167;">'.$nombres.'</p></span>
<br>
<span>Detalle: <p style="color: #171167;">'.$detalle.'</p></span>
<br>
<span>Descripcion del Problema: <p style="color: #171167;">'.$descripcion.'</p></span>
<br>
</div>
<p>Gracias</p>
<p>Servicios Eléctricos TyM LTDA</p>
</body>
';

    //Recipients
    $mail->Sender='no-responder@tymelectricos.cl';
    $mail->SetFrom('nno-responder@tymelectricos.cl', 'Servicio IT');
    $mail->addAddress($correo, $nombres);
    $mail->addAddress('denuncias@tymelectricos.cl','Sistema de Reportabilidad y Denuncias');

    //Content
    $mail->isHTML(true);               //Set email format to HTML
    $subject = "DENUNCIAS";
    $mail->Subject = $subject;   // email subject headings
    $mail->Body    = $message; //email message

    // Success sent message alert
    $mail->send();

    $mail->Sender='no-responder@tymelectricos.cl';
    $mail->SetFrom('no-responder@tymelectricos.cl', '');

    //Content
    $mail->isHTML(true);               //Set email format to HTML
    $mail->Subject = $subject;   // email subject headings
    $mail->Body    = $message; //email message

    // Success sent message alert
    $mail->send();
//}
?>