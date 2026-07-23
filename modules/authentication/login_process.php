<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Backend Authentication Processor
 */

header('Content-Type: application/json');
define('IS_API', true);

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/session.php';

// Accept JSON or POST inputs
$inputData = json_decode(file_get_contents('php://input'), true);

$usernameOrEmail = trim($inputData['username'] ?? $_POST['username'] ?? '');
$password = $inputData['password'] ?? $_POST['password'] ?? '';
$role = trim($inputData['role'] ?? $_POST['role'] ?? '');

if (empty($usernameOrEmail) || empty($password) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields (Role, Username/Email, and Password).']);
    exit();
}

try {
    // Map frontend roles to database enum values
    $roleMap = [
        'super-admin' => 'Super Admin',
        'camp-admin'  => 'Medical Camp Admin',
        'doctor'      => 'Doctor',
        'health-worker' => 'Staff',
        'citizen'     => 'Patient'
    ];
    $dbRole = $roleMap[$role] ?? $role;

    // Search for user by Username or Email AND Role
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = :username OR email = :email) AND role = :role LIMIT 1");
    $stmt->execute([
        'username' => $usernameOrEmail,
        'email' => $usernameOrEmail,
        'role' => $dbRole
    ]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'No user account found matching this Role and Username/Email.']);
        exit();
    }

    // Verify Password against password_hash column in MySQL
    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect password. Please verify your password and try again.']);
        exit();
    }

    // Map database enum back to frontend role slugs
    $reverseRoleMap = [
        'Super Admin'        => 'super-admin',
        'Medical Camp Admin' => 'camp-admin',
        'Doctor'             => 'doctor',
        'Staff'              => 'health-worker',
        'Volunteer'          => 'health-worker',
        'Patient'            => 'citizen'
    ];
    $mappedRole = $reverseRoleMap[$user['role']] ?? 'citizen';

    // Login Successful - Set Session
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = !empty($user['full_name']) ? $user['full_name'] : $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $mappedRole;

    // Map redirects
    $roleRedirects = [
        'super-admin' => '../dashboard/dashboard-super-admin.php',
        'camp-admin' => '../dashboard/dashboard-camp-admin.php',
        'doctor' => '../dashboard/dashboard-doctor.php',
        'health-worker' => '../dashboard/dashboard-health-worker.php',
        'citizen' => '../dashboard/dashboard-citizen.php'
    ];

    $redirectUrl = $roleRedirects[$mappedRole] ?? '../dashboard/dashboard-citizen.php';

    echo json_encode([
        'success' => true,
        'message' => 'Login successful! Redirecting to your dashboard...',
        'redirect' => $redirectUrl
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error during authentication: ' . $e->getMessage()]);
}
