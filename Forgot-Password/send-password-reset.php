<?php

 error_reporting(E_ALL);
 ini_set('display_errors', 1);
 include "../config.php";


$email = trim($_POST['email']); //Kthen email e userit nga forma e dorezuar.
$token = bin2hex(random_bytes(16)); //gjeneron 16 byte randome dhe bin2hex i konverton ne string hexadecimal.

$token_hash = hash("sha256", $token); //Perdor sha256 algoritem per gjenerimin e 64 char hexadecimal string.

$expiry = date("Y-m-d H:i:s", time() + 60 * 30); //Vendosja e nje kohe per skadimin e tokenit p.sh 30 min.


// Ben update tabelen user, reset_token_hash>>> ruan tokenin e enkriptuar, reset_token_expires_at>>> ruan skadimin e tokenit
$check_stmt = $conn->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ? ");

$check_stmt->bind_param("sss", $token_hash, $expiry, $email);

$check_stmt->execute();

// Kontrollon rreshat e ndikuar nese > 0 eshte bere update me sukses
if ($check_stmt-> affected_rows > 0) {

      $mail = include "mailer.php";

      $mail->setFrom("noreply@gmail.com", "No Reply");
      $mail->addAddress($email);
      $mail->Subject = "Password Reset";
      $mail->isHTML(true);
      $mail->Body = <<<END
      Click <a href="https://localhost/PROJECT/forgot-password/reset-password.php?token=$token">here</a>
      to reset your password.


      END;

       try {
            $mail->send();
       } catch (Exception $e) {
            echo "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
       }
}

echo "Message sent, please check your inbox.";

?>