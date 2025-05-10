<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Add Vendor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vendor'])) {
    $name = $_POST['name'];
    $contact_info = $_POST['contact_info'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO vendors (name, contact_info, address, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $contact_info, $address, $user_id);
    if ($stmt->execute()) {
        header("Location: vendors.php");
        exit();
    } else {
        $error = "Error adding vendor: " . $conn->error;
    }
    $stmt->close();
}

// Handle Delete Vendor
if (isset($_GET['delete'])) {
    $vendor_id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM vendors WHERE vendor_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $vendor_id, $user_id);
    if ($stmt->execute()) {
        header("Location: vendors.php");
        exit();
    } else {
        $error = "Error deleting vendor: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendors</title>
    <!-- Bootstrap CSS & Icons -->
    <!-- Bootstrap CSS & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Home Button -->
    <a href="../index.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
        <i class="bi bi-house-door-fill"></i>
        Home
    </a>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #007bff; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Vendor Details</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Add Vendor Form -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="name" class="form-control" placeholder="Vendor Name" required>
        </div>
        <div class="col-md-4">
            <input type="text" name="contact_info" class="form-control" placeholder="Contact Info" required>
        </div>
        <div class="col-md-4">
            <input type="text" name="address" class="form-control" placeholder="Address" required>
        </div>
        <div class="col-12 text-end">
            <button type="submit" name="add_vendor" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Vendor
            </button>
        </div>
    </form>

    <!-- Vendor Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Contact Info</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->prepare("SELECT * FROM vendors WHERE user_id = ? ORDER BY vendor_id ASC");
            $result->bind_param("i", $user_id);
            $result->execute();
            $vendors = $result->get_result();
            $counter = 1;

            while ($row = $vendors->fetch_assoc()) {
                echo "<tr>
                        <td>{$counter}</td>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['contact_info']) . "</td>
                        <td>" . htmlspecialchars($row['address']) . "</td>
                        <td>
                            <a href='edit_vendors.php?id={$row['vendor_id']}' class='btn btn-sm btn-primary'>
                                <i class='bi bi-pencil-square'></i> Edit
                            </a>
                            <a href='vendors.php?delete={$row['vendor_id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this vendor?\")'>
                                <i class='bi bi-trash'></i> Delete
                            </a>
                        </td>
                    </tr>";
                $counter++;
            }

            $result->close();
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
