<?php
/**
 * Personal Account Profile
 * Allows staff members to view their credentials and update their system password.
 */
require_once '../env/config.php';
include '../inc/header.php';

// Initialization
$current_authenticated_user_id = $_SESSION['user_id'];
$validation_error = '';
$success_notification = '';

/**
 * Handle Password Update (POST)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password_input = $_POST['current_password'];
    $new_password_input = $_POST['new_password'];
    $confirm_password_input = $_POST['confirm_password'];

    // Validation Logic
    if ($new_password_input !== $confirm_password_input) {
        $validation_error = "The new password and confirmation do not match.";
    } elseif (strlen($new_password_input) < 3) {
        $validation_error = "Security requirement: Password must be at least 3 characters.";
    } else {
        // Verify current credentials
        $account_lookup_sql = "SELECT password FROM users WHERE id = ?";
        $lookup_stmt = mysqli_prepare($db_connect, $account_lookup_sql);
        mysqli_stmt_bind_param($lookup_stmt, "i", $current_authenticated_user_id);
        mysqli_stmt_execute($lookup_stmt);
        $lookup_res = mysqli_stmt_get_result($lookup_stmt);
        $user_credentials = mysqli_fetch_assoc($lookup_res);

        if (password_verify($current_password_input, $user_credentials['password'])) {
            // Update to new credentials
            $new_password_hash = password_hash($new_password_input, PASSWORD_DEFAULT);
            $commit_sql = "UPDATE users SET password = ? WHERE id = ?";
            $commit_stmt = mysqli_prepare($db_connect, $commit_sql);
            mysqli_stmt_bind_param($commit_stmt, "si", $new_password_hash, $current_authenticated_user_id);
            
            if (mysqli_stmt_execute($commit_stmt)) {
                $success_notification = "Your account password has been updated across the system.";
            } else {
                $validation_error = "Server error occurred during credential update.";
            }
        } else {
            $validation_error = "Authenticity failure: The current password you entered is incorrect.";
        }
    }
}

/**
 * Fetch Current Profile Data
 */
$profile_data_sql = "SELECT * FROM users WHERE id = ?";
$profile_stmt = mysqli_prepare($db_connect, $profile_data_sql);
mysqli_stmt_bind_param($profile_stmt, "i", $current_authenticated_user_id);
mysqli_stmt_execute($profile_stmt);
$profile_info = mysqli_fetch_assoc(mysqli_stmt_get_result($profile_stmt));
?>

<div class="breadcrumb" style="margin-bottom: 2rem; color: #64748b; font-size: 0.9rem;">
    Home / <strong style="color: var(--text-color);">My Profile</strong>
</div>

<div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; max-width: 1000px; margin: 0 auto;">
    
    <!-- Profile Overview Sidebar -->
    <div class="form-col">
        <div class="form-card" style="margin: 0; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center; padding: 2.5rem 1.5rem;">
            <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; margin: 0 auto 1.5rem;">
                <?php echo strtoupper(substr($profile_info['full_name'], 0, 1)); ?>
            </div>
            
            <h2 style="margin: 0 0 0.5rem; font-size: 1.5rem; color: var(--text-color);"><?php echo htmlspecialchars($profile_info['full_name']); ?></h2>
            <div style="display: inline-block; padding: 0.25rem 0.75rem; background: #e0f2fe; color: #0369a1; border-radius: 99px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                <?php echo $_SESSION['role_name']; ?> Authorization
            </div>
            
            <div style="margin-top: 2.5rem; text-align: left; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; margin-bottom: 0.25rem;">System Handle</label>
                    <div style="font-weight: 600; color: #475569;">@<?php echo htmlspecialchars($profile_info['username']); ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; margin-bottom: 0.25rem;">Staff Since</label>
                    <div style="font-weight: 600; color: #475569;"><?php echo date('M d, Y', strtotime($profile_info['created_at'])); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Credential Management -->
    <div class="form-col">
        <div class="form-card" style="margin: 0; border: 1px solid #e2e8f0; padding: 2rem;">
            <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem; border-left: 4px solid var(--primary-color); padding-left: 1rem;">Security Settings</h2>
            
            <?php if ($validation_error): ?>
                <div style="padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 1.5rem; font-size: 0.9rem;">
                    <?php echo $validation_error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_notification): ?>
                <div style="padding: 1rem; background: #dcfce7; color: #166534; border-radius: 8px; border: 1px solid #bbf7d0; margin-bottom: 1.5rem; font-size: 0.9rem;">
                    <?php echo $success_notification; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label>Current Credentials Verification *</label>
                    <input type="password" name="current_password" required placeholder="Type your existing password" style="width: 100%;">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="form-group">
                        <label>New Secret Key *</label>
                        <input type="password" name="new_password" required placeholder="Minimum 3 characters" style="width: 100%;">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Secret *</label>
                        <input type="password" name="confirm_password" required placeholder="Re-type new password" style="width: 100%;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-weight: 700;">
                    Update Profile Credentials
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../inc/footer.php'; ?>
