<?php
/**
 * Loan Transaction Details
 * Provides a comprehensive view of a specific loan session, including all books borrowed and their return status.
 */
require_once '../env/config.php';
include '../inc/header.php';

// Initialization
$loan_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Error Handling: Missing ID
if (!$loan_id) {
    echo "<div style='max-width: 800px; margin: 2rem auto; text-align: center;'>";
    echo "<h2 style='color: #64748b;'>Invalid Transaction ID Provided</h2>";
    echo "<a href='loans.php' class='btn btn-primary'>Return to Log</a></div>";
    include '../inc/footer.php';
    exit;
}

/**
 * Fetch Main Loan Data (Master Record)
 */
$main_sql = "SELECT l.*, r.name as reader_name FROM loans l JOIN readers r ON l.reader_id = r.id WHERE l.id = ?";
$main_stmt = mysqli_prepare($db_connect, $main_sql);
mysqli_stmt_bind_param($main_stmt, "i", $loan_id);
mysqli_stmt_execute($main_stmt);
$main_result = mysqli_stmt_get_result($main_stmt);
$loan_data = mysqli_fetch_assoc($main_result);

// Error Handling: Record not found
if (!$loan_data) {
    showAlert("The requested loan transaction record was not found.", "error");
    echo '<div style="padding: 2rem; text-align: center;"><a href="loans.php" class="btn btn-primary">Return to Log</a></div>';
    include '../inc/footer.php';
    exit;
}

/**
 * Fetch Borrowed Items (Detail Records)
 */
$items_sql = "
    SELECT ld.*, bc.barcode, bk.title 
    FROM loan_details ld 
    JOIN book_copies bc ON ld.book_copy_id = bc.id 
    JOIN books bk ON bc.book_id = bk.id 
    WHERE ld.loan_id = ?
";
$items_stmt = mysqli_prepare($db_connect, $items_sql);
mysqli_stmt_bind_param($items_stmt, "i", $loan_id);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);
?>

<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / Loan Management / <strong style="color: var(--text-color);">Transaction #<?php echo $loan_id; ?></strong>
</div>

<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <a href="loans.php" style="text-decoration: none; color: #64748b; font-size: 0.9rem;">&larr; Back to Circulation Log</a>
            <h1 style="margin-top: 0.5rem; font-size: 1.75rem;">Loan Transaction #<?php echo $loan_id; ?></h1>
        </div>
        <div style="font-size: 0.85rem; color: #94a3b8; font-family: monospace;">UUID-REF: <?php echo md5($loan_id); ?></div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Card: Member & Period -->
        <div class="form-card" style="max-width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem;">
            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: #64748b; text-transform: uppercase;">Member Information</h3>
            <p style="margin-bottom: 0.75rem;"><strong>Reader Name:</strong> <?php echo htmlspecialchars($loan_data['reader_name']); ?></p>
            <p style="margin-bottom: 0.75rem;"><strong>Loan Date:</strong> <?php echo date('M d, Y', strtotime($loan_data['borrow_date'])); ?></p>
            <p style="margin-bottom: 0.75rem;"><strong>Return Deadline:</strong> <?php echo date('M d, Y', strtotime($loan_data['due_date'])); ?></p>
        </div>

        <!-- Card: Status & Management -->
        <div class="form-card" style="max-width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem;">
            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: #64748b; text-transform: uppercase;">Transaction Lifecycle</h3>
            <div style="margin-bottom: 1.5rem;">
                <strong>Session Status:</strong> 
                <span class="badge badge-<?php echo $loan_data['status']; ?>" style="padding: 0.3rem 0.8rem; margin-left: 0.5rem;">
                    <?php echo strtoupper(str_replace('_', ' ', $loan_data['status'])); ?>
                </span>
            </div>
            
            <?php if ($loan_data['status'] !== 'closed'): ?>
                <a href="return.php?loan_id=<?php echo $loan_id; ?>" class="btn btn-primary" style="display: block; text-align: center; padding: 0.85rem;">
                    Update Return Status
                </a>
            <?php else: ?>
                <div style="padding: 0.85rem; background: #dcfce7; border-radius: 8px; color: #16a34a; font-weight: 700; text-align: center; border: 1px solid #bbf7d0;">
                   Transaction Fully Completed
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Borrowed Items Breakdown -->
    <div style="margin-top: 3rem;">
        <h3 style="font-size: 1.1rem; margin-bottom: 1rem; border-left: 4px solid var(--primary-color); padding-left: 1rem;">Items Breakdown</h3>
        <div class="table-container">
            <table class="datatable">
                <thead>
                    <tr>
                        <th style="text-align: left;">Tracking Barcode</th>
                        <th style="text-align: left;">Publication Title</th>
                        <th>Item Status</th>
                        <th>Date Processed</th>
                        <th>Fine/Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($items_result) == 0): ?>
                        <tr><td colspan="5" style="text-align:center; padding: 3rem; color: #94a3b8;">No items listed in this transaction.</td></tr>
                    <?php else: ?>
                        <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                            <tr>
                                <td style="font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($item['barcode']); ?></td>
                                <td style="font-weight: 600; color: var(--text-color);">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </td>
                                <td align="center">
                                    <span class="badge badge-<?php echo ($item['status'] == 'returned') ? 'returned' : 'borrowing'; ?>" style="font-size: 0.75rem;">
                                        <?php echo strtoupper($item['status']); ?>
                                    </span>
                                </td>
                                <td align="center" style="color: #64748b; font-size: 0.85rem;">
                                    <?php echo $item['return_date'] ? date('M d, Y', strtotime($item['return_date'])) : '--'; ?>
                                </td>
                                <td align="center" style="font-weight: 600; color: var(--danger); font-size: 0.85rem;">
                                    <?php echo ($item['fine_amount'] > 0) ? number_format($item['fine_amount']) . " VNĐ" : '-'; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../inc/footer.php'; ?>
