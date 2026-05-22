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
$staff_query = "
    SELECT u.*, r.name as role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    WHERE u.role_id = 2
    ORDER BY u.id ASC
";
$staff_result = mysqli_query($db_connect, $staff_query);

// Load View
include 'views/users_display.php';

include '../inc/footer.php';
