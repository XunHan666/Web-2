<?php
/**
 * Return Processing Dashboard
 * Handles the check-in process for borrowed books, calculating late fines if applicable.
 */
require_once '../env/config.php';
require_once '../system/sys_rules.php';
include '../inc/header.php';

// Initialization
$loan_id = isset($_GET['loan_id']) ? (int)$_GET['loan_id'] : 0;

/**
 * Selection Screen: If no loan ID is provided
 */
if (!$loan_id) {
?>
    <div style="max-width: 800px; margin: 3rem auto; text-align: center; background: white; padding: 3rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
        <h2 style="color: var(--text-color); margin-bottom: 1rem;">Process Publication Return</h2>
        <p style="color: #64748b; margin-bottom: 2rem;">Search for a specific Loan ID below or return to the main log to select an active transaction.</p>
        
        <form action="return.php" method="GET" style="max-width: 450px; margin: 0 auto; display: flex; gap: 1rem;">
            <input type="number" name="loan_id" class="search-input" placeholder="Enter Registration ID (e.g. 7)" required style="flex: 1;">
            <button type="submit" class="btn btn-primary">Find Record</button>
        </form>
        
        <div style="margin-top: 2rem;">
            <a href="loans.php" class="btn" style="background: #f1f5f9; color: #475569;">&larr; View Active Circulation Log</a>
        </div>
    </div>
<?php
    include '../inc/footer.php';
    exit;
}

/**
 * Core Configuration: Fine Rates & Dates
 */
$fine_rate_settings = (int)get_setting('fine_per_day', 5000); // Default to 5000 VNĐ
$current_date = date('Y-m-d');

/**
 * Step 1: Fetch Master Transaction Data
 */
$loan_query = "SELECT l.*, r.name as reader_name, r.phone FROM loans l JOIN readers r ON l.reader_id = r.id WHERE l.id = ?";
$loan_stmt = mysqli_prepare($db_connect, $loan_query);
mysqli_stmt_bind_param($loan_stmt, "i", $loan_id);
mysqli_stmt_execute($loan_stmt);
$loan_res = mysqli_stmt_get_result($loan_stmt);
$loan_info = mysqli_fetch_assoc($loan_res);

// Safety Check: Validate record existence
if (!$loan_info) {
    showAlert("Critical Error: Transaction record #$loan_id was not found.", "error");
    echo '<div style="padding: 2rem; text-align: center;"><a href="loans.php" class="btn btn-primary">Return to Log</a></div>';
    include '../inc/footer.php';
    exit;
}

/**
 * Fine Assessment Logic
 */
$days_late_count = 0;
if ($current_date > $loan_info['due_date']) {
    $days_late_count = (strtotime($current_date) - strtotime($loan_info['due_date'])) / (60 * 60 * 24);
}
$calculated_fine = $days_late_count * $fine_rate_settings;

/**
 * Handle Mass Return Submission (POST)
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['return_details'])) {
    $selected_detail_ids = $_POST['return_details']; 

    if (!empty($selected_detail_ids)) {
        mysqli_begin_transaction($db_connect);
        try {
            foreach ($selected_detail_ids as $detail_id) {
                $detail_id = (int)$detail_id;
                
                // Get physical copy ID associated with this detail line
                $copy_lookup_res = mysqli_query($db_connect, "SELECT book_copy_id FROM loan_details WHERE id = $detail_id");
                $copy_lookup_data = mysqli_fetch_assoc($copy_lookup_res);
                $physical_copy_id = $copy_lookup_data['book_copy_id'];

                // Update detail record (Mark as returned)
                $update_detail_sql = "UPDATE loan_details SET return_date = ?, fine_amount = ?, status = 'returned' WHERE id = ?";
                $update_detail_stmt = mysqli_prepare($db_connect, $update_detail_sql);
                mysqli_stmt_bind_param($update_detail_stmt, "sdi", $current_date, $calculated_fine, $detail_id);
                mysqli_stmt_execute($update_detail_stmt);

                // Update inventory (Restore availability)
                mysqli_query($db_connect, "UPDATE book_copies SET status = 'available' WHERE id = $physical_copy_id");
            }

            // Recalculate Master Loan Status (Closed vs Partial)
            $check_status_res = mysqli_query($db_connect, "
                SELECT 
                    COUNT(*) as total_items,
                    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_count
                FROM loan_details WHERE loan_id = $loan_id
            ");
            $status_metrics = mysqli_fetch_assoc($check_status_res);
            
            $final_loan_status = 'active';
            if ($status_metrics['returned_count'] == $status_metrics['total_items']) {
                $final_loan_status = 'closed';
            } elseif ($status_metrics['returned_count'] > 0) {
                $final_loan_status = 'partial';
            }
            
            mysqli_query($db_connect, "UPDATE loans SET status = '$final_loan_status' WHERE id = $loan_id");
            mysqli_commit($db_connect);
            
            showAlert(count($selected_detail_ids) . " publication(s) successfully checked back into inventory.");
            echo "<script>setTimeout(() => { window.location.href = 'return.php?loan_id=$loan_id'; }, 2000);</script>";
        } catch (Exception $e) {
            mysqli_rollback($db_connect);
            showAlert("Action failed: " . $e->getMessage(), "error");
        }
    } else {
        showAlert("No selections detected. Please check the items you wish to return.", "error");
    }
}

/**
 * Step 2: Fetch Transaction Content (Detail Lines)
 */
$items_list_query = "
    SELECT d.*, bc.barcode, b.title 
    FROM loan_details d
    JOIN book_copies bc ON d.book_copy_id = bc.id
    JOIN books b ON bc.book_id = b.id
    WHERE d.loan_id = ?
