<?php
/**
 * Database Configuration and Connection
 */

// 1. Debug Mode Toggle (Set to true to show PHP errors on your web screen)
define('DEBUG_MODE', false);
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// 2. Disable mysqli strict exception reporting (prevents HTTP 500 on failure in PHP 8.1+)
mysqli_report(MYSQLI_REPORT_OFF);

// 3. Dynamic Database Configuration Detection (Localhost vs InfinityFree)
$is_localhost = (
    $_SERVER['HTTP_HOST'] === 'localhost' || 
    $_SERVER['SERVER_ADDR'] === '127.0.0.1' || 
    $_SERVER['SERVER_ADDR'] === '::1' ||
    strpos($_SERVER['HTTP_HOST'], '192.168.') === 0 // Handles local network access
);

if ($is_localhost) {
    // Localhost Environment (XAMPP / Laragon)
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "library_db";
} else {
    // Production Environment (InfinityFree)
    $db_host = "sql201.infinityfree.com";
    $db_user = "if0_41965602";
    $db_pass = "3UQXIiXm0bkWH";
    $db_name = "if0_41965602_library_db";
}

// Establish connection to MySQL database
$db_connect = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check if connection was successful
if (!$db_connect) {
    die("Database Connection failed: " . mysqli_connect_error());
}

// Set character set to UTF-8 for consistent text encoding
mysqli_set_charset($db_connect, "utf8");

// 3. Define Base URL to handle absolute paths for links and assets
// Auto-detect project base path relative to the document root
$root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$dir = str_replace('\\', '/', dirname(__DIR__));
$base_path = str_replace($root, '', $dir);

// Fix: Avoid double slashes ('//') when deployed directly in the root directory (htdocs)
$base_url_trimmed = trim($base_path, '/');
define('BASE_URL', ($base_url_trimmed === '') ? '/' : '/' . $base_url_trimmed . '/');
?>