<?php
/**
 * Global Header and Session Management
 */
session_start();

// Authentication and Security Check
$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'register.php'];

// If user is not logged in and current page is not public, redirect to login
if (!isset($_SESSION['user_id']) && !in_array($current_page, $public_pages)) {
    header("Location: " . BASE_URL . "authen/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibraryOS</title>
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css?v=1.4">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Disable SwAl2 animations for a snappier minimalist feel */
        .swal2-popup, .swal2-icon, .swal2-icon * {
            animation: none !important;
            transition: none !important;
        }
        
        /* Layout structure for Vertical Sidebar */
        body {
            display: flex;
            min-height: 100vh;
            background-color: var(--bg-color);
        }

        /* Vertical Sidebar Styling - Ép buộc nhận màu nền nhẹ nhàng */
        header {
            width: 260px;
            background: var(--primary-light) !important; /* Đổi hẳn sang màu nền nhẹ */
            border-right: 1px solid var(--border-color);
            border-bottom: none;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            height: 100vh;
            z-index: 100;
            padding: 2rem 0;
        }

        .nav-container {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: stretch;
            padding: 0 1.5rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color) !important;
            text-decoration: none;
            margin-bottom: 2.5rem;
            padding-left: 0.5rem;
            display: block;
        }

        nav {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        nav ul {
            display: flex;
            flex-direction: column;
            list-style: none;
            gap: 0.5rem;
        }

        nav ul li a {
            display: block;
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        /* Khi hover hoặc active: Nút nổi hẳn lên trên nền sidebar */
        nav ul li a:hover, nav ul li a.active {
            background-color: var(--card-bg) !important; 
            color: var(--primary-color) !important;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(30, 70, 70, 0.08);
        }

        /* Bottom Section of Sidebar (Account Info) */
        .nav-right {
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        /* Account Dropdown Styling adjusted for Sidebar */
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.8rem;
            background: var(--card-bg) !important;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        
        .user-info:hover {
            border-color: var(--primary-color);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-color) !important;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .user-details {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            overflow: hidden;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-color);
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .user-role {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-top: 2px;
        }

        #accountDropdown {
            display: none;
            position: absolute;
            bottom: 110%;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -4px 20px rgba(30, 37, 43, 0.08);
            border-radius: 10px;
            z-index: 1000;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        #accountDropdown a {
            display: block;
            padding: 0.75rem 1rem;
            color: var(--text-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: background 0.2s;
        }

        #accountDropdown a:hover {
            background: var(--primary-light);
            color: var(--primary-color);
        }

        /* Adjust Main Content Container to push it right of the Sidebar */
        .container {
            flex-grow: 1;
            margin-left: 260px;
            max-width: calc(100% - 260px);
            padding: 2.5rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="<?php echo BASE_URL; ?>index.php" class="logo">LibraryOS</a>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>book/books.php" class="<?php echo ($current_page == 'books.php' || strpos($current_page, 'book') !== false) ? 'active' : ''; ?>">Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reader/readers.php" class="<?php echo ($current_page == 'readers.php') ? 'active' : ''; ?>">Readers</a></li>
                    <li><a href="<?php echo BASE_URL; ?>loan/loans.php" class="<?php echo ($current_page == 'loans.php') ? 'active' : ''; ?>">Loans</a></li>
                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): // Admin Features Only ?>
                        <li><a href="<?php echo BASE_URL; ?>user/users.php" class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">User Management</a></li>
                        <li><a href="<?php echo BASE_URL; ?>system/settings.php" class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">Settings</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="nav-right">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-info" id="userAccountBtn">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                            </div>
                            <div class="user-details">
                                <span class="user-name"><?php echo $_SESSION['full_name']; ?></span>
                                <span class="user-role"><?php echo $_SESSION['role_name']; ?></span>
                            </div>
                            <div id="accountDropdown">
                                <a href="<?php echo BASE_URL; ?>user/profile.php">My Profile</a>
                                <a href="<?php echo BASE_URL; ?>authen/logout.php" style="color: var(--danger); border-top: 1px solid var(--border-color);">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>authen/login.php" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.6rem 1rem; width: 100%; text-align: center;">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    
    <script>
        /**
         * Simple Dropdown Logic
         */
        document.addEventListener('DOMContentLoaded', function() {
            const userAccountBtn = document.getElementById('userAccountBtn');
            const accountDropdown = document.getElementById('accountDropdown');
            if (userAccountBtn && accountDropdown) {
                userAccountBtn.addEventListener('click', (event) => {
                    event.stopPropagation();
                    accountDropdown.style.display = accountDropdown.style.display === 'block' ? 'none' : 'block';
                });
                // Close dropdown if user clicks elsewhere
                document.addEventListener('click', () => {
                    accountDropdown.style.display = 'none';
                });
            }
        });
    </script>

    <div class="container">
<?php
/**
 * Global Helper: Show Notification Popup
 * @param string $message The text to display
 * @param string $type success, error, info
 */
function showAlert($message, $type = 'success') {
    $icon_type = ($type == 'error') ? 'error' : (($type == 'info') ? 'info' : 'success');
    
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '$icon_type',
                title: 'LibraryOS Notification',
                text: '" . addslashes($message) . "',
                confirmButtonColor: '#1e4646',
                showClass: { popup: '', backdrop: '' },
                hideClass: { popup: '', backdrop: '' }
            });
        </script>";
    }
}
?>
<?php 
// Include Global Deletion & Return Notification logic
include_once __DIR__ . '/../Notification/Delete_notification.php';
include_once __DIR__ . '/../Notification/Return_Notification.php';
?>