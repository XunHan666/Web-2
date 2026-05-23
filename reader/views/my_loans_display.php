<h1 class="rp-page-title">🔖 My Loan History</h1>
<p class="rp-page-sub">All your borrowing sessions, newest first.</p>

<?php
// Flash messages
if (isset($_GET['success']) && $_GET['success'] === 'return_requested'):
?>
<div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:0.85rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-weight:600;">
    ✅ Return request submitted! Staff will confirm when they receive your book.
</div>
<?php elseif (isset($_GET['error']) && $_GET['error'] === 'already_requested'): ?>
<div style="background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; padding:0.85rem 1.25rem; border-radius:10px; margin-bottom:1.5rem;">
    ⚠️ You already have a pending return request for this loan.
</div>
<?php endif; ?>

<?php if (empty($loans)): ?>
    <div class="rp-empty">
        <div class="icon">📭</div>
        <p>No loan history yet.</p>
        <a href="book.php" class="rp-btn" style="margin-top:1rem">Browse Books</a>
    </div>
<?php else: ?>
    <?php foreach ($loans as $lid => $data):
        $closed   = $data['status'] === 'closed';
        $pending  = $data['status'] === 'pending';
        $rejected = $data['status'] === 'rejected';
        $overdue  = !$closed && !$pending && !$rejected && $data['days_left'] < 0;
        $is_active = !$closed && !$pending && !$rejected; // ongoing or partial

        // Check if already has pending return request
        $has_return_req = false;
        if ($is_active) {
            $rr = mysqli_fetch_array(mysqli_query($db_connect,
                "SELECT COUNT(*) FROM requests
                 WHERE type='return_book' AND status='pending'
                   AND JSON_UNQUOTE(JSON_EXTRACT(notes,'$.loan_id')) = '$lid'"
            ));
            $has_return_req = $rr[0] > 0;
        }
        
        $badge = 'badge-blue';
        $label = 'Borrowing';
        if ($closed)   { $badge = 'badge-green'; $label = 'Returned'; }
        if ($overdue)  { $badge = 'badge-danger'; $label = 'Overdue (' . abs($data['days_left']) . 'd late)'; }
        if ($pending)  { $badge = 'badge-warn'; $label = 'Pending Approval'; }
        if ($rejected) { $badge = 'badge-danger'; $label = 'Rejected'; }
    ?>
    <div class="rp-loan-card">
        <div class="rp-loan-head">
            <div>
                <strong>Session #<?php echo $lid; ?></strong>
                <span class="meta">
                    <?php if ($data['borrow_date']): ?>
                        Borrowed <?php echo date('M d, Y', strtotime($data['borrow_date'])); ?>
                    <?php else: ?>
                        Requested recently
                    <?php endif; ?>
                    <?php if ($data['status'] === 'pending' || $data['status'] === 'rejected'): ?>
                        · Due: TBD
                    <?php else: ?>
                        · Due <?php echo date('M d, Y', strtotime($data['due_date'])); ?>
                    <?php endif; ?>
                </span>
            </div>
            <div>
                <?php if ($overdue): ?>
                    <span style="font-size:0.8rem; color:var(--danger); font-weight:600;">⚠️ <?php echo abs($data['days_left']); ?> days overdue!</span>
                <?php elseif (!$closed && !$pending && !$rejected): ?>
                    <span style="font-size:0.8rem; color:var(--text-muted);">⏳ <?php echo $data['days_left']; ?> day<?php echo $data['days_left'] != 1 ? 's' : ''; ?> left</span>
                <?php endif; ?>
            </div>
            <span class="<?php echo $badge; ?>"><?php echo $label; ?></span>
        </div>
        <div class="rp-loan-body">
            <table class="rp-loan-table">
                <thead><tr><th>Book</th><th>Status</th><th>Returned On</th></tr></thead>
                <tbody>
                <?php foreach ($data['items'] as $item):
                    $ic = 'badge-blue';
                    if ($item['status'] === 'returned') $ic = 'badge-green';
                    if ($item['status'] === 'pending') $ic = 'badge-warn';
                    if ($item['status'] === 'rejected') $ic = 'badge-danger';
                ?>
                    <tr>
                        <td><a href="<?php echo BASE_URL; ?>reader/book.php?id=<?php echo $item['book_id']; ?>"><?php echo htmlspecialchars($item['title']); ?></a></td>
                        <td><span class="<?php echo $ic; ?>"><?php echo ucfirst($item['status']); ?></span></td>
                        <td><?php echo $item['return_date'] ? date('M d, Y', strtotime($item['return_date'])) : '—'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($is_active): ?>
        <?php
            $days_late_now = $data['days_left'] < 0 ? abs($data['days_left']) : 0;
            $days_left_now = $data['days_left'] > 0 ? $data['days_left'] : 0;
            $fine_now      = $days_late_now * 5000;

            // Pick footer style based on urgency
            if ($days_late_now > 0) {
                // OVERDUE — red
                $footer_bg    = '#fef2f2';
                $footer_border= '#fecaca';
            } elseif ($days_left_now <= 2) {
                // Urgent — orange
                $footer_bg    = '#fff7ed';
                $footer_border= '#fed7aa';
            } else {
                // OK — subtle
                $footer_bg    = '#f8fafc';
                $footer_border= 'var(--border-color)';
            }
        ?>
        <div style="padding:1rem 1.25rem; border-top:2px solid <?php echo $footer_border; ?>; background:<?php echo $footer_bg; ?>; border-radius:0 0 12px 12px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
            <?php if ($has_return_req): ?>
                <span style="display:inline-flex; align-items:center; gap:0.5rem; background:#fef3c7; color:#92400e; padding:0.55rem 1rem; border-radius:99px; font-size:0.85rem; font-weight:700; border:1px solid #fde68a;">
                    🕐 Return Request Pending — waiting for staff confirmation
                </span>
            <?php elseif ($days_late_now > 0): ?>
                <!-- OVERDUE state -->
                <div style="display:flex; align-items:center; gap:0.6rem;">
                    <span style="font-size:1.5rem;">🚨</span>
                    <div>
                        <div style="font-size:0.95rem; font-weight:800; color:#991b1b;">
                            <?php echo $days_late_now; ?> day<?php echo $days_late_now!=1?'s':''; ?> overdue!
                        </div>
                        <div style="font-size:0.82rem; color:#b91c1c; font-weight:600;">
                            Est. fine accumulating: <strong>~<?php echo number_format($fine_now); ?> VNĐ</strong>
                            — growing <?php echo number_format(5000); ?> VNĐ/day
                        </div>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>reader/request_return.php?loan_id=<?php echo $lid; ?>"
                   style="display:inline-flex; align-items:center; gap:0.5rem; background:#dc2626; color:white; padding:0.6rem 1.3rem; border-radius:8px; font-size:0.9rem; font-weight:800; text-decoration:none; box-shadow:0 2px 8px rgba(220,38,38,0.35);"
                   onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    🚨 Request Return Now
                </a>
            <?php elseif ($days_left_now <= 2): ?>
                <!-- URGENT — due very soon -->
                <div style="display:flex; align-items:center; gap:0.6rem;">
                    <span style="font-size:1.4rem;">⏰</span>
                    <div>
                        <div style="font-size:0.9rem; font-weight:800; color:#c2410c;">
                            Due in <?php echo $days_left_now; ?> day<?php echo $days_left_now!=1?'s':''; ?>!
                        </div>
                        <div style="font-size:0.8rem; color:#9a3412;">
                            Return before <strong><?php echo date('M d', strtotime($data['due_date'])); ?></strong> to avoid late fines.
                        </div>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>reader/request_return.php?loan_id=<?php echo $lid; ?>"
                   style="display:inline-flex; align-items:center; gap:0.5rem; background:#ea580c; color:white; padding:0.6rem 1.3rem; border-radius:8px; font-size:0.9rem; font-weight:800; text-decoration:none; box-shadow:0 2px 8px rgba(234,88,12,0.3);"
                   onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    ⏰ Request Return
                </a>
            <?php else: ?>
                <!-- ON TRACK -->
                <div style="display:flex; align-items:center; gap:0.5rem; color:#475569; font-size:0.85rem;">
                    <span>📅</span>
                    <span>Return before <strong style="color:var(--primary-color);"><?php echo date('M d', strtotime($data['due_date'])); ?></strong> to avoid fines.</span>
                </div>
                <a href="<?php echo BASE_URL; ?>reader/request_return.php?loan_id=<?php echo $lid; ?>"
                   style="display:inline-flex; align-items:center; gap:0.5rem; background:var(--primary-color); color:white; padding:0.6rem 1.3rem; border-radius:8px; font-size:0.9rem; font-weight:700; text-decoration:none;"
                   onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    📦 Request Return
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
    <?php endforeach; ?>
<?php endif; ?>
