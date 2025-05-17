<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Update Vendor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_vendor'])) {
    $vendor_id = $_POST['vendor_id'];
    $name = $_POST['name'];
    $contact_info = $_POST['contact_info'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("UPDATE vendors SET name = ?, contact_info = ?, address = ? WHERE vendor_id = ? AND user_id = ?");
    $stmt->bind_param("sssii", $name, $contact_info, $address, $vendor_id, $user_id);
    if ($stmt->execute()) {
        // Redirect to the same page to fetch updated data
        header("Location: vendors.php?id=" . $vendor_id);
        exit();
    } else {
        $error = "Error updating vendor: " . $conn->error;
    }
    $stmt->close();
}

// Fetch Vendor Data
if (isset($_GET['id'])) {
    $vendor_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM vendors WHERE vendor_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $vendor_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vendor = $result->fetch_assoc();
    $stmt->close();

    if (!$vendor) {
        echo "Vendor not found.";
        exit;
    }
} else {
    echo "Invalid vendor ID.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Vendor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
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
    <h2 class="text-center mb-4 text-primary">Edit Vendor</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="edit_vendors.php?id=<?= $vendor['vendor_id'] ?>">
        <input type="hidden" name="vendor_id" value="<?= $vendor['vendor_id'] ?>">

        <div class="mb-3">
            <label for="name" class="form-label">Vendor Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($vendor['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="contact_info" class="form-label">Contact Info</label>
            <input type="text" class="form-control" id="contact_info" name="contact_info" value="<?= htmlspecialchars($vendor['contact_info']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($vendor['address']) ?>" required>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <button type="submit" name="update_vendor" class="btn btn-success btn-space">
                    <i class="bi bi-check-circle"></i> Update Vendor
                </button>
                <a href="vendors.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this vendor?');">
                <input type="hidden" name="vendor_id" value="<?= $vendor['vendor_id'] ?>">
                <button type="submit" name="delete_vendor" class="btn btn-danger">
                    <i class="bi bi-trash-fill"></i> Delete
                </button>
            </form>
        </div>
    </form>
</div>

</body>
</html>
