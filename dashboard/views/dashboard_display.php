<?php
/**
 * Dashboard — View
 */
$is_admin = ($role_id === 1);
?>

<div class="dashboard-greeting" style="margin-bottom:2rem;">
    <h1 style="font-size:2rem; color:var(--text-color); margin:0 0 0.35rem; font-weight:700; letter-spacing:-0.02em;">
        <?php echo $time_of_day_greeting; ?>
    </h1>
    <p style="color:var(--text-muted); font-size:0.95rem; margin:0;">
        <?php echo $is_admin ? 'System administration overview.' : "Today's circulation summary."; ?>
    </p>
</div>

<?php if ($is_admin && $pending_requests > 0): ?>
<a href="<?php echo BASE_URL; ?>request_management/requests.php"
   style="display:flex; align-items:center; gap:0.75rem; margin-bottom:2rem; padding:0.85rem 1.1rem;
          background:#fffbeb; border:1px solid #fcd34d; border-radius:10px; text-decoration:none; color:#92400e;">
    <span style="font-size:1.25rem;">⚠️</span>
    <span style="font-size:0.9rem;">
        <strong><?php echo $pending_requests; ?></strong>
        system request<?php echo $pending_requests !== 1 ? 's' : ''; ?> need your approval
        <span style="color:#b45309; font-weight:600;"> → Review now</span>
    </span>
</a>
<?php elseif (!$is_admin && $pending_requests > 0): ?>
<a href="<?php echo BASE_URL; ?>request_management/requests.php"
   style="display:flex; align-items:center; gap:0.75rem; margin-bottom:2rem; padding:0.85rem 1.1rem;
          background:#fffbeb; border:1px solid #fcd34d; border-radius:10px; text-decoration:none; color:#92400e;">
    <span style="font-size:1.25rem;">⚠️</span>
    <span style="font-size:0.9rem;">
        <strong><?php echo $pending_requests; ?></strong>
        borrow/return request<?php echo $pending_requests !== 1 ? 's' : ''; ?> awaiting approval
        <span style="color:#b45309; font-weight:600;"> → Review now</span>
    </span>
</a>
<?php endif; ?>

<h2 class="dashboard-section-title">Overview</h2>

