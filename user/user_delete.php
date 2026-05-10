<?php
/**
 * Staff Account Termination API
 * Endpoint for permanently removing librarian accounts from the system.
 * Returns JSON feedback for the management interface.
 */
require_once '../env/config.php';

// Note: Config.php should already handle session_start, but ensuring session context is active.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Access Control: Administrative privileges required
 */
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access Attempt.']);
    exit();
}

/**
 * Handle Deletion Request
 */
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $target_account_id = (int)$_GET['id'];

    // Security Constraint 1: Self-deletion prevention
    if ($target_account_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Security Error: You cannot terminate your own active session account.']);
        exit();
    }

    // Security Constraint 2: Only Librarians (Role 2) can be deleted via this endpoint
    $termination_query = "DELETE FROM users WHERE id = ? AND role_id = 2";
    $deletion_stmt = mysqli_prepare($db_connect, $termination_query);
    mysqli_stmt_bind_param($deletion_stmt, "i", $target_account_id);

    if (mysqli_stmt_execute($deletion_stmt)) {
        if (mysqli_affected_rows($db_connect) > 0) {
            echo json_encode(['success' => true, 'message' => 'The librarian account has been permanently removed.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Operation Failed: Target record not found or protected (Administrative Account).']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'System Database Error: ' . mysqli_error($db_connect)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Malformed Request: Missing Account ID.']);
}
?>
