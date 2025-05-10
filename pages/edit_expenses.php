<?php
include '../includes/db_connect.php';
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if expense ID is provided
if (!isset($_GET['id'])) {
    die("Expense ID not provided.");
}

$expense_id = $_GET['id'];

// Fetch existing expense data (only if owned by this user)
$stmt = $conn->prepare("SELECT * FROM expenses WHERE expense_id = ? AND user_id = ?");
$stmt->bind_param("ii", $expense_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Expense not found or access denied.");
}

$expense = $result->fetch_assoc();
$stmt->close();

// Update Expense Logic
if (isset($_POST['update_expense'])) {
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("UPDATE expenses SET description = ?, amount = ?, date = ? WHERE expense_id = ? AND user_id = ?");
    $stmt->bind_param("sdsii", $description, $amount, $date, $expense_id, $user_id);

    if ($stmt->execute()) {
        header('Location: expenses.php');
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    
    }

    print_r($_GET); // remove after testing

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Expense</title>
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #007bff; }
    </style>
</head>
<body>

<!-- Home Button -->
<div class="mb-3">
    <a href="../index.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
        <i class="bi bi-house-door-fill"></i> Home
    </a>
</div>

<div class="container mt-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Expense</h1>
        <a href="expenses.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle"></i> Back to Expenses
        </a>
    </div>

    <form action="" method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($expense['description']) ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Amount</label>
                <input type="number" step="0.01" name="amount" class="form-control" value="<?= htmlspecialchars($expense['amount']) ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($expense['date']) ?>" required>
            </div>
        </div>
        <div class="text-end">
            <button type="submit" name="update_expense" class="btn btn-success">
                <i class="bi bi-save"></i> Update Expense
            </button>
            <a href="expenses.php" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
        </div>
    </form>
</div>

</body>
</html>
