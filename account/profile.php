<?php
require_once '../env/config.php';
$page_title = 'My Profile';
include '../inc/header.php';

$uid = $_SESSION['account_id'];
$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'update_profile') {
        $name  = trim($_POST['full_name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $dob = trim($_POST['dob'] ?? '');
        $gender = trim($_POST['gender'] ?? 'male');

        if (!$name)  $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
        if (!$phone) $errors[] = 'Phone is required.';

        if (!$errors) {
            $chk = mysqli_prepare($db_connect, "SELECT id FROM accounts WHERE email=? AND id!=?");
            mysqli_stmt_bind_param($chk, 'si', $email, $uid);
            mysqli_stmt_execute($chk);
            if (mysqli_num_rows(mysqli_stmt_get_result($chk)) > 0) $errors[] = 'Email already in use.';
        }

        if (!$errors) {
            try {
                $s1 = mysqli_prepare($db_connect, "UPDATE accounts SET full_name=?,email=?,phone=?,address=?,dob=?,gender=? WHERE id=?");
                mysqli_stmt_bind_param($s1,'ssssssi',$name,$email,$phone,$address,$dob,$gender,$uid);
                mysqli_stmt_execute($s1);
                $_SESSION['full_name'] = $name;
                $success = 'Profile updated.';
            } catch (Exception $e) { $errors[] = 'Update failed.'; }
        }
    }

    if ($_POST['action'] === 'change_password') {
        $cur = $_POST['current_password'] ?? '';
        $new = $_POST['new_password']     ?? '';
        $cnf = $_POST['confirm_password'] ?? '';
        $row = mysqli_fetch_assoc(mysqli_query($db_connect, "SELECT password FROM accounts WHERE id=$uid"));
        if (!password_verify($cur, $row['password'])) $errors[] = 'Current password incorrect.';
        elseif (strlen($new) < 6)  $errors[] = 'Min. 6 characters.';
        elseif ($new !== $cnf)     $errors[] = 'Passwords do not match.';
        if (!$errors) {
            $h = password_hash($new, PASSWORD_DEFAULT);
            $s = mysqli_prepare($db_connect,"UPDATE accounts SET password=? WHERE id=?");
            mysqli_stmt_bind_param($s,'si',$h,$uid); mysqli_stmt_execute($s);
            $success = 'Password changed.';
        }
    }
}

$user = mysqli_fetch_assoc(mysqli_query($db_connect,"SELECT * FROM accounts WHERE id=$uid"));
?>
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem 1rem;">
<?php include 'views/profile_display.php'; ?>
</div>
<?php include '../inc/footer.php'; ?>
