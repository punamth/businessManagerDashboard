<?php
include '../includes/db_connect.php';
require '../includes/mail.php'; // SMTP Mail Setup

$today = date("Y-m-d");

// Get pending reminders
$query = "SELECT * FROM payment_reminders WHERE reminder_date <= '$today' AND sent_status = 'Pending'";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $email = "";

    if ($row['customer_id']) {
        $customer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT email FROM customers WHERE customer_id = {$row['customer_id']}"));
        $email = $customer['email'];
    } elseif ($row['vendor_id']) {
        $vendor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT email FROM vendors WHERE vendor_id = {$row['vendor_id']}"));
        $email = $vendor['email'];
    }

    if ($email) {
        $subject = "Payment Reminder";
        $message = $row['message'];

        if (sendEmail($email, $subject, $message)) {
            mysqli_query($conn, "UPDATE payment_reminders SET sent_status = 'Sent' WHERE reminder_id = {$row['reminder_id']}");
        }
    }
}
?>
