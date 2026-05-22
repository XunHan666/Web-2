<?php
/**
 * Book Addition and Editing Module
 * Handles creating new books and updating existing ones, including image uploads and copy generation.
 */
require_once '../env/config.php';
include '../inc/header.php';

// Initialization
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$status_message = '';
$status_type = '';

// Fetch options for autocomplete dropdowns (datalists)
$publishers_list = mysqli_query($db_connect, "SELECT * FROM publishers ORDER BY name");
$authors_list = mysqli_query($db_connect, "SELECT * FROM authors ORDER BY name");
$categories_list = mysqli_query($db_connect, "SELECT * FROM categories ORDER BY name");

// Default empty book data structure
$book_data = [
    'title' => '', 'publisher_name' => '', 'pub_year' => date('Y'), 'description' => '',
    'author_name' => '', 'category_name' => '', 'quantity' => 1, 'cover_image' => ''
];

/**
 * Load existing book data if an ID is provided (Edit Mode)
 */
if ($book_id) {
    $fetch_sql = "
        SELECT b.*, p.name as publisher_name,
               (SELECT a.name FROM book_author ba JOIN authors a ON ba.author_id = a.id WHERE ba.book_id = b.id LIMIT 1) as author_name,
               (SELECT c.name FROM book_category bc JOIN categories c ON bc.category_id = c.id WHERE bc.book_id = b.id LIMIT 1) as category_name,
               (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id) as quantity
        FROM books b 
        LEFT JOIN publishers p ON b.publisher_id = p.id
        WHERE b.id = ?
    ";
    $fetch_stmt = mysqli_prepare($db_connect, $fetch_sql);
    mysqli_stmt_bind_param($fetch_stmt, "i", $book_id);
    mysqli_stmt_execute($fetch_stmt);
    $fetch_result = mysqli_stmt_get_result($fetch_stmt);
    if ($found_row = mysqli_fetch_assoc($fetch_result)) {
        $book_data = $found_row;
    }
}

/**
 * Helper: Find existing ID or create a new record in a lookup table
 */
function getLookupId($db_connect, $table_name, $display_name) {
    $display_name = trim($display_name);
    if (empty($display_name)) return null;
    
    // Check if it already exists
    $lookup_stmt = mysqli_prepare($db_connect, "SELECT id FROM $table_name WHERE name = ? LIMIT 1");
    mysqli_stmt_bind_param($lookup_stmt, "s", $display_name);
    mysqli_stmt_execute($lookup_stmt);
    $lookup_result = mysqli_stmt_get_result($lookup_stmt);
    
    if ($lookup_row = mysqli_fetch_assoc($lookup_result)) {
        return $lookup_row['id'];
    }
    
    // Otherwise, insert new record
    $insert_stmt = mysqli_prepare($db_connect, "INSERT INTO $table_name (name) VALUES (?)");
    mysqli_stmt_bind_param($insert_stmt, "s", $display_name);
    mysqli_stmt_execute($insert_stmt);
    return mysqli_insert_id($db_connect);
}

