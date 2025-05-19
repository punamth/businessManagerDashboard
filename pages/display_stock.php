<?php 
include '../includes/db_connect.php';
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Delete Stock
if (isset($_GET['delete'])) {
    $item_id = $_GET['delete'];
    $sql_delete = "DELETE FROM stock WHERE item_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("ii", $item_id, $user_id);

    if ($stmt->execute()) {
        $success = "Stock item deleted successfully.";
    } else {
        $error = "Error deleting stock: " . $conn->error;
    }

    $stmt->close();
}

// Prepare search query
$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $sql = "SELECT item_id, item_name, quantity, price FROM stock WHERE user_id = ? AND item_name LIKE ? ORDER BY item_name ASC";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("is", $user_id, $searchTerm);
} else {
    $sql = "SELECT item_id, item_name, quantity, price FROM stock WHERE user_id = ? ORDER BY item_id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 30px; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .table-responsive { margin-top: 20px; }
        .btn-space { margin-right: 10px; }
        .page-header { margin-bottom: 25px; }
        .search-container { margin-bottom: 25px; }
        .low-stock { color: #dc3545; font-size: 14px; margin-left: 5px; }
    </style>
</head>
<body>
<div class="mb-3">
    <a href="../index.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
        <i class="bi bi-house-door-fill"></i> Home
    </a>
</div>

<div class="container">
    <div class="d-flex justify-content-between align-items-center page-header">
        <h2 class="text-primary">Stock Management</h2>
        <a href="add_stock.php" class="btn btn-success d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle"></i> Add New Item
        </a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="search-container">
        <form method="GET" class="mb-0">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search for items..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Price per Unit</th>
                    <th>Total Value</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                            $item_id = (int) $row['item_id'];
                            $item_name = htmlspecialchars($row['item_name']);
                            $quantity = (int) $row['quantity'];  
                            $price = (float) $row['price']; 
                            $total_price = $quantity * $price;
                        ?>
                        <tr>
                            <td><?= $item_name ?></td>
                            <td>
                                <?= $quantity ?>
                                <?php if ($quantity < 5): ?>
                                    <span class="low-stock">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Low Stock
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>Rs.<?= number_format($price, 2) ?></td>
                            <td>Rs.<?= number_format($total_price, 2) ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="update_stock.php?id=<?= $item_id ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger" 
                                       data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" 
                                       data-id="<?= $item_id ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No stock items found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this stock item?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete</a>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const confirmDeleteModal = document.getElementById('confirmDeleteModal');
    confirmDeleteModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const itemId = button.getAttribute('data-id');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.href = `display_stock.php?delete=${itemId}`;
    });
</script>

</body>
</html>
