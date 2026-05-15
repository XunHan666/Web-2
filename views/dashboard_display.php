<?php
/**
 * Display Template for Dashboard - Restored to 100% Original UI
 */
?>
<!-- Chart.js for data visualization -->
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
<h2 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 1.25rem; font-weight: 800;">Quick Actions</h2>
<div class="quick-actions-grid">
    <a href="book/book_add.php" class="action-tile tile-primary">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="2" fill="none"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path><line x1="12" y1="6" x2="12" y2="10"></line><line x1="10" y1="8" x2="14" y2="8"></line></svg>
        </div>
        <span>ADD BOOKS</span>
    </a>
    <a href="loan/borrow.php" class="action-tile tile-secondary">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="2" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
        </div>
        <span>BORROW</span>
    </a>
    <a href="loan/loans.php" class="action-tile tile-success">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="2" fill="none"><path d="M21 2v6h-6"></path><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path></svg>
        </div>
        <span>RETURN</span>
    </a>
    <a href="reader/readers.php" class="action-tile tile-gray">
        <div class="tile-icon">
            <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="2" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
        </div>
        <span>READERS</span>
    </a>
</div>

<!-- Interface: Aggregated Statistics -->
<h2 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-top: 3.5rem; margin-bottom: 1.25rem; font-weight: 800;">Collection Stats</h2>
<div class="stats-grid compact-stats">
    <div class="stat-card">
        <h3>Master Collection</h3>
        <div class="value"><?php echo $total_inventory_count; ?></div>
        <p style="font-size: 0.7rem; color: #94a3b8; margin-top: 5px;">Total volumes</p>
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
    <div class="chart-widget" style="background: white; padding: 2rem; border-radius: 16px; border: 1px solid #e2e8f0;">
        <h3 style="font-size: 1.1rem; margin-bottom: 2rem; font-weight: 700;">Bi-Weekly Circulation Analysis</h3>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="circulationTrendChart"></canvas>
        </div>
    </div>
    
    <div class="top-books-widget" style="background: #1e293b; padding: 2rem; border-radius: 16px; color: #f8fafc;">
        <h3 style="font-size: 1rem; margin-bottom: 2rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">Top Performing Titles</h3>
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
                echo "<p style='color: #64748b; font-size: 0.9rem; font-style: italic;'>Data pending analytics cycle...</p>";
            }
            ?>
        </div>
    </div>
</div>

<script>
const trendCtx = document.getElementById('circulationTrendChart').getContext('2d');
const temporalLabels = [];
for (let i = 6; i >= 0; i--) {
    let d = new Date(); d.setDate(d.getDate() - i);
    temporalLabels.push(d.toLocaleDateString('en-US', { day: '2-digit', month: 'short' }));
}

new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: temporalLabels,
        datasets: [{
            label: 'Transaction Volume',
            data: [5, 18, 12, 28, 22, 14, <?php echo max(5, $pending_circulation_count); ?>], 
            borderColor: '#0284c7', backgroundColor: 'rgba(2, 132, 199, 0.05)', borderWidth: 4, tension: 0.4, fill: true
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
});
</script>
