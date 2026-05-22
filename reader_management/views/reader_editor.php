<?php
/**
 * Editor Template for Reader Registration/Editing
 */
?>
<div class="breadcrumb" style="margin-bottom: 2rem; color: #64748b; font-size: 0.9rem;">
    Home / Reader / <strong style="color: var(--text-color);"><?php echo $reader_id ? 'Edit Profile' : 'New Registration'; ?></strong>
</div>

<div class="form-wrapper" style="max-width: 650px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.5rem;"><?php echo $reader_id ? 'Update Member Profile' : 'Register Member'; ?></h1>
        <a href="readers.php" class="btn" style="background: #f1f5f9; color: #475569; font-size: 0.9rem;">&larr; Back to Directory</a>
    </div>

    <form action="" method="POST" autocomplete="off">
        <div class="form-card" style="padding: 1.5rem; border-radius: 12px;">
            <div class="form-group" style="margin-bottom:1rem;">
                <label>Full Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($reader_data['name']); ?>" required style="width: 100%;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom:1rem;">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($reader_data['email']); ?>" required style="width: 100%;">
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($reader_data['phone']); ?>" required style="width: 100%;">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label>Address *</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($reader_data['address']); ?>" required style="width: 100%;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom:1rem;">
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" value="<?php echo htmlspecialchars($reader_data['dob']); ?>" required style="width: 100%;">
                </div>
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" style="width: 100%;">
                        <option value="male" <?php echo $reader_data['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo $reader_data['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                        <option value="other" <?php echo $reader_data['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" style="width: 100%;">
                    <option value="active" <?php echo $reader_data['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $reader_data['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Save Profile</button>
            </div>
        </div>
    </form>
</div>
