<?php
/**
 * Circulation Management Log - Controller with Filtering
 */
require_once '../env/config.php';
include '../inc/header.php';

// Get Filter Parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$current_date = date('Y-m-d');

/**
 * Data Acquisition with Filter Logic
 */
$where_clause = "WHERE l.status NOT IN ('pending', 'rejected')";

if ($filter == 'pending') {
    $where_clause .= " AND l.status IN ('ongoing', 'partial')";
} elseif ($filter == 'overdue') {
    $where_clause .= " AND l.status IN ('ongoing', 'partial') AND l.due_date < '$current_date'";
} elseif ($filter == 'returned') {
    $where_clause .= " AND l.status = 'closed'";
}

$loans_query = "
    SELECT l.*, r.name as reader_name, r.phone 
    FROM loans l 
    JOIN readers r ON l.reader_id = r.id 
    $where_clause
    ORDER BY l.borrow_date DESC
";
$loans_result = mysqli_query($db_connect, $loans_query);

/**
 * Load View Layer
 */
include 'views/loan_display.php';

include '../inc/footer.php';
