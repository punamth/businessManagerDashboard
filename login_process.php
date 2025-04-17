<?php
include 'includes\db_connect.php';
session_start();

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
        header("Location: index.php");
    } else {
        echo "Invalid username or password. <a href='login.php'>Try again</a>";
    }
    
    $stmt->close();
}
?>
