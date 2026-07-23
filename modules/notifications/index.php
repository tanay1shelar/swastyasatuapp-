<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Alerts & System Notifications Log Page (Interactive Version)
 * 
 * Renders categorized historical logs (Today, Yesterday, Older)
 * with category filters, priority indicators, search bars, and details modal popups.
 */

// Define page parameters
$pageTitle = 'Alerts & System Notifications';

// Include system config and root layout components
require_once dirname(dirname(__DIR__)) . '/config/config.php';
include ROOT_PATH . 'includes/header.php';
include ROOT_PATH . 'includes/sidebar.php';
include ROOT_PATH . 'includes/navbar.php';
?>

<main class="app-content-wrapper">
    
    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="app-page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-custom-item"><a href="index.php">Health Worker Portal</a></li>
                    <li class="breadcrumb-custom-item active" aria-current="page">Alerts & Logs</li>
                </ol>
            </nav>
            <h1 class="page-title">Notification Center</h1>
            <p class="text-secondary mb-0">Browse and filter clinical alarms, logistics restocks, verification challenges, and emergency queue events.</p>
        </div>
        
        <!-- Action Buttons -->
        <div>
            <button class="btn-custom btn-custom-outline" id="btn-mark-all-read">
                <i class="bi bi-envelope-open"></i> Mark All as Read
            </button>
        </div>
    </div>

    <!-- 2. SEARCH & FILTER HEADER CARD -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-custom">
                <div class="card-custom-body p-3 d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    
                    <!-- Search input -->
                    <div class="search-bar-custom" style="max-width: 320px;">
                        <i class="bi bi-search"></i>
                        <input type="text" id="notifications-search-input" class="form-control-custom" placeholder="Search notifications by keyword...">
                    </div>

                    <!-- Category Type Filters -->
                    <div class="d-flex gap-2">
                        <select id="notifications-type-filter" class="form-control-custom" style="max-width: 180px; height: 38px;">
                            <option value="">All Categories</option>
                            <option value="Registration">Registration</option>
                            <option value="Verification">Verification</option>
                            <option value="Attendance">Attendance</option>
                            <option value="Camp">Camp</option>
                            <option value="Medicine">Medicine Inventory</option>
                            <option value="Emergency">Emergency Triage</option>
                        </select>

                        <select id="notifications-priority-filter" class="form-control-custom" style="max-width: 150px; height: 38px;">
                            <option value="">All Priorities</option>
                            <option value="High">High Priority</option>
                            <option value="Medium">Medium Priority</option>
                            <option value="Low">Low Priority</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- 3. BUCKET LISTS FOR SECTIONS (Today, Yesterday, Older) -->
    <div class="row">
        <div class="col-12">
            <div class="card-custom">
                <div class="card-custom-header d-flex justify-content-between align-items-center">
                    <h5 class="card-custom-title"><i class="bi bi-bell-fill text-accent"></i> System Activity Feeds</h5>
                    <span id="unread-page-badge" class="badge bg-danger rounded-pill px-3 py-1 text-white">0 Unread Alerts</span>
                </div>
                <div class="card-custom-body p-4">
                    
                    <!-- SECTION A: TODAY -->
                    <div class="mb-4">
                        <h6 class="text-accent fw-bold border-bottom pb-2 mb-3"><i class="bi bi-clock-fill"></i> Today</h6>
                        <div id="notifications-today-list">
                            <!-- Injected by JS -->
                        </div>
                    </div>

                    <!-- SECTION B: YESTERDAY -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-calendar-event"></i> Yesterday</h6>
                        <div id="notifications-yesterday-list">
                            <!-- Injected by JS -->
                        </div>
                    </div>

                    <!-- SECTION C: OLDER -->
                    <div>
                        <h6 class="text-secondary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-archive-fill"></i> Older Logs</h6>
                        <div id="notifications-older-list">
                            <!-- Injected by JS -->
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</main><!-- /.app-content-wrapper -->

<!-- Add page-specific styles for clickable items -->
<style>
    .notification-page-item {
        transition: var(--transition-normal);
        cursor: pointer;
    }
    .notification-page-item:hover {
        background-color: var(--bg-app);
        transform: translateX(4px);
    }
</style>

<?php
// Include structural footer scripts
include ROOT_PATH . 'includes/footer.php';
?>
