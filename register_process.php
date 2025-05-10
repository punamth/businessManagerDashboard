<?php
// Include the database connection file
include 'includes/db_connect.php';

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Always clear any existing session first
session_unset();
session_destroy();
session_start();
session_regenerate_id(true); // Generate new session ID for security

// Check if the form is submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Validate inputs
    $errors = [];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errors[] = "All fields are required.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Username already exists. Please choose another one.";
    }
    $stmt->close();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email already in use. Please use another email or try to recover your password.";
    }
    $stmt->close();

    // If there are errors, display them
    if (!empty($errors)) {
        echo "<div style='max-width: 500px; margin: 50px auto; padding: 20px; background-color: #f8d7da; border-radius: 5px;'>";
        echo "<h3>Registration Failed</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        echo "<p><a href='register.php'>Try Again</a></p>";
        echo "</div>";
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        // Get the new user ID
        $user_id = $conn->insert_id;
        
        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['login_time'] = time(); // Add login time for session timeout
        
        // Redirect to the index page or dashboard
        header("Location: index.php");
        exit();
    } else {
        // Handle errors
        echo "<div style='max-width: 500px; margin: 50px auto; padding: 20px; background-color: #f8d7da; border-radius: 5px;'>";
        echo "<h3>Registration Error</h3>";
        echo "<p>Error: " . $stmt->error . "</p>";
        echo "<p><a href='register.php'>Try Again</a></p>";
        echo "</div>";
    }

    // Close the statement and connection
    $stmt->close();
}
?>