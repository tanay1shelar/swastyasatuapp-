<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Centralized Application Constants
 * 
 * Single source of truth for global metadata, status codes, roles, and paths.
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied.");
}

// =========================================================================
// APPLICATION METADATA CONSTANTS
// =========================================================================
if (!defined('APP_NAME')) {
    define('APP_NAME', 'SwasthyaSetu - Healthcare & Medical Camp Management System');
}
define('APP_SHORT_NAME', 'SwasthyaSetu');
define('APP_VERSION', '1.0.0-beta');
define('APP_DEVELOPER', 'Apollo Partner / Senior Developer Team');
define('APP_COPYRIGHT_YEAR', date('Y'));
define('DEFAULT_TIMEZONE', 'Asia/Kolkata');

// =========================================================================
// DATABASE CREDENTIALS
// =========================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hmcms');
define('DB_CHARSET', 'utf8mb4');

// =========================================================================
// SYSTEM PATHS & FALLBACK ASSETS
// =========================================================================
define('APP_ROOT', dirname(__DIR__) . '/');
define('UPLOAD_DIR', APP_ROOT . 'uploads/');
define('DEFAULT_AVATAR', 'assets/images/default-avatar.png');
define('DEFAULT_PATIENT_PHOTO', 'assets/images/patient-default.png');

// =========================================================================
// USER ROLES & SYSTEM STATUS CODES
// =========================================================================
define('ROLE_ADMIN', 'Admin');
define('ROLE_HEALTH_WORKER', 'Health Worker');
define('ROLE_DOCTOR', 'Doctor');
define('ROLE_PHARMACIST', 'Pharmacist');

define('STATUS_PENDING', 'Pending Verification');
define('STATUS_VERIFIED', 'Verified');
define('STATUS_REJECTED', 'Rejected');
define('STATUS_PRESENT', 'Present');
define('STATUS_TRIAGE', 'In Triage');
define('STATUS_CONSULTATION', 'In Consultation');
define('STATUS_COMPLETED', 'Completed');
