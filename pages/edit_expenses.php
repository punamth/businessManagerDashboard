<?php
include '../includes/db_connect.php';

// Check if expense ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Expense ID");
}

$expense_id = $_GET['id'];

// Fetch existing expense data
$stmt = $conn->prepare("SELECT * FROM expenses WHERE expense_id = ?");
$stmt->bind_param("i", $expense_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Expense not found");
}

$expense = $result->fetch_assoc();
$stmt->close();

// Update Expense Logic
if (isset($_POST['update_expense'])) {
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("UPDATE expenses SET description = ?, amount = ?, date = ? WHERE expense_id = ?");
    $stmt->bind_param("sdsi", $description, $amount, $date, $expense_id);
    
    if ($stmt->execute()) {
        header('Location: expenses.php');
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Expense</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Edit Expense</h1>
    <form action="" method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($expense['description']); ?>" required>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo htmlspecialchars($expense['amount']); ?>" required>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($expense['date']); ?>" required>
            </div>
        </div>
        <button type="submit" name="update_expense" class="btn btn-success mt-3">Update Expense</button>
        <a href="expenses.php" class="btn btn-secondary mt-3">Cancel</a>
    </form>
</div>
</body>
</html>
