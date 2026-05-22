<?php
require_once '../env/config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header('Location: ../authen/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: requests.php');
    exit();
}

$loan_id = (int)$_POST['loan_id'];
$action = $_POST['action'];

if (!$loan_id || !in_array($action, ['approve', 'reject'])) {
    header('Location: requests.php');
    exit();
}

mysqli_begin_transaction($db_connect);

try {
    if ($action === 'approve') {
        $borrow_date = date('Y-m-d');
        // due_date was already calculated during request, but we could update it relative to borrow_date
        $due_date = date('Y-m-d', strtotime('+5 days'));
        
        mysqli_query($db_connect, "UPDATE loans SET status = 'ongoing', borrow_date = '$borrow_date', due_date = '$due_date' WHERE id = $loan_id");
        mysqli_query($db_connect, "UPDATE loan_details SET status = 'borrowed' WHERE loan_id = $loan_id");
        
        // Cập nhật trạng thái copy sách
        $details_res = mysqli_query($db_connect, "SELECT book_copy_id FROM loan_details WHERE loan_id = $loan_id");
        while ($d = mysqli_fetch_assoc($details_res)) {
            $cid = $d['book_copy_id'];
            mysqli_query($db_connect, "UPDATE book_copies SET status = 'borrowed' WHERE id = $cid");
        }
        
        mysqli_commit($db_connect);
        header('Location: requests.php?success=approved');
        
    } elseif ($action === 'reject') {
        mysqli_query($db_connect, "UPDATE loans SET status = 'rejected' WHERE id = $loan_id");
        mysqli_query($db_connect, "UPDATE loan_details SET status = 'rejected' WHERE loan_id = $loan_id");
        
        // Trả lại copy sách về available
        $details_res = mysqli_query($db_connect, "SELECT book_copy_id FROM loan_details WHERE loan_id = $loan_id");
        while ($d = mysqli_fetch_assoc($details_res)) {
            $cid = $d['book_copy_id'];
            mysqli_query($db_connect, "UPDATE book_copies SET status = 'available' WHERE id = $cid");
        }
        
        mysqli_commit($db_connect);
        header('Location: requests.php?success=rejected');
    }
} catch (Exception $e) {
    mysqli_rollback($db_connect);
    header('Location: requests.php?error=server_error');
}
exit();
?>
