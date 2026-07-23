<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';

if (!is_logged_in()) {
    header("Location: ../authentication/login.php");
    exit();
}

$role = $_SESSION['role'] ?? 'citizen';

$dashboards = [
    'super-admin' => 'dashboard-super-admin.php',
    'camp-admin' => 'dashboard-camp-admin.php',
    'doctor' => 'dashboard-doctor.php',
    'health-worker' => 'dashboard-health-worker.php',
    'citizen' => 'dashboard-citizen.php'
];

if (!isset($dashboards[$role])) {
    $_SESSION = [];
    session_destroy();
    header("Location: ../authentication/login.php?msg=unauthorized_role");
    exit();
}

$file = $dashboards[$role];
include __DIR__ . '/' . $file;
