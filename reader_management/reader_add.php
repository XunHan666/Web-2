<?php
/**
 * Reader Add/Edit - Controller
 */
require_once '../env/config.php';
include '../inc/header.php';

$reader_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$status_message = ''; $status_type = '';
$reader_data = ['name' => '', 'phone' => '', 'email' => '', 'address' => '', 'dob' => '', 'gender' => 'male', 'status' => 'active'];

if ($reader_id) {
    $res = mysqli_query($db_connect, "SELECT * FROM readers WHERE id = $reader_id");
    if ($row = mysqli_fetch_assoc($res)) $reader_data = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($db_connect, $_POST['name']);
    $email = mysqli_real_escape_string($db_connect, $_POST['email']);
    $phone = mysqli_real_escape_string($db_connect, $_POST['phone']);
    $status = mysqli_real_escape_string($db_connect, $_POST['status']);
    $address = mysqli_real_escape_string($db_connect, $_POST['address']);
    $dob = mysqli_real_escape_string($db_connect, $_POST['dob']);
    $gender = mysqli_real_escape_string($db_connect, $_POST['gender']);

    if ($reader_id) {
        $sql = "UPDATE readers SET name='$name', email='$email', phone='$phone', address='$address', dob='$dob', gender='$gender', status='$status' WHERE id=$reader_id";
    } else {
        $sql = "INSERT INTO readers (name, email, phone, address, dob, gender, status) VALUES ('$name', '$email', '$phone', '$address', '$dob', '$gender', '$status')";
    }

    if (mysqli_query($db_connect, $sql)) {
        showAlert("Reader record saved.");
        echo "<script>setTimeout(() => { window.location.href = 'readers.php'; }, 2000);</script>";
    }
}

include 'views/reader_editor.php';
include '../inc/footer.php';
