<?php
/**
 * Reader Return Request — Form + Handler
 *
 * GET  → Hiển thị form chọn ngày trả + fine preview
 * POST → Xử lý submit, insert vào requests
 */
require_once '../env/config.php';
require_once '../system/sys_rules.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Guard: reader only
if (!isset($_SESSION['account_id']) || $_SESSION['role_id'] != 3) {
    header('Location: ../authen/login.php'); exit();
}

// Load reader record
$stmt = mysqli_prepare($db_connect, "SELECT * FROM readers WHERE account_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['account_id']);
mysqli_stmt_execute($stmt);
$reader = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$reader) { header('Location: ../authen/login.php'); exit(); }

$loan_id   = (int)($_REQUEST['loan_id'] ?? 0);
$fine_rate = (int)get_setting('fine_per_day', 5000);

if (!$loan_id) { header('Location: my_loans.php'); exit(); }

// Fetch loan — must belong to this reader and be active
$loan = mysqli_fetch_assoc(mysqli_query($db_connect,
    "SELECT l.*, r.name reader_name
     FROM loans l JOIN readers r ON l.reader_id = r.id
     WHERE l.id = $loan_id AND l.reader_id = {$reader['id']}
       AND l.status IN ('ongoing','partial')"
));
if (!$loan) { header('Location: my_loans.php?error=not_found'); exit(); }

// Check không có pending return request nào cho loan này
$existing = mysqli_fetch_array(mysqli_query($db_connect,
    "SELECT COUNT(*) FROM requests
     WHERE type='return_book' AND status='pending' AND target_id = $loan_id"
))[0];
if ($existing > 0) { header('Location: my_loans.php?error=already_requested'); exit(); }

// Fetch items
$items_res = mysqli_query($db_connect,
    "SELECT ld.id detail_id, b.title, ld.status
     FROM loan_details ld
     JOIN book_copies bc ON ld.book_copy_id = bc.id
     JOIN books b ON bc.book_id = b.id
     WHERE ld.loan_id = $loan_id AND ld.status = 'borrowed'"
);
$items = [];
while ($row = mysqli_fetch_assoc($items_res)) $items[] = $row;

// ─── POST HANDLER ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $planned_date = $_POST['planned_return_date'] ?? date('Y-m-d');

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $planned_date)) {
        $planned_date = date('Y-m-d');
    }

    $notes_json = json_encode([
        'loan_id'             => $loan_id,
        'planned_return_date' => $planned_date,
        'due_date'            => $loan['due_date'],
        'fine_rate'           => $fine_rate,
    ]);
    $notes_escaped = mysqli_real_escape_string($db_connect, $notes_json);
    $account_id    = (int)$_SESSION['account_id'];

    $ok = mysqli_query($db_connect,
        "INSERT INTO requests (type, account_id, target_id, notes, status)
         VALUES ('return_book', $account_id, $loan_id, '$notes_escaped', 'pending')"
    );

    if ($ok) {
        header('Location: my_loans.php?success=return_requested'); exit();
    } else {
        $error_msg = 'Server error, please try again.';
    }
}

// ─── GET: Render form ────────────────────────────────────────────
$page_title = 'Request Return';
include '../inc/header.php';
?>

