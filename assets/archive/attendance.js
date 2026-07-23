/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Centralized Daily Attendance & Check-in Module (attendance.js)
 * 
 * Manages daily patient camp check-ins, barcode scans simulators, 
 * triage priority levels, checkout logging, and session database audits.
 */

document.addEventListener('DOMContentLoaded', function () {
    const isAttendancePage = document.getElementById('attendance-entry-form') !== null;
    if (!isAttendancePage) return;

    // -------------------------------------------------------------------------
    // SELECTORS & STATE
    // -------------------------------------------------------------------------
    const form = document.getElementById('attendance-entry-form');
    const patientIdInput = document.getElementById('attendance-patient-id');
    const btnFetch = document.getElementById('btn-fetch-patient');
    const nameDisplay = document.getElementById('attendance-patient-name-display');
    const scannedName = document.getElementById('scanned-patient-name');

    const statusSelect = document.getElementById('attendance-status-select');
    const prioritySelect = document.getElementById('attendance-priority-select');
    const timeInput = document.getElementById('attendance-checkin-time');
    const checkoutInput = document.getElementById('attendance-checkout-time');

    const btnTriggerQr = document.getElementById('btn-trigger-qr');
    const qrPlaceholder = document.getElementById('qr-camera-placeholder');
    const qrScanning = document.getElementById('qr-camera-scanning');

    const searchInput = document.getElementById('attendance-search');
    const tableBody = document.getElementById('attendance-table-body');

    let patientsList = [];
    let attendanceList = [];
    let activePatient = null;
    let isEditMode = false;
    let activeAttendanceRecord = null;

    function refreshClock() {
        if (!timeInput) return;
        const now = new Date();
        timeInput.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    function refreshData() {
        patientsList = window.HMCMS_DB.getPatients();
        attendanceList = window.HMCMS_DB.getAttendance();
        renderTable();

        // Auto load patient from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const paramId = urlParams.get('id');
        if (paramId) {
            if (patientIdInput) patientIdInput.value = paramId;
            lookupPatient(paramId);
        }
    }

    // QR scanner simulator
    if (btnTriggerQr) {
        btnTriggerQr.onclick = () => {
            if (qrPlaceholder) qrPlaceholder.style.display = 'none';
            if (qrScanning) qrScanning.style.display = 'block';

            setTimeout(() => {
                // Find verified patients who are not checked in yet
                const verifiedUnchecked = patientsList.filter(p => 
                    p.status === 'Verified' && !attendanceList.some(a => a.patientId === p.id)
                );

                const target = verifiedUnchecked.length > 0 
                    ? verifiedUnchecked[Math.floor(Math.random() * verifiedUnchecked.length)]
                    : patientsList[Math.floor(Math.random() * patientsList.length)];

                if (target) {
                    patientIdInput.value = target.id;
                    lookupPatient(target.id);
                    if (window.showToast) window.showToast('Barcode Scanned', `Scanned Patient ID ${target.id} badge.`, 'success');
                }

                if (qrScanning) qrScanning.style.display = 'none';
                if (qrPlaceholder) qrPlaceholder.style.display = 'block';
            }, 1200);
        };
    }

    if (btnFetch) {
        btnFetch.onclick = () => {
            const val = patientIdInput.value.trim().toUpperCase();
            if (val === '') {
                if (window.showToast) window.showToast('Input Required', 'Please enter a Patient ID or Token.', 'warning');
                return;
            }
            lookupPatient(val);
        };
    }

    function lookupPatient(searchVal) {
        const val = searchVal.toUpperCase().trim();
        const scannedCamp = document.getElementById('scanned-patient-camp');
        const scannedStatus = document.getElementById('scanned-patient-status');
        const scannedPrevAtt = document.getElementById('scanned-patient-prev-attendance');
        const notFoundWarning = document.getElementById('attendance-patient-not-found');
        const btnSubmit = document.getElementById('btn-mark-attendance');

        // Search by ID, Token, Name, or Phone
        const found = patientsList.find(p => 
            (p.id && p.id.toUpperCase() === val) ||
            (p.token && p.token.toUpperCase() === val) ||
            (p.token && p.token.replace('#', '').toUpperCase() === val) ||
            (p.name && p.name.toUpperCase().includes(val)) ||
            (p.phone && p.phone.toUpperCase() === val)
        );

        if (found) {
            activePatient = found;
            if (notFoundWarning) notFoundWarning.style.display = 'none';
            if (nameDisplay) nameDisplay.style.display = 'block';
            
            if (scannedName) scannedName.textContent = `${found.name} (Age: ${found.age} | ${found.gender})`;
            if (scannedCamp) scannedCamp.textContent = found.camp || 'General Camp';
            
            if (scannedStatus) {
                scannedStatus.textContent = found.status;
                scannedStatus.className = 'badge bg-' + (found.status === 'Verified' ? 'success' : (found.status === 'Rejected' ? 'danger' : 'secondary'));
            }

            // Check if patient already checked in today
            const record = attendanceList.find(a => a.patientId === found.id);
            if (record) {
                isEditMode = true;
                activeAttendanceRecord = record;
                if (btnSubmit) btnSubmit.innerHTML = '<i class="bi bi-pencil-square"></i> Update Attendance';
                
                // Populate inputs with existing check-in data
                if (statusSelect) statusSelect.value = record.vitalStatus;
                if (prioritySelect) prioritySelect.value = record.triagePriority || 'Low';
                if (timeInput) timeInput.value = record.checkin;
                if (checkoutInput) checkoutInput.value = record.checkout;

                if (scannedPrevAtt) {
                    scannedPrevAtt.innerHTML = `<span class="badge bg-info text-white mb-2 d-inline-block">Attendance Record Loaded</span><br>` +
                        `<span class="badge bg-light border text-dark mt-1 d-inline-block">Check-in: ${record.checkin} | Check-out: ${record.checkout} | Priority: ${record.triagePriority || 'Low'}</span>`;
                }
                
                if (window.showToast) window.showToast('Attendance Loaded', 'Attendance Record Loaded', 'success');
            } else {
                isEditMode = false;
                activeAttendanceRecord = null;
                if (btnSubmit) btnSubmit.innerHTML = '<i class="bi bi-calendar2-plus"></i> Mark Camp Attendance';
                
                if (statusSelect) statusSelect.value = 'Present';
                if (prioritySelect) prioritySelect.value = 'Low';
                refreshClock();
                if (checkoutInput) checkoutInput.value = '--';

                if (scannedPrevAtt) {
                    scannedPrevAtt.innerHTML = '<span class="text-muted small">No check-ins recorded today</span>';
                }
                
                if (window.showToast) window.showToast('Record Located', `Ready to check-in ${found.name}.`, 'info');
            }
        } else {
            activePatient = null;
            isEditMode = false;
            activeAttendanceRecord = null;
            if (btnSubmit) btnSubmit.innerHTML = '<i class="bi bi-calendar2-plus"></i> Mark Camp Attendance';
            if (nameDisplay) nameDisplay.style.display = 'none';
            if (notFoundWarning) notFoundWarning.style.display = 'block';
            if (window.showToast) window.showToast('Lookup Failed', `No patient matched "${searchVal}".`, 'danger');
        }
    }

    // Submit form
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!activePatient) {
                const val = patientIdInput ? patientIdInput.value.trim() : '';
                if (val !== '') {
                    lookupPatient(val);
                }
                if (!activePatient) {
                    if (window.showToast) window.showToast('Select Patient', 'Please search and select a patient record first.', 'warning');
                    return;
                }
            }

            if (isEditMode) {
                const checkoutVal = checkoutInput ? checkoutInput.value.trim() : '--';
                const ok = window.HMCMS_DB.updateAttendance(activePatient.id, statusSelect.value, prioritySelect.value, timeInput.value, checkoutVal);
                if (ok) {
                    // Update frontend state cache
                    const idx = attendanceList.findIndex(a => a.patientId === activePatient.id);
                    if (idx !== -1) {
                        attendanceList[idx].vitalStatus = statusSelect.value;
                        attendanceList[idx].triagePriority = prioritySelect.value;
                        attendanceList[idx].checkin = timeInput.value;
                        attendanceList[idx].checkout = checkoutVal;
                    }
                    sessionStorage.setItem('hmcms_attendance', JSON.stringify(attendanceList));

                    // Update main status to 'Completed' or 'In Triage' depending on checkout
                    if (checkoutVal && checkoutVal !== '--') {
                        activePatient.status = 'Completed';
                    } else {
                        activePatient.status = 'In Triage';
                    }
                    activePatient.triagePriority = prioritySelect.value;
                    window.HMCMS_DB.savePatients(patientsList);

                    window.addSystemNotification(
                        'Attendance',
                        'Attendance Updated',
                        `Modified check-in: ${activePatient.name} (ID: ${activePatient.id}). Status set to ${statusSelect.value}, Priority to ${prioritySelect.value}.`,
                        'info'
                    );

                    if (window.showToast) window.showToast('Attendance Updated', 'Attendance Updated Successfully', 'success');
                } else {
                    if (window.showToast) window.showToast('Update Failed', 'Failed to update attendance record in database.', 'danger');
                    return;
                }
            } else {
                // Verify not already in attendance log
                if (attendanceList.some(a => a.patientId === activePatient.id)) {
                    if (window.showToast) window.showToast('Already checked-in', `${activePatient.name} is already checked in.`, 'warning');
                    return;
                }

                const checkoutVal = checkoutInput ? checkoutInput.value.trim() : '--';
                const item = {
                    patientId: activePatient.id,
                    patientName: activePatient.name,
                    checkin: timeInput.value,
                    checkout: checkoutVal,
                    triagePriority: prioritySelect.value,
                    vitalStatus: statusSelect.value
                };

                // Save to attendance database
                attendanceList.unshift(item);
                sessionStorage.setItem('hmcms_attendance', JSON.stringify(attendanceList));

                // Update main status to 'In Triage'
                activePatient.status = 'In Triage';
                activePatient.triagePriority = prioritySelect.value;
                window.HMCMS_DB.savePatients(patientsList);

                // Log Alert
                window.addSystemNotification(
                    'Attendance',
                    'Attendance Checked In',
                    `Marked Present: ${activePatient.name} (ID: ${activePatient.id}) checked in. Priority set to ${prioritySelect.value}.`,
                    'success'
                );

                if (window.showToast) window.showToast('Attendance Marked', `${activePatient.name} checked in successfully.`, 'success');
            }

            // Reset form details
            form.reset();
            activePatient = null;
            isEditMode = false;
            activeAttendanceRecord = null;
            const btnSubmit = document.getElementById('btn-mark-attendance');
            if (btnSubmit) btnSubmit.innerHTML = '<i class="bi bi-calendar2-plus"></i> Mark Camp Attendance';
            if (nameDisplay) nameDisplay.style.display = 'none';
            if (checkoutInput) checkoutInput.value = '--';

            refreshClock();
            refreshData();
        });
    }

    function renderTable() {
        if (!tableBody) return;
        tableBody.innerHTML = '';

        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const filtered = attendanceList.filter(log => 
            log.patientName.toLowerCase().includes(query) || log.patientId.toLowerCase().includes(query)
        );

        if (filtered.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No attendance logs matched search criteria</td></tr>';
            return;
        }

        filtered.forEach(log => {
            const tr = document.createElement('tr');
            
            let priorityClass = 'badge-custom-primary';
            if (log.triagePriority === 'High' || log.triagePriority === 'Emergency') priorityClass = 'badge-custom-danger';
            if (log.triagePriority === 'Medium') priorityClass = 'badge-custom-warning';
            
            const statusClass = window.GlobalBadges.getClass(log.vitalStatus);

            tr.innerHTML = `
                <td>
                    <div class="fw-semibold text-primary">${log.patientName}</div>
                    <span class="text-muted small">${log.patientId}</span>
                </td>
                <td>${log.checkin}</td>
                <td class="checkout-cell text-secondary">${log.checkout}</td>
                <td class="text-center">
                    <span class="badge-custom ${priorityClass}">${log.triagePriority}</span>
                </td>
                <td class="text-center">
                    <span class="badge-custom ${statusClass}">${log.vitalStatus}</span>
                </td>
                <td class="text-end">
                    ${log.checkout === '--'
                        ? `<button class="btn-custom btn-custom-sm btn-custom-outline checkout-btn" data-id="${log.patientId}">Check-out</button>`
                        : '<span class="text-success small fw-semibold"><i class="bi bi-check-all"></i> Completed</span>'
                    }
                </td>
            `;

            const checkOutBtn = tr.querySelector('.checkout-btn');
            if (checkOutBtn) {
                checkOutBtn.onclick = () => {
                    checkOutPatient(log.patientId);
                };
            }

            tableBody.appendChild(tr);
        });
    }

    function checkOutPatient(patientId) {
        const log = attendanceList.find(a => a.patientId === patientId);
        if (log) {
            const now = new Date();
            log.checkout = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            
            // Save to attendance DB
            sessionStorage.setItem('hmcms_attendance', JSON.stringify(attendanceList));

            // Set main patient status to 'Completed'
            const pat = patientsList.find(p => p.id === patientId);
            if (pat) {
                pat.status = 'Completed';
                window.HMCMS_DB.savePatients(patientsList);
            }

            // Log Alert
            window.addSystemNotification(
                'Attendance',
                'Attendance Checked Out',
                `Dispatched: ${log.patientName} completed triage and checked out.`,
                'info'
            );

            if (window.showToast) window.showToast('Check-out log saved', `Dispatched patient ${log.patientName}.`, 'info');
            refreshData();
        }
    }

    if (searchInput) searchInput.addEventListener('input', renderTable);

    refreshClock();
    refreshData();
});
