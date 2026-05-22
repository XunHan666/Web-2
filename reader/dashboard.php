<?php
require_once '../env/config.php';
$page_title = 'Dashboard';
include '../inc/header.php';

$rid = $reader['id'];

$total_loans  = mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM loans WHERE reader_id = $rid"))[0];
$active_books = mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM loan_details ld JOIN loans l ON ld.loan_id=l.id WHERE l.reader_id=$rid AND ld.status='borrowed'"))[0];
$overdue_books= mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM loan_details ld JOIN loans l ON ld.loan_id=l.id WHERE l.reader_id=$rid AND ld.status='borrowed' AND l.due_date<CURDATE()"))[0];
$pending_requests = mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM loans WHERE reader_id=$rid AND status='pending'"))[0];

$loans_res = mysqli_query($db_connect, "
    SELECT b.title, b.id book_id, l.borrow_date, l.due_date, DATEDIFF(l.due_date, CURDATE()) days_left, ld.status as item_status
    FROM loan_details ld
    JOIN loans l ON ld.loan_id=l.id
    JOIN book_copies bc ON ld.book_copy_id=bc.id
    JOIN books b ON bc.book_id=b.id
    WHERE l.reader_id=$rid AND ld.status IN ('borrowed', 'pending')
    ORDER BY l.status DESC, l.due_date ASC
");

include 'views/dashboard_display.php';
include '../inc/footer.php';
