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
    $res = mysqli_query($db_connect, "SELECT * FROM users WHERE id = $user_id");
    if ($row = mysqli_fetch_assoc($res)) {
        $user_data = $row;
    } else {
        header("Location: users.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($db_connect, trim($_POST['username']));
    $full_name = mysqli_real_escape_string($db_connect, trim($_POST['full_name']));
    $plain_password = $_POST['password'];
    $role_id = (int)$_POST['role_id'];

    if (empty($username) || empty($full_name) || (empty($plain_password) && !isset($user_id))) {
        $submission_error = "Fields missing.";
    } else {
        if (isset($user_id) && $user_id == 1) {
            $submission_error = "Cannot modify the primary Admin account from this panel.";
        } else {
            $hash = password_hash($plain_password, PASSWORD_DEFAULT);
            if (isset($user_id)) {
                $sql = "UPDATE users SET username='$username', full_name='$full_name', role_id=$role_id";
                if (!empty($plain_password)) {
                    $sql .= ", password='$hash', reset_requested=0";
                }
                $sql .= " WHERE id=$user_id";
            } else {
                $sql = "INSERT INTO users (username, password, full_name, role_id, status) VALUES ('$username', '$hash', '$full_name', $role_id, 'active')";
            }
            
            if (mysqli_query($db_connect, $sql)) {
                showAlert(isset($user_id) ? "Account updated." : "Account created.");
                echo "<script>setTimeout(() => { window.location.href = 'users.php'; }, 2000);</script>";
            }
        }
    }
}

$roles_lookup_res = mysqli_query($db_connect, "SELECT * FROM roles WHERE id != 1");
include 'views/user_editor.php';
include '../inc/footer.php';
