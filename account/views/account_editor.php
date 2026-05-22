<?php
/**
 * Editor Template for Staff Registration/Editing
 */
?>
<div class="breadcrumb" style="margin-bottom: 2rem; color: #64748b; font-size: 0.9rem;">
    Home / Account Management / <strong style="color: var(--text-color);"><?php echo isset($account_id) ? 'Edit Record' : 'New Enrollment'; ?></strong>
</div>

<div class="form-wrapper" style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.5rem;"><?php echo isset($account_id) ? 'Modify Account' : 'Register Account'; ?></h1>
        <a href="accounts.php" class="btn" style="background: #f1f5f9; color: #475569;">&larr; Back to Directory</a>
    </div>

    <div class="form-card" style="padding: 2rem; border-radius: 12px; border: 1px solid #e2e8f0;">
        <?php if ($submission_error): ?>
            <div style="margin-bottom: 1.5rem; padding: 1rem; background: #fee2e2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca; font-size: 0.9rem;">
                <strong>Input Error:</strong> <?php echo $submission_error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="req_id" value="<?php echo isset($req_id) ? $req_id : 0; ?>">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group">
                    <label>Full Display Name *</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" placeholder="e.g. John Doe" required style="width: 100%;">
                </div>
                <div class="form-group">
                    <label>System Username *</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" placeholder="e.g. john.librarian" required style="width: 100%;">
                </div>
                <div class="form-group">
                    <label><?php echo isset($account_id) ? 'New Password (Blank to keep)' : 'Initial Password *'; ?></label>
                    <input type="password" name="password" placeholder="••••••••" <?php echo isset($account_id) ? '' : 'required'; ?> style="width: 100%;">
                </div>
                <div class="form-group">
                    <label>Assigned Role *</label>
                    <select name="role_id" required style="width: 100%;">
                        <?php while($role = mysqli_fetch_assoc($roles_lookup_res)): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo (isset($user_data['role_id']) && $user_data['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($role['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" placeholder="e.g. 0901234567" required style="width: 100%;">
                </div>
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" placeholder="e.g. name@example.com" required style="width: 100%;">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Physical Address *</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>" placeholder="e.g. 123 Main St" required style="width: 100%;">
                </div>
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" value="<?php echo htmlspecialchars($user_data['dob'] ?? ''); ?>" required style="width: 100%;">
                </div>
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" required style="width: 100%;">
                        <option value="male" <?php echo (isset($user_data['gender']) && $user_data['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo (isset($user_data['gender']) && $user_data['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                        <option value="other" <?php echo (isset($user_data['gender']) && $user_data['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>
            
            <div style="border-top: 1px solid #f1f5f9; padding-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-weight: 700;">
                    <?php echo isset($account_id) ? 'Update Account Record' : 'Initialize Account'; ?>
                </button>
            </div>
        </form>
    </div>
</div>
