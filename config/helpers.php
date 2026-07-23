<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Shared Helper & Validation Module (helpers.php)
 * 
 * Provides inputs sanitization, session security guards, JSON API responses,
 * and registration numbers generators.
 */

// Prevent direct access to config files
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied.");
}

/**
 * Sanitize text inputs for XSS prevention
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Enforces session log check. Redirects unauthorized calls to login.php
 */
function checkSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Send standard API response payload
 * @param bool $success
 * @param string $message
 * @param array $data
 */
function responseJson($success, $message, $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Calculate BMI value
 * @param float $height (in cm)
 * @param float $weight (in kg)
 * @return float|null
 */
function calculateBmi($height, $weight) {
    if ($height > 0 && $weight > 0) {
        $heightM = $height / 100;
        return round($weight / ($heightM * $heightM), 2);
    }
    return null;
}
