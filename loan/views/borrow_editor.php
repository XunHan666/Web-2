<?php
/**
 * Editor Template for Borrow - 100% ORIGINAL UI
 */
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
                        <?php mysqli_data_seek($readers_res, 0); while ($reader = mysqli_fetch_assoc($readers_res)): ?>
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
                    <?php mysqli_data_seek($available_books_res, 0); while ($book = mysqli_fetch_assoc($available_books_res)): ?>
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
document.getElementById('borrow_date_input')?.addEventListener('change', function(e) {
    let baseDate = new Date(e.target.value);
    if (!isNaN(baseDate)) {
        baseDate.setDate(baseDate.getDate() + 5);
        let yyyy = baseDate.getFullYear();
        let mm = String(baseDate.getMonth() + 1).padStart(2, '0');
        let dd = String(baseDate.getDate()).padStart(2, '0');
        document.getElementById('due_date_display').value = `${yyyy}-${mm}-${dd}`;
    }
});
</script>
