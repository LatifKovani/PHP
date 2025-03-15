<?php
    session_start();

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    
    include 'config.php'; //Kjo e mundeson qe config.php(konektimi i databazes) te kete qasje ne kete file.
    include '/opt/lampp/htdocs/PROJECT/Google/register-google.php';
    
    //Ky kusht funksion keshtu: nese kerkesa e serverit eshte me metoden POST atehere i merr ato te dhena me metoden POST. 
    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['register'])) {
        $name     = htmlspecialchars(trim($_POST['name'])); //htmlspecialchars() i shmang malicious code nga html ose javascript qe mund te shkruhet ne input field.
        $lname    = htmlspecialchars(trim($_POST['lname']));
        $email    = htmlspecialchars(trim($_POST['email']));
        $password = htmlspecialchars(trim($_POST['password']));

        //I ruan te gjitha errorat ne nje array
        $errors = [];
        
        //Nese njera nga inputet eshte empty shfaqet mesazhi.
        if (empty($name) || empty($lname) || empty($email) || empty($password)) {
            $errors[] = "All fields are required!";
        }

        //Nese input field i email eshte empty shfaqet mesazhi.
        if (empty($email)) {
            $errors[] = "Email is required!";
        }

        //Kontrollon se a eshte emaili i vlefshem, nese jo shfaqe error mesazhi.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format!";
        }

        //Nese passwordi nuk eshte me 8 karaktere kthen error mesazhin.
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long!";
        }

        //Nese passwordi nuk ka njeren nga keto kushtet kthen error.
        if (!preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password) || !preg_match("/[!@#$%^&*]/", $password)) {
            $errors[] = "Password must contain at least one uppercase letter, one number, and one special character!";
        }

        $check_stmt = $conn->prepare("SELECT ID FROM users WHERE email = ?"); //Pergadit dhe ekzekuto pyetjen ? eshte placeholder qe ndalon SQL injections.
        $check_stmt->bind_param("s", $email); //bind_param siguron qe te dhenat e perdoruesit te trajtohen si vlera jo si kod SQL.
        $check_stmt->execute();
        $check_stmt->store_result();

        //Nese numri i rreshtave ne db eshte me i madh se 1, atehere do te thote qe kjo email eshte e regjistruar dhe kthen error mesazhin.
        if ($check_stmt->num_rows > 0) {
            $_SESSION['error'] = "Email is already registered!";
            session_write_close();
            header("Location: login.php");
            exit();
        }

        
        $check_stmt->close();   

        //Nese nuk ka error, vazhdo me regjistrimin.
        if (empty($errors)) {

            //Hashed password mundeson enkriptimin e passwordit para se passwordi dergohet ne serverin e databazes.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            //conn->prepare mundeson insertimin e te dhenave ne databaze ne menyre te siguruar per t'iu shmangur SQL injections, ? jane placeholder ne vend te vlerave direkte dhe per kete arsye MYSQL nuk i trajton si kod SQL. MYSQL e ndan kodin SQL nga te dhenat per shkaqe sigurie. 
            $stmt = $conn->prepare("INSERT INTO users(name, lname, email, password) VALUES(?, ?, ?, ?)");

            //conn->bind_param kjo i lidh variablat me ?("te dhenat") ssss qendron per string,string,string,string.
            $stmt->bind_param("ssss", $name, $lname, $email, $hashed_password);

            //Ekzkuto variablen $stmt nese eshte successfully ateher me dergo ne formen e login, perndryshe error.
            if ($stmt->execute()) {
                $_SESSION['success'] = "Registration successfully! Please login.";
                header("Location: http://localhost:84/PROJECT/login.php");
                exit();
            } else {
                $errors[] = "Something went wrong. Please try again!"; 
            }

            //Mbyll insertimin e te dhenave.
            $stmt->close();
        }

        //Nese ka error, ruaji ne session dhe shfaqi ne session dhe rri ne te njejten faqe.
        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors); // Kombinoj errorat ne nje mesazh te vetem
        }

        //mbyll lidhjen
        $conn->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Form</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>
<body>
    <div class="container">
        <div class="form-container">
            <form action="register.php" method="POST" id="register-form">
                <?php
                if (isset($_SESSION['error'])) { // nese ka error ateher
                    echo "<p class='error'>" . $_SESSION['error'] . "</p>"; // shfaqe errorin
                    unset($_SESSION['error']); //fshije errorin pasi qe eshte shfaqur
                }

                if (isset($_SESSION['success'])) { //nese eshte regjistruar me sukses ateher
                    echo "<p class='success'>" . $_SESSION['success'] . "</p>"; //shfaq mesazhin qe eshte kryer me sukses
                    unset($_SESSION['success']); //fshije mesazhin pasi qe eshte shfaqur
                }
                ?>
                <h2>Sign up</h2>
                <hr>
                <input type="text" name="name" placeholder="Name" required>
                <input type="text" name="lname" placeholder="Last Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" value="submit" name="register">Sign up</button>
                
                
                <div class="social-icons">
                    <a href="<?php echo $googleRegisterUrl; ?>"><i class="fa-brands fa-google"></i></a>
                    <a href=""><i class="fa-brands fa-github"></i></a>
                </div>
                <p>Already have an account? <a href="login.php" id="">Login</a></p>
            </form>
        </div>
    </div>
</body>
</html>