/**
 * Handle Form Submission (POST)
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize basic inputs
    $title_input = trim($_POST['title']);
    $year_input  = (int)$_POST['pub_year'];
    $desc_input  = trim($_POST['description']);
    $new_qty     = max(1, (int)$_POST['quantity']); 
    
    $author_input    = $_POST['author_input'];
    $category_input  = $_POST['category_input'];
    $publisher_input = $_POST['publisher_input'];

    // Handle Cover Image Upload
    $cover_image_path = $book_id ? $book_data['cover_image'] : null;
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/books/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $unique_filename = 'book_' . uniqid() . '.' . $file_ext;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_dir . $unique_filename)) {
            $cover_image_path = 'uploads/books/' . $unique_filename;
        }
    }

    // Validation
    if (empty($title_input) || empty($publisher_input) || empty($author_input)) {
        $status_message = "Required fields (Title, Author, Publisher) are missing.";
        $status_type = "error";
    } else {
        mysqli_begin_transaction($db_connect);
        try {
            // Get or Create related IDs for lookup tables
            $author_id    = getLookupId($db_connect, 'authors', $author_input);
            $category_id  = getLookupId($db_connect, 'categories', $category_input);
            $publisher_id = getLookupId($db_connect, 'publishers', $publisher_input);

            if ($book_id) {
                // Update Main Book Info
                $update_sql = "UPDATE books SET title=?, publisher_id=?, pub_year=?, description=?, cover_image=? WHERE id=?";
                $update_stmt = mysqli_prepare($db_connect, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "siissi", $title_input, $publisher_id, $year_input, $desc_input, $cover_image_path, $book_id);
                mysqli_stmt_execute($update_stmt);
                
                // Update Relationships (Delete old first)
                mysqli_query($db_connect, "DELETE FROM book_author WHERE book_id = $book_id");
                mysqli_query($db_connect, "INSERT INTO book_author (book_id, author_id) VALUES ($book_id, $author_id)");
                
                mysqli_query($db_connect, "DELETE FROM book_category WHERE book_id = $book_id");
                if ($category_id) {
                    mysqli_query($db_connect, "INSERT INTO book_category (book_id, category_id) VALUES ($book_id, $category_id)");
                }

                // Inventory Management: Handle additions and removals
                $existing_qty = $book_data['quantity'];
                $qty_difference = $new_qty - $existing_qty;
                
                if ($qty_difference > 0) {
                    // Add new copies
                    for ($i = 0; $i < $qty_difference; $i++) {
                        $barcode = "BK-" . $book_id . "-" . uniqid();
                        mysqli_query($db_connect, "INSERT INTO book_copies (book_id, barcode, status) VALUES ($book_id, '$barcode', 'available')");
                    }
                } elseif ($qty_difference < 0) {
                    // Remove available copies
                    $abs_diff = abs($qty_difference);
                    $avail_res = mysqli_query($db_connect, "SELECT id FROM book_copies WHERE book_id = $book_id AND status = 'available' LIMIT $abs_diff");
                    $avail_ids = [];
                    while ($row = mysqli_fetch_assoc($avail_res)) {
                        $avail_ids[] = $row['id'];
                    }

                    if (count($avail_ids) < $abs_diff) {
                        throw new Exception("Cannot remove $abs_diff copies! Only " . count($avail_ids) . " are currently available (not borrowed).");
                    } else {
                        $ids_str = implode(',', $avail_ids);
                        // Cleanup loan details to satisfy foreign key
                        mysqli_query($db_connect, "DELETE FROM loan_details WHERE book_copy_id IN ($ids_str)");
                        mysqli_query($db_connect, "DELETE FROM book_copies WHERE id IN ($ids_str)");
                    }
                }
                
                mysqli_commit($db_connect);
                $status_message = "Book details updated successfully.";
                $status_type = "success";
                
                // Refresh local state for immediate feedback
                $book_data['cover_image'] = $cover_image_path;
                $book_data['quantity'] = $new_qty;

            } else {
                // Insert New Book
                $insert_sql = "INSERT INTO books (title, publisher_id, pub_year, description, cover_image) VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = mysqli_prepare($db_connect, $insert_sql);
                mysqli_stmt_bind_param($insert_stmt, "siiss", $title_input, $publisher_id, $year_input, $desc_input, $cover_image_path);
                mysqli_stmt_execute($insert_stmt);
                $book_id = mysqli_insert_id($db_connect);
                
                // Set Relationships
                mysqli_query($db_connect, "INSERT INTO book_author (book_id, author_id) VALUES ($book_id, $author_id)");
                if ($category_id) {
                    mysqli_query($db_connect, "INSERT INTO book_category (book_id, category_id) VALUES ($book_id, $category_id)");
                }
                
                // Generate initial book copies
                for ($i = 0; $i < $new_qty; $i++) {
                    $barcode = "BK-" . $book_id . "-" . str_pad($i + 1, 3, '0', STR_PAD_LEFT) . "-" . rand(100, 999);
                    mysqli_query($db_connect, "INSERT INTO book_copies (book_id, barcode, status) VALUES ($book_id, '$barcode', 'available')");
                }
                
                mysqli_commit($db_connect);
                $status_message = "Successfully created book with $new_qty copies.";
                $status_type = "success";
                
                // Setup state for display
                $book_data['cover_image'] = $cover_image_path;
            }
        } catch (Exception $e) {
            mysqli_rollback($db_connect);
            $status_message = "Error: " . $e->getMessage();
            $status_type = "error";
        }
    }
}

/**
 * Handle Cover Image Preview Logic
 */
$image_to_show = "";
if (!empty($book_data['cover_image']) && file_exists("../" . $book_data['cover_image'])) {
    $image_to_show = "../" . $book_data['cover_image'];
} else if ($book_id) {
    // If no custom cover uploaded, show the online placeholder
    $image_to_show = "https://placehold.co/400x600/1e4646/white?text=" . urlencode($book_data['title']);
}
?>

<div class="breadcrumb" style="margin-bottom: 2rem; color: #64748b; font-size: 0.9rem;">
    Home / Book Management / <strong style="color: var(--text-color);"><?php echo $book_id ? 'Edit Book' : 'Add New Book'; ?></strong>
</div>

