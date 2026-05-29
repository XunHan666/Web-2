<?php
/**
 * Unified Request Management - Controller
 */
require_once '../env/config.php';
require_once '../system/sys_rules.php';
require_once '../inc/role_guard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['account_id']) || !in_array($_SESSION['role_id'], [1, 2], true)) {
    header('Location: ' . BASE_URL . 'authen/login.php');
    exit();
}

$role_id = (int)$_SESSION['role_id'];
$page_title = ($role_id === 1) ? 'System Requests' : 'Request Management';
$requests_heading = $page_title;

include '../inc/header.php';

$where_clause = ($role_id === 1)
    ? "rq.type IN ('librarian_registration','password_reset')"
    : "rq.type IN ('borrow_book','return_book')";

$fine_rate = (int)get_setting('fine_per_day', 5000);

$requests_query = "
    SELECT rq.*,
           u.full_name as requester_name, u.username as requester_username,
           GROUP_CONCAT(DISTINCT b.title ORDER BY b.title SEPARATOR ', ') as book_title,
           GROUP_CONCAT(DISTINCT bc.barcode ORDER BY bc.barcode SEPARATOR ', ') as book_barcode,
           l.due_date as loan_due_date, l.borrow_date as loan_borrow_date
    FROM requests rq
    JOIN accounts u ON rq.account_id = u.id
    LEFT JOIN loans l ON rq.target_id = l.id AND rq.type IN ('borrow_book','return_book')
    LEFT JOIN loan_details ld ON ld.loan_id = l.id
    LEFT JOIN book_copies bc ON ld.book_copy_id = bc.id
    LEFT JOIN books b ON bc.book_id = b.id
    WHERE $where_clause
    GROUP BY rq.id, u.full_name, u.username, l.due_date, l.borrow_date
    ORDER BY FIELD(rq.status, 'pending', 'approved', 'rejected'), rq.created_at DESC
";
$requests_result = mysqli_query($db_connect, $requests_query);

include 'views/requests_display.php';
include '../inc/footer.php';
?>
