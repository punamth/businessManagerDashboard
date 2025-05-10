<?php
ob_start();
include '../includes/db_connect.php';
session_start();

$user_id = $_SESSION['user_id'];

// Add Customer Logic
if (isset($_POST['add_customer'])) {
    $customer_name = $_POST['customer_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Check for duplicate email for the same user
    $check_stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ? AND user_id = ?");
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $error = "Email already exists. Please use a different one.";
    } else {
        // Insert new customer
        $stmt = $conn->prepare("INSERT INTO customers (customer_name, email, phone, address, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $customer_name, $email, $phone, $address, $user_id);
        if ($stmt->execute()) {
            header("Location: customers.php");
            exit();
        } else {
            $error = "Error adding customer: " . $conn->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Handle Delete Customer Logic
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Ensure the customer belongs to the logged-in user
    $delete_stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $delete_id, $user_id);
    if ($delete_stmt->execute()) {
        header("Location: customers.php");
        exit();
    } else {
        $error = "Error deleting customer: " . $conn->error;
    }
    $delete_stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Customers</title>
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #007bff; }
    </style>
</head>
<body>

<!-- Home Button -->
<div class="mb-3">
    <a href="../index.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
        <i class="bi bi-house-door-fill"></i> Home
    </a>
</div>

<div class="container">
    <h2 class="text-center mb-4">Customer Details</h2>

    <!-- Display error if any -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Add Customer Form -->
    <form action="customers.php" method="POST" class="row g-3 mb-4">
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="customer_name" class="form-control" placeholder="Customer Name" required>
            </div>
            <div class="col-md-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="phone" class="form-control" placeholder="Phone" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="address" class="form-control" placeholder="Address" required>
            </div>
        </div>
        <div class="col-12 text-end">
            <button type="submit" name="add_customer" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Customer
            </button>
        </div>
    </form>

    <!-- Customer List -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
            // Fetch customers for the current user
            $fetch_stmt = $conn->prepare("SELECT * FROM customers WHERE user_id = ?");
            $fetch_stmt->bind_param("i", $user_id);
            $fetch_stmt->execute();
            $customers = $fetch_stmt->get_result();
            $sn = 1;

            while ($row = $customers->fetch_assoc()):
        ?>
            <tr>
                <td><?php echo $sn++; ?></td>
                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['address']); ?></td>
                <td>
                    <a href="edit_customers.php?id=<?php echo $row['customer_id']; ?>" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <a href="customers.php?delete=<?php echo $row['customer_id']; ?>" onclick="return confirm('Are you sure you want to delete this customer?');" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i> Delete
                    </a>
                </td>
            </tr>
        <?php endwhile; $fetch_stmt->close(); ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php ob_end_flush(); ?>
