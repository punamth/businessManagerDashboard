<?php
include '../includes/db_connect.php';

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if customer ID is provided
if (!isset($_GET['id'])) {
    die("Customer ID not provided.");
}

$customer_id = $_GET['id'];

// Fetch customer details
$sql = "SELECT customer_id, customer_name, email, phone, address FROM customers WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Customer not found.");
}

$customer = $result->fetch_assoc();
$stmt->close();

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_customer'])) {
    $customer_name = $_POST['customer_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql_update = "UPDATE customers SET customer_name = ?, email = ?, phone = ?, address = ? WHERE customer_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssi", $customer_name, $email, $phone, $address, $customer_id);

    if ($stmt_update->execute()) {
        header("Location: customers.php");
        exit; // Ensure redirection happens
    } else {
        echo "<div class='alert alert-danger'>Error updating customer: " . $stmt_update->error . "</div>";
    }

    $stmt_update->close();
}


// Handle Delete
if (isset($_POST['delete_customer'])) {
    $sql_delete = "DELETE FROM customers WHERE customer_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $customer_id);

    if ($stmt_delete->execute()) {
        header("Location: customers.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error deleting customer: " . $conn->error . "</div>";
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
    <title>Edit Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <a href="../index.php" class="btn btn-outline-primary">
    <i class="bi bi-house-door-fill"></i> Home
    </a>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

</head>
<body>
<div class="container mt-5">
    <h2>Edit Customer</h2>

    <!-- Update Form -->
    <form method="POST">
        <div class="mb-3">
            <label for="customer_name" class="form-label">Customer Name</label>
            <input type="text" class="form-control" name="customer_name" value="<?php echo htmlspecialchars($customer['customer_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($customer['address']); ?>" required>
        </div>
        <button type="submit" name="update_customer" class="btn btn-warning">Update Customer</button>
    </form>

    <!-- Delete Form -->
    <form action="edit_customers.php?id=<?php echo $customer['customer_id'];?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this customer?');">
        <button type="submit" name="delete_customer" class="btn btn-danger mt-3">Delete Customer</button>
    </form>

    <br>
    <a href="customers.php" class="btn btn-secondary">Back to Customer List</a>
</div>
</body>
</html>