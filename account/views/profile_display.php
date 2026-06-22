<h1 class="rp-page-title" style="margin-bottom:0.5rem; color:#1e293b;">👤 My Profile</h1>
<p class="rp-page-sub" style="color:#64748b; margin-bottom:2rem;">Manage your personal info and account security.</p>

<?php if ($success): ?><div style="padding:1rem; background:#dcfce7; color:#166534; border-radius:8px; margin-bottom:1.5rem;">✅ <?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<?php if ($errors):  ?><div style="padding:1rem; background:#fee2e2; color:#991b1b; border-radius:8px; margin-bottom:1.5rem;"><ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<!-- Personal Info -->
<div class="rp-card" style="background:white; padding:2rem; border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); border:1px solid #e2e8f0; margin-bottom:2rem;">
    <h2 style="margin-bottom:1.5rem; font-size:1.25rem; color:#334155; border-bottom:1px solid #e2e8f0; padding-bottom:0.5rem;">Personal Information</h2>
    <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:0.85rem; color:#64748b; font-weight:600; margin-bottom:0.5rem;">Username (cannot change)</label>
        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="width:100%; padding:0.75rem; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; color:#94a3b8;">
    </div>
    <form method="POST">
        <input type="hidden" name="action" value="update_profile">
        <div style="margin-bottom:1rem;">
            <label style="display:block; font-size:0.85rem; color:#475569; font-weight:600; margin-bottom:0.5rem;">Full Name *</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:8px;">
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1rem;">
            <div>
                <label style="display:block; font-size:0.85rem; color:#475569; font-weight:600; margin-bottom:0.5rem;">Email *</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:8px;">
            </div>
            <div>
                <label style="display:block; font-size:0.85rem; color:#475569; font-weight:600; margin-bottom:0.5rem;">Phone *</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:8px;">
            </div>
        </div>
        <div style="margin-bottom:1rem;">
            <label style="display:block; font-size:0.85rem; color:#475569; font-weight:600; margin-bottom:0.5rem;">Address *</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:8px;">
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
            <div>
                <label style="display:block; font-size:0.85rem; color:#475569; font-weight:600; margin-bottom:0.5rem;">Date of Birth *</label>
                <input type="date" name="dob" value="<?php echo htmlspecialchars($user['dob']); ?>" required style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:8px;">
            </div>
            <div>
                <label style="display:block; font-size:0.85rem; color:#475569; font-weight:600; margin-bottom:0.5rem;">Gender *</label>
                <select name="gender" required style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:8px; font-family:inherit;">
                    <option value="male" <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                    <option value="other" <?php echo $user['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="padding:0.75rem 2rem; font-weight:600; background:var(--primary-color); color:white; border:none; border-radius:8px; cursor:pointer;">Save Changes</button>
    </form>
</div>

<!-- Change Password -->
<div class="rp-card" style="background:white; padding:2rem; border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); border:1px solid #e2e8f0;">
    <h2 style="margin-bottom:1.5rem; font-size:1.25rem; color:#334155; border-bottom:1px solid #e2e8f0; padding-bottom:0.5rem;">Change Password</h2>
    <form method="POST">
        <input type="hidden" name="action" value="change_password">
        <div style="margin-bottom:1rem;">
            <label style="display:block; font-size:0.85rem; color:#475569; font-weight:600; margin-bottom:0.5rem;">Current Password</label>
            <input type="password" name="current_password" required placeholder="••••••••" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:8px;">
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
            <div>
                <label style="display:block; font-size:0.85rem; color:#475569; font-weight:600; margin-bottom:0.5rem;">New Password</label>
                <input type="password" name="new_password" required placeholder="Min. 6 chars" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:8px;">
            </div>
            <div>
                <label style="display:block; font-size:0.85rem; color:#475569; font-weight:600; margin-bottom:0.5rem;">Confirm New Password</label>
                <input type="password" name="confirm_password" required placeholder="Repeat" style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:8px;">
            </div>
        </div>
        <button type="submit" class="btn" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; padding:0.75rem 2rem; font-weight:600; border-radius:8px; cursor:pointer;">Change Password</button>
    </form>
</div>
