<?php
/**
 * System-wide dynamic configuration manager
 * Fetches settings from the database (settings table)
 */

function get_setting($key, $default = null) {
    global $db_connect;
    
    // Fallback if connection is handled globally
    if (!$db_connect) {
        require_once dirname(__DIR__) . '/env/config.php';
    }
    
    $safe_key = mysqli_real_escape_string($db_connect, $key);
    $query = "SELECT setting_value FROM settings WHERE setting_key = '$safe_key'";
    $result = mysqli_query($db_connect, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['setting_value'];
    }
    
    return $default;
}

function update_setting($key, $value) {
    global $db_connect;
    
    $safe_key = mysqli_real_escape_string($db_connect, $key);
    $safe_value = mysqli_real_escape_string($db_connect, $value);
    
    $query = "UPDATE settings SET setting_value = '$safe_value' WHERE setting_key = '$safe_key'";
    return mysqli_query($db_connect, $query);
}
?>
