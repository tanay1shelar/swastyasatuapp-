<?php
require_once '../../config/config.php';
require_once '../../includes/session.php';
$pageTitle = "User Management";
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container py-4">
    <div class="content-card p-4 rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-teal"><i class="fa-solid fa-users-gear me-2"></i> System User Management</h5>
            <a href="add_user.php" class="btn btn-primary rounded-pill px-3 btn-sm"><i class="fa-solid fa-user-plus me-1"></i> Add User</a>
        </div>
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Dr. Ramesh Sharma</td>
                    <td><span class="badge bg-primary">Doctor</span></td>
                    <td><span class="status-pill status-pill-success">Active</span></td>
                    <td>
                        <a href="edit_user.php?id=1" class="btn btn-sm btn-outline-primary rounded-pill"><i class="fa-solid fa-pen"></i></a>
                        <a href="delete_user.php?id=1" class="btn btn-sm btn-outline-danger rounded-pill"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
