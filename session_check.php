<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function check_login() {
    // If not logged in or session expired, redirect to login
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
    
    // Optional: Check for session timeout (e.g., after 30 minutes)
    $timeout = 1800; // 30 minutes in seconds
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $timeout)) {
        // Session expired
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit();
    }
    
    // Update login time on activity
    $_SESSION['login_time'] = time();
    
    // Return user ID for convenience
    return $_SESSION['user_id'];
}

// Example of how to use this in protected pages:
// $user_id = check_login();
?>