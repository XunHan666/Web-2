<?php
/**
 * Display Template for Specific Loan Transaction Details
 */
?>
<div class="breadcrumb" style="margin-bottom: 2rem; color: #64748b; font-size: 0.9rem;">
    Home / Loan Management / <strong style="color: var(--text-color);">Transaction Manifest</strong>
</div>

<div style="max-width: 1000px; margin: 0 auto;">
    <!-- Manifest Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Transaction #<?php echo $loan_info['id']; ?></h1>
            <p style="color: #64748b;">Member: <strong style="color: var(--text-color);"><?php echo htmlspecialchars($loan_info['reader_name']); ?></strong> (<?php echo htmlspecialchars($loan_info['phone']); ?>)</p>
        </div>
        <div style="text-align: right;">
            <span class="badge" style="font-size: 0.9rem; padding: 0.5rem 1.25rem; <?php 
                if($loan_info['status'] == 'closed') echo 'background:#f1f5f9; color:#94a3b8;';
                elseif($loan_info['status'] == 'partial') echo 'background:#e0f2fe; color:#0369a1;';
                else echo 'background:#fee2e2; color:#ef4444;';
            ?>"><?php echo strtoupper($loan_info['status']); ?></span>
            <p style="margin-top: 0.75rem; color: #94a3b8; font-size: 0.85rem;">Date: <?php echo date('M d, Y', strtotime($loan_info['borrow_date'])); ?></p>
        </div>
    </div>

    <!-- Asset List -->
    <div class="table-container">
        <table class="datatable">
            <thead>
                <tr>
                    <th width="50" style="text-align: center;">STT</th>
                    <th width="120">Barcode</th>
                    <th style="text-align: left;">Publication Title</th>
                    <th width="150">Current State</th>
                    <th width="150">Return Date</th>
                    <th width="150" style="text-align: right;">Accrued Fines</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($items_recordset)): ?>
                        <?php static $stt = 1; ?>
                        <td align="center" style="font-weight: 600; color: #64748b;">
                            <?php echo $stt++; ?>
                        </td>
                        <td style="font-family: monospace; font-size: 0.85rem; color: #64748b;"><?php echo htmlspecialchars($item['barcode']); ?></td>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($item['title']); ?></td>
                        <td align="center">
                            <span class="badge" style="<?php echo $item['status'] == 'returned' ? 'background:#dcfce7; color:#166534;' : 'background:#e0f2fe; color:#0369a1;'; ?>">
                                <?php echo strtoupper($item['status']); ?>
                            </span>
                        </td>
                        <td align="center"><?php echo $item['return_date'] ? date('M d, Y', strtotime($item['return_date'])) : '--'; ?></td>
                        <td align="right" style="font-weight: 700; font-family: monospace; color: #ef4444;">
                            <?php echo number_format($item['fine_amount']); ?> VNĐ
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 2rem; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 1.5rem; border-radius: 12px;">
        <a href="loans.php" class="btn" style="background: white; border: 1px solid #e2e8f0; color: #64748b;">&larr; Back to Log</a>
        <div style="text-align: right;">
            <p style="font-size: 0.85rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Total Settlement</p>
            <h2 style="font-size: 1.5rem; color: var(--text-color);"><?php 
                mysqli_data_seek($items_recordset, 0);
                $total_fine = 0;
                while($f = mysqli_fetch_assoc($items_recordset)) { $total_fine += $f['fine_amount']; }
                echo number_format($total_fine); 
            ?> VNĐ</h2>
        </div>
    </div>
</div>
