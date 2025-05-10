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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_expense'])) {
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("UPDATE expenses SET description = ?, amount = ?, date = ? WHERE expense_id = ? AND user_id = ?");
    $stmt->bind_param("sdsii", $description, $amount, $date, $expense_id, $user_id);

    if ($stmt->execute()) {
        header('Location: expenses.php');
        exit();
    } else {
        $error = "Error updating expense: " . $conn->error;
    }

    $stmt->close();
}

// Handle expense deletion
if (isset($_POST['delete_expense'])) {
    $delete_sql = "DELETE FROM expenses WHERE expense_id = ? AND user_id = ?";
    $stmt_delete = $conn->prepare($delete_sql);
    $stmt_delete->bind_param("ii", $expense_id, $user_id);

    if ($stmt_delete->execute()) {
        header("Location: expenses.php");
        exit();
    } else {
        $error = "Error deleting expense: " . $conn->error;
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
    <title>Edit Expense</title>
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
    <h2 class="text-center mb-4 text-primary">Edit Expense</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($expense['description']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" step="0.01" class="form-control" name="amount" value="<?= htmlspecialchars($expense['amount']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($expense['date']) ?>" required>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <button type="submit" name="update_expense" class="btn btn-success btn-space">
                    <i class="bi bi-check-circle"></i> Update Expense
                </button>
                <a href="expenses.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                <button type="submit" name="delete_expense" class="btn btn-danger">
                    <i class="bi bi-trash-fill"></i> Delete
                </button>
            </form>
        </div>
    </form>
</div>

</body>
</html>