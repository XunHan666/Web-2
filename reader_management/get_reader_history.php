<?php
/**
 * AJAX Handler for Reader History Modal
 */
require_once '../env/config.php';
require_once '../inc/role_guard.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_circulation_view();

$reader_id = isset($_GET['reader_id']) ? (int)$_GET['reader_id'] : null;

if (!$reader_id) {
    echo '<div style="padding: 2rem; text-align: center; color: #ef4444;">Invalid Reader ID</div>';
    exit;
}

// Fetch all loan transactions for this reader with their items
$query = "
    SELECT l.id as loan_id, l.borrow_date, l.due_date, l.status as loan_status,
           b.title as book_title, ld.status as item_status, ld.return_date
    FROM loans l
    JOIN loan_details ld ON l.id = ld.loan_id
    JOIN book_copies bc ON ld.book_copy_id = bc.id
    JOIN books b ON bc.book_id = b.id
    WHERE l.reader_id = $reader_id
    ORDER BY l.borrow_date DESC, l.id DESC
";

$res = mysqli_query($db_connect, $query);

if (mysqli_num_rows($res) == 0) {
    echo '<div style="padding: 3rem; text-align: center; color: #64748b;">No loan history found.</div>';
    exit;
}

// Group items by loan ID
$loans = [];
while ($row = mysqli_fetch_assoc($res)) {
    $loans[$row['loan_id']]['info'] = [
        'borrow_date' => $row['borrow_date'],
        'due_date' => $row['due_date'],
        'status' => $row['loan_status']
    ];
    $loans[$row['loan_id']]['items'][] = [
        'title' => $row['book_title'],
        'status' => $row['item_status'],
        'return_date' => $row['return_date']
    ];
}
?>

<div style="max-height: 500px; overflow-y: auto; padding: 1rem;">
    <?php foreach ($loans as $id => $data): ?>
        <div style="margin-bottom: 1.5rem; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
            <div style="background: #f8fafc; padding: 0.75rem 1rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #1e293b;">Transaction #<?php echo $id; ?></strong>
                    <span style="font-size: 0.8rem; color: #64748b; margin-left: 10px;"><?php echo date('M d, Y', strtotime($data['info']['borrow_date'])); ?></span>
                </div>
                <span class="badge badge-<?php echo $data['info']['status'] == 'closed' ? 'returned' : 'borrowing'; ?>" style="font-size: 0.75rem; padding: 0.25rem 0.75rem;">
                    <?php echo strtoupper($data['info']['status']); ?>
                </span>
            </div>
            <div style="padding: 1rem;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="text-align: left; color: #94a3b8; font-size: 0.75rem; text-transform: uppercase;">
                            <th style="padding-bottom: 0.5rem;">Book Title</th>
                            <th style="padding-bottom: 0.5rem; text-align: center;">Status</th>
                            <th style="padding-bottom: 0.5rem; text-align: right;">Return Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['items'] as $item): ?>
                            <tr style="border-top: 1px solid #f1f5f9;">
                                <td style="padding: 0.75rem 0; font-weight: 500; color: #334155;"><?php echo htmlspecialchars($item['title']); ?></td>
                                <td style="padding: 0.75rem 0; text-align: center;">
                                    <span style="font-size: 0.75rem; font-weight: 700; color: <?php echo $item['status'] == 'returned' ? '#10b981' : '#3b82f6'; ?>;">
                                        <?php echo strtoupper($item['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem 0; text-align: right; color: #94a3b8; font-size: 0.8rem;">
                                    <?php echo $item['return_date'] ? date('M d, Y', strtotime($item['return_date'])) : '--'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>
