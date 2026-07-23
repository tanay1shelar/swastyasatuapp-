<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query('SELECT * FROM notifications ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();
    echo json_encode(['success' => true, 'notifications' => $rows]);
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $audience = $input['audience'] ?? null;
    $title = $input['title'] ?? null;
    $message = $input['message'] ?? null;
    
    if (!$audience || !$title || !$message) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'audience, title and message are required']);
        exit;
    }
    
    $created_at = gmdate('Y-m-d\TH:i:s\Z');
    
    $stmt = $db->prepare('INSERT INTO notifications (audience, title, message, created_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$audience, $title, $message, $created_at]);
    
    echo json_encode(['success' => true, 'message' => 'Notification saved', 'id' => $db->lastInsertId()]);
}
?>
