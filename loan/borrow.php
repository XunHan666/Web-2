<?php
/**
 * New Loan Transaction - Controller
 */
require_once '../env/config.php';
include '../inc/header.php';

// Data Preparation
$readers_res = mysqli_query($db_connect, "SELECT * FROM readers WHERE status = 'active' ORDER BY name");
$available_books_res = mysqli_query($db_connect, "SELECT b.id as book_id, b.title, COUNT(bc.id) as copy_count FROM books b JOIN book_copies bc ON b.id = bc.book_id WHERE bc.status = 'available' GROUP BY b.id ORDER BY b.title");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reader_id = (int)$_POST['reader_id'];
    $borrow_date = $_POST['borrow_date'];
    $due_date = date('Y-m-d', strtotime($borrow_date . ' + 5 days'));
    $selected_book_ids = isset($_POST['book_ids']) ? $_POST['book_ids'] : [];

    if (empty($reader_id) || empty($selected_book_ids)) {
        showAlert("Please select reader and books.", "error");
    } else {
        mysqli_begin_transaction($db_connect);
        try {
            mysqli_query($db_connect, "INSERT INTO loans (reader_id, borrow_date, due_date, status) VALUES ($reader_id, '$borrow_date', '$due_date', 'ongoing')");
            $new_loan_id = mysqli_insert_id($db_connect);

            foreach ($selected_book_ids as $book_id) {
                $book_id = (int)$book_id;
                $copy_res = mysqli_query($db_connect, "SELECT id FROM book_copies WHERE book_id = $book_id AND status = 'available' LIMIT 1 FOR UPDATE");
                if ($copy_data = mysqli_fetch_assoc($copy_res)) {
                    $copy_id = $copy_data['id'];
                    mysqli_query($db_connect, "INSERT INTO loan_details (loan_id, book_copy_id, status) VALUES ($new_loan_id, $copy_id, 'borrowed')");
                    mysqli_query($db_connect, "UPDATE book_copies SET status = 'borrowed' WHERE id = $copy_id");
                } else {
                    throw new Exception("Book title no longer available.");
                }
            }
            mysqli_commit($db_connect);
            showAlert("Loan created.");
            echo "<script>setTimeout(() => { window.location.href = 'loans.php'; }, 1500);</script>";
        } catch (Exception $e) {
            mysqli_rollback($db_connect);
            showAlert("Error: " . $e->getMessage(), "error");
        }
    }
}

include 'views/borrow_editor.php';
include '../inc/footer.php';
