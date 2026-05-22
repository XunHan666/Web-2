<?php
require_once '../env/config.php';
$page_title = 'Pending Loan Requests';
include '../inc/header.php';

$requests_query = "
    SELECT l.*, r.name as reader_name, r.phone, r.email, b.title as book_title, bc.barcode
    FROM loans l 
    JOIN readers r ON l.reader_id = r.id
    JOIN loan_details ld ON ld.loan_id = l.id
    JOIN book_copies bc ON ld.book_copy_id = bc.id
    JOIN books b ON bc.book_id = b.id
    WHERE l.status = 'pending'
    ORDER BY l.created_at DESC
";
$requests_result = mysqli_query($db_connect, $requests_query);

include 'views/requests_display.php';
include '../inc/footer.php';
?>
