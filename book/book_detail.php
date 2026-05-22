<?php
/**
 * Book Detail - Controller (Fixed Image Logic)
 */
require_once '../env/config.php';
include '../inc/header.php';

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$book_id) {
    showAlert("No book ID provided.", "error");
    echo '<div style="padding:2rem;"><a href="books.php" class="btn btn-primary">Return to Index</a></div>';
    include '../inc/footer.php';
    exit;
}

$detail_query = "
    SELECT b.*, p.name as publisher_name,
           (SELECT GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') FROM book_author ba JOIN authors a ON ba.author_id = a.id WHERE ba.book_id = b.id) as author_names,
           (SELECT GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') FROM book_category bc JOIN categories c ON bc.category_id = c.id WHERE bc.book_id = b.id) as category_names,
           (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) as total_copies,
           ((SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) - 
            (SELECT COUNT(*) FROM loan_details ld JOIN book_copies bc2 ON ld.book_copy_id = bc2.id 
             WHERE bc2.book_id = b.id AND ld.status = 'borrowed')) as available_copies
    FROM books b
    LEFT JOIN publishers p ON b.publisher_id = p.id
    WHERE b.id = $book_id
";

$res = mysqli_query($db_connect, $detail_query);
$book_data = mysqli_fetch_assoc($res);

if (!$book_data) {
    showAlert("Book not found.", "error");
    echo '<div style="padding:2rem;"><a href="books.php" class="btn btn-primary">Return to Index</a></div>';
    include '../inc/footer.php';
    exit;
}

// Image Logic (Consistent with inventory)
$book_title = $book_data['title'];

if (!empty($book_data['cover_image']) && file_exists("../" . $book_data['cover_image'])) {
    $display_image = "../" . $book_data['cover_image'];
} else {
    $clean_title = strtolower(trim($book_title));
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
        "sapiens the birth of humankind.jpg",
        "Sapiens The Birth of Humankind.jpg"
    ];
    foreach ($filenames as $name) {
        if (file_exists("../img-web2/" . $name)) {
            $display_image = "../img-web2/" . $name;
            break;
        }
    }
}

include 'views/book_details_display.php';
include '../inc/footer.php';
