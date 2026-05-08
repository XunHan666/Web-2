<?php
require_once '../env/config.php';
require_once 'sys_rules.php';
include '../inc/header.php';

// Authorization Check: Only Admin can access this page
if ($_SESSION['role_id'] != 1) {
    showAlert("Access Denied: Admin privileges required.", "error");
    echo "<script>window.location.href = '" . BASE_URL . "index.php';</script>";
    exit();
}

$status = '';
$status_type = 'success';

// Handle Setting Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $fine = $_POST['fine_per_day'];
    $duration = $_POST['max_loan_days'];
    $max_books = $_POST['max_books_per_reader'];

    mysqli_begin_transaction($db_connect);
    try {
        update_setting('fine_per_day', $fine);
        update_setting('max_loan_days', $duration);
        update_setting('max_books_per_reader', $max_books);
        
        mysqli_commit($db_connect);
        $status = "System configurations updated successfully!";
    } catch (Exception $e) {
        mysqli_rollback($db_connect);
        $status = "Error updating settings: " . $e->getMessage();
        $status_type = "error";
    }
}

// Fetch current values
$current_fine = get_setting('fine_per_day', '5000');
$current_duration = get_setting('max_loan_days', '5');
$current_max_books = get_setting('max_books_per_reader', '3');
?>

<div class="search-header">
    <h1 style="font-size: 1.5rem; color: var(--text-color);">System Configuration</h1>
</div>

<?php if ($status): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $status_type; ?>',
            title: 'System Update',
            text: '<?php echo $status; ?>',
            confirmButtonColor: '#3b82f6',
            showClass: { popup: '', backdrop: '' },
            hideClass: { popup: '', backdrop: '' }
        });
    </script>
<?php endif; ?>

<div class="split-form-container">
    <div class="form-col data-col">
        <div class="form-card" style="margin: 0; max-width: 100%;">
            <h2 class="form-section-title">Library Rules & Policy</h2>
            
            <form action="" method="POST">
                <input type="hidden" name="update_settings" value="1">
                <div class="form-grid-three">
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
                    <div class="form-group">
                        <label for="max_books_per_reader">Max Books Per Reader</label>
                        <input type="number" id="max_books_per_reader" name="max_books_per_reader" value="<?php echo htmlspecialchars($current_max_books); ?>" required>
                        <span class="reader-muted">Borrowing limit per member</span>
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

<?php include '../inc/footer.php'; ?>
