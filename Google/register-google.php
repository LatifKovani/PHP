<?php

require_once __DIR__ . '/../config.php'; // Correct path to config.php
require_once __DIR__ . '/../vendor/autoload.php'; // Correct path to vendor/autoload.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Load .env file from the root folder
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$googleClientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];

// Google API configuration
$clientID = '850605513978-4qve63vc96d1ievl7mm6om58gsoapcrg.apps.googleusercontent.com';
$clientSecret = $googleClientSecret;
$redirectURL = 'http://localhost:84/PROJECT/Google/register-callback.php'; // Ensure this matches Google API Console

// Initialize Google Client
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectURL);
$client->addScope('email');
$client->addScope('profile');


// Create the Google register URL
$googleRegisterUrl = $client->createAuthUrl();

// If this is a callback from Google with the authorization code
if (isset($_GET['code'])) {
    echo "Authorization code received: " . $_GET['code'] . "<br>";

    try {
        // Exchange authorization code for access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        // Check for errors in the token response
        if (isset($token['error'])) {
            die('Error fetching access token: ' . $token['error']);
        }

        // Debug the token
        echo "<pre>";
        print_r($token);
        echo "</pre>";

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

        // Check if user already exists
        $stmt = $conn->prepare("SELECT ID FROM users WHERE email = ? OR google_id = ?");
        $stmt->bind_param("ss", $google_email, $google_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // User already exists
            $_SESSION['error'] = "An account with this email already exists. Please log in instead.";
            header("Location: /PROJECT/login.php"); // Redirect to login page in root folder
            exit();
        } else {
            // Create new user account
            $random_password = bin2hex(random_bytes(12));
            $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);

            // Insert new user
            $insert_stmt = $conn->prepare("INSERT INTO users (name, lname, email, password, google_id) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $google_name, $google_lname, $google_email, $hashed_password, $google_id);

            if ($insert_stmt->execute()) {
                $_SESSION['user_id'] = $insert_stmt->insert_id;
                $_SESSION['user_name'] = $google_name;
                $_SESSION['user_email'] = $google_email;
                $_SESSION['success'] = "Registration successful! Welcome to our platform.";

                // Redirect to dashboard.php in the root folder
                header("Location: /PROJECT/dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Error registering with Google: " . $conn->error;
                header("Location: /PROJECT/register.php"); // Redirect to register page in root folder
                exit();
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Google registration error: " . $e->getMessage();
        header("Location: /PROJECT/register.php"); // Redirect to register page in root folder
        exit();
    }
}

?>