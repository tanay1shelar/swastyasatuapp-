<?php
// Login API Endpoint
require_once '../includes/session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// CSRF Protection
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Security validation failed. Please refresh and try again.']);
    exit();
}

$usernameOrEmail = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$remember = !empty($_POST['remember']);

if (empty($usernameOrEmail) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter both username/email and password.']);
    exit();
}

try {
    // Query database for user by username or email
    $stmt = $pdo->prepare("SELECT id, full_name, email, username, password_hash, role FROM users WHERE username = :username_identifier OR email = :email_identifier LIMIT 1");
    $stmt->execute([
        'username_identifier' => $usernameOrEmail,
        'email_identifier' => $usernameOrEmail
    ]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Prevent Session Fixation
        session_regenerate_id(true);

        // Store user info in session per project requirements
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();

        // Remember Me option
        if ($remember) {
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + (30 * 24 * 60 * 60), $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        // Direct dashboard routing map
        $normalizedRole = str_replace('_', '-', $user['role']);
        $dashboardMap = [
            'super-admin' => '../dashboard/dashboard-super-admin.php',
            'camp-admin' => '../dashboard/dashboard-camp-admin.php',
            'doctor' => '../dashboard/dashboard-doctor.php',
            'health-worker' => '../dashboard/dashboard-health-worker.php',
            'citizen' => '../dashboard/dashboard-citizen.php'
        ];

        $redirect = $dashboardMap[$normalizedRole] ?? '../dashboard/dashboard.php';

        echo json_encode([
            'success' => true,
            'message' => 'Login successful. Redirecting...',
            'role' => $user['role'],
            'redirect' => $redirect
        ]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        exit();
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
}
