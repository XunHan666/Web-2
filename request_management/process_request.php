<?php
require_once '../env/config.php';
require_once '../system/sys_rules.php';
require_once '../inc/alerts.php';
require_once '../inc/role_guard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['account_id']) || !in_array($_SESSION['role_id'], [1, 2], true)) {
    header('Location: ' . BASE_URL . 'authen/login.php');
    exit();
}

$role_id = (int)$_SESSION['role_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['req_id']) || !in_array($_POST['action'], ['approve', 'reject'])) {
    header('Location: ' . BASE_URL . 'request_management/requests.php');
    exit();
}

$req_id = (int)$_POST['req_id'];
$action = $_POST['action'];

$req_res = mysqli_query($db_connect, "SELECT * FROM requests WHERE id = $req_id AND status = 'pending'");
if (!$req = mysqli_fetch_assoc($req_res)) {
    header('Location: ' . BASE_URL . 'request_management/requests.php');
    exit();
}

if ($role_id === 1 && !admin_can_process_request($req['type'])) {
    setFlashAlert('Borrow and return requests are handled by librarians.', 'error');
    header('Location: ' . BASE_URL . 'request_management/requests.php');
    exit();
}

if ($role_id === 2 && !librarian_can_process_request($req['type'])) {
    setFlashAlert('Only administrators can process this request type.', 'error');
    header('Location: ' . BASE_URL . 'request_management/requests.php');
    exit();
}

$fine_rate      = (int)get_setting('fine_per_day', 5000);
$max_loan_days  = (int)get_setting('max_loan_days', 5);

mysqli_begin_transaction($db_connect);
try {
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    mysqli_query($db_connect, "UPDATE requests SET status = '$new_status' WHERE id = $req_id");

    // ── BORROW BOOK ──────────────────────────────────────────────
    if ($req['type'] === 'borrow_book') {
        $loan_id  = $req['target_id'];
        $l_status = ($action === 'approve') ? 'ongoing'  : 'rejected';
        $ld_status= ($action === 'approve') ? 'borrowed' : 'rejected';
        $bc_status= ($action === 'approve') ? 'borrowed' : 'available';

        $sql_loan = "UPDATE loans SET status = '$l_status'";
        if ($action === 'approve') {
            $sql_loan .= ", borrow_date = CURDATE(), due_date = DATE_ADD(CURDATE(), INTERVAL $max_loan_days DAY)";
        }
        mysqli_query($db_connect, "$sql_loan WHERE id = $loan_id");
        mysqli_query($db_connect, "UPDATE loan_details SET status = '$ld_status' WHERE loan_id = $loan_id");
        mysqli_query($db_connect, "UPDATE book_copies bc JOIN loan_details ld ON bc.id = ld.book_copy_id SET bc.status = '$bc_status' WHERE ld.loan_id = $loan_id");

    // ── RETURN BOOK ──────────────────────────────────────────────
    } elseif ($req['type'] === 'return_book') {
        $loan_id = $req['target_id'];

        if ($action === 'approve') {
            $today = date('Y-m-d');

            // Fetch due_date to calculate actual fine
            $loan_info = mysqli_fetch_assoc(mysqli_query($db_connect,
                "SELECT due_date FROM loans WHERE id = $loan_id"
            ));
            $due_date  = $loan_info['due_date'];

            // Fine = days past due × rate (0 if on time)
            $days_late  = max(0, (int)ceil((strtotime($today) - strtotime($due_date)) / 86400));
            $total_fine = $days_late * $fine_rate;

            // Mark all borrowed items as returned with today's date & fine
            mysqli_query($db_connect,
                "UPDATE loan_details
                 SET status = 'returned', return_date = '$today', fine_amount = $total_fine
                 WHERE loan_id = $loan_id AND status = 'borrowed'"
            );

            // Release book copies back to available
            mysqli_query($db_connect,
                "UPDATE book_copies bc
                 JOIN loan_details ld ON bc.id = ld.book_copy_id
                 SET bc.status = 'available'
                 WHERE ld.loan_id = $loan_id"
            );

            // Sync loan master status
            $metrics = mysqli_fetch_assoc(mysqli_query($db_connect,
                "SELECT COUNT(*) as total,
                        SUM(CASE WHEN status='returned' THEN 1 ELSE 0 END) as returned
                 FROM loan_details WHERE loan_id = $loan_id"
            ));
            $new_loan_status = ($metrics['returned'] == $metrics['total']) ? 'closed' : 'partial';
            mysqli_query($db_connect, "UPDATE loans SET status = '$new_loan_status' WHERE id = $loan_id");
        }
        // Rejected: loan stays ongoing, no changes

    // ── LIBRARIAN REGISTRATION ───────────────────────────────────
    } elseif ($req['type'] === 'librarian_registration' && $action === 'approve') {
        mysqli_query($db_connect, "UPDATE accounts SET status = 'active' WHERE id = {$req['target_id']}");
    }

    mysqli_commit($db_connect);
    header('Location: ' . BASE_URL . 'request_management/requests.php?success=' . urlencode($new_status));
} catch (Exception $e) {
    mysqli_rollback($db_connect);
    header('Location: ' . BASE_URL . 'request_management/requests.php?error=server_error');
}
exit();
?>
