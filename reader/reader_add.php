<?php
/**
 * Reader Registration and Profile Update
 * Manages the creation and modification of library reader profiles.
 */
require_once '../env/config.php';
include '../inc/header.php';

// Initialization
$reader_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$status_message = '';
$status_type = '';

// Default structure for reader data
$reader_data = [
    'name' => '', 'phone' => '', 'email' => '', 'address' => '', 
    'dob' => '', 'gender' => 'male', 'status' => 'active'
];

/**
 * Load existing reader data if an ID is provided (Edit Mode)
 */
if ($reader_id) {
    $fetch_sql = "SELECT * FROM readers WHERE id = ?";
    $fetch_stmt = mysqli_prepare($db_connect, $fetch_sql);
    mysqli_stmt_bind_param($fetch_stmt, "i", $reader_id);
    mysqli_stmt_execute($fetch_stmt);
    $fetch_result = mysqli_stmt_get_result($fetch_stmt);
    if ($found_row = mysqli_fetch_assoc($fetch_result)) {
        $reader_data = $found_row;
    }
}

/**
 * Process Form Submission (POST)
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize user inputs
    $input_name    = trim($_POST['name']);
    $input_phone   = trim($_POST['phone']);
    $input_email   = trim($_POST['email']);
    $input_address = trim($_POST['address']);
    $input_dob     = $_POST['dob'];
    $input_gender  = $_POST['gender'];
    $input_status  = $_POST['status'];

    // Basic Validation
    if (empty($input_name)) {
        $status_message = "Reader name is a required field.";
        $status_type = "error";
    } else {
        mysqli_begin_transaction($db_connect);
        try {
            if ($reader_id) {
                // Update Existing Profile
                $update_sql = "UPDATE readers SET name=?, phone=?, email=?, address=?, dob=?, gender=?, status=? WHERE id=?";
                $update_stmt = mysqli_prepare($db_connect, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "sssssssi", $input_name, $input_phone, $input_email, $input_address, $input_dob, $input_gender, $input_status, $reader_id);
                mysqli_stmt_execute($update_stmt);
                
                mysqli_commit($db_connect);
                $status_message = "Reader profile updated successfully.";
                $status_type = "success";
                
                // Refresh local state for immediate UI feedback
                $reader_data = [
                    'name'    => $input_name, 
                    'phone'   => $input_phone, 
                    'email'   => $input_email, 
                    'address' => $input_address, 
                    'dob'     => $input_dob, 
                    'gender'  => $input_gender, 
                    'status'  => $input_status
                ];
            } else {
                // Insert New Profile
                $insert_sql = "INSERT INTO readers (name, phone, email, address, dob, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = mysqli_prepare($db_connect, $insert_sql);
                mysqli_stmt_bind_param($insert_stmt, "sssssss", $input_name, $input_phone, $input_email, $input_address, $input_dob, $input_gender, $input_status);
                mysqli_stmt_execute($insert_stmt);
                
                mysqli_commit($db_connect);
                $status_message = "New reader registered successfully.";
                $status_type = "success";
            }
        } catch (Exception $e) {
            mysqli_rollback($db_connect);
            $status_message = "Database operation failed: " . $e->getMessage();
            $status_type = "error";
        }
    }
}
?>

<div class="breadcrumb" style="margin-bottom: 2rem; color: #64748b; font-size: 0.9rem;">
    Home / Reader Management / <strong style="color: var(--text-color);"><?php echo $reader_id ? 'Edit Profile' : 'New Registration'; ?></strong>
</div>

<div class="form-wrapper" style="max-width: 650px; margin: 0 auto; padding-bottom: 2rem;">
    
    <!-- Page Title and Navigation -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.5rem;"><?php echo $reader_id ? 'Update Member Profile' : 'Register Member'; ?></h1>
        <a href="readers.php" class="btn" style="background: #f1f5f9; color: #475569; font-size: 0.9rem;">&larr; Back to Directory</a>
    </div>

    <!-- Registration Form -->
    <form action="" method="POST" id="mainReaderForm" autocomplete="off">
        <div class="form-card" style="padding: 1.5rem; border-radius: 12px;">
            
            <h3 class="form-section-title">Personal Information</h3>
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($reader_data['name']); ?>" required placeholder="Enter full name">
            </div>

            <div class="form-grid-three">
                <div class="form-group">
                    <label>Birth Date</label>
                    <input type="date" id="dob_input" name="dob" value="<?php echo $reader_data['dob']; ?>">
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                        <label style="font-weight: normal; cursor: pointer;">
                            <input type="radio" name="gender" value="male" <?php echo ($reader_data['gender'] == 'male') ? 'checked' : ''; ?>> Male
                        </label>
                        <label style="font-weight: normal; cursor: pointer;">
                            <input type="radio" name="gender" value="female" <?php echo ($reader_data['gender'] == 'female') ? 'checked' : ''; ?>> Female
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Account Status</label>
                    <select name="status" style="width: 100%;">
                        <option value="active" <?php echo ($reader_data['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($reader_data['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <h3 class="form-section-title" style="margin-top: 2rem;">Contact Details</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($reader_data['email']); ?>" required placeholder="user@example.com">
                </div>
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($reader_data['phone']); ?>" required placeholder="Contact number">
                </div>
            </div>

            <div class="form-group" style="margin-top: 1.5rem;">
                <label>Physical Address</label>
                <textarea name="address" rows="3" placeholder="Residential address..."><?php echo htmlspecialchars($reader_data['address']); ?></textarea>
            </div>

            <!-- Submission Actions -->
            <div style="display: flex; gap: 1.5rem; margin-top: 2.5rem;">
                <button type="submit" id="submit_btn" class="btn btn-primary" style="flex: 1; padding: 1.1rem; font-weight: 700;">
                    <?php echo $reader_id ? 'Update Record' : 'Create Member Profile'; ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
/**
 * Visual feedback for form processing
 */
document.getElementById('mainReaderForm').addEventListener('submit', function() {
    const btn = document.getElementById('submit_btn');
    btn.innerHTML = 'Saving Data...';
    btn.disabled = true;
    btn.style.opacity = '0.7';
});

/**
 * Server-side interaction notifications
 */
<?php if($status_message != ''): ?>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: '<?php echo $status_type; ?>',
            title: '<?php echo $status_type == "success" ? "Operation Successful" : "Notification"; ?>',
            text: '<?php echo addslashes($status_message); ?>',
            confirmButtonColor: 'var(--primary-color)'
        });
    });
<?php endif; ?>
</script>

<?php include '../inc/footer.php'; ?>
