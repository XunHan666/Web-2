<?php
/**
 * Display Template for Circulation Log - 100% ORIGINAL UI
 */
?>
<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / <strong style="color: var(--text-color);">Loan Management</strong>
</div>

<div class="search-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
    <div>
        <h1 style="font-size: 1.5rem; color: var(--text-color); margin-bottom: 0.25rem;">Loan Log</h1>
        <p style="color: #64748b; font-size: 0.9rem;">Monitor and manage book loans & returns</p>
    </div>
    
    <div style="display: flex; gap: 1rem; align-items: center;">
        <form action="" method="GET" id="filterForm" style="display: flex; align-items: center; gap: 0.6rem;">
            <span style="font-weight: 700; color: #64748b; font-size: 1rem; text-transform: uppercase;">Filter:</span>
            <select name="filter" class="search-input" onchange="this.form.submit()" style="padding: 0.6rem 0.85rem; border-radius: 10px; border: 1px solid var(--border-color); background: white; font-weight: 500; font-size: 0.85rem; min-width: 160px; cursor: pointer; outline: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); color: var(--text-color);">
                <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>ALL HISTORY</option>
                <option value="pending" <?php echo $filter == 'pending' ? 'selected' : ''; ?>>BORROWING</option>
                <option value="overdue" <?php echo $filter == 'overdue' ? 'selected' : ''; ?>>OVERDUE</option>
                <option value="returned" <?php echo $filter == 'returned' ? 'selected' : ''; ?>>RETURNED</option>
            </select>
        </form>
        <a href="borrow.php" class="btn btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 700;">+ New Loan</a>
    </div>
</div>

<div class="table-container">
    <table class="datatable">
        <thead>
            <tr>
                <th width="50" style="text-align: center;">STT</th>
                <th style="text-align: left;">Reader Name</th>
                <th>Borrow Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th style="text-align: center;">Note</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($loans_result) == 0): ?>
                <tr><td colspan="7" align="center" style="padding: 3rem; color: #64748b;">No loan records found.</td></tr>
            <?php else: ?>
                <?php while ($loan = mysqli_fetch_assoc($loans_result)): ?>
                    <tr>
                        <?php static $stt = 1; ?>
                        <td align="center" style="font-weight: 600; color: #64748b;">
                            <?php echo $stt++; ?>
                        </td>
                        <td>
                            <strong style="color: var(--text-color);"><?php echo htmlspecialchars($loan['reader_name']); ?></strong>
                            <div style="font-size: 0.8rem; color: #64748b;"><?php echo htmlspecialchars($loan['phone']); ?></div>
                        </td>
                        <td align="center"><?php echo $loan['borrow_date']; ?></td>
                        <td align="center" style="font-weight: 600;"><?php echo $loan['due_date']; ?></td>
                        <td align="center">
                            <span class="badge badge-<?php 
                                if($loan['status'] == 'closed') echo 'returned';
                                elseif($loan['status'] == 'ongoing' || $loan['status'] == 'partial') echo 'borrowing';
                                else echo 'overdue';
                            ?>">
                                <?php 
                                    if($loan['status'] == 'closed') echo 'RETURNED';
                                    elseif($loan['status'] == 'ongoing' || $loan['status'] == 'partial') echo 'BORROWING';
                                    else echo strtoupper($loan['status']);
                                ?>
                            </span>
                        </td>
                        <td align="center">
                            <?php 
                                $current_date = date('Y-m-d');
                                if ($loan['status'] !== 'closed' && $current_date > $loan['due_date']) {
                                    $diff = strtotime($current_date) - strtotime($loan['due_date']);
                                    $days = round($diff / (60 * 60 * 24));
                                    echo '<span style="color: #ef4444; font-weight: 700; font-size: 0.9rem;">Late ' . $days . ' days</span>';
                                } else {
                                    echo '<span style="color: #94a3b8; font-size: 0.9rem;">-</span>';
                                }
                            ?>
                        </td>
                        <td align="center">
                            <div class="action-buttons-group">
                                <a href="loan_detail.php?id=<?php echo $loan['id']; ?>" style="color: #64748b; text-decoration: none;">Details</a> | 
                                <?php if ($loan['status'] !== 'closed'): ?>
                                    <a href="return.php?loan_id=<?php echo $loan['id']; ?>" style="color: #10b981; text-decoration: none;">Return</a> | 
                                <?php endif; ?>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $loan['id']; ?>, 'Transaction #<?php echo $loan['id']; ?>', 'loan', 'loan_delete.php')" style="color: #ef4444; font-weight: 700; text-decoration: none;">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
