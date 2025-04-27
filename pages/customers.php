<?php
ob_start(); // Start output buffering
include '../includes/db_connect.php'; // Database connection

?>

<!DOCTYPE html>
<html>
<head>
    <title>Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <a href="../index.php" class="btn btn-outline-primary">
    <i class="bi bi-house-door-fill"></i> Home
    </a>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Manage Customers</h1>

    <!-- Add Customer Form -->
    <form action="customers.php" method="POST" class="mb-4">
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
        <button type="submit" name="add_customer" class="btn btn-primary mt-3">Add Customer</button>
    </form>

    <!-- Customer List -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <?php
    // Fetch Customers
    $result = mysqli_query($conn, "SELECT * FROM customers");
    $counter = 1; // Start from 1
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$counter}</td> <!-- Displaying consecutive numbers -->
                <td>{$row['customer_name']}</td>
                <td>{$row['email']}</td>
                <td>{$row['phone']}</td>
                <td>{$row['address']}</td>
                <td>
                    <a href='edit_customers.php?id={$row['customer_id']}' class='btn btn-sm btn-primary'>
                                <i class='bi bi-pencil-square'></i> Edit
                            </a>
                    <a href='customers.php?delete={$row['customer_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this customer?\")'>Delete</a>
                </td>
            </tr>";
        $counter++; // Increment row number
    }
    ?>
</tbody>
    </table>
</div>

<?php
// Add Customer Logic
if (isset($_POST['add_customer'])) {
    $customer_name = $_POST['customer_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO customers (customer_name, email, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $customer_name, $email, $phone, $address);

    if ($stmt->execute()) {
        $stmt->close();
        header('Location: customers.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Handle Delete Customer Logic
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $stmt->close();
        header('Location: customers.php');
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error deleting customer: " . $conn->error . "</div>";
    }
}
ob_end_flush(); // End output buffering
?>
</body>
</html>
