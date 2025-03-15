<?php

$host = 'localhost';
$username = 'root';
$password = '';
$db = 'project-php';

$conn = new mysqli($host, $username, $password, $db);

if (isset($conn->connect_error)) {
    die ("Connection failed:" . $conn->connect_error);
}

?>