/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Centralized Dashboard Controller Module (dashboard.js)
 * 
 * Synchronizes metrics displays (Total Registered, Verified, Checked-in, Active Camps),
 * handles widgets click routing, filters camps lists, and pops CRUD forms modals.
 */

document.addEventListener('DOMContentLoaded', function () {
    const isDashboardPage = document.getElementById('dashboard-total-patients') !== null;
    if (!isDashboardPage) return;

    // -------------------------------------------------------------------------
    // SELECTORS & STATE
    // -------------------------------------------------------------------------
    const statsTotal = document.getElementById('dashboard-total-patients');
    const statsVerified = document.getElementById('dashboard-verified-patients');
    const statsCamps = document.getElementById('dashboard-active-camps');
    const statsCheckins = document.getElementById('dashboard-checkins-today');

    const trendVerified = document.getElementById('dashboard-verified-trend');
    const trendCamps = document.getElementById('dashboard-camps-trend');
    const trendCheckins = document.getElementById('dashboard-checkins-trend');

    const campSearch = document.getElementById('camp-search');
    const campTableBody = document.getElementById('camp-table-body');
    const pagInfo = document.querySelector('.pagination-info');
    const pagNav = document.querySelector('.pagination-nav');

    let patientsList = [];
    let campsList = [];
    let filteredCamps = [];
    let activeCampToDelete = null;

    let currentPage = 1;
    const campsPerPage = 5;

    function refreshData() {
        patientsList = window.HMCMS_DB.getPatients();
        campsList = window.HMCMS_DB.getCamps();
        filteredCamps = [...campsList];

        renderStats();
        renderCampTable();
    }

    // -------------------------------------------------------------------------
    // 2. METRIC COUNTERS SYNC
    // -------------------------------------------------------------------------
    function renderStats() {
        if (statsTotal) statsTotal.textContent = patientsList.length;

        const verified = patientsList.filter(p => 
            p.status === 'Verified' || p.status === 'Completed' || p.status === 'In Consultation'
        ).length;
        const verifiedPct = patientsList.length > 0 ? Math.round((verified / patientsList.length) * 100) : 0;
        if (statsVerified) statsVerified.textContent = `${verifiedPct}%`;
        if (trendVerified) {
            trendVerified.innerHTML = `<i class="bi bi-patch-check-fill"></i> ${verified} of ${patientsList.length} verified`;
        }

        const activeCamps = campsList.filter(c => c.status === 'Active');
        if (statsCamps) statsCamps.textContent = String(activeCamps.length).padStart(2, '0');
        if (trendCamps) {
            const regions = [...new Set(activeCamps.map(c => c.region.split(',')[0]))];
            trendCamps.innerHTML = `<i class="bi bi-geo-alt-fill text-warning"></i> ${regions.join(', ') || 'No active camps'}`;
        }

        const checkins = window.HMCMS_DB.getAttendance();
        if (statsCheckins) statsCheckins.textContent = checkins.length;
        if (trendCheckins) {
            const triageCount = patientsList.filter(p => p.status === 'In Triage' || p.status === 'In Consultation' || p.status === 'Waiting').length;
            trendCheckins.innerHTML = `<i class="bi bi-activity"></i> Active triage queue size: ${triageCount}`;
        }
    }

    // -------------------------------------------------------------------------
    // 3. STATS CARDS GRID ROUTING
    // -------------------------------------------------------------------------
    document.getElementById('widget-total-patients').onclick = () => window.location.href = 'patient-list.php';
    document.getElementById('widget-identity-verified').onclick = () => window.location.href = 'patient-verification.php';
    document.getElementById('widget-active-camps').onclick = () => {
        sessionStorage.removeItem('hmcms_selected_camp_id');
        window.location.href = 'camp-assistance.php';
    };
    document.getElementById('widget-checkins-today').onclick = () => window.location.href = 'patient-attendance.php';

    // -------------------------------------------------------------------------
    // 4. CAMPS DATA TABLE
    // -------------------------------------------------------------------------
    function renderCampTable() {
        if (!campTableBody) return;
        campTableBody.innerHTML = '';

        // Apply search query
        const query = campSearch ? campSearch.value.toLowerCase().trim() : '';
        filteredCamps = campsList.filter(c => 
            c.name.toLowerCase().includes(query) || c.region.toLowerCase().includes(query) || c.coordinator.toLowerCase().includes(query)
        );

        const total = filteredCamps.length;
        const totalPages = Math.ceil(total / campsPerPage) || 1;
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * campsPerPage;
        const end = Math.min(start + campsPerPage, total);
        const slice = filteredCamps.slice(start, end);

        if (pagInfo) {
            pagInfo.textContent = total > 0 
                ? `Showing ${start + 1} to ${end} of ${total} active medical camps`
                : 'No camps matched search parameters';
        }

        renderPaginationControls(totalPages);

        if (slice.length === 0) {
            campTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No camps located</td></tr>';
            return;
        }

        slice.forEach(camp => {
            const tr = document.createElement('tr');
            const badgeClass = window.GlobalBadges.getClass(camp.status);

            tr.innerHTML = `
                <td>
                    <div class="fw-semibold text-primary">${camp.name}</div>
                    <span class="text-muted small">${camp.region}</span>
                </td>
                <td>${camp.coordinator}</td>
                <td>${camp.date}</td>
                <td class="text-center">
                    <span class="badge-custom ${badgeClass}">${camp.status}</span>
                </td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn-custom btn-custom-outline btn-custom-sm py-1 px-2 actions-dropdown-trigger" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-md">
                            <li><a class="dropdown-item view-btn" href="#" data-id="${camp.id}"><i class="bi bi-eye"></i> View Details</a></li>
                            <li><a class="dropdown-item edit-btn" href="#" data-id="${camp.id}"><i class="bi bi-pencil-square"></i> Edit Details</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger delete-btn" href="#" data-id="${camp.id}"><i class="bi bi-x-circle"></i> Cancel Camp</a></li>
                        </ul>
                    </div>
                </td>
            `;

            tr.onclick = (e) => {
                if (e.target.closest('.dropdown') || e.target.closest('.dropdown-menu')) return;
                viewCampDetails(camp.id);
            };

            tr.querySelector('.view-btn').onclick = (e) => { e.preventDefault(); viewCampDetails(camp.id); };
            tr.querySelector('.edit-btn').onclick = (e) => { e.preventDefault(); editCampDetails(camp.id); };
            tr.querySelector('.delete-btn').onclick = (e) => { e.preventDefault(); confirmDeleteCamp(camp.id); };

            campTableBody.appendChild(tr);
        });
    }

    function renderPaginationControls(totalPages) {
        if (!pagNav) return;
        pagNav.innerHTML = '';

        const prev = document.createElement('button');
        prev.className = `pagination-btn ${currentPage === 1 ? 'disabled' : ''}`;
        prev.innerHTML = '<i class="bi bi-chevron-left"></i>';
        if (currentPage > 1) prev.onclick = () => { currentPage--; renderCampTable(); };
        pagNav.appendChild(prev);

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.className = `pagination-btn ${currentPage === i ? 'active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => { currentPage = i; renderCampTable(); };
            pagNav.appendChild(btn);
        }

        const next = document.createElement('button');
        next.className = `pagination-btn ${currentPage === totalPages ? 'disabled' : ''}`;
        next.innerHTML = '<i class="bi bi-chevron-right"></i>';
        if (currentPage < totalPages) next.onclick = () => { currentPage++; renderCampTable(); };
        pagNav.appendChild(next);
    }

    // Modal view
    function viewCampDetails(campId) {
        const camp = campsList.find(c => c.id === campId);
        if (!camp) return;

        const body = document.getElementById('viewCampModalBody');
        if (!body) return;

        const badgeClass = window.GlobalBadges.getClass(camp.status);
        body.innerHTML = `
            <div class="row g-3">
                <div class="col-6">
                    <span class="d-block text-muted small uppercase">Camp ID</span>
                    <strong class="text-primary">${camp.id}</strong>
                </div>
                <div class="col-6 text-end">
                    <span class="badge-custom ${badgeClass}">${camp.status}</span>
                </div>
                <div class="col-12 border-bottom pb-2">
                    <span class="d-block text-muted small uppercase">Camp Clinic Name</span>
                    <h5 class="text-primary m-0 fw-semibold">${camp.name}</h5>
                </div>
                <div class="col-12 border-bottom pb-2">
                    <span class="d-block text-muted small uppercase">Operating Location Region</span>
                    <span class="text-secondary"><i class="bi bi-geo-alt-fill text-accent"></i> ${camp.region}</span>
                </div>
                <div class="col-6">
                    <span class="d-block text-muted small">Camp Coordinator</span>
                    <strong class="text-secondary">${camp.coordinator}</strong>
                </div>
                <div class="col-6">
                    <span class="d-block text-muted small">Chief Consultant Medical Officer</span>
                    <strong class="text-secondary">${camp.doctor}</strong>
                </div>
                <div class="col-12 border-top border-bottom py-2 bg-light-subtle rounded mt-2">
                    <div class="row">
                        <div class="col-6">
                            <span class="d-block text-muted small">Calendar Dates</span>
                            <span class="text-primary-emphasis fw-medium small"><i class="bi bi-calendar3"></i> ${camp.date}</span>
                        </div>
                        <div class="col-6">
                            <span class="d-block text-muted small">Active Shift Hours</span>
                            <span class="text-primary-emphasis fw-medium small"><i class="bi bi-clock"></i> ${camp.startTime} - ${camp.endTime}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 mt-3">
                    <span class="d-block text-muted small">Expected Registration Capacity</span>
                    <strong class="text-secondary">${camp.expectedPatients} patients</strong>
                </div>
                <div class="col-6 mt-3">
                    <span class="d-block text-muted small">Checked-in Patient Count</span>
                    <strong class="text-secondary">${camp.currentPatients} patients</strong>
                </div>
            </div>
        `;

        window.showModal('viewCampModal');
    }

    // Modal Edit
    function editCampDetails(campId) {
        const camp = campsList.find(c => c.id === campId);
        if (!camp) return;

        const body = document.getElementById('editCampModalBody');
        const form = document.getElementById('editCampForm');
        if (!body || !form) return;

        body.innerHTML = `
            <input type="hidden" name="id" value="${camp.id}">
            <div class="form-group-custom">
                <label class="form-label-custom">Camp / Clinic Outreach Name</label>
                <input type="text" class="form-control-custom" name="name" value="${camp.name}" required>
            </div>
            <div class="form-group-custom mt-3">
                <label class="form-label-custom">Region Location</label>
                <input type="text" class="form-control-custom" name="region" value="${camp.region}" required>
            </div>
            <div class="row mt-3">
                <div class="col-6">
                    <label class="form-label-custom">Camp Coordinator</label>
                    <input type="text" class="form-control-custom" name="coordinator" value="${camp.coordinator}" required>
                </div>
                <div class="col-6">
                    <label class="form-label-custom">Staff Doctor</label>
                    <input type="text" class="form-control-custom" name="doctor" value="${camp.doctor}" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-6">
                    <label class="form-label-custom">Expected Capacity</label>
                    <input type="number" class="form-control-custom" name="expectedPatients" value="${camp.expectedPatients}" required>
                </div>
                <div class="col-6">
                    <label class="form-label-custom">Camp Status</label>
                    <select class="form-control-custom" name="status" style="height: 38px;">
                        <option value="Active" ${camp.status === 'Active' ? 'selected' : ''}>Active</option>
                        <option value="Scheduled" ${camp.status === 'Scheduled' ? 'selected' : ''}>Scheduled</option>
                        <option value="Completed" ${camp.status === 'Completed' ? 'selected' : ''}>Completed</option>
                    </select>
                </div>
            </div>
        `;

        form.onsubmit = function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            
            camp.name = formData.get('name');
            camp.region = formData.get('region');
            camp.coordinator = formData.get('coordinator');
            camp.doctor = formData.get('doctor');
            camp.expectedPatients = parseInt(formData.get('expectedPatients'));
            camp.status = formData.get('status');

            window.HMCMS_DB.saveCamps(campsList);
            window.hideModal('editCampModal');
            
            window.addSystemNotification(
                'Camp',
                'Camp Profile Updated',
                `Operational details modified for camp site ${camp.name}.`,
                'success'
            );

            if (window.showToast) window.showToast('Camp Updated', `${camp.name} details saved.`, 'success');
            refreshData();
        };

        window.showModal('editCampModal');
    }

    // Modal Delete
    function confirmDeleteCamp(campId) {
        const camp = campsList.find(c => c.id === campId);
        if (!camp) return;

        activeCampToDelete = campId;
        const nameEl = document.getElementById('deleteCampName');
        if (nameEl) nameEl.textContent = camp.name;

        window.showModal('deleteCampModal');
    }

    const confirmDeleteBtn = document.getElementById('confirmDeleteCampBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.onclick = function () {
            if (!activeCampToDelete) return;
            const updated = campsList.filter(c => c.id !== activeCampToDelete);
            window.HMCMS_DB.saveCamps(updated);
            
            window.addSystemNotification(
                'Camp',
                'Camp Site Cancelled',
                `Administrative alert: Medical outreach camp cancelled.`,
                'danger'
            );

            if (window.showToast) window.showToast('Camp Cancelled', 'Record removed from directory.', 'danger');
            
            window.hideModal('deleteCampModal');
            activeCampToDelete = null;
            refreshData();
        };
    }

    if (campSearch) campSearch.addEventListener('input', () => { currentPage = 1; renderCampTable(); });

    refreshData();
});
