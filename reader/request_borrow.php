<?php
// POST-only handler: không có HTML, tự guard inline
require_once '../env/config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) { header('Location: ../authen/login.php'); exit(); }
$stmt = mysqli_prepare($db_connect, "SELECT * FROM readers WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$reader = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$reader) { header('Location: ../authen/login.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: book.php'); exit(); }


$book_id   = (int)($_POST['book_id'] ?? 0);
$reader_id = $reader['id'];

if (!$book_id) {
    header('Location: book.php');
    exit();
}

// --- Business Rules Validation ---

// 1. Sách còn available không?
$copy_res = mysqli_query($db_connect,
    "SELECT id FROM book_copies WHERE book_id = $book_id AND status = 'available' LIMIT 1"
);
if (!$copy_res || mysqli_num_rows($copy_res) == 0) {
    header('Location: book.php?id=' . $book_id . '&error=not_available');
    exit();
}

// 2. Reader đang mượn sách này rồi chưa?
$already = mysqli_fetch_array(mysqli_query($db_connect, "
    SELECT COUNT(*) FROM loan_details ld
    JOIN loans l ON ld.loan_id = l.id
    JOIN book_copies bc ON ld.book_copy_id = bc.id
    WHERE l.reader_id = $reader_id AND bc.book_id = $book_id AND ld.status = 'borrowed'
"))[0];

if ($already > 0) {
    header('Location: book.php?id=' . $book_id . '&error=already_borrowed');
    exit();
}

// 3. Reader đang mượn quá 5 quyển chưa?
$total_active = mysqli_fetch_array(mysqli_query($db_connect, "
    SELECT COUNT(*) FROM loan_details ld
    JOIN loans l ON ld.loan_id = l.id
    WHERE l.reader_id = $reader_id AND ld.status = 'borrowed'
"))[0];

if ($total_active >= 5) {
    header('Location: book.php?id=' . $book_id . '&error=limit_reached');
    exit();
}

// --- Create Loan Transaction ---
$copy_data  = mysqli_fetch_assoc($copy_res);
$copy_id    = $copy_data['id'];
$borrow_date = date('Y-m-d');
$due_date    = date('Y-m-d', strtotime('+5 days'));

mysqli_begin_transaction($db_connect);
try {
    // Tạo loan (trạng thái pending)
    mysqli_query($db_connect, "
        INSERT INTO loans (reader_id, borrow_date, due_date, status)
        VALUES ($reader_id, NULL, '$due_date', 'pending')
    ");
    $loan_id = mysqli_insert_id($db_connect);

    // Tạo loan_detail (trạng thái pending)
    mysqli_query($db_connect, "
        INSERT INTO loan_details (loan_id, book_copy_id, status)
        VALUES ($loan_id, $copy_id, 'pending')
    ");

    // Cập nhật trạng thái bản sao sách thành reserved
    mysqli_query($db_connect, "UPDATE book_copies SET status = 'reserved' WHERE id = $copy_id");

    mysqli_commit($db_connect);

    // Redirect về dashboard với thông báo thành công
    header('Location: dashboard.php?success=pending');
    exit();

} catch (Exception $e) {
    mysqli_rollback($db_connect);
    header('Location: book.php?id=' . $book_id . '&error=server_error');
    exit();
}
