<?php
/**
 * Reader Dashboard — Controller
 * Accessible to: Role 3 (Reader)
 *
 * Responsibilities:
 *   - Auth guard (handled by header.php, but guard here too for safety)
 *   - Fetch reader-specific stats
 *   - Pass variables to views/reader_dashboard_display.php
 */

require_once '../env/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Guard: reader only
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 3) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$page_title = 'Dashboard';
include '../inc/header.php';

$rid = (int)$reader['id'];

// ── Stats ──────────────────────────────────────────────────────────
$total_loans = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM loans WHERE reader_id = $rid"))[0];

$active_books = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM loan_details ld
     JOIN loans l ON ld.loan_id = l.id
     WHERE l.reader_id = $rid AND ld.status = 'borrowed'"))[0];

$overdue_books = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM loan_details ld
     JOIN loans l ON ld.loan_id = l.id
     WHERE l.reader_id = $rid AND ld.status = 'borrowed' AND l.due_date < CURDATE()"))[0];

$pending_requests = (int)mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM loans WHERE reader_id = $rid AND status = 'pending'"))[0];

// ── Active Loan Items ──────────────────────────────────────────────
$loans_res = mysqli_query($db_connect,
    "SELECT b.title, b.id book_id, l.borrow_date, l.due_date,
            DATEDIFF(l.due_date, CURDATE()) days_left,
            ld.status AS item_status
     FROM loan_details ld
     JOIN loans l        ON ld.loan_id      = l.id
     JOIN book_copies bc ON ld.book_copy_id = bc.id
     JOIN books b        ON bc.book_id      = b.id
     WHERE l.reader_id = $rid AND ld.status IN ('borrowed','pending')
     ORDER BY l.status DESC, l.due_date ASC"
);

// ── Load View ──────────────────────────────────────────────────────
include 'views/reader_dashboard_display.php';
include '../inc/footer.php';
