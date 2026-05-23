<?php
/**
 * Role-based access helpers (Admin vs Librarian).
 */

function current_role_id(): int
{
    return (int)($_SESSION['role_id'] ?? 0);
}

function is_admin_role(): bool
{
    return current_role_id() === 1;
}

function is_librarian_role(): bool
{
    return current_role_id() === 2;
}

/** Admin may open book/loan pages but cannot mutate data */
function circulation_is_readonly(): bool
{
    return is_admin_role();
}

/** SQL fragment: pending requests visible in nav badge / dashboard */
function pending_requests_filter_sql(int $role_id): string
{
    if ($role_id === 1) {
        return "type IN ('librarian_registration','password_reset')";
    }
    return "type IN ('borrow_book','return_book')";
}

function admin_can_process_request(string $type): bool
{
    return in_array($type, ['librarian_registration', 'password_reset'], true);
}

function librarian_can_process_request(string $type): bool
{
    return in_array($type, ['borrow_book', 'return_book'], true);
}

function require_staff_access(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['account_id']) || !in_array(current_role_id(), [1, 2], true)) {
        header('Location: ' . BASE_URL . 'authen/login.php');
        exit();
    }
}

/** View books / loans (Admin read-only, Librarian full) */
function require_circulation_view(): void
{
    require_staff_access();
}

/**
 * Mutations: add/edit/delete books, loans, readers — Librarian only.
 */
function require_librarian_circulation(): void
{
    require_staff_access();

    if (is_librarian_role()) {
        return;
    }

    require_once __DIR__ . '/alerts.php';
    setFlashAlert('Only librarians can perform this action.', 'error');
    header('Location: ' . BASE_URL . (is_admin_role() ? 'dashboard/admin-dashboard.php' : 'index.php'));
    exit();
}

/** Block write attempt from read-only Admin (call before POST/GET mutations) */
function deny_if_circulation_readonly(): void
{
    if (!circulation_is_readonly()) {
        return;
    }
    require_once __DIR__ . '/alerts.php';
    setFlashAlert('View only — librarians handle book and loan changes.', 'info');
    $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : (BASE_URL . 'dashboard/admin-dashboard.php');
    header('Location: ' . $back);
    exit();
}
