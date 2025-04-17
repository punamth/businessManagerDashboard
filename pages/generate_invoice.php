<?php
require('../fpdf.php');
include '../includes/db_connect.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = $_POST['company_name'];
    $company_address = $_POST['company_address'];
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address'];
    $invoice_date = $_POST['invoice_date']; // Capture the date
    $items = $_POST['item_name'];

    // Ensure numeric values
    $quantities = array_map('floatval', $_POST['quantity']);
    $prices = array_map('floatval', $_POST['price']);

    $amount_paid = floatval($_POST['amount_paid']);
    $payment_method = $_POST['payment_method'];

    // Calculate total amount
    $total_amount = 0;
    foreach ($items as $key => $item) {
        $total_amount += $quantities[$key] * $prices[$key];
    }
    $balance_due = $total_amount - $amount_paid;

    // Insert invoice into the database
    $stmt = $conn->prepare("INSERT INTO invoices (company_name, company_address, customer_name, customer_address, invoice_date, total_amount, amount_paid, balance_due, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssddds", $company_name, $company_address, $customer_name, $customer_address, $invoice_date, $total_amount, $amount_paid, $balance_due, $payment_method);
    $stmt->execute();
    $invoice_id = $stmt->insert_id;
    $stmt->close();

    // Insert items into the database
    $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, item_name, quantity, price, total) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $key => $item) {
        $total = $quantities[$key] * $prices[$key];
        $stmt->bind_param("issdd", $invoice_id, $item, $quantities[$key], $prices[$key], $total);
        $stmt->execute();
    }
    $stmt->close();

    // Start output buffering
    ob_start();

    // Generate PDF invoice
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(190, 10, strtoupper($company_name), 0, 1, 'C');

    // Company address
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(190, 8, $company_address, 0, 1, 'C');
    $pdf->Ln(5);

    // Invoice Date
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(100, 8, "Invoice Date: " . $invoice_date, 0, 1);
    $pdf->Ln(2);

    // Customer details
    $pdf->Cell(100, 8, "Customer: " . $customer_name, 0, 1);
    $pdf->Cell(100, 8, "Address: " . $customer_address, 0, 1);
    $pdf->Ln(5);

    // Table Headers
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(80, 10, 'Item', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Price', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Total', 1, 1, 'C', true);

    // Table Data
    $pdf->SetFont('Arial', '', 12);
    foreach ($items as $key => $item) {
        $total = $quantities[$key] * $prices[$key];
        $pdf->Cell(80, 10, $item, 1);
        $pdf->Cell(30, 10, $quantities[$key], 1, 0, 'C');
        $pdf->Cell(40, 10, number_format($prices[$key], 2), 1, 0, 'R');
        $pdf->Cell(40, 10, number_format($total, 2), 1, 1, 'R');
    }

    // Amount Paid and Balance
    $pdf->Cell(150, 10, "Amount Paid", 1, 0, 'R');
    $pdf->Cell(40, 10, number_format($amount_paid, 2), 1, 1, 'R');

    $pdf->Cell(150, 10, "Balance Due", 1, 0, 'R');
    $pdf->Cell(40, 10, number_format($balance_due, 2), 1, 1, 'R');
    $pdf->Ln(5);

    // Payment Method (Now in a Proper Box)
    $pdf->Cell(150, 10, "Payment Method", 1, 0, 'R');
    $pdf->Cell(40, 10, $payment_method, 1, 1, 'R');
    $pdf->Ln(5);

    // Grand Total (Now at the bottom)
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(150, 10, "Grand Total", 1, 0, 'R');
    $pdf->Cell(40, 10, number_format($total_amount, 2), 1, 1, 'R');

    // Prevent previous output issues
    ob_end_clean();

    // Save PDF to server
    $invoice_folder = __DIR__ . '/../invoices/';
    $pdf_filename = $invoice_folder . "invoice_" . $invoice_id . ".pdf";
    $pdf->Output('F', $pdf_filename);

    // Output PDF to browser
    $pdf->Output();
}
?>
