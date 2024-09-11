<?php
$servername = "localhost"; // Your server name
$username = "root"; // Your database username
$password = "123"; // Your database password
$dbname = "bookstore_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>