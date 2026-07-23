<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $medicine_id = $input['medicine_id'] ?? null;
    $update_type = $input['update_type'] ?? null;
    $update_qty = $input['update_qty'] ?? null;
    
    if (!$medicine_id || !$update_type || $update_qty === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'medicine_id, update_type and update_qty are required']);
        exit;
    }
    
    if (!is_numeric($medicine_id) || !is_numeric($update_qty)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'medicine_id and update_qty must be numbers']);
        exit;
    }
    
    if (!in_array($update_type, ['add', 'remove'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'update_type must be add or remove']);
        exit;
    }
    
    $stmt = $db->prepare('SELECT stock_qty FROM medicines WHERE id = ?');
    $stmt->execute([$medicine_id]);
    $current = $stmt->fetch();
    
    if (!$current) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Medicine not found']);
        exit;
    }
    
    $new_stock = $update_type === 'add' ? $current['stock_qty'] + $update_qty : $current['stock_qty'] - $update_qty;
    $new_stock = max($new_stock, 0);
    
    try {
        $db->beginTransaction();
        
        $update_stmt = $db->prepare('UPDATE medicines SET stock_qty = ? WHERE id = ?');
        $update_stmt->execute([$new_stock, $medicine_id]);
        
        $created_at = gmdate('Y-m-d\TH:i:s\Z');
        $insert_stmt = $db->prepare('INSERT INTO stock_updates (medicine_id, update_type, update_qty, created_at) VALUES (?, ?, ?, ?)');
        $insert_stmt->execute([$medicine_id, $update_type, $update_qty, $created_at]);
        
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Stock updated', 'medicine_id' => $medicine_id, 'stock_qty' => $new_stock]);
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
