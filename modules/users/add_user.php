<?php
require_once '../../config/config.php';
$pageTitle = "Add New User";
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="content-card p-4 rounded-4">
                <h5 class="fw-bold text-teal mb-3">Add System User</h5>
                <form action="user_list.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Role</label>
                        <select class="form-select" required>
                            <option value="Doctor">Doctor</option>
                            <option value="Health Worker">Health Worker</option>
                            <option value="Camp Admin">Camp Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
