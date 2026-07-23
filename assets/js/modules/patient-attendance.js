/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Patient Attendance Check-in Controller Script
 * 
 * Simulates daily camp check-ins via USB badge scanner scans, manual patient ID checkups,
 * triage priority queues, and checks out patients dynamically.
 */

document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------------------
    // 1. SELECTORS & STATE
    // -------------------------------------------------------------------------
    const attendanceForm = document.getElementById('attendance-entry-form');
    const patientIdInput = document.getElementById('attendance-patient-id');
    const btnFetchPatient = document.getElementById('btn-fetch-patient');
    
    // Scanned info label
    const nameDisplay = document.getElementById('attendance-patient-name-display');
    const scannedName = document.getElementById('scanned-patient-name');
    
    // Form fields
    const statusSelect = document.getElementById('attendance-status-select');
    const prioritySelect = document.getElementById('attendance-priority-select');
    const timeInput = document.getElementById('attendance-checkin-time');

    // QR scanner simulator selectors
    const btnTriggerQr = document.getElementById('btn-trigger-qr');
    const qrPlaceholder = document.getElementById('qr-camera-placeholder');
    const qrScanning = document.getElementById('qr-camera-scanning');

    // Table & Search
    const searchInput = document.getElementById('attendance-search');
    const tableBody = document.getElementById('attendance-table-body');

    let patientsList = [];
    let attendanceRoster = [];
    let locatedPatient = null;

    // Synchronize clock for intake timestamp
    function updateIntakeTime() {
        if (!timeInput) return;
        const now = new Date();
        timeInput.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    function loadData() {
        patientsList = window.HMCMS_DB.getPatients();
        attendanceRoster = window.HMCMS_DB.getAttendance();
    }

    // -------------------------------------------------------------------------
    // 2. SCAN & FETCH LOOKUPS
    // -------------------------------------------------------------------------
    if (btnTriggerQr) {
        btnTriggerQr.addEventListener('click', function () {
            // Toggle scanner camera loader
            if (qrPlaceholder) qrPlaceholder.style.display = 'none';
            if (qrScanning) qrScanning.style.display = 'block';

            setTimeout(() => {
                // Pick a random patient from the database who is not checked in
                const unregistered = patientsList.filter(p => p.status === 'Registered' || p.status === 'Verified');
                const randomPatient = unregistered.length > 0
                    ? unregistered[Math.floor(Math.random() * unregistered.length)]
                    : patientsList[Math.floor(Math.random() * patientsList.length)];
                
                if (randomPatient) {
                    patientIdInput.value = randomPatient.id;
                    lookupPatient(randomPatient.id);
                    if (window.showToast) window.showToast('Barcode Read', `Patient ID ${randomPatient.id} scanned successfully.`, 'success');
                }

                // Restore camera placeholder
                if (qrScanning) qrScanning.style.display = 'none';
                if (qrPlaceholder) qrPlaceholder.style.display = 'block';
            }, 1200);
        });
    }

    if (btnFetchPatient) {
        btnFetchPatient.addEventListener('click', function () {
            const idVal = patientIdInput.value.trim().toUpperCase();
            if (idVal === '') {
                if (window.showToast) window.showToast('Input Required', 'Please enter a Patient ID or Token first.', 'warning');
                return;
            }
            lookupPatient(idVal);
        });
    }

    function lookupPatient(idOrToken) {
        locatedPatient = patientsList.find(p => 
            p.id.toUpperCase() === idOrToken || 
            p.token.toUpperCase() === idOrToken ||
            p.token.replace('#', '').toUpperCase() === idOrToken
        );

        if (locatedPatient) {
            if (nameDisplay) nameDisplay.style.display = 'block';
            if (scannedName) scannedName.textContent = `${locatedPatient.name} (Age: ${locatedPatient.age})`;
            if (window.showToast) window.showToast('Record Located', `Matched: ${locatedPatient.name}.`, 'info');
        } else {
            locatedPatient = null;
            if (nameDisplay) nameDisplay.style.display = 'none';
            if (window.showToast) window.showToast('Lookup Failed', `No patient matched index ${idOrToken}.`, 'danger');
        }
    }

    // -------------------------------------------------------------------------
    // 3. MARK ATTENDANCE (Submit form)
    // -------------------------------------------------------------------------
    if (attendanceForm) {
        attendanceForm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!locatedPatient) {
                if (window.showToast) window.showToast('Selection Required', 'Perform a patient lookup before check-in.', 'warning');
                return;
            }

            // Check if patient is already checked in today
            const alreadyChecked = attendanceRoster.some(a => a.patientId === locatedPatient.id);
            if (alreadyChecked) {
                if (window.showToast) window.showToast('Already Checked In', `${locatedPatient.name} is already logged in today's attendance roster.`, 'warning');
                return;
            }

            // Create attendance item schema
            const newAttendance = {
                patientId: locatedPatient.id,
                patientName: locatedPatient.name,
                checkin: timeInput.value,
                checkout: '--',
                triagePriority: prioritySelect.value,
                vitalStatus: statusSelect.value
            };

            // Update patient status to 'In Triage' or 'Registered' in main DB
            locatedPatient.status = newAttendance.vitalStatus === 'Absent' ? 'Pending ID' : 'In Triage';
            window.HMCMS_DB.savePatients(patientsList);

            // Save to attendance DB
            attendanceRoster.unshift(newAttendance); // Add to top
            sessionStorage.setItem('hmcms_attendance', JSON.stringify(attendanceRoster));

            if (window.showToast) {
                window.showToast(
                    'Attendance Logged',
                    `Check-in logged for ${locatedPatient.name} (Triage: ${newAttendance.triagePriority}).`,
                    'success'
                );
            }

            // Reset form controls
            attendanceForm.reset();
            locatedPatient = null;
            if (nameDisplay) nameDisplay.style.display = 'none';
            
            updateIntakeTime();
            loadData();
            renderAttendanceTable();
        });
    }

    // -------------------------------------------------------------------------
    // 4. ROSTER TABLE RENDERING
    // -------------------------------------------------------------------------
    function renderAttendanceTable() {
        if (!tableBody) return;
        tableBody.innerHTML = '';

        const logs = window.HMCMS_DB.getAttendance();
        
        // Search filtering
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const filtered = logs.filter(log => 
            log.patientName.toLowerCase().includes(query) || 
            log.patientId.toLowerCase().includes(query)
        );

        if (filtered.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-calendar2-x fs-2 mb-2 d-block text-muted"></i>
                        <span>No patient check-ins recorded today.</span>
                    </td>
                </tr>
            `;
            return;
        }

        filtered.forEach(log => {
            const tr = document.createElement('tr');
            
            // Triage priority color code mapping
            let priorityBadge = 'badge-custom-primary';
            if (log.triagePriority === 'High') priorityBadge = 'badge-custom-danger';
            if (log.triagePriority === 'Medium') priorityBadge = 'badge-custom-warning';
            
            // Attendance checked status badge
            let statusBadge = 'badge-custom-success';
            if (log.vitalStatus === 'Late') statusBadge = 'badge-custom-warning';
            if (log.vitalStatus === 'Absent') statusBadge = 'badge-custom-danger';

            tr.innerHTML = `
                <td>
                    <div class="fw-semibold text-primary">${log.patientName}</div>
                    <span class="text-muted small">${log.patientId}</span>
                </td>
                <td>${log.checkin}</td>
                <td class="checkout-cell text-secondary">${log.checkout}</td>
                <td class="text-center">
                    <span class="badge-custom ${priorityBadge}">${log.triagePriority}</span>
                </td>
                <td class="text-center">
                    <span class="badge-custom ${statusBadge}">${log.vitalStatus}</span>
                </td>
                <td class="text-end">
                    ${log.checkout === '--' 
                        ? `<button class="btn-custom btn-custom-sm btn-custom-outline checkout-btn" data-id="${log.patientId}">Check-out</button>` 
                        : '<span class="text-muted small uppercase fw-medium"><i class="bi bi-check-all text-success"></i> Dispatched</span>'
                    }
                </td>
            `;

            // Row buttons
            const checkoutBtn = tr.querySelector('.checkout-btn');
            if (checkoutBtn) {
                checkoutBtn.onclick = function (e) {
                    e.stopPropagation();
                    checkoutPatient(this.getAttribute('data-id'));
                };
            }

            tableBody.appendChild(tr);
        });
    }

    function checkoutPatient(patientId) {
        const logs = window.HMCMS_DB.getAttendance();
        const log = logs.find(l => l.patientId === patientId);
        
        if (log) {
            const now = new Date();
            log.checkout = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            
            // Save to storage
            sessionStorage.setItem('hmcms_attendance', JSON.stringify(logs));
            
            // Update patient state to completed in main DB
            const patients = window.HMCMS_DB.getPatients();
            const pat = patients.find(p => p.id === patientId);
            if (pat) {
                pat.status = 'Completed';
                window.HMCMS_DB.savePatients(patients);
            }

            if (window.showToast) {
                window.showToast('Patient Checked Out', `Checkout timestamp logged for ${log.patientName}.`, 'info');
            }

            loadData();
            renderAttendanceTable();
        }
    }

    // Filter table input search
    if (searchInput) {
        searchInput.addEventListener('input', renderAttendanceTable);
    }

    // -------------------------------------------------------------------------
    // 5. INITIALIZE
    // -------------------------------------------------------------------------
    loadData();
    updateIntakeTime();
    renderAttendanceTable();
});
