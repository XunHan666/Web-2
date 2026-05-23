<?php
/**
 * Dashboard — Controller
 * Accessible to: Role 1 (Admin), Role 2 (Librarian)
 *
 * Responsibilities:
 *   - Auth guard
 *   - Fetch all data needed by the view
 *   - Pass variables to views/dashboard_display.php
 */

require_once '../env/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// ── Auth Guard ────────────────────────────────────────────────────
$role_id = (int)($_SESSION['role_id'] ?? 0);
if (!in_array($role_id, [1, 2])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$page_title = 'Dashboard';
include '../inc/header.php';

// ── Common Stats ──────────────────────────────────────────────────
$total_inventory_count     = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM book_copies"))[0];

$ready_for_loan_count      = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM book_copies WHERE status = 'available'"))[0];

$total_registered_readers  = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM readers"))[0];

$pending_circulation_count = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM loans WHERE status IN ('ongoing','partial')"))[0];

// ── Role-Specific Stats ───────────────────────────────────────────
if ($role_id === 1) {
    $admin_id           = (int)$_SESSION['account_id'];
    $total_accounts     = (int)mysqli_fetch_array(mysqli_query($db_connect,
        "SELECT COUNT(*) FROM accounts WHERE id != $admin_id"))[0];
    $pending_requests   = (int)mysqli_fetch_array(mysqli_query($db_connect,
        "SELECT COUNT(*) FROM requests WHERE status = 'pending'"))[0];
} else {
    $total_accounts   = null; // not used for librarian
    $pending_requests = (int)mysqli_fetch_array(mysqli_query($db_connect,
        "SELECT COUNT(*) FROM requests WHERE status = 'pending'
         AND type IN ('borrow_book','return_book')"))[0];
}

// ── Greeting ──────────────────────────────────────────────────────
$staff_display_name = htmlspecialchars($_SESSION['full_name'] ?? 'Staff', ENT_QUOTES, 'UTF-8');
$staff_role_label   = htmlspecialchars($_SESSION['role_name'] ?? '', ENT_QUOTES, 'UTF-8');
$hour               = (int)date('H');
$greeting_word      = $hour < 12 ? 'Morning' : ($hour < 18 ? 'Afternoon' : 'Evening');
$time_of_day_greeting = "Good {$greeting_word}, {$staff_display_name}!";

// ── Load View ─────────────────────────────────────────────────────
include 'views/dashboard_display.php';
include '../inc/footer.php';
