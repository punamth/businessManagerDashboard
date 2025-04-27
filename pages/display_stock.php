<?php 
include '../includes/db_connect.php';

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Delete Stock
if (isset($_GET['delete'])) {
    $item_id = $_GET['delete'];
    $sql_delete = "DELETE FROM stock WHERE item_id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Stock item deleted successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting stock: " . $conn->error . "</div>";
    }

    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <a href="../index.php" class="btn btn-outline-primary">
    <i class="bi bi-house-door-fill"></i> Home
    </a>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

</head>
<body>
<div class="container mt-5">
    <h2>All Stock Items</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Price per Unit</th>
                <th>Total Price</th>
                <th>Actions</th> <!-- Added Actions column for update and delete buttons -->
            </tr>
        </thead>
        <tbody>
            <?php
            // Query to fetch stock data
            $sql = "SELECT item_id, item_name, quantity, price FROM stock ORDER BY item_id ASC";
            $result = $conn->query($sql);

            // Check if there are any results
            if ($result->num_rows > 0) {
                // Loop through each result
                while ($row = $result->fetch_assoc()) {
                    $item_id = (int) $row['item_id'];
                    $item_name = htmlspecialchars($row['item_name']);
                    $quantity = (int) $row['quantity'];  // Ensure quantity is an integer
                    $price = (float) $row['price']; // Ensure price is a float
                    $total_price = $quantity * $price;

                    echo "<tr>
                            <td>{$item_name}</td>
                            <td>{$quantity}</td>
                            <td>{$price}</td>
                            <td>{$total_price}</td>
                            <td>
                                <a href='update_stock.php?id={$item_id}' class='btn btn-warning btn-sm'>Update</a>
                                <a href='display_stock.php?delete={$item_id}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this item?\");'>Delete</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No stock available</td></tr>";
            }

            // Close the database connection
            $conn->close();
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
