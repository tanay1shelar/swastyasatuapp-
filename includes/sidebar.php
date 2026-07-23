<?php
<<<<<<< HEAD
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Shared Sidebar Component
 * 
 * Renders the primary collapsible navigation sidebar, list of clinical items,
 * and profile card details of the authenticated health staff.
 */

// Safe access validation
if (!defined('APP_NAME')) {
    exit('Direct access not permitted.');
}

// Fetch active authenticated user details from session
$user = getCurrentUser();
?>
<aside class="app-sidebar" id="app-sidebar">
    <script>
        if (localStorage.getItem('sidebar-collapsed') === 'true' && window.innerWidth >= 992) {
            document.getElementById('app-sidebar').classList.add('collapsed');
        }
    </script>
    
    <!-- Sidebar Header (Brand Identity) -->
    <div class="sidebar-brand">
        <div class="brand-logo-container">
            <img src="<?php echo BASE_URL; ?>assets/images/logo/swasthyasetu-logo.jpeg" alt="SwasthyaSetu Logo" class="brand-logo-img">
        </div>
        <div class="brand-title-group">
            <div class="brand-name-row">
                <span class="brand-name"><?php echo APP_SHORT_NAME; ?></span>
                <span class="brand-badge">v1.0</span>
            </div>
            <span class="brand-subtext">Healthcare & Medical Camp</span>
        </div>
    </div>

    <!-- Navigation links list -->
    <nav class="sidebar-nav">
        <!-- Division 1: Primary Camp Operations -->
        <div class="nav-section-title">Camp Operations</div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/dashboard/" class="nav-link">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/patient-registration/" class="nav-link">
                    <i class="bi bi-person-plus"></i>
                    <span class="nav-label">Patient Registration</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/patient-verification/" class="nav-link">
                    <i class="bi bi-patch-check"></i>
                    <span class="nav-label">Patient Verification</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/patient-attendance/" class="nav-link">
                    <i class="bi bi-calendar2-check"></i>
                    <span class="nav-label">Patient Attendance</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/camp-assistance/" class="nav-link">
                    <i class="bi bi-clipboard2-pulse"></i>
                    <span class="nav-label">Camp Assistance</span>
                </a>
            </li>
        </ul>

        <!-- Division 2: Clinical Records management -->
        <div class="nav-section-title">Medical Records</div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/update-patient/" class="nav-link">
                    <i class="bi bi-person-gear"></i>
                    <span class="nav-label">Update Patient</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/patient-list/" class="nav-link">
                    <i class="bi bi-people"></i>
                    <span class="nav-label">Patient List</span>
                </a>
            </li>
        </ul>

        <!-- Division: Medical Management -->
        <div class="nav-section-title">Medical Management</div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/medical-stock/" class="nav-link">
                    <i class="bi bi-capsule"></i>
                    <span class="nav-label">Medical Stock Inventory</span>
                </a>
            </li>
        </ul>

        <!-- Division 3: Settings & Logs -->
        <div class="nav-section-title">Staff Portal</div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/notifications/" class="nav-link">
                    <i class="bi bi-bell"></i>
                    <span class="nav-label">Notifications</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>modules/profile/" class="nav-link">
                    <i class="bi bi-person-bounding-box"></i>
                    <span class="nav-label">Profile</span>
                </a>
            </li>
            <li class="nav-item border-top border-secondary-subtle mt-3 pt-2">
                <a href="<?php echo BASE_URL; ?>authentication/logout.php" class="nav-link text-danger-hover">
                    <i class="bi bi-box-arrow-right text-danger"></i>
                    <span class="nav-label text-danger">Logout</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar footer profile quick badge -->
    <div class="sidebar-footer">
        <img class="sidebar-avatar" src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="User Avatar">
        <div class="sidebar-user-info">
            <div class="sidebar-user-name" title="<?php echo htmlspecialchars($user['name']); ?>">
                <?php echo htmlspecialchars($user['name']); ?>
            </div>
            <div class="sidebar-user-role"><?php echo ($user['role'] === 'Administrator') ? 'Health Worker' : htmlspecialchars($user['role']); ?></div>
        </div>
    </div>
</aside>
=======
// Global Sidebar template
>>>>>>> origin/main
