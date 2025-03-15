<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



if (!file_exists('/opt/lampp/htdocs/PROJECT/config.php')) {
    die('config.php not found!');
}
require_once '/opt/lampp/htdocs/PROJECT/config.php';

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die('vendor/autoload.php not found!');
}
require_once __DIR__ . '/../vendor/autoload.php';


// Load .env file from the root folder
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$googleClientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];

// Google API configuration
$clientID = '850605513978-4qve63vc96d1ievl7mm6om58gsoapcrg.apps.googleusercontent.com';
$clientSecret = $googleClientSecret;
$redirectURL = 'http://localhost:84/PROJECT/Google/google-callback.php'; // Replace with your actual domain

// Initialize Google Client
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectURL);
$client->addScope('email');
$client->addScope('profile');


// Create the Google login URL
$googleLoginUrl = $client->createAuthUrl();

// If this is a callback from Google with the authorization code
if (isset($_GET['code'])) {
    echo "Authorization code received: " . $_GET['code'] . "<br>";

    // Exchange authorization code for access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    // Check for errors in the token response
    if (isset($token['error'])) {
        die('Error fetching access token: ' . $token['error']);
    }

    // Set the access token
    $client->setAccessToken($token);

    // Handle token expiry
    if ($client->isAccessTokenExpired()) {
        $refreshToken = $client->getRefreshToken();
        if ($refreshToken) {
            $client->fetchAccessTokenWithRefreshToken($refreshToken);
        } else {
            die('Refresh token is missing. Please re-authenticate.');
        }
    }

    // Get user profile info
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();

    // Get user data
    $google_email = $google_account_info->email;
    $google_name = $google_account_info->givenName;
    $google_lname = $google_account_info->familyName;
    $google_id = $google_account_info->id;

    // Check if user exists in database
    $stmt = $conn->prepare("SELECT ID, name, lname, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $google_email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($ID, $name, $lname, $email);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // User exists, create session
        $_SESSION['user_id'] = $ID;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        // Redirect to dashboard.php in the root folder
        header("Location: /PROJECT/dashboard.php");
        exit();
    } else {
        // User doesn't exist, create new account
        // Generate a random password for the Google user
        $random_password = bin2hex(random_bytes(12));
        $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);

        // Insert new user
        $insert_stmt = $conn->prepare("INSERT INTO users (name, lname, email, password, google_id) VALUES (?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssss", $google_name, $google_lname, $google_email, $hashed_password, $google_id);

        if ($insert_stmt->execute()) {
            $_SESSION['user_id'] = $insert_stmt->insert_id;
            $_SESSION['user_name'] = $google_name;
            $_SESSION['user_email'] = $google_email;

            // Redirect to dashboard.php in the root folder
            header("Location: /PROJECT/dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Error registering with Google: " . $conn->error;
            header("Location: /PROJECT/login.php");
            exit();
        }
    }
}

?>