<?php
/**
 * User Account Registration
 */
require_once '../env/config.php';
session_start();

// Redirect to dashboard if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input
    $input_username  = mysqli_real_escape_string($db_connect, $_POST['username']);
    $input_password  = $_POST['password'];
    $confirm_pwd     = $_POST['confirm_password'];
    $input_full_name = mysqli_real_escape_string($db_connect, $_POST['full_name']);

    // Basic Validation: Passwords must match
    if ($input_password !== $confirm_pwd) {
        $error_message = 'Passwords do not match.';
    } else {
        // Validation: Ensure the username is unique
        $check_sql = "SELECT id FROM users WHERE username = '$input_username'";
        $check_res = mysqli_query($db_connect, $check_sql);

        if ($check_res && mysqli_num_rows($check_res) > 0) {
            $error_message = 'Username already exists.';
        } else {
            // Create a secure hash of the password
            $hashed_password = password_hash($input_password, PASSWORD_DEFAULT);
            
            // Default role is set to Librarian (ID 2)
            $insert_sql = "INSERT INTO users (username, password, full_name, role_id, status) 
                          VALUES ('$input_username', '$hashed_password', '$input_full_name', 2, 'active')";
            
            if (mysqli_query($db_connect, $insert_sql)) {
                $success_message = 'Account created successfully! You can now <a href="login.php">login</a>.';
            } else {
                $error_message = 'Failed to create account. Please contact technical support.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LibraryOS</title>
    
    <!-- Link to Global Styles -->
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 2rem 1rem;
        }
        .register-card {
            width: 100%; max-width: 450px; padding: 2.5rem;
            background: var(--card-bg); border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .register-header { text-align: center; margin-bottom: 2rem; }
        .register-header .logo-text {
            font-size: 1.75rem; font-weight: 800; color: var(--primary-color);
            letter-spacing: -0.025em; margin-bottom: 0.5rem; display: block;
        }
        .register-header p { color: #64748b; font-size: 0.95rem; }
        .btn-register {
            width: 100%; padding: 0.875rem; background-color: var(--primary-color);
            color: white; border: none; border-radius: 10px; font-size: 1rem;
            font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-top: 1rem;
        }
        .btn-register:hover { opacity: 0.9; transform: translateY(-1px); }
        .login-link { text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: #64748b; }
        .login-link a { color: var(--primary-color); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <span class="logo-text">LibraryOS Registration</span>
            <p>Join the library management team.</p>
        </div>

        <!-- Display Alerts -->
        <?php if ($error_message): ?>
            <div class="alert alert-error" style="font-size: 0.9rem; margin-bottom: 1.5rem;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success" style="font-size: 0.9rem; margin-bottom: 1.5rem;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Show form only if registration was not successful -->
        <?php if (!$success_message): ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="johndoe" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-register">Create Account</button>
        </form>
        <?php endif; ?>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>
</body>
</html>
