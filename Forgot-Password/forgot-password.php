
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <div class="forgot-password-container" >
            <form action="/PROJECT/forgot-password/send-password-reset.php" method="POST" id="forgot-password">
                <h2>Forgot Password</h2>
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit" name="reset-button">Send Reset Link</button>
            </form>
        </div>
    </div>
</body>
</html>