<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Update Patient Profile Module
 * 
 * Provides a patient directory search input and records update console.
 * Enables editing patient credentials, medical summaries, and printing camp cards.
 */

// Define page parameters
$pageTitle = 'Update Patient Profile';

// Include system config and root layout components
require_once dirname(dirname(__DIR__)) . '/config/config.php';
include ROOT_PATH . 'includes/header.php';
include ROOT_PATH . 'includes/sidebar.php';
include ROOT_PATH . 'includes/navbar.php';
?>

<!-- Reusable Module Update Patient Content Area -->
<main class="app-content-wrapper">
    
    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="app-page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-custom-item"><a href="index.php">Health Worker Portal</a></li>
                    <li class="breadcrumb-custom-item active" aria-current="page">Update Patient</li>
                </ol>
            </nav>
            <h1 class="page-title">Modify Patient Records</h1>
            <p class="text-secondary mb-0">Search registered camp attendees by name, phone, or Aadhaar, and edit their clinical and personal details.</p>
        </div>
    </div>

    <!-- 2. LARGE SEARCH PANEL -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-custom">
                <div class="card-custom-body p-4">
                    <label class="form-label-custom fs-6 text-accent mb-2">Search Patient Profile Directory</label>
                    <div class="search-large-container position-relative">
                        <i class="bi bi-search search-large-icon text-muted"></i>
                        <input type="text" id="patient-update-search" class="form-control-custom form-control-lg" placeholder="Type Patient ID, Full Name, Contact Phone, or Aadhaar Card number..." style="padding-left: 3rem; height: 50px;">
                        
                        <!-- Search results auto-suggestions box -->
                        <div id="search-suggestions" class="dropdown-menu shadow-lg w-100 p-2 mt-1" style="display: none; max-height: 280px; overflow-y: auto;">
                            <!-- Injected by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. WORKSPACE GRID -->
    <div class="row" id="update-workspace-container">
        
        <!-- Empty State Card (Visible initially) -->
        <div class="col-12" id="workspace-empty-state">
            <div class="card-custom text-center py-5">
                <div class="card-custom-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-search-heart text-muted mb-3" style="font-size: 4rem;"></i>
                    <h5 class="fw-semibold text-primary">Initialize Edit Console</h5>
                    <p class="text-secondary small mb-0" style="max-width: 380px;">Type a patient name or ID inside the query bar above, select the record, and edit the patient demographics.</p>
                </div>
            </div>
        </div>

        <!-- Selected Patient Workspace (Hidden initially) -->
        <div class="col-12 col-lg-4 mb-4" id="workspace-left-panel" style="display: none !important;">
            <div class="card-custom text-center h-100 py-5">
                <div class="card-custom-body d-flex flex-column align-items-center justify-content-center">
                    <!-- Photo -->
                    <div class="mb-3 position-relative">
                        <img id="patient-card-photo" src="" alt="Patient Portrait" class="rounded-circle border" style="width: 120px; height: 120px; object-fit: cover; border-width: 4px !important;">
                    </div>
                    
                    <h4 id="patient-card-name" class="fw-bold text-primary mb-1">Rajesh Kumar</h4>
                    <span id="patient-card-id" class="badge bg-secondary-subtle text-secondary px-3 py-1 mb-3">PAT-042</span>

                    <div class="text-start w-100 border-top mt-3 pt-3 px-3">
                        <div class="mb-2">
                            <span class="d-block text-muted small">Assigned camp clinic</span>
                            <strong class="text-secondary small" id="patient-card-camp">Apollo Health Camp - Phase 1</strong>
                        </div>
                        <div class="mb-2">
                            <span class="d-block text-muted small">Assigned staff physician</span>
                            <strong class="text-secondary small" id="patient-card-doctor">Dr. Rajesh Verma</strong>
                        </div>
                        <div class="mb-0">
                            <span class="d-block text-muted small">Aadhaar Card Reference</span>
                            <strong class="text-secondary small font-monospace" id="patient-card-aadhaar">8765 4321 0987</strong>
                        </div>
                    </div>

                    <div class="w-100 mt-4 px-3">
                        <button class="btn-custom btn-custom-outline w-100" id="btn-print-patient-card">
                            <i class="bi bi-printer"></i> Print Patient Camp Card
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8 mb-4" id="workspace-right-panel" style="display: none !important;">
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-pencil-square text-accent"></i> Modify Intake Information</h5>
                </div>
                <div class="card-custom-body p-4">
                    
                    <!-- Update form -->
                    <form class="needs-validation" id="patient-update-form" novalidate>
                        
                        <!-- Demographics -->
                        <h6 class="form-label-custom border-bottom pb-2 mb-3 text-accent"><i class="bi bi-person-vcard"></i> 1. Profile Credentials</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Full Name</label>
                                    <input type="text" class="form-control-custom" name="name" id="edit-name" required>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Gender</label>
                                    <select class="form-control-custom" name="gender" id="edit-gender" style="height: 38px;" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Blood Group</label>
                                    <select class="form-control-custom" name="blood" id="edit-blood" style="height: 38px;" required>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Date of Birth</label>
                                    <input type="date" class="form-control-custom" name="dob" id="edit-dob" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Contact Phone</label>
                                    <input type="tel" class="form-control-custom" name="phone" id="edit-phone" pattern="^\+91 [0-9]{5}\s[0-9]{5}$" placeholder="+91 XXXXX XXXXX" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Email Address</label>
                                    <input type="email" class="form-control-custom" name="email" id="edit-email" required>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <h6 class="form-label-custom border-bottom pb-2 mb-3 text-accent"><i class="bi bi-geo-alt"></i> 2. Residential Address</h6>
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Street details</label>
                                    <textarea class="form-control-custom" name="address" id="edit-address" rows="2" required></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <h6 class="form-label-custom border-bottom pb-2 mb-3 text-accent"><i class="bi bi-telephone-outbound"></i> 3. Emergency Contact Details</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Contact Person Name</label>
                                    <input type="text" class="form-control-custom" name="emergencyName" id="edit-emergencyName" required>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Relationship</label>
                                    <input type="text" class="form-control-custom" name="emergencyRelation" id="edit-emergencyRelation" required>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Contact Phone</label>
                                    <input type="tel" class="form-control-custom" name="emergencyPhone" id="edit-emergencyPhone" pattern="^\+91 [0-9]{5}\s[0-9]{5}$" placeholder="+91 XXXXX XXXXX" required>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Details -->
                        <h6 class="form-label-custom border-bottom pb-2 mb-3 text-accent"><i class="bi bi-heart-pulse"></i> 4. Clinical Medical History</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Allergies</label>
                                    <input type="text" class="form-control-custom" name="allergies" id="edit-allergies">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Chronic Illnesses / Conditions</label>
                                    <input type="text" class="form-control-custom" name="chronic" id="edit-chronic">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Current Medications Summary</label>
                                    <textarea class="form-control-custom" name="medications" id="edit-medications" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Camp allocations -->
                        <h6 class="form-label-custom border-bottom pb-2 mb-3 text-accent"><i class="bi bi-hospital"></i> 5. Camp Allocations & Queue States</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Assigned Camp Site</label>
                                    <select class="form-control-custom" name="camp" id="edit-camp" style="height: 38px;" required>
                                        <!-- Injected by JS -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Allocated Physician Doctor</label>
                                    <input type="text" class="form-control-custom" name="doctor" id="edit-doctor" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Registration Intake Status</label>
                                    <select class="form-control-custom" name="status" id="edit-status" style="height: 38px;" required>
                                        <option value="Registered">Registered</option>
                                        <option value="Verified">Verified</option>
                                        <option value="In Triage">In Triage</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Pending ID">Pending ID</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between border-top pt-4 mt-4">
                            <button type="button" class="btn-custom btn-custom-outline" id="btn-cancel-update"><i class="bi bi-x-circle"></i> Cancel</button>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn-custom btn-custom-outline" id="btn-reset-update"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                                <button type="submit" class="btn-custom btn-custom-primary" id="btn-submit-update"><i class="bi bi-check-circle"></i> Save Changes</button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>

</main><!-- /.app-content-wrapper -->

<!-- Scoped CSS for Suggestion panel layout details -->
<style>
    .search-large-icon {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.35rem;
        pointer-events: none;
    }
    #search-suggestions.dropdown-menu {
        display: block;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border-color);
        z-index: 1000;
        background-color: var(--bg-card);
    }
    .suggestion-item {
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-sm);
        transition: var(--transition-fast);
    }
    .suggestion-item:hover {
        background-color: var(--bg-app);
    }
</style>

<!-- Load DB mocks and update patient controller scripts -->
<script src="<?php echo BASE_URL; ?>assets/js/core/database-mock.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/modules/update-patient.js"></script>

<?php
// Include structural footer scripts
include ROOT_PATH . 'includes/footer.php';
?>
