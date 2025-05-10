<?php
include 'includes/db_connect.php';
session_start();

// Clear any existing session data
if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color:rgb(101, 178, 226);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .reset-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.58);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            color:rgb(43, 154, 223);
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

    <div class="reset-box">
        <h2 class="text-center">Reset Password</h2>
        
        <?php if (!isset($_GET['token'])): ?>
            <!-- Step 1: Request password reset link -->
            <p class="text-center mb-4">Enter your username to receive a password reset link</p>
            <form action="reset_request.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Request Reset Link</button>
                <p class="mt-3 text-center"><a href="login.php">Back to Login</a></p>
            </form>
        <?php else: ?>
            <!-- Step 2: Enter new password with token -->
            <p class="text-center mb-4">Enter your new password</p>
            <form action="reset_password.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" name="new_password" id="new_password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="showPassword">
                    <label class="form-check-label" for="showPassword">Show Password</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                <p class="mt-3 text-center"><a href="login.php">Back to Login</a></p>
            </form>
            
            <script>
                document.getElementById("showPassword").addEventListener("change", function () {
                    var passwordInput = document.getElementById("new_password");
                    var confirmInput = document.getElementById("confirm_password");
                    passwordInput.type = this.checked ? "text" : "password";
                    confirmInput.type = this.checked ? "text" : "password";
                });
            </script>
        <?php endif; ?>
        
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>