";
$items_stmt = mysqli_prepare($db_connect, $items_list_query);
mysqli_stmt_bind_param($items_stmt, "i", $loan_id);
mysqli_stmt_execute($items_stmt);
$items_recordset = mysqli_stmt_get_result($items_stmt);
?>

<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / Loan Management / <strong style="color: var(--text-color);">Check-in Facility</strong>
</div>

<div style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem;">Process Return: Transaction #<?php echo $loan_info['id']; ?></h1>
        <a href="loans.php" class="btn" style="background: #f1f5f9; color: #475569; font-size: 0.9rem;">&larr; Back to Circulation Log</a>
    </div>

    <!-- Header Card: Borrower & Deadline -->
    <div class="form-card" style="margin-bottom: 2rem; background: #f8fafc; border: 1px solid #e2e8f0; padding: 1.5rem; border-radius: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h3 style="margin-bottom: 0.5rem; color: var(--text-color);">Reader: <?php echo htmlspecialchars($loan_info['reader_name']); ?></h3>
                <p style="color: #64748b; font-size: 0.9rem;">Contact: <?php echo htmlspecialchars($loan_info['phone']); ?></p>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 0.85rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Deadline</p>
                <p style="font-weight: 700; color: #475569;"><?php echo date('M d, Y', strtotime($loan_info['due_date'])); ?></p>
            </div>
        </div>
        
        <?php if ($days_late_count > 0): ?>
            <div style="padding: 1.25rem; background: #fee2e2; color: #991b1b; border-radius: 10px; margin-top: 1.5rem; border: 1px solid #fecaca;">
                <strong style="text-transform: uppercase; font-size: 0.8rem;">[ CRITICAL OVERDUE ALERT ]</strong><br>
                This transaction is <?php echo $days_late_count; ?> day(s) past the deadline. <br>
                Assessed fine per item: <span style="font-size: 1.1rem; font-weight: 800;"><?php echo number_format($calculated_fine); ?> VNĐ</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Interface: Check-in Table -->
    <form action="" method="POST" id="massReturnForm">
        <div class="table-container">
            <table class="datatable">
                <thead>
                    <tr>
                        <th width="40"><input type="checkbox" id="selectAllItems" style="width: 18px; height: 18px; cursor: pointer;"></th>
                        <th>Reference Barcode</th>
                        <th>Book Title</th>
                        <th>Current Status</th>
                        <th>Return Date</th>
                        <th>Fine Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $active_items_found = false;
                    while ($row = mysqli_fetch_assoc($items_recordset)): 
                    ?>
                        <tr>
                            <td align="center">
                                <?php if ($row['status'] == 'borrowed'): 
                                    $active_items_found = true;
                                ?>
                                    <input type="checkbox" name="return_details[]" class="item-checkbox" value="<?php echo $row['id']; ?>" style="width: 18px; height: 18px; cursor: pointer;">
                                <?php else: ?>
                                    <span style="color: #cbd5e1;">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-family: monospace; font-size: 0.85rem; color: #64748b;"><?php echo htmlspecialchars($row['barcode']); ?></td>
                            <td style="font-weight: 600; color: var(--text-color);"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td align="center">
                                <span class="badge" style="<?php 
                                    if($row['status'] == 'returned') {
                                        echo 'background:#dcfce7; color:#10b981;';
                                    } elseif($days_late_count > 0) {
                                        echo 'background:#fee2e2; color:#ef4444;';
                                    } else {
                                        echo 'background:#e0f2fe; color:#0284c7;';
                                    }
                                ?> min-width: 90px; text-align: center;">
                                    <?php echo strtoupper($row['status']); ?>
                                </span>
                            </td>
                            <td align="center" style="font-size: 0.85rem; color: #94a3b8;">
                                <?php echo $row['return_date'] ? date('M d, Y', strtotime($row['return_date'])) : '--'; ?>
                            </td>
                            <td align="center" style="font-weight: 700;">
                                <?php if ($row['status'] == 'returned'): ?>
                                    <span style="color: #64748b;"><?php echo number_format($row['fine_amount']); ?> VNĐ</span>
                                <?php elseif ($days_late_count > 0): ?>
                                    <span style="color:var(--danger);"><?php echo number_format($calculated_fine); ?> VNĐ (EST)</span>
                                <?php else: ?>
                                    0 VNĐ
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Submission Actions -->
        <?php if ($active_items_found): ?>
            <div style="margin-top: 1.5rem; text-align: right; background: #f8fafc; padding: 1.5rem; border-radius: 12px; border: 1px dashed #cbd5e1;">
                <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 1rem;">Verify the selected items belong to this borrower before confirming the check-in.</p>
                <button type="submit" id="submit_return_btn" class="btn btn-primary" style="padding: 1rem 2.5rem; font-weight: 700;">
                    Confirm Selected Check-ins
                </button>
            </div>
        <?php else: ?>
            <div style="margin-top: 2rem; text-align: center; color: #16a34a; font-weight: 700; padding: 2rem; background: #dcfce7; border-radius: 12px; border: 1px solid #bbf7d0;">
                All items in this transaction have been successfully returned & processed.
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
/**
 * Dynamic UI interactions
 */
document.getElementById('selectAllItems').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

document.getElementById('massReturnForm').addEventListener('submit', function(e) {
    const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
    if (checkedCount === 0) {
        e.preventDefault();
        Swal.fire('Selection Required', 'Please select at least one item to return.', 'warning');
        return;
    }
    
    document.getElementById('submit_return_btn').innerHTML = 'Processing...';
    document.getElementById('submit_return_btn').disabled = true;
});
</script>

<?php include '../inc/footer.php'; ?>
