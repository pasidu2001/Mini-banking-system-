<?php
// Database configuration
$servername = "localhost";   // Hostname (usually localhost)
$username = "root";          // Your MySQL username
$password = "";              // Your MySQL password
$dbname = "BankingSystem";  // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Stop script and display error if connection fails
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to utf8 for proper encoding
$conn->set_charset("utf8");

// Now $conn can be used for queries in your other PHP files
?>
