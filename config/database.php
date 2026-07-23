<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * PDO Database Connection & Automatic Schema Integration
 */

$host = 'localhost';
$user = 'root';      // Default XAMPP MySQL username
$pass = '';          // Default XAMPP MySQL password (blank)
$dbname = 'hmcms_db';

try {
    // 1. Connection to MySQL Host
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);

    // 2. Ensure Database Exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname` ");

    // 3. Ensure Users Table Exists with password_hash column
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `full_name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `role` VARCHAR(50) NOT NULL DEFAULT 'citizen',
        `phone` VARCHAR(20) DEFAULT NULL,
        `address` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Schema Helper: Ensure all registration & reset columns exist
    $existingColumns = $pdo->query("DESCRIBE `users`")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('password_hash', $existingColumns)) {
        try { $pdo->exec("ALTER TABLE `users` ADD `password_hash` VARCHAR(255) NOT NULL"); } catch (Exception $ignored) {}
    }
    if (!in_array('gender', $existingColumns)) {
        try { $pdo->exec("ALTER TABLE `users` ADD `gender` VARCHAR(20) DEFAULT NULL"); } catch (Exception $ignored) {}
    }
    if (!in_array('dob', $existingColumns)) {
        try { $pdo->exec("ALTER TABLE `users` ADD `dob` DATE DEFAULT NULL"); } catch (Exception $ignored) {}
    }

    // 4b. Ensure password_resets Table Exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(100) NOT NULL,
        `token` VARCHAR(255) NOT NULL UNIQUE,
        `expires_at` DATETIME NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 5. Create / Update Required Test Users
    try {
        $requiredUsers = [
            ['Super Admin', 'superadmin@hmcms.com', 'superadmin', 'super-admin'],
            ['Camp Admin', 'admin@hmcms.com', 'campadmin', 'camp-admin'],
            ['Doctor', 'doctor@hmcms.com', 'doctor', 'doctor'],
            ['Health Worker', 'worker@hmcms.com', 'healthworker', 'health-worker'],
            ['Citizen', 'citizen@hmcms.com', 'citizen', 'citizen']
        ];
        
        $newHash = password_hash('nandini2486', PASSWORD_DEFAULT);
        
        $checkStmt = $pdo->prepare("SELECT id FROM `users` WHERE email = ? OR username = ?");
        $updateStmt = $pdo->prepare("UPDATE `users` SET `password_hash` = ?, `role` = ?, `full_name` = ? WHERE id = ?");
        $insertStmt = $pdo->prepare("INSERT INTO `users` (`full_name`, `email`, `username`, `password_hash`, `role`) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($requiredUsers as $u) {
            $checkStmt->execute([$u[1], $u[2]]);
            $existing = $checkStmt->fetch();
            if ($existing) {
                $updateStmt->execute([$newHash, $u[3], $u[0], $existing['id']]);
            } else {
                $insertStmt->execute([$u[0], $u[1], $u[2], $newHash, $u[3]]);
            }
        }

    } catch (Exception $e) {
        // Ignore duplicate key errors or seeding failures
    }

} catch (PDOException $e) {
    if (defined('IS_API')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit();
    } else {
        echo '<!DOCTYPE html><html><head><title>Database Error</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
        echo '</head><body class="bg-light">';
        echo '<div class="container mt-5"><div class="alert alert-danger shadow-sm" role="alert">';
        echo '<h4 class="alert-heading"><i class="fa-solid fa-triangle-exclamation"></i> Database Connection Error</h4>';
        echo '<p>We are currently unable to connect to the database. Please try again later or contact the system administrator.</p>';
        echo '<hr><p class="mb-0 small text-muted">Technical Details: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div></div></body></html>';
        exit();
    }
}
