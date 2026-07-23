<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Patient Directory Module
 * 
 * Provides a database view for camp registry files, sorting keys,
 * filter options, statistical charts, and details modal triggers.
 */

// Define page parameters
$pageTitle = 'Patient List Directory';

// Include system config and root layout components
require_once dirname(dirname(__DIR__)) . '/config/config.php';
include ROOT_PATH . 'includes/header.php';
include ROOT_PATH . 'includes/sidebar.php';
include ROOT_PATH . 'includes/navbar.php';
?>

<!-- Reusable Module Patient Directory Content Area -->
<main class="app-content-wrapper">
    
    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="app-page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-custom-item"><a href="index.php">Health Worker Portal</a></li>
                    <li class="breadcrumb-custom-item active" aria-current="page">Patient List</li>
                </ol>
            </nav>
            <h1 class="page-title">Patient Records Directory</h1>
            <p class="text-secondary mb-0">Browse through the centralized SwasthyaSetu registry, review clinical logs, and export attendee profiles.</p>
        </div>
    </div>

    <!-- Dynamic Alert Container for Patient list actions -->
    <div id="alert-container-list" class="mb-3"></div>

    <!-- 2. STATISTICAL METRIC WIDGETS GRID -->
    <div class="row mb-4">
        <!-- Card 1: Total Patients -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card-custom border-primary mb-0">
                <div class="card-custom-body stat-widget py-3">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="stats-total-patients">0</span>
                        <span class="stat-widget-label">Total Registered</span>
                    </div>
                    <div class="stat-widget-icon bg-primary-subtle text-accent" style="width: 42px; height: 42px; font-size: 1.15rem;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Verified Patients -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card-custom border-success mb-0">
                <div class="card-custom-body stat-widget py-3">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="stats-verified-patients">0</span>
                        <span class="stat-widget-label">Identity Verified</span>
                    </div>
                    <div class="stat-widget-icon bg-success-subtle text-success" style="width: 42px; height: 42px; font-size: 1.15rem;">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Pending Checks -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card-custom border-warning mb-0">
                <div class="card-custom-body stat-widget py-3">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="stats-pending-patients">0</span>
                        <span class="stat-widget-label">Pending ID checks</span>
                    </div>
                    <div class="stat-widget-icon bg-warning-subtle text-warning" style="width: 42px; height: 42px; font-size: 1.15rem;">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Today's Intake registrations -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card-custom border-danger mb-0">
                <div class="card-custom-body stat-widget py-3">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="stats-today-patients">0</span>
                        <span class="stat-widget-label">Registrations Today</span>
                    </div>
                    <div class="stat-widget-icon bg-danger-subtle text-danger" style="width: 42px; height: 42px; font-size: 1.15rem;">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. DIRECTORY DATA TABLE CARD -->
    <div class="row">
        <div class="col-12">
            <div class="card-custom">
                <!-- Filters Header Section -->
                <div class="card-custom-header d-flex flex-wrap gap-3 justify-content-between align-items-center py-3">
                    
                    <!-- Search input -->
                    <div class="search-bar-custom" style="max-width: 280px;">
                        <i class="bi bi-search"></i>
                        <input type="text" id="patient-search-input" class="form-control-custom" placeholder="Search by name, ID, phone...">
                    </div>

                    <!-- Filter / Action options -->
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        
                        <!-- Camp site selection -->
                        <select class="form-control-custom py-1 px-2 text-xs" id="filter-camp" style="max-width: 180px; height: 32px; font-size: var(--font-size-xs);">
                            <!-- Injected dynamically by JS -->
                        </select>

                        <!-- Status filter selection -->
                        <select class="form-control-custom py-1 px-2 text-xs" id="filter-status" style="max-width: 140px; height: 32px; font-size: var(--font-size-xs);">
                            <option value="">All Statuses</option>
                            <option value="Registered">Registered</option>
                            <option value="Verified">Verified</option>
                            <option value="In Triage">In Triage</option>
                            <option value="Completed">Completed</option>
                            <option value="Pending ID">Pending ID</option>
                        </select>

                        <!-- Sort dropdown selector -->
                        <select class="form-control-custom py-1 px-2 text-xs" id="sort-by" style="max-width: 140px; height: 32px; font-size: var(--font-size-xs);">
                            <option value="name-asc">Name (A-Z)</option>
                            <option value="name-desc">Name (Z-A)</option>
                            <option value="id-asc">Patient ID (Oldest)</option>
                            <option value="id-desc">Patient ID (Newest)</option>
                            <option value="reg-desc">Reg Date (Newest)</option>
                        </select>

                        <!-- Export button -->
                        <button class="btn-custom btn-custom-outline btn-custom-sm py-1 px-2 me-2" id="btn-export-patients" style="height: 32px;">
                            <i class="bi bi-file-earmark-arrow-down"></i> Export
                        </button>

                        <!-- Reload button -->
                        <button class="btn-custom btn-custom-outline btn-custom-sm" id="btn-refresh-list" style="height: 32px; width: 32px; padding: 0;" title="Reload list">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="card-custom-body p-0">
                    <div id="alert-container-list"></div>
                    <div class="table-custom-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Attendee Profile</th>
                                    <th class="text-center">Age / Gender</th>
                                    <th class="text-center">Blood</th>
                                    <th>Contact Details</th>
                                    <th>Camp Location Site</th>
                                    <th>Registration Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="patient-list-table-body">
                                <!-- Injected dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Table Footer Pagination -->
                <div class="card-custom-footer py-3">
                    <div class="pagination-custom">
                        <span class="pagination-info" id="patient-list-pagination-info">Showing 0 to 0 of 0 patient records</span>
                        <nav class="pagination-nav" id="patient-list-pagination-nav">
                            <!-- Injected dynamically by JS -->
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. DIALOG MODAL LAYOUTS -->
    
    <!-- Modal A: Detailed Profile View -->
    <div class="modal-custom-backdrop" id="viewPatientModal">
        <div class="modal-custom-dialog" style="max-width: 650px;">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title"><i class="bi bi-person-lines-fill text-accent"></i> Patient Profile Records</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-custom-body" id="viewPatientModalBody">
                <!-- Injected dynamically by JS -->
            </div>
            <div class="modal-custom-footer">
                <button class="btn-custom btn-custom-outline" id="modal-btn-print-card"><i class="bi bi-printer"></i> Print thermal Card</button>
                <button class="btn-custom btn-custom-primary" data-dismiss="modal">Close Details</button>
            </div>
        </div>
    </div>

    <!-- Modal B: Deletion Confirmation -->
    <div class="modal-custom-backdrop" id="deletePatientModal">
        <div class="modal-custom-dialog">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Delete Patient</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-custom-body">
                <p class="mb-0">Are you sure you want to permanently delete this patient?</p>
            </div>
            <div class="modal-custom-footer">
                <button class="btn-custom btn-custom-outline" data-dismiss="modal">Cancel</button>
                <button class="btn-custom btn-custom-danger" id="confirmDeletePatientBtn">Delete</button>
            </div>
        </div>
    </div>

