<?php
<<<<<<< HEAD
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Core Configuration File
 * 
 * Defines global constants, paths, database credentials, and sets up
 * the database connection using PDO.
 */

// Prevent direct access to config file
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied.");
}

// Ensure session is started if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load central application constants
require_once __DIR__ . '/constants.php';

// Set default timezone for healthcare reporting
date_default_timezone_set(defined('DEFAULT_TIMEZONE') ? DEFAULT_TIMEZONE : 'Asia/Kolkata');

// =========================================================================
// ENVIRONMENT SETTINGS & DYNAMIC BASE URL
// =========================================================================
// Detect if running on HTTPS or HTTP
$serverPort = $_SERVER['SERVER_PORT'] ?? 80;
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $serverPort == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . '/');
}

if (!defined('BASE_URL')) {
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    $appRoot = str_replace('\\', '/', dirname(__DIR__));
    $relPath = trim(str_replace($docRoot, '', $appRoot), '/');
    $baseFolder = $relPath ? '/' . $relPath : '';
    define('BASE_URL', $protocol . $host . $baseFolder . '/');
}

// Load architectural modules in standardized order: Database -> Helpers -> Queries -> Functions
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/queries.php';
require_once __DIR__ . '/functions.php';

/**
 * Get Database Connection (Legacy alias wrapper)
 */
function getDbConnection() {
    return db_connect();
}

/**
 * Display a premium, user-friendly maintenance/database error page
 */
function displayMaintenancePage() {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Offline - SwasthyaSetu</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary: #0f172a;
                --accent: #2563eb;
                --text: #334155;
                --bg: #f8fafc;
            }
            body {
                font-family: 'Inter', sans-serif;
                background-color: var(--bg);
                color: var(--text);
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .card {
                background: white;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
                max-width: 500px;
                width: 100%;
                text-align: center;
                border: 1px solid #e2e8f0;
            }
            h1 {
                font-family: 'Poppins', sans-serif;
                color: var(--primary);
                font-size: 24px;
                margin-top: 0;
            }
            p {
                line-height: 1.6;
                color: #64748b;
                margin-bottom: 24px;
            }
            .icon {
                font-size: 48px;
                color: #ef4444;
                margin-bottom: 20px;
            }
            .btn {
                background: var(--accent);
                color: white;
                padding: 10px 24px;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 500;
                display: inline-block;
                transition: background 0.2s;
            }
            .btn:hover {
                background: #1d4ed8;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon">⚠️</div>
            <h1>System Connection Issue</h1>
            <p>We are unable to connect to the medical records database. Our administrative systems are performing scheduled maintenance or experiencing high latency. Please verify your local MySQL server is running in XAMPP.</p>
            <a href="javascript:location.reload();" class="btn">Retry Connection</a>
        </div>
    </body>
    </html>
    <?php
}
=======
// Application Configurations
>>>>>>> origin/main
