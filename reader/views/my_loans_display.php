<h1 class="rp-page-title">🔖 My Loan History</h1>
<p class="rp-page-sub">All your borrowing sessions, newest first.</p>

<?php if (empty($loans)): ?>
    <div class="rp-empty">
        <div class="icon">📭</div>
        <p>No loan history yet.</p>
        <a href="book.php" class="rp-btn" style="margin-top:1rem">Browse Books</a>
    </div>
<?php else: ?>
    <?php foreach ($loans as $lid => $data):
        $closed  = $data['status'] === 'closed';
        $pending = $data['status'] === 'pending';
        $rejected = $data['status'] === 'rejected';
        $overdue = !$closed && !$pending && !$rejected && $data['days_left'] < 0;
        
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
                    · Due <?php echo date('M d, Y', strtotime($data['due_date'])); ?>
                </span>
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
    </div>
    <?php endforeach; ?>
<?php endif; ?>