<div style="max-width: 620px; margin: 0 auto;">

    <!-- Back -->
    <a href="my_loans.php" style="text-decoration:none; color:#64748b; display:inline-flex; align-items:center; gap:0.4rem; margin-bottom:1.5rem; font-size:0.9rem;">
        ← Back to My Loans
    </a>

    <div style="background:white; border-radius:16px; padding:2.5rem; box-shadow:0 4px 24px rgba(30,37,43,0.07);">

        <h1 style="font-size:1.5rem; font-weight:800; color:var(--text-color); margin-bottom:0.4rem;">📦 Request Book Return</h1>
        <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:2rem;">
            Choose the date you plan to bring the book to the library.
        </p>

        <!-- Loan Info -->
        <div style="background:#f8fafc; border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1.75rem; border:1px solid var(--border-color);">
            <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); margin-bottom:0.5rem;">Loan Session #<?php echo $loan_id; ?></div>
            <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
                <div>
                    <div style="font-size:0.78rem; color:var(--text-muted);">Borrowed on</div>
                    <div style="font-weight:700;"><?php echo date('M d, Y', strtotime($loan['borrow_date'])); ?></div>
                </div>
                <div>
                    <div style="font-size:0.78rem; color:var(--text-muted);">Due date</div>
                    <div style="font-weight:700; color:<?php echo (strtotime($loan['due_date']) < time()) ? 'var(--danger)' : 'var(--success)'; ?>">
                        <?php echo date('M d, Y', strtotime($loan['due_date'])); ?>
                        <?php if (strtotime($loan['due_date']) < time()): ?>
                            <span style="font-size:0.75rem;">(Already overdue)</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Books list -->
            <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border-color);">
                <div style="font-size:0.78rem; color:var(--text-muted); margin-bottom:0.5rem;">Books to return:</div>
                <?php foreach ($items as $item): ?>
                    <div style="font-weight:600; font-size:0.9rem; padding:0.25rem 0;">📚 <?php echo htmlspecialchars($item['title']); ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($error_msg)): ?>
        <div style="background:#fce8e6; color:var(--danger); padding:0.85rem 1rem; border-radius:8px; margin-bottom:1.25rem; font-size:0.9rem;">
            ⚠️ <?php echo $error_msg; ?>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" id="returnForm">
            <label style="font-weight:600; font-size:0.9rem; display:block; margin-bottom:0.5rem;">
                📅 Planned Return Date
            </label>
            <input type="date"
                   name="planned_return_date"
                   id="plannedDate"
                   min="<?php echo date('Y-m-d'); ?>"
                   value="<?php echo date('Y-m-d'); ?>"
                   style="width:100%; padding:0.8rem 1rem; border:1.5px solid var(--border-color); border-radius:8px; font-size:1rem; margin-bottom:1.5rem;"
                   required>

            <!-- Fine Preview Banner -->
            <div id="finePreview" style="display:none; border-radius:12px; padding:1.4rem 1.6rem; margin-bottom:1.5rem; border-left:5px solid; transition:all 0.3s ease;"></div>

            <button type="submit" id="submitBtn"
                    style="width:100%; background:var(--primary-color); color:white; border:none; padding:0.9rem; border-radius:10px; font-size:1rem; font-weight:700; cursor:pointer; transition:all 0.3s ease;">
                Submit Return Request
            </button>
        </form>
    </div>
</div>

<script>
const dueDate   = new Date('<?php echo $loan['due_date']; ?>');
const fineRate  = <?php echo $fine_rate; ?>;
const dateInput = document.getElementById('plannedDate');
const preview   = document.getElementById('finePreview');
const submitBtn = document.getElementById('submitBtn');

// Inject keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes warnPulse {
        0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.35); }
        50%      { box-shadow: 0 0 0 10px rgba(239,68,68,0); }
    }
    @keyframes warnOrange {
        0%,100% { box-shadow: 0 0 0 0 rgba(249,115,22,0.3); }
        50%      { box-shadow: 0 0 0 8px rgba(249,115,22,0); }
    }
