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
    
    <!-- External CSS and Google Fonts -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css?v=2.1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Notification Library (SweetAlert2) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Disable SwAl2 animations for a snappier minimalist feel */
        .swal2-popup, .swal2-icon, .swal2-icon * {
            animation: none !important;
            transition: none !important;
        }
        
        /* Account Dropdown Styling */
        .nav-right { display: flex; align-items: center; gap: 1.5rem; }
        .user-info {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 1rem;
            background: #f1f5f9; border-radius: 9999px; cursor: pointer;
            transition: all 0.2s; position: relative;
        }
        .user-info:hover { background: #e2e8f0; }
        .user-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--primary-color); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.875rem;
        }
        .user-details { display: flex; flex-direction: column; line-height: 1; }
        .user-name { font-weight: 600; font-size: 0.9rem; color: var(--text-color); }
        .user-role { font-size: 0.7rem; color: #64748b; text-transform: uppercase; margin-top: 2px; }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="<?php echo BASE_URL; ?>index.php" class="logo">LibraryOS</a>
            <nav style="display: flex; align-items: center; gap: 2.5rem;">
                <!-- Main Navigation Links -->
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>book/books.php">Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reader/readers.php">Readers</a></li>
                    <li><a href="<?php echo BASE_URL; ?>loan/loans.php">Loans</a></li>
                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): // Admin Features Only ?>
                        <li><a href="<?php echo BASE_URL; ?>user/users.php">User Management</a></li>
                        <li><a href="<?php echo BASE_URL; ?>system/settings.php">Settings</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="nav-right">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- User Account Trigger -->
                        <div class="user-info" id="userAccountBtn">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                            </div>
                            <div class="user-details">
                                <span class="user-name"><?php echo $_SESSION['full_name']; ?></span>
                                <span class="user-role"><?php echo $_SESSION['role_name']; ?></span>
                            </div>
                            <!-- Account Dropdown Menu -->
                            <div id="accountDropdown" style="display:none; position: absolute; top: 110%; right: 0; background: white; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 12px; width: 180px; z-index: 1000; border: 1px solid var(--border-color); overflow: hidden;">
                                <a href="<?php echo BASE_URL; ?>user/profile.php" style="display: block; padding: 0.75rem 1rem; color: var(--text-color); text-decoration: none; font-size: 0.9rem; font-weight: 600;">My Profile</a>
                                <a href="<?php echo BASE_URL; ?>authen/logout.php" style="display: block; padding: 0.75rem 1rem; color: var(--danger); text-decoration: none; font-size: 0.9rem; font-weight: 600; border-top: 1px solid #f1f5f9;">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>authen/login.php" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Login</a>
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
                    accountDropdown.style.display = accountDropdown.style.display === 'none' ? 'block' : 'none';
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
if (!function_exists('showAlert')) {
    /**
     * Global Helper: Show Notification Popup
     * @param string $message The text to display
     * @param string $type success, error, info
     */
    function showAlert($message, $type = 'success') {
        // Standardize SweetAlert icons
        $icon_type = ($type == 'error') ? 'error' : (($type == 'info') ? 'info' : 'success');
        
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '$icon_type',
                    title: 'LibraryOS Notification',
                    text: '" . addslashes($message) . "',
                    confirmButtonColor: '#3b82f6',
                    showClass: { popup: '', backdrop: '' },
                    hideClass: { popup: '', backdrop: '' }
                });
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
