<?php $heading = $requests_heading ?? 'Request Management'; ?>
<div class="breadcrumb" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.9rem;">
    Home / <strong style="color: var(--text-color);"><?php echo htmlspecialchars($heading); ?></strong>
</div>

<div class="search-header" style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.5rem; color: var(--text-color);"><?php echo htmlspecialchars($heading); ?></h1>
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
                    <?php
                    // ── Detect type (fallback if ENUM not yet updated) ──
                    $req_type = $req['type'];
                    if (empty($req_type) && !empty($req['notes'])) {
                        $tmp = json_decode($req['notes'], true);
                        if (!empty($tmp['loan_id'])) $req_type = 'return_book';
                    }

                    // ── Fine calculation for return_book ──
                    $planned_date  = null;
                    $days_late     = 0;
                    $fine_amount   = 0;
                    $today_late    = 0;
                    $today_fine    = 0;
                    if ($req_type === 'return_book' && !empty($req['notes'])) {
                        $notes_data   = json_decode($req['notes'], true);
                        $planned_date = $notes_data['planned_return_date'] ?? null;
                        $due_dt       = $req['loan_due_date'] ?? ($notes_data['due_date'] ?? null);
                        if ($planned_date && $due_dt) {
                            $diff = (strtotime($planned_date) - strtotime($due_dt)) / 86400;
                            $days_late   = max(0, (int)ceil($diff));
                            $fine_amount = $days_late * $fine_rate;
                        }
                        // Actual days late from today (for confirm)
                        $today_late = max(0, (int)ceil((strtotime(date('Y-m-d')) - strtotime($due_dt ?? date('Y-m-d'))) / 86400));
                        $today_fine = $today_late * $fine_rate;
                    }
                    ?>
                    <tr>
                        <td align="center" style="font-weight: 600; color: #64748b;">
                            #<?php echo $req['id']; ?>
                        </td>
                        <td>
                            <?php if ($req_type == 'borrow_book'): ?>
                                <span class="badge" style="background:#e0f2fe;color:#0369a1;">Borrow Book</span>
                            <?php elseif ($req_type == 'return_book'): ?>
                                <span class="badge" style="background:#f3e8ff;color:#7c3aed;">Return Book</span>
                            <?php elseif ($req_type == 'librarian_registration'): ?>
                                <span class="badge" style="background:#fef3c7;color:#d97706;">Librarian Registration</span>
                            <?php elseif ($req_type == 'password_reset'): ?>
                                <span class="badge" style="background:#fee2e2;color:#b91c1c;">Password Reset</span>
                            <?php else: ?>
                                <span class="badge" style="background:#f1f5f9;color:#64748b;"><?php echo htmlspecialchars($req['type'] ?: 'Unknown'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="color: var(--text-color);"><?php echo htmlspecialchars($req['requester_name']); ?></strong><br>
                            <span style="font-size: 0.85rem; color: #64748b;">@<?php echo htmlspecialchars($req['requester_username']); ?></span>
                        </td>
                        <td>
                            <?php if ($req_type == 'borrow_book'): ?>
                                <span style="font-weight: 600;"><?php echo htmlspecialchars($req['book_title'] ?? ''); ?></span><br>
                                <span style="font-size: 0.85rem; color: #64748b;">Barcode: <?php echo htmlspecialchars($req['book_barcode'] ?? ''); ?></span>

                            <?php elseif ($req_type == 'return_book'): ?>
                                <!-- Book(s) being returned -->
                                <span style="font-weight: 700; color:var(--text-color);">
                                    <?php echo htmlspecialchars($req['book_title'] ?? ('Loan #' . $req['target_id'])); ?>
                                </span><br>
                                <!-- Due date + Planned return date -->
                                <div style="margin-top:5px; display:flex; flex-direction:column; gap:3px;">
                                    <span style="font-size:0.82rem; color:#64748b;">
                                        📌 Due: <strong><?php echo $req['loan_due_date'] ? date('M d, Y', strtotime($req['loan_due_date'])) : '—'; ?></strong>
                                    </span>
                                    <span style="font-size:0.82rem; color:#64748b;">
                                        📅 Reader plans to return: 
                                        <strong style="color:<?php echo $days_late > 0 ? '#dc2626' : '#16a34a'; ?>">
                                            <?php echo $planned_date ? date('M d, Y', strtotime($planned_date)) : '—'; ?>
                                        </strong>
                                        <?php if ($days_late > 0): ?>
                                            <span style="color:#dc2626; font-size:0.78rem;">(<?php echo $days_late; ?>d late)</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if ($req['status'] === 'pending' && $today_fine > 0): ?>
                                    <div style="margin-top:6px; padding:4px 10px; background:#fef2f2; border-left:3px solid #ef4444; border-radius:4px; font-size:0.82rem; font-weight:700; color:#991b1b;">
                                        ⚠️ Fine if confirmed today: <?php echo number_format($today_fine); ?> VNĐ
                                    </div>
                                <?php elseif ($req['status'] === 'pending'): ?>
                                    <div style="margin-top:6px; padding:4px 10px; background:#f0fdf4; border-left:3px solid #22c55e; border-radius:4px; font-size:0.82rem; color:#166534;">
                                        ✅ On time — no fine
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($req_type == 'librarian_registration'): ?>
                                <span style="font-size: 0.85rem; color: #64748b;">Awaiting account activation</span>
                            <?php elseif ($req_type == 'password_reset'): ?>
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
                                <?php if ($req_type == 'password_reset'): ?>
                                    <a href="<?php echo BASE_URL; ?>account/account_add.php?id=<?php echo $req['target_id']; ?>&req_id=<?php echo $req['id']; ?>" class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">Change Password</a>

                                <?php elseif ($req_type == 'return_book'): ?>
                                    <!-- Confirm Return: shows actual fine at time of confirm -->
                                    <?php if ($today_fine > 0): ?>
                                    <div style="font-size:0.75rem; color:var(--danger); margin-bottom:4px; font-weight:600;">
                                        Fine today: <?php echo number_format($today_fine); ?> VNĐ
                                    </div>
                                    <?php endif; ?>
                                    <form action="<?php echo BASE_URL; ?>request_management/process_request.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="req_id" value="<?php echo $req['id']; ?>">
                                        <button type="submit" name="action" value="approve"
                                                class="btn" style="background:#10b981;color:white;padding:0.25rem 0.75rem;font-size:0.8rem;"
                                                onclick="return confirm('Confirm you have received the book? Fine will be calculated as of today.')">
                                            ✓ Confirm Return
                                        </button>
                                        <button type="submit" name="action" value="reject"
                                                class="btn" style="background:#ef4444;color:white;padding:0.25rem 0.75rem;font-size:0.8rem;margin-left:0.25rem;">
                                            ✕ Reject
                                        </button>
                                    </form>

                                <?php else: ?>
                                    <form action="<?php echo BASE_URL; ?>request_management/process_request.php" method="POST" style="display:inline;">
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
