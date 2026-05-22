<h1 class="rp-page-title">📚 Browse Books</h1>
<p class="rp-page-sub">Search and discover books available in the library.</p>

<form method="GET" class="rp-search-bar">
    <input type="text" name="search" placeholder="Title or author..." value="<?php echo htmlspecialchars($search); ?>">
    <select name="category">
        <option value="0">All Categories</option>
        <?php while ($c = mysqli_fetch_assoc($cats_res)): ?>
            <option value="<?php echo $c['id']; ?>" <?php echo $cat_id==$c['id']?'selected':''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
        <?php endwhile; ?>
    </select>
    <button type="submit" class="rp-btn">Search</button>
    <?php if ($search || $cat_id): ?><a href="book.php" class="rp-btn rp-btn-ghost">Clear</a><?php endif; ?>
</form>

<?php if (mysqli_num_rows($books_res) == 0): ?>
    <div class="rp-empty"><div class="icon">🔍</div><p>No books found.</p></div>
<?php else: ?>
    <div class="rp-books-grid">
    <?php while ($b = mysqli_fetch_assoc($books_res)):
        $cover = (!empty($b['cover_image']) && file_exists('../'.$b['cover_image']))
            ? BASE_URL . $b['cover_image']
            : 'https://placehold.co/200x300/1e4646/white?text=' . urlencode($b['title']);
        $avail = $b['available_count'] > 0;
    ?>
        <div class="rp-book-card">
            <a href="<?php echo BASE_URL; ?>reader/book.php?id=<?php echo $b['id']; ?>" class="rp-book-cover">
                <img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>">
                <span class="<?php echo $avail ? 'badge-available' : 'badge-unavailable'; ?>">
                    <?php echo $avail ? '✓ Available' : 'Unavailable'; ?>
                </span>
            </a>
            <div class="rp-book-body">
                <div>
                    <div class="rp-book-title"><?php echo htmlspecialchars($b['title']); ?></div>
                    <div class="rp-book-author"><?php echo htmlspecialchars($b['authors'] ?? 'Unknown'); ?></div>
                </div>
                <a href="<?php echo BASE_URL; ?>reader/book.php?id=<?php echo $b['id']; ?>"
                   class="rp-btn <?php echo $avail ? '' : 'rp-btn-ghost'; ?>" style="text-align:center; display:block;">
                    <?php echo $avail ? 'View & Borrow' : 'View Details'; ?>
                </a>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
<?php endif; ?>
