<?php
require_once '../../includes/session.php';
require_once '../../config/database.php';

// If already logged in and no message parameter is present, redirect to dashboard
if (is_logged_in() && empty($_GET['msg']) && empty($_GET['error'])) {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

$error = '';
$success = '';
$resetUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid registered email address.';
        } else {
            try {
                // Step 2: Verify email exists
                $stmt = $pdo->prepare("SELECT id, email, full_name FROM users WHERE email = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch();

                if ($user) {
                    // Step 3: Generate secure reset token
                    $token = bin2hex(random_bytes(32));

                    // Step 4: Store token and 30-minute expiry time in database
                    // Delete any old pending tokens for this email first
                    $delStmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
                    $delStmt->execute(['email' => $email]);

                    $insStmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL 30 MINUTE))");
                    $insStmt->execute(['email' => $email, 'token' => $token]);

                    // Construct reset link
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $resetUrl = "{$protocol}://{$host}/Healthcare%20and%20camp%20management%20system/modules/authentication/reset_password.php?token={$token}";

                    $success = 'Password reset instructions have been generated successfully.';
                } else {
                    // Security best practice: don't reveal if account exists, or display user friendly notice
                    $error = 'No registered account was found with that email address.';
                }
            } catch (PDOException $e) {
                $error = 'Database connection error. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - HMCMS Portal</title>
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>

    <div class="auth-container">
        <div class="auth-header">
            <img src="../../assets/images/logo/logo.png" alt="Swasthya Setu Logo" class="auth-logo">
            <h1>Forgot Password</h1>
            <p>Swasthya Setu • Connecting Communities to Better Healthcare</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>

            <!-- Development Mode Display -->
            <div style="background: #f8fafc; border: 1.5px dashed #cbd5e1; padding: 18px; border-radius: 12px; margin-bottom: 20px;">
                <p style="font-size: 13px; color: #475569; margin-bottom: 8px;">
                    <i class="fa-solid fa-terminal" style="color: #0ea5e9;"></i> <strong>Development Mode Reset Link:</strong><br>
                    (Simulating Email Transmission)
                </p>
                <a href="<?php echo htmlspecialchars($resetUrl); ?>" style="word-break: break-all; font-size: 13px; color: #0284c7; font-weight: 600; text-decoration: underline;">
                    <?php echo htmlspecialchars($resetUrl); ?>
                </a>
            </div>

            <div style="text-align: center;">
                <a href="<?php echo htmlspecialchars($resetUrl); ?>" class="btn-submit" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">
                    Proceed to Reset Password
                </a>
            </div>
        <?php else: ?>

            <form action="forgot_password.php" method="POST" id="forgotForm" novalidate>
                <!-- CSRF Token -->
                <?php 
                    if (empty($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                ?>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="form-group">
                    <label class="form-label" for="email">Registered Email Address</label>
                    <div class="form-input-group">
                        <input type="email" id="email" name="email" class="form-input" placeholder="enter your registered email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="resetBtn">
                    <span class="btn-text">Generate Reset Link</span>
                    <div class="spinner"></div>
                </button>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="login.php" class="forgot-link" style="color: #64748b; font-size: 14px;">
                        <i class="fa-solid fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </form>

        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('forgotForm');
            if (form) {
                const btn = document.getElementById('resetBtn');
                form.addEventListener('submit', function(e) {
                    const email = document.getElementById('email');
                    email.classList.remove('input-error');
                    
                    if (!email.value.trim()) {
                        e.preventDefault();
                        email.classList.add('input-error');
                        const container = document.querySelector('.auth-container');
                        container.style.animation = 'none';
                        container.offsetHeight;
                        container.style.animation = 'shake 0.4s cubic-bezier(.36,.07,.19,.97) both';
                    } else {
                        btn.classList.add('loading');
                    }
                });
            }
        });
    </script>
</body>
</html>
