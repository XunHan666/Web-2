<?php
require_once '../env/config.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit(); }

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = mysqli_real_escape_string($db_connect, $_POST['username']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $full_name = mysqli_real_escape_string($db_connect, $_POST['full_name']);

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (mysqli_num_rows(mysqli_query($db_connect, "SELECT id FROM users WHERE username='$username'")) > 0) {
        $error = 'Username already exists.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ok   = mysqli_query($db_connect, "INSERT INTO users (username,password,full_name,role_id,status) VALUES ('$username','$hash','$full_name',2,'active')");
        $success = $ok ? 'Account created! <a href="login.php">Login now</a>.' : 'Registration failed. Please try again.';
        if (!$ok) $error = $success;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — LibraryOS</title>
    <link rel="stylesheet" href="../css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <span class="logo-text">LibraryOS</span>
            <p>Create a staff account.</p>
        </div>

        <?php if ($error):   ?><div class="auth-alert-error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success && !$error): ?><div class="auth-alert-success"><?php echo $success; ?></div><?php endif; ?>

        <?php if (!$success || $error): ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="Nguyen Van A" required autofocus>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min. 6 chars" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat" required>
                </div>
            </div>
            <button type="submit" class="btn-submit">Create Account</button>
        </form>
        <?php endif; ?>

        <div class="auth-footer">Already have an account? <a href="login.php">Sign In</a></div>
    </div>
</body>
</html>