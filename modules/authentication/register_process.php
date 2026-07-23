<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * User Registration Backend Processor
 */

// Disable direct HTML warnings to preserve JSON structure
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');

header('Content-Type: application/json');
define('IS_API', true);

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/session.php';

// Accept JSON or POST inputs
$inputData = json_decode(file_get_contents('php://input'), true);

$fullName = trim($inputData['full_name'] ?? $_POST['full_name'] ?? '');
$email = trim($inputData['email'] ?? $_POST['email'] ?? '');
$username = trim($inputData['username'] ?? $_POST['username'] ?? '');
$password = $inputData['password'] ?? $_POST['password'] ?? '';
$confirmPassword = $inputData['confirm_password'] ?? $_POST['confirm_password'] ?? '';
$role = trim($inputData['role'] ?? $_POST['role'] ?? 'citizen');
$phone = trim($inputData['phone'] ?? $_POST['phone'] ?? '');

// Allowed roles whitelist
$allowedRoles = ['super-admin', 'camp-admin', 'doctor', 'health-worker', 'citizen'];

// Validation Checks
if (empty($fullName) || empty($email) || empty($username) || empty($password) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled out.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit();
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
    exit();
}

if (!empty($confirmPassword) && $password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit();
}

if (!in_array($role, $allowedRoles)) {
    $role = 'citizen';
}

try {
    // Check if email or username already exists in MySQL
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
    $stmt->execute(['email' => $email, 'username' => $username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this Email or Username already exists. Please login instead.']);
        exit();
    }

    // Generate password hash using PASSWORD_DEFAULT
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Map frontend roles to database enum values
    $roleMap = [
        'super-admin'   => 'Super Admin',
        'camp-admin'    => 'Medical Camp Admin',
        'doctor'        => 'Doctor',
        'health-worker' => 'Staff',
        'citizen'       => 'Patient'
    ];
    $dbRole = $roleMap[$role] ?? 'Patient';

    // Insert New User into MySQL Database saving into password_hash, full_name, and name columns
    $insertStmt = $pdo->prepare("INSERT INTO users (full_name, name, email, username, password_hash, role, phone) VALUES (:full_name, :name, :email, :username, :password_hash, :role, :phone)");
    $insertStmt->execute([
        'full_name'     => $fullName,
        'name'          => $fullName,
        'email'         => $email,
        'username'      => $username,
        'password_hash' => $passwordHash,
        'role'          => $dbRole,
        'phone'         => $phone
    ]);

    $newUserId = $pdo->lastInsertId();

    // Set PHP Session
    $_SESSION['user_id'] = $newUserId;
    $_SESSION['username'] = $username;
    $_SESSION['full_name'] = $fullName;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;

    // Role Redirect Mapping
    $roleRedirects = [
        'super-admin' => '../dashboard/dashboard-super-admin.php',
        'camp-admin' => '../dashboard/dashboard-camp-admin.php',
        'doctor' => '../dashboard/dashboard-doctor.php',
        'health-worker' => '../dashboard/dashboard-health-worker.php',
        'citizen' => '../dashboard/dashboard-citizen.php'
    ];

    $redirectUrl = $roleRedirects[$role] ?? '../dashboard/dashboard-citizen.php';

    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully! Redirecting...',
        'redirect' => $redirectUrl
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error during registration: ' . $e->getMessage()]);
}
