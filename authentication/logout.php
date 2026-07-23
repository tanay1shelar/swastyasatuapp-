<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Logout Script
 * 
 * Safely destroys the active administrative session and redirects back 
 * to index.php with a logout alert flag.
 */

// Import session manager
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/session.php';

// Unset all session parameters
$_SESSION = [];

// Destroy session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect back to login page with notice parameter
header("Location: " . BASE_URL . "login.php?status=logged_out");
exit;
