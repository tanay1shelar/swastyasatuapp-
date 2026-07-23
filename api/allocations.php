<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query('SELECT * FROM allocations ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();
    echo json_encode(['success' => true, 'allocations' => $rows]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $camp_id = $input['camp_id'] ?? null;
    $worker_id = $input['worker_id'] ?? null;
    $shift_date = $input['shift_date'] ?? null;
    
    if (!$camp_id || !$worker_id || !$shift_date) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'camp_id, worker_id and shift_date are required']);
        exit;
    }
    
    $created_at = gmdate('Y-m-d\TH:i:s\Z');
    
    $stmt = $db->prepare('INSERT INTO allocations (camp_id, worker_id, shift_date, created_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$camp_id, $worker_id, $shift_date, $created_at]);
    
    echo json_encode(['success' => true, 'message' => 'Allocation saved', 'id' => $db->lastInsertId()]);
}
?>
