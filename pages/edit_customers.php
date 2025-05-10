<?php
include '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    die("Customer ID not provided.");
}

$customer_id = $_GET['id'];

// Fetch customer details for the logged-in user
$sql = "SELECT customer_id, customer_name, email, phone, address FROM customers WHERE customer_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $customer_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Customer not found or you don't have permission to access this customer.");
}

$customer = $result->fetch_assoc();
$stmt->close();

// Handle customer update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_customer'])) {
    $customer_name = $_POST['customer_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $update_sql = "UPDATE customers SET customer_name = ?, email = ?, phone = ?, address = ? WHERE customer_id = ? AND user_id = ?";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("ssssii", $customer_name, $email, $phone, $address, $customer_id, $user_id);

    if ($stmt_update->execute()) {
        header("Location: customers.php");
        exit();
    } else {
        $error = "Error updating customer: " . $conn->error;
    }

    $stmt_update->close();
}

// Handle customer deletion
if (isset($_POST['delete_customer'])) {
    $delete_sql = "DELETE FROM customers WHERE customer_id = ? AND user_id = ?";
    $stmt_delete = $conn->prepare($delete_sql);
    $stmt_delete->bind_param("ii", $customer_id, $user_id);

    if ($stmt_delete->execute()) {
        header("Location: customers.php");
        exit();
    } else {
        $error = "Error deleting customer: " . $conn->error;
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
    <h2 class="text-center mb-4 text-primary">Edit Customer</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="customer_name" class="form-label">Customer Name</label>
            <input type="text" class="form-control" name="customer_name" value="<?= htmlspecialchars($customer['customer_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($customer['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($customer['address']) ?>" required>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <button type="submit" name="update_customer" class="btn btn-success btn-space">
                    <i class="bi bi-check-circle"></i> Update Customer
                </button>
                <a href="customers.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                <button type="submit" name="delete_customer" class="btn btn-danger">
                    <i class="bi bi-trash-fill"></i> Delete
                </button>
            </form>
        </div>
    </form>
</div>

</body>
</html>
