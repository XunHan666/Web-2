<?php
require_once '../env/config.php';
session_start();

if (isset($_SESSION['account_id'])) {
    header('Location: ../index.php'); exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = mysqli_prepare($db_connect,
        "SELECT u.*, r.name AS role_name FROM accounts u JOIN roles r ON u.role_id = r.id WHERE u.username = ?"
    );
    mysqli_stmt_bind_param($stmt, 's', $_POST['username']);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($row && password_verify($_POST['password'], $row['password'])) {
        if ($row['status'] !== 'active') {
            $error = 'Your account is pending admin approval.';
        } else {
            $_SESSION['account_id'] = $row['id'];
            $_SESSION['username']   = $row['username'];
            $_SESSION['full_name']  = $row['full_name'];
            $_SESSION['role_id']    = $row['role_id'];
            $_SESSION['role_name']  = $row['role_name'];
            header('Location: ' . ($row['role_id'] == 3 ? '../dashboard/reader-dashboard.php' : '../index.php'));
            exit();
        }
    } else {
        $error = 'Invalid username or password.';
    }
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
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>
