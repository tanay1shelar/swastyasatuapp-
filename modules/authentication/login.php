<?php
require_once '../../config/config.php';
$pageTitle = "User Login";
require_once '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="content-card p-4 rounded-4 shadow-sm">
                <div class="text-center mb-4">
                    <img src="<?php echo BASE_URL; ?>assets/images/swasthyasetu-logo.png" alt="Logo" style="height: 60px; width: 60px; border-radius: 50%; object-fit: cover;" class="mb-2" />
                    <h5 class="fw-bold text-teal">Sign In to Swasthya Setu</h5>
                </div>
                <form action="auth.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Role</label>
                        <select name="role" class="form-select" required>
                            <option value="Super Admin">Super Admin</option>
                            <option value="Camp Medical Admin" selected>Camp Medical Admin</option>
                            <option value="Doctor">Doctor</option>
                            <option value="Health Worker">Health Worker</option>
                            <option value="Citizen/Patient">Citizen / Patient</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Username / Email</label>
                        <input type="text" name="username" class="form-control" placeholder="Enter username" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required />
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Sign In</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
