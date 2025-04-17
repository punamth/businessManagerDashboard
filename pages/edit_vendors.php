<?php
include '../includes/db_connect.php';

// Check if vendor ID is provided
if (!isset($_GET['id'])) {
    die("Vendor ID not provided.");
}

$vendor_id = $_GET['id'];

// Fetch vendor details
$sql = "SELECT vendor_id, name, contact_info, address FROM vendors WHERE vendor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Vendor not found.");
}

$vendor = $result->fetch_assoc();
$stmt->close();

// Handle form submission to update vendor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_vendor'])) {
    $name = $_POST['name'];
    $contact_info = $_POST['contact_info'];
    $address = $_POST['address'];

    $sql_update = "UPDATE vendors SET name = ?, contact_info = ?, address = ? WHERE vendor_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssi", $name, $contact_info, $address, $vendor_id);

    if ($stmt_update->execute()) {
        header("Location: vendors.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error updating vendor: " . $conn->error . "</div>";
    }

    $stmt_update->close();
}

// Handle vendor deletion
if (isset($_POST['delete_vendor'])) {
    $sql_delete = "DELETE FROM vendors WHERE vendor_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $vendor_id);

    if ($stmt_delete->execute()) {
        header("Location: vendors.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error deleting vendor: " . $conn->error . "</div>";
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
    <title>Edit Vendor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Vendor</h2>

    <!-- Update Form -->
    <form action="edit_vendors.php?id=<?php echo $vendor['vendor_id']; ?>" method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Vendor Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($vendor['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="contact_info" class="form-label">Contact Info</label>
            <input type="text" class="form-control" id="contact_info" name="contact_info" value="<?php echo htmlspecialchars($vendor['contact_info']); ?>">
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($vendor['address']); ?>">
        </div>

        <button type="submit" name="update_vendor" class="btn btn-warning">Update Vendor</button>
    </form>

    <br>

    <!-- Delete Vendor Form -->
    <form action="edit_vendors.php?id=<?php echo $vendor['vendor_id']; ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this vendor?');">
        <button type="submit" name="delete_vendor" class="btn btn-danger">Delete Vendor</button>
    </form>

    <br>
    <a href="vendors.php" class="btn btn-secondary mt-3">Back to Vendor List</a>
</div>
</body>
</html>
