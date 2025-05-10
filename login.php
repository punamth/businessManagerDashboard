<?php
include 'includes/db_connect.php';
session_start(); // Start session to check if already logged in

// Clear any existing session data on the login page
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
    <title>Login</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color:rgb(101, 178, 226); /* Blue Background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.58);
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

    <div class="login-box">
        <h2 class="text-center">Login</h2>
        <form action="login_process.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="showPassword">
                <label class="form-check-label" for="showPassword">Show Password</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
            <?php
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['login_error'])) {
                echo '<div class="alert alert-danger mt-3" role="alert">' . $_SESSION['login_error'] . '</div>';
                unset($_SESSION['login_error']);
            }
            ?>

            <div class="d-flex justify-content-between mt-3">
                <p><a href="register.php">Register</a></p>
                <p><a href="forgot_password.php">Forgot Password?</a></p>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById("showPassword").addEventListener("change", function () {
            var passwordInput = document.getElementById("password");
            passwordInput.type = this.checked ? "text" : "password";
        });
    </script>
</body>
</html>
