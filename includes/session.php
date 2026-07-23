<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Session Management & Role Authentication Security Guards
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Enforce role-based page protection guard
 * @param array $allowed_roles List of role strings allowed to view the page
 */
function check_auth($allowed_roles = []) {
    $login_url = '/Healthcare and camp management system/modules/authentication/login.php';
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: " . $login_url . "?msg=please_login");
        exit();
    }

    // Session Timeout Guard (e.g. 1 hour = 3600 seconds)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 3600)) {
        session_unset();
        session_destroy();
        header("Location: " . $login_url . "?msg=session_timeout");
        exit();
    }

    if (!empty($allowed_roles)) {
        // Normalize role comparison (support both super-admin and super_admin formats)
        $user_role_norm = str_replace('_', '-', $_SESSION['role']);
        $allowed_norm = array_map(function($r) {
            return str_replace('_', '-', $r);
        }, $allowed_roles);

        if (!in_array($user_role_norm, $allowed_norm)) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }
            session_destroy();
            header("Location: " . $login_url . "?msg=unauthorized_role");
            exit();
        }
    }
}

/**
 * Check if a session is currently active
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}
