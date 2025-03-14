<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../config.php";

date_default_timezone_set("Europe/Berlin");

if (!isset($_POST['token'])) {
    die("No token found in the URL.");
}

if (!isset($_POST['password']) || empty(trim($_POST['password']))) {
      die("Password is required.");
  }
  
  
  $token = $_POST['token'];
  $password = trim($_POST['password']);
  $password_confirm = trim($_POST['password_confirm']);

$token_hash = hash("sha256", $token);

$check_stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = ?");
$check_stmt->bind_param("s", $token_hash);
$check_stmt->execute();

$result = $check_stmt->get_result();
$user = $result->fetch_assoc();

if ($user === null) {
    die("Token not found");
}

$expiry_time = new DateTime($user['reset_token_expires_at']);
$current_time = new DateTime();

if ($expiry_time < $current_time) {
      die("Token has expired");
  }

  $errors = [];

//Nese passwordi nuk eshte me 8 karaktere kthen error mesazhin.
if (strlen($password) < 8) {
      $errors[] = "Password must be at least 8 characters long!";
}

//Nese passwordi nuk ka njeren nga keto kushtet kthen error.
if (!preg_match("/[A-Z]/", $password)) {
      $errors[] = "Password must contain at least one uppercase letter!";
}

if (!preg_match("/[0-9]/", $password)) {
      $errors[] = "Password must conatin at least one number!";
}

if (!preg_match("/[!@#$%^&*]/", $password)) {
      $errors[] = "Password must contain at least one special characater!";
}

if ($_POST['password'] !== $_POST['password_confirm']) {
      $errors[] = "Passwords do not match!";
  }
  

if (!empty($errors)) {
      die(implode("<br>", $errors)); 
  }

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$check_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE ID = ?"); 

$check_stmt->bind_param("si", $hashed_password, $user["ID"]);

$check_stmt->execute();

header("Location: login.php?message=reset_success");
exit;
?>