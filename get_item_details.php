<?php
// get_item_details.php
$conn = new mysqli('localhost', 'root', '', 'akbare_organic_dairy');

if (isset($_GET['id'])) {
    $item_id = $_GET['id'];
    
    // Fetch item details
    $result = $conn->query("SELECT * FROM items WHERE id = '$item_id'");
    
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        
        // Return the item details as JSON
        echo json_encode($item);
    } else {
        echo json_encode(['error' => 'Item not found']);
    }
}

$conn->close();
?>
