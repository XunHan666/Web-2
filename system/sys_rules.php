<?php
/**
 * System Rules Service - Fixed Schema
 */

function get_setting($key, $default = '') {
    global $db_connect;
    $safe_key = mysqli_real_escape_string($db_connect, $key);
    $query = mysqli_query($db_connect, "SELECT setting_value FROM settings WHERE setting_key = '$safe_key'");
    if ($row = mysqli_fetch_assoc($query)) return $row['setting_value'];
    return $default;
}

function update_setting($key, $value) {
    global $db_connect;
    $safe_key = mysqli_real_escape_string($db_connect, $key);
    $safe_value = mysqli_real_escape_string($db_connect, $value);
    
    $check = mysqli_query($db_connect, "SELECT setting_key FROM settings WHERE setting_key = '$safe_key'");
    if (mysqli_num_rows($check) > 0) {
        return mysqli_query($db_connect, "UPDATE settings SET setting_value = '$safe_value' WHERE setting_key = '$safe_key'");
    } else {
        return mysqli_query($db_connect, "INSERT INTO settings (setting_key, setting_value) VALUES ('$safe_key', '$safe_value')");
    }
}

if (!function_exists('showAlert')) {
    function showAlert($message, $type = 'success') {
        // Standardize icon
        $icon = ($type == 'error') ? 'error' : 'success';
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({ 
                    icon: '$icon', 
                    title: 'Notification', 
                    text: '" . addslashes($message) . "',
                    confirmButtonColor: '#3b82f6'
                });
            });
        </script>";
    }
}
