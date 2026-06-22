<?php
/**
 * User Deletion Handler
 */
require_once '../env/config.php';
require_once '../inc/alerts.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: Only Admins can delete users
if ($_SESSION['role_id'] != 1) {
    setFlashAlert("Admin access required.", "error");
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $target_id = (int)$_GET['id'];
    
    // Prevent self-deletion
    if ($target_id === (int)$_SESSION['account_id']) {
        setFlashAlert("You cannot delete your own account.", "error");
        header("Location: accounts.php");
        exit();
    }
    
    $delete_stmt = mysqli_prepare($db_connect, "DELETE FROM accounts WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $target_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        setFlashAlert("User account has been permanently removed.");
    } else {
        setFlashAlert("Error deleting user: " . mysqli_error($db_connect), "error");
    }
    header("Location: accounts.php");
    exit();
} else {
    header("Location: accounts.php");
    exit();
}
?>
