<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Shared Navbar/Header Component
 * 
 * Renders the top sticky bar containing sidebar toggler, real-time clock,
 * theme customization trigger, clinical system alerts dropdown, and user action dropdown.
 */

// Safe access validation
if (!defined('APP_NAME')) {
    exit('Direct access not permitted.');
}

// Fetch active authenticated user details
$user = getCurrentUser();
?>
<!-- Main Application Right Panel Wrapper Starts -->
<div class="app-main">

    <!-- Top Sticky Header Navbar -->
    <header class="app-navbar">
        
        <!-- Navbar Left Side (Sidebar toggle and system status indicators) -->
        <div class="navbar-left">
            <button class="sidebar-toggle-btn" id="sidebar-toggle" aria-label="Toggle Sidebar" title="Toggle Navigation Menu">
                <i class="bi bi-list"></i>
            </button>
            
            <!-- Live System Clock Panel -->
            <div class="navbar-datetime d-none d-md-flex" title="Server Synchronized Time (Asia/Kolkata)">
                <span id="navbar-current-date">
                    <i class="bi bi-calendar3"></i> Loading Date...
                </span>
                <span class="time-span" id="navbar-current-time">
                    <i class="bi bi-clock"></i> Loading Clock...
                </span>
            </div>

            <!-- Reusable Global Search Component -->
            <div class="search-bar-custom ms-3 d-none d-lg-block" style="max-width: 250px; position: relative;">
                <i class="bi bi-search" style="font-size: 0.85rem; left: 0.75rem;"></i>
                <input type="text" id="global-patient-search" class="form-control-custom py-1" placeholder="Global search ID, Name, Phone..." style="padding-left: 2rem; height: 32px; font-size: var(--font-size-xs); width: 220px;">
                <div id="global-search-suggestions" class="dropdown-menu shadow-lg p-2 mt-1" style="display: none; max-height: 250px; overflow-y: auto; width: 280px; position: absolute; z-index: 1050;">
                    <!-- Auto Injected Suggestions -->
                </div>
            </div>
        </div>

        <!-- Navbar Right Side (Utilities, Notifications, User Accounts) -->
        <div class="navbar-right">
            
            <!-- Theme Mode Toggle Button -->
            <button class="navbar-action-btn" id="theme-toggle-btn" title="Toggle System Theme">
                <i class="bi bi-moon-stars-fill"></i>
            </button>

            <!-- Dynamic Notifications bell Alert Dropdown -->
            <div class="dropdown">
                <button class="navbar-action-btn" id="notification-bell-btn" data-toggle="custom-dropdown" aria-expanded="false" title="System Health Alerts">
                    <i class="bi bi-bell-fill"></i>
                    <span class="badge-pulse" id="notification-badge" style="display: none;">3</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-xl border-0 mt-2 p-0" aria-labelledby="notification-bell-btn" style="min-width: 320px;">
                    <div class="card-custom-header py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                        <h6 class="m-0 fw-semibold text-primary">Camp System Notifications</h6>
                        <span class="badge-custom badge-custom-primary rounded-pill px-2 py-1 small">New Alerts</span>
                    </div>
                    <div class="notifications-wrapper-list p-2" id="navbar-notifications-list" style="max-height: 280px; overflow-y: auto;">
                        <!-- Dynamic Alerts injected by notification.js -->
                    </div>
                    <div class="text-center py-2 px-3 border-top">
                        <a href="<?php echo BASE_URL; ?>modules/notifications/" class="small fw-semibold text-primary">View all alert history</a>
                    </div>
                </div>
            </div>

            <!-- Health Worker User Details & Actions Menu -->
            <div class="dropdown navbar-user-menu">
                <button class="navbar-user-trigger" id="user-dropdown-btn" data-toggle="custom-dropdown" aria-expanded="false">
                    <img class="navbar-user-avatar" src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Staff Portrait">
                    <div class="navbar-user-details d-none d-lg-flex">
                        <span class="navbar-username"><?php echo htmlspecialchars($user['name']); ?></span>
                        <span class="navbar-userrole"><?php echo ($user['role'] === 'Administrator') ? 'Health Worker' : htmlspecialchars($user['role']); ?></span>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-xl" aria-labelledby="user-dropdown-btn">
                    <li class="dropdown-header-custom">
                        <div class="name"><?php echo htmlspecialchars($user['name']); ?></div>
                        <div class="role text-muted"><?php echo ($user['role'] === 'Administrator') ? 'Health Worker' : htmlspecialchars($user['role']); ?></div>
                        <div class="email text-muted small mt-1" style="font-size: 11px;"><?php echo htmlspecialchars($user['email']); ?></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/profile/">
                            <i class="bi bi-person"></i> Profile Details
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/notifications/">
                            <i class="bi bi-bell"></i> Alerts & Logs
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>authentication/logout.php">
                            <i class="bi bi-box-arrow-right text-danger"></i> Sign Out
                        </a>
                    </li>
                </ul>
            </div>
            
        </div>
    </header>

    <!-- Modal 6: Global Notification Details Dialog -->
    <div class="modal-custom-backdrop" id="notificationDetailsModal">
        <div class="modal-custom-dialog">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title"><i class="bi bi-bell-fill text-accent"></i> Clinical Alert Log</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-custom-body" id="notificationDetailsBody">
                <!-- Injected dynamically by JavaScript -->
            </div>
            <div class="modal-custom-footer">
                <button class="btn-custom btn-custom-outline" data-dismiss="modal">Dismiss</button>
            </div>
        </div>
    </div>
