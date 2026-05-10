<?php
/**
 * Staff Management Directory
 * Lists all registered librarians and system staff accounts.
 * Accessible only to administrators (Role ID 1).
 */
require_once '../env/config.php';
include '../inc/header.php';

/**
 * Authorization: Strict administrative access check
 */
if ($_SESSION['role_id'] != 1) {
    showAlert("Administrative privileges are required to manage staff accounts.", "error");
    echo "<script>window.location.href = '" . BASE_URL . "index.php';</script>";
    exit();
}

/**
 * Fetch Staff Accounts
 * Note: Administrators (Role 1) manage Librarians (Role 2).
 */
$staff_query = "
    SELECT u.*, r.name as role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    WHERE u.role_id = 2
    ORDER BY u.id ASC
";
$staff_result = mysqli_query($db_connect, $staff_query);
?>

<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / <strong style="color: var(--text-color);">Staff Management</strong>
</div>

<div class="search-header" style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; color: var(--text-color);">Librarian Directory</h1>
    <a href="user_add.php" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.75rem;">
        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Register New Staff
    </a>
</div>

<div class="table-container">
    <table class="datatable">
        <thead>
            <tr>
                <th width="60">ID</th>
                <th style="text-align: left;">Staff Name</th>
                <th style="text-align: left;">System Login</th>
                <th>Authorization</th>
                <th>Account Status</th>
                <th>Registration Date</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($staff_result) == 0): ?>
                <tr><td colspan="7" style="text-align:center; padding: 3rem; color: #94a3b8;">No staff accounts registered yet.</td></tr>
            <?php else: ?>
                <?php while ($staff = mysqli_fetch_assoc($staff_result)): ?>
                    <tr>
                        <td align="center" style="font-weight: 600; color: #94a3b8;">#<?php echo $staff['id']; ?></td>
                        <td>
                            <div class="contact-block">
                                <div class="reader-avatar" style="background: var(--primary-color); border-radius: 8px;">
                                    <?php echo strtoupper(substr($staff['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="reader-name" style="font-weight: 600; color: var(--text-color);">
                                        <?php echo htmlspecialchars($staff['full_name']); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Librarian Account</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <code style="background: #f1f5f9; color: #475569; padding: 0.25rem 0.5rem; border-radius: 6px; font-family: monospace;">
                                @<?php echo htmlspecialchars($staff['username']); ?>
                            </code>
                        </td>
                        <td align="center">
                            <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight: 700;">
                                <?php echo strtoupper($staff['role_name']); ?>
                            </span>
                        </td>
                        <td align="center">
                            <span class="badge" style="background: <?php echo $staff['status'] == 'active' ? '#dcfce7' : '#f1f5f9'; ?>; color: <?php echo $staff['status'] == 'active' ? '#166534' : '#475569'; ?>;">
                                <?php echo ucfirst($staff['status']); ?>
                            </span>
                        </td>
                        <td align="center" style="color: #64748b; font-size: 0.85rem;">
                            <?php echo date('M d, Y', strtotime($staff['created_at'])); ?>
                        </td>
                        <td align="center">
                            <div class="action-buttons-group">
                                <a href="user_edit.php?id=<?php echo $staff['id']; ?>" class="action-btn action-edit" title="Edit Staff Record">
                                    <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </a>
                                <?php if ($staff['id'] != $_SESSION['user_id']): ?>
                                    <button onclick="requestUserDeletion(<?php echo $staff['id']; ?>, '<?php echo addslashes($staff['full_name']); ?>')" class="action-btn action-delete" title="Terminate Account">
                                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
/**
 * Ajax deletion handler for staff records
 */
function requestUserDeletion(staffId, staffName) {
    Swal.fire({
        title: 'Confirm Deletion',
        text: `You are about to permanently remove ${staffName} from the system. This action is irreversible.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Yes, remove account'
    }).then((confirmation) => {
        if (confirmation.isConfirmed) {
            fetch(`user_delete.php?id=${staffId}`)
                .then(response => response.json())
                .then(results => {
                    if (results.success) {
                        Swal.fire('Account Terminated', results.message, 'success')
                            .then(() => window.location.reload());
                    } else {
                        Swal.fire('Operation Failed', results.message, 'error');
                    }
                })
                .catch(err => {
                    console.error('API Error:', err);
                    Swal.fire('System Error', 'Communication with the server failed.', 'error');
                });
        }
    });
}
</script>

<?php include '../inc/footer.php'; ?>
