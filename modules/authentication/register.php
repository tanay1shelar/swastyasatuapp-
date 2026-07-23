<?php
require_once '../../config/config.php';
$pageTitle = "Patient Registration";
require_once '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="content-card p-4 rounded-4 shadow-sm">
                <h5 class="fw-bold text-teal mb-3"><i class="fa-solid fa-user-plus me-2"></i> Register New Patient</h5>
                <form action="auth.php?action=register" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="fullname" class="form-control" placeholder="John Doe" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required />
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Register Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
