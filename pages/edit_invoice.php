<?php
session_start();
include '../includes/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch invoice by ID and ensure it belongs to the logged-in user
if (isset($_GET['id'])) {
    $invoice_id = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $invoice_id, $user_id);
    $stmt->execute();
    $invoice = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // If invoice not found or doesn't belong to user
    if (!$invoice) {
        die("Unauthorized access or invoice not found.");
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invoice_id = $_POST['invoice_id'];
    $amount_paid = $_POST['amount_paid'];

    // Ensure invoice belongs to the user before update
    $stmt = $conn->prepare("SELECT total_amount FROM invoices WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $invoice_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        die("Unauthorized update attempt.");
    }

    $total_amount = $row['total_amount'];
    $balance_due = $total_amount - $amount_paid;

    // Update invoice
    $stmt = $conn->prepare("UPDATE invoices SET amount_paid = ?, balance_due = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ddii", $amount_paid, $balance_due, $invoice_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Redirect
    header("Location: payment_tracking.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Invoice Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h3>Edit Payment for Invoice #<?php echo $invoice['id']; ?></h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">

                <div class="mb-3">
                    <label class="form-label">Customer Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($invoice['customer_name']); ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Total Amount</label>
                    <input type="number" class="form-control" value="<?php echo number_format($invoice['total_amount'], 2); ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Amount Paid</label>
                    <input type="number" name="amount_paid" class="form-control" step="0.01" value="<?php echo $invoice['amount_paid']; ?>" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="payment_tracking.php" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Update Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
