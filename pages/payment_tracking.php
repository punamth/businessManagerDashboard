
<?php include '../includes/db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Customer Payments</h1>
    <form action="customer_payment_process.php" method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <select name="customer_id" class="form-control" required>
                    <option value="">Select Customer</option>
                    <?php
                    $customers = mysqli_query($conn, "SELECT * FROM customers");
                    while ($row = mysqli_fetch_assoc($customers)) {
                        echo "<option value='{$row['customer_id']}'>{$row['customer_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="invoice_id" class="form-control" required>
                    <option value="">Select Invoice</option>
                    <?php
                    $invoices = mysqli_query($conn, "SELECT invoice_id, total_amount, due_amount FROM invoices WHERE due_amount > 0");
                    while ($row = mysqli_fetch_assoc($invoices)) {
                        echo "<option value='{$row['invoice_id']}'>Invoice #{$row['invoice_id']} - Due: {$row['due_amount']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" name="paid_amount" class="form-control" placeholder="Amount Paid" required>
            </div>
            <div class="col-md-3">
                <select name="payment_method" class="form-control" required>
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
            </div>
        </div>
        <button type="submit" name="add_payment" class="btn btn-primary mt-3">Add Payment</button>
    </form>
</div>
</body>
</html>

-- Customer Payment Processing Script (customer_payment_process.php)
<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment'])) {
    $customer_id = $_POST['customer_id'];
    $invoice_id = $_POST['invoice_id'];
    $paid_amount = $_POST['paid_amount'];
    $payment_method = $_POST['payment_method'];

    // Insert payment
    $stmt = $conn->prepare("INSERT INTO customer_payments (customer_id, paid_amount, payment_method, payment_date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ids", $customer_id, $paid_amount, $payment_method);
    $stmt->execute();
    
    // Update invoice paid amount
    $stmt = $conn->prepare("UPDATE invoices SET paid_amount = paid_amount + ? WHERE invoice_id = ?");
    $stmt->bind_param("di", $paid_amount, $invoice_id);
    $stmt->execute();
    
    header('Location: customer_payments.php');
    exit();
}
?>
