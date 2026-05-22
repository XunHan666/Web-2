<?php
require_once '../env/config.php';

$book_id = (int)($_GET['id'] ?? 0);

if ($book_id > 0) {
    // ---- BOOK DETAIL LOGIC ----
    $page_title = 'Book Detail';
    
    $detail_query = "
        SELECT b.*, p.name as publisher_name,
               (SELECT GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') FROM book_author ba JOIN authors a ON ba.author_id = a.id WHERE ba.book_id = b.id) as author_names,
               (SELECT GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') FROM book_category bc JOIN categories c ON bc.category_id = c.id WHERE bc.book_id = b.id) as category_names,
               (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) as total_copies,
               (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id AND bc.status = 'available') as available_copies
        FROM books b LEFT JOIN publishers p ON b.publisher_id = p.id
        WHERE b.id = $book_id
    ";
    $book_data = mysqli_fetch_assoc(mysqli_query($db_connect, $detail_query));
    if (!$book_data) { header('Location: book.php'); exit(); }

    $display_image = (!empty($book_data['cover_image']) && file_exists('../' . $book_data['cover_image']))
        ? '../' . $book_data['cover_image']
        : 'https://placehold.co/400x600/1e4646/white?text=' . urlencode($book_data['title']);

    $is_reader_view = true;
    
    include '../inc/header.php';
    include '../book/views/book_details_display.php';
    include '../inc/footer.php';

} else {
    // ---- BROWSE BOOKS LOGIC ----
    $page_title = 'Browse Books';
    include '../inc/header.php';

    $search   = trim($_GET['search']   ?? '');
    $cat_id   = (int)($_GET['category'] ?? 0);
    $cats_res = mysqli_query($db_connect, "SELECT * FROM categories ORDER BY name");

    $where = []; $params = []; $types = '';
    if ($search)  { $where[] = "(b.title LIKE ? OR a.name LIKE ?)"; $p = "%$search%"; $params = array_merge($params, [$p,$p]); $types .= 'ss'; }
    if ($cat_id)  { $where[] = "EXISTS (SELECT 1 FROM book_category bc2 WHERE bc2.book_id=b.id AND bc2.category_id=?)"; $params[] = $cat_id; $types .= 'i'; }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = mysqli_prepare($db_connect, "
        SELECT DISTINCT b.id, b.title, b.cover_image,
               GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') authors,
               (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id=b.id AND bc.status='available') available_count
        FROM books b
        LEFT JOIN book_author ba ON ba.book_id=b.id
        LEFT JOIN authors a ON ba.author_id=a.id
        LEFT JOIN book_category bcat ON bcat.book_id=b.id
        $where_sql
        GROUP BY b.id ORDER BY b.title
    ");
    if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $books_res = mysqli_stmt_get_result($stmt);

    include 'views/books_display.php';
    include '../inc/footer.php';
}
?>
