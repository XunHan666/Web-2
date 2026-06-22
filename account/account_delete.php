<?php
/**
 * User Deletion Handler
 */
require_once '../env/config.php';
include '../inc/header.php';

// Security: Only Admins can delete users
if ($_SESSION['role_id'] != 1) {
    showAlert("Admin access required.", "error");
    echo "<script>setTimeout(() => { window.location.href = '../index.php'; }, 1500);</script>";
    exit();
}

if (isset($_GET['id'])) {
    $target_id = (int)$_GET['id'];
    
    // Prevent self-deletion
    if ($target_id === (int)$_SESSION['account_id']) {
        showAlert("You cannot delete your own account.", "error");
        echo "<script>setTimeout(() => { window.location.href = 'accounts.php'; }, 2000);</script>";
        exit();
    }
    
    $delete_stmt = mysqli_prepare($db_connect, "DELETE FROM accounts WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $target_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        showAlert("User account has been permanently removed.");
    } else {
        showAlert("Error deleting user: " . mysqli_error($db_connect), "error");
    }
    echo "<script>setTimeout(() => { window.location.href = 'accounts.php'; }, 2000);</script>";
} else {
    header("Location: accounts.php");
    exit();
}

include '../inc/footer.php';
?>
