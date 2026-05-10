<?php
/**
 * Readers Directory Management
 * Displays a searchable and filterable list of library members with statistics.
 */
require_once '../env/config.php';
include '../inc/header.php';

// Initialization
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; 


/**
 * Statistics Dashboard Metrics
 */
// Total registered
$total_readers_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM readers");
$total_readers_count = mysqli_fetch_array($total_readers_res)[0];

// Active status
$active_readers_res = mysqli_query($db_connect, "SELECT COUNT(*) FROM readers WHERE status = 'active'");
$active_readers_count = mysqli_fetch_array($active_readers_res)[0];

// Overdue status (Readers with at least one overdue book)
$overdue_query = "
    SELECT COUNT(DISTINCT r.id) 
    FROM readers r 
    JOIN loans l ON r.id = l.reader_id 
    JOIN loan_details ld ON l.id = ld.loan_id 
    WHERE ld.status = 'borrowed' AND l.due_date < CURDATE()
";
$overdue_readers_res = mysqli_query($db_connect, $overdue_query);
$overdue_readers_count = mysqli_fetch_array($overdue_readers_res)[0];

/**
 * Fetch Readers for main table display
 */
$search_pattern = "%$search_term%";
$main_list_query = "
    SELECT r.*, 
           (SELECT COUNT(l.id) FROM loans l WHERE l.reader_id = r.id) as total_loans,
           (SELECT COUNT(ld.id) 
            FROM loans l 
            JOIN loan_details ld ON l.id = ld.loan_id 
            WHERE l.reader_id = r.id AND ld.status = 'borrowed' AND l.due_date < CURDATE()
           ) as overdue_count
    FROM readers r
    WHERE (r.name LIKE ? OR r.email LIKE ?)
";

// Apply status filters
if ($status_filter === 'active') {
    $main_list_query .= " AND r.status = 'active'";
} elseif ($status_filter === 'inactive') {
    $main_list_query .= " AND r.status = 'inactive'";
} elseif ($status_filter === 'overdue') {
    $main_list_query .= " AND (SELECT COUNT(ld.id) FROM loans l JOIN loan_details ld ON l.id = ld.loan_id WHERE l.reader_id = r.id AND ld.status = 'borrowed' AND l.due_date < CURDATE()) > 0";
}

$main_list_query .= " ORDER BY r.id ASC";

$stmt = mysqli_prepare($db_connect, $main_list_query);
mysqli_stmt_bind_param($stmt, "ss", $search_pattern, $search_pattern);
mysqli_stmt_execute($stmt);
$readers_result = mysqli_stmt_get_result($stmt);
?>

<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / Reader Management / <strong style="color: var(--text-color);">Readers Directory</strong>
</div>

<!-- Statistics Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
    <div class="stat-card" style="padding: 1.5rem; border-left-color: #3b82f6;">
        <h3 style="margin-bottom: 0;">Total Readers</h3>
        <div class="value" style="font-size: 1.5rem; color: #3b82f6;"><?php echo $total_readers_count; ?> users</div>
    </div>
    <div class="stat-card" style="padding: 1.5rem; border-left-color: #10b981;">
        <h3 style="margin-bottom: 0;">Active Members</h3>
        <div class="value" style="font-size: 1.5rem; color: #10b981;"><?php echo $active_readers_count; ?> active</div>
    </div>
    <div class="stat-card" style="padding: 1.5rem; border-left-color: #ef4444;">
        <h3 style="margin-bottom: 0;">Overdue Alert</h3>
        <div class="value" style="font-size: 1.5rem; color: #ef4444;"><?php echo $overdue_readers_count; ?> flagged</div>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <form action="" method="GET" style="display: flex; gap: 0.5rem; flex: 1; min-width: 300px; max-width: 650px;">
        <input type="text" name="search" placeholder="Search by name or email..." class="search-input" value="<?php echo htmlspecialchars($search_term); ?>" style="width: 250px;">
        <select name="filter" class="search-input" style="width: 150px; padding: 0.75rem;">
            <option value="all">Filter: All</option>
            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active Only</option>
            <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive Only</option>
            <option value="overdue" <?php echo $status_filter == 'overdue' ? 'selected' : ''; ?>>Overdue Only</option>
        </select>
        <button type="submit" class="btn btn-primary">Refresh List</button>
    </form>
    
    <a href="reader_add.php" class="btn btn-primary">
        + Register New Reader
    </a>
</div>

