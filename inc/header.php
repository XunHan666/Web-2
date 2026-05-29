<?php
/**
 * Global Header and Session Management
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$current_path = $_SERVER['PHP_SELF'];
$public_pages = ['login.php', 'register.php', 'forgot_password.php'];

$is_public_book_page = (
    strpos($current_path, '/reader/book.php') !== false
);

if (!isset($_SESSION['account_id']) && !in_array($current_page, $public_pages) && !$is_public_book_page) {
    header("Location: " . BASE_URL . "authen/login.php");
    exit();
}

$is_reader_area = (bool)preg_match('#/reader/#', $current_path) || basename($current_page) === 'reader-dashboard.php';

if (isset($_SESSION['role_id'])) {
    if ($_SESSION['role_id'] == 3) {
        if (!$is_reader_area) {
            header("Location: " . BASE_URL . "dashboard/reader-dashboard.php");
            exit();
        }
        global $reader, $db_connect;
        if (isset($db_connect)) {
            $stmt = mysqli_prepare($db_connect, "SELECT * FROM readers WHERE account_id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $_SESSION['account_id']);
            mysqli_stmt_execute($stmt);
            $reader = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            if (!$reader) {
                // Fetch details from accounts to sync all reader info
                $acc_stmt = mysqli_prepare($db_connect, "SELECT phone, email, address, dob, gender FROM accounts WHERE id = ?");
                mysqli_stmt_bind_param($acc_stmt, 'i', $_SESSION['account_id']);
                mysqli_stmt_execute($acc_stmt);
                $acc_info = mysqli_fetch_assoc(mysqli_stmt_get_result($acc_stmt));
                
                $ins_stmt = mysqli_prepare($db_connect, "INSERT INTO readers (name, phone, email, address, dob, gender, account_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
                mysqli_stmt_bind_param($ins_stmt, 'ssssssi', $_SESSION['full_name'], $acc_info['phone'], $acc_info['email'], $acc_info['address'], $acc_info['dob'], $acc_info['gender'], $_SESSION['account_id']);
                mysqli_stmt_execute($ins_stmt);
                mysqli_stmt_execute($stmt);
                $reader = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            }
        }
    } else {
        if ($is_reader_area) {
            header("Location: " . BASE_URL . "index.php");
            exit();
        }
        global $db_connect, $pending_count;
        $pending_count = 0;
        if (isset($db_connect)) {
            require_once __DIR__ . '/role_guard.php';
            $req_where = pending_requests_filter_sql((int)$_SESSION['role_id']);
            $p_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM requests WHERE status = 'pending' AND $req_where");
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css?v=1.7">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Badge VIEW-ONLY trên nav */
        .nav-view-badge {
            display: inline-block;
            font-size: 0.58rem;
            font-weight: 700;
            color: #94a3b8;
            background: #e8ecef;
            border-radius: 4px;
            padding: 1px 5px;
            letter-spacing: 0.05em;
            vertical-align: middle;
            margin-left: 5px;
            line-height: 1.7;
        }
    </style>
</head>

