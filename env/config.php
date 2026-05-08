<?php
/**
 * Database Configuration and Connection
 */

// Establish connection to MySQL database
// Parameters: host, username, password, database_name
$db_connect = mysqli_connect("localhost", "root", "", "library_db");

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
