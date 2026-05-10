<?php
/**
 * User Login and Session Initiation
 */
require_once '../env/config.php';
session_start();

// Redirect to dashboard if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input to prevent basic SQL injection
    $input_username = mysqli_real_escape_string($db_connect, $_POST['username']);
    $input_password = $_POST['password'];

    // Query to find user and their role name via a JOIN
    $sql_query = "SELECT u.*, r.name as role_name 
                 FROM users u 
                 JOIN roles r ON u.role_id = r.id 
                 WHERE u.username = '$input_username' AND u.status = 'active'";
    
    $query_result = mysqli_query($db_connect, $sql_query);

    // Check if user exists
    if ($query_result && mysqli_num_rows($query_result) > 0) {
        $user_data = mysqli_fetch_assoc($query_result);
        
        // Verify the entered password against the hashed password in database
        if (password_verify($input_password, $user_data['password'])) {
            // Setup Session variables for the authenticated user
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['full_name'] = $user_data['full_name'];
            $_SESSION['role_id'] = $user_data['role_id'];
            $_SESSION['role_name'] = $user_data['role_name'];

            // Successful login, redirect to Dashboard
            header("Location: ../index.php");
            exit();
        } else {
            $error_message = 'Invalid username or password.';
        }
    } else {
        $error_message = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LibraryOS</title>
    
    <!-- Include Global CSS -->
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }
        .login-card {
            width: 100%; max-width: 400px; padding: 2.5rem;
            background: var(--card-bg); border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .login-header { text-align: center; margin-bottom: 2rem; }
        .login-header .logo-text {
            font-size: 1.75rem; font-weight: 800; color: var(--primary-color);
            letter-spacing: -0.025em; margin-bottom: 0.5rem; display: block;
        }
        .login-header p { color: #64748b; font-size: 0.95rem; }
        .btn-login {
            width: 100%; padding: 0.875rem; background-color: var(--primary-color);
            color: white; border: none; border-radius: 10px; font-size: 1rem;
            font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-top: 1rem;
        }
        .btn-login:hover { opacity: 0.9; transform: translateY(-1px); }
        .register-link { text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: #64748b; }
        .register-link a { color: var(--primary-color); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <span class="logo-text">LibraryOS</span>
            <p>Welcome back! Please enter your details.</p>
        </div>

        <!-- Display Error Notice if available -->
        <?php if ($error_message): ?>
            <div class="alert alert-error" style="font-size: 0.9rem; margin-bottom: 1.5rem;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <!-- Recovery & Registration Links -->
        <div class="register-link">
            <a href="forgot_password.php" style="color: #64748b; font-size: 0.85rem; font-weight: 500; display: block; margin-bottom: 0.5rem;">Forgot Password?</a>
            Don't have an employee account? <a href="register.php">Contact Admin</a>
        </div>
    </div>
</body>
</html>
