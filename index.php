<?php 
    include 'includes/db_connect.php';
    session_start();

    function verify_session() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
            header("Location: login.php");
            exit();
        }

        $timeout = 1800;
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $timeout)) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['timeout_message'] = "Your session has expired. Please log in again.";
            header("Location: login.php");
            exit();
        }

        $_SESSION['login_time'] = time();
        return $_SESSION['user_id'];
    }

    $user_id = verify_session();

    // Fetch user name
    $stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $username = htmlspecialchars($user['username']);

    // Fetch customer count
    $customer_count = $conn->query("SELECT COUNT(*) as count FROM customers WHERE user_id = $user_id")->fetch_assoc()['count'];

    // Fetch vendor count
    $vendor_count = $conn->query("SELECT COUNT(*) as count FROM vendors WHERE user_id = $user_id")->fetch_assoc()['count'];

    // Due payments
    $due_payments = $conn->query("SELECT SUM(balance_due) as total FROM invoices WHERE user_id = $user_id")->fetch_assoc()['total'] ?? 0;

    // Low stock
    $low_stock = $conn->query("SELECT COUNT(*) as count FROM stock WHERE quantity < 5 AND user_id = $user_id")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business Manager Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5faff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }

        .sidebar {
            width: 250px;
            background-color: rgb(101, 178, 226);
            min-height: 100vh;
            color: white;
        }

        .sidebar .nav-link {
            color: white;
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .top-bar {
            background-color: #ffffff;
            padding: 15px 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar h3 {
            color:  #007bff;
        }

        .dashboard-section h5 {
            color: #007bff;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar p-3">
            <h4 class="text-center mb-4">📊 Manager</h4>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="index.php" class="nav-link"><i class="bi bi-house-door me-2"></i>Dashboard</a></li>
                <li class="nav-item mb-2"><a href="pages/display_stock.php" class="nav-link"><i class="bi bi-box-seam me-2"></i>Stock Management</a></li>
                <li class="nav-item mb-2"><a href="pages/customers.php" class="nav-link"><i class="bi bi-people me-2"></i>Customers</a></li>
                <li class="nav-item mb-2"><a href="pages/vendors.php" class="nav-link"><i class="bi bi-truck me-2"></i>Vendors</a></li>
                <li class="nav-item mb-2"><a href="pages/expenses.php" class="nav-link"><i class="bi bi-wallet2 me-2"></i>Expenses Tracking</a></li>
                <li class="nav-item mb-2"><a href="pages/create_bill.php" class="nav-link"><i class="bi bi-receipt me-2"></i>Invoice Generation</a></li>
                <li class="nav-item mb-2"><a href="pages/payment_tracking.php" class="nav-link"><i class="bi bi-cash-stack me-2"></i>Payment Tracking</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <!-- Top Bar -->
            <div class="top-bar mb-4">
                <h3 class="fw-bold">Dashboard</h3>
                <div>
                    <span class="me-3 fw-semibold">👋 Welcome, <?php echo $username; ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                </div>
            </div>

            <!-- Quick Insight Cards -->
            <div class="row text-white mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary h-100 shadow-sm text-center">
                        <div class="card-body">
                            <i class="bi bi-people fs-1 mb-2"></i>
                            <h6>Total Customers</h6>
                            <h3><?php echo $customer_count; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-info h-100 shadow-sm text-center">
                        <div class="card-body">
                            <i class="bi bi-truck fs-1 mb-2"></i>
                            <h6>Total Vendors</h6>
                            <h3><?php echo $vendor_count; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning h-100 shadow-sm text-center text-dark">
                        <div class="card-body">
                            <i class="bi bi-exclamation-circle fs-1 mb-2"></i>
                            <h6>Due Payments</h6>
                            <h3>₹<?php echo number_format($due_payments, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-danger h-100 shadow-sm text-center">
                        <div class="card-body">
                            <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                            <h6>Low Stock Items</h6>
                            <h3><?php echo $low_stock; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add your sales chart or other content here -->
            <?php
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../login.php');
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $sales_raw = [];

        // Fetch paid invoices for the logged-in user
        $query = "SELECT DATE(invoice_date) as sale_date, amount_paid 
                FROM invoices 
                WHERE amount_paid > 0 AND user_id = $user_id";
        $result = $conn->query($query);

        // Prepare sales data by date
        while ($row = $result->fetch_assoc()) {
            $date = $row['sale_date'];
            $amount = floatval($row['amount_paid']);
            $sales_raw[$date] = ($sales_raw[$date] ?? 0) + $amount;
        }

        // Daily sales
        $daily = $sales_raw;

        // Weekly sales
        $weekly = [];
        foreach ($sales_raw as $date => $amount) {
            $week = date('o-\WW', strtotime($date)); // e.g., 2024-W15
            $weekly[$week] = ($weekly[$week] ?? 0) + $amount;
        }

        // Monthly sales
        $monthly = [];
        foreach ($sales_raw as $date => $amount) {
            $month = date('Y-m', strtotime($date)); // e.g., 2024-04
            $monthly[$month] = ($monthly[$month] ?? 0) + $amount;
        }

        // Encode for Chart.js
        function encodeChartData($array) {
            return [
                json_encode(array_keys($array)),
                json_encode(array_values($array))
            ];
        }

        [$daily_labels, $daily_data] = encodeChartData($daily);
        [$weekly_labels, $weekly_data] = encodeChartData($weekly);
        [$monthly_labels, $monthly_data] = encodeChartData($monthly);
        ?>

        <div class="row justify-content-left">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary">📈 Sales Report</h5>

                        <div class="mb-3">
                            <label for="view-select" class="form-label fw-semibold">View By:</label>
                            <select id="view-select" class="form-select w-auto d-inline-block" onchange="updateChart()">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <canvas id="salesChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = {
            daily: {
                labels: <?php echo $daily_labels; ?>,
                data: <?php echo $daily_data; ?>
            },
            weekly: {
                labels: <?php echo $weekly_labels; ?>,
                data: <?php echo $weekly_data; ?>
            },
            monthly: {
                labels: <?php echo $monthly_labels; ?>,
                data: <?php echo $monthly_data; ?>
            }
        };

        const ctx = document.getElementById('salesChart').getContext('2d');
        let salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.daily.labels,
                datasets: [{
                    label: 'Total Sales (Rs)',
                    data: chartData.daily.data,
                    backgroundColor: 'rgba(0, 123, 255, 0.9)',
                    borderColor: '#007bff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Date' } },
                    y: { title: { display: true, text: 'Sales Amount (Rs)' }, beginAtZero: true }
                }
            }
        });

        function updateChart() {
            const view = document.getElementById('view-select').value;
            salesChart.data.labels = chartData[view].labels;
            salesChart.data.datasets[0].data = chartData[view].data;
            salesChart.update();
        }
    </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
