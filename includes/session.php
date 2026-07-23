<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Authentication and Session Management
 * 
 * Manages user sessions, authentication checks, role permissions, and security.
 */

// Prevent direct access to session file
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied.");
}

// Ensure configuration is loaded for BASE_URL
require_once dirname(__DIR__) . '/config/config.php';

// Configure session cookie parameters for security
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    // Enable secure cookies if running on HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}

/**
 * Check if the user is authenticated
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Enforce authentication. Redirects to login page if user is not logged in.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // If not logged in, redirect to login page
        header("Location: " . BASE_URL . "authentication/login.php");
        exit;
    }
}

/**
 * Get current logged in user details
 * @return array User details or empty array
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return [];
    }
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'name' => $_SESSION['name'],
        'role' => $_SESSION['role'],
        'email' => $_SESSION['email'] ?? 'staff@hmcms.org',
        'avatar' => $_SESSION['avatar'] ?? (defined('DEFAULT_AVATAR') ? DEFAULT_AVATAR : 'assets/images/default-avatar.png')
    ];
}

/**
 * Log in a user by setting session variables
 * @param int $userId
 * @param string $username
 * @param string $name
 * @param string $role
 * @param string $email
 */
function loginUser($userId, $username, $name, $role, $email = '', $avatar = '') {
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['name'] = $name;
    $_SESSION['role'] = $role;
    $_SESSION['email'] = $email;
    $_SESSION['avatar'] = !empty($avatar) ? $avatar : (defined('DEFAULT_AVATAR') ? DEFAULT_AVATAR : 'assets/images/default-avatar.png');
    $_SESSION['last_activity'] = time();
}

/**
 * Log out user by clearing and destroying the session
 */
function logoutUser() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    // Redirect to login page
    header("Location: " . BASE_URL . "authentication/login.php");
    exit;
}

/**
 * Check if current user has the required roles
 * @param array|string $allowedRoles Single role string or array of allowed roles
 * @return bool
 */
function hasRole($allowedRoles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['role'];
    
    if (is_array($allowedRoles)) {
        return in_array($userRole, $allowedRoles);
    }
    
    return $userRole === $allowedRoles;
}

/**
 * Enforce role-based access control. Shows 403 error page if user is not authorized.
 * @param array|string $allowedRoles
 */
function requireRole($allowedRoles) {
    requireLogin();
    
    if (!hasRole($allowedRoles)) {
        http_response_code(403);
        // Render a professional Access Denied screen
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Denied - SwasthyaSetu</title>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
            <style>
                :root {
                    --primary: #0f172a;
                    --accent: #2563eb;
                    --text: #334155;
                    --bg: #f8fafc;
                }
                body {
                    font-family: 'Inter', sans-serif;
                    background-color: var(--bg);
                    color: var(--text);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                    padding: 20px;
                }
                .card {
                    background: white;
                    padding: 40px;
                    border-radius: 12px;
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
                    max-width: 500px;
                    width: 100%;
                    text-align: center;
                    border: 1px solid #e2e8f0;
                }
                h1 {
                    font-family: 'Poppins', sans-serif;
                    color: var(--primary);
                    font-size: 24px;
                    margin-top: 0;
                }
                p {
                    line-height: 1.6;
                    color: #64748b;
                    margin-bottom: 24px;
                }
                .icon {
                    font-size: 48px;
                    color: #f59e0b;
                    margin-bottom: 20px;
                }
                .btn {
                    background: var(--accent);
                    color: white;
                    padding: 10px 24px;
                    border-radius: 6px;
                    text-decoration: none;
                    font-weight: 500;
                    display: inline-block;
                    transition: background 0.2s;
                }
                .btn:hover {
                    background: #1d4ed8;
                }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="icon">🔒</div>
                <h1>Unauthorized Access</h1>
                <p>You do not have the required administrative permissions to access this module. Please contact your system administrator if you believe this is an error.</p>
                <a href="<?php echo BASE_URL; ?>" class="btn">Return to Dashboard</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Enforce active login checks on all app wrapper pages
$currentScript = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');
if (basename($currentScript) !== 'login.php' && strpos($currentScript, 'authentication/login.php') === false) {
    requireLogin();
}
