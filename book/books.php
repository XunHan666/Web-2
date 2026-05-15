<?php
/**
 * Book Inventory Directory - Controller (Fixed Header Issue & Image Logic)
 */
require_once '../env/config.php';

// 1. Xử lý logic TRƯỚC khi gửi bất kỳ nội dung HTML nào (Để tránh lỗi Headers already sent)
if (isset($_GET['update_copies']) && isset($_GET['delta'])) {
    $book_id = (int)$_GET['update_copies'];
    $delta = (int)$_GET['delta'];

    if ($delta > 0) {
        $barcode = "BK-" . $book_id . "-" . uniqid();
        mysqli_query($db_connect, "INSERT INTO book_copies (book_id, barcode, status) VALUES ($book_id, '$barcode', 'available')");
    } elseif ($delta < 0) {
        $avail_res = mysqli_query($db_connect, "SELECT id FROM book_copies WHERE book_id = $book_id AND status = 'available' LIMIT 1");
        if ($row = mysqli_fetch_assoc($avail_res)) {
            $cid = $row['id'];
            mysqli_query($db_connect, "DELETE FROM loan_details WHERE book_copy_id = $cid");
            mysqli_query($db_connect, "DELETE FROM book_copies WHERE id = $cid");
        }
    }
    header("Location: books.php" . (!empty($_GET['search']) ? "?search=".urlencode($_GET['search']) : ""));
    exit();
}

// 2. Sau khi xử lý xong logic chuyển hướng mới include Header
include '../inc/header.php';
require_once '../Notification/Delete_notification.php';

$search_term = isset($_GET['search']) ? $_GET['search'] : '';

/**
 * Global Statistics
 */
$total_copies_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM book_copies");
$total_copies_count = mysqli_fetch_array($total_copies_res)[0];

$borrowed_copies_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM loan_details WHERE status = 'borrowed'");
$borrowed_copies_count = mysqli_fetch_array($borrowed_copies_res)[0];

$available_copies_count = $total_copies_count - $borrowed_copies_count;

/**
 * Main Data Acquisition with Image Logic
 */
$search_pattern = "%$search_term%";
$books_query = "
    SELECT b.*, 
           (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) as quantity,
           ((SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) - 
            (SELECT COUNT(*) FROM loan_details ld JOIN book_copies bc2 ON ld.book_copy_id = bc2.id 
             WHERE bc2.book_id = b.id AND ld.status = 'borrowed')) as available_count,
           (SELECT GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') FROM authors a JOIN book_author ba ON a.id = ba.author_id WHERE ba.book_id = b.id) as author_names,
           (SELECT GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') FROM categories c JOIN book_category bc ON c.id = bc.category_id WHERE bc.book_id = b.id) as category_names
    FROM books b 
    LEFT JOIN book_author ba ON b.id = ba.book_id
    LEFT JOIN authors a ON ba.author_id = a.id
    LEFT JOIN book_category bc ON b.id = bc.book_id
    LEFT JOIN categories c ON bc.category_id = c.id
    WHERE b.title LIKE ? OR a.name LIKE ? OR c.name LIKE ?
    GROUP BY b.id
    ORDER BY b.id DESC
";

$stmt = mysqli_prepare($db_connect, $books_query);
mysqli_stmt_bind_param($stmt, "sss", $search_pattern, $search_pattern, $search_pattern);
mysqli_stmt_execute($stmt);
$books_result = mysqli_stmt_get_result($stmt);

// Chuyển kết quả sang mảng để xử lý logic ảnh bìa thông minh
$books_list = [];
while ($book = mysqli_fetch_assoc($books_result)) {
    $title = $book['title'];
    $display_image = "https://placehold.co/100x150/007bff/white?text=" . urlencode(substr($title, 0, 20));

    if (!empty($book['cover_image']) && file_exists("../" . $book['cover_image'])) {
        $display_image = "../" . $book['cover_image'];
    } else {
        $clean_title = strtolower(trim($title));
        $title_no_colon = str_replace(':', '', $clean_title);
        $title_parts = explode(':', $clean_title);
        $short_title = trim($title_parts[0]);

        $filenames = [
            $clean_title . ".jpg",
            str_replace(' ', '_', $clean_title) . ".jpg",
            $title_no_colon . ".jpg",
            str_replace(' ', '_', $title_no_colon) . ".jpg",
            $short_title . ".jpg",
            str_replace(' ', '_', $short_title) . ".jpg",
            "sapiens the birth of humankind.jpg", // Hardcoded fallback for Sapiens specifically
            "Sapiens The Birth of Humankind.jpg"
        ];
        foreach ($filenames as $fname) {
            if (file_exists("../img-web2/" . $fname)) {
                $display_image = "../img-web2/" . $fname;
                break;
            }
        }
    }
    $book['display_image'] = $display_image;
    $books_list[] = $book;
}

include 'views/inventory_display.php';
include '../inc/footer.php';
