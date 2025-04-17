<?php include '../includes/db_connect.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Expenses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Manage Expenses</h1>

    <!-- Add Expense Form -->
    <form action="expenses.php" method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <input type="text" name="description" class="form-control" placeholder="Expense Description" required>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" required>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" required>
            </div>
        </div>
        <button type="submit" name="add_expense" class="btn btn-primary mt-3">Add Expense</button>
    </form>

    <!-- Expense List -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM expenses ORDER BY date DESC");
            $counter = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$counter}</td>
                        <td>{$row['description']}</td>
                        <td>{$row['amount']}</td>
                        <td>{$row['date']}</td>
                        <td>
                            <a href='edit_expenses.php?id={$row['expense_id']}' class='btn btn-warning btn-sm'>Edit</a>
                            <a href='expenses.php?delete={$row['expense_id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure?');\">Delete</a>
                        </td>
                    </tr>";
                $counter++;
            }
            ?>
        </tbody>
    </table>
</div>

<?php
// Add Expense Logic
if (isset($_POST['add_expense'])) {
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO expenses (description, amount, date) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $description, $amount, $date);
    
    if ($stmt->execute()) {
        header('Location: expenses.php');
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// Delete Expense Logic
if (isset($_GET['delete'])) {
    $expense_id = $_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM expenses WHERE expense_id = ?");
    $stmt->bind_param("i", $expense_id);
    
    if ($stmt->execute()) {
        header('Location: expenses.php');
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
    $stmt->close();
}
?>
</body>
</html>
