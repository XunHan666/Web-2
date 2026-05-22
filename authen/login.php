<?php
/**
 * Login - Controller
 */
require_once '../env/config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($db_connect, $_POST['username']);
    $password = $_POST['password'];

    // Query to find user and their role name via a JOIN
    $sql_query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  JOIN roles r ON u.role_id = r.id 
                  WHERE u.username = '$input_username' AND u.status = 'active'";
    
    $query_result = mysqli_query($db_connect, $sql_query);

    if ($res && mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            header("Location: ../index.php");
            exit();
        }
    }
    $error_message = 'Invalid credentials.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LibraryOS</title>
    
    <link rel="stylesheet" href="../css/style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* BẢNG MÀU ĐỒNG BỘ TRANG CHỦ (Tùy chỉnh tại đây):
               - Nếu trang chủ là màu Cam (Shopee): dùng #ee4d2d và hover #d03d22
               - Nếu trang chủ là màu Xanh Đậm (Lazada/Thư viện mới): dùng #1e4646 và hover #153232
            */
            --primary-color: #1e4646;       /* Màu chủ đạo trang chủ của bạn */
            --primary-hover: #153232;       /* Màu khi di chuột vào nút */
            --primary-light: rgba(30, 70, 70, 0.1); /* Màu nền nhạt cho hiệu ứng focus */
            
            /* Ảnh nền phủ kín và cố định */
            --bg-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=1920');
        }

        body {
            display: flex; 
            align-items: center; 
            justify-content: center;
            min-height: 100vh; 
            margin: 0;
            font-family: 'Inter', sans-serif;
            
            /* Gradient overlay tối tiệp màu hòa quyện với ảnh nền */
            background-image: linear-gradient(135deg, rgba(15, 23, 42, 0.7) 0%, rgba(30, 41, 59, 0.85) 100%), var(--bg-image);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .login-card {
            width: 100%; 
            max-width: 420px; 
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px); /* Tăng độ mờ kính nhìn sang trọng hơn */
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header { 
            text-align: center; 
            margin-bottom: 2.25rem; 
        }

        .login-header .logo-text {
            font-size: 2.25rem; 
            font-weight: 800; 
            color: var(--primary-color); /* Đồng bộ màu logo */
            letter-spacing: -0.03em; 
            margin-bottom: 0.5rem; 
            display: block;
        }

        .login-header p { 
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
        
        /* Hiệu ứng focus đồng bộ màu trang chủ */
        .form-group input:focus {
            background: #ffffff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        .btn-login {
            width: 100%; 
            padding: 0.875rem; 
            background-color: var(--primary-color); /* Đồng bộ màu nút bấm chính */
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

        .btn-login:hover { 
            background-color: var(--primary-hover); /* Đồng bộ màu hover */
            transform: translateY(-1px); 
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .register-link { 
            text-align: center; 
            margin-top: 1.75rem; 
            font-size: 0.9rem; 
            color: #64748b; 
            border-top: 1px solid #e2e8f0;
            padding-top: 1.25rem;
        }

        .register-link a { 
            color: var(--primary-color); /* Đồng bộ màu link liên kết */
            text-decoration: none; 
            font-weight: 600; 
        }
        
        .register-link a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <span class="logo-text">LibraryOS</span>
            <p>Welcome back! Please enter your details.</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-error" style="font-size: 0.875rem; margin-bottom: 1.5rem; padding: 0.75rem 1rem; background: #fef2f2; color: #b91c1c; border-radius: 8px; border: 1px solid #fee2e2; font-weight: 500;">
                <svg style="width:16px; height:16px; inline-block; vertical-align:text-bottom; margin-right:4px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <?php echo $error_message; ?>
            </div>
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
            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <div class="register-link">
            <a href="forgot_password.php" style="color: #64748b; font-size: 0.85rem; font-weight: 500; display: block; margin-bottom: 0.75rem;">Forgot Password?</a>
            Don't have an employee account? <a href="register.php">Contact Admin</a>
        </div>
    </div>
</body>
</html>
