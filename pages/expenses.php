<?php
session_start();
include '../includes/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Add Expense
if (isset($_POST['add_expense'])) {
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO expenses (description, amount, date, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdsi", $description, $amount, $date, $user_id);

    if ($stmt->execute()) {
        header('Location: expenses.php');
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// Delete Expense
if (isset($_GET['delete'])) {
    $expense_id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM expenses WHERE expense_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $expense_id, $user_id);

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
    <title>Expenses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #007bff; }
    </style>
</head>
<body>

<div class="mb-3">
    <a href="../index.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
        <i class="bi bi-house-door-fill"></i> Home
    </a>
</div>

<div class="container mt-3">
    <h2 class="text-center mb-4">Expenses Tracking</h2>

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
        <div class="text-end mt-3">
            <button type="submit" name="add_expense" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Expense
            </button>
        </div>
    </form>

    <!-- Expense Table -->
    <table class="table table-striped table-bordered">
        <thead class="table-primary">
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
            $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY date DESC");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $counter = 1;
            while ($row = $result->fetch_assoc()) {
                $eid = $row['expense_id']; // Get ID for debugging
                echo "<!-- Debug: ID=$eid -->"; // DEBUG

                echo "<tr>
                        <td>{$counter}</td>
                        <td>" . htmlspecialchars($row['description']) . "</td>
                        <td>Rs. " . number_format($row['amount'], 2) . "</td>
                        <td>{$row['date']}</td>
                        <td>
                            <a href='edit_expenses.php?id={$eid}' class='btn btn-sm btn-primary'>
                                <i class='bi bi-pencil-square'></i> Edit
                            </a>
                            <a href='expenses.php?delete={$eid}' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this expense?');\">
                                <i class='bi bi-trash'></i> Delete
                            </a>
                        </td>
                    </tr>";
                $counter++;
            }
            $stmt->close();
        ?>
        </tbody>
    </table>
</div>

</body>
</html>
