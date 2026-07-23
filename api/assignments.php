<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query('SELECT * FROM assignments ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();
    echo json_encode(['success' => true, 'assignments' => $rows]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $camp_id = $input['camp_id'] ?? null;
    $doctor_id = $input['doctor_id'] ?? null;
    $assignment_date = $input['assignment_date'] ?? null;
    
    if (!$camp_id || !$doctor_id || !$assignment_date) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'camp_id, doctor_id and assignment_date are required']);
        exit;
    }
    
    $created_at = gmdate('Y-m-d\TH:i:s\Z');
    
    $stmt = $db->prepare('INSERT INTO assignments (camp_id, doctor_id, assignment_date, created_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$camp_id, $doctor_id, $assignment_date, $created_at]);
    
    echo json_encode(['success' => true, 'message' => 'Assignment saved', 'id' => $db->lastInsertId()]);
}
?>