</main><!-- /.app-content-wrapper -->

<!-- Scoped table row design and Drag/Drop Zone styling -->
<style>
    .avatar-table-mini {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-full);
        object-fit: cover;
    }
    #patient-list-table-body tr {
        cursor: pointer;
    }
    #drag-drop-area {
        border: 2px dashed #cbd5e1;
        transition: all 0.2s ease-in-out;
    }
    #drag-drop-area:hover, #drag-drop-area.dragover {
        border-color: var(--primary);
        background-color: #f8fafc !important;
    }
</style>

<!-- Modal E: Import Patients Modal -->
<div class="modal-custom-backdrop" id="importPatientsModal">
    <div class="modal-custom-dialog" style="max-width: 800px;">
        <div class="modal-custom-header">
            <h5 class="modal-custom-title"><i class="bi bi-file-earmark-arrow-up text-accent"></i> Import Patients Registry</h5>
            <button class="modal-custom-close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-custom-body">
            <!-- Step 1: File Upload Form -->
            <div id="import-step-upload">
                <p class="text-secondary small">Select an Excel (.xlsx, .xls) or CSV (.csv) file to import patient records directly. The columns in the file must match the registry fields.</p>
                <div class="mb-3 p-4 rounded text-center bg-light cursor-pointer" id="drag-drop-area">
                    <i class="bi bi-cloud-upload fs-1 text-primary mb-2 d-block"></i>
                    <span class="d-block fw-semibold text-secondary">Drag & Drop file here or Click to Browse</span>
                    <input type="file" id="import-file-input" accept=".csv, .xls, .xlsx" class="d-none">
                    <span class="text-muted small mt-1 d-block" id="selected-filename">Supported files: CSV, XLS, XLSX</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn-custom btn-custom-outline btn-custom-sm" id="btn-download-template">
                        <i class="bi bi-download"></i> Download Sample Template
                    </button>
                </div>
            </div>

            <!-- Step 2: Validation and Preview -->
            <div id="import-step-preview" style="display: none;">
                <div class="alert alert-info py-2 px-3 small d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="fw-semibold">File Analysis:</span> 
                        Total rows: <span id="preview-total-count" class="fw-bold text-primary">0</span> | 
                        Valid: <span id="preview-valid-count" class="fw-bold text-success">0</span> | 
                        Invalid: <span id="preview-invalid-count" class="fw-bold text-danger">0</span>
                    </div>
                    <span class="badge bg-warning-subtle text-warning font-monospace" id="preview-status-badge" style="font-size: 10px;">Awaiting Confirmation</span>
                </div>
                <div class="table-custom-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table-custom text-xs">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 60px;">Row</th>
                                <th>Patient Name</th>
                                <th>Camp Name</th>
                                <th class="text-center">Aadhaar</th>
                                <th class="text-center">Status</th>
                                <th>Validation Result</th>
                            </tr>
                        </thead>
                        <tbody id="import-preview-table-body">
                            <!-- Injected dynamically by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Step 3: Progress and Results -->
            <div id="import-step-progress" style="display: none;">
                <div class="text-center py-4">
                    <h6 class="fw-semibold text-primary mb-2" id="import-progress-status">Importing records...</h6>
                    <div class="progress mb-3" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" id="import-progress-bar"></div>
                    </div>
                    <p class="text-muted small mb-0" id="import-progress-details">Processing row 0 of 0...</p>
                </div>
            </div>

            <!-- Step 4: Import Report Summary -->
            <div id="import-step-report" style="display: none;">
                <div class="alert alert-success d-flex align-items-center mb-3 py-2 px-3 small">
                    <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                    <div><strong class="d-block">Import Completed</strong> Valid records integrated into the database registry successfully.</div>
                </div>
                <div class="row g-2 mb-3 text-center">
                    <div class="col-4">
                        <div class="bg-light p-2 rounded border">
                            <span class="text-muted d-block small">Imported</span>
                            <strong class="fs-4 text-success" id="report-imported-count">0</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light p-2 rounded border">
                            <span class="text-muted d-block small">Skipped (Duplicate)</span>
                            <strong class="fs-4 text-warning" id="report-skipped-count">0</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-light p-2 rounded border">
                            <span class="text-muted d-block small">Failed (Error)</span>
                            <strong class="fs-4 text-danger" id="report-failed-count">0</strong>
                        </div>
                    </div>
                </div>
                <div class="table-custom-responsive" style="max-height: 200px; overflow-y: auto;">
                    <table class="table-custom text-xs">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 60px;">Row</th>
                                <th>Patient Name</th>
                                <th class="text-danger">Error Reason / Log</th>
                            </tr>
                        </thead>
                        <tbody id="import-report-errors-body">
                            <!-- Injected dynamically by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-custom-footer">
            <button class="btn-custom btn-custom-outline" id="btn-import-cancel" data-dismiss="modal">Cancel</button>
            <button class="btn-custom btn-custom-primary" id="btn-import-next" disabled>Confirm & Import</button>
        </div>
    </div>
</div>

</main><!-- /.app-content-wrapper -->

<!-- Load SheetJS for XLS/XLSX client-side decoding -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- Load active database mocks and patient list controllers -->
<script src="<?php echo BASE_URL; ?>assets/js/core/database-mock.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/modules/patient-list.js"></script>

<?php
// Include structural footer scripts
include ROOT_PATH . 'includes/footer.php';
?>
