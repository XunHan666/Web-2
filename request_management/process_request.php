<?php
require_once '../env/config.php';
session_start();

if (!isset($_SESSION['account_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../authen/login.php'); exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['req_id']) || !in_array($_POST['action'], ['approve', 'reject'])) {
    header('Location: requests.php'); exit();
}

$req_id = (int)$_POST['req_id'];
$action = $_POST['action'];

$req_res = mysqli_query($db_connect, "SELECT * FROM requests WHERE id = $req_id AND status = 'pending'");
if (!$req = mysqli_fetch_assoc($req_res)) {
    header('Location: requests.php'); exit();
}

mysqli_begin_transaction($db_connect);
try {
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    mysqli_query($db_connect, "UPDATE requests SET status = '$new_status' WHERE id = $req_id");

    if ($req['type'] === 'borrow_book') {
        $loan_id = $req['target_id'];
        $l_status = ($action === 'approve') ? 'ongoing' : 'rejected';
        $ld_status = ($action === 'approve') ? 'borrowed' : 'rejected';
        $bc_status = ($action === 'approve') ? 'borrowed' : 'available';
        
        $sql_loan = "UPDATE loans SET status = '$l_status'";
        if ($action === 'approve') {
            $sql_loan .= ", borrow_date = CURDATE(), due_date = DATE_ADD(CURDATE(), INTERVAL 5 DAY)";
        }
        mysqli_query($db_connect, "$sql_loan WHERE id = $loan_id");
        mysqli_query($db_connect, "UPDATE loan_details SET status = '$ld_status' WHERE loan_id = $loan_id");
        mysqli_query($db_connect, "UPDATE book_copies bc JOIN loan_details ld ON bc.id = ld.book_copy_id SET bc.status = '$bc_status' WHERE ld.loan_id = $loan_id");
        
    } elseif ($req['type'] === 'librarian_registration' && $action === 'approve') {
        mysqli_query($db_connect, "UPDATE accounts SET status = 'active' WHERE id = {$req['target_id']}");
    }
    
    mysqli_commit($db_connect);
    header("Location: requests.php?success=$new_status");
} catch (Exception $e) {
    mysqli_rollback($db_connect);
    header('Location: requests.php?error=server_error');
}
exit();
?>
