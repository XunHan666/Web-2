<?php
/**
 * Display Template for Book Inventory - 100% ACCURATE UI
 */
?>
<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / Book Management / <strong style="color: var(--text-color);">Book Inventory</strong>
</div>

<!-- Statistics Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
    <div class="stat-card" style="padding: 1.5rem; border-left: 4px solid var(--primary-color);">
        <h3 style="margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase; color: #64748b;">Total Inventory</h3>
        <div class="value" style="font-size: 2rem; font-weight: 700;"><?php echo $total_copies_count; ?> copies</div>
    </div>
    <div class="stat-card" style="padding: 1.5rem; border-left: 4px solid #10b981;">
        <h3 style="margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase; color: #64748b;">On Shelves</h3>
        <div class="value" style="font-size: 2rem; font-weight: 700; color: #10b981;"><?php echo $available_copies_count; ?> copies</div>
    </div>
    <div class="stat-card" style="padding: 1.5rem; border-left: 4px solid #f59e0b;">
        <h3 style="margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase; color: #64748b;">Borrowed</h3>
        <div class="value" style="font-size: 2rem; font-weight: 700; color: #f59e0b;"><?php echo $borrowed_copies_count; ?> copies</div>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <form action="" method="GET" style="display: flex; gap: 0.5rem; flex: 1; max-width: 500px;">
        <input type="text" name="search" placeholder="Search title, author, or category..." class="search-input" value="<?php echo htmlspecialchars($search_term); ?>" style="flex: 1; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color);">
        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">Search</button>
    </form>
    
    <div style="display: flex; gap: 1rem;">
        <a href="book_add.php" class="btn btn-primary">+ Add New Book</a>
    </div>
</div>

<!-- Books Table -->
<div class="table-container">
    <table class="datatable">
        <thead>
            <tr>
                <th width="80">Cover</th>
                <th style="text-align: left;">Book Details</th>
                <th style="text-align: left;">Category</th>
                <th style="text-align: center;">Pub. Year</th>
                <th style="text-align: center;">Copies</th>
                <th style="text-align: center;">Inventory Status</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($books_list)): ?>
                <tr><td colspan="7" align="center" style="padding: 3rem; color: #64748b;">No results found.</td></tr>
            <?php else: ?>
                <?php foreach ($books_list as $book): 
                    $status_badge_class = 'returned';
                    $status_text = 'Available: ' . $book['available_count'] . '/' . $book['quantity'];
                    if ($book['available_count'] == 0) {
                        $status_badge_class = 'overdue';
                    } elseif ($book['available_count'] < $book['quantity']) {
                        $status_badge_class = 'borrowing';
                    }
                ?>
                    <tr>
                        <td align="center">
                            <div class="avatar-cover">
                                <img src="<?php echo $book['display_image']; ?>" alt="Cover" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
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
                                <span style="color: #e2e8f0; margin: 0 4px;">|</span>
                                <a href="book_add.php?id=<?php echo $book['id']; ?>" style="color: #0ea5e9; text-decoration: none;">Edit</a>
                                <span style="color: #e2e8f0; margin: 0 4px;">|</span>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $book['id']; ?>, '<?php echo addslashes($book['title']); ?>', 'book', 'book_delete.php')" style="color: #ef4444; text-decoration: none;">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
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
.btn-qty:hover { background: var(--primary-color); color: white; border-color: var(--primary-color); }
</style>

<script>
function changeCopies(id, delta, title = '', currentCount = 0) {
    const searchParam = new URLSearchParams(window.location.search).get('search') || '';
    if (delta < 0 && currentCount === 1) {
        Swal.fire({
            title: 'Remove Last Copy?',
            text: `You are removing the last copy of '${title}'.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete book'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `books.php?update_copies=${id}&delta=${delta}&search=${encodeURIComponent(searchParam)}`;
            }
        });
        return;
    }
    window.location.href = `books.php?update_copies=${id}&delta=${delta}&search=${encodeURIComponent(searchParam)}`;
}
</script>
