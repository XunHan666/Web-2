<?php
/**
 * Display Template for Staff Management - 100% ORIGINAL UI
 */
?>
<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / System Administration / <strong style="color: var(--text-color);">Librarian Directory</strong>
</div>

<div class="search-header" style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; color: var(--text-color);">Staff Accounts</h1>
    <a href="user_add.php" class="btn btn-primary">+ Register New Staff</a>
</div>

<div class="table-container">
    <table class="datatable">
        <thead>
            <tr>
                <th width="50" style="text-align: center;">STT</th>
                <th style="text-align: left;">Staff Name</th>
                <th style="text-align: left;">Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Member Since</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($staff_result) == 0): ?>
                <tr><td colspan="7" align="center" style="padding: 3rem; color: #64748b;">No staff records found.</td></tr>
            <?php else: ?>
                <?php while ($staff = mysqli_fetch_assoc($staff_result)): ?>
                    <tr>
                        <?php static $stt = 1; ?>
                        <td align="center" style="font-weight: 600; color: #64748b;">
                            <?php echo $stt++; ?>
                        </td>
                        <td>
                            <strong style="font-size: 1.1rem; color: var(--text-color);"><?php echo htmlspecialchars($staff['full_name']); ?></strong>
                            <div style="font-size: 0.8rem; color: #64748b;">Staff ID: #<?php echo $staff['id']; ?></div>
                        </td>
                        <td>
                            <code style="background: #f1f5f9; color: #475569; padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.9rem;">
                                @<?php echo htmlspecialchars($staff['username']); ?>
                            </code>
                        </td>
                        <td align="center">
                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; text-transform: uppercase;">
                                <?php echo htmlspecialchars($staff['role_name']); ?>
                            </span>
                        </td>
                        <td align="center">
                            <span class="badge" style="background: <?php echo $staff['status'] == 'active' ? '#dcfce7' : '#f1f5f9'; ?>; color: <?php echo $staff['status'] == 'active' ? '#166534' : '#475569'; ?>;">
                                <?php echo ucfirst($staff['status']); ?>
                            </span>
                        </td>
                        <td align="center"><?php echo date('M d, Y', strtotime($staff['created_at'])); ?></td>
                        <td align="center">
                            <div class="action-buttons-group">
                                <a href="user_add.php?id=<?php echo $staff['id']; ?>" style="color: #0ea5e9; text-decoration: none;">Edit</a>
                                <?php if ($staff['id'] != $_SESSION['user_id']): ?>
                                    <span style="color: #e2e8f0;">|</span>
                                    <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $staff['id']; ?>, '<?php echo addslashes($staff['full_name']); ?>', 'staff', 'user_delete.php')" style="color: #ef4444; text-decoration: none;">Delete</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
