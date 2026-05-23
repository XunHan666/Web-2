<?php
/**
 * Display Template for Book Detail - 100% ORIGINAL UI
 */
?>
<div style="max-width: 1000px; margin: 0 auto;">
    <!-- Navigation -->
    <?php $back_url = !empty($is_reader_view) ? BASE_URL . 'reader/book.php' : 'books.php'; ?>
    <a href="<?php echo $back_url; ?>" style="text-decoration: none; color: #64748b; margin-bottom: 2rem; display: inline-block;">← Back to Library</a>
    
    <div style="display: grid; grid-template-columns: 350px 1fr; gap: 3rem; background: var(--card-bg); padding: 3rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
        
        <!-- Book Cover Column -->
        <div style="border-radius: 12px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.1);">
            <img src="<?php echo $display_image; ?>" alt="Cover" style="width: 100%; display: block;">
        </div>

        <!-- Book Details Column -->
        <div>
            <!-- Basic Tags -->
            <span class="badge" style="background: var(--primary-color); color: white; margin-bottom: 1rem;">
                <?php echo htmlspecialchars($book_data['category_names'] ?: 'Uncategorized'); ?>
            </span>

            <!-- Primary Header -->
            <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem; line-height: 1.1; color: var(--text-color);">
                <?php echo htmlspecialchars($book_data['title']); ?>
            </h1>
            <p style="font-size: 1.25rem; color: #64748b; margin-bottom: 2rem;">
                By <?php echo htmlspecialchars($book_data['author_names'] ?: 'Unknown Author'); ?>
            </p>

            <!-- Detailed Stats Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                <div>
                    <label style="color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Availability</label>
                    <p style="font-size: 1.1rem; font-weight: 700; margin-top: 0.25rem;">
                        <?php echo $book_data['available_copies']; ?> of <?php echo $book_data['total_copies']; ?> units ready
                    </p>
                </div>
                <div>
                    <label style="color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">Publishing</label>
                    <p style="font-size: 1.1rem; font-weight: 600; margin-top: 0.25rem;">
                        <?php echo htmlspecialchars($book_data['publisher_name'] ?: 'Unknown'); ?> (<?php echo $book_data['pub_year']; ?>)
                    </p>
                </div>
            </div>
            
            <!-- Summary Description -->
            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1rem; margin-bottom: 0.75rem; color: var(--text-color); font-weight: 700; text-transform: uppercase;">About this Book</h3>
                <p style="color: #475569; line-height: 1.8;">
                    <?php echo !empty($book_data['description']) ? nl2br(htmlspecialchars($book_data['description'])) : "No detailed description provided."; ?>
                </p>
            </div>

            <!-- Actions (thay đổi theo role) -->
            <div style="margin-top: 3rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <?php if (!empty($is_reader_view)): ?>
                    <!-- Reader view: check login status -->
                    <?php if ($book_data['available_copies'] > 0): ?>
                        <?php if (!isset($_SESSION['account_id'])): ?>
                            <!-- Guest: prompt to login -->
                            <a href="<?php echo BASE_URL; ?>authen/login.php" 
                               class="btn btn-primary" style="padding:1rem 2.5rem; border-radius:10px; text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem;">
                                🔐 Login to Borrow
                            </a>
                            <a href="<?php echo BASE_URL; ?>authen/register.php"
                               class="btn" style="border:1px solid var(--primary-color);padding:1rem 2rem;border-radius:10px;background:white;color:var(--primary-color); text-decoration:none;">
                                Register Free
                            </a>
                        <?php else: ?>
                            <!-- Logged-in Reader: submit borrow form -->
                            <form action="<?php echo BASE_URL; ?>reader/request_borrow.php" method="POST">
                                <input type="hidden" name="book_id" value="<?php echo $book_data['id']; ?>">
                                <button type="submit" class="btn btn-primary" style="padding:1rem 2.5rem; border-radius:10px;"
                                        onclick="return confirm('Confirm borrow: <?php echo addslashes($book_data['title']); ?>?')">
                                    Borrow this Book
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn" style="background:#e2e8f0;color:#94a3b8;cursor:not-allowed;padding:1rem 2.5rem;border-radius:10px;" disabled>All Copies Borrowed</button>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>reader/book.php"
                       class="btn" style="border:1px solid var(--border-color);padding:1rem 2rem;border-radius:10px;background:white;color:#475569;">← Back</a>
                <?php elseif (!empty($circulation_readonly)): ?>
                    <a href="books.php" class="btn" style="border:1px solid var(--border-color);padding:1rem 2rem;border-radius:10px;background:white;color:#475569;">← Back to Catalog</a>
                    <span style="font-size:0.85rem;color:#64748b;">View only — contact a librarian to edit or loan.</span>
                <?php else: ?>
                    <!-- Librarian: Loan + Edit -->
                    <?php if ($book_data['available_copies'] > 0): ?>
                        <a href="../loan/borrow.php" class="btn btn-primary" style="padding:1rem 2.5rem;border-radius:10px;">Create Loan Transaction</a>
                    <?php else: ?>
                        <button class="btn" style="background:#e2e8f0;color:#94a3b8;cursor:not-allowed;padding:1rem 2.5rem;border-radius:10px;" disabled>All Copies are Borrowed</button>
                    <?php endif; ?>
                    <a href="book_add.php?id=<?php echo $book_data['id']; ?>" class="btn" style="border:1px solid var(--border-color);padding:1rem 2.5rem;border-radius:10px;background:white;color:#475569;">Modify Records</a>
                <?php endif; ?>
            </div>
            
            <p style="margin-top: 2rem; color: #cbd5e1; font-size: 0.8rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                System Reference: #<?php echo $book_data['id']; ?>
            </p>
        </div>
    </div>
</div>
