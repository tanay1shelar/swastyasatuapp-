/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Medical Stock Inventory Controller
 * 
 * Manages inventory list rendering, statistics calculations, search, filters,
 * CSV exports, and CRUD modals.
 */

document.addEventListener('DOMContentLoaded', function () {
    // UI Elements
    const campSelect = document.getElementById('inventory-camp-select');
    const tableBody = document.getElementById('inventory-table-body');
    const btnAdd = document.getElementById('btn-add-medicine');
    const btnRefresh = document.getElementById('btn-refresh-inventory');
    
    const searchInput = document.getElementById('medicine-search-input');
    const categoryFilter = document.getElementById('filter-category');
    const statusFilter = document.getElementById('filter-status');

    // Stats Elements
    const statsTotal = document.getElementById('stats-total-medicines');
    const statsLow = document.getElementById('stats-low-stock');
    const statsOut = document.getElementById('stats-out-of-stock');
    const statsExpiring = document.getElementById('stats-expiring-soon');

    // Forms & Modals Elements
    const formModal = document.getElementById('medicineFormModal');
    const medicineForm = document.getElementById('medicine-inventory-form');
    const formTitle = document.getElementById('medicineFormTitle');
    const formInvId = document.getElementById('form-inventory-id');
    const formCampId = document.getElementById('form-camp-id');

    const viewModalBody = document.getElementById('viewMedicineModalBody');

    const adjustForm = document.getElementById('adjust-stock-form');
    const adjustInvId = document.getElementById('adjust-inventory-id');
    const adjustCampId = document.getElementById('adjust-camp-id');
    const adjustMedName = document.getElementById('adjust-medicine-name');
    const adjustCurrentStock = document.getElementById('adjust-current-stock');
    const adjustAmountInput = document.getElementById('adjust-amount');
    const adjustReasonInput = document.getElementById('adjust-reason');

    const confirmDeleteBtn = document.getElementById('confirmDeleteMedicineBtn');
    const deleteMedName = document.getElementById('deleteMedicineName');
    const deleteInvId = document.getElementById('delete-inventory-id');

    // State Variables
    let inventory = [];
    let filtered = [];
    let activeCampId = 0;

    // Helper to perform synchronous GET
    function apiGet(action) {
        try {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'api.php?action=' + action, false); // Synchronous
            xhr.send();
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                return res.success ? res.data : [];
            }
        } catch (e) {
            console.error("API Get error: ", e);
        }
        return [];
    }

    // Helper to perform synchronous POST
    function apiPost(formData) {
        try {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'api.php', false); // Synchronous
            xhr.send(formData);
            if (xhr.status === 200) {
                return JSON.parse(xhr.responseText);
            }
        } catch (e) {
            console.error("API Post error: ", e);
        }
        return { success: false, message: 'Server connection failure.' };
    }

    // Initialize Page
    function init() {
        // Load active outreach camps
        const camps = window.HMCMS_DB.getCamps().filter(c => c.status === 'Active');
        if (camps.length === 0) {
            if (window.showToast) window.showToast('No Camps Found', 'Create active medical camps first.', 'danger');
            return;
        }

        // Fill camp dropdown
        campSelect.innerHTML = '';
        camps.forEach(camp => {
            const opt = document.createElement('option');
            opt.value = camp.id;
            opt.textContent = camp.name;
            campSelect.appendChild(opt);
        });

        // Restore selected camp
        const savedCamp = localStorage.getItem('medical-stock-active-camp');
        if (savedCamp && camps.some(c => String(c.id) === String(savedCamp))) {
            activeCampId = parseInt(savedCamp);
            campSelect.value = activeCampId;
        } else {
            activeCampId = parseInt(camps[0].id);
            campSelect.value = activeCampId;
        }

        loadInventory();

        // Listen for camp switches
        if (campSelect) {
            campSelect.addEventListener('change', function () {
                activeCampId = parseInt(this.value);
                localStorage.setItem('medical-stock-active-camp', activeCampId);
                loadInventory();
            });
        }

        // Set up filters
        if (searchInput) {
            searchInput.addEventListener('input', applyFiltersAndRender);
        }
        if (categoryFilter) {
            categoryFilter.addEventListener('change', applyFiltersAndRender);
        }
        if (statusFilter) {
            statusFilter.addEventListener('change', applyFiltersAndRender);
        }
        if (btnRefresh) {
            btnRefresh.addEventListener('click', loadInventory);
        }

        // Add Medicine Modal Trigger
        if (btnAdd) {
            btnAdd.addEventListener('click', openAddModal);
        }

        // Form Submission
        if (medicineForm) {
            medicineForm.addEventListener('submit', handleFormSubmit);
        }
        if (adjustForm) {
            adjustForm.addEventListener('submit', handleAdjustmentSubmit);
        }
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', handleDeleteConfirm);
        }

        // Export Handler
        const btnExportStock = document.getElementById('btn-export-stock');
        if (btnExportStock) {
            btnExportStock.addEventListener('click', function (e) {
                e.preventDefault();
                triggerStockCsvExport();
            });
        }
    }

    // Load Stock List from database
    function loadInventory() {
        if (activeCampId <= 0) return;
        
        try {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `api.php?action=get_inventory&camp_id=${activeCampId}`, false);
            xhr.send();
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                inventory = res.success ? res.data : [];
            }
        } catch (e) {
            console.error(e);
            inventory = [];
        }

        calculateStats();
        applyFiltersAndRender();
    }

    // Calculate Dashboard Stats Card Values
    function calculateStats() {
        const total = inventory.length;
        let low = 0;
        let out = 0;
        let expiring = 0;

        const today = new Date();
        const ninetyDaysLimit = new Date();
        ninetyDaysLimit.setDate(today.getDate() + 90);

        inventory.forEach(item => {
            const qty = parseInt(item.quantity);
            const min = parseInt(item.minimum_quantity);
            const expiry = new Date(item.expiry_date);

            if (qty <= 0) {
                out++;
            } else if (qty <= min) {
                low++;
            }

            if (expiry <= ninetyDaysLimit) {
                expiring++;
            }
        });

        if (statsTotal) statsTotal.textContent = total;
        if (statsLow) statsLow.textContent = low;
        if (statsOut) statsOut.textContent = out;
        if (statsExpiring) statsExpiring.textContent = expiring;
    }

    // Filters and Renders Table Rows
    function applyFiltersAndRender() {
        const query = searchInput.value.toLowerCase().trim();
        const category = categoryFilter.value;
        const status = statusFilter.value;

        filtered = inventory.filter(item => {
            const matchesSearch = item.medicine_name.toLowerCase().includes(query) || 
                                  item.generic_name.toLowerCase().includes(query) || 
                                  item.supplier.toLowerCase().includes(query);
            
            const matchesCategory = category === '' || item.category === category;
            
            let itemStatus = 'In Stock';
            const qty = parseInt(item.quantity);
            const min = parseInt(item.minimum_quantity);
            if (qty <= 0) {
                itemStatus = 'Out of Stock';
            } else if (qty <= min) {
                itemStatus = 'Low Stock';
            }
            const matchesStatus = status === '' || itemStatus === status;

            return matchesSearch && matchesCategory && matchesStatus;
        });

        renderTable();
    }

    function renderTable() {
        if (!tableBody) return;
        tableBody.innerHTML = '';

        if (filtered.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                        No medicine inventory records found.
                    </td>
                </tr>
            `;
            return;
        }

        const today = new Date();
        const ninetyDaysLimit = new Date();
        ninetyDaysLimit.setDate(today.getDate() + 90);

        filtered.forEach(item => {
            const qty = parseInt(item.quantity);
            const min = parseInt(item.minimum_quantity);
            const expiry = new Date(item.expiry_date);

            // Determine Status badge
            let statusBadge = '';
            if (qty <= 0) {
                statusBadge = '<span class="badge-custom bg-danger-subtle text-danger">Out of Stock</span>';
            } else if (qty <= min) {
                statusBadge = '<span class="badge-custom bg-warning-subtle text-warning">Low Stock</span>';
            } else {
                statusBadge = '<span class="badge-custom bg-success-subtle text-success">In Stock</span>';
            }

            // Expiry coloring
            let expiryClass = '';
            if (expiry <= today) {
                expiryClass = 'text-danger fw-bold';
            } else if (expiry <= ninetyDaysLimit) {
                expiryClass = 'text-warning fw-semibold';
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <strong class="text-primary d-block">${escapeHtml(item.medicine_name)}</strong>
                    <span class="text-muted small">${escapeHtml(item.generic_name)}</span>
                </td>
                <td>${escapeHtml(item.category)}</td>
                <td class="text-center fw-bold">${qty}</td>
                <td class="text-center text-muted">${min}</td>
                <td class="text-center">${escapeHtml(item.unit)}</td>
                <td class="${expiryClass}">${item.expiry_date}</td>
                <td>${escapeHtml(item.supplier)}</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-end">
                    <button class="btn-custom btn-custom-outline btn-custom-sm py-1 px-2 btn-view-medicine" data-id="${item.inventory_id}" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn-custom btn-custom-outline btn-custom-sm py-1 px-2 btn-adjust-stock" data-id="${item.inventory_id}" title="Adjust Stock">
                        <i class="bi bi-plus-minus"></i>
                    </button>
                    <button class="btn-custom btn-custom-outline btn-custom-sm py-1 px-2 btn-edit-medicine" data-id="${item.inventory_id}" title="Edit Medicine">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn-custom btn-custom-outline btn-custom-sm py-1 px-2 text-danger btn-delete-medicine" data-id="${item.inventory_id}" title="Delete Record">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            // Action Listeners
            const btnView = tr.querySelector('.btn-view-medicine');
            if (btnView) {
                btnView.addEventListener('click', () => openViewModal(item));
            }
            const btnAdjust = tr.querySelector('.btn-adjust-stock');
            if (btnAdjust) {
                btnAdjust.addEventListener('click', () => openAdjustModal(item));
            }
            const btnEdit = tr.querySelector('.btn-edit-medicine');
            if (btnEdit) {
                btnEdit.addEventListener('click', () => openEditModal(item));
            }
            const btnDelete = tr.querySelector('.btn-delete-medicine');
            if (btnDelete) {
                btnDelete.addEventListener('click', () => openDeleteModal(item));
            }

            tableBody.appendChild(tr);
        });
    }

    // Modal Triggers
    function openAddModal() {
        medicineForm.reset();
        formInvId.value = '';
        formCampId.value = activeCampId;
        formTitle.innerHTML = '<i class="bi bi-plus-circle text-accent"></i> Add New Medicine';
        
        // Pre-fill date fields
        document.getElementById('form-purchase-date').value = new Date().toISOString().slice(0, 10);
        
        window.showModal('medicineFormModal');
    }

    function openEditModal(item) {
        formTitle.innerHTML = '<i class="bi bi-pencil text-accent"></i> Edit Medicine Record';
        formInvId.value = item.inventory_id;
        formCampId.value = item.camp_id;

        document.getElementById('form-medicine-name').value = item.medicine_name;
        document.getElementById('form-generic-name').value = item.generic_name;
        document.getElementById('form-category').value = item.category;
        document.getElementById('form-batch-number').value = item.batch_number;
        document.getElementById('form-supplier').value = item.supplier;
        document.getElementById('form-unit').value = item.unit;
        document.getElementById('form-purchase-date').value = item.purchase_date;
        document.getElementById('form-expiry-date').value = item.expiry_date;
        document.getElementById('form-quantity').value = item.quantity;
        document.getElementById('form-min-quantity').value = item.minimum_quantity;
        document.getElementById('form-price').value = item.price;
        document.getElementById('form-remarks').value = item.remarks || '';

        window.showModal('medicineFormModal');
    }

    function openViewModal(item) {
        if (!viewModalBody) return;

        const qty = parseInt(item.quantity);
        const min = parseInt(item.minimum_quantity);
        let statusBadge = '';
        if (qty <= 0) {
            statusBadge = '<span class="badge-custom bg-danger-subtle text-danger">Out of Stock</span>';
        } else if (qty <= min) {
            statusBadge = '<span class="badge-custom bg-warning-subtle text-warning">Low Stock</span>';
        } else {
            statusBadge = '<span class="badge-custom bg-success-subtle text-success">In Stock</span>';
        }

        viewModalBody.innerHTML = `
            <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-3">
                <div class="bg-primary-subtle text-primary rounded p-3 fs-3">
                    <i class="bi bi-capsule"></i>
                </div>
                <div>
                    ${statusBadge}
                    <h4 class="fw-bold text-primary m-0 mt-1">${escapeHtml(item.medicine_name)}</h4>
                    <span class="text-muted small">Generic: <strong>${escapeHtml(item.generic_name)}</strong></span>
                </div>
            </div>
            <div class="row g-3 small text-secondary">
                <div class="col-6">
                    <span class="d-block text-muted small">Category</span>
                    <strong>${escapeHtml(item.category)}</strong>
                </div>
                <div class="col-6">
                    <span class="d-block text-muted small">Batch Number</span>
                    <strong>${escapeHtml(item.batch_number)}</strong>
                </div>
                <div class="col-6 border-top pt-2">
                    <span class="d-block text-muted small">Current Level</span>
                    <strong class="text-primary">${qty} ${escapeHtml(item.unit)}</strong>
                </div>
                <div class="col-6 border-top pt-2">
                    <span class="d-block text-muted small">Minimum Limit</span>
                    <strong>${min} ${escapeHtml(item.unit)}</strong>
                </div>
                <div class="col-6 border-top pt-2">
                    <span class="d-block text-muted small">Unit Price</span>
                    <strong class="text-success">₹${parseFloat(item.price).toFixed(2)}</strong>
                </div>
                <div class="col-6 border-top pt-2">
                    <span class="d-block text-muted small">Supplier</span>
                    <strong>${escapeHtml(item.supplier)}</strong>
                </div>
                <div class="col-6 border-top pt-2">
                    <span class="d-block text-muted small">Purchase Date</span>
                    <strong>${item.purchase_date}</strong>
                </div>
                <div class="col-6 border-top pt-2">
                    <span class="d-block text-muted small">Expiry Date</span>
                    <strong class="${new Date(item.expiry_date) <= new Date() ? 'text-danger' : ''}">${item.expiry_date}</strong>
                </div>
                <div class="col-12 border-top pt-2">
                    <span class="d-block text-muted small">Remarks & Storage Comments</span>
                    <span>${escapeHtml(item.remarks || 'None')}</span>
                </div>
            </div>
        `;
        window.showModal('viewMedicineModal');
    }

    function openAdjustModal(item) {
        adjustInvId.value = item.inventory_id;
        adjustCampId.value = item.camp_id;
        adjustMedName.textContent = item.medicine_name;
        adjustCurrentStock.textContent = `${item.quantity} ${item.unit}`;
        adjustAmountInput.value = '';
        adjustReasonInput.value = '';
        window.showModal('adjustStockModal');
    }

    function openDeleteModal(item) {
        deleteInvId.value = item.inventory_id;
        deleteMedName.textContent = item.medicine_name;
        window.showModal('deleteMedicineModal');
    }

    // CRUD Submissions
    function handleFormSubmit(e) {
        e.preventDefault();
        if (!medicineForm.checkValidity()) {
            medicineForm.classList.add('was-validated');
            if (window.showToast) window.showToast('Validation Failed', 'Complete all required fields.', 'danger');
            return;
        }

        const formData = new FormData(medicineForm);
        formData.append('action', 'save_medicine');
        const res = apiPost(formData);

        if (res.success) {
            window.hideModal('medicineFormModal');
            if (window.showToast) window.showToast('Record Saved', res.message, 'success');
            loadInventory();
        } else {
            if (window.showToast) window.showToast('Saving Failed', res.message, 'danger');
        }
    }

    function handleAdjustmentSubmit(e) {
        e.preventDefault();
        if (!adjustForm.checkValidity()) {
            adjustForm.classList.add('was-validated');
            return;
        }

        const invId = parseInt(adjustInvId.value);
        const campId = parseInt(adjustCampId.value);
        const item = inventory.find(i => parseInt(i.inventory_id) === invId);
        if (!item) return;

        const action = document.querySelector('input[name="adjust_action"]:checked').value;
        const amount = parseInt(adjustAmountInput.value);
        const reason = adjustReasonInput.value.trim();

        let newQty = parseInt(item.quantity);
        if (action === 'increase') {
            newQty += amount;
        } else {
            newQty = Math.max(0, newQty - amount);
        }

        // Re-use save_medicine but only update stock quantity & remarks
        const body = new FormData();
        body.append('action', 'save_medicine');
        body.append('inventory_id', invId);
        body.append('camp_id', campId);
        body.append('medicine_name', item.medicine_name);
        body.append('generic_name', item.generic_name);
        body.append('category', item.category);
        body.append('batch_number', item.batch_number);
        body.append('supplier', item.supplier);
        body.append('unit', item.unit);
        body.append('purchase_date', item.purchase_date);
        body.append('expiry_date', item.expiry_date);
        body.append('minimum_quantity', item.minimum_quantity);
        body.append('price', item.price);
        body.append('quantity', newQty);
        body.append('remarks', `Stock adjusted: ${action === 'increase' ? '+' : '-'}${amount} (${reason}). Previous: ${item.remarks || ''}`);

        const res = apiPost(body);
        if (res.success) {
            window.hideModal('adjustStockModal');
            if (window.showToast) window.showToast('Inventory Adjusted', `Stock quantity updated successfully.`, 'success');
            loadInventory();
        } else {
            if (window.showToast) window.showToast('Failed to Adjust', res.message, 'danger');
        }
    }

    function handleDeleteConfirm() {
        const invId = parseInt(deleteInvId.value);
        if (invId <= 0) return;

        const body = new FormData();
        body.append('action', 'delete_medicine');
        body.append('inventory_id', invId);

        const res = apiPost(body);
        if (res.success) {
            window.hideModal('deleteMedicineModal');
            if (window.showToast) window.showToast('Medicine Deleted', 'Medicine successfully removed from camp stock.', 'danger');
            loadInventory();
        } else {
            if (window.showToast) window.showToast('Deletion Failed', res.message, 'danger');
        }
    }

    function displayInventoryAlert(message, type = 'danger') {
        const container = document.getElementById('alert-container-inventory');
        if (!container) return;
        container.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        setTimeout(() => {
            const alertEl = container.querySelector('.alert');
            if (alertEl) {
                const bsAlert = bootstrap.Alert.getInstance(alertEl) || new bootstrap.Alert(alertEl);
                bsAlert.close();
            }
        }, 5000);
    }

    // CSV Download
    function triggerStockCsvExport() {
        if (activeCampId <= 0) return;
        if (filtered.length === 0) {
            displayInventoryAlert('No records available to export.', 'danger');
            return;
        }
        
        const ids = filtered.map(item => item.inventory_id).join(',');
        window.location.href = `api.php?action=export_stock_csv&ids=${encodeURIComponent(ids)}`;
        if (window.showToast) window.showToast('Export Initiated', 'Stock inventory CSV file is downloading.', 'success');
    }

    // Escape HTML Helper
    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Start Controller
    init();
});
