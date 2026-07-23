<?php
require_once '../../includes/db.php';

$successMsg = '';
$errorMsg = '';

// Handle Actions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $city = trim($_POST['city'] ?? ''); 
        $address = trim($_POST['address'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $status = $_POST['status'] ?? 'Active';
        
        // Let's store address in 'district' field since that's what we have, or combine city/address.
        // Actually, schema has 'district' which we can use for city, and we can just ignore address or append it.
        // Let's store "$city - $address" in district for now to preserve it.
        $district = $city . ($address ? " - " . $address : "");

        if (empty($name) || empty($state)) {
            $errorMsg = "Center Name and State are required.";
        } else {
            if ($action === 'add') {
                // Generate a center code e.g., HC-1004
                $stmt = $pdo->query("SELECT MAX(id) FROM centers");
                $maxId = $stmt->fetchColumn();
                $nextId = $maxId ? $maxId + 1 : 1001; // Start at 1001 if empty
                $centerCode = 'HC-' . $nextId;

                $sql = "INSERT INTO centers (center_code, name, email, state, district, contact_person, contact_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$centerCode, $name, $email, $state, $district, $contactPerson, $contactPhone, $status])) {
                    // Log activity
                    $pdo->prepare("INSERT INTO activity_logs (action_type, title, description) VALUES ('center', 'New Center Registered', ?)")->execute(["\"$name\" was added to the network."]);
                    $successMsg = "Center added successfully!";
                } else {
                    $errorMsg = "Failed to add center.";
                }
            } elseif ($action === 'edit') {
                $id = $_POST['center_id'] ?? 0;
                $sql = "UPDATE centers SET name=?, email=?, state=?, district=?, contact_person=?, contact_number=?, status=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$name, $email, $state, $district, $contactPerson, $contactPhone, $status, $id])) {
                    $successMsg = "Center updated successfully!";
                } else {
                    $errorMsg = "Failed to update center.";
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['center_id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM centers WHERE id = ?");
        if ($stmt->execute([$id])) {
            $successMsg = "Center deleted successfully!";
        } else {
            $errorMsg = "Failed to delete center.";
        }
    }
}

// Fetch Centers
$stmtCenters = $pdo->query("SELECT * FROM centers ORDER BY created_at DESC");
$centersList = $stmtCenters->fetchAll();

include '../../includes/header.php'; 
?>

            <div class="main-content">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Healthcare Centers</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Centers</li>
                            </ol>
                        </nav>
                    </div>
                    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#centerModal" id="addNewBtn">
                        <i class="fa-solid fa-plus me-2"></i> Add New Center
                    </button>
                </div>

                <?php if ($successMsg): ?>
                    <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4"><i class="fa-solid fa-check-circle me-2"></i><?= htmlspecialchars($successMsg) ?></div>
                <?php endif; ?>
                <?php if ($errorMsg): ?>
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4"><i class="fa-solid fa-exclamation-triangle me-2"></i><?= htmlspecialchars($errorMsg) ?></div>
                <?php endif; ?>

                <!-- Search & Filter Card -->
                <div class="card mb-4 border-0 shadow-sm" style="border-radius: 1.25rem; overflow: hidden;">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0" id="searchIconBtn" style="cursor: pointer;"><i class="fa-solid fa-search text-muted"></i></span>
                                    <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Search centers by name or location...">
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <select id="statusFilter" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-lg-2">
                                <button id="filterBtn" class="btn btn-outline-custom w-100">Filter</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table Card -->
                <div class="card border-0 shadow-sm" style="border-radius: 1.25rem; overflow: hidden;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="centersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="ps-4">Center ID</th>
                                        <th scope="col">Center Name</th>
                                        <th scope="col">Location</th>
                                        <th scope="col">Contact Person</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($centersList)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No healthcare centers found. Add a new one to get started.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($centersList as $center): ?>
                                            <tr data-id="<?= htmlspecialchars($center['id']) ?>" 
                                                data-name="<?= htmlspecialchars($center['name']) ?>"
                                                data-email="<?= htmlspecialchars($center['email']) ?>"
                                                data-district="<?= htmlspecialchars($center['district']) ?>"
                                                data-state="<?= htmlspecialchars($center['state']) ?>"
                                                data-contact="<?= htmlspecialchars($center['contact_person']) ?>"
                                                data-phone="<?= htmlspecialchars($center['contact_number']) ?>"
                                                data-status="<?= htmlspecialchars(strtolower($center['status'] ?? '')) ?>">
                                                <td class="ps-4 fw-medium"><?= htmlspecialchars($center['center_code']) ?></td>
                                                <td>
                                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($center['name']) ?></div>
                                                    <div class="small text-muted"><?= htmlspecialchars($center['email'] ?: 'No email') ?></div>
                                                </td>
                                                <td>
                                                    <?php
                                                        // Extract city from district assuming "City - Address"
                                                        $locParts = explode(" - ", $center['district'], 2);
                                                        $cityDisplay = $locParts[0] ?: 'Unknown City';
                                                    ?>
                                                    <?= htmlspecialchars($center['district']) ?> <br>
                                                    <small class="text-muted"><?= htmlspecialchars($cityDisplay) ?>, <?= htmlspecialchars($center['state']) ?></small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($center['contact_person'] ?: 'N/A') ?> <br>
                                                    <small class="text-muted"><?= htmlspecialchars($center['contact_number'] ?: 'N/A') ?></small>
                                                </td>
                                                <td>
                                                    <?php if (strtolower($center['status']) === 'active'): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <button type="button" class="btn btn-sm btn-light text-primary me-1 btn-edit" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                                    
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this healthcare center?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="center_id" value="<?= htmlspecialchars($center['id']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-light text-danger btn-delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted small">Showing <?= count($centersList) ?> entries</span>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

            <!-- Add/Edit Center Modal -->
    <div class="modal fade" id="centerModal" tabindex="-1" aria-labelledby="centerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold" id="centerModalLabel">Add New Healthcare Center</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="centerForm" method="POST" action="">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="center_id" id="formCenterId" value="">
                    
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Center Name -->
                            <div class="col-md-12">
                                <label for="centerName" class="form-label fw-medium">Center Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="centerName" placeholder="e.g. City General Hospital" required>
                            </div>
                            
                            <!-- State -->
                            <div class="col-md-6">
                                <label for="centerState" class="form-label fw-medium">State <span class="text-danger">*</span></label>
                                <select class="form-select" name="state" id="centerState" required>
                                    <option value="" disabled selected>Select State</option>
                                    <option value="Maharashtra">Maharashtra</option>
                                    <option value="Karnataka">Karnataka</option>
                                    <option value="Delhi">Delhi</option>
                                    <option value="Gujarat">Gujarat</option>
                                    <option value="Tamil Nadu">Tamil Nadu</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <!-- City -->
                            <div class="col-md-6">
                                <label for="centerCity" class="form-label fw-medium">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="city" id="centerCity" placeholder="e.g. Mumbai" required>
                            </div>
                            
                            <!-- Address Line -->
                            <div class="col-md-12">
                                <label for="centerLocation" class="form-label fw-medium">Street Address</label>
                                <textarea class="form-control" name="address" id="centerLocation" rows="1" placeholder="Full street address"></textarea>
                            </div>

                            <!-- Contact Person -->
                            <div class="col-md-6">
                                <label for="contactPerson" class="form-label fw-medium">Contact Person Name</label>
                                <input type="text" class="form-control" name="contact_person" id="contactPerson" placeholder="Full Name">
                            </div>

                            <!-- Phone Number -->
                            <div class="col-md-6">
                                <label for="contactPhone" class="form-label fw-medium">Phone Number</label>
                                <input type="tel" class="form-control" name="contact_phone" id="contactPhone" placeholder="+1 234 567 8900">
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="contactEmail" class="form-label fw-medium">Email Address</label>
                                <input type="email" class="form-control" name="email" id="contactEmail" placeholder="email@example.com">
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label for="centerStatus" class="form-label fw-medium">Status</label>
                                <select class="form-select" name="status" id="centerStatus">
                                    <option value="Active" selected>Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-custom">Save Center</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const centerForm = document.getElementById('centerForm');
    const formAction = document.getElementById('formAction');
    const formCenterId = document.getElementById('formCenterId');
    const modalTitle = document.getElementById('centerModalLabel');
    const tableBody = document.querySelector('table tbody');
    
    // The "Add New Center" button
    const addNewBtn = document.getElementById('addNewBtn');
    if (addNewBtn) {
        addNewBtn.addEventListener('click', () => {
            modalTitle.textContent = 'Add New Healthcare Center';
            centerForm.reset();
            formAction.value = 'add';
            formCenterId.value = '';
        });
    }

    // Handle Edit via Event Delegation
    if (tableBody) {
        tableBody.addEventListener('click', function(e) {
            // --- EDIT BUTTON ---
            const editBtn = e.target.closest('.btn-edit');
            if (editBtn) {
                e.preventDefault();
                const row = editBtn.closest('tr');
                
                const id = row.getAttribute('data-id') || '';
                const name = row.getAttribute('data-name') || '';
                const email = row.getAttribute('data-email') || '';
                const district = row.getAttribute('data-district') || '';
                const state = row.getAttribute('data-state') || '';
                const contact = row.getAttribute('data-contact') || '';
                const phone = row.getAttribute('data-phone') || '';
                const status = row.getAttribute('data-status') || '';
                
                // Parse city and address from district
                let city = district;
                let address = '';
                if(district && district.includes(' - ')) {
                    const parts = district.split(' - ');
                    city = parts.shift().trim();
                    address = parts.join(' - ').trim();
                }

                // Populate form
                formAction.value = 'edit';
                formCenterId.value = id;
                document.getElementById('centerName').value = name;
                document.getElementById('contactEmail').value = email;
                document.getElementById('centerCity').value = city;
                document.getElementById('centerLocation').value = address;
                
                const stateSelect = document.getElementById('centerState');
                let optionExists = Array.from(stateSelect.options).some(opt => opt.value === state);
                stateSelect.value = optionExists ? state : (state ? 'Other' : '');
                
                document.getElementById('contactPerson').value = contact;
                document.getElementById('contactPhone').value = phone;
                
                const statusSelect = document.getElementById('centerStatus');
                statusSelect.value = (status.toLowerCase() === 'inactive') ? 'Inactive' : 'Active';

                modalTitle.textContent = 'Edit Healthcare Center';
                
                // Show modal
                const modalEl = document.getElementById('centerModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modalInstance.show();
            }
        });
    }

    // --- SEARCH & FILTER LOGIC ---
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const filterBtn = document.getElementById('filterBtn');

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const rows = tableBody.querySelectorAll('tr[data-id]');

        rows.forEach(row => {
            const name = row.getAttribute('data-name').toLowerCase();
            const location = row.getAttribute('data-district').toLowerCase();
            const status = row.getAttribute('data-status').toLowerCase();

            const matchesSearch = name.includes(searchTerm) || location.includes(searchTerm);
            const matchesStatus = (statusValue === '') || (status === statusValue);

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (filterBtn) filterBtn.addEventListener('click', applyFilters);
    if (searchInput) searchInput.addEventListener('keyup', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
});
</script>
<?php include '../../includes/footer.php'; ?>
