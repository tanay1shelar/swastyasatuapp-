<header class="top-header">
    <div class="header-left">
        <button class="toggle-sidebar-btn" id="sidebarToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
    
    <div class="header-right">
        <div class="notification-wrapper dropdown">
            <div class="notification-bell" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer; position: relative;">
                <i class="fa-regular fa-bell"></i>
                <span class="badge-dot" id="mainBellDot"></span>
            </div>
            
            <div class="dropdown-menu dropdown-menu-end shadow border-0 mt-3" style="width: 320px; padding: 0;">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background-color: var(--bg-main);">
                    <h6 class="mb-0 fw-bold" style="color: var(--primary-color);">Notifications</h6>
                    <span class="badge bg-danger rounded-pill" id="dropdownBadge">2 New</span>
                </div>
                
                <div style="max-height: 300px; overflow-y: auto;">
                    <!-- Notification Item 1 -->
                    <a href="#" class="dropdown-item p-3 border-bottom" style="white-space: normal;">
                        <div class="d-flex gap-3">
                            <div class="text-danger mt-1"><i class="fa-solid fa-circle-exclamation"></i></div>
                            <div>
                                <p class="mb-1 fw-bold text-dark" style="font-size: 0.9rem;">Low Medicine Stock</p>
                                <p class="mb-1 text-muted" style="font-size: 0.8rem;">City General Hospital is running low on Paracetamol (Batch #442).</p>
                                <small class="text-muted" style="font-size: 0.75rem;">10 mins ago</small>
                            </div>
                        </div>
                    </a>
                    
                    <!-- Notification Item 2 -->
                    <a href="#" class="dropdown-item p-3 border-bottom" style="white-space: normal; background-color: var(--bg-card);">
                        <div class="d-flex gap-3">
                            <div class="mt-1" style="color: var(--accent-color);"><i class="fa-solid fa-calendar-plus"></i></div>
                            <div>
                                <p class="mb-1 fw-bold text-dark" style="font-size: 0.9rem;">New Camp Request</p>
                                <p class="mb-1 text-muted" style="font-size: 0.8rem;">Pending approval for "Rural Eye Checkup" in Westend District.</p>
                                <small class="text-muted" style="font-size: 0.75rem;">1 hour ago</small>
                            </div>
                        </div>
                    </a>

                    <!-- Notification Item 3 (Read) -->
                    <a href="#" class="dropdown-item p-3" style="white-space: normal; opacity: 0.7;">
                        <div class="d-flex gap-3">
                            <div class="mt-1" style="color: var(--secondary-color);"><i class="fa-solid fa-file-export"></i></div>
                            <div>
                                <p class="mb-1 fw-bold text-dark" style="font-size: 0.9rem;">Report Generated</p>
                                <p class="mb-1 text-muted" style="font-size: 0.8rem;">The Monthly Camp Performance PDF is ready for download.</p>
                                <small class="text-muted" style="font-size: 0.75rem;">Yesterday</small>
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="p-2 border-top text-center">
                    <a href="#notificationsOffcanvas" data-bs-toggle="offcanvas" class="text-decoration-none fw-medium" style="color: var(--primary-color); font-size: 0.85rem;">View All Notifications</a>
                </div>
            </div>
        </div>
        
        <div class="user-profile dropdown">
            <div class="d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://ui-avatars.com/api/?name=Super+Admin&background=0284c7&color=fff" alt="User" class="user-avatar">
                <div class="user-info d-none d-md-flex">
                    <span class="user-name">Super Admin</span>
                    <span class="user-role">System Administrator</span>
                </div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><a class="dropdown-item" href="#"><i class="fa-regular fa-user me-2"></i> Profile</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fa-solid fa-gear me-2"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</header>

<!-- Notifications Offcanvas Sidebar -->
<div class="offcanvas offcanvas-end shadow" tabindex="-1" id="notificationsOffcanvas" aria-labelledby="notificationsOffcanvasLabel" style="width: 420px; border-left: 1px solid var(--border-color);">
    <div class="offcanvas-header border-bottom py-4" style="background-color: var(--bg-main);">
        <h5 class="offcanvas-title fw-bold" id="notificationsOffcanvasLabel" style="color: var(--primary-color);">
            <i class="fa-regular fa-bell me-2"></i> All Notifications
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0" style="background-color: #fff;">
        <div class="list-group list-group-flush">
            <!-- Urgent Item -->
            <a href="#" class="list-group-item list-group-item-action p-4 border-bottom">
                <div class="d-flex gap-3">
                    <div class="text-danger mt-1"><i class="fa-solid fa-circle-exclamation fa-lg"></i></div>
                    <div>
                        <div class="d-flex w-100 justify-content-between mb-1">
                            <h6 class="mb-0 fw-bold text-dark">Low Medicine Stock</h6>
                            <small class="text-muted fw-medium">10 mins ago</small>
                        </div>
                        <p class="mb-2 text-muted" style="font-size: 0.9rem; line-height: 1.4;">City General Hospital is running low on Paracetamol (Batch #442). Restock required immediately.</p>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">Action Required</span>
                    </div>
                </div>
            </a>
            
            <!-- Actionable Item -->
            <a href="#" class="list-group-item list-group-item-action p-4 border-bottom" style="background-color: var(--bg-card);">
                <div class="d-flex gap-3">
                    <div class="mt-1" style="color: var(--accent-color);"><i class="fa-solid fa-calendar-plus fa-lg"></i></div>
                    <div>
                        <div class="d-flex w-100 justify-content-between mb-1">
                            <h6 class="mb-0 fw-bold text-dark">New Camp Request</h6>
                            <small class="text-muted fw-medium">1 hour ago</small>
                        </div>
                        <p class="mb-2 text-muted" style="font-size: 0.9rem; line-height: 1.4;">Pending approval for "Rural Eye Checkup" in Westend District. Organized by Dr. Alan Smith.</p>
                        <button class="btn btn-sm btn-primary-custom py-1 px-3" style="font-size: 0.8rem;" id="btnReviewRequest">Review Request</button>
                    </div>
                </div>
            </a>

            <!-- Informational Item 1 -->
            <a href="#" class="list-group-item list-group-item-action p-4 border-bottom" style="opacity: 0.85;">
                <div class="d-flex gap-3">
                    <div class="mt-1" style="color: var(--secondary-color);"><i class="fa-solid fa-file-export fa-lg"></i></div>
                    <div>
                        <div class="d-flex w-100 justify-content-between mb-1">
                            <h6 class="mb-0 fw-bold text-dark">Report Generated</h6>
                            <small class="text-muted fw-medium">Yesterday</small>
                        </div>
                        <p class="mb-2 text-muted" style="font-size: 0.9rem; line-height: 1.4;">The Monthly Camp Performance PDF is ready for download.</p>
                        <a href="javascript:void(0)" id="btnDownloadReport" class="text-decoration-none fw-semibold" style="color: var(--secondary-color); font-size: 0.85rem;"><i class="fa-solid fa-download me-1"></i> Download File</a>
                    </div>
                </div>
            </a>
            
            <!-- Informational Item 2 -->
            <a href="#" class="list-group-item list-group-item-action p-4 border-bottom" style="opacity: 0.85;">
                <div class="d-flex gap-3">
                    <div class="mt-1" style="color: var(--secondary-color);"><i class="fa-solid fa-check-circle fa-lg"></i></div>
                    <div>
                        <div class="d-flex w-100 justify-content-between mb-1">
                            <h6 class="mb-0 fw-bold text-dark">System Backup Complete</h6>
                            <small class="text-muted fw-medium">2 Days ago</small>
                        </div>
                        <p class="mb-0 text-muted" style="font-size: 0.9rem; line-height: 1.4;">Automated database backup was completed successfully without errors.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="p-4 text-center">
            <button class="btn btn-outline-custom btn-sm" id="btnMarkAllRead"><i class="fa-solid fa-check-double me-1"></i> Mark All as Read</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Review Request Button
    const btnReview = document.getElementById('btnReviewRequest');
    if (btnReview) {
        btnReview.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Loading Camp Approval workflow for "Rural Eye Checkup"...');
            window.location.href = '../monitoring/index.php'; // Redirect to monitoring
        });
    }

    // Download File Button
    const btnDownload = document.getElementById('btnDownloadReport');
    if (btnDownload) {
        btnDownload.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Downloading Monthly_Camp_Performance.pdf...');
            // In a real app, this would trigger an actual file download
        });
    }

    // Mark All as Read Button
    const btnMarkAll = document.getElementById('btnMarkAllRead');
    if (btnMarkAll) {
        btnMarkAll.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove red dots and badges
            const dot = document.getElementById('mainBellDot');
            if (dot) dot.style.display = 'none';
            
            const badge = document.getElementById('dropdownBadge');
            if (badge) badge.style.display = 'none';
            
            // Remove the 'Action Required' urgency text and styles
            document.querySelectorAll('.badge.bg-danger').forEach(el => {
                if(el.id !== 'dropdownBadge') el.style.display = 'none';
            });
            
            // Dim all notification items slightly to indicate 'read' status
            document.querySelectorAll('.list-group-item').forEach(el => {
                el.style.opacity = '0.7';
                el.style.backgroundColor = '#fff';
            });
            
            alert('All notifications have been marked as read.');
        });
    }
});
</script>
