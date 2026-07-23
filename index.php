<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Root Entry Point & Router
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/session.php';

if (isLoggedIn()) {
    header("Location: " . BASE_URL . "modules/dashboard/");
} else {
    header("Location: " . BASE_URL . "authentication/login.php");
}
exit;
