<?php
/**
 * Staff Account Modifier
 * Allows administrators to update profile information and reset credentials for system staff.
 */
require_once '../env/config.php';
include '../inc/header.php';

/**
 * Authorization: Verify administrative permissions
 */
if ($_SESSION['role_id'] != 1) {
    showAlert("Administrative privileges are required to modify staff profiles.", "error");
    echo "<script>window.location.href = '" . BASE_URL . "index.php';</script>";
    exit();
}

// Initialization and Data Retrieval
$target_user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$update_error_msg = '';

/**
 * Handle Profile Updates (POST)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updated_full_name = mysqli_real_escape_string($db_connect, trim($_POST['full_name']));
    $updated_username = mysqli_real_escape_string($db_connect, trim($_POST['username']));
    $new_role_id = (int)$_POST['role_id'];
    $account_status = $_POST['status'];
    $reset_password = $_POST['new_password'];

    // Step 1: Update Primary Demographic Data
    $update_sql = "UPDATE users SET full_name = ?, username = ?, role_id = ?, status = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($db_connect, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ssisi", $updated_full_name, $updated_username, $new_role_id, $account_status, $target_user_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        $feedback_note = "Profile updated successfully.";

        // Step 2: Handle Optional Password Modification
        if (!empty($reset_password)) {
            $secured_password = password_hash($reset_password, PASSWORD_DEFAULT);
            mysqli_query($db_connect, "UPDATE users SET password = '$secured_password' WHERE id = $target_user_id");
            $feedback_note .= " Password has been reset.";
        }

        showAlert($feedback_note);
        echo "<script>setTimeout(() => { window.location.href = 'users.php'; }, 2000);</script>";
    } else {
        $update_error_msg = "Database Update Error: " . mysqli_error($db_connect);
    }
}

/**
 * Fetch Current User Record
 */
$user_lookup_res = mysqli_query($db_connect, "SELECT * FROM users WHERE id = $target_user_id");
$user_record = mysqli_fetch_assoc($user_lookup_res);

if (!$user_record) {
    showAlert("System Error: Staff record not found.", "error");
    echo "<script>window.location.href = 'users.php';</script>";
    exit();
}

/**
 * Fetch Available Roles (Admin safety check)
 */
$available_roles_res = mysqli_query($db_connect, "SELECT * FROM roles WHERE id != 1");
?>

<div class="breadcrumb" style="margin-bottom: 2rem; color: #64748b; font-size: 0.9rem;">
    Home / Staff Management / <strong style="color: var(--text-color);">Modify Profile</strong>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem;">Edit Staff: <?php echo htmlspecialchars($user_record['full_name']); ?></h1>
        <a href="users.php" class="btn" style="background: #f1f5f9; color: #475569; font-size: 0.9rem;">&larr; Back to Directory</a>
    </div>

    <!-- Interface Card -->
    <div class="form-card" style="padding: 2rem; border-radius: 12px; border: 1px solid #e2e8f0;">
        <?php if ($update_error_msg): ?>
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca; font-size: 0.9rem;">
                <?php echo $update_error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group">
                    <label>Full Legal Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_record['full_name']); ?>" required style="width: 100%;">
                </div>
                
                <div class="form-group">
                    <label>System Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user_record['username']); ?>" required style="width: 100%;">
                </div>
                
                <div class="form-group">
                    <label>Authorized Role</label>
                    <select name="role_id" required style="width: 100%;">
                        <?php while($role = mysqli_fetch_assoc($available_roles_res)): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo $role['id'] == $user_record['role_id'] ? 'selected' : ''; ?>>
                                <?php echo ucfirst($role['name']); ?>
                            </option>
                        <?php endwhile; ?>
                        <?php if ($user_record['role_id'] == 1): ?>
                            <option value="1" selected>Administrator (Immutable)</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Account Lifecycle Status</label>
                    <select name="status" required style="width: 100%;">
                        <option value="active" <?php echo $user_record['status'] == 'active' ? 'selected' : ''; ?>>Active License</option>
                        <option value="inactive" <?php echo $user_record['status'] == 'inactive' ? 'selected' : ''; ?>>Suspended / Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Password Reset Section -->
            <div style="background: #f7fee7; padding: 1.5rem; border-radius: 12px; border: 1px solid #d9f99d; margin-bottom: 2rem;">
                <h3 style="margin-top: 0; color: #3f6212; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Security Override</h3>
                <p style="color: #4d7c0f; font-size: 0.85rem; margin-bottom: 1rem; line-height: 1.4;">
                    Manual override for lost credentials. Leave the following field empty unless a password forced-reset is required by the user.
                </p>
                <div class="form-group" style="max-width: 400px; margin-bottom: 0;">
                    <input type="password" name="new_password" placeholder="Type new password to override..." style="width: 100%; border-color: #bef264;">
                </div>
            </div>
            
            <div style="border-top: 1px solid #f1f5f9; padding-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-weight: 700;">
                    Commit Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../inc/footer.php'; ?>
