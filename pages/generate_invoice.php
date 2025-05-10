<?php
session_start();
include '../includes/db_connect.php';
require('../fpdf.php');

// Check user login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $invoice_date = $_POST['invoice_date'];
    $company_name = strtoupper($_POST['company_name']); // Make company name UPPERCASE
    $company_address = $_POST['company_address'];
    $customer_name = $_POST['customer_name'];
    $customer_address = $_POST['customer_address'];
    $item_names = $_POST['item_name'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['price'];
    $total_amount = $_POST['total_amount'];
    $amount_paid = $_POST['amount_paid'];
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'None';

    $amount_due = $total_amount - $amount_paid;

    // Insert invoice into database
    $stmt = $conn->prepare("INSERT INTO invoices (invoice_date, company_name, company_address, customer_name, customer_address, total_amount, amount_paid, balance_due, payment_method, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssddsi", $invoice_date, $company_name, $company_address, $customer_name, $customer_address, $total_amount, $amount_paid, $amount_due, $payment_method, $user_id);
    $stmt->execute();
    $invoice_id = $stmt->insert_id;  // Get the last inserted invoice ID
    $stmt->close();

    // Insert invoice items into database
    $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, item_name, quantity, price, user_id) VALUES (?, ?, ?, ?, ?)");
    foreach ($item_names as $index => $item_name) {
        $quantity = $quantities[$index];
        $price = $prices[$index];
        $stmt->bind_param("isidi", $invoice_id, $item_name, $quantity, $price, $user_id);
        $stmt->execute();
    }

    $stmt->close();

    // Decrease stock quantity after invoice generation
    foreach ($item_names as $index => $item_name) {
        $quantity = $quantities[$index];

        // Convert item name to uppercase for case-insensitive matching
        $item_name_upper = strtoupper($item_name);

        // Update stock quantity
        $update_stmt = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE UPPER(item_name) = ?");
        $update_stmt->bind_param("is", $quantity, $item_name_upper);
        $update_stmt->execute();
        $update_stmt->close();
    }

    // Start FPDF for PDF generation
    $pdf = new FPDF();
    $pdf->AddPage();

    // Company Name
    $pdf->SetFont('Arial', 'B', 20); // Big, Bold
    $pdf->Cell(0, 10, $company_name, 0, 1, 'C'); // Centered
    $pdf->Ln(2);

    // Company Address
    $pdf->SetFont('Arial', '', 12); // Smaller font
    $pdf->Cell(0, 8, $company_address, 0, 1, 'C'); // Centered
    $pdf->Ln(10);

    // Invoice Information
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(100, 8, 'Invoice Date: ' . $invoice_date, 0, 1);
    $pdf->Cell(100, 8, 'Customer Name: ' . $customer_name, 0, 1);
    $pdf->Cell(100, 8, 'Customer Address: ' . $customer_address, 0, 1);
    $pdf->Ln(10);

    // Items Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(10, 10, 'S.N.', 1, 0, 'C');
    $pdf->Cell(80, 10, 'Item Name', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Quantity', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Price', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Subtotal', 1, 1, 'C');

    // Items Table Data
    $pdf->SetFont('Arial', '', 12);
    foreach ($item_names as $index => $item_name) {
        $quantity = $quantities[$index];
        $price = $prices[$index];
        $subtotal = $quantity * $price;

        $pdf->Cell(10, 10, $index + 1, 1, 0, 'C');
        $pdf->Cell(80, 10, $item_name, 1, 0);
        $pdf->Cell(30, 10, $quantity, 1, 0, 'C');
        $pdf->Cell(30, 10, number_format($price, 2), 1, 0, 'C');
        $pdf->Cell(40, 10, number_format($subtotal, 2), 1, 1, 'C');
    }

    $pdf->Ln(10);

    // Summary
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(150, 10, 'Total Amount:', 0, 0, 'R');
    $pdf->Cell(40, 10, number_format($total_amount, 2), 0, 1, 'C');

    $pdf->Cell(150, 10, 'Amount Paid:', 0, 0, 'R');
    $pdf->Cell(40, 10, number_format($amount_paid, 2), 0, 1, 'C');

    $pdf->Cell(150, 10, 'Amount Due:', 0, 0, 'R');
    $pdf->Cell(40, 10, number_format($amount_due, 2), 0, 1, 'C');

    $pdf->Cell(150, 10, 'Payment Method:', 0, 0, 'R');
    $pdf->Cell(40, 10, $payment_method, 0, 1, 'C');

    // Output PDF
    $pdf->Output('I', 'Invoice_' . $invoice_id . '.pdf');
}
?>
