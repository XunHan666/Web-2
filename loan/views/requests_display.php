<div class="page-header">
    <div class="header-content">
        <h1>Pending Loan Requests</h1>
        <p class="text-muted">Review and approve borrowing requests from readers.</p>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php 
        if ($_GET['success'] == 'approved') echo "Request approved successfully. The book is now borrowed.";
        if ($_GET['success'] == 'rejected') echo "Request rejected successfully. The book is available again.";
        ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>Reader</th>
                        <th>Book Requested</th>
                        <th>Barcode</th>
                        <th>Requested On</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($requests_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($requests_result)): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($row['reader_name']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($row['phone']); ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?php echo htmlspecialchars($row['book_title']); ?></div>
                                </td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['barcode']); ?></span></td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                <td class="text-end">
                                    <form action="process_request.php" method="POST" class="d-inline">
                                        <input type="hidden" name="loan_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger ms-1">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No pending loan requests at the moment.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
