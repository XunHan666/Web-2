<?php
/**
 * Staff Registration - Controller
 */
require_once '../env/config.php';
include '../inc/header.php';

if ($_SESSION['role_id'] != 1) {
    showAlert("Admin privileges required.", "error");
    echo "<script>window.location.href = '" . BASE_URL . "index.php';</script>";
    exit();
}

$submission_error = '';
$user_data = [];

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $res = mysqli_query($db_connect, "SELECT * FROM accounts WHERE id = $user_id");
    if ($row = mysqli_fetch_assoc($res)) {
        $user_data = $row;
    } else {
        header("Location: accounts.php");
        exit();
    }
}

$req_id = isset($_GET['req_id']) ? (int)$_GET['req_id'] : (isset($_POST['req_id']) ? (int)$_POST['req_id'] : 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($db_connect, trim($_POST['username']));
    $full_name = mysqli_real_escape_string($db_connect, trim($_POST['full_name']));
    $plain_password = $_POST['password'];
    $role_id = (int)$_POST['role_id'];
    $phone = mysqli_real_escape_string($db_connect, trim($_POST['phone'] ?? ''));
    $email = mysqli_real_escape_string($db_connect, trim($_POST['email'] ?? ''));
    $address = mysqli_real_escape_string($db_connect, trim($_POST['address'] ?? ''));
    $dob = mysqli_real_escape_string($db_connect, trim($_POST['dob'] ?? ''));
    $gender = mysqli_real_escape_string($db_connect, trim($_POST['gender'] ?? 'male'));

    if (empty($username) || empty($full_name) || (empty($plain_password) && !isset($user_id))) {
        $submission_error = "Fields missing.";
    } else {
        if (isset($user_id) && $user_id == 1) {
            $submission_error = "Cannot modify the primary Admin account from this panel.";
        } else {
            $hash = password_hash($plain_password, PASSWORD_DEFAULT);
            if (isset($user_id)) {
                $sql = "UPDATE accounts SET username='$username', full_name='$full_name', role_id=$role_id, phone='$phone', email='$email', address='$address', dob='$dob', gender='$gender'";
                if (!empty($plain_password)) {
                    $sql .= ", password='$hash'";
                }
                $sql .= " WHERE id=$user_id";
            } else {
                $sql = "INSERT INTO accounts (username, password, full_name, phone, email, address, dob, gender, role_id, status) VALUES ('$username', '$hash', '$full_name', '$phone', '$email', '$address', '$dob', '$gender', $role_id, 'active')";
            }
            
            if (mysqli_query($db_connect, $sql)) {
                if (isset($user_id) && !empty($plain_password) && $req_id > 0) {
                    mysqli_query($db_connect, "UPDATE requests SET status='approved' WHERE id=$req_id AND type='password_reset'");
                }
                showAlert(isset($user_id) ? "Account updated." : "Account created.");
                echo "<script>setTimeout(() => { window.location.href = 'accounts.php'; }, 2000);</script>";
            }
        }
    }
}

$roles_lookup_res = mysqli_query($db_connect, "SELECT * FROM roles WHERE id != 1");
include 'views/account_editor.php';
include '../inc/footer.php';
