<?php
// db.php
// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$db_file = __DIR__ . '/../database/hmcms.db';
$db_exists = file_exists($db_file);

try {
    $db = new PDO('sqlite:' . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if (!$db_exists) {
        $queries = [
            "CREATE TABLE IF NOT EXISTS assignments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                camp_id TEXT NOT NULL,
                doctor_id TEXT NOT NULL,
                assignment_date TEXT NOT NULL,
                created_at TEXT NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS allocations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                camp_id TEXT NOT NULL,
                worker_id TEXT NOT NULL,
                shift_date TEXT NOT NULL,
                created_at TEXT NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS notifications (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                audience TEXT NOT NULL,
                title TEXT NOT NULL,
                message TEXT NOT NULL,
                created_at TEXT NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS medicines (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                category TEXT NOT NULL,
                batch_no TEXT NOT NULL,
                expiry_date TEXT NOT NULL,
                stock_qty INTEGER NOT NULL,
                created_at TEXT NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS stock_updates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                medicine_id INTEGER NOT NULL,
                update_type TEXT NOT NULL,
                update_qty INTEGER NOT NULL,
                created_at TEXT NOT NULL,
                FOREIGN KEY (medicine_id) REFERENCES medicines(id)
            )"
        ];
        
        foreach ($queries as $query) {
            $db->exec($query);
        }
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}
?>
