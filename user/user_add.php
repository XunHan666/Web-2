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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($db_connect, trim($_POST['username']));
    $full_name = mysqli_real_escape_string($db_connect, trim($_POST['full_name']));
    $plain_password = $_POST['password'];
    $role_id = (int)$_POST['role_id'];

    if (empty($username) || empty($plain_password) || empty($full_name)) {
        $submission_error = "Fields missing.";
    } else {
        $hash = password_hash($plain_password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, full_name, role_id, status) VALUES ('$username', '$hash', '$full_name', $role_id, 'active')";
        if (mysqli_query($db_connect, $sql)) {
            showAlert("Account created.");
            echo "<script>setTimeout(() => { window.location.href = 'users.php'; }, 2000);</script>";
        }
    }
}

$roles_lookup_res = mysqli_query($db_connect, "SELECT * FROM roles WHERE id != 1");
include 'views/user_editor.php';
include '../inc/footer.php';
