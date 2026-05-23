<?php
/**
 * Return Processing - Controller
 */
require_once '../env/config.php';
require_once '../system/sys_rules.php';
require_once '../inc/role_guard.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_librarian_circulation();
include '../inc/header.php';

// Initialization
$loan_id = isset($_GET['loan_id']) ? (int)$_GET['loan_id'] : 0;

if (!$loan_id) {
    echo '<div style="max-width: 800px; margin: 3rem auto; text-align: center; background: white; padding: 3rem; border-radius: 12px;">
        <h2>Process Publication Return</h2>
        <form action="return.php" method="GET" style="max-width: 450px; margin: 2rem auto; display: flex; gap: 1rem;">
            <input type="number" name="loan_id" class="search-input" placeholder="Enter Loan ID" required style="flex: 1;">
            <button type="submit" class="btn btn-primary">Find Record</button>
        </form>
        <a href="loans.php" class="btn" style="background: #f1f5f9; color: #475569;">&larr; Back to Log</a>
    </div>';
    include '../inc/footer.php';
    exit;
}

/**
 * Configuration & Data Fetching
 */
$fine_rate = (int)get_setting('fine_per_day', 5000);
$current_date = date('Y-m-d');

$loan_query = "SELECT l.*, r.name as reader_name, r.phone FROM loans l JOIN readers r ON l.reader_id = r.id WHERE l.id = $loan_id";
$loan_res = mysqli_query($db_connect, $loan_query);
$loan_info = mysqli_fetch_assoc($loan_res);

if (!$loan_info) {
    showAlert("Transaction #$loan_id not found.", "error");
    echo '<div style="padding: 2rem; text-align: center;"><a href="loans.php" class="btn btn-primary">Return to Log</a></div>';
    include '../inc/footer.php';
    exit;
}

$days_late_count = 0;
if ($current_date > $loan_info['due_date']) {
    $days_late_count = (strtotime($current_date) - strtotime($loan_info['due_date'])) / (60 * 60 * 24);
}
$calculated_fine = $days_late_count * $fine_rate;

/**
 * Handle POST Submission
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_details'])) {
    $selected_detail_ids = $_POST['return_details']; 
    if (!empty($selected_detail_ids)) {
        mysqli_begin_transaction($db_connect);
        try {
            foreach ($selected_detail_ids as $detail_id) {
                $detail_id = (int)$detail_id;
                $copy_res = mysqli_query($db_connect, "SELECT book_copy_id FROM loan_details WHERE id = $detail_id");
                $copy_id = mysqli_fetch_assoc($copy_res)['book_copy_id'];

                mysqli_query($db_connect, "UPDATE loan_details SET return_date = '$current_date', fine_amount = $calculated_fine, status = 'returned' WHERE id = $detail_id");
                mysqli_query($db_connect, "UPDATE book_copies SET status = 'available' WHERE id = $copy_id");
            }

            // Sync Master Status
            $metrics = mysqli_fetch_assoc(mysqli_query($db_connect, "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned FROM loan_details WHERE loan_id = $loan_id"));
            $new_status = ($metrics['returned'] == $metrics['total']) ? 'closed' : 'partial';
            mysqli_query($db_connect, "UPDATE loans SET status = '$new_status' WHERE id = $loan_id");
            
            mysqli_commit($db_connect);
            showAlert(count($selected_detail_ids) . " items returned.");
            echo "<script>setTimeout(() => { window.location.href = 'return.php?loan_id=$loan_id'; }, 2000);</script>";
        } catch (Exception $e) {
            mysqli_rollback($db_connect);
            showAlert("Error: " . $e->getMessage(), "error");
        }
    }
}

/**
 * Fetch Items for View
 */
$items_query = "SELECT d.*, bc.barcode, b.title FROM loan_details d JOIN book_copies bc ON d.book_copy_id = bc.id JOIN books b ON bc.book_id = b.id WHERE d.loan_id = $loan_id";
$items_recordset = mysqli_query($db_connect, $items_query);

// Load View
include 'views/return_editor.php';

include '../inc/footer.php';
