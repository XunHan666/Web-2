<h1 class="rp-page-title">👤 My Profile</h1>
<p class="rp-page-sub">Manage your personal info and account security.</p>

<?php if ($success): ?><div class="rp-alert-ok">✅ <?php echo htmlspecialchars($success); ?></div><?php endif; ?>
<?php if ($errors):  ?><div class="rp-alert-err"><ul><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<!-- Personal Info -->
<div class="rp-card">
    <h2>Personal Information</h2>
    <div class="rp-field">
        <label>Username (cannot change)</label>
        <input type="text" value="<?php echo htmlspecialchars($username); ?>" disabled>
    </div>
    <form method="POST">
        <input type="hidden" name="action" value="update_profile">
        <div class="rp-field"><label>Full Name *</label><input type="text" name="name" value="<?php echo htmlspecialchars($reader['name']); ?>" required></div>
        <div class="rp-form-row">
            <div class="rp-field"><label>Email *</label><input type="email" name="email" value="<?php echo htmlspecialchars($reader['email']); ?>" required></div>
            <div class="rp-field"><label>Phone *</label><input type="text" name="phone" value="<?php echo htmlspecialchars($reader['phone']); ?>" required></div>
        </div>
        <button type="submit" class="rp-btn">Save Changes</button>
    </form>
</div>

<!-- Change Password -->
<div class="rp-card">
    <h2>Change Password</h2>
    <form method="POST">
        <input type="hidden" name="action" value="change_password">
        <div class="rp-field"><label>Current Password</label><input type="password" name="current_password" required placeholder="••••••••"></div>
        <div class="rp-form-row">
            <div class="rp-field"><label>New Password</label><input type="password" name="new_password" required placeholder="Min. 6 chars"></div>
            <div class="rp-field"><label>Confirm New Password</label><input type="password" name="confirm_password" required placeholder="Repeat"></div>
        </div>
        <button type="submit" class="rp-btn rp-btn-ghost">Change Password</button>
    </form>
</div>
