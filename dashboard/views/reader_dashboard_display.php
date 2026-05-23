<?php
/**
 * Reader Dashboard — View
 * Expects from reader-dashboard.php (controller):
 *   $reader, $total_loans, $active_books, $overdue_books,
 *   $pending_requests, $loans_res
 */
?>

<h1 class="rp-page-title">Welcome back, <?php echo htmlspecialchars($reader['name']); ?>! 👋</h1>
<p class="rp-page-sub">Here's your reading activity at a glance.</p>

<div class="rp-stats">
    <div class="rp-stat" style="border-left-color:#0ea5e9">
        <h3>Total Loans</h3>
        <div class="num" style="color:#0284c7"><?php echo $total_loans; ?></div>
        <p class="sub">All-time borrows</p>
    </div>
    <div class="rp-stat" style="border-left-color:#10b981">
        <h3>Borrowing</h3>
        <div class="num" style="color:#10b981"><?php echo $active_books; ?></div>
        <p class="sub">Books in hand</p>
    </div>
    <div class="rp-stat" style="border-left-color:#eab308">
        <h3>Pending Requests</h3>
        <div class="num" style="color:#eab308"><?php echo $pending_requests; ?></div>
        <p class="sub">Awaiting approval</p>
    </div>
    <div class="rp-stat" style="border-left-color:#ef4444">
        <h3>Overdue</h3>
        <div class="num" style="color:#ef4444"><?php echo $overdue_books; ?></div>
        <p class="sub">Please return ASAP</p>
    </div>
</div>

<h2 style="font-size:1rem; font-weight:700; margin-bottom:1rem;">📖 Currently Borrowing</h2>

<?php if (mysqli_num_rows($loans_res) == 0): ?>
    <div class="rp-empty">
        <div class="icon">📭</div>
        <p>No active loans. Start reading!</p>
        <a href="<?php echo BASE_URL; ?>reader/book.php" class="rp-btn" style="margin-top:1rem;">Browse Books</a>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="datatable">
            <thead><tr>
                <th style="text-align:left">Book</th>
                <th>Borrowed On</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr></thead>
            <tbody>
            <?php while ($r = mysqli_fetch_assoc($loans_res)): ?>
                <tr>
                    <td>
                        <a href="<?php echo BASE_URL; ?>reader/book.php?id=<?php echo $r['book_id']; ?>"
                           style="font-weight:600;color:#0284c7;text-decoration:none">
                            <?php echo htmlspecialchars($r['title']); ?>
                        </a>
                    </td>
                    <td style="text-align:center;color:#64748b;font-size:0.875rem">
                        <?php echo $r['borrow_date'] ? date('M d, Y', strtotime($r['borrow_date'])) : '—'; ?>
                    </td>
                    <td style="text-align:center;font-size:0.875rem">
                        <?php echo ($r['item_status'] == 'pending') ? '—'
                            : ($r['due_date'] ? date('M d, Y', strtotime($r['due_date'])) : '—'); ?>
                    </td>
                    <td style="text-align:center">
                        <?php if ($r['item_status'] == 'pending'): ?>
                            <span class="badge-warn">Pending Approval</span>
                        <?php elseif ($r['days_left'] < 0): ?>
                            <span class="badge-danger">Overdue <?php echo abs($r['days_left']); ?>d</span>
                        <?php elseif ($r['days_left'] <= 2): ?>
                            <span class="badge-warn">Due in <?php echo $r['days_left']; ?>d</span>
                        <?php else: ?>
                            <span class="badge-ok"><?php echo $r['days_left']; ?> days left</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div style="text-align:right;margin-top:1rem">
        <a href="<?php echo BASE_URL; ?>reader/my_loans.php"
           style="color:#0284c7;font-weight:600;text-decoration:none;font-size:0.875rem">
            View full history →
        </a>
    </div>
<?php endif; ?>
