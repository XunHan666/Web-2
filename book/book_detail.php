<?php
/**
 * Book Detail View
 * Shows comprehensive information about a specific book, its availability, and management options.
 */
require_once '../env/config.php';
include '../inc/header.php';

// Initialization
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Validation: Ensure ID exists
if (!$book_id) {
    showAlert("No book ID provided.", "error");
    echo '<div style="padding:2rem;"><a href="books.php" class="btn btn-primary">Return to Index</a></div>';
    include '../inc/footer.php';
    exit;
}

// Fetch complete book details using JOINs
$detail_query = "
    SELECT b.id, b.title, b.pub_year, b.description, b.cover_image, p.name as publisher_name,
           GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') as author_names,
           GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as category_names,
           (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) as total_copies,
           (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id AND bc.status = 'available') as available_copies
    FROM books b
    LEFT JOIN publishers p ON b.publisher_id = p.id
    LEFT JOIN book_author ba ON b.id = ba.book_id
    LEFT JOIN authors a ON ba.author_id = a.id
    LEFT JOIN book_category bc ON b.id = bc.book_id
    LEFT JOIN categories c ON bc.category_id = c.id
    WHERE b.id = ?
    GROUP BY b.id
";

$detail_stmt = mysqli_prepare($db_connect, $detail_query);
mysqli_stmt_bind_param($detail_stmt, "i", $book_id);
mysqli_stmt_execute($detail_stmt);
$detail_result = mysqli_stmt_get_result($detail_stmt);
$book_data = mysqli_fetch_assoc($detail_result);

// Error Handling: Book not found
if (!$book_data) {
    showAlert("Book requested was not found in the database.", "error");
    echo '<div style="padding:2rem;"><a href="books.php" class="btn btn-primary">Return to Index</a></div>';
    include '../inc/footer.php';
    exit;
}

// Prepare Cover Image logic
$book_title = $book_data['title'];
$display_image = "https://placehold.co/400x600/007bff/white?text=" . urlencode($book_title); 

if (!empty($book_data['cover_image']) && file_exists("../" . $book_data['cover_image'])) {
    $display_image = "../" . $book_data['cover_image'];
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
?>

<div style="max-width: 1000px; margin: 0 auto;">
    <!-- Navigation -->
    <a href="books.php" style="text-decoration: none; color: #64748b; margin-bottom: 2rem; display: inline-block;">&larr; Back to Library</a>
    
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

            <!-- Management Actions -->
            <div style="margin-top: 3rem; display: flex; gap: 1rem; align-items: center;">
                <?php if ($book_data['available_copies'] > 0): ?>
                    <a href="../loan/borrow.php" class="btn btn-primary" style="padding: 1rem 2.5rem; border-radius: 10px;">Create Loan Transaction</a>
                <?php else: ?>
                    <button class="btn" style="background: #e2e8f0; color: #94a3b8; cursor: not-allowed; padding: 1rem 2.5rem; border-radius: 10px;" disabled>All Copies are Borrowed</button>
                <?php endif; ?>
                
                <a href="book_add.php?id=<?php echo $book_data['id']; ?>" class="btn" style="border: 1px solid var(--border-color); padding: 1rem 2.5rem; border-radius: 10px; background: white; color: #475569;">Modify Records</a>
            </div>
            
            <p style="margin-top: 2rem; color: #cbd5e1; font-size: 0.8rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                System Reference: #<?php echo $book_data['id']; ?>
            </p>
        </div>
    </div>
</div>

<?php include '../inc/footer.php'; ?>
