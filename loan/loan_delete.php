<?php
/**
 * Loan Deletion Logic
 */
require_once '../env/config.php';
require_once '../inc/role_guard.php';
require_once '../inc/alerts.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_librarian_circulation();

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
    setFlashAlert("Cannot delete! This transaction is still active. Please process the return first.", "error");
    header("Location: loans.php");
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
    setFlashAlert("Loan transaction deleted successfully.", "success");
    header("Location: loans.php");
    exit();
} catch (Exception $e) {
    mysqli_rollback($db_connect);
    setFlashAlert("Error: Failed to delete transaction.", "error");
    header("Location: loans.php");
    exit();
}
?>
