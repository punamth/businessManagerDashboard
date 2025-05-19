<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$item_name = trim($_POST['item_name']);
$quantity = $_POST['quantity'];
$price = $_POST['price'];

// Server-side validation
if (!filter_var($quantity, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
    die("Invalid quantity. Must be a positive integer.");
}

if (!filter_var($price, FILTER_VALIDATE_FLOAT) || $price <= 0) {
    die("Invalid price. Must be a positive number.");
}

// Check if item already exists for this user (optional: adjust condition if stock is global)
$check_stmt = $conn->prepare("SELECT item_id FROM stock WHERE item_name = ? AND user_id = ?");
$check_stmt->bind_param("si", $item_name, $user_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    // Item already exists
    header("Location: add_stock.php?status=error&msg=" . urlencode("Item '$item_name' already exists!"));
    exit();
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Insert new stock item
$insert_stmt = $conn->prepare("INSERT INTO stock (item_name, quantity, price, user_id) VALUES (?, ?, ?, ?)");
$insert_stmt->bind_param("sidi", $item_name, $quantity, $price, $user_id);

if ($insert_stmt->execute()) {
    header("Location: add_stock.php?status=success&msg=" . urlencode("Stock added successfully!"));
exit();
} else {
    echo "Error: " . $insert_stmt->error;
}

$insert_stmt->close();
$conn->close();
?>
