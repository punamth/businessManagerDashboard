<?php 
    include 'includes/db_connect.php';
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Manager Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            margin-top: 50px;
        }
        .dashboard-section img {
            max-width: 200px;
            margin-bottom: 5px;
        }
        .dashboard-section {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: transform 0.2s;
        }
        .dashboard-section:hover {
            transform: scale(1.05);
        }
        h1 {
            color: rgb(43, 154, 223);
        }
        a {
            color: rgb(43, 154, 223);
            text-decoration: none;
        }

        /* 🔴 Enhanced Logout Button */
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 15px;
            background-color: #dc3545; /* Bootstrap Danger Color */
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #c82333; /* Darker Red on Hover */
            text-decoration: none;
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<!-- 🔴 Logout Button -->
<a href="logout.php" class="logout-btn">Logout</a>

<div class="container dashboard-container">
    <h1 class="text-center mb-4">Business Manager Dashboard</h1>

    <div class="row text-center gy-4">
        <!-- Add Stock -->
        <div class="col-md-3">
            <div class="dashboard-section">
                <a href="pages/add_stock.php">
                    <img src="images/adding_stock.png" alt="Add Stock" class="img-fluid">
                    <h5>Add Stock</h5>
                </a>
            </div>
        </div>

        <!-- Display Stock -->
        <div class="col-md-3">
            <div class="dashboard-section">
                <a href="pages/display_stock.php">
                    <img src="images/update_stock.png" alt="Display Stock" class="img-fluid">
                    <h5>Display Stock</h5>
                </a>
            </div>
        </div>

        <!-- Create Bill -->
        <div class="col-md-3">
            <div class="dashboard-section">
                <a href="pages/create_bill.php">
                    <img src="images/invoice_generation.png" alt="Create Bill" class="img-fluid">
                    <h5>Invoice Generation</h5>
                </a>
            </div>
        </div>

        <!-- View Reports -->
        <div class="col-md-3">
            <div class="dashboard-section">
                <a href="pages/sales_reports.php">
                    <img src="images/sales_reports.png" alt="View Reports" class="img-fluid">
                    <h5>Sales Reports and Analytics</h5>
                </a>
            </div>
        </div>
    </div>

    <div class="row text-center gy-4 mt-4">
        <!-- Vendor Management -->
        <div class="col-md-3">
            <div class="dashboard-section">
                <a href="pages/vendors.php">
                    <img src="images/vendor_management.png" alt="Vendor Management" class="img-fluid">
                    <h5>Vendor Management</h5>
                </a>
            </div>
        </div>

        <!-- Expense Tracking -->
        <div class="col-md-3">
            <div class="dashboard-section">
                <a href="pages/expenses.php">
                    <img src="images/expense_tracking.png" alt="Expense Tracking" class="img-fluid">
                    <h5>Expense Tracking</h5>
                </a>
            </div>
        </div>

        <!-- Customer Management -->
        <div class="col-md-3">
            <div class="dashboard-section">
                <a href="pages/customers.php">
                    <img src="images/customer_management.png" alt="Customer Management" class="img-fluid">
                    <h5>Customer Management</h5>
                </a>
            </div>
        </div>

        <!-- Payment Tracking -->
        <div class="col-md-3">
            <div class="dashboard-section">
                <a href="pages/payment_tracking.php">
                    <img src="images/payment_tracking.png" alt="Payment Tracking" class="img-fluid">
                    <h5>Payment Tracking</h5>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
