<nav class="sidebar p-3 d-flex flex-column" id="appSidebar">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <img src="<?php echo BASE_URL; ?>assets/images/swasthyasetu-logo.png" alt="Swasthya Setu Logo" style="height: 42px; width: 42px; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.25);" />
            <div>
                <h6 class="sidebar-brand-title mb-0" style="font-weight: 800; font-size: 1.05rem; letter-spacing: 0.5px;">Swasthya Setu</h6>
                <small class="sidebar-brand-subtitle d-block" style="font-size: 0.65rem; line-height: 1.1; marginTop: 2px;">Connecting Communities</small>
            </div>
        </div>
        <button type="button" class="btn-close d-lg-none" aria-label="Close sidebar" onclick="window.toggleMobileSidebar && window.toggleMobileSidebar(false)"></button>
    </div>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="<?php echo BASE_URL; ?>modules/doctors/doctor-scheduling.html" class="nav-link active">
                <i class="fa-solid fa-user-doctor me-2"></i> Scheduling
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>modules/health_workers/health-worker-allocation.html" class="nav-link">
                <i class="fa-solid fa-users me-2"></i> Resource Allocation
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>modules/inventory/medicine-inventory.html" class="nav-link">
                <i class="fa-solid fa-pills me-2"></i> Medicine Inventory
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>modules/notifications/camp-notifications.html" class="nav-link">
                <i class="fa-solid fa-bullhorn me-2"></i> Notifications Center
            </a>
        </li>
    </ul>
    <hr />
    <a href="#" class="nav-link"><i class="fa-solid fa-right-from-bracket me-2"></i> Sign Out</a>
</nav>
