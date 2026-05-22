<?php
require_once '../env/config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php'); exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($db_connect, $_POST['username']);
    $password = $_POST['password'];

    $res = mysqli_query($db_connect, "SELECT u.*, r.name role_name FROM users u JOIN roles r ON u.role_id=r.id WHERE u.username='$username' AND u.status='active'");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id']   = $row['id'];
            $_SESSION['username']  = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['role_id']   = $row['role_id'];
            $_SESSION['role_name'] = $row['role_name'];
            header('Location: ' . ($row['role_id'] == 3 ? '../reader/dashboard.php' : '../index.php'));
            exit();
        }
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — LibraryOS</title>
    <link rel="stylesheet" href="../css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <span class="logo-text">LibraryOS</span>
            <p>Welcome back! Please sign in.</p>
        </div>

        <?php if ($error): ?>
            <div class="auth-alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-submit">Sign In</button>
        </form>

        <div class="auth-footer">
            <a href="forgot_password.php" style="color:#64748b;font-weight:500;display:block;margin-bottom:0.6rem;">Forgot password?</a>
            Are you a reader? <a href="reader_register.php">Register here</a>
        </div>
    </div>
</body>
</html>
