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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and fetch form data
    $invoice_date = $_POST['invoice_date'];
    $company_name = strtoupper(trim($_POST['company_name']));
    $company_address = trim($_POST['company_address']);
    $customer_name = trim($_POST['customer_name']); // Customer name from form
    $customer_address = trim($_POST['customer_address']);
    $item_names = $_POST['item_name'];
    $quantities = $_POST['quantity'];
    $prices = $_POST['price'];
    $total_amount = floatval($_POST['total_amount']);
    $amount_paid = floatval($_POST['amount_paid']);
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'None';

    $amount_due = $total_amount - $amount_paid;

    // Verify stock availability
    foreach ($item_names as $index => $item_name) {
        $quantity = intval($quantities[$index]);
        $item_name_upper = strtoupper($item_name);

        $check_stmt = $conn->prepare("SELECT quantity FROM stock WHERE UPPER(item_name) = ?");
        $check_stmt->bind_param("s", $item_name_upper);
        $check_stmt->execute();
        $check_stmt->bind_result($available_quantity);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($available_quantity === null || $available_quantity < $quantity) {
            echo "<script>alert('Insufficient stock for item: $item_name'); window.history.back();</script>";
            exit;
        }
    }

    // Insert invoice
    $stmt = $conn->prepare("INSERT INTO invoices (invoice_date, company_name, company_address, customer_name, customer_address, total_amount, amount_paid, balance_due, payment_method, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssddsi", $invoice_date, $company_name, $company_address, $customer_name, $customer_address, $total_amount, $amount_paid, $amount_due, $payment_method, $user_id);
    $stmt->execute();
    $invoice_id = $stmt->insert_id;
    $stmt->close();

    // Insert invoice items
    $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, item_name, quantity, price, user_id) VALUES (?, ?, ?, ?, ?)");
    foreach ($item_names as $index => $item_name) {
        $quantity = intval($quantities[$index]);
        $price = floatval($prices[$index]);
        $stmt->bind_param("isidi", $invoice_id, $item_name, $quantity, $price, $user_id);
        $stmt->execute();
    }
    $stmt->close();

    // Update stock quantities
    foreach ($item_names as $index => $item_name) {
        $quantity = intval($quantities[$index]);
        $item_name_upper = strtoupper($item_name);

        $update_stmt = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE UPPER(item_name) = ?");
        $update_stmt->bind_param("is", $quantity, $item_name_upper);
        $update_stmt->execute();
        $update_stmt->close();
    }

    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Company Info
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->Cell(0, 10, $company_name, 0, 1, 'C');
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, $company_address, 0, 1, 'C');
    $pdf->Ln(10);

    // Customer Info
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(100, 8, 'Invoice Date: ' . $invoice_date, 0, 1);
    $pdf->Cell(100, 8, 'Customer Name: ' . $customer_name, 0, 1);
    $pdf->Cell(100, 8, 'Customer Address: ' . $customer_address, 0, 1);
    $pdf->Ln(10);

    // Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(10, 10, 'S.N.', 1, 0, 'C');
    $pdf->Cell(80, 10, 'Item Name', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Quantity', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Price', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Subtotal', 1, 1, 'C');

    // Table Body
    $pdf->SetFont('Arial', '', 12);
    foreach ($item_names as $index => $item_name) {
        $quantity = intval($quantities[$index]);
        $price = floatval($prices[$index]);
        $subtotal = $quantity * $price;

        $pdf->Cell(10, 10, $index + 1, 1, 0, 'C');
        $pdf->Cell(80, 10, $item_name, 1, 0);
        $pdf->Cell(30, 10, $quantity, 1, 0, 'C');
        $pdf->Cell(30, 10, number_format($price, 2), 1, 0, 'C');
        $pdf->Cell(40, 10, number_format($subtotal, 2), 1, 1, 'C');
    }

    $pdf->Ln(10);

    // Payment Summary
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(150, 10, 'Total Amount:', 0, 0, 'R');
    $pdf->Cell(40, 10, number_format($total_amount, 2), 0, 1, 'C');

    $pdf->Cell(150, 10, 'Amount Paid:', 0, 0, 'R');
    $pdf->Cell(40, 10, number_format($amount_paid, 2), 0, 1, 'C');

    $pdf->Cell(150, 10, 'Amount Due:', 0, 0, 'R');
    $pdf->Cell(40, 10, number_format($amount_due, 2), 0, 1, 'C');

    $pdf->Cell(150, 10, 'Payment Method:', 0, 0, 'R');
    $pdf->Cell(40, 10, $payment_method, 0, 1, 'C');

    // Output PDF inline
    $pdf->Output('I', 'Invoice_' . $invoice_id . '.pdf');
}
?>
