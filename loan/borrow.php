<?php
/**
 * Create New Loan Transaction
 * Handles the registration of new book loans, managing reader association and book copy allocation.
 */
require_once '../env/config.php';
include '../inc/header.php';

/**
 * Data Preparation: Fetch Active Readers and Available Books
 */
$readers_res = mysqli_query($db_connect, "SELECT * FROM readers WHERE status = 'active' ORDER BY name");

// Aggregate available copies by title to simplify selection for the user
$available_books_res = mysqli_query($db_connect, "
    SELECT b.id as book_id, b.title, COUNT(bc.id) as copy_count 
    FROM books b 
    JOIN book_copies bc ON b.id = bc.book_id 
    WHERE bc.status = 'available' 
    GROUP BY b.id
    ORDER BY b.title
");

/**
 * Handle Form Submission (POST)
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reader_id = (int)$_POST['reader_id'];
    $borrow_date = $_POST['borrow_date'];
    
    // System Rule: Due date is strictly enforced as Borrow Date + 5 days
    $due_date = date('Y-m-d', strtotime($borrow_date . ' + 5 days'));
    
    $selected_book_ids = isset($_POST['book_ids']) ? $_POST['book_ids'] : [];

    // Basic Validation
    if (empty($reader_id) || empty($selected_book_ids)) {
        showAlert("Please select a reader and at least one book title.", "error");
    } else {
        mysqli_begin_transaction($db_connect);
        try {
            // Step 1: Create the Master Loan Record
            $insert_loan_sql = "INSERT INTO loans (reader_id, borrow_date, due_date, status) VALUES (?, ?, ?, 'active')";
            $insert_loan_stmt = mysqli_prepare($db_connect, $insert_loan_sql);
            mysqli_stmt_bind_param($insert_loan_stmt, "iss", $reader_id, $borrow_date, $due_date);
            mysqli_stmt_execute($insert_loan_stmt);
            $new_loan_id = mysqli_insert_id($db_connect);

            // Step 2: Allocate available copies for each selected title
            foreach ($selected_book_ids as $book_id) {
                $book_id = (int)$book_id;
                
                // Find the first available physical copy for this book title
                $find_copy_sql = "SELECT id FROM book_copies WHERE book_id = ? AND status = 'available' LIMIT 1 FOR UPDATE";
                $find_copy_stmt = mysqli_prepare($db_connect, $find_copy_sql);
                mysqli_stmt_bind_param($find_copy_stmt, "i", $book_id);
                mysqli_stmt_execute($find_copy_stmt);
                
                $copy_res = mysqli_stmt_get_result($find_copy_stmt);
                if ($copy_data = mysqli_fetch_assoc($copy_res)) {
                    $physical_copy_id = $copy_data['id'];
                    
                    // Link copy to loan detail record
                    mysqli_query($db_connect, "INSERT INTO loan_details (loan_id, book_copy_id, status) VALUES ($new_loan_id, $physical_copy_id, 'borrowed')");
                    
                    // Mark physical copy as borrowed in inventory
                    mysqli_query($db_connect, "UPDATE book_copies SET status = 'borrowed' WHERE id = $physical_copy_id");
                } else {
                    // Safety valve: Handle book becoming unavailable during process
                    throw new Exception("One of the selected books is no longer available in stock.");
                }
            }

            mysqli_commit($db_connect);
            showAlert("Loan transaction created successfully! Copies have been automatically allocated.");
            
            // Return to Log after a short delay
            echo "<script>setTimeout(() => { window.location.href = 'loans.php'; }, 2000);</script>";
        } catch (Exception $e) {
            mysqli_rollback($db_connect);
            showAlert("Transaction failed: " . $e->getMessage(), "error");
        }
    }
}
?>

<div class="breadcrumb" style="margin-bottom: 2rem; color: #64748b; font-size: 0.9rem;">
    Home / Loan Management / <strong style="color: var(--text-color);">New Transaction</strong>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem;">Register New Loan</h1>
        <a href="loans.php" class="btn" style="background: #f1f5f9; color: #475569; font-size: 0.9rem;">&larr; Back to Log</a>
    </div>

    <!-- Registration Form -->
    <div class="form-card" style="padding: 1.5rem; border-radius: 12px;">
        <form action="" method="POST" id="loanCreationForm">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Borrower Selection -->
                <div class="form-group">
                    <label>Library Member (Active Only) *</label>
                    <select name="reader_id" required style="width: 100%;">
                        <option value="">-- Choose Reader --</option>
                        <?php while ($reader = mysqli_fetch_assoc($readers_res)): ?>
                            <option value="<?php echo $reader['id']; ?>">
                                <?php echo htmlspecialchars($reader['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div><!-- Spacer --></div>

                <!-- Date Settings -->
                <div class="form-group">
                    <label>Borrowing Date</label>
                    <input type="date" name="borrow_date" id="borrow_date_input" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%;">
                </div>
                <div class="form-group">
                    <label>Return Due (Fixed 5-Day Period)</label>
                    <input type="date" id="due_date_display" value="<?php echo date('Y-m-d', strtotime('+5 days')); ?>" readonly style="background-color: #f8fafc; opacity: 0.7; cursor: not-allowed; width: 100%;">
                    <small style="color: #94a3b8;">System enforces a 5-day borrowing limit.</small>
                </div>
            </div>

            <!-- Book Selection -->
            <div class="form-group" style="margin-top: 1.5rem;">
                <label>Publications to Borrow * (Multi-select enabled via Ctrl/Cmd)</label>
                <select name="book_ids[]" multiple size="10" required style="width: 100%; height: 300px; border: 1px solid var(--border-color); border-radius: 8px;">
                    <?php while ($book = mysqli_fetch_assoc($available_books_res)): ?>
                        <option value="<?php echo $book['book_id']; ?>">
                            <?php echo htmlspecialchars($book['title']); ?> [<?php echo $book['copy_count']; ?> available]
                        </option>
                    <?php endwhile; ?>
                </select>
                <p style="color: #64748b; font-size: 0.8rem; margin-top: 0.5rem; line-height: 1.4;">
                    <strong>Note:</strong> You are selecting book titles. The system will automatically allocate the first available physical copy (barcode) for each chosen title.
                </p>
            </div>

            <button type="submit" id="submit_btn" class="btn btn-primary" style="width: 100%; margin-top: 2rem; padding: 1rem; font-weight: 700;">
                Validate and Create Transaction
            </button>
        </form>
    </div>
</div>

<script>
/**
 * Update the due date display automatically when the borrow date changes
 */
document.getElementById('borrow_date_input').addEventListener('change', function(e) {
    let baseDate = new Date(e.target.value);
    if (!isNaN(baseDate)) {
        baseDate.setDate(baseDate.getDate() + 5);
        let yyyy = baseDate.getFullYear();
        let mm = String(baseDate.getMonth() + 1).padStart(2, '0');
        let dd = String(baseDate.getDate()).padStart(2, '0');
        document.getElementById('due_date_display').value = `${yyyy}-${mm}-${dd}`;
    }
});

/**
 * Handle visual state during submission
 */
document.getElementById('loanCreationForm').addEventListener('submit', function() {
    const btn = document.getElementById('submit_btn');
    btn.innerHTML = 'Processing Transaction...';
    btn.disabled = true;
    btn.style.opacity = '0.7';
});
</script>

<?php include '../inc/footer.php'; ?>
