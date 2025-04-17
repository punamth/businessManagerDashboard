<?php
include '../includes/db_connect.php';

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if an item ID is provided
if (!isset($_GET['id'])) {
    die("Item ID not provided.");
}

$item_id = $_GET['id'];

// Fetch the stock item from the database
$sql = "SELECT item_id, item_name, quantity, price FROM stock WHERE item_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the item exists
if ($result->num_rows == 0) {
    die("Stock item not found.");
}

$item = $result->fetch_assoc();
$stmt->close();

// Handle the form submission to update the stock
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];

    // Update the stock in the database
    $sql_update = "UPDATE stock SET item_name = ?, quantity = ?, price = ? WHERE item_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sdii", $item_name, $quantity, $price, $item_id);

    if ($stmt_update->execute()) {
        echo "<div class='alert alert-success'>Stock updated successfully.</div>";
        // Redirect back to display_stock.php
        header("Location: display_stock.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error updating stock: " . $conn->error . "</div>";
    }

    $stmt_update->close();
}

// Handle delete request
if (isset($_POST['delete_stock'])) {
    // Delete the stock item from the database
    $sql_delete = "DELETE FROM stock WHERE item_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $item_id);

    if ($stmt_delete->execute()) {
        echo "<div class='alert alert-success'>Stock item deleted successfully.</div>";
        // Redirect back to display_stock.php
        header("Location: display_stock.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error deleting stock: " . $conn->error . "</div>";
    }

    $stmt_delete->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Update Stock Item</h2>

    <!-- Update Form -->
    <form action="update_stock.php?id=<?php echo $item['item_id']; ?>" method="POST">
        <div class="mb-3">
            <label for="item_name" class="form-label">Item Name</label>
            <input type="text" class="form-control" id="item_name" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo $item['quantity']; ?>" required>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price per Unit</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $item['price']; ?>" required>
        </div>

        <button type="submit" name="update_stock" class="btn btn-warning">Update Stock</button>
    </form>

    <br>

    <!-- Delete Stock Form -->
    <form action="update_stock.php?id=<?php echo $item['item_id']; ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this stock item?');">
        <button type="submit" name="delete_stock" class="btn btn-danger">Delete Stock</button>
    </form>

    <br>
    <a href="display_stock.php" class="btn btn-secondary mt-3">Back to Stock List</a>
</div>
</body>
</html>
