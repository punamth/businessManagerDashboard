<?php
include '../includes/db_connect.php';
session_start();

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <!-- Bootstrap CSS & Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Home Button -->
    <a href="../index.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
        <i class="bi bi-house-door-fill"></i>
        Home
    </a>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #007bff; }
        select { margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">

    <h2>Sales Report</h2>

    <label for="view-select"><strong>View By:</strong></label>
    <select id="view-select" class="form-select w-auto d-inline-block" onchange="updateChart()">
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
    </select>

    <canvas id="salesChart" height="100"></canvas>
</div>

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
                backgroundColor: 'rgba(0, 123, 255, 0.5)',
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

</body>
</html>
