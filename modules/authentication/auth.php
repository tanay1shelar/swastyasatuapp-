<?php
require_once '../../config/config.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

$role = isset($_POST['role']) ? sanitizeInput($_POST['role']) : 'User';
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = $role;

header('Location: ' . BASE_URL . 'index.php');
exit;
?>
