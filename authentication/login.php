<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * User Login Authentication Portal
 * 
 * Standalone entry view allowing health workers to authenticate.
 */

// Define page and context
if (!defined('APP_NAME')) {
    define('APP_NAME', 'HMCMS Login');
}
require_once dirname(__DIR__) . '/config/config.php';

// If already logged in, bypass login
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "modules/dashboard/");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = 'Please enter your Employee ID / Email and Password.';
    } else {
        try {
            $db = db_connect();
            // Lookup by employee ID or email
            $stmt = $db->prepare("
                SELECT * FROM users 
                WHERE (employee_id = :id1 OR email = :email1) AND status = 'Active'
                LIMIT 1
            ");
            $stmt->execute([':id1' => $identifier, ':email1' => $identifier]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session to prevent hijacking
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['employee_id']; // set username as employee ID
                $_SESSION['name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['avatar'] = !empty($user['profile_photo']) ? $user['profile_photo'] : 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&q=80&w=150';
                $_SESSION['last_activity'] = time();

                // Log login action
                db_log_activity($user['id'], "Logged in successfully", "Security");

                header("Location: " . BASE_URL . "modules/dashboard/");
                exit;
            } else {
                $error = 'Invalid Employee ID / Email or Password.';
            }
        } catch (PDOException $e) {
            error_log("Login Query Error: " . $e->getMessage());
            $error = 'System query timeout. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SwasthyaSetu Health Worker Portal</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- System Styling Variables -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/variables.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-app);
            color: var(--text-primary);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }
        .login-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            max-width: 440px;
            width: 100%;
            padding: 2.5rem;
            transition: var(--transition-normal);
        }
        .login-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--primary);
        }
        .login-logo {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        .form-control-custom {
            background-color: var(--bg-app);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            font-size: var(--font-size-sm);
            width: 100%;
            transition: var(--transition-fast);
        }
        .form-control-custom:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .btn-login {
            background-color: var(--accent);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: var(--font-size-sm);
            width: 100%;
            transition: var(--transition-fast);
            cursor: pointer;
        }
        .btn-login:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

    <div class="login-card text-center">
        <div class="login-logo mb-3 d-flex justify-content-center">
            <img src="<?php echo BASE_URL; ?>assets/images/logo/swasthyasetu-logo.jpeg" alt="SwasthyaSetu Logo" style="width: 64px; height: 64px; object-fit: cover; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
        </div>
        <h3 class="login-title mb-1">SwasthyaSetu Portal</h3>
        <p class="text-secondary small mb-4">Healthcare & Medical Camp Management System</p>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'logged_out'): ?>
            <div class="alert alert-success py-2 px-3 small border-0 text-start" role="alert">
                <i class="bi bi-info-circle-fill me-1"></i> You have signed out successfully.
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 small border-0 text-start" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>authentication/login.php" class="text-start">
            <div class="mb-3">
                <label class="form-label small text-secondary">Employee ID / Email Address</label>
                <input type="text" name="identifier" class="form-control-custom" placeholder="e.g. EMP-2026-9042" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label small text-secondary">Security Password</label>
                <input type="password" name="password" class="form-control-custom" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Authenticate Credentials
            </button>
        </form>

        <div class="mt-4 pt-3 border-top text-center text-muted" style="font-size: 10px;">
            Demo Staff PIN: <strong class="text-primary">Password@123</strong>
        </div>
    </div>

</body>
</html>