<body>
    <header>
        <div class="nav-container">
            <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3): ?>
                <a href="<?php echo BASE_URL; ?>dashboard/reader-dashboard.php" class="logo">LibraryOS</a>
                <span style="font-size: 0.72rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2rem; display: block;">Reader Portal</span>
            <?php elseif (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                <a href="<?php echo BASE_URL; ?>dashboard/admin-dashboard.php" class="logo">LibraryOS</a>
                <span style="font-size: 0.72rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2rem; display: block;">Admin Portal</span>
            <?php elseif (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2): ?>
                <a href="<?php echo BASE_URL; ?>dashboard/librarian-dashboard.php" class="logo">LibraryOS</a>
                <span style="font-size: 0.72rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2rem; display: block;">Librarian Portal</span>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>index.php" class="logo">LibraryOS</a>
            <?php endif; ?>

            <nav>
                <?php if (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 3): ?>
                <!-- ===== READER PORTAL MENU ===== -->
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>dashboard/reader-dashboard.php" class="<?php echo ($current_page == 'reader-dashboard.php' || $current_page == 'dashboard.php') ? 'active' : ''; ?>">My Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reader/book.php" class="<?php echo ($current_page == 'book.php') ? 'active' : ''; ?>">Browse Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reader/my_loans.php" class="<?php echo ($current_page == 'my_loans.php') ? 'active' : ''; ?>">My Loans</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reader/profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">My Profile</a></li>
                </ul>

                <?php elseif (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                <!-- ===== ADMIN MENU ===== -->
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>dashboard/admin-dashboard.php" class="<?php echo in_array($current_page, ['admin-dashboard.php', 'dashboard.php']) ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>book/books.php" class="<?php echo ($current_page == 'books.php' || strpos($current_page, 'book') !== false) ? 'active' : ''; ?>">
                        Books <span class="nav-view-badge">VIEW-ONLY</span>
                    </a></li>
                    <li><a href="<?php echo BASE_URL; ?>loan/loans.php" class="<?php echo ($current_page == 'loans.php' || $current_page == 'loan_detail.php') ? 'active' : ''; ?>">
                        Loans <span class="nav-view-badge">VIEW-ONLY</span>
                    </a></li>
                    <li><a href="<?php echo BASE_URL; ?>reader_management/readers.php" class="<?php echo ($current_page == 'readers.php') ? 'active' : ''; ?>">
                        Readers <span class="nav-view-badge">VIEW-ONLY</span>
                    </a></li>
                    <li><a href="<?php echo BASE_URL; ?>request_management/requests.php" class="<?php echo ($current_page == 'requests.php') ? 'active' : ''; ?>">
                        System Requests
                        <?php if (isset($pending_count) && $pending_count > 0): ?>
                            <span style="background:var(--danger);color:white;border-radius:10px;padding:2px 6px;font-size:0.7rem;margin-left:4px;"><?php echo $pending_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                    <li><a href="<?php echo BASE_URL; ?>account/accounts.php" class="<?php echo ($current_page == 'accounts.php' || strpos($current_page, 'account') !== false) ? 'active' : ''; ?>">Account Management</a></li>
                    <li><a href="<?php echo BASE_URL; ?>system/settings.php" class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">Settings</a></li>
                </ul>

                <?php elseif (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2): ?>
                <!-- ===== LIBRARIAN MENU ===== -->
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>dashboard/librarian-dashboard.php" class="<?php echo in_array($current_page, ['librarian-dashboard.php', 'dashboard.php']) ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>book/books.php" class="<?php echo ($current_page == 'books.php' || strpos($current_page, 'book') !== false) ? 'active' : ''; ?>">Books Management</a></li>
                    <li><a href="<?php echo BASE_URL; ?>reader_management/readers.php" class="<?php echo ($current_page == 'readers.php') ? 'active' : ''; ?>">Readers Management</a></li>
                    <li><a href="<?php echo BASE_URL; ?>loan/loans.php" class="<?php echo ($current_page == 'loans.php' || strpos($current_page, 'borrow') !== false || strpos($current_page, 'return') !== false) ? 'active' : ''; ?>">Loans Management</a></li>
                    <li><a href="<?php echo BASE_URL; ?>request_management/requests.php" class="<?php echo ($current_page == 'requests.php') ? 'active' : ''; ?>">
                        Request Management
                        <?php if (isset($pending_count) && $pending_count > 0): ?>
                            <span style="background:var(--danger);color:white;border-radius:10px;padding:2px 6px;font-size:0.7rem;margin-left:4px;"><?php echo $pending_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                </ul>

                <?php else: ?>
                <!-- ===== GUEST SLOGAN ===== -->
                <div class="guest-slogan-block">
                    <div class="guest-slogan-icon">📖</div>
                    <h2 class="guest-slogan-title">Your next great<br>read awaits.</h2>
                    <p class="guest-slogan-sub">Explore thousands of books — for free, no account needed.</p>
                    <div class="guest-slogan-divider"></div>
                    <ul class="guest-feature-list">
                        <li><span class="guest-feat-icon">✦</span> Browse our full catalog</li>
                        <li><span class="guest-feat-icon">✦</span> Borrow books with an account</li>
                        <li><span class="guest-feat-icon">✦</span> Track your reading history</li>
                        <li><span class="guest-feat-icon">✦</span> Free registration, always</li>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="nav-right">
                    <?php if (isset($_SESSION['account_id'])): ?>
                        <div class="user-info" id="userAccountBtn">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                            </div>
                            <div class="user-details">
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="user-role"><?php echo htmlspecialchars($_SESSION['role_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div id="accountDropdown">
                                <?php if ($_SESSION['role_id'] == 3): ?>
                                    <a href="<?php echo BASE_URL; ?>reader/profile.php">My Profile</a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>account/profile.php">My Profile</a>
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
        document.addEventListener('DOMContentLoaded', function() {
            const userAccountBtn = document.getElementById('userAccountBtn');
            const accountDropdown = document.getElementById('accountDropdown');
            if (userAccountBtn && accountDropdown) {
                userAccountBtn.addEventListener('click', (event) => {
                    event.stopPropagation();
                    accountDropdown.style.display = accountDropdown.style.display === 'block' ? 'none' : 'block';
                });
                document.addEventListener('click', () => {
                    accountDropdown.style.display = 'none';
                });
            }
        });
    </script>

    <div class="container">
<?php
require_once __DIR__ . '/alerts.php';
renderFlashAlert();
?>
<?php
include_once __DIR__ . '/../Notification/views/notif_templates.php';
?>
