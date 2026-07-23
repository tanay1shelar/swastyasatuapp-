<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Database Connection Wrapper
 * 
 * Sets up safe connection bounds using PDO.
 */

// Prevent direct access to config files
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied.");
}

require_once __DIR__ . '/config.php';

/**
 * Get Database Connection
 * Returns active PDO instance.
 */
function db_connect() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            displayMaintenancePage();
            exit;
        }
    }
    return $pdo;
}
