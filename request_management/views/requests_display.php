<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / <strong style="color: var(--text-color);">Request Management</strong>
</div>

<div class="search-header" style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; color: var(--text-color);">System Requests</h1>
</div>

<div class="table-container">
    <table class="datatable">
        <thead>
            <tr>
                <th width="80" style="text-align: center;">Req ID</th>
                <th>Type</th>
                <th>Requester</th>
                <th>Details</th>
                <th>Date</th>
                <th>Status</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($requests_result) == 0): ?>
                <tr><td colspan="7" align="center" style="padding: 3rem; color: #64748b;">No requests found.</td></tr>
            <?php else: ?>
                <?php while ($req = mysqli_fetch_assoc($requests_result)): ?>
                    <tr>
                        <td align="center" style="font-weight: 600; color: #64748b;">
                            #<?php echo $req['id']; ?>
                        </td>
                        <td>
                            <?php 
                                if ($req['type'] == 'borrow_book') echo '<span class="badge" style="background:#e0f2fe;color:#0369a1;">Borrow Book</span>';
                                elseif ($req['type'] == 'librarian_registration') echo '<span class="badge" style="background:#fef3c7;color:#d97706;">Librarian Registration</span>';
                                elseif ($req['type'] == 'password_reset') echo '<span class="badge" style="background:#fee2e2;color:#b91c1c;">Password Reset</span>';
                            ?>
                        </td>
                        <td>
                            <strong style="color: var(--text-color);"><?php echo htmlspecialchars($req['requester_name']); ?></strong><br>
                            <span style="font-size: 0.85rem; color: #64748b;">@<?php echo htmlspecialchars($req['requester_username']); ?></span>
                        </td>
                        <td>
                            <?php if ($req['type'] == 'borrow_book'): ?>
                                <span style="font-weight: 600;"><?php echo htmlspecialchars($req['book_title'] ?? ''); ?></span><br>
                                <span style="font-size: 0.85rem; color: #64748b;">Barcode: <?php echo htmlspecialchars($req['book_barcode'] ?? ''); ?></span>
                            <?php elseif ($req['type'] == 'librarian_registration'): ?>
                                <span style="font-size: 0.85rem; color: #64748b;">Awaiting account activation</span>
                            <?php elseif ($req['type'] == 'password_reset'): ?>
                                <span style="font-size: 0.85rem; color: #64748b;">Needs a new password assigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo date('M d, Y', strtotime($req['created_at'])); ?>
                        </td>
                        <td>
                            <span class="badge" style="background: <?php echo $req['status'] == 'pending' ? '#fef3c7' : ($req['status'] == 'approved' ? '#dcfce7' : '#fee2e2'); ?>; color: <?php echo $req['status'] == 'pending' ? '#d97706' : ($req['status'] == 'approved' ? '#166534' : '#b91c1c'); ?>;">
                                <?php echo ucfirst($req['status']); ?>
                            </span>
                        </td>
                        <td align="center">
                            <?php if ($req['status'] == 'pending'): ?>
                                <?php if ($req['type'] == 'password_reset'): ?>
                                    <a href="../account/account_add.php?id=<?php echo $req['target_id']; ?>&req_id=<?php echo $req['id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">Change Password</a>
                                <?php else: ?>
                                    <form action="process_request.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="req_id" value="<?php echo $req['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn" style="background: #10b981; color: white; padding: 0.25rem 0.75rem; font-size: 0.8rem;">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn" style="background: #ef4444; color: white; padding: 0.25rem 0.75rem; font-size: 0.8rem; margin-left: 0.25rem;">Reject</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #94a3b8; font-size: 0.85rem;">Completed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
