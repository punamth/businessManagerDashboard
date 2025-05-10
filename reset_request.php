<?php
include 'includes/db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    
    // Check if username exists
    $stmt = $conn->prepare("SELECT user_id, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        $email = $row['email'];
        
        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 3600); // Token expires in 1 hour
        
        // Store token in database
        $stmt = $conn->prepare("INSERT INTO password_reset (user_id, token, expires) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $token, $expires);
        $stmt->execute();
        
        // In a real application, you would send an email with a reset link
        // For demonstration purposes, we'll just display the link
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/forgot_password.php?token=" . $token;
        
        echo "<div style='text-align: center; margin-top: 50px;'>";
        echo "<h2>Password Reset Link</h2>";
        echo "<p>A password reset link has been generated for: " . htmlspecialchars($email) . "</p>";
        echo "<p>In a real application, this link would be emailed to the user.</p>";
        echo "<p><a href='" . htmlspecialchars($reset_link) . "'>Click here to reset your password</a></p>";
        echo "<p><a href='login.php'>Back to login</a></p>";
        echo "</div>";
    } else {
        echo "<div style='text-align: center; margin-top: 50px;'>";
        echo "<h2>User Not Found</h2>";
        echo "<p>No account found with that username.</p>";
        echo "<p><a href='forgot_password.php'>Try again</a></p>";
        echo "</div>";
    }
    
    $stmt->close();
}
?>
