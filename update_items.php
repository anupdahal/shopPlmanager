<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: admin_panel.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'akbare_organic_dairy');

// Check if 'id' is passed via the URL
$item_id = isset($_GET['id']) ? $_GET['id'] : null;
$item = null;

if ($item_id) {
    // Fetch the item details
    $result = $conn->query("SELECT * FROM items WHERE id = '$item_id'");
    $item = $result->fetch_assoc();
}

// Handle the update request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];

    // Update item details
    $conn->query("UPDATE items SET item_name='$item_name', cost_price='$cost_price', selling_price='$selling_price' WHERE id='$item_id'");
    header("Location: update_items.php?success=Item updated successfully&id=$item_id");
    exit;
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Item</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Update Item</h1>
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
        <?php if (isset($_GET['success'])): ?>
            <p><?= $_GET['success'] ?></p>
        <?php endif; ?>

        <!-- Pre-fill the form with item details if available -->
        <?php if ($item): ?>
        <form method="post" id="update-form">
            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">

            <label for="item_name">Item Name:</label>
            <input type="text" id="item_name" name="item_name" value="<?= $item['item_name'] ?>" required>

            <label for="cost_price">Cost Price:</label>
            <input type="number" id="cost_price" name="cost_price" step="0.01" value="<?= $item['cost_price'] ?>" required>

            <label for="selling_price">Selling Price:</label>
            <input type="number" id="selling_price" name="selling_price" step="0.01" value="<?= $item['selling_price'] ?>" required>

            <button type="submit">Update Item</button>
        </form>
        <?php else: ?>
            <p>Item not found.</p>
        <?php endif; ?>
    </main>
</body>
</html>
