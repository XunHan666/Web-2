<?php
/**
 * Editor Template for Book - 100% ORIGINAL UI
 */
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
            <div class="form-col" style="flex-grow: 1;">
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
document.getElementById('file_input')?.addEventListener('change', function(event) {
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
</script>
