<?php
require_once '../../includes/session.php';
require_once '../../config/database.php';

// If already logged in and no message parameter is present, redirect
if (is_logged_in() && empty($_GET['msg']) && empty($_GET['error'])) {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$success = '';
$validToken = false;
$resetRecord = null;

if (empty($token)) {
    $error = 'Invalid request. Password reset token is missing.';
} else {
    try {
        // Step 5: Verify token exists and is not expired (30 mins limit)
        $stmt = $pdo->prepare("SELECT id, email, expires_at FROM password_resets WHERE token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        $resetRecord = $stmt->fetch();

        if (!$resetRecord) {
            $error = 'Invalid reset token. Please request a new password reset link.';
        } elseif (strtotime($resetRecord['expires_at']) < time()) {
            // Expired token
            $error = 'This password reset link has expired (valid for 30 minutes). Please request a new one.';
            // Clean up expired token
            $delStmt = $pdo->prepare("DELETE FROM password_resets WHERE id = :id");
            $delStmt->execute(['id' => $resetRecord['id']]);
        } else {
            $validToken = true;
        }
    } catch (PDOException $e) {
        $error = 'Database error occurred. Please try again later.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($password) || empty($confirmPassword)) {
            $error = 'Please enter both password fields.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            try {
                // Step 7: Update password using password_hash()
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $updateUser = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE email = :email");
                $updateUser->execute(['hash' => $newHash, 'email' => $resetRecord['email']]);

                // Step 8: Delete consumed reset token
                $delToken = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
                $delToken->execute(['email' => $resetRecord['email']]);

                // Step 9: Redirect to Login Page with success flag
                header("Location: login.php?msg=password_reset_success");
                exit();
            } catch (PDOException $e) {
                $error = 'Failed to reset password. Please try again.';
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
    <title>Set New Password - HMCMS Portal</title>
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>

    <div class="auth-container">
        <div class="auth-header">
            <img src="../../assets/images/logo/logo.png" alt="Swasthya Setu Logo" class="auth-logo">
            <h1>Reset Your Password</h1>
            <p>Swasthya Setu • Connecting Communities to Better Healthcare</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php if (!$validToken): ?>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="forgot_password.php" class="btn-submit" style="text-decoration: none; display: inline-block;">Request New Reset Link</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($validToken): ?>
            <form action="reset_password.php" method="POST" id="resetPasswordForm" novalidate>
                <!-- CSRF Token -->
                <?php 
                    if (empty($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                ?>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="form-group">
                    <label class="form-label" for="password">New Password <span style="color:red">*</span></label>
                    <div class="form-input-group">
                        <input type="password" id="password" name="password" class="form-input" placeholder="Min 6 characters" required>
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password <span style="color:red">*</span></label>
                    <div class="form-input-group">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Re-enter new password" required>
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text">Update Password</span>
                    <div class="spinner"></div>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetPasswordForm');
            if (form) {
                const btn = document.getElementById('submitBtn');
                form.addEventListener('submit', function(e) {
                    const pwd = document.getElementById('password');
                    const cpwd = document.getElementById('confirm_password');
                    let isValid = true;

                    pwd.classList.remove('input-error');
                    cpwd.classList.remove('input-error');

                    if (pwd.value.length < 6) {
                        pwd.classList.add('input-error');
                        isValid = false;
                    }

                    if (pwd.value !== cpwd.value) {
                        cpwd.classList.add('input-error');
                        isValid = false;
                    }

                    if (!isValid) {
                        e.preventDefault();
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
