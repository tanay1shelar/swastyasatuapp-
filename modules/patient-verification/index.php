<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Patient Identity Verification Module
 * 
 * Provides an active clinical verification workspace featuring patient queue checks,
 * UIDAI Aadhaar links, SMS OTP checks, fingerprint biometrics, and verification logs.
 */

// Define page parameters
$pageTitle = 'Patient Verification';

// Include system config and root layout components
require_once dirname(dirname(__DIR__)) . '/config/config.php';
include ROOT_PATH . 'includes/header.php';
include ROOT_PATH . 'includes/sidebar.php';
include ROOT_PATH . 'includes/navbar.php';
?>

<!-- Reusable Module Verification Content Area -->
<main class="app-content-wrapper">
    
    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="app-page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-custom-item"><a href="index.php">Health Worker Portal</a></li>
                    <li class="breadcrumb-custom-item active" aria-current="page">Verification</li>
                </ol>
            </nav>
            <h1 class="page-title">Identity Verification Workspace</h1>
            <p class="text-secondary mb-0">Crosscheck registered attendee details against UIDAI Aadhaar registers using biometric scans or secure OTP pins.</p>
        </div>
        <!-- Export button -->
        <button class="btn-custom btn-custom-outline" id="btn-export-verification">
            <i class="bi bi-file-earmark-arrow-down"></i> Export
        </button>
    </div>

    <!-- Dynamic Alert Container for Verification Actions -->
    <div id="alert-container-verification" class="mb-3"></div>

    <!-- 2. WORKSPACE GRID -->
    <div class="row">
        
        <!-- Left Column: Verification Queue (30% equivalent: col-lg-4) -->
        <div class="col-12 col-lg-4 mb-4">
            <div class="card-custom h-100">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-people-fill text-accent"></i> Verification Queue</h5>
                </div>
                
                <!-- Queue Search -->
                <div class="p-3 border-bottom bg-light">
                    <div class="search-bar-custom w-100" style="max-width: 100%;">
                        <i class="bi bi-search"></i>
                        <input type="text" id="queue-search" class="form-control-custom" placeholder="Search pending queue...">
                    </div>
                </div>

                <!-- Queue List -->
                <div class="card-custom-body p-0" style="max-height: 520px; overflow-y: auto;">
                    <div class="list-group list-group-flush" id="verification-pending-list">
                        <!-- Injected by JS -->
                        <div class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                            <span class="d-block small">Synchronizing pending queue...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Aadhaar Verification Console (70% equivalent: col-lg-8) -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card-custom h-100" id="active-workspace-card">
                <!-- Search Box Header (Always visible) -->
                <div class="p-4 border-bottom bg-light">
                    <h6 class="fw-semibold text-primary mb-3"><i class="bi bi-person-vcard text-accent"></i> Aadhaar Identity Verification Console</h6>
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-6">
                            <label class="form-label-custom">12-Digit Aadhaar Card Number</label>
                            <input type="text" class="form-control-custom form-control-lg text-center font-monospace fs-5 fw-bold" id="verify-aadhaar-input" placeholder="XXXX XXXX XXXX" maxlength="14" style="letter-spacing: 2px;">
                        </div>
                        <div class="col-12 col-md-3">
                            <button class="btn btn-primary w-100 py-2 fs-6 fw-semibold" id="btn-fetch-uidai" style="height: 48px;"><i class="bi bi-search"></i> Search Aadhaar</button>
                        </div>
                        <div class="col-12 col-md-3">
                            <button class="btn btn-outline-secondary w-100 py-2 fs-6 fw-semibold" id="btn-clear-aadhaar" style="height: 48px;"><i class="bi bi-x-circle"></i> Clear</button>
                        </div>
                    </div>
                    <div class="mt-3" id="search-error-msg" style="display: none;">
                        <div class="alert alert-danger mb-0 py-2 px-3 small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> No patient found.
                        </div>
                    </div>
                </div>

                <!-- Empty State (Shown when no active patient is loaded) -->
                <div class="card-custom-body d-flex flex-column align-items-center justify-content-center py-5 text-center" id="workspace-empty-state">
                    <i class="bi bi-shield-lock text-muted mb-3" style="font-size: 4.5rem;"></i>
                    <h5 class="fw-semibold text-primary">Verification Console Idle</h5>
                    <p class="text-secondary small" style="max-width: 340px;">Search a patient's 12-digit Aadhaar number or select a patient from the Verification Queue to load information.</p>
                </div>

                <!-- Active Patient Form Workspace (Hidden by default using Bootstrap d-none) -->
                <div class="card-custom-body p-4 d-none" id="workspace-active-form">
                    
                    <!-- Dynamic Verification Alert Box -->
                    <div id="verification-alert-container" class="mb-3"></div>

                    <!-- Large Patient Information Card -->
                    <div class="card border mb-3 bg-light-subtle">
                        <div class="card-body">
                            <!-- Photo and Name Header -->
                            <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-3">
                                <img id="detail-patient-photo" src="" alt="Portrait" class="rounded-circle border" style="width: 90px; height: 90px; object-fit: cover; border-width: 3px !important;">
                                <div>
                                    <h4 id="detail-patient-name" class="fw-bold text-primary m-0"></h4>
                                    <div class="text-muted small mt-1">
                                        Patient ID: <strong id="detail-patient-id" class="font-monospace text-secondary"></strong>
                                    </div>
                                    <div class="text-muted small">
                                        Registration Number: <strong id="detail-reg-number" class="text-secondary"></strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Details Grid -->
                            <div class="row g-3 small text-secondary">
                                <div class="col-12 col-md-4">
                                    <span class="d-block text-muted small">Aadhaar Number</span>
                                    <strong id="detail-patient-aadhaar" class="text-dark font-monospace fs-6"></strong>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="d-block text-muted small">Age</span>
                                    <strong id="detail-patient-age" class="text-dark"></strong>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="d-block text-muted small">Gender</span>
                                    <strong id="detail-patient-gender" class="text-dark"></strong>
                                </div>

                                <div class="col-6 col-md-4 border-top pt-2">
                                    <span class="d-block text-muted small">Blood Group</span>
                                    <strong id="detail-patient-blood" class="text-primary fs-6"></strong>
                                </div>
                                <div class="col-6 col-md-4 border-top pt-2">
                                    <span class="d-block text-muted small">Phone Number</span>
                                    <strong id="detail-patient-phone" class="text-dark"></strong>
                                </div>
                                <div class="col-12 col-md-4 border-top pt-2">
                                    <span class="d-block text-muted small">Emergency Contact</span>
                                    <strong id="detail-patient-emergency" class="text-dark"></strong>
                                </div>

                                <div class="col-12 col-md-4 border-top pt-2">
                                    <span class="d-block text-muted small">Camp Location Site</span>
                                    <strong id="detail-patient-camp" class="text-dark"></strong>
                                </div>
                                <div class="col-6 col-md-4 border-top pt-2">
                                    <span class="d-block text-muted small">Registration Date</span>
                                    <strong id="detail-patient-reg-date" class="text-dark"></strong>
                                </div>
                                <div class="col-6 col-md-4 border-top pt-2">
                                    <span class="d-block text-muted small">Current Status</span>
                                    <strong id="detail-patient-status-badge" class="text-dark">--</strong>
                                </div>

                                <div class="col-6 col-md-4 border-top pt-2">
                                    <span class="d-block text-muted small">Verification Status</span>
                                    <strong id="detail-patient-verification-status" class="text-dark"></strong>
                                </div>
                                <div class="col-6 col-md-4 border-top pt-2">
                                    <span class="d-block text-muted small">Attendance Status</span>
                                    <strong id="detail-patient-attendance-status" class="text-dark"></strong>
                                </div>
                                <div class="col-12 border-top pt-2">
                                    <span class="d-block text-muted small">Residential Address</span>
                                    <span id="detail-patient-address" class="text-dark"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer actions (Verify Button & Metadata) -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3" id="verification-actions-footer">
                        <!-- Verification History Audit metadata -->
                        <div id="verified-meta-details" style="display: none;">
                            <span class="d-block text-success fw-semibold mb-1" style="font-size: 11px;"><i class="bi bi-patch-check-fill"></i> Already Verified</span>
                            <div class="text-muted" style="font-size: 10px; line-height: 1.4;">
                                <div>Date: <strong id="verified-date">--</strong></div>
                                <div>Time: <strong id="verified-time">--</strong></div>
                                <div>Verifier: <strong id="verified-by">--</strong></div>
                                <div>Ref ID: <strong id="verified-id">--</strong></div>
                            </div>
                        </div>
                        <div class="w-100 text-end">
                            <button class="btn btn-success btn-lg px-5 py-2 fw-semibold" id="btn-confirm-verification" disabled><i class="bi bi-shield-check"></i> VERIFY</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- 3. VERIFICATION HISTORY TIMELINE (12 columns) -->
    <div class="row mt-2">
        <div class="col-12">
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-journal-medical text-accent"></i> Recent Verification Logs</h5>
                </div>
                <div class="card-custom-body p-0">
                    <div class="table-custom-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Patient Name & ID</th>
                                    <th>Method Logged</th>
                                    <th>Verification Remarks</th>
                                    <th>Timestamp</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody id="verification-history-table">
                                <!-- Injected dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Confirmation Modal -->
    <div class="modal fade" id="confirmVerificationModal" tabindex="-1" aria-labelledby="confirmVerificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-primary" id="confirmVerificationModalLabel">Confirm Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4 text-center">
                    <i class="bi bi-shield-question text-warning display-4 d-block mb-3"></i>
                    <p class="mb-0 fs-6 text-secondary">Are you sure you want to verify this patient?</p>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success px-4" id="btn-modal-confirm-verify">Confirm</button>
                </div>
            </div>
        </div>
    </div>

</main><!-- /.app-content-wrapper -->

<!-- Load active database mocks and verification script handlers -->
<script src="<?php echo BASE_URL; ?>assets/js/core/database-mock.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/modules/patient-verification.js"></script>

<?php
// Include structural footer scripts
include ROOT_PATH . 'includes/footer.php';
?>
