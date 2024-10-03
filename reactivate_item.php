<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'akbare_organic_dairy');

// Reactivate item
if (isset($_GET['id'])) {
    $reactivate_id = $_GET['id'];
    $conn->query("UPDATE items SET is_active = 1 WHERE id='$reactivate_id'");
    header("Location: dashboard.php?success=Item reactivated successfully");
}
?>
