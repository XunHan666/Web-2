<?php
/**
 * System Management Dashboard
 * Provides a high-level overview of library inventory, circulation metrics, and quick action shortcuts.
 */
require_once 'env/config.php';
include 'inc/header.php';

/**
 * Business Intelligence: Key Performance Indicators (KPIs)
 */

// 1. Total Physical Inventory (Aggregate count of all versions/copies)
$inventory_query = mysqli_query($db_connect, "SELECT COUNT(*) FROM book_copies");
$inventory_data = mysqli_fetch_array($inventory_query);
$total_inventory_count = $inventory_data[0];

// 2. Shelf Availability (Items currently present and ready for lending)
$available_query = mysqli_query($db_connect, "SELECT COUNT(*) FROM book_copies WHERE status = 'available'");
$available_data = mysqli_fetch_array($available_query);
$ready_for_loan_count = $available_data[0];

// 3. Registered Membership (Total user base)
$readers_query = mysqli_query($db_connect, "SELECT COUNT(*) FROM readers");
$readers_data = mysqli_fetch_array($readers_query);
$total_registered_readers = $readers_data[0];

// 4. Circulation Load (Current active and partially returned transactions)
$active_loans_query = mysqli_query($db_connect, "SELECT COUNT(*) FROM loans WHERE status IN ('ongoing', 'partial')");
$active_loans_data = mysqli_fetch_array($active_loans_query);
$pending_circulation_count = $active_loans_data[0];

/**
 * UI State: Dynamic personalization
 */
$staff_display_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'System User';
$staff_role_label = isset($_SESSION['role_name']) ? $_SESSION['role_name'] : 'Staff';
$time_of_day_greeting = "Good " . (date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening')) . ", " . $staff_display_name . "!";
$role_context_banner = "Operational Role: " . ucfirst($staff_role_label);
?>

<div class="dashboard-greeting" style="margin-bottom: 3rem;">
    <h1 style="font-size: 2rem; color: var(--text-color); margin-bottom: 0.5rem; font-weight: 700; letter-spacing: -0.02em;">
        <?php echo $time_of_day_greeting; ?>
    </h1>
    <p style="color: var(--text-muted); font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <span style="background: var(--primary-light); color: var(--primary-color); padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
            <?php echo $staff_role_label; ?>
        </span>
        <span>
            There are currently 
            <?php if ($pending_circulation_count > 0): ?>
                <strong style="color: var(--danger); font-weight: 700;">
                     <?php echo $pending_circulation_count; ?> active transactions
                </strong>
            <?php else: ?>
                <strong style="color: var(--success); font-weight: 700;">0 active pendings</strong>
            <?php endif; ?>
            tracked in the system today.
        </span>
    </p>
</div>

<h2 style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 1.25rem; font-weight: 700;">Infrastructure Insights</h2>
<div class="stats-grid compact-stats" style="margin-bottom: 4rem;">
    <div class="stat-card" style="border-left: 4px solid var(--primary-color);">
        <h3>Master Collection</h3>
        <div class="value"><?php echo $total_inventory_count; ?></div>
        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Total physical volumes</p>
    </div>
    
    <div class="stat-card" style="border-left: 4px solid var(--success);">
        <h3 style="color: var(--success);">Circulation Units</h3>
        <div class="value" style="color: var(--success);"><?php echo $ready_for_loan_count; ?></div>
        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Ready on shelves</p>
    </div>
    
    <div class="stat-card" style="border-left: 4px solid #0ea5e9;">
        <h3 style="color: #0284c7;">Member Base</h3>
        <div class="value" style="color: #0ea5e9;"><?php echo $total_registered_readers; ?></div>
        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Verified readers</p>
    </div>
    
    <div class="stat-card <?php echo ($pending_circulation_count > 0) ? 'stat-card-danger' : ''; ?>" style="border-left: 4px solid var(--danger);">
        <h3 style="color: var(--danger);">Outstanding Loans</h3>
        <div class="value" style="color: var(--danger);"><?php echo $pending_circulation_count; ?></div>
        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Active tickets</p>
    </div>
</div>

<h2 style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 1.25rem; font-weight: 700;">Workflow Essentials</h2>
<div class="quick-actions-grid" style="margin-bottom: 2rem;">
    <a href="<?php echo BASE_URL; ?>book/book_add.php" class="action-tile tile-primary" title="Register new acquisitions">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path><line x1="12" y1="6" x2="12" y2="10"></line><line x1="10" y1="8" x2="14" y2="8"></line></svg>
        </div>
        <span>Inbound Intake</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>loan/borrow.php" class="action-tile tile-secondary" title="Process outgoing loans">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        </div>
        <span>Checkout Desk</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>loan/loans.php" class="action-tile tile-success" title="Manage returns & logging">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2v6h-6"></path><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 2v6h6"></path></svg>
        </div>
        <span>Check-in Facility</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>reader/readers.php" class="action-tile tile-gray" title="Manage member database">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <span>Reader Registry</span>
    </a>
</div>

<?php include 'inc/footer.php'; ?>