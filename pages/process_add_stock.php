<?php
include '../includes/db_connect.php';

$item_name = $_POST['item_name'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];

$sql = "INSERT INTO stock (item_name, quantity, price) VALUES ('$item_name', $quantity, $price)";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Stock added successfully!'); window.location.href='add_stock.php';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}

$conn->close();
?>
