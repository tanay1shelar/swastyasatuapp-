<?php 
require_once '../../includes/db.php';
include '../../includes/header.php'; 

// Fetch Dashboard Metrics
$stmtCenters = $pdo->query("SELECT COUNT(*) FROM centers");
$totalCenters = $stmtCenters->fetchColumn();

$stmtCamps = $pdo->query("SELECT COUNT(*) FROM camps WHERE status IN ('Scheduled', 'Ongoing')");
$activeCamps = $stmtCamps->fetchColumn();

// Dummy values for modules not yet built
$totalReports = 156;
$systemAlerts = 3;

// Fetch Recent Activity
$stmtLogs = $pdo->query("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 5");
$activities = $stmtLogs->fetchAll();
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard Overview</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>
    </div>
    <div class="text-end">
        <p class="text-muted small mb-0">Welcome back, Super Admin!</p>
        <p class="fw-medium" style="color: var(--primary-color);">System Status: <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1">All Systems Operational</span></p>
    </div>
</div>

<!-- Key Metrics Row -->
<div class="row g-4 mb-4">
    <!-- Healthcare Centers Widget -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm" style="background-color: var(--bg-card);">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted fw-semibold mb-1">Healthcare Centers</h6>
                        <h3 class="fw-bold mb-0" style="color: var(--primary-color);"><?= htmlspecialchars($totalCenters) ?></h3>
                    </div>
                    <div class="p-2 rounded" style="background-color: rgba(255, 127, 80, 0.15); color: var(--accent-color);">
                        <i class="fa-solid fa-building-user fa-lg"></i>
                    </div>
                </div>
                <div class="mt-auto">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"><i class="fa-solid fa-arrow-trend-up me-1"></i> +3 this month</span>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 p-0">
                <a href="../centers/index.php" class="btn w-100 py-3 fw-medium" style="background-color: var(--primary-color); color: var(--sidebar-text); border-radius: 0 0 var(--border-radius) var(--border-radius);">
                    Manage Centers <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Camp Monitoring Widget -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm" style="background-color: var(--bg-card);">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted fw-semibold mb-1">Active Camps</h6>
                        <h3 class="fw-bold mb-0" style="color: var(--primary-color);"><?= htmlspecialchars($activeCamps) ?></h3>
                    </div>
                    <div class="p-2 rounded" style="background-color: rgba(16, 185, 129, 0.15); color: var(--secondary-color);">
                        <i class="fa-solid fa-truck-medical fa-lg"></i>
                    </div>
                </div>
                <div class="mt-auto">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"><i class="fa-solid fa-spinner fa-spin me-1"></i> Ongoing live</span>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 p-0">
                <a href="../monitoring/index.php" class="btn w-100 py-3 fw-medium" style="background-color: var(--primary-color); color: var(--sidebar-text); border-radius: 0 0 var(--border-radius) var(--border-radius);">
                    Monitor Camps <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Reports Widget -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm" style="background-color: var(--bg-card);">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted fw-semibold mb-1">Generated Reports</h6>
                        <h3 class="fw-bold mb-0" style="color: var(--primary-color);"><?= htmlspecialchars($totalReports) ?></h3>
                    </div>
                    <div class="p-2 rounded" style="background-color: rgba(255, 127, 80, 0.15); color: var(--accent-color);">
                        <i class="fa-solid fa-chart-pie fa-lg"></i>
                    </div>
                </div>
                <div class="mt-auto">
                    <span class="text-muted small fw-medium">Last generated 2 hours ago</span>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 p-0">
                <a href="../reports/index.php" class="btn w-100 py-3 fw-medium" style="background-color: var(--primary-color); color: var(--sidebar-text); border-radius: 0 0 var(--border-radius) var(--border-radius);">
                    View Analytics <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts Widget -->
    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm" style="background-color: var(--bg-card);">
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted fw-semibold mb-1">System Alerts</h6>
                        <h3 class="fw-bold mb-0 text-danger"><?= htmlspecialchars($systemAlerts) ?></h3>
                    </div>
                    <div class="p-2 rounded bg-danger bg-opacity-10 text-danger">
                        <i class="fa-solid fa-bell fa-lg"></i>
                    </div>
                </div>
                <div class="mt-auto">
                    <span class="text-danger small fw-medium"><i class="fa-solid fa-circle-exclamation me-1"></i> Action required</span>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 p-0">
                <a href="../settings/index.php" class="btn w-100 py-3 fw-medium" style="background-color: var(--primary-color); color: var(--sidebar-text); border-radius: 0 0 var(--border-radius) var(--border-radius);">
                    Manage Settings <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Management Row -->
