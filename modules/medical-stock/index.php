<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Medical Stock Inventory Module
 * 
 * Manages medicine inventories per camp, supporting stock tracking, 
 * low stock alerts, expirations, and stock adjustments.
 */

// Define page parameters
$pageTitle = 'Medical Stock Inventory';

// Include system config and root layout components
require_once dirname(dirname(__DIR__)) . '/config/config.php';
include ROOT_PATH . 'includes/header.php';
include ROOT_PATH . 'includes/sidebar.php';
include ROOT_PATH . 'includes/navbar.php';
?>

<main class="app-content-wrapper">
    
    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="app-page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-custom-item"><a href="index.php">Health Worker Portal</a></li>
                    <li class="breadcrumb-custom-item active" aria-current="page">Medical Stock</li>
                </ol>
            </nav>
            <h1 class="page-title">Medical Stock Inventory</h1>
            <p class="text-secondary mb-0">Track and manage medicine stocks, register new suppliers, and monitor low or out-of-stock items for active camps.</p>
        </div>
        <!-- Export button -->
        <button class="btn-custom btn-custom-outline" id="btn-export-stock">
            <i class="bi bi-file-earmark-arrow-down"></i> Export
        </button>
    </div>

    <!-- Dynamic Alert Container for Inventory Actions -->
    <div id="alert-container-inventory" class="mb-3"></div>

    <!-- 2. CAMP SELECTOR AND DASHBOARD METRICS -->
    <div class="card-custom mb-4">
        <div class="card-custom-body p-3 bg-light-subtle">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="form-label-custom fw-semibold text-primary mb-1"><i class="bi bi-funnel"></i> Selected Outreach Camp Site</label>
                    <select class="form-control-custom" id="inventory-camp-select" style="height: 38px;">
                        <!-- Injected dynamically by JS -->
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-8 text-md-end mt-3 mt-md-0">
                    <button class="btn-custom btn-custom-primary" id="btn-add-medicine">
                        <i class="bi bi-plus-circle"></i> Add New Medicine
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. DASHBOARD METRICS CARDS -->
    <div class="row mb-4">
        <!-- Card 1: Total Medicines -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card-custom border-primary mb-0">
                <div class="card-custom-body stat-widget py-3">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="stats-total-medicines">0</span>
                        <span class="stat-widget-label">Total Medicines</span>
                    </div>
                    <div class="stat-widget-icon bg-primary-subtle text-primary" style="width: 42px; height: 42px; font-size: 1.15rem;">
                        <i class="bi bi-capsule"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Low Stock -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card-custom border-warning mb-0">
                <div class="card-custom-body stat-widget py-3">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="stats-low-stock">0</span>
                        <span class="stat-widget-label">Low Stock Items</span>
                    </div>
                    <div class="stat-widget-icon bg-warning-subtle text-warning" style="width: 42px; height: 42px; font-size: 1.15rem;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Out of Stock -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card-custom border-danger mb-0">
                <div class="card-custom-body stat-widget py-3">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="stats-out-of-stock">0</span>
                        <span class="stat-widget-label">Out of Stock</span>
                    </div>
                    <div class="stat-widget-icon bg-danger-subtle text-danger" style="width: 42px; height: 42px; font-size: 1.15rem;">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Expiring Soon -->
        <div class="col-12 col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card-custom border-info mb-0">
                <div class="card-custom-body stat-widget py-3">
                    <div class="stat-widget-info">
                        <span class="stat-widget-value" id="stats-expiring-soon">0</span>
                        <span class="stat-widget-label">Expiring (< 90 Days)</span>
                    </div>
                    <div class="stat-widget-icon bg-info-subtle text-info" style="width: 42px; height: 42px; font-size: 1.15rem;">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. INVENTORY TABLE CARD -->
    <div class="row">
        <div class="col-12">
            <div class="card-custom">
                <!-- Filters Header Section -->
                <div class="card-custom-header d-flex flex-wrap gap-3 justify-content-between align-items-center py-3">
                    
                    <!-- Search input -->
                    <div class="search-bar-custom" style="max-width: 280px;">
                        <i class="bi bi-search"></i>
                        <input type="text" id="medicine-search-input" class="form-control-custom" placeholder="Search medicine name, generic...">
                    </div>

                    <!-- Filter options -->
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <!-- Category selection -->
                        <select class="form-control-custom py-1 px-2 text-xs" id="filter-category" style="max-width: 160px; height: 32px; font-size: var(--font-size-xs);">
                            <option value="">All Categories</option>
                            <option value="Tablets">Tablets</option>
                            <option value="Capsules">Capsules</option>
                            <option value="Syrups">Syrups</option>
                            <option value="Injections">Injections</option>
                            <option value="Vaccines">Vaccines</option>
                            <option value="Emergency">Emergency</option>
                            <option value="Consumables">Consumables</option>
                        </select>

                        <!-- Status filter selection -->
                        <select class="form-control-custom py-1 px-2 text-xs" id="filter-status" style="max-width: 140px; height: 32px; font-size: var(--font-size-xs);">
                            <option value="">All Statuses</option>
                            <option value="In Stock">In Stock</option>
                            <option value="Low Stock">Low Stock</option>
                            <option value="Out of Stock">Out of Stock</option>
                        </select>

                        <!-- Reload button -->
                        <button class="btn-custom btn-custom-outline btn-custom-sm" id="btn-refresh-inventory" style="height: 32px; width: 32px; padding: 0;" title="Reload list">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="card-custom-body p-0">
                    <div class="table-custom-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Medicine Name</th>
                                    <th>Category</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-center">Min Stock</th>
                                    <th class="text-center">Unit</th>
                                    <th>Expiry Date</th>
                                    <th>Supplier</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="inventory-table-body">
                                <!-- Injected dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. DIALOG MODAL LAYOUTS -->
    
    <!-- Modal A: Add/Edit Medicine Form Modal -->
    <div class="modal-custom-backdrop" id="medicineFormModal">
        <div class="modal-custom-dialog" style="max-width: 650px;">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title" id="medicineFormTitle"><i class="bi bi-prescription2 text-accent"></i> Add New Medicine</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <form id="medicine-inventory-form" novalidate>
                <div class="modal-custom-body">
                    <input type="hidden" id="form-inventory-id" name="inventory_id">
                    <input type="hidden" id="form-camp-id" name="camp_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Medicine Name *</label>
                            <input type="text" class="form-control-custom" name="medicine_name" id="form-medicine-name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Generic Name *</label>
                            <input type="text" class="form-control-custom" name="generic_name" id="form-generic-name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom">Category *</label>
                            <select class="form-control-custom" name="category" id="form-category" required>
                                <option value="" selected disabled>Select Category...</option>
                                <option value="Tablets">Tablets</option>
                                <option value="Capsules">Capsules</option>
                                <option value="Syrups">Syrups</option>
                                <option value="Injections">Injections</option>
                                <option value="Vaccines">Vaccines</option>
                                <option value="Emergency">Emergency</option>
                                <option value="Consumables">Consumables</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Batch Number *</label>
                            <input type="text" class="form-control-custom" name="batch_number" id="form-batch-number" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom">Supplier Name *</label>
                            <input type="text" class="form-control-custom" name="supplier" id="form-supplier" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Unit Specification * (e.g. pcs, strip, bottle)</label>
                            <input type="text" class="form-control-custom" name="unit" id="form-unit" placeholder="e.g. pcs" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-custom">Purchase Date *</label>
                            <input type="date" class="form-control-custom" name="purchase_date" id="form-purchase-date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Expiry Date *</label>
                            <input type="date" class="form-control-custom" name="expiry_date" id="form-expiry-date" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label-custom">Current Stock Quantity *</label>
                            <input type="number" class="form-control-custom" name="quantity" id="form-quantity" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Minimum stock Limit *</label>
                            <input type="number" class="form-control-custom" name="minimum_quantity" id="form-min-quantity" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-custom">Unit Price (INR) *</label>
                            <input type="number" class="form-control-custom" name="price" id="form-price" min="0" step="0.01" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label-custom">Remarks / Storage Notes</label>
                            <textarea class="form-control-custom" name="remarks" id="form-remarks" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-custom-footer">
                    <button type="button" class="btn-custom btn-custom-outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-custom btn-custom-primary" id="btn-save-medicine">Save Record</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal B: View Details Modal -->
    <div class="modal-custom-backdrop" id="viewMedicineModal">
        <div class="modal-custom-dialog" style="max-width: 600px;">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title"><i class="bi bi-info-circle text-accent"></i> Medicine Product Card</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-custom-body" id="viewMedicineModalBody">
                <!-- Injected dynamically by JS -->
            </div>
            <div class="modal-custom-footer">
                <button class="btn-custom btn-custom-primary" data-dismiss="modal">Close Card</button>
            </div>
        </div>
    </div>

    <!-- Modal C: Adjust Quantity Modal -->
    <div class="modal-custom-backdrop" id="adjustStockModal">
        <div class="modal-custom-dialog" style="max-width: 450px;">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title"><i class="bi bi-plus-minus text-accent"></i> Adjust Inventory Levels</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <form id="adjust-stock-form" novalidate>
                <div class="modal-custom-body">
                    <input type="hidden" id="adjust-inventory-id" name="inventory_id">
                    <input type="hidden" id="adjust-camp-id" name="camp_id">
                    
                    <div class="mb-3">
                        <span class="d-block text-muted small">Medicine Name</span>
                        <strong class="text-primary fs-5" id="adjust-medicine-name">Amoxicillin 500mg</strong>
                    </div>

                    <div class="mb-3">
                        <span class="d-block text-muted small">Current Level</span>
                        <strong class="text-secondary" id="adjust-current-stock">150 pcs</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Adjustment Action *</label>
                        <div class="d-flex gap-4 mt-1">
                            <label class="d-flex align-items-center gap-2 cursor-pointer">
                                <input type="radio" name="adjust_action" value="increase" checked>
                                <span class="text-success fw-semibold"><i class="bi bi-plus-circle"></i> Increase Stock</span>
                            </label>
                            <label class="d-flex align-items-center gap-2 cursor-pointer">
                                <input type="radio" name="adjust_action" value="decrease">
                                <span class="text-danger fw-semibold"><i class="bi bi-dash-circle"></i> Decrease Stock</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Amount to Adjust *</label>
                        <input type="number" class="form-control-custom" name="adjust_amount" id="adjust-amount" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">Comments / Reason *</label>
                        <input type="text" class="form-control-custom" name="adjust_reason" id="adjust-reason" placeholder="e.g. Received new batch / Dispensed to patient" required>
                    </div>
                </div>
                <div class="modal-custom-footer">
                    <button type="button" class="btn-custom btn-custom-outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-custom btn-custom-primary">Submit Adjustment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal D: Deletion Confirmation Modal -->
    <div class="modal-custom-backdrop" id="deleteMedicineModal">
        <div class="modal-custom-dialog">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Warning: Confirm Deletion</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-custom-body">
                <p>Are you sure you want to delete <strong id="deleteMedicineName" class="text-primary"></strong> from this camp's stock list?</p>
                <p class="text-danger small mb-0"><i class="bi bi-info-circle-fill"></i> Deleting will permanently remove stock indicators for this medical camp.</p>
            </div>
            <div class="modal-custom-footer">
                <input type="hidden" id="delete-inventory-id">
                <button class="btn-custom btn-custom-outline" data-dismiss="modal">Cancel</button>
                <button class="btn-custom btn-custom-danger" id="confirmDeleteMedicineBtn">Permanently Delete</button>
            </div>
        </div>
    </div>

</main>

<!-- Load database controllers and medical stock scripts -->
<script src="<?php echo BASE_URL; ?>assets/js/core/database-mock.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/modules/medical-stock.js"></script>

<?php
// Include structural footer scripts
include ROOT_PATH . 'includes/footer.php';
?>
