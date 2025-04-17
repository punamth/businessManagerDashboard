<?php
$invoice_folder = __DIR__ . '/invoices/';
$sales_data = [];

// Scan the invoices folder for PDF files
$files = glob($invoice_folder . "*.pdf");

foreach ($files as $file) {
    // Convert PDF to text using pdftotext
    $output = shell_exec("C:\xampp\poppler-24.08.0\Library\bin\pdftotext.exe \"$file\" -"); // Windows
    // $output = shell_exec("pdftotext '$file' -"); // Linux/Mac (Uncomment for Linux)

    if (!$output) continue;

    // Extract date (YYYY-MM-DD format)
    preg_match('/(\d{4}-\d{2}-\d{2})/', $output, $date_match);
    $date = $date_match[1] ?? 'Unknown';

    // Extract total amount
    preg_match('/Grand Total\s+(\d+\.\d{2})/', $output, $total_match);
    $total_amount = isset($total_match[1]) ? floatval($total_match[1]) : 0;

    if ($date !== 'Unknown') {
        if (!isset($sales_data[$date])) {
            $sales_data[$date] = 0;
        }
        $sales_data[$date] += $total_amount;
    }
}

// Convert data to JSON for Chart.js
$sales_dates = json_encode(array_keys($sales_data));
$sales_values = json_encode(array_values($sales_data));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; text-align: center; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h2 { color: #007bff; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Sales Report</h2>
        <canvas id="salesChart"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $sales_dates; ?>,
                datasets: [{
                    label: 'Total Sales ($)',
                    data: <?php echo $sales_values; ?>,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0, 0, 255, 0.1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Date' } },
                    y: { title: { display: true, text: 'Sales Amount ($)' }, beginAtZero: true }
                }
            }
        });
    </script>

</body>
</html>
