<?php
require_once '../env/config.php';
$page_title = 'My Profile';
include '../inc/header.php';

$uid = $_SESSION['user_id'];
$rid = $reader['id'];
$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_POST['action'] === 'update_profile') {
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (!$name)  $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
        if (!$phone) $errors[] = 'Phone is required.';

        if (!$errors) {
            $chk = mysqli_prepare($db_connect, "SELECT id FROM users WHERE email=? AND id!=?");
            mysqli_stmt_bind_param($chk, 'si', $email, $uid);
            mysqli_stmt_execute($chk);
            if (mysqli_stmt_num_rows(mysqli_stmt_get_result($chk)) > 0) $errors[] = 'Email already in use.';
        }

        if (!$errors) {
            mysqli_begin_transaction($db_connect);
            try {
                $s1 = mysqli_prepare($db_connect, "UPDATE users   SET full_name=?,email=?        WHERE id=?");
                $s2 = mysqli_prepare($db_connect, "UPDATE readers SET name=?,email=?,phone=? WHERE id=?");
                mysqli_stmt_bind_param($s1,'ssi',$name,$email,$uid);
                mysqli_stmt_bind_param($s2,'sssi',$name,$email,$phone,$rid);
                mysqli_stmt_execute($s1); mysqli_stmt_execute($s2);
                mysqli_commit($db_connect);
                $_SESSION['full_name'] = $name;
                $success = 'Profile updated.';
                // Reload reader data
                $r2 = mysqli_prepare($db_connect, "SELECT * FROM readers WHERE id=?");
                mysqli_stmt_bind_param($r2,'i',$rid); mysqli_stmt_execute($r2);
                $reader = mysqli_fetch_assoc(mysqli_stmt_get_result($r2));
            } catch (Exception $e) { mysqli_rollback($db_connect); $errors[] = 'Update failed.'; }
        }
    }

    if ($_POST['action'] === 'change_password') {
        $cur = $_POST['current_password'] ?? '';
        $new = $_POST['new_password']     ?? '';
        $cnf = $_POST['confirm_password'] ?? '';
        $row = mysqli_fetch_assoc(mysqli_query($db_connect, "SELECT password FROM users WHERE id=$uid"));
        if (!password_verify($cur, $row['password'])) $errors[] = 'Current password incorrect.';
        elseif (strlen($new) < 6)  $errors[] = 'Min. 6 characters.';
        elseif ($new !== $cnf)     $errors[] = 'Passwords do not match.';
        if (!$errors) {
            $h = password_hash($new, PASSWORD_DEFAULT);
            $s = mysqli_prepare($db_connect,"UPDATE users SET password=? WHERE id=?");
            mysqli_stmt_bind_param($s,'si',$h,$uid); mysqli_stmt_execute($s);
            $success = 'Password changed.';
        }
    }
}

$username = mysqli_fetch_assoc(mysqli_query($db_connect,"SELECT username FROM users WHERE id=$uid"))['username'];
?>

include 'views/profile_display.php';
include '../inc/footer.php';
