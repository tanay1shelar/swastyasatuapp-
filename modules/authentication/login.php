<?php
require_once '../../includes/session.php';

// If already logged in and no alert message present, redirect to dashboard
if (is_logged_in() && empty($_GET['msg']) && empty($_GET['error'])) {
    header("Location: ../dashboard/dashboard.php");
    exit();
}

$error = $_GET['error'] ?? '';
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HMCMS Portal</title>
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>

    <div class="auth-container">
        <div class="auth-header">
            <img src="../../assets/images/logo/logo.png" alt="Swasthya Setu Logo" class="auth-logo">
            <h1>Swasthya Setu</h1>
            <p>Connecting Communities to Better Healthcare</p>
        </div>

        <?php if ($error === 'invalid'): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                Invalid username or password.
            </div>
        <?php elseif ($error === 'db_error'): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-server"></i>
                Database connection error. Please try again later.
            </div>
        <?php elseif ($error === 'empty'): ?>
            <div class="alert alert-warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Please enter both username/email and password.
            </div>
        <?php elseif ($error === 'csrf'): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-shield-halved"></i>
                Security validation failed. Please try again.
            </div>
        <?php endif; ?>

        <?php if ($msg === 'logged_out'): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                You have been successfully logged out.
            </div>
        <?php elseif ($msg === 'please_login'): ?>
            <div class="alert alert-warning">
                <i class="fa-solid fa-lock"></i>
                Please log in to access that page.
            </div>
        <?php elseif ($msg === 'unauthorized_role'): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-ban"></i>
                You are not authorized to view that page.
            </div>
        <?php elseif ($msg === 'password_reset'): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-envelope-circle-check"></i>
                Password reset instructions sent to your email.
            </div>
        <?php elseif ($msg === 'registered'): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-user-check"></i>
                Registration successful. Please log in with your credentials.
            </div>
        <?php elseif ($msg === 'password_reset_success'): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-key"></i>
                Password reset successful! You can now log in with your new password.
            </div>
        <?php elseif ($msg === 'session_timeout'): ?>
            <div class="alert alert-warning">
                <i class="fa-solid fa-clock"></i>
                Your session has expired. Please log in again.
            </div>
        <?php endif; ?>

        <!-- Container for dynamic alerts -->
        <div id="alertContainer"></div>

        <form id="loginForm" novalidate>
            <!-- CSRF Token -->
            <?php 
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            ?>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <label class="form-label" for="username">Username or Email</label>
                <div class="form-input-group">
                    <input type="text" id="username" name="username" class="form-input" placeholder="Enter username or email" required>
                    <i class="fa-regular fa-user"></i>
                </div>
                <div class="error-text" id="usernameError">Please enter your username or email.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="form-input-group">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Enter password" required>
                    <i class="fa-solid fa-lock"></i>
                    <i class="fa-regular fa-eye toggle-password" id="togglePassword" title="Show/Hide Password"></i>
                </div>
                <div class="error-text" id="passwordError">Please enter your password.</div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    Remember Me
                </label>
                <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-submit" id="loginBtn">
                <span class="btn-text">Secure Login</span>
                <div class="spinner"></div>
            </button>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="register.php" class="forgot-link" style="color: #64748b; font-size: 14px;">Don't have an account? Register</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const usernameInput = document.getElementById('username');
            const alertContainer = document.getElementById('alertContainer');

            // Password Toggle
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            function showAlert(type, message, icon) {
                alertContainer.innerHTML = `
                    <div class="alert alert-${type}">
                        <i class="fa-solid ${icon}"></i>
                        ${message}
                    </div>
                `;
            }

            // Form Validation and Submission
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                let isValid = true;
                
                // Reset errors
                usernameInput.classList.remove('input-error');
                password.classList.remove('input-error');
                document.getElementById('usernameError').style.display = 'none';
                document.getElementById('passwordError').style.display = 'none';
                alertContainer.innerHTML = '';

                if (!usernameInput.value.trim()) {
                    usernameInput.classList.add('input-error');
                    document.getElementById('usernameError').style.display = 'block';
                    isValid = false;
                }

                if (!password.value.trim()) {
                    password.classList.add('input-error');
                    document.getElementById('passwordError').style.display = 'block';
                    isValid = false;
                }

                if (!isValid) {
                    // Shake animation on error
                    const container = document.querySelector('.auth-container');
                    container.style.animation = 'none';
                    container.offsetHeight; // trigger reflow
                    container.style.animation = 'shake 0.4s cubic-bezier(.36,.07,.19,.97) both';
                    return;
                } 

                // Show loading state
                loginBtn.classList.add('loading');
                loginBtn.disabled = true;

                // Submit via AJAX
                const formData = new FormData(loginForm);
                fetch('../../api/login_api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message, 'fa-circle-check');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 500);
                    } else {
                        showAlert('danger', data.message, 'fa-circle-exclamation');
                        loginBtn.classList.remove('loading');
                        loginBtn.disabled = false;
                        
                        // Shake on API error
                        const container = document.querySelector('.auth-container');
                        container.style.animation = 'none';
                        container.offsetHeight;
                        container.style.animation = 'shake 0.4s cubic-bezier(.36,.07,.19,.97) both';
                    }
                })
                .catch(error => {
                    showAlert('danger', 'A network error occurred. Please try again.', 'fa-triangle-exclamation');
                    loginBtn.classList.remove('loading');
                    loginBtn.disabled = false;
                });
            });

            // Shake Animation CSS injection
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes shake {
                    10%, 90% { transform: translate3d(-1px, 0, 0); }
                    20%, 80% { transform: translate3d(2px, 0, 0); }
                    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
                    40%, 60% { transform: translate3d(4px, 0, 0); }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>
