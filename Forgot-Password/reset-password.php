<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/../config.php';

date_default_timezone_set("Europe/Berlin");

if (!isset($_GET['token'])) {
    die("No token found in the URL.");
}

$token = $_GET['token'];

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

if (strtotime($user['reset_token_expires_at']) <= time()) {
      die("Token has expired.");
  }
  
?>

<!DOCTYPE html>
<html lang="en">
<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Reset Password</title>
      <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container">
        <div class="forgot-password-container" >
            <form action="Forgot-Password/process-reset-password.php" method="POST" id="forgot-password">
                <h2>Forgot Password</h2>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token)?>">
                <input type="password" name="password" placeholder="Enter your Password" required>
                <input type="password" name="password_confirm" placeholder="Repeat your Password" required>
                <button type="submit" name="reset-button">Send</button>
            </form>
        </div>
    </div>
</body>
</html>
