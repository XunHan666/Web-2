<?php
/**
 * Root Entry Point — Smart Router
 *
 * Guest / Reader (role 3)  → Public book catalog (Browse Books)
 * Librarian (role 2)       → /librarian-dashboard.php
 * Admin (role 1)           → /admin-dashboard.php
 */
require_once 'env/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['role_id'])) {
    if ($_SESSION['role_id'] == 1) {
        header("Location: " . BASE_URL . "admin-dashboard.php");
        exit();
    } elseif ($_SESSION['role_id'] == 2) {
        header("Location: " . BASE_URL . "librarian-dashboard.php");
        exit();
    } elseif ($_SESSION['role_id'] == 3) {
        header("Location: " . BASE_URL . "reader/book.php");
        exit();
    }
}

// Guest → Public book catalog
header("Location: " . BASE_URL . "reader/book.php");
exit();
?>
