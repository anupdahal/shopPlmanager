<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: admin_panel.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'akbare_organic_dairy');

// Fetch only active items for the sell dropdown
$items_result = $conn->query("SELECT * FROM items WHERE is_active = 1");

// Handle sell request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $selling_quantity = $_POST['selling_quantity'];

    // Fetch selling price from the database
    $item = $conn->query("SELECT selling_price FROM items WHERE id='$item_id'")->fetch_assoc();
    $selling_price = $item['selling_price'];

    // Check if selling_price was retrieved
    if ($selling_price !== null) {
        // Insert sale into the database
        $stmt = $conn->prepare("INSERT INTO sales (item_id, selling_price, selling_date, selling_quantity) VALUES (?, ?, ?, ?)");
        $selling_date = date('Y-m-d');
        $stmt->bind_param("idsi", $item_id, $selling_price, $selling_date, $selling_quantity);
        
        if ($stmt->execute()) {
            header("Location: sell_items.php?success=Item sold successfully");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error: Selling price not found.";
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Items</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Sell Items</h1>
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

        <form method="post">
            <label for="item_id">Select Item to Sell:</label>
            <select name="item_id" required>
                <option value="">Select an item</option>
                <?php while ($item = $items_result->fetch_assoc()): ?>
                    <option value="<?= $item['id'] ?>"><?= $item['item_name'] ?> (Price: <?= $item['selling_price'] ?>)</option>
                <?php endwhile; ?>
            </select>

            <label for="selling_quantity">Selling Quantity:</label>
            <input type="number" name="selling_quantity" min="1" required>

            <button type="submit">Sell Item</button>
        </form>
    </main>
</body>
</html>
