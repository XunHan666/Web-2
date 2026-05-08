<?php
/**
 * Loan Transaction Deletion Handler
 * Removes a specific loan detail record from history.
 */
require_once '../env/config.php';
include '../inc/header.php';

if (isset($_GET['id'])) {
    $detail_record_id = (int)$_GET['id'];
    
    try {
        // Step 1: Check if the item is still active (borrowed)
        $check_sql = "SELECT status FROM loan_details WHERE id = ?";
        $check_stmt = mysqli_prepare($db_connect, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "i", $detail_record_id);
        mysqli_stmt_execute($check_stmt);
        $status_data = mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt));
        
        if ($status_data && $status_data['status'] == 'borrowed') {
            showAlert("Cannot delete an active loan record. The book must be returned first.", "error");
        } else {
            // Step 2: Delete the history record
            $delete_sql = "DELETE FROM loan_details WHERE id = ?";
            $delete_stmt = mysqli_prepare($db_connect, $delete_sql);
            mysqli_stmt_bind_param($delete_stmt, "i", $detail_record_id);
            mysqli_stmt_execute($delete_stmt);
            
            showAlert("Transaction history record deleted successfully.");
        }
    } catch(Exception $e) {
        showAlert("Error deleting record: " . $e->getMessage(), "error");
    }
    echo "<script>setTimeout(() => { window.location.href = 'loans.php'; }, 2000);</script>";
} else {
    header("Location: loans.php");
    exit();
}

include '../inc/footer.php';
?>
