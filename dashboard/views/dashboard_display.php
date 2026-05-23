<?php
/**
 * Dashboard — View
 * Expects variables from dashboard.php (controller):
 *   $role_id, $time_of_day_greeting, $staff_role_label,
 *   $pending_circulation_count, $total_inventory_count,
 *   $ready_for_loan_count, $total_registered_readers,
 *   $pending_requests, $total_accounts (admin only)
 */
?>

<!-- Greeting -->
<div class="dashboard-greeting" style="margin-bottom:3rem;">
    <h1 style="font-size:2rem; color:var(--text-color); margin-bottom:0.5rem; font-weight:700; letter-spacing:-0.02em;">
        <?php echo $time_of_day_greeting; ?>
    </h1>
    <p style="color:var(--text-muted); font-size:1rem; display:flex; align-items:center; gap:0.5rem;">
        <span style="background:var(--primary-light); color:var(--primary-color); padding:4px 12px; border-radius:9999px; font-size:0.8rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">
            <?php echo $staff_role_label; ?>
        </span>
        <span>
            There are currently
            <?php if ($pending_circulation_count > 0): ?>
                <strong style="color:var(--danger);"><?php echo $pending_circulation_count; ?> active transactions</strong>
            <?php else: ?>
                <strong style="color:var(--success);">0 active loans</strong>
            <?php endif; ?>
            tracked in the system today.
        </span>
    </p>
</div>

<!-- Stats Grid -->
<h2 style="font-size:0.8rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-muted); margin-bottom:1.25rem; font-weight:700;">
    <?php echo $role_id === 1 ? 'Infrastructure Insights' : "Today's Overview"; ?>
</h2>
<div class="stats-grid compact-stats" style="margin-bottom:4rem;">

    <div class="stat-card" style="border-left:4px solid var(--primary-color);">
        <h3><?php echo $role_id === 1 ? 'Master Collection' : 'Book Copies'; ?></h3>
        <div class="value"><?php echo $total_inventory_count; ?></div>
        <p style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">Total physical volumes</p>
    </div>

    <div class="stat-card" style="border-left:4px solid var(--success);">
        <h3 style="color:var(--success);"><?php echo $role_id === 1 ? 'Loan Units' : 'Available'; ?></h3>
        <div class="value" style="color:var(--success);"><?php echo $ready_for_loan_count; ?></div>
        <p style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">Ready on shelves</p>
    </div>

    <div class="stat-card" style="border-left:4px solid #0ea5e9;">
        <h3 style="color:#0284c7;"><?php echo $role_id === 1 ? 'Member Base' : 'Readers'; ?></h3>
        <div class="value" style="color:#0ea5e9;"><?php echo $total_registered_readers; ?></div>
        <p style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">
            <?php echo $role_id === 1 ? 'Verified readers' : 'Registered members'; ?>
        </p>
    </div>

    <div class="stat-card <?php echo $pending_circulation_count > 0 ? 'stat-card-danger' : ''; ?>"
         style="border-left:4px solid var(--danger);">
        <h3 style="color:var(--danger);"><?php echo $role_id === 1 ? 'Outstanding Loans' : 'Active Loans'; ?></h3>
        <div class="value" style="color:var(--danger);"><?php echo $pending_circulation_count; ?></div>
        <p style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">Active tickets</p>
    </div>

    <?php if ($role_id === 1 && $total_accounts !== null): ?>
    <div class="stat-card" style="border-left:4px solid #8b5cf6;">
        <h3 style="color:#7c3aed;">Total Accounts</h3>
        <div class="value" style="color:#8b5cf6;"><?php echo $total_accounts; ?></div>
        <p style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">Excl. your account</p>
    </div>
    <?php endif; ?>

    <div class="stat-card <?php echo $pending_requests > 0 ? 'stat-card-danger' : ''; ?>"
         style="border-left:4px solid #f59e0b;">
        <h3 style="color:#d97706;"><?php echo $role_id === 1 ? 'Pending Requests' : 'Borrow/Return Requests'; ?></h3>
        <div class="value" style="color:#f59e0b;"><?php echo $pending_requests; ?></div>
        <p style="font-size:0.75rem; color:var(--text-muted); margin-top:5px;">Awaiting approval</p>
    </div>

</div>

<!-- Quick Actions -->
<h2 style="font-size:0.8rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-muted); margin-bottom:1.25rem; font-weight:700;">
    <?php echo $role_id === 1 ? 'Workflow Essentials' : 'Quick Actions'; ?>
</h2>
<div class="quick-actions-grid" style="margin-bottom:2rem;">

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
                <polyline points="10 9 9 9 8 9"></polyline>
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

    <?php if ($role_id === 1): ?>
    <a href="<?php echo BASE_URL; ?>account/accounts.php" class="action-tile tile-gray" title="Manage all accounts">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <span>Account Management</span>
    </a>
    <?php else: ?>
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
