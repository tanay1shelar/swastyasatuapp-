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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = trim($_POST['role'] ?? 'citizen');
        $gender = trim($_POST['gender'] ?? '');
        $dob = trim($_POST['dob'] ?? '');
        $address = trim($_POST['address'] ?? '');

        // Allowed public registration roles ONLY
        $allowedRoles = ['citizen', 'doctor', 'health-worker'];
        $normalizedRole = str_replace('_', '-', $role);

        // Validation
        if (empty($fullName) || empty($email) || empty($phone) || empty($username) || empty($password) || empty($confirmPassword) || empty($gender)) {
            $error = 'Please fill in all required fields marked with (*).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (!in_array($normalizedRole, $allowedRoles)) {
            $error = 'Selected user role is invalid for public registration.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Password and Confirm Password do not match.';
        } else {
            try {
                // Unique Email & Username Check
                $checkStmt = $pdo->prepare("SELECT id, email, username FROM users WHERE email = :email OR username = :username LIMIT 1");
                $checkStmt->execute(['email' => $email, 'username' => $username]);
                $existingUser = $checkStmt->fetch();

                if ($existingUser) {
                    if (strtolower($existingUser['email']) === strtolower($email)) {
                        $error = 'Email address is already registered.';
                    } else {
                        $error = 'Username is already taken. Please choose another.';
                    }
                } else {
                    // Password Hashing
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                    // Prepared Statement Insertion
                    $insertStmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, username, password_hash, role, gender, dob, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $dbDob = !empty($dob) ? $dob : NULL;
                    
                    if ($insertStmt->execute([$fullName, $email, $phone, $username, $passwordHash, $normalizedRole, $gender, $dbDob, $address])) {
                        header("Location: login.php?msg=registered");
                        exit();
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Database error occurred. Please try again later.';
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
    <title>Account Registration - HMCMS Portal</title>
    <link rel="stylesheet" href="../../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-container { max-width: 580px; }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        @media (max-width: 576px) { .form-row { flex-direction: column; gap: 0; } }
        select.form-input { appearance: auto; cursor: pointer; }
    </style>
</head>
<body>
    <div class="bg-shape shape1"></div>
    <div class="bg-shape shape2"></div>

    <div class="auth-container">
        <div class="auth-header">
            <img src="../../assets/images/logo/logo.png" alt="Swasthya Setu Logo" class="auth-logo">
            <h1>Create an Account</h1>
            <p>Swasthya Setu • Connecting Communities to Better Healthcare</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="registerForm" novalidate>
            <!-- CSRF Token -->
            <?php 
                if (empty($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            ?>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <label class="form-label" for="full_name">Full Name <span style="color:red">*</span></label>
                <div class="form-input-group">
                    <input type="text" id="full_name" name="full_name" class="form-input" placeholder="John Doe" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                    <i class="fa-regular fa-id-card"></i>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address <span style="color:red">*</span></label>
                    <div class="form-input-group">
                        <input type="email" id="email" name="email" class="form-input" placeholder="john@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Mobile Number <span style="color:red">*</span></label>
                    <div class="form-input-group">
                        <input type="text" id="phone" name="phone" class="form-input" placeholder="+1 234 567 890" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                        <i class="fa-solid fa-phone"></i>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="username">Username <span style="color:red">*</span></label>
                    <div class="form-input-group">
                        <input type="text" id="username" name="username" class="form-input" placeholder="johndoe" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        <i class="fa-regular fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="role">User Role <span style="color:red">*</span></label>
                    <div class="form-input-group">
                        <select id="role" name="role" class="form-input" style="padding-left: 45px;" required>
                            <option value="citizen" <?php echo (($_POST['role'] ?? '') === 'citizen') ? 'selected' : ''; ?>>Citizen</option>
                            <option value="doctor" <?php echo (($_POST['role'] ?? '') === 'doctor') ? 'selected' : ''; ?>>Doctor</option>
                            <option value="health-worker" <?php echo (($_POST['role'] ?? '') === 'health-worker') ? 'selected' : ''; ?>>Health Worker</option>
                        </select>
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">Password <span style="color:red">*</span></label>
                    <div class="form-input-group">
                        <input type="password" id="password" name="password" class="form-input" placeholder="Min 6 characters" required>
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password <span style="color:red">*</span></label>
                    <div class="form-input-group">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Re-enter password" required>
                        <i class="fa-solid fa-shield-check"></i>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="gender">Gender <span style="color:red">*</span></label>
                    <div class="form-input-group">
                        <select id="gender" name="gender" class="form-input" style="padding-left: 45px;" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (($_POST['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <i class="fa-solid fa-venus-mars"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="dob">Date of Birth (Optional)</label>
                    <div class="form-input-group">
                        <input type="date" id="dob" name="dob" class="form-input" value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address (Optional)</label>
                <div class="form-input-group">
                    <input type="text" id="address" name="address" class="form-input" placeholder="City, State, Country" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="registerBtn" style="margin-top: 10px;">
                <span class="btn-text">Complete Registration</span>
                <div class="spinner"></div>
            </button>

            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" class="forgot-link" style="color: #64748b; font-size: 14px;">
                    Already have an account? <strong style="color: #0ea5e9;">Log In</strong>
                </a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const registerBtn = document.getElementById('registerBtn');

            form.addEventListener('submit', function(e) {
                let isValid = true;
                const required = form.querySelectorAll('input[required], select[required]');

                required.forEach(input => {
                    input.classList.remove('input-error');
                    if (!input.value.trim()) {
                        input.classList.add('input-error');
                        isValid = false;
                    }
                });

                const pwd = document.getElementById('password');
                const cpwd = document.getElementById('confirm_password');

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
                    registerBtn.classList.add('loading');
                }
            });

            // Injection for shake animation
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
