<?php
/**
 * Editor Template for Settings - 100% ORIGINAL UI
 */
?>
<div class="search-header">
    <h1 style="font-size: 1.5rem; color: var(--text-color);">System Configuration</h1>
</div>

<div class="split-form-container">
    <div class="form-col data-col">
        <div class="form-card" style="margin: 0; max-width: 100%;">
            <h2 class="form-section-title">Library Rules & Policy</h2>
            
            <form action="" method="POST">
                <input type="hidden" name="update_settings" value="1">
                <div class="form-grid-two" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    <div class="form-group">
                        <label for="fine_per_day">Late Return Fine (VND/Day)</label>
                        <input type="number" id="fine_per_day" name="fine_per_day" value="<?php echo htmlspecialchars($current_fine); ?>" required>
                        <span class="reader-muted">Cost charged per day after due date</span>
                    </div>
                    <div class="form-group">
                        <label for="max_loan_days">Standard Loan Duration (Days)</label>
                        <input type="number" id="max_loan_days" name="max_loan_days" value="<?php echo htmlspecialchars($current_duration); ?>" required>
                        <span class="reader-muted">Default borrowing period</span>
                    </div>
                </div>
                
                <div class="alert alert-info" style="margin-top: 1.5rem;">
                    <strong>Notice:</strong> Changes applied here will immediately affect fine calculations for all new and ongoing returns.
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2.5rem;">Save System Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>
