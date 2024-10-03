<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: admin_panel.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name = $_POST['item_name'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];

    $conn = new mysqli('localhost', 'root', '', 'akbare_organic_dairy');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO items (item_name, cost_price, selling_price) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $item_name, $cost_price, $selling_price);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: add_items.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Items</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Add Items</h1>
        <nav>
            <ul>
            <li><a href="index.html">User Panel</a></li>
                <li><a href="add_items.php">Add Items</a></li>
                <li><a href="sell_items.php">Sell Items</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="admin_panel.php?logout=true">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <form action="add_items.php" method="POST">
            <label for="item_name">Item Name:</label>
            <input type="text" name="item_name" required>
            <label for="cost_price">Cost Price:</label>
            <input type="number" step="0.01" name="cost_price" required>
            <label for="selling_price">Selling Price:</label>
            <input type="number" step="0.01" name="selling_price" required>
            <button type="submit">Add Item</button>
        </form>
    </main>
</body>
</html>
