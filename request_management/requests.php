<?php
/**
 * Unified Request Management - Controller
 */
require_once '../env/config.php';
require_once '../system/sys_rules.php';
$page_title = 'Request Management';
include '../inc/header.php';

if (!isset($_SESSION['account_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../index.php');
    exit();
}

$role_id = (int)$_SESSION['role_id'];

// Admin sees all; Librarian sees borrow_book + return_book
$where_clause = ($role_id == 1)
    ? "1=1"
    : "rq.type IN ('borrow_book','return_book')";

$fine_rate = (int)get_setting('fine_per_day', 5000);

$requests_query = "
    SELECT rq.*,
           u.full_name as requester_name, u.username as requester_username,
           GROUP_CONCAT(DISTINCT b.title ORDER BY b.title SEPARATOR ', ') as book_title,
           bc.barcode as book_barcode,
           l.due_date as loan_due_date, l.borrow_date as loan_borrow_date
    FROM requests rq
    JOIN accounts u ON rq.account_id = u.id
    LEFT JOIN loans l ON rq.target_id = l.id AND rq.type IN ('borrow_book','return_book')
    LEFT JOIN loan_details ld ON ld.loan_id = l.id
    LEFT JOIN book_copies bc ON ld.book_copy_id = bc.id
    LEFT JOIN books b ON bc.book_id = b.id
    WHERE $where_clause
    GROUP BY rq.id
    ORDER BY FIELD(rq.status, 'pending', 'approved', 'rejected'), rq.created_at DESC
";
$requests_result = mysqli_query($db_connect, $requests_query);

include 'views/requests_display.php';
include '../inc/footer.php';
?>
