<?php
/**
 * UIU Career Portal - Authentication & RBAC Session Manager
 * Provides session lifecycle, authorization checks, and secure redirect guards.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Return current authenticated session array or null
 */
function current_user() {
    if (!empty($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return null;
}

/**
 * Check if session has a logged-in user
 */
function is_logged_in() {
    return !empty($_SESSION['user']['id']);
}

/**
 * Check if the active user matches one of the specified roles
 */
function has_role($allowed_roles) {
    if (!is_logged_in()) {
        return in_array('guest', (array)$allowed_roles, true);
    }
    $role = $_SESSION['user']['role'] ?? 'guest';
    return in_array($role, (array)$allowed_roles, true);
}

/**
 * Enforce role authorization; redirects to login if unauthenticated or unauthorized
 */
function require_role($allowed_roles, $base_path = '../') {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = "Please log in with your university credentials to access this portal.";
        header("Location: " . $base_path . "login.php");
        exit;
    }
    $current_role = $_SESSION['user']['role'] ?? '';
    if (!in_array($current_role, (array)$allowed_roles, true)) {
        $_SESSION['flash_error'] = "Access denied: Unauthorized role for this section.";
        header("Location: " . $base_path . "index.php");
        exit;
    }
}

/**
 * Store user session data
 */
function login_user($user_row, $profile_data = []) {
    $_SESSION['user'] = [
        'id'       => (int)$user_row['id'],
        'email'    => $user_row['email'],
        'role'     => $user_row['role'],
        'name'     => $profile_data['full_name'] ?? $profile_data['name'] ?? $user_row['email'],
        'profile'  => $profile_data,
    ];
    session_regenerate_id(true);
}

/**
 * Destroy user session and invalidate cookies
 */
function logout_user($base_path = '') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
