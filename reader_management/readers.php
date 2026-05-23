<?php
/**
 * Reader Management - Controller
 */
require_once '../env/config.php';
require_once '../inc/role_guard.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_circulation_view();
include '../inc/header.php';


// Initialization
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

/**
 * Global Statistics
 */
$total_readers_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM readers");
$total_readers_count = mysqli_fetch_array($total_readers_res)[0];

$active_readers_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM readers WHERE status = 'active'");
$active_readers_count = mysqli_fetch_array($active_readers_res)[0];

$overdue_readers_res = mysqli_query($db_connect, "SELECT COUNT(DISTINCT reader_id) FROM loans WHERE status = 'overdue'");
$overdue_readers_count = mysqli_fetch_array($overdue_readers_res)[0] ?: 0;

/**
 * Main Data Acquisition
 */
$where_clauses = [];
$params = [];
$types = "";

if (!empty($search_term)) {
    $where_clauses[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $pattern = "%$search_term%";
    $params = array_merge($params, [$pattern, $pattern, $pattern]);
    $types .= "sss";
}

if ($status_filter !== 'all') {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Query joining with loans to get activity count
$readers_query = "
    SELECT r.*, 
           (SELECT COUNT(*) FROM loans l WHERE l.reader_id = r.id) as loan_count
    FROM readers r 
    $where_sql 
    ORDER BY r.id DESC
";

$stmt = mysqli_prepare($db_connect, $readers_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$readers_result = mysqli_stmt_get_result($stmt);

include 'views/readers_display.php';
include '../inc/footer.php';
