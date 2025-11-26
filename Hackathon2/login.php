<?php
// login.php
session_start();
require 'db.php';

// If already logged in, redirect
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'user') {
        header("Location: employee_dashboard.php");
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if ($email === '' || $pass === '') {
        $error = "Please enter both email and password.";
    } else {
        // 1) Check admin table
        $stmt = $conn->prepare("SELECT admin_id, admin_pass FROM admin WHERE admin_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($admin_id, $admin_pass);
            $stmt->fetch();

            if ($pass === $admin_pass) { // plain-text compare for now
                $_SESSION['role']      = 'admin';
                $_SESSION['admin_id']  = $admin_id;
                $_SESSION['admin_email'] = $email;
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            // 2) Check user table
            $stmt->close();
            $stmt = $conn->prepare("SELECT user_id, user_pass FROM user WHERE user_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($user_id, $user_pass);
                $stmt->fetch();

                if ($pass === $user_pass) { // plain-text compare for now
                    $_SESSION['role']     = 'user';
                    $_SESSION['user_id']  = $user_id;
                    $_SESSION['user_email'] = $email;
                    header("Location: employee_dashboard.php");
                    exit;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "Account not found.";
            }
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tipsy Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .login-container {
            width: 320px;
            margin: 80px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.10);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label { display: block; margin-top: 10px; }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        button {
            margin-top: 15px;
            width: 100%;
            padding: 10px;
            border: none;
            background: #333;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background: #555;
        }
        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Tipsy Login</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>