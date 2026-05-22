<?php
/**
 * Editor Template for Staff Registration/Editing
 */
?>
<div class="breadcrumb" style="margin-bottom: 2rem; color: #64748b; font-size: 0.9rem;">
    Home / Staff Management / <strong style="color: var(--text-color);"><?php echo isset($user_id) ? 'Edit Record' : 'New Enrollment'; ?></strong>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem;"><?php echo isset($user_id) ? 'Modify Staff Account' : 'Register Staff Member'; ?></h1>
        <a href="users.php" class="btn" style="background: #f1f5f9; color: #475569; font-size: 0.9rem;">&larr; Back to Directory</a>
    </div>

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
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>" placeholder="e.g. John Doe" required style="width: 100%;">
                </div>
                <div class="form-group">
                    <label>System Username *</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" placeholder="e.g. john.librarian" required style="width: 100%;">
                </div>
                <div class="form-group">
                    <label><?php echo isset($user_id) ? 'New Password (Blank to keep)' : 'Initial Password *'; ?></label>
                    <input type="password" name="password" placeholder="••••••••" <?php echo isset($user_id) ? '' : 'required'; ?> style="width: 100%;">
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
            </div>
            
            <div style="border-top: 1px solid #f1f5f9; padding-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 1rem 3rem; font-weight: 700;">
                    <?php echo isset($user_id) ? 'Update Staff Record' : 'Initialize Account'; ?>
                </button>
            </div>
        </form>
    </div>
</div>
