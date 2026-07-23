<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Health Worker Dashboard (Root Level Console)
 * 
 * Provides an operational dashboard for health staff tracking live registration,
 * verification logs, patient activities, and camp status alerts.
 */

// Define page parameters
$pageTitle = 'Medical Admin Dashboard';

// Include system config and root layout components
require_once dirname(dirname(__DIR__)) . '/config/config.php';
include ROOT_PATH . 'includes/header.php';
include ROOT_PATH . 'includes/sidebar.php';
include ROOT_PATH . 'includes/navbar.php';

// Fetch live statistics from MySQL database
$stats = db_load_dashboard_statistics();
$verPct = $stats['total_patients'] > 0 ? round(($stats['verified_patients'] / $stats['total_patients']) * 100, 1) : 0;
?>

<!-- Main Dashboard Content Wrapper -->
<main class="app-content-wrapper">
    
    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="app-page-header">
        <div>
            <!-- Reusable Breadcrumb Component -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-custom-item"><a href="index.php">Health Worker Portal</a></li>
                    <li class="breadcrumb-custom-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
            <h1 class="page-title">Health Worker Control Center</h1>
            <p class="text-secondary mb-0">Overview of operational clinics, mobile vaccine units, and patient registers.</p>
        </div>
        
        <!-- Reusable Header Buttons Component -->
        <div>
            <button class="btn-custom btn-custom-primary" onclick="showModal('newCampModal')">
                <i class="bi bi-hospital"></i> View Active Camps
            </button>
        </div>
    </div>

    <!-- 2. STATS WIDGETS CARDS GRID -->
    <div class="row mb-5">
        <!-- Widget 1: Registered Patients -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0" id="widget-total-patients" style="cursor: pointer;">
            <div class="card-custom card-custom-accent h-100 mb-0 transition-transform">
                <div class="card-custom-body stat-widget">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="dashboard-total-patients"><?php echo number_format($stats['total_patients']); ?></span>
                        <span class="stat-widget-label">Total Patients</span>
                        <span class="stat-widget-trend text-success" id="dashboard-total-trend">
                            <i class="bi bi-arrow-up-right"></i> +12% this week
                        </span>
                    </div>
                    <div class="stat-widget-icon bg-primary-subtle text-accent">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 2: Verification Status -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0" id="widget-identity-verified" style="cursor: pointer;">
            <div class="card-custom card-custom-success h-100 mb-0 transition-transform">
                <div class="card-custom-body stat-widget">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="dashboard-verified-patients"><?php echo $verPct; ?>%</span>
                        <span class="stat-widget-label">Identity Verified</span>
                        <span class="stat-widget-trend text-success" id="dashboard-verified-trend">
                            <i class="bi bi-check-all"></i> Aadhaar / OTP compliant
                        </span>
                    </div>
                    <div class="stat-widget-icon bg-success-subtle text-success">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 3: Active Camps -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0" id="widget-active-camps" style="cursor: pointer;">
            <div class="card-custom card-custom-warning h-100 mb-0 transition-transform">
                <div class="card-custom-body stat-widget">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="dashboard-active-camps"><?php echo str_pad($stats['active_camps'], 2, '0', STR_PAD_LEFT); ?></span>
                        <span class="stat-widget-label">Active Camps</span>
                        <span class="stat-widget-trend text-secondary" id="dashboard-camps-trend">
                            <i class="bi bi-geo-alt-fill text-warning"></i> Palwal, Alwar, Nuh
                        </span>
                    </div>
                    <div class="stat-widget-icon bg-warning-subtle text-warning">
                        <i class="bi bi-hospital"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget 4: Todays Camp Check-ins -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0" id="widget-checkins-today" style="cursor: pointer;">
            <div class="card-custom card-custom-danger h-100 mb-0 transition-transform">
                <div class="card-custom-body stat-widget">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="dashboard-checkins-today"><?php echo number_format($stats['checkins_today']); ?></span>
                        <span class="stat-widget-label">Check-ins Today</span>
                        <span class="stat-widget-trend text-danger" id="dashboard-checkins-trend">
                            <i class="bi bi-activity"></i> Active queue: <?php echo $stats['waiting_queue']; ?> triage
                        </span>
                    </div>
                    <div class="stat-widget-icon bg-danger-subtle text-danger">
                        <i class="bi bi-clipboard2-heart-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. DETAILED OPERATIONAL LAYOUT -->
    <div class="row">
        
        <!-- Left Panel: Table & Registration Placeholder (7 Columns) -->
        <div class="col-12 col-lg-7">
            
            <!-- Table Component -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="card-custom-title">Operational Mobile Medical Camps</h5>
                    <!-- Table Search Bar Component -->
                    <div class="search-bar-custom">
                        <i class="bi bi-search"></i>
                        <input type="text" id="camp-search" class="form-control-custom" placeholder="Search locations...">
                    </div>
                </div>
                
                <div class="card-custom-body p-0">
                    <div class="table-custom-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Camp Name & Region</th>
                                    <th>Coordinator</th>
                                    <th>Date Scheduled</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="camp-table-body">
                                <!-- JS Injection of Dynamic Medical Camps -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Reusable Pagination Layout Component -->
                <div class="card-custom-footer py-3">
                    <div class="pagination-custom">
                        <span class="pagination-info">Showing 1 to 3 of 8 active medical camps</span>
                        <nav class="pagination-nav">
                            <button class="pagination-btn disabled" aria-disabled="true"><i class="bi bi-chevron-left"></i></button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn"><i class="bi bi-chevron-right"></i></button>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Replaced patient registration form with placeholder card -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="card-custom-title">Patient Registration</h5>
                </div>
                <div class="card-custom-body text-center py-5">
                    <div class="text-muted fs-1 mb-3"><i class="bi bi-person-plus"></i></div>
                    <p class="mb-0 fw-medium text-secondary">Module Ready for Development</p>
                </div>
            </div>

        </div>

        <!-- Right Panel: Playgrounds & Vitals Placeholder (5 Columns) -->
        <div class="col-12 col-lg-5">
            
            <!-- Replaced UX playground with placeholder card -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="card-custom-title">System Tools & Playgrounds</h5>
                </div>
                <div class="card-custom-body text-center py-5">
                    <div class="text-muted fs-1 mb-3"><i class="bi bi-tools"></i></div>
                    <p class="mb-0 fw-medium text-secondary">Module Ready for Development</p>
                </div>
            </div>

            <!-- Replaced Empty State Triage check-ins with placeholder card -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="card-custom-title">Patient Triage & Vitals</h5>
                </div>
                <div class="card-custom-body text-center py-5">
                    <div class="text-muted fs-1 mb-3"><i class="bi bi-heart-pulse"></i></div>
                    <p class="mb-0 fw-medium text-secondary">Module Ready for Development</p>
                </div>
            </div>

        </div>

    </div>

    <!-- =========================================================================
       REUSABLE DIALOG MODALS LAYOUTS
       ========================================================================= -->

    <!-- Modal 1: View Active Camps -->
    <div class="modal-custom-backdrop" id="newCampModal">
        <div class="modal-custom-dialog" style="max-width: 550px;">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title"><i class="bi bi-hospital"></i> Active & Assigned Camps</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-custom-body">
                <div class="list-group">
                    <div class="p-3 mb-2 rounded border bg-light">
                        <strong class="text-primary d-block">Apollo Rural Health Camp - Phase 1</strong>
                        <span class="text-secondary small d-block"><i class="bi bi-geo-alt-fill text-warning"></i> Community Health Centre, Palwal, Haryana</span>
                        <span class="badge bg-success-subtle text-success mt-2">Active</span>
                    </div>
                    <div class="p-3 mb-2 rounded border bg-light">
                        <strong class="text-primary d-block">Rotary Pediatric Outreach Camp</strong>
                        <span class="text-secondary small d-block"><i class="bi bi-geo-alt-fill text-warning"></i> Panchayat Bhawan, Palwal, Haryana</span>
                        <span class="badge bg-success-subtle text-success mt-2">Active</span>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn-custom btn-custom-outline" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: System Architecture Info -->
    <div class="modal-custom-backdrop" id="demoInfoModal">
        <div class="modal-custom-dialog" style="max-width: 650px;">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title">SwasthyaSetu Architecture Foundation Guide</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-custom-body">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-check text-success fs-1"></i>
                    <h6 class="mt-2 fw-semibold">Enterprise Hospital Foundation Completed</h6>
                </div>
                
                <h6 class="fw-semibold text-primary">System Assets Structure:</h6>
                <ul class="small text-secondary ps-3 mb-4">
                    <li><strong>Layout Includes:</strong> Modular header, sticky top navbar, responsive sidebar and copyright footer.</li>
                    <li><strong>Database Schema:</strong> Fully indexed schema file incorporating `camps`, `patients`, `verifications`, and `attendance`.</li>
                    <li><strong>Styling Library:</strong> Design tokens using custom CSS variables (Colors, typography, radii, spacing grid).</li>
                    <li><strong>Clean Javascript:</strong> Vanilla script files split by core capabilities avoiding chaotic inline execution.</li>
                </ul>

                <div class="alert alert-info py-2 px-3 small border-0 bg-light-subtle text-primary-emphasis mb-0">
                    <i class="bi bi-info-circle-fill me-1"></i> This foundation handles layouts, transitions and variables. Modules (Dashboard statistics, Patient Registers, Verification check-ins) are fully prepared for database integration.
                </div>
            </div>
            <div class="modal-custom-footer">
                <button class="btn-custom btn-custom-primary" data-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>

    <!-- Reusable Dialog Modals for Camp CRUD simulation -->
    
    <!-- Modal 3: View Camp Details -->
    <div class="modal-custom-backdrop" id="viewCampModal">
        <div class="modal-custom-dialog" style="max-width: 600px;">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title"><i class="bi bi-geo-alt-fill text-accent"></i> Medical Camp Details</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-custom-body" id="viewCampModalBody">
                <!-- Injected dynamically by JavaScript -->
            </div>
            <div class="modal-custom-footer">
                <button class="btn-custom btn-custom-outline" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>

    <!-- Modal 4: Edit Camp Details -->
    <div class="modal-custom-backdrop" id="editCampModal">
        <div class="modal-custom-dialog" style="max-width: 600px;">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title"><i class="bi bi-pencil-square text-accent"></i> Edit Medical Camp Details</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <form id="editCampForm">
                <div class="modal-custom-body" id="editCampModalBody">
                    <!-- Injected dynamically by JavaScript -->
                </div>
                <div class="modal-custom-footer">
                    <button type="button" class="btn-custom btn-custom-outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-custom btn-custom-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 5: Delete Camp Confirmation -->
    <div class="modal-custom-backdrop" id="deleteCampModal">
        <div class="modal-custom-dialog">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Confirm Camp Deletion</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-custom-body">
                <p>Are you sure you want to cancel and delete the medical camp <strong id="deleteCampName" class="text-primary"></strong>?</p>
                <p class="text-danger small mb-0"><i class="bi bi-info-circle-fill"></i> This action is irreversible for this active session.</p>
            </div>
            <div class="modal-custom-footer">
                <button class="btn-custom btn-custom-outline" data-dismiss="modal">Cancel</button>
                <button class="btn-custom btn-custom-danger" id="confirmDeleteCampBtn">Delete Camp</button>
            </div>
        </div>
    </div>

</main><!-- /.app-content-wrapper -->

<!-- Add page-specific styles for click transitions -->
<style>
    .transition-transform {
        transition: var(--transition-normal);
    }
    .transition-transform:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }
    .transition-transform:active {
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }
    
    /* Clickable table row indicator */
    #camp-table-body tr {
        cursor: pointer;
    }
</style>

<!-- Load mock data store and dashboard interaction controllers -->
<script src="<?php echo BASE_URL; ?>assets/js/core/database-mock.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/modules/dashboard.js"></script>

<?php
// Include structural footer scripts
include ROOT_PATH . 'includes/footer.php';
?>
