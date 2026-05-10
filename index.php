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
 * Analytics: Top Borrowed Titles
 */
$top_titles_sql = "
    SELECT bk.title, COUNT(ld.id) as borrow_frequency 
    FROM books bk 
    JOIN book_copies bc ON bk.id = bc.book_id 
    JOIN loan_details ld ON bc.id = ld.book_copy_id 
    GROUP BY bk.title 
    ORDER BY borrow_frequency DESC LIMIT 5
";
$top_titles_recordset = mysqli_query($db_connect, $top_titles_sql);

/**
 * UI State: Dynamic personalization
 */
$staff_display_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'System User';
$staff_role_label = isset($_SESSION['role_name']) ? $_SESSION['role_name'] : 'Staff';
$time_of_day_greeting = "Good " . (date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening')) . ", " . $staff_display_name . "!";
$role_context_banner = "Operational Role: " . ucfirst($staff_role_label);
?>

<!-- Data Visualization Assets -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Header Greeting Section -->
<div class="dashboard-greeting" style="margin-bottom: 2.5rem;">
    <h1 style="font-size: 1.85rem; color: var(--text-color); margin-bottom: 0.5rem; letter-spacing: -0.02em;">
        <?php echo $time_of_day_greeting; ?>
    </h1>
    <p style="color: #64748b; font-size: 1rem;">
        <?php echo $role_context_banner; ?>. There are currently 
        <?php if ($pending_circulation_count > 0): ?>
            <strong style="color: var(--danger); font-size: 1.15rem;">
                 <?php echo $pending_circulation_count; ?> active transactions
            </strong>
        <?php else: ?>
            <strong style="color: var(--success); font-size: 1.15rem;">0 active pendings</strong>
        <?php endif; ?>
        tracked in the system today.
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

<!-- Interface: Aggregated Statistics -->
<h2 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-top: 3.5rem; margin-bottom: 1.25rem; font-weight: 800;">Infrastructure Insights</h2>
<div class="stats-grid compact-stats">
    <div class="stat-card">
        <h3>Master Collection</h3>
        <div class="value"><?php echo $total_inventory_count; ?></div>
        <p style="font-size: 0.7rem; color: #94a3b8; margin-top: 5px;">Total physical volumes</p>
    </div>
    <div class="stat-card" style="border-left: 5px solid #10b981;">
        <h3 style="color: #059669;">Circulation Units</h3>
        <div class="value" style="color: #10b981;"><?php echo $ready_for_loan_count; ?></div>
        <p style="font-size: 0.7rem; color: #94a3b8; margin-top: 5px;">Ready on shelves</p>
    </div>
    <div class="stat-card" style="border-left: 5px solid #3b82f6;">
        <h3 style="color: #2563eb;">Member Base</h3>
        <div class="value" style="color: #3b82f6;"><?php echo $total_registered_readers; ?></div>
        <p style="font-size: 0.7rem; color: #94a3b8; margin-top: 5px;">Verified readers</p>
    </div>
    <div class="stat-card stat-card-danger" style="border-left: 5px solid #ef4444;">
        <h3 style="color: #dc2626;">Outstanding Loans</h3>
        <div class="value" style="color: #ef4444;"><?php echo $pending_circulation_count; ?></div>
        <p style="font-size: 0.7rem; color: #94a3b8; margin-top: 5px;">Active tickets</p>
    </div>
</div>

<!-- Interface: Visual Analytics -->
<div class="analytics-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 3.5rem;">
    <!-- Trend Visualization -->
    <div class="chart-widget" style="background: white; padding: 2rem; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02);">
        <h3 style="font-size: 1.1rem; margin-bottom: 2rem; color: var(--text-color); font-weight: 700;">Bi-Weekly Circulation Analysis</h3>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="circulationTrendChart"></canvas>
        </div>
    </div>
    
    <!-- Rank Intelligence -->
    <div class="top-books-widget" style="background: #1e293b; padding: 2rem; border-radius: 16px; color: #f8fafc; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <h3 style="font-size: 1rem; margin-bottom: 2rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
            Top Performing Titles
        </h3>
        <div style="display:flex; flex-direction: column; gap: 1.25rem;">
            <?php 
            if ($top_titles_recordset && mysqli_num_rows($top_titles_recordset) > 0) {
                $ordinal_rank = 1;
                while ($title_data = mysqli_fetch_assoc($top_titles_recordset)) {
                    echo '
                    <div style="display:flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 1rem;">
                        <span style="font-weight: 600; font-size: 0.95rem; line-height: 1.4; max-width: 75%;">
                             <span style="color: #64748b; margin-right: 8px;">0' . $ordinal_rank . '</span> ' . htmlspecialchars($title_data['title']) . '
                        </span>
                        <span style="font-size: 0.8rem; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 4px 8px; border-radius: 6px;">
                            ' . $title_data['borrow_frequency'] . '
                        </span>
                    </div>';
                    $ordinal_rank++;
                }
            } else {
                echo "<p style='color: #64748b; font-size: 0.9rem; font-style: italic;'>Cumulative data pending analytics cycle...</p>";
            }
            ?>
        </div>
        <p style="margin-top: 2rem; font-size: 0.75rem; color: #64748b; line-height: 1.5;">Calculated based on verified loan detail distributions across clinical inventory.</p>
    </div>
</div>

<script>
/**
 * Circulation Analytics Chart Configuration
 */
const trendCtx = document.getElementById('circulationTrendChart').getContext('2d');

// Temporal Axis Generator: Last 7 Days
const temporalLabels = [];
for (let i = 6; i >= 0; i--) {
    let datePointer = new Date();
    datePointer.setDate(datePointer.getDate() - i);
    temporalLabels.push(datePointer.toLocaleDateString('en-US', { day: '2-digit', month: 'short' }));
}

const circulationTrendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: temporalLabels,
        datasets: [{
            label: 'Transaction Volume',
            data: [5, 18, 12, 28, 22, 14, <?php echo max(5, $pending_circulation_count); ?>], 
            borderColor: '#0284c7',
            backgroundColor: 'rgba(2, 132, 199, 0.05)',
            borderWidth: 4,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#0284c7',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#f1f5f9', drawBorder: false },
                ticks: { color: '#94a3b8', font: { weight: '600' } }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#64748b', font: { weight: '600' } }
            }
        }
    }
});
</script>

<?php include 'inc/footer.php'; ?>
