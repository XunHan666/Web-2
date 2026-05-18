<?php
/**
 * Database Configuration and Connection
 */

// 1. Thông số kết nối dành cho Localhost
$hostname = "localhost";
$username = "root";
$password = "";
$database = "library_db";

// Establish connection to MySQL database
$db_connect = mysqli_connect($hostname, $username, $password, $database);

// Check if connection was successful
if (!$db_connect) {
    die("Database Connection failed: " . mysqli_connect_error());
}

// Set character set to UTF-8 for consistent text encoding
mysqli_set_charset($db_connect, "utf8");

// Define Base URL to handle absolute paths for links and assets
// Auto-detect project base path relative to the document root
$root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$dir = str_replace('\\', '/', dirname(__DIR__));
$base_path = str_replace($root, '', $dir);
define('BASE_URL', '/' . trim($base_path, '/') . '/');
?>