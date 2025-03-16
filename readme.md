<?

session_start(); <!-- Fillon nje sesion per te ruajtur te dhenat user ID, email ,name.

<!--send-password-reset.php -->

$email = trim($_POST['email']); <!-- E merr email e userit nga forma e cila eshte bere submit, kjo perdoret per te verifikuar userin ne databaze.

<!-- random_bytes(16) i gjeneron 128 bit random dhe me funksionin bin2hex e konverton 128 bit ne hexadecimal string                                       1 byte = 8 bit , 16 byte = 128 bit -->
random_bytes(16) = 0011010101010000011001 
bin2hex = 1a2b3c4d5e6f708192a3b4c5d6e7f809 

$token_hash = hash("sha256", $token);  <!-- Hash kryen enkriptimin e tokenit origjinal. sha256 eshte nje algoritem e cila prodhon nje hexadecimal string prej 64 karakterave -->

$expiry = date("Y-m-d H:i:s", time() + 60 * 30);  time():--- kthen kohen e tashme, 60 * 30--- i shton 30 minuta se kohes tanishme, date("Y-m-d H:i:s")--- e kthen kohen e tanishme si kohe te mundshme per MYSQL.

$conn->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ? "); // Pregadit nje SQL statement per ekzekutim.
bind_param("sss", $token_hash, $expiry, $email);    <!-- I lidh tokenin e enkriptuar, kohen e skadimit dhe emailin me SQL Statement.
execute();      <!-- Ekzekuton prepared statement.
if ($check_stmt-> affected_rows > 0) <!--Kontrollon nese databaza eshte bere update.
affected_rows <!--Kthen numrin e rreshtave qe jane ndikuar nga SQL statement.

//mailer.php
isSMTP();    <!-- perdoret per t'i treguar PHPMailer te perdor SMTP per dergimin e emails.
Host         <!-- adresa e SMTP psh smtp.gmail.com server addresa e gmail.
SMTPAuth     <!-- aktivizon SMTP Authentication. Kjo eshte e kerkuar nga gmail.
Username     <!-- email addresa e gmail personale psh latifkovani14@gmail.com
Password     <!-- eshte App password nga gmail personal.
SMTPSecure   <!-- specifikon metoden e enkriptimit PHPMailer::ENCRYPTION_STARTTLS aktivizon enkriptimin TLS.
Port         <!-- Porti i SMTP per GMAIL eshte 587 per yahoo ose outlook mund te jete ndryshe.
return $mail <!-- e kthen PHPMailer tkonfigurar e cila mund te perdoret ne scriptat tjera.

<!--login.php -->
$_POST['code'] <!-- Google dergon nje autorizim kod si query ne redirect URL.
fetchAccessTokenWithAuthCode() <!--Nderron kodin e autorizim per nje token te aksesueshem.
setAccessToken(): <!-- Sets the access token in the Google client for subsequent API requests.

            <!-- RememerMe.php --->
                if ($remember) {
                $token = bin2hex(random_bytes(64));  //Gjenero nje token te sigurt.
                $hashed_token = password_hash($token, PASSWORD_DEFAULT); //Enkripto para se te ruaj
                $expiry = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); //Tokeni skadon per 30 dite.

                //Ruaje tokenin ne databaze
                $stmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE ID = ?");
                $stmt->bind_param("ssi", $hashed_token, $expiry, $ID);
                $stmt->execute();

                //Ruaje tokenin
                echo "<script>
                    <localStorage.setItem('remember_token', '$token');
                    </script>";
            }
        