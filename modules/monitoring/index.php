<?php 
require_once '../../includes/db.php';

// Fetch all camps
$stmt = $pdo->query("SELECT c.*, ce.name as center_name, ce.district, ce.state 
                     FROM camps c 
                     JOIN centers ce ON c.center_id = ce.id 
                     ORDER BY c.created_at DESC");
$camps = $stmt->fetchAll();

// Calculate Stats
$activeCampsCount = 0;
$patientsToday = 0;
$doctorsOnDuty = 0;
$criticalAlerts = 2; // Dummy value for demonstration

foreach ($camps as $camp) {
    if (in_array($camp['status'], ['Scheduled', 'Ongoing'])) {
        $activeCampsCount++;
    }
    if ($camp['status'] === 'Ongoing') {
        $patientsToday += $camp['patients_treated'];
        $doctorsOnDuty += 3; // Estimating 3 doctors per ongoing camp
    }
}

include '../../includes/header.php'; 
?>

            <div class="main-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Live Camp Monitoring</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="layout.html">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Monitoring</li>
                            </ol>
                        </nav>
                    </div>
                    <button class="btn btn-outline-custom" id="btnRefreshData">
                        <i class="fa-solid fa-rotate-right me-2"></i> Refresh Data
                    </button>
                </div>

                <!-- Summary Cards -->
                <div class="row g-4 mb-4">
                    <!-- Card 1 -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="text-muted fw-medium">Active Camps</div>
                                    <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                                        <i class="fa-solid fa-tent fa-lg"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-0"><?= $activeCampsCount ?></h3>
                                <div class="text-success small mt-2 fw-medium">
                                    <i class="fa-solid fa-arrow-up me-1"></i> 2 more than yesterday
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="text-muted fw-medium">Patients Today</div>
                                    <div class="bg-success bg-opacity-10 text-success p-2 rounded-3">
                                        <i class="fa-solid fa-users fa-lg"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-0"><?= $patientsToday ?></h3>
                                <div class="text-success small mt-2 fw-medium">
                                    <i class="fa-solid fa-arrow-up me-1"></i> +15% from last week
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="text-muted fw-medium">Doctors on Duty</div>
                                    <div class="bg-info bg-opacity-10 text-info p-2 rounded-3">
                                        <i class="fa-solid fa-user-doctor fa-lg"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-0"><?= $doctorsOnDuty ?></h3>
                                <div class="text-muted small mt-2 fw-medium">Across all active camps</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="text-muted fw-medium">Critical Alerts</div>
                                    <div class="bg-danger bg-opacity-10 text-danger p-2 rounded-3">
                                        <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-0"><?= $criticalAlerts ?></h3>
                                <div class="text-danger small mt-2 fw-medium">Low medicine stock</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Camps Grid -->
                <h5 class="fw-bold mb-3 text-dark">Ongoing Camps Directory</h5>
                <div class="row g-4">
                    <?php if (empty($camps)): ?>
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="fa-solid fa-tent-slash fa-3x mb-3" style="color: #cbd5e1;"></i>
                            <h5>No camps found.</h5>
                            <p>Register a healthcare center and schedule a camp first.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($camps as $camp): ?>
                            <!-- Camp Item -->
                            <div class="col-md-6 col-xl-4">
                                <div class="card border-0 shadow-sm h-100 <?= $camp['status'] === 'Ongoing' ? 'border-start border-4 border-success' : '' ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($camp['title']) ?></h6>
                                            <?php if ($camp['status'] === 'Ongoing'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"><i class="fa-solid fa-circle fa-2xs me-1 heartbeat"></i> Live</span>
                                            <?php elseif ($camp['status'] === 'Scheduled'): ?>
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1"><i class="fa-regular fa-calendar me-1"></i> Scheduled</span>
                                            <?php elseif ($camp['status'] === 'Completed'): ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1"><i class="fa-solid fa-check me-1"></i> Completed</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1"><i class="fa-solid fa-xmark me-1"></i> Cancelled</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small mb-3">
                                            <i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($camp['center_name']) ?> <br>
                                            <span class="ms-3 text-muted" style="font-size: 0.8rem;"><?= htmlspecialchars($camp['district']) ?>, <?= htmlspecialchars($camp['state']) ?></span>
                                        </div>
                                        
                                        <div class="row g-2 mb-3 text-center">
                                            <div class="col-4">
                                                <div class="p-2 bg-light rounded">
                                                    <div class="fw-bold text-dark"><?= date('M d', strtotime($camp['start_date'])) ?></div>
                                                    <div class="small text-muted" style="font-size: 0.7rem;">Start</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-2 bg-light rounded">
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($camp['patients_treated']) ?></div>
                                                    <div class="small text-muted" style="font-size: 0.7rem;">Treated</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="p-2 bg-light rounded">
                                                    <div class="fw-bold text-dark"><?= date('M d', strtotime($camp['end_date'])) ?></div>
                                                    <div class="small text-muted" style="font-size: 0.7rem;">End</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="small text-muted fw-medium">Status: <?= $camp['status'] ?></div>
                                            <button class="btn btn-sm btn-outline-custom btn-view-details" 
                                                data-title="<?= htmlspecialchars($camp['title']) ?>" 
                                                data-location="<?= htmlspecialchars($camp['center_name']) ?>"
                                                data-treated="<?= $camp['patients_treated'] ?>">
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                </div>
            </div>

<!-- Camp Details Modal -->
<div class="modal fade" id="campDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="modalCampName" style="color: var(--primary-color);">Camp Name</h5>
                    <p class="text-muted small mb-0" id="modalCampLocation"><i class="fa-solid fa-location-dot me-1"></i> Location</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center border">
                            <div class="text-muted small fw-bold text-uppercase mb-1">Chief Medical Officer</div>
                            <div class="fw-bold text-dark">Dr. Sarah Jenkins</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center border">
                            <div class="text-muted small fw-bold text-uppercase mb-1">Medicines Dispatched</div>
                            <div class="fw-bold text-dark">850 Units</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded text-center border">
                            <div class="text-muted small fw-bold text-uppercase mb-1">Urgent Cases</div>
                            <div class="fw-bold text-danger">3 Transfers</div>
                        </div>
                    </div>
                </div>
                
                <h6 class="fw-bold text-dark mb-3">Recent Activity Feed</h6>
                <div class="list-group list-group-flush border rounded">
                    <div class="list-group-item px-3 py-2 border-bottom text-muted small">
                        <span class="text-dark fw-bold">10:45 AM:</span> Patient #1042 registered for general checkup.
                    </div>
                    <div class="list-group-item px-3 py-2 border-bottom text-muted small">
                        <span class="text-dark fw-bold">10:30 AM:</span> Dr. Jenkins completed minor surgery on Patient #1020.
                    </div>
                    <div class="list-group-item px-3 py-2 text-muted small bg-light">
                        <span class="text-danger fw-bold">10:15 AM:</span> Low stock alert for Paracetamol triggered.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="../reports/index.php" class="btn btn-primary-custom">View Full Analytics</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Refresh Data Logic
    const refreshBtn = document.getElementById('btnRefreshData');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const originalHTML = this.innerHTML;
            // Set to loading state
            this.innerHTML = '<i class="fa-solid fa-rotate-right fa-spin me-2"></i> Refreshing...';
            this.classList.add('disabled');
            
            // Reload the page to fetch latest data from DB
            window.location.reload();
        });
    }

    // 2. View Details Modal Logic
    const viewBtns = document.querySelectorAll('.btn-view-details');
    const modalEl = document.getElementById('campDetailsModal');
    let campModal = null;
    if (modalEl && typeof bootstrap !== 'undefined') {
        campModal = new bootstrap.Modal(modalEl);
    }

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Traverse DOM to find camp details
            const cardBody = this.closest('.card-body');
            const campName = cardBody.querySelector('h6').innerText;
            const campLocation = cardBody.querySelector('.text-muted.small').innerText.trim();
            
            // Populate Modal
            document.getElementById('modalCampName').innerText = campName;
            document.getElementById('modalCampLocation').innerHTML = `<i class="fa-solid fa-location-dot me-1"></i> ${campLocation}`;
            
            // Show Modal
            if (campModal) campModal.show();
        });
    });
});
</script>
<?php include '../../includes/footer.php'; ?>
