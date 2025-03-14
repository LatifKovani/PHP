<?php
require_once 'config.php';
require_once 'vendor/autoload.php'; // Google API Client library

// Google API configuration - same as in login-google.php
$clientID = '850605513978-4qve63vc96d1ievl7mm6om58gsoapcrg.apps.googleusercontent.com';
$clientSecret = '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';
$redirectURL = 'http://localhost:84/PROJECT/register-callback.php'; // New callback specifically for registration

// Creating a new Google client
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
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);
        
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
            header("Location: login.php");
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
                
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Error registering with Google: " . $conn->error;
                header("Location: register.php");
                exit();
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Google registration error: " . $e->getMessage();
        header("Location: register.php");
        exit();
    }
}
?>