<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Patient Registration Module
 * 
 * Provides form inputs for patient personal details, residential address,
 * medical histories, uploads (photo/identity card), and generates auto-token numbers.
 */

// Define page parameters
$pageTitle = 'Patient Registration';

// Include system config and root layout components
require_once dirname(dirname(__DIR__)) . '/config/config.php';
include ROOT_PATH . 'includes/header.php';
include ROOT_PATH . 'includes/sidebar.php';
include ROOT_PATH . 'includes/navbar.php';
?>

<!-- Reusable Module Registration Content Area -->
<main class="app-content-wrapper">
    
    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="app-page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-custom-item"><a href="index.php">Health Worker Portal</a></li>
                    <li class="breadcrumb-custom-item active" aria-current="page">Patient Registration</li>
                </ol>
            </nav>
            <h1 class="page-title">Patient Intake & Registration</h1>
            <p class="text-secondary mb-0">Create new patient demographic records, assign queue numbers, and log initial vitals.</p>
        </div>
    </div>

    <!-- 2. REGISTRATION FORM CONTAINER -->
    <div class="row">
        <div class="col-12 col-xl-9">
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-person-plus-fill text-accent"></i> Patient Intake Registry Form</h5>
                </div>
                <div class="card-custom-body p-4">
                    
                    <form class="needs-validation" id="patient-registration-form" novalidate>
                        
                        <!-- Auto Assigned Identifiers Box -->
                        <div class="alert alert-info border-0 bg-light py-3 px-4 rounded mb-4 d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-info-circle-fill text-accent me-2"></i>
                                <span>Intake IDs are automatically allocated by the active session database:</span>
                            </div>
                            <div class="d-flex gap-3">
                                <div>
                                    <span class="text-muted small uppercase d-block text-end">Patient ID</span>
                                    <strong class="text-primary font-monospace" id="auto-patient-id">PAT-101</strong>
                                </div>
                                <div>
                                    <span class="text-muted small uppercase d-block text-end">Active Token</span>
                                    <strong class="text-success font-monospace" id="auto-token-id">#201</strong>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION A: Patient Personal Details -->
                        <h6 class="form-label-custom border-bottom pb-2 mb-3 text-accent"><i class="bi bi-person-vcard"></i> 1. Personal Demographics</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Full Name (as in Aadhaar)</label>
                                    <input type="text" class="form-control-custom" name="name" placeholder="e.g. Rajesh Kumar" required>
                                    <div class="invalid-feedback">Full name is required.</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Contact Phone</label>
                                    <input type="tel" class="form-control-custom" name="phone" placeholder="e.g. +91 98765 43210" pattern="^\+91 [0-9]{5}\s[0-9]{5}$" required>
                                    <div class="invalid-feedback">Format required: +91 XXXXX XXXXX</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Aadhaar Card Number</label>
                                    <input type="text" class="form-control-custom" name="aadhaar" placeholder="e.g. 1234 5678 9012" pattern="^[0-9]{12}$|^[0-9]{4}\s[0-9]{4}\s[0-9]{4}$" required>
                                    <div class="invalid-feedback">Aadhaar must be a 12-digit number.</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Date of Birth</label>
                                    <input type="date" class="form-control-custom" name="dob" required>
                                    <div class="invalid-feedback">Date of birth is required.</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Gender Identification</label>
                                    <select class="form-control-custom" name="gender" style="height: 38px;" required>
                                        <option value="" selected disabled>Select...</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a gender.</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Blood Group</label>
                                    <select class="form-control-custom" name="blood" style="height: 38px;" required>
                                        <option value="" selected disabled>Select...</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                    <div class="invalid-feedback">Please select blood group.</div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION B: Address details -->
                        <h6 class="form-label-custom border-bottom pb-2 mb-3 text-accent"><i class="bi bi-geo-alt"></i> 2. Residential Address</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Street Name & House Details</label>
                                    <input type="text" class="form-control-custom" name="address" placeholder="e.g. House No. 45, Village Gahlot" required>
                                    <div class="invalid-feedback">Address field cannot be empty.</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">City / Town</label>
                                    <input type="text" class="form-control-custom" name="city" placeholder="e.g. Palwal" required>
                                    <div class="invalid-feedback">City is required.</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">State</label>
                                    <input type="text" class="form-control-custom" name="state" placeholder="e.g. Haryana" required>
                                    <div class="invalid-feedback">State is required.</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Postal Pincode</label>
                                    <input type="text" class="form-control-custom" name="pincode" placeholder="e.g. 121102" pattern="^[0-9]{6}$" required>
                                    <div class="invalid-feedback">Pincode must be 6 digits.</div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION C: Medical Information -->
                        <h6 class="form-label-custom border-bottom pb-2 mb-3 text-accent"><i class="bi bi-heart-pulse"></i> 3. Clinical & Medical Summary</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Known Drug/Food Allergies</label>
                                    <input type="text" class="form-control-custom" name="allergies" placeholder="e.g. Penicillin, Peanuts (or 'None')">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Chronic Illnesses / Conditions</label>
                                    <input type="text" class="form-control-custom" name="chronic" placeholder="e.g. Type II Diabetes, Hypertension (or 'None')">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Current Medications Summary</label>
                                    <textarea class="form-control-custom" name="medications" rows="2" placeholder="List dosage and item names if currently undergoing medical treatment..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION D: Emergency Contact -->
                        <h6 class="form-label-custom border-bottom pb-2 mb-3 text-accent"><i class="bi bi-telephone-outbound"></i> 4. Emergency Contact details</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Contact Person Name</label>
                                    <input type="text" class="form-control-custom" name="emergencyName" placeholder="e.g. Amit Kumar" required>
                                    <div class="invalid-feedback">Contact person name is required.</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Relationship to Patient</label>
                                    <input type="text" class="form-control-custom" name="emergencyRelation" placeholder="e.g. Brother" required>
                                    <div class="invalid-feedback">Relationship details are required.</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Contact Phone</label>
                                    <input type="tel" class="form-control-custom" name="emergencyPhone" placeholder="e.g. +91 99887 76655" pattern="^\+91 [0-9]{5}\s[0-9]{5}$" required>
                                    <div class="invalid-feedback">Format required: +91 XXXXX XXXXX</div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION E: Camp Allocation & File Uploads -->
                        <h6 class="form-label-custom border-bottom pb-2 mb-3 text-accent"><i class="bi bi-hospital"></i> 5. Camp Details & Photo Attachments</h6>
                        <div class="row g-3 mb-4">
                            <!-- Camp dropdown -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Target Medical Camp Site</label>
                                    <select class="form-control-custom" name="camp" id="registration-camp-select" style="height: 38px;" required>
                                        <!-- Injected dynamically by JS -->
                                    </select>
                                    <div class="invalid-feedback">Select a medical camp.</div>
                                </div>
                            </div>
                            
                            <!-- Doctor station -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Allocated General Physician Room</label>
                                    <input type="text" class="form-control-custom" name="assignedStaff" id="registration-assigned-staff" value="Dr. Rajesh Verma" readonly>
                                </div>
                            </div>

                            <!-- Photo upload preview -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Patient Portrait Photograph</label>
                                    <input type="file" class="form-control-custom" id="photo-upload-input" accept="image/*" style="height: 38px;">
                                    <div class="mt-2 text-center p-3 border rounded bg-light" style="max-height: 180px; position: relative; overflow: hidden;">
                                        <img id="photo-preview-img" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100%25' height='100%25' viewBox='0 0 100 100' preserveAspectRatio='none'%3E%3Crect width='100' height='100' fill='%23f1f5f9'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='8' fill='%2394a3b8'%3EPhotograph Preview%3C/text%3E%3C/svg%3E" alt="Portrait Preview" style="max-height: 140px; border-radius: var(--radius-sm);">
                                    </div>
                                </div>
                            </div>

                            <!-- Document upload preview -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Identity Document (Aadhaar / Ration Card)</label>
                                    <input type="file" class="form-control-custom" id="doc-upload-input" accept="image/*,application/pdf" style="height: 38px;">
                                    <div class="mt-2 text-center p-3 border rounded bg-light" style="max-height: 180px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                        <div id="doc-preview-container" class="text-muted small">
                                            <i class="bi bi-file-earmark-medical fs-1 d-block mb-1 text-muted"></i>
                                            <span>Document Preview</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between border-top pt-4 mt-4">
                            <button type="button" class="btn-custom btn-custom-outline" id="btn-reset-registration"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn-custom btn-custom-outline" id="btn-draft-registration"><i class="bi bi-journal-bookmark"></i> Save Draft</button>
                                <button type="submit" class="btn-custom btn-custom-primary" id="btn-submit-registration"><i class="bi bi-check-circle"></i> Register Patient</button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
        
        <!-- Right side draft information widgets (3 columns) -->
        <div class="col-12 col-xl-3">
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-journal-bookmark-fill text-accent"></i> Saved Intake Drafts</h5>
                </div>
                <div class="card-custom-body p-3 text-center" id="registration-drafts-list">
                    <!-- Dynamic drafts loaded by JS -->
                    <div class="py-4 text-muted">
                        <i class="bi bi-journal-medical fs-2 mb-2 d-block text-muted"></i>
                        <span class="small text-secondary">No drafts saved in this session.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main><!-- /.app-content-wrapper -->

<!-- Load active database mocks and registration script handlers -->
<script src="<?php echo BASE_URL; ?>assets/js/core/database-mock.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/modules/patient-registration.js"></script>

<?php
// Include structural footer scripts
include ROOT_PATH . 'includes/footer.php';
?>
