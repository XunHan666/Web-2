<?php
require_once '../env/config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role_id'] == 3 ? '../reader/dashboard.php' : '../index.php')); exit();
}

$errors = []; $success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email']     ?? '');
    $phone    = trim($_POST['phone']     ?? '');
    $username = trim($_POST['username']  ?? '');
    $password = $_POST['password']       ?? '';
    $confirm  = $_POST['confirm']        ?? '';

    if (!$name)  $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (!$phone) $errors[] = 'Phone is required.';
    if (!$username) $errors[] = 'Username is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $chk = mysqli_prepare($db_connect, "SELECT id FROM users WHERE username=? OR email=?");
        mysqli_stmt_bind_param($chk, 'ss', $username, $email);
        mysqli_stmt_execute($chk);
        if (mysqli_stmt_num_rows(mysqli_stmt_get_result($chk)) > 0) $errors[] = 'Username or email already exists.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        mysqli_begin_transaction($db_connect);
        try {
            $s1 = mysqli_prepare($db_connect, "INSERT INTO users (username,password,full_name,role_id,status) VALUES (?,?,?,3,'active')");
            mysqli_stmt_bind_param($s1,'sss',$username,$hash,$name);
            mysqli_stmt_execute($s1);
            $uid = mysqli_insert_id($db_connect);

            $s2 = mysqli_prepare($db_connect, "INSERT INTO readers (name,email,phone,status,user_id) VALUES (?,?,?,'active',?)");
            mysqli_stmt_bind_param($s2,'sssi',$name,$email,$phone,$uid);
            mysqli_stmt_execute($s2);

            mysqli_commit($db_connect);
            $success = true;
        } catch (Exception $e) { mysqli_rollback($db_connect); $errors[] = 'Registration failed. Please try again.'; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reader Registration — LibraryOS</title>
    <link rel="stylesheet" href="../css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-card" style="max-width:520px">
        <div class="auth-header">
            <span class="logo-text">LibraryOS</span>
            <p>Create your reader account</p>
        </div>

        <?php if ($success): ?>
            <div class="auth-success-box">
                <div style="font-size:2rem;margin-bottom:.5rem">🎉</div>
                <strong>Registration Successful!</strong>
                <p style="margin:.5rem 0 0">Your account is ready. <a href="login.php" style="color:var(--primary-color);font-weight:700">Sign in now</a></p>
            </div>
        <?php else: ?>
            <?php if ($errors): ?>
                <div class="auth-alert-error">
                    <ul style="margin:.25rem 0 0 1rem;padding:0">
                        <?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="reader_register.php" method="POST">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm">Confirm *</label>
                        <input type="password" id="confirm" name="confirm" placeholder="Repeat password" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Create Account</button>
            </form>
        <?php endif; ?>

        <div class="auth-footer">Already have an account? <a href="login.php">Sign In</a></div>
    </div>
</body>
</html>
