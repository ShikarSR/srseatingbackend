<?php
$servername = "localhost";  // MySQL host
$username = "root";         // MySQL username
$password = "";             // MySQL password
$dbname = "contact_form";   // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
