<?php
/**
 * Books Inventory Management
 * Displays a searchable list of books and basic statistics.
 */
require_once '../env/config.php';
include '../inc/header.php';

// Initialization
$search_term = isset($_GET['search']) ? $_GET['search'] : '';


/**
 * Handle Copy Quantity Update
 */
if (isset($_GET['update_copies']) && isset($_GET['delta'])) {
    $book_id = (int)$_GET['update_copies'];
    $delta = (int)$_GET['delta'];

    if ($delta > 0) {
        // Add copies
        for ($i = 0; $i < $delta; $i++) {
            mysqli_query($db_connect, "INSERT INTO book_copies (book_id, status) VALUES ($book_id, 'available')");
        }
        showAlert("Added $delta copy(ies) successfully.");
    } elseif ($delta < 0) {
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
}

/**
 * Handle Inventory Synchronization (Heal Logic)
 */
if (isset($_POST['sync_inventory'])) {
    mysqli_begin_transaction($db_connect);
    try {
        // Reset all copies to available
        mysqli_query($db_connect, "UPDATE book_copies SET status = 'available'");
        
        // Sync with active loans
        $sync_sql = "
            UPDATE book_copies bc
            JOIN loan_details ld ON bc.id = ld.book_copy_id
            SET bc.status = 'borrowed'
            WHERE ld.status = 'borrowed'
        ";
        mysqli_query($db_connect, $sync_sql);
        
        mysqli_commit($db_connect);
        showAlert("Inventory status synchronized with active loans successfully.");
    } catch (Exception $e) {
        mysqli_rollback($db_connect);
        showAlert("Sync failed: " . $e->getMessage(), "error");
    }
}


/**
 * Statistics Dashboard Calculations
 */
$total_copies_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM book_copies");
$total_copies_count = mysqli_fetch_array($total_copies_res)[0];

$borrowed_copies_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM loan_details WHERE status = 'borrowed'");
$borrowed_copies_count = mysqli_fetch_array($borrowed_copies_res)[0];

$available_copies_count = $total_copies_count - $borrowed_copies_count;

/**
 * Fetch Books with Search Criteria
 */
$search_pattern = "%$search_term%";
$main_query = "
    SELECT b.id, b.title, b.pub_year, b.cover_image, p.name as publisher_name,
           GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') as author_names,
           GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names,
           (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) as quantity,
           ((SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) - 
            (SELECT COUNT(*) FROM loan_details ld JOIN book_copies bc2 ON ld.book_copy_id = bc2.id 
             WHERE bc2.book_id = b.id AND ld.status = 'borrowed')) as available_count
    FROM books b
    LEFT JOIN publishers p ON b.publisher_id = p.id
    LEFT JOIN book_author ba ON b.id = ba.book_id
    LEFT JOIN authors a ON ba.author_id = a.id
    LEFT JOIN book_category bc ON b.id = bc.book_id
    LEFT JOIN categories c ON bc.category_id = c.id
    WHERE b.title LIKE ? OR a.name LIKE ? OR c.name LIKE ?
    GROUP BY b.id
    ORDER BY b.id ASC
";

$search_stmt = mysqli_prepare($db_connect, $main_query);
mysqli_stmt_bind_param($search_stmt, "sss", $search_pattern, $search_pattern, $search_pattern);
mysqli_stmt_execute($search_stmt);
$books_result = mysqli_stmt_get_result($search_stmt);
?>

<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / Book Management / <strong style="color: var(--text-color);">Book Inventory</strong>
</div>

<!-- Statistics Cards -->
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

<!-- Toolbar: Search and Action Buttons -->
<div class="toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <form action="" method="GET" style="display: flex; gap: 0.5rem; flex: 1; min-width: 300px; max-width: 500px;">
        <input type="text" name="search" placeholder="Search title, author, or category..." class="search-input" value="<?php echo htmlspecialchars($search_term); ?>" style="width: 250px;">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
    
    <form action="" method="POST" style="display: inline-block;">
        <button type="submit" name="sync_inventory" class="btn" style="background: #e0f2fe; color: #0284c7; font-weight: 600;">
            Sync Inventory
        </button>
    </form>
    
    <a href="book_add.php" class="btn btn-primary">
        + Add New Book
    </a>
</div>

<!-- Books Table -->
<div class="table-container">
    <table class="datatable">
        <thead>
            <tr>
                <th width="80">Cover</th>
                <th style="text-align: left;">Book Details</th>
                <th style="text-align: left;">Category</th>
                <th>Pub. Year</th>
                <th style="text-align: center;">Copies</th>
                <th>Inventory Status</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($books_result) == 0): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: #64748b;">
                        No books found matching "<strong><?php echo htmlspecialchars($search_term); ?></strong>".
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($book = mysqli_fetch_assoc($books_result)): 
                    // Handle Cover Image logic
                    $book_title = $book['title'];
                    // Increase truncation to 40 characters for better placeholder readability
                    $display_image = "https://placehold.co/100x150/007bff/white?text=" . urlencode(substr($book_title, 0, 40));
                    
                    if (!empty($book['cover_image']) && file_exists("../" . $book['cover_image'])) {
                        $display_image = "../" . $book['cover_image'];
                    } else {
                        // Enhanced auto-matching logic for famous titles and special formats
                        $clean_title = strtolower(trim($book_title));
                        $title_no_colon = str_replace(':', '', $clean_title);
                        $title_colon_to_space = str_replace(':', ' ', $clean_title);
                        $title_no_subtitle = strtolower(trim(explode(':', $book_title)[0]));
                        
                        $search_filenames = [
                            $clean_title . ".jpg",
                            str_replace(' ', '_', $clean_title) . ".jpg",
                            $title_no_colon . ".jpg",
                            str_replace('  ', ' ', $title_colon_to_space) . ".jpg",
                            str_replace(' ', '_', str_replace('  ', ' ', $title_colon_to_space)) . ".jpg",
                            str_replace('&', 'and', $clean_title) . ".jpg",
                            str_replace(' ', '_', str_replace('&', 'and', $clean_title)) . ".jpg",
                            $title_no_subtitle . ".jpg",
                            str_replace(' ', '_', $title_no_subtitle) . ".jpg"
                        ];
                        
                        foreach ($search_filenames as $filename) {
                            if (file_exists("../img-web2/" . $filename)) {
                                $display_image = "../img-web2/" . $filename;
                                break;
                            }
                        }
                    }
                    
                    // Determine Status Badge styles
                    $status_badge_class = 'returned'; // Default green
                    $status_text = 'Available: ' . $book['available_count'] . '/' . $book['quantity'];
                    
                    if ($book['available_count'] == 0) {
                        $status_badge_class = 'overdue'; // red
                        $status_text = 'Borrowed: 0/' . $book['quantity'];
                    } elseif ($book['available_count'] < $book['quantity']) {
                        $status_badge_class = 'borrowing'; // yellow
                    }
                ?>
                    <tr>
                        <td align="center">
                            <div class="avatar-cover">
                                <img src="<?php echo $display_image; ?>" alt="Cover" loading="lazy">
                            </div>
                        </td>
                        <td>
                            <strong style="font-size: 1.1rem; color: var(--text-color); display: block; margin-bottom: 0.25rem;">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </strong>
                            <div style="font-size: 0.85rem; color: #64748b;">By <?php echo htmlspecialchars($book['author_names'] ?: 'Unknown'); ?></div>
                        </td>
                        <td>
                            <span class="badge" style="background:#f1f5f9; color:#475569; font-weight:500;">
                                <?php echo htmlspecialchars($book['category_names'] ?: 'Uncategorized'); ?>
                            </span>
                        </td>
                        <td align="center"><?php echo $book['pub_year']; ?></td>
                        <td align="center">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
                                <button class="btn-qty" onclick="changeCopies(<?php echo $book['id']; ?>, -1, '<?php echo addslashes($book['title']); ?>', <?php echo $book['quantity']; ?>)">-</button>
                                <span style="font-weight: 700; color: var(--primary-color); font-size: 1.1rem; min-width: 24px;"><?php echo $book['quantity']; ?></span>
                                <button class="btn-qty" onclick="changeCopies(<?php echo $book['id']; ?>, 1)">+</button>
                            </div>
                        </td>
                        <td align="center">
                            <span class="badge badge-<?php echo $status_badge_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td align="center">
                            <div class="action-buttons-group">
                                <a href="book_detail.php?id=<?php echo $book['id']; ?>" style="color: #64748b; text-decoration: none;">View</a>
                                <span style="color: #e2e8f0;">|</span>
                                <a href="book_add.php?id=<?php echo $book['id']; ?>" style="color: #0ea5e9; text-decoration: none;">Edit</a>
                                <span style="color: #e2e8f0;">|</span>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $book['id']; ?>, '<?php echo addslashes($book['title']); ?>', 'book', 'book_delete.php')" style="color: #ef4444; text-decoration: none;">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.btn-qty { 
    width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--border-color); 
    background: #ffffff; cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-weight: 700; color: var(--text-color); transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.btn-qty:hover { background: var(--primary-color); color: white; border-color: var(--primary-color); transform: translateY(-1px); }
.btn-qty:active { transform: translateY(0); }
</style>

<script>
function changeCopies(id, delta, title = '', currentCount = 0) {
    if (delta < 0 && currentCount === 1) {
        Swal.fire({
            title: 'Remove Last Copy?',
            text: `You are removing the last copy of '${title}'. This will delete the book entirely. Continue?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete book'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `books.php?update_copies=${id}&delta=${delta}`;
            }
        });
        return;
    }
    window.location.href = `books.php?update_copies=${id}&delta=${delta}`;
}
</script>

<?php include '../inc/footer.php'; ?>
