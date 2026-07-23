<?php include '../../includes/header.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title">System Settings</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Settings</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-4">
    <!-- Settings Navigation -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="list-group list-group-flush border-0 rounded-3 nav" role="tablist">
                    <a class="list-group-item list-group-item-action active border-0 d-flex align-items-center gap-3 py-3" data-bs-toggle="pill" href="#v-pills-general" role="tab" style="font-weight: 500;">
                        <i class="fa-solid fa-globe"></i> General Settings
                    </a>
                    <a class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-3 py-3" data-bs-toggle="pill" href="#v-pills-security" role="tab" style="font-weight: 500;">
                        <i class="fa-solid fa-shield-halved"></i> Security & Roles
                    </a>
                    <a class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-3 py-3" data-bs-toggle="pill" href="#v-pills-notifications" role="tab" style="font-weight: 500;">
                        <i class="fa-solid fa-bell"></i> Notifications
                    </a>
                    <a class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-3 py-3" data-bs-toggle="pill" href="#v-pills-backup" role="tab" style="font-weight: 500;">
                        <i class="fa-solid fa-database"></i> Backup & Restore
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Forms -->
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                
                <div class="tab-content" id="v-pills-tabContent">
                    
                    <!-- General Settings Tab -->
                    <div class="tab-pane fade show active" id="v-pills-general" role="tabpanel">
                        <h5 class="fw-bold text-dark mb-4 border-bottom pb-3">General Application Settings</h5>
                        <form onsubmit="event.preventDefault(); showSuccessToast('General settings updated successfully.');">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <h6 class="fw-semibold text-dark">Organization Info</h6>
                                    <p class="text-muted small">Update the healthcare organization name and emergency contact details displayed on the citizen landing page.</p>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label fw-medium small">Organization Name</label>
                                        <input type="text" class="form-control" value="State Ministry of Health">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium small">Emergency Helpline</label>
                                        <input type="tel" class="form-control" value="1-800-MED-CAMP">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-medium small">Public Support Email</label>
                                        <input type="email" class="form-control" value="support@healthcamps.gov">
                                    </div>
                                </div>
                            </div>
                            <hr class="text-muted opacity-25">
                            <div class="row mb-4 mt-4">
                                <div class="col-md-4">
                                    <h6 class="fw-semibold text-dark">Localization</h6>
                                    <p class="text-muted small">Configure timezone and regional settings.</p>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label fw-medium small">System Timezone</label>
                                        <select class="form-select">
                                            <option>(GMT-05:00) Eastern Time</option>
                                            <option selected>(GMT+05:30) India Standard Time</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <button type="submit" class="btn btn-primary-custom">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Security & Roles Tab -->
                    <div class="tab-pane fade" id="v-pills-security" role="tabpanel">
                        <h5 class="fw-bold text-dark mb-4 border-bottom pb-3">Security & Role Management</h5>
                        <form onsubmit="event.preventDefault(); showSuccessToast('Security settings updated successfully.');">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <h6 class="fw-semibold text-dark">Password Policies</h6>
                                    <p class="text-muted small">Enforce strong password rules for all staff accounts.</p>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="reqUppercase" checked>
                                        <label class="form-check-label fw-medium ms-2" for="reqUppercase">Require Uppercase Letters</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="reqSpecialChar" checked>
                                        <label class="form-check-label fw-medium ms-2" for="reqSpecialChar">Require Special Characters</label>
                                    </div>
                                    <div class="mb-3 mt-3">
                                        <label class="form-label fw-medium small">Minimum Password Length</label>
                                        <input type="number" class="form-control" value="8" min="6" max="32">
                                    </div>
                                </div>
                            </div>
                            <hr class="text-muted opacity-25">
                            <div class="row mb-4 mt-4">
                                <div class="col-md-4">
                                    <h6 class="fw-semibold text-dark">Session Management</h6>
                                    <p class="text-muted small">Control user session timeouts for security.</p>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label fw-medium small">Auto-logout timeout (Minutes)</label>
                                        <select class="form-select">
                                            <option value="15">15 Minutes</option>
                                            <option value="30" selected>30 Minutes</option>
                                            <option value="60">1 Hour</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <button type="submit" class="btn btn-primary-custom">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Notifications Tab -->
                    <div class="tab-pane fade" id="v-pills-notifications" role="tabpanel">
                        <h5 class="fw-bold text-dark mb-4 border-bottom pb-3">Notification Preferences</h5>
                        <form onsubmit="event.preventDefault(); showSuccessToast('Notification settings updated successfully.');">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <h6 class="fw-semibold text-dark">Citizen Alerts</h6>
                                    <p class="text-muted small">Manage what alerts are automatically sent to patients.</p>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="smsAlerts" checked>
                                        <label class="form-check-label fw-medium ms-2" for="smsAlerts">Enable SMS Alerts for Appointments</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="emailAlerts" checked>
                                        <label class="form-check-label fw-medium ms-2" for="emailAlerts">Send Post-Camp Feedback Emails</label>
                                    </div>
                                </div>
                            </div>
                            <hr class="text-muted opacity-25">
                            <div class="row mb-4 mt-4">
                                <div class="col-md-4">
                                    <h6 class="fw-semibold text-dark">Admin Notifications</h6>
                                    <p class="text-muted small">Select when the Super Admin receives system alerts.</p>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="alertMedicine" checked>
                                        <label class="form-check-label" for="alertMedicine">Low Medicine Stock Alerts</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="alertCamp" checked>
                                        <label class="form-check-label" for="alertCamp">New Camp Creation Requests</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                                <button type="submit" class="btn btn-primary-custom">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Backup & Restore Tab -->
                    <div class="tab-pane fade" id="v-pills-backup" role="tabpanel">
                        <h5 class="fw-bold text-dark mb-4 border-bottom pb-3">System Backup & Restore</h5>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <h6 class="fw-semibold text-dark">Automated Backups</h6>
                                <p class="text-muted small">Configure how often the system backs up database records.</p>
                            </div>
                            <div class="col-md-8">
                                <div class="alert alert-success d-flex align-items-center" role="alert">
                                    <i class="fa-solid fa-check-circle me-3 fa-lg"></i>
                                    <div>
                                        <strong>Backup Service is Active</strong><br>
                                        Last successful backup: Today at 02:00 AM
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-medium small">Backup Frequency</label>
                                    <select class="form-select">
                                        <option value="daily" selected>Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <hr class="text-muted opacity-25">
                        <div class="row mb-4 mt-4">
                            <div class="col-md-4">
                                <h6 class="fw-semibold text-dark">Manual Actions</h6>
                                <p class="text-muted small">Manually create or restore a database backup.</p>
                            </div>
                            <div class="col-md-8">
                                <div class="d-grid gap-3 d-md-block">
                                    <button class="btn btn-primary-custom me-md-2" onclick="alert('Manual backup started...')">
                                        <i class="fa-solid fa-download me-2"></i> Generate Backup Now
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="alert('Restore functionality requires server confirmation.')">
                                        <i class="fa-solid fa-rotate-left me-2"></i> Restore from File
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- /.tab-content -->
                
            </div>
        </div>
    </div>
</div>

<style>
/* Custom CSS for pills active state to match our premium aesthetic */
.nav-pills .nav-link.active, .nav-pills .show>.nav-link, .list-group-item.active {
    background-color: var(--sidebar-active-bg) !important;
    color: var(--sidebar-active-color) !important; /* Fixed text contrast for active state */
    border-color: var(--sidebar-active-bg) !important;
    border-left: 4px solid var(--primary-color) !important;
}
.list-group-item {
    border-left: 4px solid transparent !important;
    transition: all 0.2s ease;
}

/* Hover effect matching Quick Actions */
.list-group-item:not(.active):hover {
    background-color: var(--sidebar-text) !important;
    color: var(--primary-color) !important;
    border-left: 4px solid var(--secondary-color) !important;
}
.list-group-item:not(.active):hover i {
    color: var(--primary-color) !important;
}
</style>

<?php include '../../includes/footer.php'; ?>
