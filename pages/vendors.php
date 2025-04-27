<?php include '../includes/db_connect.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Vendors</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <a href="../index.php" class="btn btn-outline-primary">
    <i class="bi bi-house-door-fill"></i> Home
    </a>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Manage Vendors</h1>

    <!-- Add Vendor Form -->
    <form action="vendors.php" method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="Vendor Name" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="contact_info" class="form-control" placeholder="Contact Info">
            </div>
            <div class="col-md-4">
                <input type="text" name="address" class="form-control" placeholder="Address">
            </div>
        </div>
        <button type="submit" name="add_vendor" class="btn btn-primary mt-3">Add Vendor</button>
    </form>

    <!-- Vendor List -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th> <!-- Consecutive numbering instead of database ID -->
                <th>Name</th>
                <th>Contact Info</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM vendors ORDER BY vendor_id ASC");
            $counter = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$counter}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['contact_info']}</td>
                        <td>{$row['address']}</td>
                        <td>
                            <a href='edit_vendors.php?id={$row['vendor_id']}' class='btn btn-sm btn-primary'>
                                <i class='bi bi-pencil-square'></i> Edit
                            </a>
                            <a href='vendors.php?delete={$row['vendor_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                        </td>
                    </tr>";
                $counter++;
            }
            ?>
        </tbody>
    </table>
</div>

<?php
// Add Vendor Logic
if (isset($_POST['add_vendor'])) {
    $name = $_POST['name'];
    $contact_info = $_POST['contact_info'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO vendors (name, contact_info, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $contact_info, $address);
    if ($stmt->execute()) {
        header('Location: vendors.php');
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}

// Delete Vendor Logic
if (isset($_GET['delete'])) {
    $vendor_id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM vendors WHERE vendor_id = ?");
    $stmt->bind_param("i", $vendor_id);
    if ($stmt->execute()) {
        header('Location: vendors.php');
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
?>

</body>
</html>
