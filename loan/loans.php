<?php
/**
 * Circulation Log
 * Lists all active and past library transactions (loans) with filtering and return options.
 */
require_once '../env/config.php';
include '../inc/header.php';

// Initialization
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['filter']) ? $_GET['filter'] : ''; 

/**
 * Handle Single Item Return
 */
if (isset($_GET['return_detail_id'])) {
    $detail_record_id = (int)$_GET['return_detail_id'];
    mysqli_begin_transaction($db_connect);
    try {
        // Step 1: Update the specific detail record status
        mysqli_query($db_connect, "UPDATE loan_details SET status = 'returned', return_date = NOW() WHERE id = $detail_record_id");
        
        // Step 2: identify the book copy to make it available again
        $copy_lookup_res = mysqli_query($db_connect, "SELECT book_copy_id, loan_id FROM loan_details WHERE id = $detail_record_id");
        $copy_data = mysqli_fetch_assoc($copy_lookup_res);
        $book_copy_id = $copy_data['book_copy_id'];
        $parent_loan_id = $copy_data['loan_id'];
        
        mysqli_query($db_connect, "UPDATE book_copies SET status = 'available' WHERE id = $book_copy_id");
        
        // Step 3: Check if all items in this loan are now returned to close the transaction
        $remaining_items_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM loan_details WHERE loan_id = $parent_loan_id AND status = 'borrowed'");
        $remaining_count = mysqli_fetch_array($remaining_items_res)[0];
        
        if ($remaining_count == 0) {
            mysqli_query($db_connect, "UPDATE loans SET status = 'closed' WHERE id = $parent_loan_id");
        } else {
            mysqli_query($db_connect, "UPDATE loans SET status = 'partial' WHERE id = $parent_loan_id");
        }
        
        mysqli_commit($db_connect);
        showAlert("Book checked back in successfully.");
    } catch (Exception $e) {
        mysqli_rollback($db_connect);
        showAlert("Error during return process: " . $e->getMessage(), "error");
    }
}


/**
 * Loan Statistics Dashboard
 */
// Count active items
$active_items_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM loan_details WHERE status = 'borrowed'");
$active_items_count = mysqli_fetch_array($active_items_res)[0];

// Count overdue items
$overdue_items_query = "
    SELECT COUNT(*) FROM loan_details ld 
    JOIN loans l ON ld.loan_id = l.id 
    WHERE ld.status = 'borrowed' AND l.due_date < CURDATE()
";
$overdue_items_res = mysqli_query($db_connect, $overdue_items_query);
$overdue_items_count = mysqli_fetch_array($overdue_items_res)[0];

// Count new loans created this week
$weekly_new_query = "SELECT COUNT(*) FROM loan_details ld JOIN loans l ON ld.loan_id = l.id WHERE YEARWEEK(l.borrow_date, 1) = YEARWEEK(CURDATE(), 1)";
$weekly_new_res = mysqli_query($db_connect, $weekly_new_query);
$weekly_new_count = mysqli_fetch_array($weekly_new_res)[0];

/**
 * Fetch Transactions for main list
 */
$search_pattern = "%$search_query%";
$transactions_query = "
    SELECT ld.id as detail_id, l.id as loan_id, 
           r.name as reader_name, r.id as reader_id, 
           bk.title as book_title, bc.barcode, 
           l.borrow_date, l.due_date, ld.status as item_status, ld.return_date,
           (SELECT COUNT(ld2.id) 
            FROM loans l2 JOIN loan_details ld2 ON l2.id = ld2.loan_id 
            WHERE l2.reader_id = r.id AND ld2.status = 'borrowed' AND l2.due_date < CURDATE()
           ) as reader_overdue_warning
    FROM loan_details ld
    JOIN loans l ON ld.loan_id = l.id
    JOIN readers r ON l.reader_id = r.id
    JOIN book_copies bc ON ld.book_copy_id = bc.id
    JOIN books bk ON bc.book_id = bk.id
    WHERE (r.name LIKE ? OR bk.title LIKE ?)
";

// Apply Time-Status Filters
if ($status_filter === 'pending') {
    $transactions_query .= " AND ld.status = 'borrowed' AND l.due_date >= CURDATE()";
} elseif ($status_filter === 'overdue') {
    $transactions_query .= " AND ld.status = 'borrowed' AND l.due_date < CURDATE()";
} elseif ($status_filter === 'closed') {
    $transactions_query .= " AND l.status = 'closed'";
}

$transactions_query .= " ORDER BY ld.id ASC";

$stmt = mysqli_prepare($db_connect, $transactions_query);
mysqli_stmt_bind_param($stmt, "ss", $search_pattern, $search_pattern);
mysqli_stmt_execute($stmt);
$transactions_result = mysqli_stmt_get_result($stmt);
?>

<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / Loan Management / <strong style="color: var(--text-color);">Circulation Log</strong>
</div>

<!-- Statistics Section -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
    <div class="stat-card" style="padding: 1.5rem; border-left-color: #3b82f6;">
        <h3 style="margin-bottom: 0;">Currently Borrowed</h3>
        <div class="value" style="font-size: 1.5rem; color: #3b82f6;"><?php echo $active_items_count; ?> volumes</div>
    </div>
    <div class="stat-card stat-card-danger" style="padding: 1.5rem; border-left-color: #ef4444;">
        <h3 style="margin-bottom: 0; color: #ef4444;">Overdue Alert</h3>
        <div class="value" style="font-size: 1.5rem; color: #ef4444;"><?php echo $overdue_items_count; ?> critical</div>
    </div>
    <div class="stat-card" style="padding: 1.5rem; border-left-color: #10b981;">
        <h3 style="margin-bottom: 0;">Weekly Activity</h3>
        <div class="value" style="font-size: 1.5rem; color: #10b981;"><?php echo $weekly_new_count; ?> new loans</div>
    </div>
