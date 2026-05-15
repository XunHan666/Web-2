<?php
/**
 * Loan Deletion Logic
 */
require_once '../env/config.php';
require_once '../Notification/Delete_notification.php';

$loan_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$loan_id) {
    header("Location: loans.php");
    exit();
}

// Security: Only allow deleting CLOSED loans
$check_query = "SELECT status FROM loans WHERE id = $loan_id";
$res = mysqli_query($db_connect, $check_query);
$loan = mysqli_fetch_assoc($res);

if (!$loan) {
    header("Location: loans.php");
    exit();
}

if ($loan['status'] !== 'closed') {
    // Should not happen via UI, but for safety
    echo "<script>alert('Error: Only closed loans can be deleted.'); window.location.href='loans.php';</script>";
    exit();
}

// Transactional deletion
mysqli_begin_transaction($db_connect);
try {
    // 1. Delete details first (Foreign Key constraint)
    mysqli_query($db_connect, "DELETE FROM loan_details WHERE loan_id = $loan_id");
    
    // 2. Delete the master loan record
    mysqli_query($db_connect, "DELETE FROM loans WHERE id = $loan_id");
    
    mysqli_commit($db_connect);
    header("Location: loans.php?delete_success=1");
} catch (Exception $e) {
    mysqli_rollback($db_connect);
    header("Location: loans.php?error=1");
}
