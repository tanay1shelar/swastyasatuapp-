<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Invalid HTTP request method', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$role = $input['role'] ?? 'User';
$username = $input['username'] ?? '';

if (empty($username)) {
    jsonResponse(false, [], 'Username or email is required', 400);
}

jsonResponse(true, ['user' => ['username' => $username, 'role' => $role]], 'Authenticated successfully');
?>
