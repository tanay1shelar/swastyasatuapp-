<header class="top-header p-3 sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-lg-none me-1" id="mobileDrawerTrigger" type="button" aria-label="Toggle Navigation">
                <i class="fa-solid fa-bars fs-5"></i>
            </button>
            <img src="<?php echo BASE_URL; ?>assets/images/swasthyasetu-logo.png" alt="Swasthya Setu Logo" style="height: 44px; width: 44px; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.15);" />
            <div>
                <h5 class="fw-extrabold text-teal mb-0" style="letter-spacing: 0.5px;">Swasthya Setu</h5>
                <small class="text-muted d-block" style="font-size: 0.7rem; line-height: 1;">Connecting Communities To Better Healthcare</small>
            </div>
        </div>

        <div class="d-none d-lg-flex align-items-center">
            <a href="<?php echo BASE_URL; ?>modules/doctors/doctor-scheduling.html" class="nav-link-item">Scheduling</a>
            <a href="<?php echo BASE_URL; ?>modules/health_workers/health-worker-allocation.html" class="nav-link-item">Allocation</a>
            <a href="<?php echo BASE_URL; ?>modules/inventory/medicine-inventory.html" class="nav-link-item">Inventory</a>
            <a href="<?php echo BASE_URL; ?>modules/notifications/camp-notifications.html" class="nav-link-item">Notifications</a>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm theme-toggle-btn d-flex align-items-center" type="button">
                <i class="fa-solid fa-moon me-1"></i> Dark Mode
            </button>
            <button class="btn btn-primary rounded-pill px-4 btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#roleSignInModal">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In
            </button>
        </div>
    </div>
</header>
