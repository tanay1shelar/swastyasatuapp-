/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Patient Directory Controller Script
 * 
 * Manages stats indicator cards updates, multi-filter selectors, search keyups,
 * paginated rendering (10 rows/page), exports, and details/delete modals.
 */

document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------------------
    // 1. SELECTORS & STATE
    // -------------------------------------------------------------------------
    const statsTotal = document.getElementById('stats-total-patients');
    const statsVerified = document.getElementById('stats-verified-patients');
    const statsPending = document.getElementById('stats-pending-patients');
    const statsToday = document.getElementById('stats-today-patients');

    const searchInput = document.getElementById('patient-search-input');
    const campFilter = document.getElementById('filter-camp');
    const statusFilter = document.getElementById('filter-status');
    const sortBySelect = document.getElementById('sort-by');
    const btnRefresh = document.getElementById('btn-refresh-list');
    
    // Table selectors
    const tableBody = document.getElementById('patient-list-table-body');
    const pagInfo = document.getElementById('patient-list-pagination-info');
    const pagNav = document.getElementById('patient-list-pagination-nav');

    let patientsList = [];
    let campsList = [];
    let filteredPatients = [];
    let activePatientToDelete = null;

    let currentPage = 1;
    const patientsPerPage = 10;

    function loadData() {
        patientsList = window.HMCMS_DB.getPatients();
        campsList = window.HMCMS_DB.getCamps();
        filteredPatients = [...patientsList];
    }

    // -------------------------------------------------------------------------
    // 2. STATISTICS COUNTERS UPDATE
    // -------------------------------------------------------------------------
    function renderStatsCounters() {
        if (statsTotal) statsTotal.textContent = patientsList.length;

        // Verified includes: 'Verified', 'Completed', 'In Consultation'
        const verifiedCount = patientsList.filter(p => 
            p.status === 'Verified' || p.status === 'Completed' || p.status === 'In Consultation'
        ).length;
        if (statsVerified) statsVerified.textContent = verifiedCount;

        // Pending ID checks includes: 'Registered', 'Pending ID'
        const pendingCount = patientsList.filter(p => 
            p.status === 'Registered' || p.status === 'Pending ID'
        ).length;
        if (statsPending) statsPending.textContent = pendingCount;

        // Today's registrations matching "2026-07-19" (simulated today)
        const todayCount = patientsList.filter(p => p.registrationDate === '2026-07-19').length;
        if (statsToday) statsToday.textContent = todayCount;
    }

    // Populate camp dropdown options
    function initCampFilterOptions() {
        if (!campFilter) return;
        campFilter.innerHTML = '<option value="">All Camp Sites</option>';
        campsList.forEach(camp => {
            const opt = document.createElement('option');
            opt.value = camp.name;
            opt.textContent = camp.name;
            campFilter.appendChild(opt);
        });
    }

    // -------------------------------------------------------------------------
    // 3. TABLE POPULATION & RENDERING
    // -------------------------------------------------------------------------
    function renderDirectoryTable() {
        if (!tableBody) return;
        tableBody.innerHTML = '';

        // Apply filters
        applyFiltersAndSort();

        const totalRecords = filteredPatients.length;
        const totalPages = Math.ceil(totalRecords / patientsPerPage) || 1;

        if (currentPage > totalPages) currentPage = totalPages;

        const startIndex = (currentPage - 1) * patientsPerPage;
        const endIndex = Math.min(startIndex + patientsPerPage, totalRecords);
        const paginatedList = filteredPatients.slice(startIndex, endIndex);

        // Update pagination labels
        if (pagInfo) {
            pagInfo.textContent = totalRecords > 0
                ? `Showing ${startIndex + 1} to ${endIndex} of ${totalRecords} patient records`
                : 'No patients found matching the selected filters';
        }

        renderPaginationControls(totalPages);

        if (paginatedList.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-people-fill fs-2 mb-2 d-block text-muted"></i>
                        <span>No records found in database query.</span>
                    </td>
                </tr>
            `;
            return;
        }

        paginatedList.forEach(patient => {
            const tr = document.createElement('tr');
            
            // Status mapping
            let statusBadge = 'badge-custom-primary';
            if (patient.status === 'Verified' || patient.status === 'Completed') statusBadge = 'badge-custom-success';
            if (patient.status === 'In Triage' || patient.status === 'In Consultation') statusBadge = 'badge-custom-warning';
            if (patient.status === 'Pending ID') statusBadge = 'badge-custom-danger';

            tr.innerHTML = `
                <td><strong class="font-monospace text-secondary">${patient.id}</strong></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img class="avatar-table-mini border" src="${patient.photo}" alt="Attendee avatar">
                        <div>
                            <div class="fw-semibold text-primary">${patient.name}</div>
                            <span class="text-muted small">${patient.token}</span>
                        </div>
                    </div>
                </td>
                <td class="text-center">${patient.age} Yrs / ${patient.gender}</td>
                <td class="text-center"><span class="badge bg-light text-primary border">${patient.blood}</span></td>
                <td>
                    <span class="d-block small text-secondary"><i class="bi bi-telephone"></i> ${patient.phone}</span>
                    <span class="text-muted d-block" style="font-size: 11px;"><i class="bi bi-envelope"></i> ${patient.email}</span>
                </td>
                <td class="text-secondary small">${patient.camp}</td>
                <td class="small text-secondary">${patient.registrationDate}</td>
                <td class="text-center">
                    <span class="badge-custom ${statusBadge}">${patient.status}</span>
                </td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn-custom btn-custom-outline btn-custom-sm py-1 px-2 actions-dropdown-trigger" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-md">
                            <li><a class="dropdown-item view-patient-btn" href="#" data-id="${patient.id}"><i class="bi bi-eye"></i> View Profile</a></li>
                            <li><a class="dropdown-item edit-patient-btn" href="update-patient.php?id=${patient.id}"><i class="bi bi-pencil-square"></i> Edit Profile</a></li>
                            <li><a class="dropdown-item print-patient-btn" href="#" data-id="${patient.id}"><i class="bi bi-printer"></i> Print Card</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger delete-patient-btn" href="#" data-id="${patient.id}"><i class="bi bi-trash"></i> Delete Card</a></li>
                        </ul>
                    </div>
                </td>
            `;

            // Row click triggers View modal (excluding actions dropdown bubble)
            tr.addEventListener('click', function (e) {
                if (e.target.closest('.dropdown') || e.target.closest('.dropdown-menu')) return;
                viewPatientDetails(patient.id);
            });

            tableBody.appendChild(tr);
        });

        attachActionListeners();
    }

    function renderPaginationControls(totalPages) {
        if (!pagNav) return;
        pagNav.innerHTML = '';

        // Prev page
        const prev = document.createElement('button');
        prev.className = `pagination-btn ${currentPage === 1 ? 'disabled' : ''}`;
        prev.innerHTML = '<i class="bi bi-chevron-left"></i>';
        if (currentPage > 1) {
            prev.onclick = () => { currentPage--; renderDirectoryTable(); };
        }
        pagNav.appendChild(prev);

        // Numeric links
        for (let i = 1; i <= totalPages; i++) {
            // Render only current page, one before and one after, or pages 1 and total if listing > 5 pages
            if (totalPages > 5 && i !== 1 && i !== totalPages && Math.abs(i - currentPage) > 1) {
                if (i === 2 || i === totalPages - 1) {
                    const span = document.createElement('span');
                    span.textContent = '...';
                    span.className = 'mx-1 text-muted';
                    pagNav.appendChild(span);
                }
                continue;
            }

            const btn = document.createElement('button');
            btn.className = `pagination-btn ${currentPage === i ? 'active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => { currentPage = i; renderDirectoryTable(); };
            pagNav.appendChild(btn);
        }

        // Next page
        const next = document.createElement('button');
        next.className = `pagination-btn ${currentPage === totalPages ? 'disabled' : ''}`;
        next.innerHTML = '<i class="bi bi-chevron-right"></i>';
        if (currentPage < totalPages) {
            next.onclick = () => { currentPage++; renderDirectoryTable(); };
        }
        pagNav.appendChild(next);
    }

    // -------------------------------------------------------------------------
    // 4. FILTERING & SORTING COMPLIANCE
    // -------------------------------------------------------------------------
    function applyFiltersAndSort() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const camp = campFilter ? campFilter.value : '';
        const status = statusFilter ? statusFilter.value : '';
        const sort = sortBySelect ? sortBySelect.value : 'name-asc';

        // Filter arrays
        filteredPatients = patientsList.filter(p => {
            const matchesQuery = p.name.toLowerCase().includes(query) || 
                                 p.id.toLowerCase().includes(query) ||
                                 p.phone.toLowerCase().includes(query) ||
                                 p.token.toLowerCase().includes(query);
            
            const matchesCamp = camp === '' || p.camp === camp;
            const matchesStatus = status === '' || p.status === status;

            return matchesQuery && matchesCamp && matchesStatus;
        });

        // Sort arrays
        filteredPatients.sort((a, b) => {
            if (sort === 'name-asc') return a.name.localeCompare(b.name);
            if (sort === 'name-desc') return b.name.localeCompare(a.name);
            if (sort === 'id-asc') return a.id.localeCompare(b.id);
            if (sort === 'id-desc') return b.id.localeCompare(a.id);
            if (sort === 'reg-desc') return b.registrationDate.localeCompare(a.registrationDate);
            return 0;
        });
    }

    // -------------------------------------------------------------------------
    // 5. DETAIL VIEW & CRUD SIMULATION
    // -------------------------------------------------------------------------
    function attachActionListeners() {
        document.querySelectorAll('.view-patient-btn').forEach(btn => {
            btn.onclick = function (e) {
                e.preventDefault();
                viewPatientDetails(this.getAttribute('data-id'));
            };
        });

        document.querySelectorAll('.print-patient-btn').forEach(btn => {
            btn.onclick = function (e) {
                e.preventDefault();
                printPatientBadge(this.getAttribute('data-id'));
            };
        });

        document.querySelectorAll('.delete-patient-btn').forEach(btn => {
            btn.onclick = function (e) {
                e.preventDefault();
                confirmDeletePatient(this.getAttribute('data-id'));
            };
        });
    }

    function viewPatientDetails(patientId) {
        const patient = patientsList.find(p => p.id === patientId);
        if (!patient) return;

        const body = document.getElementById('viewPatientModalBody');
        const printBtn = document.getElementById('modal-btn-print-card');
        
        if (!body) return;

        let statusBadge = 'badge-custom-primary';
        if (patient.status === 'Verified' || patient.status === 'Completed') statusBadge = 'badge-custom-success';
        if (patient.status === 'In Triage' || patient.status === 'In Consultation') statusBadge = 'badge-custom-warning';
        if (patient.status === 'Pending ID') statusBadge = 'badge-custom-danger';

        body.innerHTML = `
            <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-3">
                <img src="${patient.photo}" alt="Portrait" class="rounded-circle border" style="width: 80px; height: 80px; object-fit: cover; border-width: 3px !important;">
                <div>
                    <span class="badge-custom ${statusBadge} mb-1">${patient.status}</span>
                    <h4 class="fw-bold text-primary m-0">${patient.name}</h4>
                    <span class="text-muted small">Patient ID: <strong>${patient.id}</strong> | Token: <strong>${patient.token}</strong></span>
                </div>
            </div>
            
            <div class="row g-3 small text-secondary">
                <div class="col-6">
                    <span class="d-block text-muted small">Age / Gender</span>
                    <strong class="text-secondary">${patient.age} Years — ${patient.gender}</strong>
                </div>
                <div class="col-6">
                    <span class="d-block text-muted small">Blood Group</span>
                    <strong class="text-primary">${patient.blood}</strong>
                </div>
                <div class="col-6 border-top pt-2">
                    <span class="d-block text-muted small">Contact Mobile</span>
                    <strong class="text-secondary">${patient.phone}</strong>
                </div>
                <div class="col-6 border-top pt-2">
                    <span class="d-block text-muted small">Email Address</span>
                    <strong class="text-secondary">${patient.email}</strong>
                </div>
                <div class="col-12 border-top pt-2">
                    <span class="d-block text-muted small">Residential Address</span>
                    <span class="text-secondary">${patient.address}</span>
                </div>
                
                <div class="col-12 border-top border-bottom py-2 bg-light-subtle rounded mt-2">
                    <h6 class="fw-semibold text-primary mb-2" style="font-size: 11px;"><i class="bi bi-telephone-outbound"></i> Emergency Contact:</h6>
                    <div class="row">
                        <div class="col-6">
                            <span class="text-muted d-block">Contact Name: <strong class="text-secondary">${patient.emergencyName}</strong></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Phone: <strong class="text-secondary">${patient.emergencyPhone}</strong> (${patient.emergencyRelation})</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-2">
                    <h6 class="fw-semibold text-primary mb-2" style="font-size: 11px;"><i class="bi bi-heart-pulse"></i> Medical Records:</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <span class="d-block text-muted small">Allergies</span>
                            <span class="text-danger fw-semibold small">${patient.allergies}</span>
                        </div>
                        <div class="col-6">
                            <span class="d-block text-muted small">Chronic Illnesses</span>
                            <span class="text-danger-emphasis fw-medium small">${patient.chronic}</span>
                        </div>
                        <div class="col-12">
                            <span class="d-block text-muted small">Current Medications</span>
                            <span class="text-secondary small">${patient.medications}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 border-top pt-2 mt-2">
                    <span class="d-block text-muted small">Assigned Camp</span>
                    <span class="text-primary-emphasis fw-medium small">${patient.camp}</span>
                </div>
                <div class="col-6 border-top pt-2 mt-2">
                    <span class="d-block text-muted small">Staff Physician</span>
                    <span class="text-primary-emphasis fw-medium small">${patient.doctor}</span>
                </div>
            </div>
        `;

        if (printBtn) {
            printBtn.onclick = function () {
                printPatientBadge(patientId);
            };
        }

        window.showModal('viewPatientModal');
    }

    function printPatientBadge(patientId) {
        const patient = patientsList.find(p => p.id === patientId);
        if (patient && window.showToast) {
            window.showToast('Intake Card Spooled', `Print job sent to thermal printer queue for ${patient.name}.`, 'success');
        }
    }

    function confirmDeletePatient(patientId) {
        const patient = patientsList.find(p => p.id === patientId);
        if (!patient) return;

        activePatientToDelete = patientId;
        const nameEl = document.getElementById('deletePatientName');
        if (nameEl) nameEl.textContent = patient.name;

        window.showModal('deletePatientModal');
    }

    const confirmDeleteBtn = document.getElementById('confirmDeletePatientBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.onclick = function () {
            if (!activePatientToDelete) return;

            const updatedList = patientsList.filter(p => p.id !== activePatientToDelete);
            window.HMCMS_DB.savePatients(updatedList);

            if (window.showToast) {
                window.showToast('Patient Deleted', 'The clinical record has been removed from this session.', 'danger');
            }

            window.hideModal('deletePatientModal');
            activePatientToDelete = null;

            currentPage = 1;
            loadData();
            renderStatsCounters();
            renderDirectoryTable();
        };
    }

    // -------------------------------------------------------------------------
    // 6. EXPORTS & FILTERS HANDLERS
    // -------------------------------------------------------------------------
    document.querySelectorAll('.export-action').forEach(item => {
        item.onclick = function (e) {
            e.preventDefault();
            const format = this.getAttribute('data-format').toUpperCase();
            if (window.showToast) {
                window.showToast(
                    'Export Initiated',
                    `Downloading patient directory dataset containing ${filteredPatients.length} records in ${format} format.`,
                    'success'
                );
            }
        };
    });

    if (btnRefresh) {
        btnRefresh.onclick = function () {
            loadData();
            renderStatsCounters();
            renderDirectoryTable();
            if (window.showToast) window.showToast('Directory Synced', 'Patient records directory refreshed.', 'info');
        };
    }

    // Bind filters
    if (searchInput) searchInput.addEventListener('input', () => { currentPage = 1; renderDirectoryTable(); });
    if (campFilter) campFilter.addEventListener('change', () => { currentPage = 1; renderDirectoryTable(); });
    if (statusFilter) statusFilter.addEventListener('change', () => { currentPage = 1; renderDirectoryTable(); });
    if (sortBySelect) sortBySelect.addEventListener('change', () => { currentPage = 1; renderDirectoryTable(); });

    // -------------------------------------------------------------------------
    // 7. INITIALIZE
    // -------------------------------------------------------------------------
    loadData();
    initCampFilterOptions();
    renderStatsCounters();
    renderDirectoryTable();
});
