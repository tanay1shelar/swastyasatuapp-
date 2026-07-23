<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query('SELECT * FROM medicines ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();
    echo json_encode(['success' => true, 'medicines' => $rows]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['med_name'] ?? null;
    $category = $input['category'] ?? null;
    $batch_no = $input['batch_no'] ?? null;
    $expiry_date = $input['expiry_date'] ?? null;
    $stock_qty = $input['stock_qty'] ?? null;
    
    if (!$name || !$category || !$batch_no || !$expiry_date || $stock_qty === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'All medicine fields are required']);
        exit;
    }
    
    if (!is_numeric($stock_qty)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'stock_qty must be a number']);
        exit;
    }
    
    $created_at = gmdate('Y-m-d\TH:i:s\Z');
    
    $stmt = $db->prepare('INSERT INTO medicines (name, category, batch_no, expiry_date, stock_qty, created_at) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $category, $batch_no, $expiry_date, (int)$stock_qty, $created_at]);
    
    echo json_encode(['success' => true, 'message' => 'Medicine saved', 'id' => $db->lastInsertId()]);
}
?>
