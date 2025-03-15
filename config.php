<?php

$host = 'localhost';
$username = 'root';
$password = '';
$db = 'project-php';

$conn = new mysqli($host, $username, $password, $db);

if (isset($conn->connect_error)) {
    die ("Connection failed:" . $conn->connect_error);
}

define('FB_APP_ID', '1149743846530586'); 
define('FB_APP_SECRET', 'd0673dc12ab8d6ef616eda155dca7b62');

?>