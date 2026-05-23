<?php
/**
 * Dashboard — Controller (Admin & Librarian)
 */
require_once '../env/config.php';
require_once '../inc/role_guard.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role_id = (int)($_SESSION['role_id'] ?? 0);
if (!in_array($role_id, [1, 2])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$page_title = 'Dashboard';
include '../inc/header.php';

// Inventory
$total_inventory_count = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM book_copies"))[0];
$ready_for_loan_count = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM book_copies WHERE status = 'available'"))[0];
$checked_out_count = $total_inventory_count - $ready_for_loan_count;

// People
$total_registered_readers = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM readers"))[0];

// Circulation
$active_loans_count = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM loans WHERE status IN ('ongoing','partial')"))[0];

// Requests (scope differs by role)
if ($role_id === 1) {
    $admin_id = (int)$_SESSION['account_id'];
    $total_accounts = (int)mysqli_fetch_array(mysqli_query($db_connect,
        "SELECT COUNT(*) FROM accounts WHERE id != $admin_id"))[0];
    $req_filter = pending_requests_filter_sql(1);
    $pending_requests = (int)mysqli_fetch_array(mysqli_query($db_connect,
        "SELECT COUNT(*) FROM requests WHERE status = 'pending' AND $req_filter"))[0];
} else {
    $total_accounts = null;
    $req_filter = pending_requests_filter_sql(2);
    $pending_requests = (int)mysqli_fetch_array(mysqli_query($db_connect,
        "SELECT COUNT(*) FROM requests WHERE status = 'pending' AND $req_filter"))[0];
}

$staff_display_name = htmlspecialchars($_SESSION['full_name'] ?? 'Staff', ENT_QUOTES, 'UTF-8');
$hour = (int)date('H');
$greeting_word = $hour < 12 ? 'Morning' : ($hour < 18 ? 'Afternoon' : 'Evening');
$time_of_day_greeting = "Good {$greeting_word}, {$staff_display_name}!";

include 'views/dashboard_display.php';
include '../inc/footer.php';
