<?php
/**
 * Display Template for Readers - UPDATED WITH MODAL HISTORY
 */
?>
<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / Reader Management / <strong style="color: var(--text-color);">Reader Directory</strong>
</div>

<!-- Statistics Header -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
    <div class="stat-card" style="padding: 1.5rem; border-left-color: var(--primary-color);">
        <h3 style="margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase; color: #64748b;">Total Readers</h3>
        <div class="value" style="font-size: 1.5rem; font-weight: 700;"><?php echo $total_readers_count; ?> registered</div>
    </div>
    <div class="stat-card" style="padding: 1.5rem; border-left-color: #10b981;">
        <h3 style="margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase; color: #64748b;">Active Members</h3>
        <div class="value" style="font-size: 1.5rem; font-weight: 700; color: #10b981;"><?php echo $active_readers_count; ?> active</div>
    </div>
    <div class="stat-card" style="padding: 1.5rem; border-left-color: #ef4444;">
        <h3 style="margin-bottom: 0.5rem; font-size: 0.875rem; text-transform: uppercase; color: #64748b;">Overdue Alert</h3>
        <div class="value" style="font-size: 1.5rem; font-weight: 700; color: #ef4444;"><?php echo $overdue_readers_count; ?> readers</div>
    </div>
</div>

<div class="toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <form action="" method="GET" style="display: flex; gap: 0.5rem; flex: 1; min-width: 300px; max-width: 600px;">
        <input type="text" name="search" placeholder="Search name, email, or phone..." class="search-input" value="<?php echo htmlspecialchars($search_term); ?>" style="width: 250px; border-radius: 8px;">
        <select name="filter" class="search-input" style="width: 150px; padding: 0.75rem; border-radius: 8px;">
            <option value="all">All Status</option>
            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active Only</option>
            <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive Only</option>
        </select>
        <button type="submit" class="btn btn-primary" style="border-radius: 8px;">Filter</button>
    </form>
    <a href="reader_add.php" class="btn btn-primary" style="border-radius: 8px;">+ Register Reader</a>
</div>

<div class="table-container" style="border-radius: 12px; overflow-x: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%;">
    <table class="datatable">
        <thead>
            <tr style="background: #f8fafc;">
                <th width="60" style="text-align: center;">STT</th>
                <th style="text-align: left;">Reader Name</th>
                <th style="text-align: left;">Contact Info</th>
                <th style="text-align: left;">Join Date</th>
                <th style="text-align: center;">Activity</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center; white-space: nowrap;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($readers_result) == 0): ?>
                <tr><td colspan="7" align="center" style="padding: 3rem; color: #64748b;">No readers found.</td></tr>
            <?php else: ?>
                <?php $stt = 1; while ($reader = mysqli_fetch_assoc($readers_result)): ?>
                    <tr>
                        <td align="center" style="color: #94a3b8; font-size: 0.9rem;">
                            <?php echo $stt++; ?>
                        </td>
                        <td>
                            <strong style="font-size: 1rem; color: #1e293b; font-weight: 700;"><?php echo htmlspecialchars($reader['name']); ?></strong>
                        </td>
                        <td>
                            <div style="color: #64748b; font-size: 0.9rem;"><?php echo htmlspecialchars($reader['email']); ?></div>
                            <div style="color: #94a3b8; font-size: 0.85rem;"><?php echo htmlspecialchars($reader['phone']); ?></div>
                        </td>
                        <td>
                            <div style="color: #475569; font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($reader['created_at'])); ?></div>
                        </td>
                        <td align="center">
                            <span style="background: #f1f5f9; color: #475569; padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 500;">
                                <?php echo $reader['loan_count']; ?> loan sessions
                            </span>
                        </td>
                        <td align="center">
                            <span class="badge" style="background: <?php echo $reader['status'] == 'active' ? '#dcfce7' : '#f1f5f9'; ?>; color: <?php echo $reader['status'] == 'active' ? '#166534' : '#475569'; ?>; border-radius: 20px; padding: 0.4rem 1rem;">
                                <?php echo ucfirst($reader['status']); ?>
                            </span>
                        </td>
                        <td align="center" style="white-space: nowrap;">
                            <div style="display: flex; gap: 0.5rem; justify-content: center; align-items: center;">
                                <a href="javascript:void(0)" onclick="openHistory(<?php echo $reader['id']; ?>, '<?php echo addslashes($reader['name']); ?>')" style="color: #64748b; text-decoration: underline; font-size: 0.9rem;">History</a>
                                <span style="color: #e2e8f0;">|</span>
                                <a href="reader_add.php?id=<?php echo $reader['id']; ?>" style="color: #0ea5e9; text-decoration: none; font-size: 0.9rem;">Edit</a>
                                <span style="color: #e2e8f0;">|</span>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $reader['id']; ?>, '<?php echo addslashes($reader['name']); ?>', 'reader', 'reader_delete.php')" style="color: #ef4444; text-decoration: none; font-size: 0.9rem;">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- History Modal -->
<div id="historyModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div style="background-color: white; border-radius: 20px; width: 90%; max-width: 800px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden;">
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h2 id="modalTitle" style="font-size: 1.25rem; margin: 0; color: #1e293b;">Loan History</h2>
            <button onclick="closeHistory()" style="background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; padding: 0.5rem;">&times;</button>
        </div>
        <div id="modalBody" style="padding: 1rem;">
            <div style="text-align: center; padding: 3rem;">
                <div class="loader" style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 1rem; color: #64748b;">Fetching records...</p>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
function openHistory(readerId, readerName) {
    const modal = document.getElementById('historyModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.innerText = `Loan History: ${readerName}`;
    modal.style.display = 'flex';
    
    // Fetch data via AJAX
    fetch(`get_reader_history.php?reader_id=${readerId}`)
        .then(response => response.text())
        .then(html => {
            modalBody.innerHTML = html;
        })
        .catch(err => {
            modalBody.innerHTML = '<div style="padding: 3rem; text-align: center; color: #ef4444;">Failed to load history.</div>';
        });
}

function closeHistory() {
    document.getElementById('historyModal').style.display = 'none';
}

// Close on click outside
window.onclick = function(event) {
    const modal = document.getElementById('historyModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>
