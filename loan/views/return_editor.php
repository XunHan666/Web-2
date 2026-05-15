<?php
/**
 * Editor Template for Return - 100% ORIGINAL UI
 */
?>
<div class="breadcrumb" style="margin-bottom: 2rem; color: #64748b; font-size: 0.9rem;">
    Home / Circulation / <strong style="color: var(--text-color);">Asset Check-in</strong>
</div>

<div style="max-width: 1000px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem;">Check-in Transaction #<?php echo $loan_info['id']; ?></h1>
        <a href="loans.php" class="btn" style="background: #f1f5f9; color: #475569;">&larr; Back to Log</a>
    </div>

    <?php 
    // Pre-calculate if there are any active (borrowed) items
    $has_active_items = false;
    mysqli_data_seek($items_recordset, 0);
    while ($chk = mysqli_fetch_assoc($items_recordset)) {
        if ($chk['status'] == 'borrowed') { $has_active_items = true; break; }
    }
    ?>

    <!-- Reader Info Card -->
    <div class="form-card" style="margin-bottom: 2rem; background: #f8fafc; border: 1px solid #e2e8f0; padding: 1.5rem; border-radius: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h3 style="margin-bottom: 0.5rem;">Reader: <?php echo htmlspecialchars($loan_info['reader_name']); ?></h3>
                <p style="color: #64748b; font-size: 0.9rem;">Contact: <?php echo htmlspecialchars($loan_info['phone']); ?></p>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 0.85rem; color: #94a3b8; font-weight: 700;">Deadline</p>
                <p style="font-weight: 700; color: <?php echo ($days_late_count > 0 && $has_active_items) ? 'var(--danger)' : 'inherit'; ?>;">
                    <?php echo $loan_info['due_date']; ?>
                </p>
            </div>
        </div>
        
        <?php if ($days_late_count > 0 && $has_active_items): ?>
            <div style="padding: 1.25rem; background: #fee2e2; color: #991b1b; border-radius: 10px; margin-top: 1.5rem; border: 1px solid #fecaca; display: flex; align-items: center; gap: 1rem;">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <div>
                    <strong>Overdue Alert:</strong> This session is <?php echo $days_late_count; ?> day(s) late. 
                    Fine per item: <strong><?php echo number_format($calculated_fine); ?> VNĐ</strong>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Return Form -->
    <form action="" method="POST">
        <div class="table-container">
            <table class="datatable">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAllItems"></th>
                        <th width="40" style="text-align: center;">STT</th>
                        <th>Barcode</th>
                        <th style="text-align: left;">Book Title</th>
                        <th>Status</th>
                        <th>Return Date</th>
                        <th style="text-align: right;">Fine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_active_items = false;
                    mysqli_data_seek($items_recordset, 0);
                    while ($row = mysqli_fetch_assoc($items_recordset)): 
                    ?>
                        <tr>
                            <td align="center">
                                <?php if ($row['status'] == 'borrowed'): $has_active_items = true; ?>
                                    <input type="checkbox" name="return_details[]" class="item-checkbox" value="<?php echo $row['id']; ?>">
                                <?php else: ?>
                                    <span style="color: #cbd5e1;">-</span>
                                <?php endif; ?>
                            </td>
                            <?php static $stt = 1; ?>
                            <td align="center" style="font-weight: 600; color: #64748b;">
                                <?php echo $stt++; ?>
                            </td>
                            <td style="font-family: monospace;"><?php echo htmlspecialchars($row['barcode']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td align="center">
                                <span class="badge badge-<?php echo $row['status'] == 'returned' ? 'returned' : ($days_late_count > 0 ? 'overdue' : 'borrowing'); ?>">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </td>
                            <td align="center"><?php echo $row['return_date'] ?: '--'; ?></td>
                            <td align="right" style="font-weight: 700; font-family: monospace;">
                                <?php echo number_format($row['status'] == 'returned' ? $row['fine_amount'] : ($row['status'] == 'borrowed' ? $calculated_fine : 0)); ?> VNĐ
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if ($has_active_items): ?>
            <div style="margin-top: 2rem; text-align: right;">
                <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-weight: 700;">Confirm Selected Returns</button>
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
document.getElementById('selectAllItems')?.addEventListener('change', function() {
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
