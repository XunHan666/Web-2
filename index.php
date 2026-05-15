<?php
/**
 * Dashboard - Controller
 */
require_once 'env/config.php';
include 'inc/header.php';

<<<<<<< Updated upstream
// KPIs
$total_inventory_count = mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM book_copies"))[0];
$ready_for_loan_count = mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM book_copies WHERE status = 'available'"))[0];
$total_registered_readers = mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM readers"))[0];
$pending_circulation_count = mysqli_fetch_array(mysqli_query($db_connect, "SELECT COUNT(*) FROM loans WHERE status IN ('ongoing', 'partial')"))[0];

// Top Titles
$top_titles_sql = "SELECT bk.title, COUNT(ld.id) as borrow_frequency FROM books bk JOIN book_copies bc ON bk.id = bc.book_id JOIN loan_details ld ON bc.id = ld.book_copy_id GROUP BY bk.title ORDER BY borrow_frequency DESC LIMIT 5";
$top_titles_recordset = mysqli_query($db_connect, $top_titles_sql);
=======
<?php
/**
 * System Management Dashboard
 * Provides quick action shortcuts and top performing title insights.
 */
require_once 'env/config.php';
include 'inc/header.php';

<?php
/**
 * System Management Dashboard
 * Provides quick action shortcuts and operational overview.
 */
require_once 'env/config.php';
include 'inc/header.php';
>>>>>>> Stashed changes

// Personalization Context
$staff_display_name = $_SESSION['full_name'] ?? 'Guest';
$staff_role_label = $_SESSION['role_name'] ?? 'Staff';
$time_of_day_greeting = "Welcome, " . $staff_display_name . "!";
$role_context_banner = "Operational Role: " . ucfirst($staff_role_label); // Đã thêm biến này để fix lỗi

<<<<<<< Updated upstream
include 'views/dashboard_display.php';
include 'inc/footer.php';
=======
<!-- Header Greeting Section -->
<div class="dashboard-greeting" style="margin-bottom: 2.5rem;">
    <h1 style="font-size: 1.85rem; color: var(--text-color); margin-bottom: 0.5rem; letter-spacing: -0.02em;">
        <?php echo $time_of_day_greeting; ?>
    </h1>
    <p style="color: #64748b; font-size: 1rem;">
        <?php echo $role_context_banner; ?>. Welcome to the library management console.
    </p>
</div>

<!-- Interface: Transaction Shortcuts -->
<h2 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 1.25rem; font-weight: 800;">Workflow Essentials</h2>
<div class="quick-actions-grid">
    <a href="<?php echo BASE_URL; ?>book/book_add.php" class="action-tile tile-primary" title="Register new acquisitions">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="2" fill="none"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path><line x1="12" y1="6" x2="12" y2="10"></line><line x1="10" y1="8" x2="14" y2="8"></line></svg>
        </div>
        <span>Inbound Intake</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>loan/borrow.php" class="action-tile tile-secondary" title="Process outgoing loans">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="2" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        </div>
        <span>Checkout Desk</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>loan/loans.php" class="action-tile tile-success" title="Manage returns & logging">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 2v6h-6"></path><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 2v6h6"></path></svg>
        </div>
        <span>Check-in Facility</span>
    </a>
    
    <a href="<?php echo BASE_URL; ?>reader/readers.php" class="action-tile tile-gray" title="Manage member database">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="2" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <span>Reader Registry</span>
    </a>
</div>



<?php include 'inc/footer.php'; ?>
>>>>>>> Stashed changes