</div>

<!-- Interface Toolbar -->
<div class="toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <form action="" method="GET" style="display: flex; gap: 0.5rem; flex: 1; min-width: 300px; max-width: 700px;">
        <input type="text" name="search" placeholder="Search for user or book title..." class="search-input" value="<?php echo htmlspecialchars($search_query); ?>" style="width: 250px;">
        <select name="filter" class="search-input" style="width: 180px; padding: 0.75rem;">
            <option value="">Filter: All History</option>
            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending Returns</option>
            <option value="overdue" <?php echo $status_filter == 'overdue' ? 'selected' : ''; ?>>Overdue Only</option>
            <option value="closed" <?php echo $status_filter == 'closed' ? 'selected' : ''; ?>>Fully Closed</option>
        </select>
        <button type="submit" class="btn btn-primary">Refresh Log</button>
    </form>
    
    <a href="borrow.php" class="btn btn-primary">
        + Create New Transaction
    </a>
</div>

<!-- Records Table -->
<div class="table-container" style="overflow-x: auto;">
    <table class="datatable">
        <thead>
            <tr>
                <th width="80">LOG ID</th>
                <th style="text-align: left;">Borrower Name</th>
                <th style="text-align: left;">Book (Barcode)</th>
                <th>Loan Date</th>
                <th>Due Date</th>
                <th>Current Status</th>
                <th style="text-align: center;">Management</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($transactions_result) == 0): ?>
                <tr><td colspan="7" style="text-align: center; padding: 3rem; color: #64748b;">No circulation history found for your search.</td></tr>
            <?php else: ?>
                <?php while ($log = mysqli_fetch_assoc($transactions_result)): 
                    $today_date = new DateTime();
                    $due_date_obj = new DateTime($log['due_date']);
                    
                    // Logic to determine status appearance
                    if ($log['item_status'] == 'returned') {
                        $badge_style = "background:#dcfce7; color:#10b981;";
                        $badge_label = "Returned";
                    } else if ($today_date > $due_date_obj) {
                        $days_diff = $today_date->diff($due_date_obj)->days;
                        $badge_style = "background:#fee2e2; color:#ef4444;";
                        $badge_label = "Overdue ($days_diff days)";
                    } else {
                        $badge_style = "background:#e0f2fe; color:#0284c7;";
                        $badge_label = "Borrowed";
                    }

                    // Warning for readers with multiple overdues
                    $has_multiple_overdues = $log['reader_overdue_warning'] >= 2;
                ?>
                    <tr>
                        <td align="center" style="font-weight: 600; color: #94a3b8; font-family: monospace;">#<?php echo $log['detail_id']; ?></td>
                        
                        <td>
                            <div style="font-weight: 600; color: var(--text-color);">
                                <?php echo htmlspecialchars($log['reader_name']); ?>
                                <?php if($has_multiple_overdues && $log['item_status'] == 'borrowed'): ?>
                                    <span style="color: #ef4444; font-size: 0.75rem; margin-left: 0.5rem; font-weight: 700;">[ RISK ]</span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td>
                            <div style="font-weight: 500; color: var(--text-color);">
                                <?php echo htmlspecialchars($log['book_title']); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: #94a3b8; font-family: monospace;">
                                Barcode: <?php echo htmlspecialchars($log['barcode']); ?>
                            </div>
                        </td>

                        <td align="center" style="color: #64748b; font-size: 0.9rem;">
                            <?php echo date('M d, Y', strtotime($log['borrow_date'])); ?>
                        </td>

                        <td align="center" style="font-weight: 600; font-size: 0.9rem; <?php echo ($today_date > $due_date_obj && $log['item_status'] == 'borrowed') ? 'color: var(--danger);' : 'color: #64748b;'; ?>">
                            <?php echo date('M d, Y', strtotime($log['due_date'])); ?>
                        </td>

                        <td align="center">
                            <span class="badge" style="<?php echo $badge_style; ?> padding: 0.3rem 0.8rem; min-width: 100px; text-align: center;">
                                <?php echo $badge_label; ?>
                            </span>
                        </td>

                        <td align="center">
                            <div class="action-buttons-group">
                                <a href="loan_detail.php?id=<?php echo $log['loan_id']; ?>" style="color: #64748b; text-decoration: none;">View</a>
                                <span style="color: #e2e8f0;">|</span>
                                <?php if($log['item_status'] == 'borrowed'): ?>
                                    <a href="javascript:void(0)" onclick="confirmReturn(<?php echo $log['detail_id']; ?>, '<?php echo addslashes($log['book_title']); ?>', '<?php echo addslashes($log['reader_name']); ?>')" style="color: #10b981; text-decoration: none;">Return</a>
                                    <span style="color: #e2e8f0;">|</span>
                                <?php endif; ?>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $log['detail_id']; ?>, '<?php echo addslashes($log['book_title']); ?>', 'transaction', 'loan_delete.php')" style="color: #ef4444; text-decoration: none;">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../inc/footer.php'; ?>
