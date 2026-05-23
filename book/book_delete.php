<?php
/**
 * Book Deletion Handler — process first, then redirect (no full page render).
 */
require_once '../env/config.php';
require_once '../inc/alerts.php';
require_once '../inc/role_guard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_librarian_circulation();

$redirect = BASE_URL . 'book/books.php';

if (!isset($_GET['id'])) {
    header('Location: ' . $redirect);
    exit();
}

$book_id = (int)$_GET['id'];

// Block if any copy is currently borrowed
$check_stmt = mysqli_prepare($db_connect, "
    SELECT DISTINCT r.name
    FROM loan_details ld
    INNER JOIN book_copies bc ON ld.book_copy_id = bc.id
    INNER JOIN loans l ON ld.loan_id = l.id
    INNER JOIN readers r ON l.reader_id = r.id
    WHERE bc.book_id = ? AND ld.status = 'borrowed'
    LIMIT 20
");
mysqli_stmt_bind_param($check_stmt, 'i', $book_id);
mysqli_stmt_execute($check_stmt);
$borrower_res = mysqli_stmt_get_result($check_stmt);

$borrowers = [];
while ($row = mysqli_fetch_assoc($borrower_res)) {
    $borrowers[] = $row['name'];
}

if (!empty($borrowers)) {
    setFlashAlert(
        'Cannot delete! This book is currently borrowed by: ' . implode(', ', $borrowers),
        'error'
    );
    header('Location: ' . $redirect);
    exit();
}

// Cover image path (delete file after DB row removed)
$cover_path = null;
$cover_stmt = mysqli_prepare($db_connect, 'SELECT cover_image FROM books WHERE id = ?');
mysqli_stmt_bind_param($cover_stmt, 'i', $book_id);
mysqli_stmt_execute($cover_stmt);
if ($cover_row = mysqli_fetch_assoc(mysqli_stmt_get_result($cover_stmt))) {
    $cover_path = $cover_row['cover_image'] ?? null;
}

mysqli_begin_transaction($db_connect);

try {
    // loan_details.book_copy_id has no CASCADE → remove history first
    $ld_stmt = mysqli_prepare($db_connect, "
        DELETE ld FROM loan_details ld
        INNER JOIN book_copies bc ON ld.book_copy_id = bc.id
        WHERE bc.book_id = ?
    ");
    mysqli_stmt_bind_param($ld_stmt, 'i', $book_id);
    mysqli_stmt_execute($ld_stmt);

    $del_stmt = mysqli_prepare($db_connect, 'DELETE FROM books WHERE id = ?');
    mysqli_stmt_bind_param($del_stmt, 'i', $book_id);
    mysqli_stmt_execute($del_stmt);

    if (mysqli_stmt_affected_rows($del_stmt) < 1) {
        throw new Exception('Book not found or already deleted.');
    }

    mysqli_commit($db_connect);

    if ($cover_path) {
        $file = dirname(__DIR__) . '/' . ltrim($cover_path, '/');
        if (is_file($file)) {
            @unlink($file);
        }
    }

    setFlashAlert('Book and its copies have been permanently removed.');
} catch (Exception $e) {
    mysqli_rollback($db_connect);
    setFlashAlert('Unable to delete book: ' . $e->getMessage(), 'error');
}

header('Location: ' . $redirect);
exit();
