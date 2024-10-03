<?php
$host = 'localhost';        // 
$db = 'akbare_organic_dairy';   // 
$user = 'root'; // 
$pass = '';  // 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
