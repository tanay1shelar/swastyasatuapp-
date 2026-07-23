<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Camp Assistance Module
 * 
 * Provides an operational dashboard for active camp coordinators. Integrates
 * physician stations, patient queue controls, medicine inventory, emergencies, and timelines.
 */

// Define page parameters
$pageTitle = 'Camp Assistance';

// Include system config and root layout components
require_once dirname(dirname(__DIR__)) . '/config/config.php';
include ROOT_PATH . 'includes/header.php';
include ROOT_PATH . 'includes/sidebar.php';
include ROOT_PATH . 'includes/navbar.php';
?>

<!-- Reusable Module Camp Assistance Content Area -->
<main class="app-content-wrapper">
    
    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="app-page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-custom-item"><a href="index.php">Health Worker Portal</a></li>
                    <li class="breadcrumb-custom-item active" aria-current="page">Camp Assistance</li>
                </ol>
            </nav>
            <h1 class="page-title">Outreach Camp Workspace</h1>
            <p class="text-secondary mb-0">Coordinate clinical pipelines, monitor medicine stock charts, triage critical emergencies, and manage consultation queues.</p>
        </div>
    </div>

    <!-- ACTIVE CAMP SELECTOR PANEL -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3 bg-light p-2 rounded border">
                <label class="form-label-custom mb-0 fw-semibold text-primary text-nowrap" style="font-size: 13px;"><i class="bi bi-hospital"></i> Active Camp Deployment:</label>
                <select class="form-control-custom py-1" id="camp-active-selector" style="max-width: 400px; height: 35px; font-size: 13px;">
                    <!-- Dynamic active camps injected by JS -->
                </select>
            </div>
        </div>
    </div>

    <!-- 2. CAMP OVERVIEW & STATUS SUMMARY (Top Row) -->
    <div class="row mb-4">
        <!-- Card 1: Today's Camp Site -->
        <div class="col-12 col-md-6 col-xl-3 mb-3 mb-xl-0">
            <div class="card-custom h-100 mb-0">
                <div class="card-custom-body py-3">
                    <span class="text-muted small uppercase d-block">Today's Camp Site</span>
                    <strong class="text-primary fs-6 d-block mt-1" id="camp-active-site-title">Loading...</strong>
                    <span class="text-secondary small mt-1 d-block" id="camp-active-location"><i class="bi bi-geo-alt-fill text-warning"></i> Loading...</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Assigned Doctor -->
        <div class="col-12 col-md-6 col-xl-3 mb-3 mb-xl-0">
            <div class="card-custom h-100 mb-0">
                <div class="card-custom-body py-3">
                    <span class="text-muted small uppercase d-block">Chief Consultant Physician</span>
                    <strong class="text-primary fs-6 d-block mt-1" id="camp-active-doctor">Loading...</strong>
                    <span class="text-secondary small mt-1 d-block" id="camp-active-doctor-dept"><i class="bi bi-person-badge-fill text-accent"></i> General Physician</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Volunteer List -->
        <div class="col-12 col-md-6 col-xl-3 mb-3 mb-xl-0">
            <div class="card-custom h-100 mb-0">
                <div class="card-custom-body py-3">
                    <span class="text-muted small uppercase d-block" id="camp-active-volunteers-title">Staff & Volunteers (3)</span>
                    <strong class="text-primary small d-block mt-1" id="camp-active-volunteer-primary">Neha Patel (Registrar)</strong>
                    <span class="text-secondary small d-block" style="font-size: 11px;" id="camp-active-volunteers-secondary">Suresh Kumar (Vitals), Vikram Singh (Triage)</span>
                </div>
            </div>
        </div>

        <!-- Card 4: Queue Status -->
        <div class="col-12 col-md-6 col-xl-3 mb-3 mb-xl-0">
            <div class="card-custom h-100 mb-0">
                <div class="card-custom-body py-3">
                    <span class="text-muted small uppercase d-block">Intake Queue Status</span>
                    <strong class="text-primary fs-6 d-block mt-1" id="camp-queue-summary">0 Active Triage</strong>
                    <span class="text-secondary small mt-1 d-block mb-2" id="camp-completed-summary"><i class="bi bi-people-fill text-success"></i> 0 Checked-in & Completed</span>
                    <div class="border-top pt-2 mt-2">
                        <div class="d-flex justify-content-between mb-1" style="font-size: 11px;">
                            <span class="text-secondary">Waiting Patients:</span>
                            <strong id="camp-stats-waiting" class="text-primary">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1" style="font-size: 11px;">
                            <span class="text-secondary">Completed Patients:</span>
                            <strong id="camp-stats-completed" class="text-success">0</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1" style="font-size: 11px;">
                            <span class="text-secondary">Emergency:</span>
                            <strong id="camp-stats-emergency" class="text-danger">0</strong>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size: 11px;">
                            <span class="text-secondary">Normal:</span>
                            <strong id="camp-stats-normal" class="text-dark">0</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. WORKSPACE GRID -->
    <div class="row">
        
        <!-- Left Columns (8 columns): Patient Queue & Medicine Inventory -->
        <div class="col-12 col-lg-8">
            
            <!-- Patient Queue Panel -->
            <div class="card-custom mb-4">
                <div class="card-custom-header d-flex justify-content-between align-items-center">
                    <h5 class="card-custom-title"><i class="bi bi-people-fill text-accent"></i> Consultation Intake Queue</h5>
                    <span class="badge bg-warning-subtle text-warning px-3 py-1 small rounded-pill fw-semibold" id="queue-count-badge">14 patients waiting</span>
                </div>
                <div class="card-custom-body p-0">
                    <div class="table-custom-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Token</th>
                                    <th>Patient Details</th>
                                    <th class="text-center">Priority</th>
                                    <th class="text-center">Intake Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="camp-patient-queue-table">
                                <!-- Injected dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Table Footer Pagination -->
                <div class="card-custom-footer py-2 border-top bg-light">
                    <div class="pagination-custom d-flex justify-content-between align-items-center px-3" style="min-height: 40px;">
                        <span class="pagination-info text-secondary" id="queue-pagination-info" style="font-size: 11px; font-weight: 500;">Showing 0 to 0 of 0 patients waiting</span>
                        <nav class="pagination-nav d-flex gap-1" id="queue-pagination-nav">
                            <!-- Injected dynamically by JS -->
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Medicine Inventory Panel -->
            <div class="card-custom mb-4">
                <div class="card-custom-header d-flex justify-content-between align-items-center">
                    <h5 class="card-custom-title"><i class="bi bi-capsules text-accent"></i> Camp Medicine Stock Inventory</h5>
                    <button class="btn-custom btn-custom-outline btn-custom-sm" id="btn-restock-all"><i class="bi bi-arrow-down-up"></i> Restock Low Items</button>
                </div>
                <div class="card-custom-body p-0">
                    <div class="table-custom-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Medicine Name</th>
                                    <th>Therapeutic Class</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="camp-medicine-inventory-table">
                                <!-- Injected dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Columns (4 columns): Emergency Alerts, Timeline, Insights -->
        <div class="col-12 col-lg-4">
            
            <!-- Emergency Cases Alerts -->
            <div class="card-custom mb-4 border-danger">
                <div class="card-custom-header bg-danger-subtle border-danger">
                    <h5 class="card-custom-title text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Emergency Triage Dispatch</h5>
                </div>
                <div class="card-custom-body p-3" id="camp-emergency-list">
                    <!-- Injected dynamically by JS -->
                </div>
            </div>

            <!-- Timeline of Today's camp events -->
            <div class="card-custom mb-4">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-calendar3-event text-accent"></i> Today's Deployment Timeline</h5>
                </div>
                <div class="card-custom-body p-3">
                    <ul class="camp-timeline ps-3 mb-0 small text-secondary">
                        <li class="timeline-event mb-3">
                            <span class="d-block fw-bold text-primary">09:00 AM — Outreach Unit Setup</span>
                            <span>Tents erected, power rigs calibrated, and pharmaceutical cold boxes plugged in.</span>
                        </li>
                        <li class="timeline-event mb-3">
                            <span class="d-block fw-bold text-primary">09:30 AM — Registrations Opened</span>
                            <span>Aadhaar checks initialized; token check-ins dispatched to vitals room.</span>
                        </li>
                        <li class="timeline-event mb-3">
                            <span class="d-block fw-bold text-primary">11:30 AM — Supply Delivery Audits</span>
                            <span>Paracetamol 650mg and insulin cold reserves restocked by Apollo logistics truck.</span>
                        </li>
                        <li class="timeline-event">
                            <span class="d-block fw-bold text-primary">02:00 PM — Mid-Shift Briefings</span>
                            <span>Volunteer rotations synchronized. Average triage wait times logged at 12 minutes.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Camp Summary / Insights -->
            <div class="card-custom mb-4">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-bar-chart-fill text-accent"></i> Shift Summary Insights</h5>
                </div>
                <div class="card-custom-body p-3">
                    <div class="mb-3">
                        <span class="d-block text-secondary small mb-1">Clinic Capacity Utilization</span>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="camp-fill-rate-bar"></div>
                        </div>
                        <span class="text-muted d-block mt-1" style="font-size: 10px;" id="camp-fill-rate-summary">0% Fill Rate (Expected: 200 patients, Current: 0)</span>
                    </div>
                    <div class="mb-3">
                        <span class="d-block text-secondary small mb-1">Identity Compliance Score</span>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 94%;" aria-valuenow="94" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="text-muted d-block mt-1" style="font-size: 10px;">94.8% Verified compliant logs (Aadhaar/OTP verified)</span>
                    </div>
                    <div>
                        <span class="d-block text-secondary small">Consultation Averages</span>
                        <strong class="text-primary fs-5">12 mins</strong>
                        <span class="text-muted d-block small" style="font-size: 10px;">Average patient triage wait time in queue station.</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</main><!-- /.app-content-wrapper -->

<!-- Custom inline style adjustments for visual timeline pointers -->
<style>
    .camp-timeline {
        list-style-type: none;
        position: relative;
        border-left: 2px solid var(--border-color);
        padding-left: 1.5rem !important;
    }
    .timeline-event {
        position: relative;
    }
    .timeline-event::before {
        content: "";
        position: absolute;
        width: 12px;
        height: 12px;
        border-radius: var(--radius-full);
        background-color: var(--accent);
        left: -31px;
        top: 4px;
        border: 2px solid var(--bg-card);
    }
</style>

<!-- Load active database mocks and camp assistance controllers -->
<script src="<?php echo BASE_URL; ?>assets/js/core/database-mock.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/modules/camp-assistance.js"></script>

<?php
// Include structural footer scripts
include ROOT_PATH . 'includes/footer.php';
?>
