<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: admin_panel.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'akbare_organic_dairy');

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // Delete item only from items table
    $conn->query("DELETE FROM items WHERE id='$delete_id'");
    header("Location: delete_items.php?success=Item deleted successfully");
}

// Fetch all items
$items = $conn->query("SELECT * FROM items");

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Items</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Delete Items</h1>
        <nav>
            <ul>
                <li><a href="admin_panel.php">Admin</a></li>
                <li><a href="add_items.php">Add Items</a></li>
                <li><a href="sell_items.php">Sell Items</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?php if (isset($_GET['success'])): ?>
            <p><?= $_GET['success'] ?></p>
        <?php endif; ?>

        <h3>Items List</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Item Name</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><?= $item['item_name'] ?></td>
                    <td><?= $item['cost_price'] ?></td>
                    <td><?= $item['selling_price'] ?></td>
                    <td><a href="?delete_id=<?= $item['id'] ?>" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
