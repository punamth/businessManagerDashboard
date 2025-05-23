<?php
include 'includes/db_connect.php';
session_start();

// Clear any existing session data first
session_unset();
session_destroy();
session_start();
session_regenerate_id(true); // Generate new session ID for security

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password);
    
    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time();
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['login_error'] = "Incorrect username or password.";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
}
?>
