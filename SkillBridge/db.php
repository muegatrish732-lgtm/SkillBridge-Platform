<?php
// db.php - Database connection configuration
$host = 'localhost';
$user = 'root'; // Default XAMPP username
$pass = '';     // Default XAMPP password (empty)
$dbname = 'skillbridge_db';

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>