<div class="form-wrapper" style="max-width: 850px; margin: 0 auto; padding-bottom: 2rem;">
    
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.5rem;"><?php echo $book_id ? 'Edit Book Record' : 'Create New Book'; ?></h1>
        <a href="books.php" class="btn" style="background: #f1f5f9; color: #475569; font-size: 0.9rem;">&larr; Back to List</a>
    </div>

    <!-- Main Registration Form -->
    <form action="" method="POST" id="mainBookForm" autocomplete="off" enctype="multipart/form-data">
        <div class="split-form-container">
            <!-- Media Sidebar -->
            <div class="form-col" style="width: 250px; flex-shrink: 0;">
                <div class="form-card" style="padding: 1rem; text-align: center;">
                    <label style="font-weight: 600; color: var(--text-color); margin-bottom: 1rem; display: block;">Cover Art</label>
                    
                    <div class="image-upload-wrapper" onclick="document.getElementById('file_input').click()">
                        <?php if(!empty($image_to_show)): ?>
                            <img id="image_preview" src="<?php echo $image_to_show; ?>" alt="Cover">
                        <?php else: ?>
                            <img id="image_preview" src="" alt="Preview" style="display: none;">
                            <span class="upload-placeholder">
                                <div>Click to browse image</div>
                            </span>
                        <?php endif; ?>
                    </div>
                    <input type="file" id="file_input" name="cover_image" accept="image/*" style="display: none;">
                    <small style="color: #94a3b8; display: block; margin-top: 1rem;">JPG, PNG accepted.</small>
                </div>
            </div>

            <!-- Content Area -->
            <div class="form-col">
                <div class="form-card" style="padding: 1.25rem;">
                    
                    <h3 class="form-section-title">Essential Details</h3>
                    
                    <div class="form-group">
                        <label>Book Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($book_data['title']); ?>" required placeholder="Enter full title">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label>Author *</label>
                            <input list="authors" name="author_input" value="<?php echo htmlspecialchars($book_data['author_name']); ?>" required placeholder="Search or add author...">
                            <datalist id="authors">
                                <?php mysqli_data_seek($authors_list, 0); while ($a = mysqli_fetch_assoc($authors_list)): ?>
                                    <option value="<?php echo htmlspecialchars($a['name']); ?>">
                                <?php endwhile; ?>
                            </datalist>
                        </div>

                        <div class="form-group">
                            <label>Publisher *</label>
                            <input list="publishers" name="publisher_input" value="<?php echo htmlspecialchars($book_data['publisher_name']); ?>" required placeholder="Search or add publisher...">
                            <datalist id="publishers">
                                <?php mysqli_data_seek($publishers_list, 0); while ($p = mysqli_fetch_assoc($publishers_list)): ?>
                                    <option value="<?php echo htmlspecialchars($p['name']); ?>">
                                <?php endwhile; ?>
                            </datalist>
                        </div>
                    </div>

                    <h3 class="form-section-title" style="margin-top: 1.5rem;">Inventory Info</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label>Category</label>
                            <input list="categories" name="category_input" value="<?php echo htmlspecialchars($book_data['category_name']); ?>" placeholder="Select genre...">
                            <datalist id="categories">
                                <?php mysqli_data_seek($categories_list, 0); while ($c = mysqli_fetch_assoc($categories_list)): ?>
                                    <option value="<?php echo htmlspecialchars($c['name']); ?>">
                                <?php endwhile; ?>
                            </datalist>
                        </div>
                        
                        <div class="form-group">
                            <label>Publication Year</label>
                            <input type="number" id="pub_year" name="pub_year" value="<?php echo $book_data['pub_year']; ?>" min="1400">
                        </div>

                        <div class="form-group">
                            <label>Total Copies</label>
                            <input type="number" name="quantity" value="<?php echo $book_data['quantity']; ?>" min="1" style="font-weight: bold; text-align: center;">
                            <?php if($book_id): ?>
                                <small style="display: block; color: #64748b; font-size: 0.7rem; margin-top: 5px;">Must have at least 1 copy</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h3 class="form-section-title" style="margin-top: 1.5rem;">Description</h3>
                    <div class="form-group">
                        <textarea name="description" rows="5" placeholder="Brief summary of the book content..."><?php echo htmlspecialchars($book_data['description'] ?? ''); ?></textarea>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" id="submit_button" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                            <?php echo $book_id ? 'Update Book Record' : 'Add Book to Library'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
/**
 * Image Upload Preview
 */
document.getElementById('file_input').addEventListener('change', function(event) {
    if (event.target.files && event.target.files[0]) {
        const file_reader = new FileReader();
        file_reader.onload = function(e) {
            const preview = document.getElementById('image_preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            const placeholder = document.querySelector('.upload-placeholder');
            if(placeholder) placeholder.style.display = 'none';
        }
        file_reader.readAsDataURL(event.target.files[0]);
    }
});

/**
 * Handle Server-side Notifications via SweetAlert
 */
<?php if($status_message != ''): ?>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: '<?php echo $status_type; ?>',
            title: '<?php echo addslashes($status_message); ?>',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    });
<?php endif; ?>
</script>

<?php include '../inc/footer.php'; ?>
