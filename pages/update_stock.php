<?php
include '../includes/db_connect.php';
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if an item ID is provided
if (!isset($_GET['id'])) {
    die("Item ID not provided.");
}

$item_id = $_GET['id'];

// Fetch the stock item from the database (only if owned by this user)
$sql = "SELECT item_id, item_name, quantity, price FROM stock WHERE item_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the item exists
if ($result->num_rows == 0) {
    die("Stock item not found or you don't have permission to access this item.");
}

$item = $result->fetch_assoc();
$stmt->close();

// Handle the form submission to update the stock
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];

    // Update the stock in the database
    $sql_update = "UPDATE stock SET item_name = ?, quantity = ?, price = ? WHERE item_id = ? AND user_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sdiii", $item_name, $quantity, $price, $item_id, $user_id);

    if ($stmt_update->execute()) {
        header("Location: display_stock.php");
        exit;
    } else {
        $error = "Error updating stock: " . $conn->error;
    }

    $stmt_update->close();
}

// Handle delete request
if (isset($_POST['delete_stock'])) {
    // Delete the stock item from the database
    $sql_delete = "DELETE FROM stock WHERE item_id = ? AND user_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $item_id, $user_id);

    if ($stmt_delete->execute()) {
        header("Location: display_stock.php");
        exit;
    } else {
        $error = "Error deleting stock: " . $conn->error;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 30px; }
        .container { max-width: 600px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .btn-space { margin-right: 10px; }
    </style>
</head>
<body>

<div class="mb-3">
    <a href="../index.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
        <i class="bi bi-house-door-fill"></i> Home
    </a>
</div>

<div class="container">
    <h2 class="text-center mb-4 text-primary">Update Stock Item</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="item_name" class="form-label">Item Name</label>
            <input type="text" class="form-control" id="item_name" name="item_name" value="<?= htmlspecialchars($item['item_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="<?= $item['quantity'] ?>" required>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price per Unit</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= $item['price'] ?>" required>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <button type="submit" name="update_stock" class="btn btn-success btn-space">
                    <i class="bi bi-check-circle"></i> Update Item
                </button>
                <a href="display_stock.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this stock item?');">
                <button type="submit" name="delete_stock" class="btn btn-danger">
                    <i class="bi bi-trash-fill"></i> Delete
                </button>
            </form>
        </div>
    </form>
</div>

</body>
</html>