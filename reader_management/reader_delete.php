<?php
/**
 * Reader Profile Deletion Handler
 * Permanently removes a reader and their entire loan history.
 */
require_once '../env/config.php';
require_once '../inc/role_guard.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_librarian_circulation();
include '../inc/header.php';

if (isset($_GET['id'])) {
    $reader_id = (int)$_GET['id'];
    
    // Step 1: Safety Check - Ensure reader has no active (borrowed) books
    $check_loan_sql = "
        SELECT 1 
        FROM loans l 
        JOIN loan_details ld ON l.id = ld.loan_id 
        WHERE l.reader_id = ? AND ld.status = 'borrowed' 
        LIMIT 1
    ";
    $check_stmt = mysqli_prepare($db_connect, $check_loan_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $reader_id);
    mysqli_stmt_execute($check_stmt);
    $active_loans_res = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($active_loans_res) > 0) {
        showAlert("Cannot delete! This reader currently has unreturned books. Please process returns first.", "error");
    } else {
        mysqli_begin_transaction($db_connect);
        try {
            // Step 2: Cleanup loan history
            // Since loan_details has ON DELETE CASCADE from loans, we just need to delete loans
            $delete_loans_stmt = mysqli_prepare($db_connect, "DELETE FROM loans WHERE reader_id = ?");
            mysqli_stmt_bind_param($delete_loans_stmt, "i", $reader_id);
            mysqli_stmt_execute($delete_loans_stmt);
            
            // Step 3: Delete the reader profile
            $delete_reader_stmt = mysqli_prepare($db_connect, "DELETE FROM readers WHERE id = ?");
            mysqli_stmt_bind_param($delete_reader_stmt, "i", $reader_id);
            mysqli_stmt_execute($delete_reader_stmt);
            
            mysqli_commit($db_connect);
            showAlert("Reader profile and historical data deleted successfully.");
        } catch (Exception $e) {
            mysqli_rollback($db_connect);
            showAlert("Error deleting reader: " . $e->getMessage(), "error");
        }
    }
    echo "<script>setTimeout(() => { window.location.href = 'readers.php'; }, 2000);</script>";
} else {
    header("Location: readers.php");
    exit();
}

include '../inc/footer.php';
?>
