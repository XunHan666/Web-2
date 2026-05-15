<?php
/**
 * Dashboard - Controller
 */
require_once 'env/config.php';
include 'inc/header.php';

// KPIs
$total_inventory_count = mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM book_copies"))[0];
$ready_for_loan_count = mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM book_copies WHERE status = 'available'"))[0];
$total_registered_readers = mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM readers"))[0];
$pending_circulation_count = mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM loans WHERE status IN ('ongoing', 'partial')"))[0];


// Personalization Context
$staff_display_name = $_SESSION['full_name'] ?? 'Guest';
$staff_role_label = $_SESSION['role_name'] ?? 'Staff';
$time_of_day_greeting = "Welcome, " . $staff_display_name . "!";
$role_context_banner = "Operational Role: " . ucfirst($staff_role_label);

include 'views/dashboard_display.php';
include 'inc/footer.php';
?>
