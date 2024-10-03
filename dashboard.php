<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: admin_panel.php");
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'akbare_organic_dairy');

// Fetch today's sales detai;s for the pie chart and total sold prices
$sales_data = $conn->query("SELECT item_id, SUM(selling_quantity) as total_quantity, SUM(selling_price * selling_quantity) as total_selling_price FROM sales WHERE selling_date = CURDATE() GROUP BY item_id");

$sales_distribution = [];

$total_selling_price_today = 0; 
$total_profit_today = 0; 
$total_loss_today = 0; 

while ($row = $sales_data->fetch_assoc()) {
    $item = $conn->query("SELECT item_name, cost_price FROM items WHERE id = " . $row['item_id'])->fetch_assoc();
    if ($item) { 
        $sales_distribution[] = [
            'name' => $item['item_name'],
            'value' => $row['total_quantity']
        ];
        $total_selling_price_today += $row['total_selling_price'];

        // Calculate profit or loss
        $total_item_cost = $item['cost_price'] * $row['total_quantity'];
        if ($total_item_cost < $row['total_selling_price']) {
            $total_profit_today += ($row['total_selling_price'] - $total_item_cost);
        } else {
            $total_loss_today += ($total_item_cost - $row['total_selling_price']);
        }
    }
}



$sales_distribution_today = [];
$sales_distribution_yesterday = [];

// Today's sales data
$sales_data_today = $conn->query("SELECT item_id, SUM(selling_quantity) as total_quantity FROM sales WHERE selling_date = CURDATE() GROUP BY item_id");

while ($row = $sales_data_today->fetch_assoc()) {
    $item = $conn->query("SELECT item_name FROM items WHERE id = " . $row['item_id'])->fetch_assoc();
    if ($item) {
        $sales_distribution_today[] = [
            'name' => $item['item_name'],
            'value' => $row['total_quantity']
        ];
    }
}

// Yesterday's sales data
$sales_data_yesterday = $conn->query("SELECT item_id, SUM(selling_quantity) as total_quantity FROM sales WHERE selling_date = CURDATE() - INTERVAL 1 DAY GROUP BY item_id");

while ($row_yesterday = $sales_data_yesterday->fetch_assoc()) {
    $item = $conn->query("SELECT item_name FROM items WHERE id = " . $row_yesterday['item_id'])->fetch_assoc();
    if ($item) {
        $sales_distribution_yesterday[] = [
            'name' => $item['item_name'],
            'value' => $row_yesterday['total_quantity']
        ];
    }
}



// Fetch yesterday's sales data
$sales_data_yesterday = $conn->query("SELECT item_id, SUM(selling_quantity) as total_quantity, SUM(selling_price * selling_quantity) as total_selling_price FROM sales WHERE selling_date = CURDATE() - INTERVAL 1 DAY GROUP BY item_id");

$total_selling_price_yesterday = 0;  

$total_profit_yesterday = 0; 
$total_loss_yesterday = 0;  

while ($row_yesterday = $sales_data_yesterday->fetch_assoc()) {
    $item = $conn->query("SELECT item_name, cost_price FROM items WHERE id = " . $row_yesterday['item_id'])->fetch_assoc();
    if ($item) {
        $total_selling_price_yesterday += $row_yesterday['total_selling_price']; // Add to total sales for yesterday

        $total_item_cost = $item['cost_price'] * $row_yesterday['total_quantity'];
        if ($total_item_cost < $row_yesterday['total_selling_price']) {
            $total_profit_yesterday += ($row_yesterday['total_selling_price'] - $total_item_cost);
        } else {
            $total_loss_yesterday += ($total_item_cost - $row_yesterday['total_selling_price']);
        }
    }
}


$total_profit_loss_today = $total_profit_today - $total_loss_today;
$total_profit_loss_yesterday = $total_profit_yesterday - $total_loss_yesterday;

// item delete 
if (isset($_GET['delete_item_id'])) {
    $delete_id = $_GET['delete_item_id'];
    $conn->query("UPDATE items SET is_active = 0 WHERE id='$delete_id'");
    header("Location: dashboard.php?success=Item marked as inactive");
}

// sale delete request
if (isset($_GET['delete_sale_id'])) {
    $delete_sale_id = $_GET['delete_sale_id'];
    $conn->query("DELETE FROM sales WHERE id='$delete_sale_id'");
    header("Location: dashboard.php?success=Sale record deleted successfully");
}

// sell request
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
            header("Location: dashboard.php?success=Item sold successfully");
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error: Selling price not found.";
    }
}

// Fetch all active items for listing
$items_result = $conn->query("SELECT * FROM items WHERE is_active = 1");

