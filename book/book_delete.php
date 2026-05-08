<?php
/**
 * Book Deletion Handler
 * Permanently removes a book and its associated copies/history.
 */
require_once '../env/config.php';
include '../inc/header.php'; // For showAlert helper

if (isset($_GET['id'])) {
    $book_id = (int)$_GET['id'];
    
    // Step 1: Optimized check for active loans
    // We only need to know if ANY copy of this book is currently 'borrowed'
    $check_sql = "
        SELECT 1 
        FROM loan_details ld
        JOIN book_copies bc ON ld.book_copy_id = bc.id
        WHERE bc.book_id = ? AND ld.status = 'borrowed'
        LIMIT 1
    ";
    
    $check_stmt = mysqli_prepare($db_connect, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $book_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        // If borrowed, we need to find WHO is borrowing it for a better error message
        $borrower_sql = "
            SELECT DISTINCT r.name 
            FROM loan_details ld
            JOIN loans l ON ld.loan_id = l.id
            JOIN book_copies bc ON ld.book_copy_id = bc.id
            JOIN readers r ON l.reader_id = r.id
            WHERE bc.book_id = ? AND ld.status = 'borrowed'
        ";
        $borrower_stmt = mysqli_prepare($db_connect, $borrower_sql);
        mysqli_stmt_bind_param($borrower_stmt, "i", $book_id);
        mysqli_stmt_execute($borrower_stmt);
        $borrower_res = mysqli_stmt_get_result($borrower_stmt);
        
        $borrowers = [];
        while ($row = mysqli_fetch_assoc($borrower_res)) {
            $borrowers[] = $row['name'];
        }
        
        showAlert("Cannot delete! This book is currently being borrowed by: " . htmlspecialchars(implode(', ', $borrowers)), "error");
        echo "<script>setTimeout(() => { window.location.href = 'books.php'; }, 2000);</script>";
        exit();
    } else {
        // Step 2: Safe to delete
        mysqli_begin_transaction($db_connect);
        try {
            // Cleanup loan history (Satisfy foreign key restrictions on book_copies)
            // Note: ON DELETE CASCADE is NOT on book_copy_id in loan_details in database.sql
            mysqli_query($db_connect, "DELETE FROM loan_details WHERE book_copy_id IN (SELECT id FROM book_copies WHERE book_id = $book_id)");
            
            // Delete the book (This will cascade to book_copies, book_author, book_category due to ON DELETE CASCADE)
            $delete_stmt = mysqli_prepare($db_connect, "DELETE FROM books WHERE id = ?");
            mysqli_stmt_bind_param($delete_stmt, "i", $book_id);
            mysqli_stmt_execute($delete_stmt);
            
            mysqli_commit($db_connect);
            showAlert("Book and its entire history have been permanently removed.");
            echo "<script>setTimeout(() => { window.location.href = 'books.php'; }, 2000);</script>";
        } catch (Exception $e) {
            mysqli_rollback($db_connect);
            showAlert("System Error: Unable to complete deletion. " . $e->getMessage(), "error");
            echo "<script>setTimeout(() => { window.location.href = 'books.php'; }, 3000);</script>";
        }
    }
} else {
    header("Location: books.php");
    exit();
}

include '../inc/footer.php';
?>
