<?php
require_once '../../config/config.php';
$pageTitle = "System Reports";
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-4">
    <div class="content-card p-4 rounded-4">
        <h5 class="fw-bold text-teal mb-3"><i class="fa-solid fa-file-invoice me-2"></i> Medical Camp Analytics & Reports</h5>
        <p class="text-muted">Export PDF / Excel summary logs of camp operations, medicine usage, and patient checkups.</p>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