<div class="row g-4">
    <!-- Quick Actions Panel -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom pt-4 pb-3 px-4">
                <h5 class="fw-bold mb-0" style="color: var(--primary-color);">Quick Actions</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3">
                    <a href="../centers/index.php" class="btn btn-outline-custom d-flex justify-content-between align-items-center py-3 text-start">
                        <div>
                            <div class="fw-bold"><i class="fa-solid fa-plus me-2"></i> Register Healthcare Center</div>
                            <small class="text-muted">Add a new hospital or clinic to the network.</small>
                        </div>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                    
                    <a href="../monitoring/index.php" class="btn btn-outline-custom d-flex justify-content-between align-items-center py-3 text-start">
                        <div>
                            <div class="fw-bold"><i class="fa-solid fa-calendar-check me-2"></i> Schedule Medical Camp</div>
                            <small class="text-muted">Initiate a new medical camp operation.</small>
                        </div>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                    
                    <a href="../reports/index.php" class="btn btn-outline-custom d-flex justify-content-between align-items-center py-3 text-start">
                        <div>
                            <div class="fw-bold"><i class="fa-solid fa-file-export me-2"></i> Export Monthly Analytics</div>
                            <small class="text-muted">Download PDFs of the latest performance metrics.</small>
                        </div>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent System Activity -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0" style="color: var(--primary-color);">Recent Activity Timeline</h5>
                <button class="btn btn-sm text-muted"><i class="fa-solid fa-ellipsis-vertical"></i></button>
            </div>
            <div class="card-body p-4">
                <div class="position-relative border-start border-2 ms-3 pb-3" style="border-color: var(--border-color) !important;">
                    
                    <?php if (empty($activities)): ?>
                        <p class="text-muted">No recent activity found.</p>
                    <?php else: ?>
                        <?php foreach ($activities as $log): 
                            // Determine icon and color based on action type
                            $icon = 'fa-check';
                            $colorVar = 'var(--secondary-color)';
                            
                            if (strpos($log['action_type'], 'center') !== false) {
                                $icon = 'fa-building';
                                $colorVar = 'var(--accent-color)';
                            } elseif (strpos($log['action_type'], 'backup') !== false) {
                                $icon = 'fa-download';
                                $colorVar = 'var(--primary-color)';
                            } elseif (strpos($log['action_type'], 'alert') !== false) {
                                $icon = 'fa-bell';
                                $colorVar = '#dc3545'; // danger
                            }
                            
                            // Format time safely (dummy time diff for now, or just show date)
                            $timeStr = date('M d, Y h:i A', strtotime($log['created_at']));
                        ?>
                        <!-- Timeline Item -->
                        <div class="position-relative mb-4 ps-4">
                            <div class="position-absolute rounded-circle shadow-sm d-flex justify-content-center align-items-center" style="width: 32px; height: 32px; left: -17px; top: 0; background-color: var(--bg-card); border: 2px solid <?= $colorVar ?>; color: <?= $colorVar ?>;">
                                <i class="fa-solid <?= $icon ?> small"></i>
                            </div>
                            <p class="mb-1 fw-bold text-dark"><?= htmlspecialchars($log['title']) ?></p>
                            <p class="small text-muted mb-1"><?= htmlspecialchars($log['description']) ?></p>
                            <small class="text-muted"><i class="fa-regular fa-clock me-1"></i> <?= $timeStr ?></small>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Card Hover Effects */
.card:hover {
    transform: translateY(-3px);
}
.btn-outline-custom {
    text-align: left !important;
}
.btn-outline-custom:hover i.fa-chevron-right {
    transform: translateX(4px);
    transition: transform 0.2s ease;
}
</style>

<?php include '../../includes/footer.php'; ?>