`;
document.head.appendChild(style);

function formatCurrency(n) {
    return n.toLocaleString('vi-VN') + ' VNĐ';
}

function updatePreview() {
    const planned = new Date(dateInput.value);
    if (isNaN(planned)) return;
    const daysLate = Math.ceil((planned - dueDate) / 86400000);

    if (daysLate <= 0) {
        preview.style.cssText = 'display:block; border-radius:12px; padding:1.4rem 1.6rem; margin-bottom:1.5rem; border-left:5px solid #22c55e; background:#f0fdf4; color:#14532d; animation:none;';
        preview.innerHTML = `
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <span style="font-size:2rem;">✅</span>
                <div>
                    <strong style="font-size:1.05rem;display:block;margin-bottom:0.15rem;">No late fee!</strong>
                    <span style="font-size:0.875rem;opacity:0.85;">You plan to return on time or early — no fine will be charged.</span>
                </div>
            </div>`;
        submitBtn.style.background = 'var(--primary-color)';
        submitBtn.innerHTML = 'Submit Return Request';

    } else {
        const fine = daysLate * fineRate;
        let cfg;

        if (daysLate >= 7) {
            cfg = {
                border:'#ef4444', bg:'#fef2f2', color:'#7f1d1d',
                anim:'warnPulse 1.6s ease-in-out infinite',
                icon:'🚨',
                urgency:`<div style="margin-top:0.8rem;padding:0.65rem 1rem;background:#fee2e2;border-radius:8px;font-size:0.875rem;font-weight:700;color:#991b1b;">
                    🔥 Severely overdue — return ASAP to stop the fine growing!
                </div>`,
                btn:'#dc2626', btnLabel:'🚨 Submit (Heavy Fine Applies)'
            };
        } else if (daysLate >= 3) {
            cfg = {
                border:'#f97316', bg:'#fff7ed', color:'#7c2d12',
                anim:'warnOrange 2s ease-in-out infinite',
                icon:'⚠️',
                urgency:`<div style="margin-top:0.8rem;padding:0.65rem 1rem;background:#ffedd5;border-radius:8px;font-size:0.875rem;font-weight:700;color:#9a3412;">
                    ⏰ The sooner you return, the less you pay.
                </div>`,
                btn:'#ea580c', btnLabel:'⚠️ Submit (Fine Applies)'
            };
        } else {
            cfg = {
                border:'#f59e0b', bg:'#fffbeb', color:'#78350f',
                anim:'none',
                icon:'⚠️',
                urgency:`<div style="margin-top:0.8rem;padding:0.65rem 1rem;background:#fef3c7;border-radius:8px;font-size:0.875rem;font-weight:600;color:#92400e;">
                    💡 Try returning a bit earlier to avoid this fine.
                </div>`,
                btn:'#d97706', btnLabel:'⚠️ Submit (Fine Applies)'
            };
        }

        preview.style.cssText = `display:block;border-radius:12px;padding:1.4rem 1.6rem;margin-bottom:1.5rem;border-left:5px solid ${cfg.border};background:${cfg.bg};color:${cfg.color};animation:${cfg.anim};`;
        preview.innerHTML = `
            <div style="display:flex;align-items:flex-start;gap:0.9rem;">
                <span style="font-size:2.2rem;flex-shrink:0;margin-top:-4px;">${cfg.icon}</span>
                <div style="flex:1;">
                    <strong style="font-size:1.05rem;">Late Return Warning</strong>
                    <div style="font-size:0.875rem;margin-top:0.2rem;">
                        Your planned date is <strong>${daysLate} day${daysLate > 1 ? 's' : ''}</strong> past the due date.
                    </div>
                    <div style="font-size:2rem;font-weight:900;letter-spacing:-0.03em;margin:0.4rem 0 0.1rem;line-height:1;">
                        ${formatCurrency(fine)}
                    </div>
                    <div style="font-size:0.78rem;opacity:0.7;">
                        ${formatCurrency(fineRate)}/day × ${daysLate} day${daysLate>1?'s':''} — final fine charged on day staff confirms
                    </div>
                    ${cfg.urgency}
                </div>
            </div>`;

        submitBtn.style.background = cfg.btn;
        submitBtn.innerHTML = cfg.btnLabel;
    }
}

dateInput.addEventListener('change', updatePreview);
updatePreview();
</script>

<?php include '../inc/footer.php'; ?>
