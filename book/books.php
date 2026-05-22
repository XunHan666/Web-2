<?php
/**
 * Book Inventory Directory - Controller (Fixed Header Issue & Image Logic)
 */
require_once '../env/config.php';

// 1. Xử lý logic TRƯỚC khi gửi bất kỳ nội dung HTML nào (Để tránh lỗi Headers already sent)
if (isset($_GET['update_copies']) && isset($_GET['delta'])) {
    $book_id = (int)$_GET['update_copies'];
    $delta = (int)$_GET['delta'];

   // MỚI (ĐÃ FIX LỖI DUPLICATE BARCODE)
    if ($delta > 0) {
        // Thêm bản sao mới kèm mã Barcode sinh tự động duy nhất
        for ($i = 0; $i < $delta; $i++) {
            // Sinh mã ngẫu nhiên dạng: B[ID_SÁCH]_[Thời_Gian_Hiện_Tại][Số_Thứ_Tự_Ngẫu_Nhiên]
            // Ví dụ: B5_171615432189
            $auto_barcode = "B" . $book_id . "_" . time() . rand(10, 99);
            
            $insert_copy_query = "INSERT INTO book_copies (book_id, barcode, status) 
                                VALUES ($book_id, '$auto_barcode', 'available')";
            
            mysqli_query($db_connect, $insert_copy_query);
        }
        showAlert("Added $delta copy(ies) successfully.");
    }
    elseif ($delta < 0) {
        $abs_delta = abs($delta);
        
        // Check available copies
        $avail_res = mysqli_query($db_connect, "SELECT id FROM book_copies WHERE book_id = $book_id AND status = 'available' LIMIT $abs_delta");
        $avail_ids = [];
        while ($row = mysqli_fetch_assoc($avail_res)) {
            $avail_ids[] = $row['id'];
        }

        if (count($avail_ids) < $abs_delta) {
            showAlert("Cannot remove $abs_delta copies! Only " . count($avail_ids) . " are currently available (not borrowed).", "error");
        } else {
            $ids_str = implode(',', $avail_ids);
            
            // Cleanup: Delete related records in loan_details first to satisfy foreign key constraints
            mysqli_query($db_connect, "DELETE FROM loan_details WHERE book_copy_id IN ($ids_str)");
            
            // Now safe to delete the copies
            mysqli_query($db_connect, "DELETE FROM book_copies WHERE id IN ($ids_str)");
            
            // Check if any copies remain
            $remain_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM book_copies WHERE book_id = $book_id");
            $remain_count = mysqli_fetch_array($remain_res)[0];
            
            if ($remain_count == 0) {
                deleteBook($db_connect, $book_id);
                showAlert("Last copy removed. The book record has been deleted from the catalog.");
            } else {
                showAlert("Removed $abs_delta copy(ies) successfully.");
            }
        }
    }
    header("Location: books.php" . (!empty($_GET['search']) ? "?search=".urlencode($_GET['search']) : ""));
    exit();
}

// 2. Sau khi xử lý xong logic chuyển hướng mới include Header
include '../inc/header.php';


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
           (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id AND bc.status = 'available') as available_count,
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

?>
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
    <div class="stat-card" style="padding: 1.5rem; border-left-color: var(--primary-color);">
        <h3 style="margin-bottom: 0;">Total Inventory</h3>
        <div class="value" style="font-size: 1.5rem;"><?php echo $total_copies_count; ?> copies</div>
    </div>
    <div class="stat-card" style="padding: 1.5rem; border-left-color: #10b981;">
        <h3 style="margin-bottom: 0;">On Shelves</h3>
        <div class="value" style="font-size: 1.5rem; color: #10b981;"><?php echo $available_copies_count; ?> copies</div>
    </div>
    <div class="stat-card" style="padding: 1.5rem; border-left-color: #f59e0b;">
        <h3 style="margin-bottom: 0;">Borrowed</h3>
        <div class="value" style="font-size: 1.5rem; color: #f59e0b;"><?php echo $borrowed_copies_count; ?> copies</div>
    </div>
</div>

