<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$item_name = $_POST['item_name'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];

// Server-side validation
if (!filter_var($quantity, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
    die("Invalid quantity. Must be a positive integer.");
}

if (!filter_var($price, FILTER_VALIDATE_FLOAT) || $price <= 0) {
    die("Invalid price. Must be a positive number.");
}

// Use prepared statement to insert safely
$stmt = $conn->prepare("INSERT INTO stock (item_name, quantity, price, user_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sidi", $item_name, $quantity, $price, $user_id);

if ($stmt->execute()) {
    echo "<script>alert('Stock added successfully!'); window.location.href='add_stock.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
