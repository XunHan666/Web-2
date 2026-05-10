<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - LibraryOS</title>
    
    <!-- Link to Global Styles -->
    <link rel="stylesheet" href="../css/style.css">
    
    <style>
        body {
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Inter', sans-serif;
        }
        .info-card {
            max-width: 500px; background: white; padding: 3rem;
            border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .icon-circle {
            width: 80px; height: 80px; background: #fff7ed;
            color: #f97316; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 2rem;
        }
        h1 { font-size: 1.75rem; color: #1e293b; margin-bottom: 1rem; }
        p { color: #64748b; line-height: 1.6; margin-bottom: 2rem; }
        .admin-contact-box {
            background: #f8fafc; padding: 1.5rem; border-radius: 12px;
            border: 1px solid #e2e8f0; margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="info-card">
        <!-- Security Icon -->
        <div class="icon-circle">
            <svg viewBox="0 0 24 24" width="40" height="40" stroke="currentColor" stroke-width="2" fill="none">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        
        <h1>Account Security</h1>
        <p>For security protection, password resets are handled manually by the Administrator. This prevents unauthorized access to the library management system.</p>
        
        <!-- Administrator Contact Instructions -->
        <div class="admin-contact-box">
            <div style="font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Contact Administrator</div>
            <div style="color: #64748b; font-size: 0.9rem;">Please visit the main office or contact your supervisor to verify your identity and have your password reset manually.</div>
        </div>
        
        <a href="login.php" class="btn btn-primary" style="text-decoration: none; display: inline-block;">Back to Login</a>
    </div>
</body>
</html>
