<?php
/**
 * Global Header and Session Management
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication and Security Check
$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'register.php', 'reader_register.php', 'forgot_password.php'];

// If user is not logged in and current page is not public, redirect to login
if (!isset($_SESSION['user_id']) && !in_array($current_page, $public_pages)) {
    header("Location: " . BASE_URL . "authen/login.php");
    exit();
}

$is_reader_area = (strpos($_SERVER['PHP_SELF'], '/reader/') !== false);

if (isset($_SESSION['role_id'])) {
    if ($_SESSION['role_id'] == 3) {
        if (!$is_reader_area) {
            header("Location: " . BASE_URL . "reader/dashboard.php");
            exit();
        }
        
        // Fetch reader info globally for Reader Portal
        global $reader, $db_connect;
        if (isset($db_connect)) {
            $stmt = mysqli_prepare($db_connect, "SELECT * FROM readers WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            $reader = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            if (!$reader) { session_destroy(); header('Location: ' . BASE_URL . 'authen/login.php'); exit(); }
        }
    } else {
        if ($is_reader_area) {
            header("Location: " . BASE_URL . "index.php");
            exit();
        }
        
        // Admin/Staff: Get pending requests count for badge
        global $db_connect, $pending_count;
        $pending_count = 0;
        if (isset($db_connect)) {
            $p_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM loans WHERE status = 'pending'");
            if ($p_res) $pending_count = mysqli_fetch_array($p_res)[0];
        }
    }
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
</head>

<body>
    <header>
        <div class="nav-container">
            <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3): ?>
                <a href="<?php echo BASE_URL; ?>reader/dashboard.php" class="logo">LibraryOS</a>
                <span style="font-size: 0.72rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2rem; display: block;">Reader Portal</span>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>index.php" class="logo">LibraryOS</a>
            <?php endif; ?>
            <nav>
                <ul>
                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3): ?>
                    <!-- ===== READER PORTAL MENU ===== -->
                    <li><a href="<?php echo BASE_URL; ?>reader/dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">My Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reader/book.php" class="<?php echo ($current_page == 'book.php') ? 'active' : ''; ?>">Browse Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reader/my_loans.php" class="<?php echo ($current_page == 'my_loans.php') ? 'active' : ''; ?>">My Loans</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reader/profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">My Profile</a></li>
                    <?php else: ?>
                    <!-- ===== STAFF / ADMIN MENU ===== -->
                    <li><a href="<?php echo BASE_URL; ?>index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>book/books.php" class="<?php echo ($current_page == 'books.php' || strpos($current_page, 'book') !== false) ? 'active' : ''; ?>">Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reader_management/readers.php" class="<?php echo ($current_page == 'readers.php') ? 'active' : ''; ?>">Readers</a></li>
                    <li><a href="<?php echo BASE_URL; ?>loan/loans.php" class="<?php echo ($current_page == 'loans.php') ? 'active' : ''; ?>">Loans</a></li>
                    <li><a href="<?php echo BASE_URL; ?>loan/requests.php" class="<?php echo ($current_page == 'requests.php') ? 'active' : ''; ?>">
                        Requests
                        <?php if(isset($pending_count) && $pending_count > 0): ?>
                            <span style="background:var(--danger);color:white;border-radius:10px;padding:2px 6px;font-size:0.7rem;margin-left:4px;"><?php echo $pending_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                    <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): // Admin only ?>
                        <li><a href="<?php echo BASE_URL; ?>user/users.php" class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">User Management</a></li>
                        <li><a href="<?php echo BASE_URL; ?>system/settings.php" class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">Settings</a></li>
                    <?php endif; ?>
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
                                <?php if ($_SESSION['role_id'] == 3): ?>
                                    <a href="<?php echo BASE_URL; ?>reader/profile.php">My Profile</a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>user/profile.php">My Profile</a>
                                <?php endif; ?>
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
if (!function_exists('showAlert')) {
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
            });
            </script>";
        }
}
?>
<?php 
include_once __DIR__ . '/../Notification/views/notif_templates.php';
?>