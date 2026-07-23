<?php
// db.php - Database connection configuration
$host = 'localhost';
$dbname = 'hmcms_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // Set PDO to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Fetch associative arrays by default
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, do not echo exact error details. For now, it's helpful for debugging.
    die("Database connection failed. Please ensure MySQL is running in XAMPP and you have imported database.sql. Error: " . $e->getMessage());
}
?>