<!-- Readers Table -->
<div class="table-container">
    <table class="datatable">
        <thead>
            <tr>
                <th width="60">No.</th>
                <th style="text-align: left;">Reader Name</th>
                <th style="text-align: left;">Contact Info</th>
                <th>Join Date</th>
                <th>Activity</th>
                <th>Status</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($readers_result) == 0): ?>
                <tr><td colspan="7" style="text-align: center; padding: 3rem; color: #64748b;">No reader records found matching your criteria.</td></tr>
            <?php else: 
                $row_index = 1;
                while ($reader = mysqli_fetch_assoc($readers_result)): 
                    $is_overdue = $reader['overdue_count'] > 0;
                    $badge_class = $reader['status'] == 'active' ? 'returned' : 'borrowing';
                    
                    // JSON Prep for History Modal (Limit to 10 latest)
                    $hist_sql = "
                        SELECT bk.title, l.borrow_date, ld.status
                        FROM loans l
                        JOIN loan_details ld ON l.id = ld.loan_id
                        JOIN book_copies bc ON ld.book_copy_id = bc.id
                        JOIN books bk ON bc.book_id = bk.id
                        WHERE l.reader_id = " . $reader['id'] . "
                        ORDER BY l.borrow_date DESC LIMIT 10
                    ";
                    $hist_res = mysqli_query($db_connect, $hist_sql);
                    $hist_data = [];
                    while($hr = mysqli_fetch_assoc($hist_res)) { $hist_data[] = $hr; }
                    $hist_json = htmlspecialchars(json_encode($hist_data), ENT_QUOTES, 'UTF-8');
                ?>
                    <tr>
                        <td align="center" style="color: #94a3b8; font-family: monospace;">#<?php echo $row_index++; ?></td>
                        
                        <td style="font-weight: 600; color: var(--text-color);">
                            <?php echo htmlspecialchars($reader['name']); ?>
                        </td>

                        <td>
                            <div style="font-size: 0.85rem; color: #64748b;">
                                <?php echo htmlspecialchars($reader['email']); ?><br>
                                <span style="font-size: 0.8rem; color: #94a3b8;"><?php echo htmlspecialchars($reader['phone']); ?></span>
                            </div>
                        </td>

                        <td align="center" style="color: #64748b;">
                            <?php echo date('M d, Y', strtotime($reader['created_at'])); ?>
                        </td>

                        <td align="center">
                            <span class="badge" style="background:#f1f5f9; color:#475569; font-size: 0.8rem;">
                                <?php echo $reader['total_loans']; ?> loan sessions
                            </span>
                        </td>

                        <td align="center">
                            <span class="badge badge-<?php echo $badge_class; ?>" style="min-width: 80px; text-align: center;">
                                <?php echo ucfirst($reader['status']); ?>
                            </span>
                        </td>

                        <td align="center">
                            <div class="action-buttons-group">
                                <a href="javascript:void(0)" onclick="openHistoryModal('<?php echo addslashes($reader['name']); ?>', '<?php echo $hist_json; ?>')" style="color: #64748b; text-decoration: none;">History</a>
                                <span style="color: #e2e8f0;">|</span>
                                <a href="reader_add.php?id=<?php echo $reader['id']; ?>" style="color: #0ea5e9; text-decoration: none;">Edit</a>
                                <span style="color: #e2e8f0;">|</span>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $reader['id']; ?>, '<?php echo addslashes($reader['name']); ?>', 'reader', 'reader_delete.php')" style="color: #ef4444; text-decoration: none;">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- History Modal Dialog -->
<dialog id="historyDialog" style="padding: 0; border: none; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-width: 500px; width: 100%;">
    <div style="padding: 1.5rem; background: var(--card-bg); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;" id="modalReaderName">Loan History</h3>
        <button onclick="document.getElementById('historyDialog').close()" style="background: var(--primary-color); border: none; border-radius: 6px; color: white; padding: 0.4rem 0.8rem; font-size: 0.8rem; cursor: pointer;">Close</button>
    </div>
    <div style="padding: 1.5rem; max-height: 400px; overflow-y: auto;" id="modalHistoryList">
        <!-- Content dynamically injected -->
    </div>
</dialog>

<script>
/**
 * Modern Reader History Modal
 */
function openHistoryModal(reader_name, json_data) {
    document.getElementById('modalReaderName').innerText = "Loan History: " + reader_name;
    
    let history_items = [];
    try { history_items = JSON.parse(json_data); } catch (e) { console.error("JSON Error", e); }

    let html_content = '';
    if (history_items.length === 0) {
        html_content = '<div style="text-align: center; color: #94a3b8; padding: 2rem;">No previous loan activity found.</div>';
    } else {
        html_content += '<div style="display: flex; flex-direction: column; gap: 1rem;">';
        history_items.forEach(item => {
            let status_color = item.status === 'returned' ? 'color:#10b981;' : 'color:#ef4444;';
            html_content += `
                <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
                    <div style="font-weight: 600; color: var(--text-color); font-size: 0.95rem;">${item.title}</div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-top: 0.25rem;">
                        <span style="color:#64748b;">Borrowed on: ${item.borrow_date}</span>
                        <span style="font-weight:700; text-transform:uppercase; ${status_color}">${item.status}</span>
                    </div>
                </div>
            `;
        });
        html_content += '</div>';
    }
    document.getElementById('modalHistoryList').innerHTML = html_content;
    document.getElementById('historyDialog').showModal();
}
</script>

<?php include '../inc/footer.php'; ?>
