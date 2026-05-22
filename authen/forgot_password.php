<?php
require_once '../env/config.php';
session_start();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($db_connect, $_POST['username']);
    
    // Check if user exists
    $res = mysqli_query($db_connect, "SELECT id FROM accounts WHERE username='$username'");
    if (mysqli_num_rows($res) > 0) {
        $account_id = mysqli_fetch_assoc($res)['id'];
        mysqli_query($db_connect, "INSERT INTO requests (type, account_id, target_id, status) VALUES ('password_reset', $account_id, $account_id, 'pending')");
        $message = "A password reset request for account '$username' has been sent to the Administrator.";
        $message_type = 'success';
    } else {
        $message = "Username not found in the system.";
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - LibraryOS</title>
    <link rel="stylesheet" href="../css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-card" style="text-align: center; max-width: 500px;">
        <div style="width:80px; height:80px; background:#fff7ed; color:#f97316; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem;">
            <svg viewBox="0 0 24 24" width="40" height="40" stroke="currentColor" stroke-width="2" fill="none">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        
        <h1 style="font-size: 1.5rem; color: #1e293b; margin-bottom: 1rem; margin-top: 0;">Account Security</h1>
        <p style="color: #475569; line-height: 1.5; margin-bottom: 1.5rem; font-size: 0.95rem;">For security protection, password resets are handled manually by the Administrator. Enter your username below to notify the Admin.</p>
        
        <?php if ($message): ?>
            <div class="<?php echo $message_type == 'success' ? 'auth-alert-success' : 'auth-alert-error'; ?>" style="margin-bottom: 1.5rem; text-align: left;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($message_type !== 'success'): ?>
        <form action="forgot_password.php" method="POST" style="text-align: left; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
            </div>
            <button type="submit" class="btn-submit">Send Request to Admin</button>
        </form>
        <?php endif; ?>
        
        <a href="login.php" class="btn-submit" style="text-decoration: none; display: block; box-sizing: border-box; text-align: center;">Back to Login</a>
    </div>
</body>
</html>
