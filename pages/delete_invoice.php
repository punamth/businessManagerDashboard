<?php
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['invoice_id'])) {
    $id = intval($_POST['invoice_id']);

    // First delete invoice items
    $conn->query("DELETE FROM invoice_items WHERE invoice_id = $id");

    // Then delete the invoice
    $conn->query("DELETE FROM invoices WHERE id = $id");

    // Redirect
    header("Location: payment_tracking.php");
    exit();
}
?>
