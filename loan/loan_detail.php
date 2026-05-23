<?php
/**
 * Loan Transaction Details - Controller
 */
require_once '../env/config.php';
require_once '../inc/role_guard.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_circulation_view();
$circulation_readonly = circulation_is_readonly();
include '../inc/header.php';

$loan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$loan_id) {
    header("Location: loans.php");
    exit;
}

/**
 * Data Acquisition
 */
// 1. Fetch Master Info
$loan_query = "SELECT l.*, r.name as reader_name, r.phone FROM loans l JOIN readers r ON l.reader_id = r.id WHERE l.id = $loan_id";
$loan_res = mysqli_query($db_connect, $loan_query);
$loan_info = mysqli_fetch_assoc($loan_res);

if (!$loan_info) {
    showAlert("Transaction #$loan_id not found.", "error");
    echo "<script>window.location.href = 'loans.php';</script>";
    exit;
}

// 2. Fetch Detail Items
$items_query = "
    SELECT d.*, bc.barcode, b.title 
    FROM loan_details d 
    JOIN book_copies bc ON d.book_copy_id = bc.id 
    JOIN books b ON bc.book_id = b.id 
    WHERE d.loan_id = $loan_id
";
$items_recordset = mysqli_query($db_connect, $items_query);

/**
 * Load View Layer
 */
include 'views/loan_details_display.php';

include '../inc/footer.php';
