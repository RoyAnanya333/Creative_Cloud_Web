<?php
// Start session for auth system
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = "localhost";
$user = "root";           // Change if you have a different DB username
$pass = "";               // Change if you have a DB password
$db   = "creativecloudweb"; // Database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
