<?php
/**
 * Staff Registration Script
 * Allows administrators to create new librarian accounts.
 */
require_once '../env/config.php';
include '../inc/header.php';

/**
 * Authorization: Check if the current user has administrative permissions
 */
if ($_SESSION['role_id'] != 1) {
    showAlert("Administrative privileges are required to register new staff.", "error");
    echo "<script>window.location.href = '" . BASE_URL . "index.php';</script>";
    exit();
}

$submission_error = '';

/**
 * Handle Account Registration (POST)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and prepare inputs
    $username = mysqli_real_escape_string($db_connect, trim($_POST['username']));
    $full_name = mysqli_real_escape_string($db_connect, trim($_POST['full_name']));
    $plain_password = $_POST['password'];
    $assigned_role_id = (int)$_POST['role_id'];

    // Validation logic
    if (empty($username) || empty($plain_password) || empty($full_name)) {
        $submission_error = "Please fill in all required fields.";
    } else {
        // Check for duplicate usernames
        $duplicate_check_query = "SELECT id FROM users WHERE username = '$username'";
        $duplicate_check_res = mysqli_query($db_connect, $duplicate_check_query);
        
        if (mysqli_num_rows($duplicate_check_res) > 0) {
            $submission_error = "The username '@$username' is already taken. Please choose another.";
        } else {
            // Cryptographic Hashing and Storage
            $password_hash = password_hash($plain_password, PASSWORD_DEFAULT);
            $insert_user_sql = "
                INSERT INTO users (username, password, full_name, role_id, status) 
                VALUES ('$username', '$password_hash', '$full_name', $assigned_role_id, 'active')
            ";
            
            if (mysqli_query($db_connect, $insert_user_sql)) {
                showAlert("Account for '$full_name' has been created successfully.");
                echo "<script>setTimeout(() => { window.location.href = 'users.php'; }, 2000);</script>";
            } else {
                $submission_error = "Database insertion failed: " . mysqli_error($db_connect);
            }
        }
    }
}

/**
 * Fetch available roles (Excluding the Super Admin role for safety)
 */
$roles_lookup_res = mysqli_query($db_connect, "SELECT * FROM roles WHERE id != 1");
?>

<div class="breadcrumb" style="margin-bottom: 2rem; color: #64748b; font-size: 0.9rem;">
    Home / Staff Management / <strong style="color: var(--text-color);">New Enrollment</strong>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem;">Register Staff Member</h1>
        <a href="users.php" class="btn" style="background: #f1f5f9; color: #475569; font-size: 0.9rem;">&larr; Back to Directory</a>
    </div>

    <!-- Registration Form -->
    <div class="form-card" style="padding: 2rem; border-radius: 12px; border: 1px solid #e2e8f0;">
        <?php if ($submission_error): ?>
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca; font-size: 0.9rem;">
                <strong>Input Error:</strong> <?php echo $submission_error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group">
                    <label>Full Display Name *</label>
                    <input type="text" name="full_name" placeholder="e.g. John Doe" required style="width: 100%;">
                </div>
                
                <div class="form-group">
                    <label>System Username *</label>
                    <input type="text" name="username" placeholder="e.g. john.librarian" required style="width: 100%;">
                </div>

                <div class="form-group">
                    <label>Initial Login Password *</label>
                    <input type="password" name="password" placeholder="••••••••" required style="width: 100%;">
                    <small style="color: #94a3b8; font-size: 0.75rem;">Password will be securely hashed before storage.</small>
                </div>

                <div class="form-group">
                    <label>Assigned Authorization Role *</label>
                    <select name="role_id" required style="width: 100%;">
                        <?php while($role = mysqli_fetch_assoc($roles_lookup_res)): ?>
                            <option value="<?php echo $role['id']; ?>" selected>
                                <?php echo ucfirst($role['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div style="border-top: 1px solid #f1f5f9; padding-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-weight: 700;">
                    Initialize Staff Account
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../inc/footer.php'; ?>