<div class="custom-toolbar">
    <div class="search-section">
        <form action="" method="GET" class="search-form">
            <div class="search-input-wrapper">
                <input type="text" name="search" placeholder="Search by title, author, or category..." class="search-input-field" value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit" class="search-submit-btn">Search</button>
            </div>
        </form>
    </div>
    
    <div class="action-section">
        <form action="" method="POST" style="margin: 0;">
            <button type="submit" name="sync_inventory" class="action-btn-secondary">
                Sync Inventory
            </button>
        </form>
        
        <a href="book_add.php" class="action-btn-primary">
            + Add New Book
        </a>
    </div>
</div>

<?php if (mysqli_num_rows($books_result) == 0): ?>
    <div style="text-align: center; padding: 5rem 1rem; color: #64748b; background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <img src="https://placehold.co/120x120/f1f5f9/64748b?text=No+Data" alt="No data" style="margin-bottom: 1rem; border-radius: 50%;">
        <p style="font-size: 1.1rem; margin: 0;">No books found matching "<strong><?php echo htmlspecialchars($search_term); ?></strong>".</p>
    </div>
<?php else: ?>
    <div class="shopee-grid">
        <?php while ($book = mysqli_fetch_assoc($books_result)): 
            $book_title = $book['title'];
            if (!empty($book['cover_image']) && file_exists("../" . $book['cover_image'])) {
                $display_image = "../" . $book['cover_image'];
            } else {
                $display_image = "https://placehold.co/100x150/1e4646/white?text=" . urlencode(substr($book_title, 0, 40));
            }
            
            // Badge translation logic
            $status_badge_class = 'returned'; 
            $status_text = 'Available: ' . $book['available_count'] . '/' . $book['quantity'];
            if ($book['available_count'] == 0) {
                $status_badge_class = 'overdue'; 
                $status_text = 'Out of Stock: 0/' . $book['quantity'];
            } elseif ($book['available_count'] < $book['quantity']) {
                $status_badge_class = 'borrowing'; 
            }
        ?>
            <div class="shopee-card">
                <div class="card-img-wrapper">
                    <img src="<?php echo $display_image; ?>" alt="Cover" loading="lazy">
                    <span class="card-badge">
                        <?php echo $book['pub_year']; ?>
                    </span>
                </div>
                
                <div class="card-info">
                    <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="card-title" title="<?php echo htmlspecialchars($book['title']); ?>">
                        <?php echo htmlspecialchars($book['title']); ?>
                    </a>
                    
                    <div class="card-author">By <?php echo htmlspecialchars($book['author_names'] ?: 'Unknown'); ?></div>
                    <div class="card-category"><?php echo htmlspecialchars($book['category_names'] ?: 'Uncategorized'); ?></div>
                    
                    <div class="card-status-text <?php echo $status_badge_class; ?>">
                        <?php echo $status_text; ?>
                    </div>
                    
                    <div class="card-qty-actions">
                        <button class="btn-qty" onclick="changeCopies(<?php echo $book['id']; ?>, -1, '<?php echo addslashes($book['title']); ?>', <?php echo $book['quantity']; ?>)">-</button>
                        <span class="qty-display"><?php echo $book['quantity']; ?></span>
                        <button class="btn-qty" onclick="changeCopies(<?php echo $book['id']; ?>, 1)">+</button>
                    </div>
                </div>
                
                <div class="card-footer-actions">
                    <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="action-btn view"><i class="fas fa-eye"></i> View</a>
                    <a href="book_add.php?id=<?php echo $book['id']; ?>" class="action-btn edit"><i class="fas fa-edit"></i> Edit</a>
                    <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $book['id']; ?>, '<?php echo addslashes($book['title']); ?>', 'book', 'book_delete.php')" class="action-btn delete"><i class="fas fa-trash"></i> Delete</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>


<script>
function changeCopies(id, delta, title = '', currentCount = 0) {
    if (delta < 0 && currentCount === 1) {
        Swal.fire({
            title: 'Remove Last Copy?',
            text: `Removing the last copy of '${title}' will delete the book entirely.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete'
        }).then(r => { if (r.isConfirmed) window.location.href = `books.php?update_copies=${id}&delta=${delta}`; });
    } else {
        window.location.href = `books.php?update_copies=${id}&delta=${delta}`;
    }
}
</script>

<?php include '../inc/footer.php'; ?>

