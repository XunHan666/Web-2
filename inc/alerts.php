<?php
/**
 * SweetAlert helpers — safe to include before header (supports flash + redirect).
 */

if (!function_exists('showAlert')) {
    function showAlert($message, $type = 'success') {
        $icon_type = ($type === 'error') ? 'error' : (($type === 'info') ? 'info' : 'success');
        $safe_msg  = addslashes($message);

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{$icon_type}',
                    title: 'LibraryOS Notification',
                    text: '{$safe_msg}',
                    confirmButtonColor: '#1e4646',
                    showClass: { popup: '', backdrop: '' },
                    hideClass: { popup: '', backdrop: '' }
                });
            });
        </script>";
    }
}

if (!function_exists('setFlashAlert')) {
    function setFlashAlert($message, $type = 'success') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['_flash'] = ['message' => $message, 'type' => $type];
    }
}

if (!function_exists('renderFlashAlert')) {
    function renderFlashAlert() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!empty($_SESSION['_flash'])) {
            $flash = $_SESSION['_flash'];
            unset($_SESSION['_flash']);
            showAlert($flash['message'], $flash['type'] ?? 'success');
        }
    }
}
