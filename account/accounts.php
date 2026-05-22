<?php
/**
 * Staff Management Directory - Controller
 */
require_once '../env/config.php';
include '../inc/header.php';


/**
 * Authorization: Admin only
 */
if ($_SESSION['role_id'] != 1) {
    showAlert("Administrative privileges required.", "error");
    echo "<script>window.location.href = '" . BASE_URL . "index.php';</script>";
    exit();
}

/**
 * Fetch Staff Accounts
 */
$role_filter = isset($_GET['role']) ? (int)$_GET['role'] : 0;
$where_clause = "u.role_id != 1";
if ($role_filter === 2 || $role_filter === 3) {
    $where_clause .= " AND u.role_id = $role_filter";
}

$staff_query = "
    SELECT u.*, r.name as role_name 
    FROM accounts u 
    JOIN roles r ON u.role_id = r.id 
    WHERE $where_clause
    ORDER BY u.id ASC
";
$staff_result = mysqli_query($db_connect, $staff_query);

// Load View
include 'views/accounts_display.php';

include '../inc/footer.php';
