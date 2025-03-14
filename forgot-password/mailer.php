<?php

// Kjo perdor PHPMailer library e cila perdoret per te derguar emails permes SMTP (Simple Mail Transfer Protocol).
use PHPMailer\PHPMailer\PHPMailer; //PHPMailer perdored per me konfiguru dhe dergu emails.
use PHPMailer\PHPMailer\Exception; //Exception perdoret per trajtimin e errorave qe mund te ndodhin gjate dergimit te emails.

// Loads Composer's autoloader
require __DIR__ . '/vendor/autoload.php';

// Krijon nje instance te re PHPMailer
$mail = new PHPMailer(true); // true i leshon Exeptions, nese ndonje error ndodhe gjate dergimit PHPMailer jep nje Exception qe mund te trajtohet.

// Konfigruimi i SMTP Serverit
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com'; // SMTP server
$mail->SMTPAuth = true; // Enable SMTP authentication
$mail->Username = 'latifkovani14@gmail.com'; // Your email address
$mail->Password = 'zjpy geqa gpez xaln'; // Your email password or app password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
$mail->Port = 587; // Port for TLS

return $mail;
?>