<div class="stats-grid dashboard-overview" style="margin-bottom:2rem;">

    <?php if ($is_admin): ?>
    <a href="<?php echo BASE_URL; ?>book/books.php"
       class="stat-card" style="border-left:4px solid var(--primary-color); text-decoration:none; color:inherit; display:block;">
        <h3>Book Inventory</h3>
        <div class="value"><?php echo $total_inventory_count; ?></div>
        <p class="stat-card-detail">
            <span style="color:var(--success); font-weight:600;"><?php echo $ready_for_loan_count; ?> available</span>
            ·
            <span style="color:var(--danger); font-weight:600;"><?php echo $checked_out_count; ?> out</span>
            · <span style="font-weight:600;">View →</span>
        </p>
    </a>
    <?php else: ?>
    <div class="stat-card" style="border-left:4px solid var(--primary-color);">
        <h3>Book Inventory</h3>
        <div class="value"><?php echo $total_inventory_count; ?></div>
        <p class="stat-card-detail">
            <span style="color:var(--success); font-weight:600;"><?php echo $ready_for_loan_count; ?> available</span>
            ·
            <span style="color:var(--danger); font-weight:600;"><?php echo $checked_out_count; ?> out</span>
        </p>
    </div>
    <?php endif; ?>

    <div class="stat-card" style="border-left:4px solid #0ea5e9;">
        <h3 style="color:#0284c7;"><?php echo $is_admin ? 'People' : 'Readers'; ?></h3>
        <div class="value" style="color:#0ea5e9;"><?php echo $total_registered_readers; ?></div>
        <p class="stat-card-detail">
            <?php echo $total_registered_readers; ?> reader profile<?php echo $total_registered_readers !== 1 ? 's' : ''; ?>
            <?php if ($is_admin && $total_accounts !== null): ?>
                <br><span style="color:#7c3aed; font-weight:600;"><?php echo $total_accounts; ?> login account<?php echo $total_accounts !== 1 ? 's' : ''; ?></span>
                <span class="stat-card-note"> (excl. you)</span>
            <?php endif; ?>
        </p>
    </div>

    <?php if ($is_admin): ?>
    <a href="<?php echo BASE_URL; ?>loan/loans.php"
       class="stat-card <?php echo $active_loans_count > 0 ? 'stat-card-danger' : ''; ?>"
       style="border-left:4px solid var(--danger); text-decoration:none; color:inherit; display:block;">
        <h3 style="color:var(--danger);">Active Loans</h3>
        <div class="value" style="color:var(--danger);"><?php echo $active_loans_count; ?></div>
        <p class="stat-card-detail">Ongoing or partial · <span style="font-weight:600;">View →</span></p>
    </a>

    <a href="<?php echo BASE_URL; ?>request_management/requests.php"
       class="stat-card <?php echo $pending_requests > 0 ? 'stat-card-danger' : ''; ?>"
       style="border-left:4px solid #f59e0b; text-decoration:none; color:inherit; display:block;">
        <h3 style="color:#d97706;">System Requests</h3>
        <div class="value" style="color:#f59e0b;"><?php echo $pending_requests; ?></div>
        <p class="stat-card-detail">Librarian signup &amp; password reset · <span style="font-weight:600;">Review →</span></p>
    </a>
    <?php else: ?>
    <a href="<?php echo BASE_URL; ?>loan/loans.php"
       class="stat-card <?php echo $active_loans_count > 0 ? 'stat-card-danger' : ''; ?>"
       style="border-left:4px solid var(--danger); text-decoration:none; color:inherit; display:block;">
        <h3 style="color:var(--danger);">Active Loans</h3>
        <div class="value" style="color:var(--danger);"><?php echo $active_loans_count; ?></div>
        <p class="stat-card-detail">Ongoing or partial · <span style="font-weight:600;">Manage →</span></p>
    </a>

    <a href="<?php echo BASE_URL; ?>request_management/requests.php"
       class="stat-card <?php echo $pending_requests > 0 ? 'stat-card-danger' : ''; ?>"
       style="border-left:4px solid #f59e0b; text-decoration:none; color:inherit; display:block;">
        <h3 style="color:#d97706;">Pending Requests</h3>
        <div class="value" style="color:#f59e0b;"><?php echo $pending_requests; ?></div>
        <p class="stat-card-detail">Borrow &amp; return · <span style="font-weight:600;">Review →</span></p>
    </a>
    <?php endif; ?>

</div>

<h2 class="dashboard-section-title">Quick Actions</h2>
<div class="quick-actions-grid <?php echo $is_admin ? 'quick-actions-admin' : ''; ?>" style="margin-bottom:2rem;">

<?php if ($is_admin): ?>

    <a href="<?php echo BASE_URL; ?>account/accounts.php" class="action-tile tile-gray" title="Manage accounts">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <span>Account Management</span>
    </a>

    <a href="<?php echo BASE_URL; ?>request_management/requests.php" class="action-tile tile-secondary" title="System requests">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
            </svg>
        </div>
        <span>System Requests</span>
    </a>

    <a href="<?php echo BASE_URL; ?>system/settings.php" class="action-tile tile-primary" title="Library settings">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"></path>
            </svg>
        </div>
        <span>Settings</span>
    </a>

<?php else: ?>

    <a href="<?php echo BASE_URL; ?>book/book_add.php" class="action-tile tile-primary" title="Register new book">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                <line x1="12" y1="6" x2="12" y2="10"></line>
                <line x1="10" y1="8" x2="14" y2="8"></line>
            </svg>
        </div>
        <span>Add Book</span>
    </a>

    <a href="<?php echo BASE_URL; ?>loan/borrow.php" class="action-tile tile-secondary" title="Process outgoing loan">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
        </div>
        <span>Borrow Book</span>
    </a>

    <a href="<?php echo BASE_URL; ?>loan/loans.php" class="action-tile tile-success" title="Manage returns">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 2v6h-6"></path>
                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                <path d="M3 2v6h6"></path>
            </svg>
        </div>
        <span>Return Book</span>
    </a>

    <a href="<?php echo BASE_URL; ?>reader_management/readers.php" class="action-tile tile-gray" title="Manage readers">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <span>Reader Management</span>
    </a>

<?php endif; ?>

</div>
