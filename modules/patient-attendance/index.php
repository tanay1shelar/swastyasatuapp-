<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Patient Attendance Check-in Module
 * 
 * Manages daily camp check-ins using QR scanners, manual token entries,
 * triage status classifications, and checkout audits.
 */

// Define page parameters
$pageTitle = 'Patient Attendance';

// Include system config and root layout components
require_once dirname(dirname(__DIR__)) . '/config/config.php';
include ROOT_PATH . 'includes/header.php';
include ROOT_PATH . 'includes/sidebar.php';
include ROOT_PATH . 'includes/navbar.php';
?>

<!-- Reusable Module Attendance Content Area -->
<main class="app-content-wrapper">
    
    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="app-page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-custom-item"><a href="index.php">Health Worker Portal</a></li>
                    <li class="breadcrumb-custom-item active" aria-current="page">Attendance</li>
                </ol>
            </nav>
            <h1 class="page-title">Triage Attendance Check-in</h1>
            <p class="text-secondary mb-0">Record camp check-ins, scan barcode tokens, log check-in timestamps, and assign priority triage levels.</p>
        </div>
    </div>

    <!-- 2. CHECK-IN CONSOLE GRID -->
    <div class="row">
        
        <!-- Left Column: Check-in Scanner Console (4 columns) -->
        <div class="col-12 col-lg-4 mb-4">
            <div class="card-custom h-100">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-qr-code-scan text-accent"></i> Check-in Intake Console</h5>
                </div>
                <div class="card-custom-body p-4">
                    
                    <!-- QR Barcode Scanner Simulation (Disabled/Hidden) -->
                    <div style="display: none;">
                        <label class="form-label-custom">QR Badge Scanner Simulator</label>
                        <div class="border rounded p-3 text-center bg-light mb-4" style="position: relative; overflow: hidden;">
                            <div id="qr-camera-placeholder">
                                <i class="bi bi-camera fs-1 text-muted d-block mb-1"></i>
                                <span class="small d-block text-secondary">Intelligent USB Badge Scanner</span>
                                <button type="button" class="btn-custom btn-custom-sm btn-custom-outline mt-2" id="btn-trigger-qr"><i class="bi bi-play-fill"></i> Simulate QR Scan</button>
                            </div>
                            <div id="qr-camera-scanning" style="display: none;">
                                <div class="spinner-border text-primary mb-2" style="width: 32px; height: 32px;" role="status"></div>
                                <span class="d-block small text-accent animate-pulse">Reading barcode symbols...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Input Form -->
                    <form id="attendance-entry-form" novalidate>
                        
                        <!-- Field 1: Patient ID / Token -->
                        <div class="form-group-custom mb-3">
                            <label class="form-label-custom">Patient ID, Token, Name, or Phone</label>
                            <div class="input-group">
                                <input type="text" class="form-control-custom" id="attendance-patient-id" placeholder="Enter ID, Name, or Phone..." required>
                                <button type="button" class="btn-custom btn-custom-outline btn-custom-sm" id="btn-fetch-patient" style="height: 38px;"><i class="bi bi-search"></i> Check</button>
                            </div>
                            
                            <!-- Search Result Pane -->
                            <div class="mt-3 p-3 border rounded bg-light" id="attendance-patient-name-display" style="display: none;">
                                <div class="mb-1 small"><strong>Patient Name:</strong> <span id="scanned-patient-name" class="text-primary fw-semibold"></span></div>
                                <div class="mb-1 small"><strong>Assigned Camp:</strong> <span id="scanned-patient-camp" class="text-secondary fw-semibold"></span></div>
                                <div class="mb-1 small"><strong>Verification Status:</strong> <span id="scanned-patient-status"></span></div>
                                <div class="small"><strong>Previous Attendance:</strong> <span id="scanned-patient-prev-attendance" class="text-muted"></span></div>
                            </div>
                            
                            <div class="mt-2 text-danger small" id="attendance-patient-not-found" style="display: none;">
                                <i class="bi bi-exclamation-circle-fill"></i> Patient Not Found
                            </div>
                        </div>

                        <!-- Field 2: Status -->
                        <div class="form-group-custom mb-3">
                            <label class="form-label-custom">Attendance Checked State</label>
                            <select class="form-control-custom" id="attendance-status-select" style="height: 38px;" required>
                                <option value="Present" selected>Present</option>
                                <option value="Late">Late</option>
                                <option value="Absent">Absent</option>
                            </select>
                        </div>

                        <!-- Field 3: Triage Priority -->
                        <div class="form-group-custom mb-3">
                            <label class="form-label-custom">Triage Priority Allocation</label>
                            <select class="form-control-custom" id="attendance-priority-select" style="height: 38px;" required>
                                <option value="Low" selected>Low Priority (Routine check-up)</option>
                                <option value="Medium">Medium Priority (Vitals audit)</option>
                                <option value="High">High Priority (Urgent consultation)</option>
                                <option value="Emergency">Emergency (Immediate attention)</option>
                            </select>
                        </div>

                        <!-- Field 4 & 5: Check-in & Check-out Timestamps -->
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label-custom">Check-in Time</label>
                                <input type="text" class="form-control-custom" id="attendance-checkin-time">
                            </div>
                            <div class="col-6">
                                <label class="form-label-custom">Check-out Time</label>
                                <input type="text" class="form-control-custom" id="attendance-checkout-time" value="--">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-custom btn-custom-primary w-100" id="btn-mark-attendance">
                            <i class="bi bi-calendar2-plus"></i> Mark Camp Attendance
                        </button>

                    </form>

                </div>
            </div>
        </div>

        <!-- Right Column: Today's Attendance logs (8 columns) -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card-custom h-100">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-clipboard-check text-accent"></i> Today's Attendance Roster</h5>
                    <!-- Table Search -->
                    <div class="search-bar-custom">
                        <i class="bi bi-search"></i>
                        <input type="text" id="attendance-search" class="form-control-custom" placeholder="Search check-ins...">
                    </div>
                </div>
                <div class="card-custom-body p-0">
                    <div class="table-custom-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Patient ID & Name</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th class="text-center">Triage</th>
                                    <th class="text-center">Checked Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-table-body">
                                <!-- Injected dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

</main><!-- /.app-content-wrapper -->

<!-- Load active database mocks and attendance script handlers -->
<script src="<?php echo BASE_URL; ?>assets/js/core/database-mock.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/modules/patient-attendance.js"></script>

<?php
// Include structural footer scripts
include ROOT_PATH . 'includes/footer.php';
?>
