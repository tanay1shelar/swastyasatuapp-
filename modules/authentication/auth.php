<?php
/**
 * Authentication Middleware
 * Include this file at the top of secured pages to protect them.
 */

// We assume session.php will be included from the relative path based on where this auth.php is included.
// Since this is included from various dashboard files, we need a reliable path.
// Using __DIR__ ensures we always find the correct relative path to session.php
require_once __DIR__ . '/../../includes/session.php';

if (!is_logged_in()) {
    // Determine the base path to redirect correctly
    $redirect_url = '/Healthcare and camp management system/modules/authentication/login.php?msg=please_login';
    header("Location: " . $redirect_url);
    exit();
}
