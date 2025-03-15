<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', 'php_errors.log');

        include 'config.php';
        include '/opt/lampp/htdocs/PROJECT/Google/login-google.php';
        
        //Nese ne session ka error ateher shfaq 
        if (isset($_SESSION['error'])) {
            echo "<p class='error'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success'])) { //nese eshte regjistruar me sukses ateher
            echo "<p class='success'>" . $_SESSION['success'] . "</p>"; //shfaq mesazhin qe eshte kryer me sukses
            unset($_SESSION['success']); //fshije mesazhin pasi qe eshte shfaqur
        }
        
 
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $remember = isset($_POST['remember']);

        //nese fusha e email ose e passwordit jane te paplotesuara atehere rikthen mesazhin qe te gjitha fushat duhet te plotesohen
        if (empty($email) || !isset($_POST['password']) ||empty($password)) {
            $_SESSION['error'] = "All fields are required!";
            header("Location: login.php");
            exit();
        }

        //Kontrollo te dhenat e userit
        $stmt = $conn->prepare("SELECT ID, name, lname, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($ID, $name,$lname, $email, $hashed_password);
        $stmt->fetch();
        
        if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $ID;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            header("Location: dashboard.php");
            exit();
            /*
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
                */
        }else {
            $_SESSION['error'] = "Invalid email or password!";
            header("Location: login.php");
            exit();
        }

        $stmt->close();

        }

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Form</title>
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    </head>
    <body>
        <div class="container">
            <div class="form-container">
                <form action="" method="POST" id="login-form">
                    <h2>Sign in</h2>
                    <hr>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>

                    <div class="remember-forgot">
                      <!--  
                        <div class="remember-container">
                            <input type="checkbox" id="remember-me" class="remember-me" name="remember">
                            <label for="remember-me">Remember me</label>
                        </div>
                        -->
                            <a href="Forgot-Password/forgot-password.php" class="forgot-password">Forgot your Password?</a>
                    </div>


                    <button type="submit" value="submit" name="signin">Sign in</button>
                    
                    <div class="social-icons">
                        <a href="<?php echo $googleLoginUrl; ?>"><i class="fa-brands fa-google"></i></a>
                        <a href="#"><i class="fa-brands fa-github"></i></a>
                    </div>
                    <p>Don't have an account? <a href="register.php" id="">Register</a></p>
                </form>
            </div>
        </div>
    </body>
</html> 