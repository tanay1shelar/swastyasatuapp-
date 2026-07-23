<?php
require_once '../../config/config.php';
$pageTitle = "Edit User";
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="content-card p-4 rounded-4">
                <h5 class="fw-bold text-teal mb-3">Edit User Account</h5>
                <form action="user_list.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" class="form-control" value="Dr. Ramesh Sharma" required />
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
