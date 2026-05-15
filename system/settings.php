<?php
/**
 * Settings - Controller
 */
require_once '../env/config.php';
require_once 'sys_rules.php';
include '../inc/header.php';

if ($_SESSION['role_id'] != 1) {
    showAlert("Admin only.", "error");
    echo "<script>window.location.href = '../index.php';</script>";
    exit();
}

$status = ''; $status_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    update_setting('fine_per_day', $_POST['fine_per_day']);
    update_setting('max_loan_days', $_POST['max_loan_days']);
    update_setting('max_books_per_reader', $_POST['max_books_per_reader']);
    showAlert("Settings updated.");
}

$current_fine = get_setting('fine_per_day', '5000');
$current_duration = get_setting('max_loan_days', '5');
$current_max_books = get_setting('max_books_per_reader', '3');

include 'views/settings_editor.php';
include '../inc/footer.php';
