<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/session.php';

$pageTitle = "Dashboard Overview";
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-4">
    <div class="content-card p-4 rounded-4">
        <h4 class="fw-bold mb-3 text-teal"><i class="fa-solid fa-chart-line me-2"></i> System Analytics & Operations Dashboard</h4>
        <div class="row g-4 my-2">
            <div class="col-md-3">
                <div class="dashboard-stat-card">
                    <h6 class="text-muted mb-1">Total Medical Camps</h6>
                    <h3 class="fw-extrabold text-teal mb-0">24</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat-card">
                    <h6 class="text-muted mb-1">Patients Treated</h6>
                    <h3 class="fw-extrabold text-success mb-0">343</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat-card">
                    <h6 class="text-muted mb-1">Active Doctors</h6>
                    <h3 class="fw-extrabold text-primary mb-0">12</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-stat-card">
                    <h6 class="text-muted mb-1">Medicine Stock Units</h6>
                    <h3 class="fw-extrabold text-warning mb-0">8,600</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