// Fetch all sold items for listing
$sold_items_result = $conn->query("SELECT sales.*, items.item_name FROM sales JOIN items ON sales.item_id = items.id WHERE items.is_active IN (0,1)");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
    }
    header {
        background: #4CAF50;
        color: white;
        padding: 10px 20px;
        text-align: center;
    }
    nav ul {
        list-style: none;
        padding: 0;
        text-align: center;
    }
    nav ul li {
        display: inline-block;
        margin-right: 15px;
    }
    nav ul li a {
        color: white;
        text-decoration: none;
        font-weight: bold;
    }
    main {
        margin-top: 20px;
        padding: 0 10px;
    }
    h3 {
        color: #333;
        /* margin: 0;
        padding: 0; */
    }
    canvas {
        max-width: 600px;
        margin: 20px auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    table th, table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ccc;
    }
    table th {
        background-color: #4CAF50;
        color: white;
    }
    table tr:hover {
        background-color: #f2f2f2;
    }
    .alert {
        padding: 10px;
        background-color: #d4edda;
        color: #155724;
        margin-bottom: 20px;
        border: 1px solid #c3e6cb;
        border-radius: 5px;
    }

    .bodyitems {
        margin: 0;
        padding: 0;
        width: 100%;
    }

    p{
        color: black;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        header {
            padding: 15px;
        }
        nav ul li {
            display: block;
            text-align: center;
            margin: 10px 0;
        }
        table th, table td {
            padding: 8px;
            font-size: 14px;
        }
        canvas {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        header {
            font-size: 18px;
        }
        table th, table td {
            font-size: 12px;
        }
        nav ul li {
            margin: 5px 0;
        }
    }

    @media (max-width: 360px) {
        nav ul li {
            margin: 3px 0;
        }
        table th, table td {
            margin: 0.8px;
            padding: 0.5px;
            font-size: 12px;
        }

        p{
            margin: 5px;
            padding: 5px;
        }
    }
</style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1>Dashboard</h1>
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

        <canvas id="salesComparisonChart" style="max-width: 600px; margin: auto;"></canvas>

<script>
    const ctx = document.getElementById('salesComparisonChart').getContext('2d');
    const salesComparisonData = {
        labels: <?= json_encode(array_column($sales_distribution_today, 'name')) ?>,
        datasets: [{
            label: 'Today\'s Sales',
            data: <?= json_encode(array_column($sales_distribution_today, 'value')) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: 'Yesterday\'s Sales',
            data: <?= json_encode(array_column($sales_distribution_yesterday, 'value')) ?>,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    };

    const salesComparisonChart = new Chart(ctx, {
        type: 'bar',
        data: salesComparisonData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Today vs Yesterday Sales Comparison'
                }
            }
        }
    });
</script>

<b>
<p><b>Yesterday's Sales Total: ₹<?php echo number_format($total_selling_price_yesterday, 2); ?></b></p>
<p>Yesterday's Profit/Loss: ₹<?php echo number_format($total_profit_loss_yesterday, 2); ?></p>
<p><b style="color: green;">Yesterday's Profit: ₹<?php echo number_format($total_profit_yesterday, 2); ?></b></p>
<p><b style="color: red;">Yesterday's Loss: ₹<?php echo number_format($total_loss_yesterday, 2); ?></b></p>
<br>
<p>Today's Total Sales: ₹<?php echo number_format($total_selling_price_today, 2); ?></p>
<p>Today's Profit/Loss: ₹<?php echo number_format($total_profit_loss_today, 2); ?></p>
<p style="color: green;">Today's Profit: ₹<?php echo number_format($total_profit_today, 2); ?></p>
<p style="color: red;">Today's Loss: ₹<?php echo number_format($total_loss_today, 2); ?></p>
</b>

        <canvas id="salesChart" style="max-width: 600px; margin: auto;"></canvas>

        </form>

       <div class="bodyitems">
       <h3>Items List</h3>
        <table>
            <thead>
                <tr>
                    <!-- <th>ID</th> -->
                    <th>Item Name</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items_result->fetch_assoc()): ?>
                <tr>
                    <!-- <td><?= $item['id'] ?></td> -->
                    <td><?= $item['item_name'] ?></td>
                    <td><?= $item['cost_price'] ?></td>
                    <td><?= $item['selling_price'] ?></td>
                    <td>
    <button><a href="update_items.php?id=<?= $item['id'] ?>">Edit</a></button>
    <button><a href="?delete_item_id=<?= $item['id'] ?>" onclick="return confirm('Are you sure you want to mark this item as inactive?');">Delete</a></button>
    <?php if ($item['is_active'] == 0): ?>
        <a href="reactivate_item.php?id=<?= $item['id'] ?>">Reactivate</a>
    <?php endif; ?>
</td>

                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3>Sold Items List</h3>
        <table>
            <thead>
                <tr>
                    <!-- <th>ID</th> -->
                    <th>Name</th>
                    <th>Date</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($sold_item = $sold_items_result->fetch_assoc()): ?>
                <tr>
                    <!-- <td><?= $sold_item['id'] ?></td> -->
                    <td><?= $sold_item['item_name'] ?></td>
                    <td><?= $sold_item['selling_date'] ?></td>
                    <td><?= $sold_item['selling_quantity'] ?></td>
                    <td>
                        <button><a href="?delete_sale_id=<?= $sold_item['id'] ?>" onclick="return confirm('Are you sure you want to delete this sale record?');">Delete</a></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

       </div>
    <script>
        const ctxx = document.getElementById('salesChart').getContext('2d');
        const salesData = {
            labels: <?= json_encode(array_column($sales_distribution, 'name')) ?>,
            datasets: [{
                label: 'Sales Quantity',
                data: <?= json_encode(array_column($sales_distribution, 'value')) ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        };

        const salesChart = new Chart(ctxx, {
            type: 'pie',
            data: salesData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Sales Distribution Today'
                    }
                }
            }
        });
    </script>
</body>
</html>



