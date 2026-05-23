<?php
/** Redirect stub — đã chuyển sang dashboard/reader-dashboard.php */
require_once '../env/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Location: ' . BASE_URL . 'dashboard/reader-dashboard.php');
exit();
