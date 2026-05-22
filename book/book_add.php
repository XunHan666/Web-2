<?php
/**
 * Book Registration/Edit - Controller
 */
require_once '../env/config.php';
include '../inc/header.php';

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$status_message = '';
$status_type = '';

// Fetch options for datalists
$publishers_list = mysqli_query($db_connect, "SELECT * FROM publishers ORDER BY name");
$authors_list = mysqli_query($db_connect, "SELECT * FROM authors ORDER BY name");
$categories_list = mysqli_query($db_connect, "SELECT * FROM categories ORDER BY name");

// Default state
$book_data = ['title' => '', 'publisher_name' => '', 'pub_year' => date('Y'), 'description' => '', 'author_name' => '', 'category_name' => '', 'quantity' => 1, 'cover_image' => ''];

if ($book_id) {
    $fetch_sql = "
        SELECT b.*, p.name as publisher_name, 
               (SELECT a.name FROM book_author ba JOIN authors a ON ba.author_id = a.id WHERE ba.book_id = b.id LIMIT 1) as author_name, 
               (SELECT c.name FROM book_category bc JOIN categories c ON bc.category_id = c.id WHERE bc.book_id = b.id LIMIT 1) as category_name, 
               (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id) as quantity 
        FROM books b 
        LEFT JOIN publishers p ON b.publisher_id = p.id 
        WHERE b.id = $book_id";
    $book_data = mysqli_fetch_assoc(mysqli_query($db_connect, $fetch_sql));
}

function getLookupId($db_connect, $table_name, $display_name) {
    $display_name = trim($display_name);
    if (empty($display_name)) return null;
    $safe_name = mysqli_real_escape_string($db_connect, $display_name);
    $res = mysqli_query($db_connect, "SELECT id FROM $table_name WHERE name = '$safe_name' LIMIT 1");
    if ($row = mysqli_fetch_assoc($res)) return $row['id'];
    mysqli_query($db_connect, "INSERT INTO $table_name (name) VALUES ('$safe_name')");
    return mysqli_insert_id($db_connect);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title_input = mysqli_real_escape_string($db_connect, $_POST['title']);
    $year_input = (int)$_POST['pub_year'];
    $desc_input = mysqli_real_escape_string($db_connect, $_POST['description']);
    $new_qty = max(1, (int)$_POST['quantity']);
    
    $author_id = getLookupId($db_connect, 'authors', $_POST['author_input']);
    $category_id = getLookupId($db_connect, 'categories', $_POST['category_input']);
    $publisher_id = getLookupId($db_connect, 'publishers', $_POST['publisher_input']);

    $cover_image_path = $book_id ? ($book_data['cover_image'] ?? null) : null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_info = pathinfo($_FILES['cover_image']['name']);
        $extension = strtolower($file_info['extension'] ?? '');
        
        if (in_array($extension, $allowed_extensions)) {
            $upload_dir = '../uploads/books/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $unique_filename = 'book_' . uniqid() . '.' . $extension;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_dir . $unique_filename)) {
                $cover_image_path = 'uploads/books/' . $unique_filename;
            }
        } else {
            showAlert("Invalid file type. Only images are allowed.", "error");
        }
    }

    mysqli_begin_transaction($db_connect);
    try {
        if ($book_id) {
            mysqli_query($db_connect, "UPDATE books SET title='$title_input', publisher_id=" . ($publisher_id ?: "NULL") . ", pub_year='$year_input', description='$desc_input', cover_image='$cover_image_path' WHERE id=$book_id");
            mysqli_query($db_connect, "DELETE FROM book_author WHERE book_id = $book_id");
            mysqli_query($db_connect, "INSERT INTO book_author (book_id, author_id) VALUES ($book_id, $author_id)");
            mysqli_query($db_connect, "DELETE FROM book_category WHERE book_id = $book_id");
            if ($category_id) mysqli_query($db_connect, "INSERT INTO book_category (book_id, category_id) VALUES ($book_id, $category_id)");
            
            // Sync Quantity: Only remove copies that are available and have no loan history
            $current_qty = (int)$book_data['quantity'];
            if ($new_qty > $current_qty) {
                for ($i = 0; $i < ($new_qty - $current_qty); $i++) {
                    $barcode = "BK-$book_id-" . uniqid();
                    mysqli_query($db_connect, "INSERT INTO book_copies (book_id, barcode, status) VALUES ($book_id, '$barcode', 'available')");
                }
            } elseif ($new_qty < $current_qty) {
                $abs_diff = $current_qty - $new_qty;
                // Find copies that are available AND have no entries in loan_details
                $safe_to_delete_res = mysqli_query($db_connect, "
                    SELECT bc.id FROM book_copies bc 
                    LEFT JOIN loan_details ld ON bc.id = ld.book_copy_id 
                    WHERE bc.book_id = $book_id 
                    AND bc.status = 'available' 
                    AND ld.id IS NULL 
                    LIMIT $abs_diff");
                
                while ($row = mysqli_fetch_assoc($safe_to_delete_res)) {
                    $cid = $row['id'];
                    mysqli_query($db_connect, "DELETE FROM book_copies WHERE id = $cid");
                }
            }
        } else {
            mysqli_query($db_connect, "INSERT INTO books (title, publisher_id, pub_year, description, cover_image) VALUES ('$title_input', " . ($publisher_id ?: "NULL") . ", '$year_input', '$desc_input', '$cover_image_path')");
            $book_id = mysqli_insert_id($db_connect);
            mysqli_query($db_connect, "INSERT INTO book_author (book_id, author_id) VALUES ($book_id, $author_id)");
            if ($category_id) mysqli_query($db_connect, "INSERT INTO book_category (book_id, category_id) VALUES ($book_id, $category_id)");
            for ($i = 0; $i < $new_qty; $i++) {
                $barcode = "BK-$book_id-" . str_pad($i + 1, 3, '0', STR_PAD_LEFT) . "-" . rand(100, 999);
                mysqli_query($db_connect, "INSERT INTO book_copies (book_id, barcode, status) VALUES ($book_id, '$barcode', 'available')");
            }
        }
        mysqli_commit($db_connect);
        showAlert("Book processed successfully.");
        echo "<script>setTimeout(() => { window.location.href = 'books.php'; }, 1500);</script>";
    } catch (Exception $e) {
        mysqli_rollback($db_connect);
        showAlert("Error: " . $e->getMessage(), "error");
    }
}

$image_to_show = "";
if (!empty($book_data['cover_image'])) {
    $image_to_show = "../" . $book_data['cover_image'];
} else if ($book_id) {
    // Standard matching for preview
    $clean_title = strtolower(trim($book_data['title']));
    if (file_exists("../img-web2/" . $clean_title . ".jpg")) $image_to_show = "../img-web2/" . $clean_title . ".jpg";
}

include 'views/book_editor.php';
include '../inc/footer.php';
