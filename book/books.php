s<?php
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

<style>
:root {
    --shopee-orange: #ee4d2d;
    --card-bg: #ffffff;
    --text-main: #212121;
    --text-muted: #757575;
}

/* --- Toolbar UI Modifications --- */
.custom-toolbar {
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 25px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.search-section {
    width: 100%;
    display: flex;
    justify-content: center;
}

.search-form {
    width: 100%;
    max-width: 650px;
}

.search-input-wrapper {
    display: flex;
    border: 2px solid var(--shopee-orange);
    border-radius: 4px;
    overflow: hidden;
}

.search-input-field {
    flex: 1;
    border: none;
    padding: 12px 16px;
    font-size: 0.95rem;
    outline: none;
    color: #333;
}

.search-submit-btn {
    background: var(--shopee-orange);
    color: white;
    border: none;
    padding: 0 30px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.search-submit-btn:hover {
    background: #d73f22;
}

.action-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid #f1f5f9;
    padding-top: 15px;
}

.action-btn-primary, .action-btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 40px;
    padding: 0 20px;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
    box-sizing: border-box;
}

.action-btn-primary {
    background: #0284c7;
    color: white;
    border: none;
}
.action-btn-primary:hover {
    background: #0369a1;
}

.action-btn-secondary {
    background: #e0f2fe;
    color: #0284c7;
    border: none;
}
.action-btn-secondary:hover {
    background: #bae6fd;
}

/* --- Shopee Grid Core Layout --- */
.shopee-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 2rem;
}

.shopee-card {
    background: var(--card-bg);
    border-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.05);
}

.shopee-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: var(--shopee-orange);
}

.card-img-wrapper {
    position: relative;
    width: 100%;
    padding-top: 133.33%; /* Forced 3:4 Book Ratio */
    background: #f5f5f5;
}

.card-img-wrapper img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.card-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    padding: 2px 6px;
    font-size: 0.75rem;
    border-radius: 2px;
    color: white;
    font-weight: bold;
    background: rgba(0,0,0,0.6);
}

.card-info {
    padding: 12px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.card-title {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--text-main);
    text-decoration: none;
    line-height: 1.3rem;
    height: 2.6rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 6px;
}
.card-title:hover {
    color: var(--shopee-orange);
}

.card-author, .card-category {
    font-size: 0.75rem;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 4px;
}

.card-category {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 2px;
    align-self: flex-start;
    max-width: 100%;
    color: #475569;
}

.card-status-text {
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 10px;
    margin-bottom: 8px;
}
.card-status-text.returned { color: #10b981; }
.card-status-text.borrowing { color: #f59e0b; }
.card-status-text.overdue { color: #ef4444; }

.card-qty-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: auto;
    border-top: 1px dashed #f0f0f0;
    padding-top: 8px;
}

.qty-display {
    font-weight: 700;
    color: var(--text-main);
    font-size: 1rem;
    min-width: 20px;
    text-align: center;
}

.btn-qty { 
    width: 26px; height: 26px; border-radius: 4px; border: 1px solid #ddd; 
    background: #ffffff; cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-weight: 700; color: var(--text-main); transition: all 0.2s;
}
.btn-qty:hover { 
    background: var(--shopee-orange); color: white; border-color: var(--shopee-orange); 
}

.card-footer-actions {
    display: flex;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
    transform: translateY(100%);
    transition: transform 0.2s ease;
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
}

.shopee-card:hover .card-footer-actions {
    transform: translateY(0);
}

.shopee-card:hover .card-info {
    padding-bottom: 40px; 
}

.action-btn {
    flex: 1;
    text-align: center;
    padding: 8px 0;
    font-size: 0.8rem;
    text-decoration: none;
    font-weight: 500;
}
.action-btn.view { color: #64748b; }
.action-btn.edit { color: #0ea5e9; border-left: 1px solid #eee; border-right: 1px solid #eee; }
.action-btn.delete { color: #ef4444; }
.action-btn:hover { background: #f1f5f9; }

/* Responsive Adjustments */
@media (max-width: 600px) {
    .action-section {
        flex-direction: column;
        gap: 10px;
    }
    .action-section form, .action-btn-primary {
        width: 100%;
    }
    .action-btn-secondary, .action-btn-primary {
        width: 100%;
    }
}
</style>

<script>
function changeCopies(id, delta, title = '', currentCount = 0) {
    if (delta < 0 && currentCount === 1) {
        Swal.fire({
            title: 'Remove Last Copy?',
            text: `You are removing the last copy of '${title}'. This will delete the book record from the catalog entirely. Proceed?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete book'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `books.php?update_copies=${id}&delta=${delta}`;
            }
        }
    }
    $book['display_image'] = $display_image;
    $books_list[] = $book;
}

<?php include '../inc/footer.php'; ?>
