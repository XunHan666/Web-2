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
                $success_message = 'Account created successfully! You can now <a href="login.php" style="color: var(--primary-color); font-weight:700;">login</a>.';
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
    
    <link rel="stylesheet" href="../css/style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* BẢNG MÀU ĐỒNG BỘ TRANG CHỦ & TRANG LOGIN */
            --primary-color: #1e4646;       /* Màu chủ đạo thư viện của bạn */
            --primary-hover: #153232;       /* Màu khi di chuột vào nút */
            --primary-light: rgba(30, 70, 70, 0.1); /* Màu nền nhạt khi focus */
            
            /* Ảnh nền đồng bộ hoàn toàn với trang login */
            --bg-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=1920');
        }

        body {
            display: flex; 
            align-items: center; 
            justify-content: center;
            min-height: 100vh; 
            margin: 0;
            font-family: 'Inter', sans-serif;
            padding: 2rem 1rem;
            box-sizing: border-box;
            
            /* Cấu hình ảnh nền phủ kín và cố định y hệt bên Login */
            background-image: linear-gradient(135deg, rgba(15, 23, 42, 0.7) 0%, rgba(30, 41, 59, 0.85) 100%), var(--bg-image);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .register-card {
            width: 100%; 
            max-width: 440px; 
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px); /* Hiệu ứng kính mờ thời thượng */
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .register-header { 
            text-align: center; 
            margin-bottom: 2.25rem; 
        }

        .register-header .logo-text {
            font-size: 2.25rem; 
            font-weight: 800; 
            color: var(--primary-color);
            letter-spacing: -0.03em; 
            margin-bottom: 0.5rem; 
            display: block;
        }

        .register-header p { 
            color: #475569; 
            font-size: 0.95rem; 
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
            background: #f8fafc;
        }
        
        .form-group input:focus {
            background: #ffffff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .btn-register {
            width: 100%; 
            padding: 0.875rem; 
            background-color: var(--primary-color);
            color: white; 
            border: none; 
            border-radius: 10px; 
            font-size: 1rem;
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); 
            margin-top: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-register:hover { 
            background-color: var(--primary-hover);
            transform: translateY(-1px); 
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .login-link { 
            text-align: center; 
            margin-top: 1.75rem; 
            font-size: 0.9rem; 
            color: #64748b; 
            border-top: 1px solid #e2e8f0;
            padding-top: 1.25rem;
        }

        .login-link a { 
            color: var(--primary-color);
            text-decoration: none; 
            font-weight: 600; 
        }
        
        .login-link a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <span class="logo-text">LibraryOS</span>
            <p>Join the library management team.</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-error" style="font-size: 0.875rem; margin-bottom: 1.5rem; padding: 0.75rem 1rem; background: #fef2f2; color: #b91c1c; border-radius: 8px; border: 1px solid #fee2e2; font-weight: 500;">
                <svg style="width:16px; height:16px; inline-block; vertical-align:text-bottom; margin-right:4px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success" style="font-size: 0.875rem; margin-bottom: 1.5rem; padding: 0.75rem 1rem; background: #f0fdf4; color: #15803d; border-radius: 8px; border: 1px solid #dcfce7; font-weight: 500;">
                <svg style="width:16px; height:16px; inline-block; vertical-align:text-bottom; margin-right:4px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!$success_message): ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="John Doe" required autofocus>
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