<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Display user information
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head> 
<body>
    <h1>Welcome, <?php echo $_SESSION['name']; ?>!</h1>
    <p>Email: <?php echo $_SESSION['email']; ?></p>
    <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture">
    <a href="logout.php">Logout</a>
</body>
</html>