<?php
include 'includes/db_connect.php';
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any existing session data
if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start(); // Start a fresh session
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: rgb(101, 178, 226);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;  
        }
        h2 {
            color:rgb(43, 154, 223); /* Blue Text */
        }
        .btn-primary {
            background-color:rgb(43, 154, 223);
            border: none;
        }
        .btn-primary:hover {
            background-color:rgb(43, 154, 223);
        }
        a {
            color:rgb(43, 154, 223);
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="register-box">
    <h2 class="text-center">Register</h2>
    <form action="register_process.php" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" name="username" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <div class="mb-3">
            <label for="confirmPassword" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="showPassword">
            <label class="form-check-label" for="showPassword">Show Password</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
        <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // JavaScript to toggle password visibility
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirmPassword');
    const showPasswordCheckbox = document.getElementById('showPassword');

    showPasswordCheckbox.addEventListener('change', function() {
        const type = this.checked ? 'text' : 'password';
        passwordField.type = type;
        confirmPasswordField.type = type;
    });
</script>
</body>
</html>



<?php
// Include the database connection file
include 'includes/db_connect.php';


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
        // Clear any existing session data
        session_unset();
        session_destroy();
        session_start();
        
        // Registration successful - get user ID to set session
        $user_id = $conn->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        
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