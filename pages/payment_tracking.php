<?php
include '../includes/db_connect.php';
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch invoice data, including customer name
$result = $conn->query("SELECT * FROM invoices WHERE user_id = $user_id ORDER BY invoice_date DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Payment Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 30px; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .table-responsive { margin-top: 20px; }
        .btn-space { margin-right: 10px; }
        .page-header { margin-bottom: 25px; }
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
        <h2 class="text-primary">Customer Payment Tracking</h2>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>Customer</th>
                    <th>Total Amount</th>
                    <th>Amount Paid</th>
                    <th>Amount Due</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td>Rs.<?= number_format($row['total_amount'], 2) ?></td>
                            <td>Rs.<?= number_format($row['amount_paid'], 2) ?></td>
                            <td>Rs.<?= number_format($row['balance_due'], 2) ?></td>
                            <td>
                                <?php
                                    if ($row['amount_paid'] >= $row['total_amount']) {
                                        echo '<span class="badge bg-success">Paid</span>';
                                    } elseif ($row['amount_paid'] == 0) {
                                        echo '<span class="badge bg-danger">Unpaid</span>';
                                    } else {
                                        echo '<span class="badge bg-warning text-dark">Partially Paid</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="edit_invoice.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="delete_invoice.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                                        <input type="hidden" name="invoice_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No invoices found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
