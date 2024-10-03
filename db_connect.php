<?php
$host = 'localhost';        // same yai
$db = 'akbare_organic_dairy';   // database ko name
$user = 'root'; // default yai
$pass = '';  // default yai

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
