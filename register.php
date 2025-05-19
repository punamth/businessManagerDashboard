<?php
include 'includes/db_connect.php';
session_start();

$username = $email = "";
$errors = [
    'username' => '',
    'email' => '',
    'password' => '',
    'confirmPassword' => '',
    'general' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    if (empty($username)) {
        $errors['username'] = "Username is required.";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    if (empty($confirmPassword)) {
        $errors['confirmPassword'] = "Please confirm your password.";
    } elseif ($password !== $confirmPassword) {
        $errors['confirmPassword'] = "Passwords do not match.";
    }

    // Check username uniqueness only if it's not empty and valid
    if (!empty($username) && empty($errors['username'])) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['username'] = "Username already exists.";
        }
        $stmt->close();
    }

    // Check email uniqueness only if valid
    if (!empty($email) && empty($errors['email'])) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['email'] = "Email already in use.";
        }
        $stmt->close();
    }

    // If no errors, insert into DB
    if (!array_filter($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit();
        } else {
            $errors['general'] = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error {
            color: red;
            font-size: 0.9em;
        }
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
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.58);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            color: rgb(43, 154, 223);
        }
        .btn-primary {
            background-color: rgb(43, 154, 223);
            border: none;
        }
        .btn-primary:hover {
            background-color: rgb(43, 154, 223);
        }
        a {
            color: rgb(43, 154, 223);
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

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?= $errors['general'] ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>">
            <div class="error"><?= $errors['username'] ?></div>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
            <div class="error"><?= $errors['email'] ?></div>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control">
            <div class="error"><?= $errors['password'] ?></div>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirmPassword" id="confirmPassword" class="form-control">
            <div class="error"><?= $errors['confirmPassword'] ?></div>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" id="showPassword" class="form-check-input">
            <label class="form-check-label" for="showPassword">Show Password</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Register</button>

        <div class="d-flex justify-content-between mt-3">
            <p><a href="login.php">Already have an account? Login</a></p>
        </div>
    </form>
</div>

<script>
    document.getElementById('showPassword').addEventListener('change', function () {
        const type = this.checked ? 'text' : 'password';
        document.getElementById('password').type = type;
        document.getElementById('confirmPassword').type = type;
    });
</script>

</body>
</html>
