/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Centralized Camp Assistance Module (camp.js)
 * 
 * Manages active queues, triage priorities, handles Call Next and Complete actions,
 * updates medicine inventory stocks, dispatches emergencies, and simulates queue flows.
 */

document.addEventListener('DOMContentLoaded', function () {
    const isCampPage = document.getElementById('camp-patient-queue-table') !== null;
    if (!isCampPage) return;

    // -------------------------------------------------------------------------
    // SELECTORS & STATE
    // -------------------------------------------------------------------------
    const queueTableBody = document.getElementById('camp-patient-queue-table');
    const medicineTableBody = document.getElementById('camp-medicine-inventory-table');
    const emergencyList = document.getElementById('camp-emergency-list');

    const queueBadge = document.getElementById('queue-count-badge');
    const queueSummary = document.getElementById('camp-queue-summary');
    const btnRestockAll = document.getElementById('btn-restock-all');

    let patientsList = [];
    let activeCamp = null;
    let queuePage = 1;
    const queueLimit = 10;
    let campInventory = [];

    function loadCampInventory() {
        if (!activeCamp) return;
        try {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `api.php?action=get_inventory&camp_id=${activeCamp.id}`, false); // Synchronous GET
            xhr.send();
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                campInventory = res.success ? res.data : [];
            }
        } catch (e) {
            console.error(e);
            campInventory = [];
        }
    }

    function refreshData() {
        patientsList = window.HMCMS_DB.getPatients();
        const activeCamps = window.HMCMS_DB.getCamps().filter(c => c.status === 'Active');
        
        // Select dropdown
        const selector = document.getElementById('camp-active-selector');
        if (selector) {
            if (selector.children.length === 0) {
                selector.innerHTML = '';
                activeCamps.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = `${c.name} (${c.region}) — ${c.doctor} — ${c.date}`;
                    selector.appendChild(opt);
                });
                
                // Handle session storage selection
                const storedId = sessionStorage.getItem('hmcms_selected_camp_id');
                if (storedId && activeCamps.some(c => String(c.id) === String(storedId))) {
                    selector.value = storedId;
                } else if (activeCamps.length > 0) {
                    selector.value = activeCamps[0].id;
                    sessionStorage.setItem('hmcms_selected_camp_id', activeCamps[0].id);
                }
                
                selector.onchange = function () {
                    sessionStorage.setItem('hmcms_selected_camp_id', this.value);
                    refreshData();
                };
            } else {
                // Ensure dropdown selection aligns with stored state
                const storedId = sessionStorage.getItem('hmcms_selected_camp_id');
                if (storedId && selector.value !== storedId) {
                    selector.value = storedId;
                }
            }
        }

        const selectedId = sessionStorage.getItem('hmcms_selected_camp_id') || (activeCamps[0] ? activeCamps[0].id : null);
        if (window.lastActiveCampId !== selectedId) {
            queuePage = 1;
            window.lastActiveCampId = selectedId;
        }
        activeCamp = activeCamps.find(c => String(c.id) === String(selectedId)) || activeCamps[0] || window.HMCMS_DB.getCamps()[0];
        
        loadCampInventory();
        
        renderCampMetrics();
        renderQueue();
        renderMedicines();
        renderEmergencies();
    }

    function renderCampMetrics() {
        if (!activeCamp) return;

        // Today's Camp Site
        const siteTitleEl = document.getElementById('camp-active-site-title');
        const locationEl = document.getElementById('camp-active-location');
        if (siteTitleEl) siteTitleEl.textContent = activeCamp.name;
        if (locationEl) locationEl.innerHTML = `<i class="bi bi-geo-alt-fill text-warning"></i> ${activeCamp.region}`;

        // Assigned Doctor
        const doctorEl = document.getElementById('camp-active-doctor');
        const doctorDeptEl = document.getElementById('camp-active-doctor-dept');
        if (doctorEl) doctorEl.textContent = activeCamp.doctor;
        if (doctorDeptEl) {
            doctorDeptEl.innerHTML = `<i class="bi bi-person-badge-fill text-accent"></i> ${activeCamp.doctor === 'Dr. Aditi Sharma' ? 'Station 1 (Cardiology & General)' : 'Station 2 (General Physician)'}`;
        }

        // Volunteers list (set dynamic coordinator from database)
        const volTitleEl = document.getElementById('camp-active-volunteers-title');
        const volPrimaryEl = document.getElementById('camp-active-volunteer-primary');
        const volSecondaryEl = document.getElementById('camp-active-volunteers-secondary');
        if (volTitleEl) volTitleEl.textContent = 'Camp Coordinator';
        if (volPrimaryEl) volPrimaryEl.textContent = activeCamp.coordinator || 'Vikram Singh';
        if (volSecondaryEl) volSecondaryEl.textContent = 'Assigned Staff Coordinator for this outreach deployment.';

        // Filter checked-in patients for current camp using attendance log
        const attendanceList = window.HMCMS_DB.getAttendance();
        let waitingCount = 0;
        let completedCount = 0;
        let emergencyCount = 0;
        let normalCount = 0;
        let campPatientsCount = 0;

        attendanceList.forEach(att => {
            const patient = patientsList.find(p => p.id === att.patientId);
            if (patient) {
                const isSameCamp = (String(patient.camp_id) === String(activeCamp.id) || 
                                    String(patient.camp) === String(activeCamp.name) || 
                                    String(patient.camp) === String(activeCamp.id));
                if (isSameCamp) {
                    campPatientsCount++;
                    const status = patient.status;
                    const priority = att.triagePriority || patient.priority || 'Low';

                    if (status === 'Completed') {
                        completedCount++;
                    } else if (status === 'In Triage' || status === 'Verified' || status === 'Waiting' || status === 'Registered' || status === 'In Consultation') {
                        waitingCount++;
                    }

                    if (priority === 'Emergency' || priority === 'High') {
                        emergencyCount++;
                    } else {
                        normalCount++;
                    }
                }
            }
        });

        // Queue count
        const queueSummaryEl = document.getElementById('camp-queue-summary');
        if (queueSummaryEl) queueSummaryEl.textContent = `${waitingCount} Active Triage`;

        // Completed status
        const completedSummaryEl = document.getElementById('camp-completed-summary');
        if (completedSummaryEl) {
            completedSummaryEl.innerHTML = `<i class="bi bi-people-fill text-success"></i> ${completedCount} Checked-in & Completed`;
        }

        // Set detailed stats
        const statsWaiting = document.getElementById('camp-stats-waiting');
        const statsCompleted = document.getElementById('camp-stats-completed');
        const statsEmergency = document.getElementById('camp-stats-emergency');
        const statsNormal = document.getElementById('camp-stats-normal');

        if (statsWaiting) statsWaiting.textContent = waitingCount;
        if (statsCompleted) statsCompleted.textContent = completedCount;
        if (statsEmergency) statsEmergency.textContent = emergencyCount;
        if (statsNormal) statsNormal.textContent = normalCount;

        // Capacity insights (Expected capacity 200)
        const expected = 200;
        const pct = Math.min(100, Math.round((campPatientsCount / expected) * 100));
        
        const fillBarEl = document.getElementById('camp-fill-rate-bar');
        const fillSummaryEl = document.getElementById('camp-fill-rate-summary');
        
        if (fillBarEl) {
            fillBarEl.style.width = `${pct}%`;
            fillBarEl.setAttribute('aria-valuenow', pct);
        }
        if (fillSummaryEl) {
            fillSummaryEl.textContent = `${pct}.0% Fill Rate (Expected: ${expected} patients, Current: ${campPatientsCount})`;
        }
    }

    // -------------------------------------------------------------------------
    // 2. CONSULTATION INTAKE QUEUE
    // -------------------------------------------------------------------------
    function renderQueue() {
        if (!queueTableBody) return;
        queueTableBody.innerHTML = '';

        const attendanceList = window.HMCMS_DB.getAttendance();

        // Find active queue patients for the active camp from the attendance table
        const activeQueue = [];
        attendanceList.forEach(att => {
            const patient = patientsList.find(p => p.id === att.patientId);
            if (patient) {
                const isSameCamp = (String(patient.camp_id) === String(activeCamp.id) || 
                                    String(patient.camp) === String(activeCamp.name) || 
                                    String(patient.camp) === String(activeCamp.id));
                if (isSameCamp) {
                    // Only include in queue if they are not completed
                    if (patient.status !== 'Completed') {
                        activeQueue.push({
                            id: patient.id,
                            name: patient.name,
                            age: patient.age,
                            gender: patient.gender,
                            token: att.token_number || patient.token || att.token || '#000',
                            priority: att.triagePriority || patient.priority || 'Low',
                            status: patient.status
                        });
                    }
                }
            }
        });

        // Sort: Consultation first, then Priority (High/Emergency -> Medium -> Low)
        const priorityWeight = { 'Emergency': 4, 'High': 3, 'Medium': 2, 'Low': 1 };
        activeQueue.sort((a, b) => {
            if (a.status === 'In Consultation' && b.status !== 'In Consultation') return -1;
            if (b.status === 'In Consultation' && a.status !== 'In Consultation') return 1;

            const priorityA = a.priority;
            const priorityB = b.priority;
            return (priorityWeight[priorityB] || 0) - (priorityWeight[priorityA] || 0);
        });

        // Update counts
        const count = activeQueue.length;
        if (queueBadge) queueBadge.textContent = `${count} patients waiting`;

        // Pagination calculations
        const total = activeQueue.length;
        const totalPages = Math.ceil(total / queueLimit) || 1;
        if (queuePage > totalPages) queuePage = totalPages;

        const startIdx = (queuePage - 1) * queueLimit;
        const endIdx = Math.min(startIdx + queueLimit, total);
        const paginatedQueue = activeQueue.slice(startIdx, endIdx);

        // Render pagination info
        const pagInfo = document.getElementById('queue-pagination-info');
        if (pagInfo) {
            pagInfo.textContent = total > 0 
                ? `Showing ${startIdx + 1} to ${endIdx} of ${total} patients waiting` 
                : 'Showing 0 to 0 of 0 patients waiting';
        }

        // Render pagination nav
        const pagNav = document.getElementById('queue-pagination-nav');
        if (pagNav) {
            pagNav.innerHTML = '';
            
            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.className = `pagination-btn ${queuePage === 1 ? 'disabled' : ''}`;
            prevBtn.innerHTML = '<i class="bi bi-chevron-left"></i>';
            prevBtn.disabled = queuePage === 1;
            prevBtn.onclick = (e) => {
                e.preventDefault();
                if (queuePage > 1) {
                    queuePage--;
                    renderQueue();
                }
            };
            pagNav.appendChild(prevBtn);

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (totalPages > 5 && Math.abs(i - queuePage) > 2 && i !== 1 && i !== totalPages) {
                    if (i === 2 || i === totalPages - 1) {
                        const dots = document.createElement('span');
                        dots.textContent = '...';
                        dots.className = 'px-1 text-muted';
                        pagNav.appendChild(dots);
                    }
                    continue;
                }
                const pageBtn = document.createElement('button');
                pageBtn.className = `pagination-btn ${queuePage === i ? 'active' : ''}`;
                pageBtn.textContent = i;
                pageBtn.onclick = (e) => {
                    e.preventDefault();
                    queuePage = i;
                    renderQueue();
                };
                pagNav.appendChild(pageBtn);
            }

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.className = `pagination-btn ${queuePage === totalPages ? 'disabled' : ''}`;
            nextBtn.innerHTML = '<i class="bi bi-chevron-right"></i>';
            nextBtn.disabled = queuePage === totalPages;
            nextBtn.onclick = (e) => {
                e.preventDefault();
                if (queuePage < totalPages) {
                    queuePage++;
                    renderQueue();
                }
            };
            pagNav.appendChild(nextBtn);
        }

        if (paginatedQueue.length === 0) {
            queueTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No patients waiting in this camp.</td></tr>';
            return;
        }

        paginatedQueue.forEach(patient => {
            const tr = document.createElement('tr');
            
            const priority = patient.priority;
            let priorityBadge = 'badge-custom-primary';
            if (priority === 'High' || priority === 'Emergency') priorityBadge = 'badge-custom-danger';
            if (priority === 'Medium') priorityBadge = 'badge-custom-warning';

            let statusBadge = 'badge-custom-primary';
            let textStatus = 'Waiting';
            if (patient.status === 'In Consultation') {
                statusBadge = 'badge-custom-warning';
                textStatus = 'Consulting';
            }

            tr.innerHTML = `
                <td><strong class="font-monospace text-secondary">${patient.token}</strong></td>
                <td>
                    <div class="fw-semibold text-primary">${patient.name}</div>
                    <span class="text-muted small">Age: ${patient.age} | ${patient.gender}</span>
                </td>
                <td class="text-center">
                    <span class="badge-custom ${priorityBadge}">${priority}</span>
                </td>
                <td class="text-center">
                    <span class="badge-custom ${statusBadge}">${textStatus}</span>
                </td>
                <td class="text-end">
                    ${patient.status !== 'In Consultation'
                        ? `<button class="btn-custom btn-custom-sm btn-custom-outline call-btn" data-id="${patient.id}">Call Next</button>`
                        : `<button class="btn-custom btn-custom-sm btn-custom-success complete-btn" data-id="${patient.id}">Complete</button>`
                    }
                </td>
            `;

            const callBtn = tr.querySelector('.call-btn');
            if (callBtn) {
                callBtn.onclick = () => {
                    callPatient(patient.id);
                };
            }

            const completeBtn = tr.querySelector('.complete-btn');
            if (completeBtn) {
                completeBtn.onclick = () => {
                    completeConsultation(patient.id);
                };
            }

            queueTableBody.appendChild(tr);
        });
    }

    function getPriorityOfPatient(patientId) {
        const p = patientsList.find(p => p.id === patientId);
        return p && p.priority ? p.priority : 'Low';
    }

    function callPatient(patientId) {
        const patient = patientsList.find(p => p.id === patientId);
        if (patient) {
            // Restore any other In Consultation back to In Triage
            patientsList.forEach(p => {
                if (p.status === 'In Consultation') p.status = 'In Triage';
            });

            patient.status = 'In Consultation';
            window.HMCMS_DB.savePatients(patientsList);

            // Log Alert
            window.addSystemNotification(
                'Camp',
                'Patient Called',
                `Token ${patient.token} (${patient.name}) called to Consultation Station 2 by ${activeCamp ? activeCamp.doctor : 'Physician'}.`,
                'warning'
            );

            if (window.showToast) window.showToast('Calling Patient', `Calling Token ${patient.token} to Station 2.`, 'success');
            refreshData();
        }
    }

    function completeConsultation(patientId) {
        const patient = patientsList.find(p => p.id === patientId);
        if (patient) {
            patient.status = 'Completed';
            window.HMCMS_DB.savePatients(patientsList);

            // Log checkout timestamp in attendance roster
            const attendance = window.HMCMS_DB.getAttendance();
            const record = attendance.find(a => a.patientId === patientId);
            if (record) {
                const now = new Date();
                record.checkout = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                sessionStorage.setItem('hmcms_attendance', JSON.stringify(attendance));
            }

            // Log Alert
            window.addSystemNotification(
                'Camp',
                'Consultation Completed',
                `Finished: ${patient.name} (${patient.token}) has completed prescription checkup and checked out.`,
                'success'
            );

            if (window.showToast) window.showToast('Consultation Complete', 'Prescription details logged.', 'success');
            refreshData();
        }
    }

    // -------------------------------------------------------------------------
    // 3. MEDICINE INVENTORY
    // -------------------------------------------------------------------------
    function renderMedicines() {
        if (!medicineTableBody) return;
        medicineTableBody.innerHTML = '';

        if (campInventory.length === 0) {
            medicineTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No medicines assigned to this camp.</td></tr>';
            return;
        }
        campInventory.forEach(med => {
            const tr = document.createElement('tr');
            
            const qty = parseInt(med.quantity);
            const minQty = parseInt(med.minimum_quantity);

            let status = 'In Stock';
            let badgeClass = 'badge-custom-success';
            if (qty <= 0) {
                status = 'Out of Stock';
                badgeClass = 'badge-custom-danger';
            } else if (qty < minQty) {
                status = 'Low Stock';
                badgeClass = 'badge-custom-warning';
            }

            tr.innerHTML = `
                <td><strong class="text-primary">${med.medicine_name}</strong></td>
                <td>${med.category}</td>
                <td class="text-center font-monospace">${qty} ${med.unit}</td>
                <td class="text-center">
                    <span class="badge-custom ${badgeClass}">${status}</span>
                </td>
                <td class="text-end">
                    <button class="btn-custom btn-custom-sm btn-custom-outline restock-btn" data-id="${med.inventory_id}">Restock</button>
                </td>
            `;

            tr.querySelector('.restock-btn').onclick = function () {
                restockMedicine(med.inventory_id);
            };

            medicineTableBody.appendChild(tr);
        });
    }

    function restockMedicine(medId) {
        const med = campInventory.find(m => parseInt(m.inventory_id) === parseInt(medId));
        if (med) {
            const newQty = parseInt(med.quantity) + 100;
            
            const body = new FormData();
            body.append('action', 'save_medicine');
            body.append('inventory_id', med.inventory_id);
            body.append('camp_id', med.camp_id);
            body.append('medicine_name', med.medicine_name);
            body.append('generic_name', med.generic_name);
            body.append('category', med.category);
            body.append('batch_number', med.batch_number);
            body.append('supplier', med.supplier);
            body.append('unit', med.unit);
            body.append('purchase_date', med.purchase_date);
            body.append('expiry_date', med.expiry_date);
            body.append('minimum_quantity', med.minimum_quantity);
            body.append('price', med.price);
            body.append('quantity', newQty);
            body.append('remarks', `Stock adjusted: +100 (Restocked from Camp Assistance). Previous: ${med.remarks || ''}`);

            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'api.php', false); // Synchronous
                xhr.send(body);
                if (xhr.status === 200) {
                    const res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        // Log alert
                        window.addSystemNotification(
                            'Medicine',
                            'Medicine Inventory Restocked',
                            `Supply update: Added 100 units to ${med.medicine_name} inventory.`,
                            'success'
                        );

                        if (window.showToast) window.showToast('Inventory Updated', `Restocked 100 units of ${med.medicine_name}.`, 'success');
                        refreshData();
                    } else {
                        if (window.showToast) window.showToast('Error', res.message || 'Restock failed.', 'danger');
                    }
                }
            } catch (e) {
                console.error(e);
            }
        }
    }

    if (btnRestockAll) {
        btnRestockAll.addEventListener('click', function () {
            let count = 0;
            campInventory.forEach(med => {
                const qty = parseInt(med.quantity);
                const min = parseInt(med.minimum_quantity);
                if (qty < min) {
                    const newQty = qty + 150;
                    
                    const body = new FormData();
                    body.append('action', 'save_medicine');
                    body.append('inventory_id', med.inventory_id);
                    body.append('camp_id', med.camp_id);
                    body.append('medicine_name', med.medicine_name);
                    body.append('generic_name', med.generic_name);
                    body.append('category', med.category);
                    body.append('batch_number', med.batch_number);
                    body.append('supplier', med.supplier);
                    body.append('unit', med.unit);
                    body.append('purchase_date', med.purchase_date);
                    body.append('expiry_date', med.expiry_date);
                    body.append('minimum_quantity', med.minimum_quantity);
                    body.append('price', med.price);
                    body.append('quantity', newQty);
                    body.append('remarks', `Stock adjusted: +150 (Batch Restocked from Camp Assistance). Previous: ${med.remarks || ''}`);

                    try {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'api.php', false); // Synchronous
                        xhr.send(body);
                        if (xhr.status === 200) {
                            const res = JSON.parse(xhr.responseText);
                            if (res.success) {
                                count++;
                            }
                        }
                    } catch (e) {
                        console.error(e);
                    }
                }
            });

            if (count > 0) {
                window.addSystemNotification(
                    'Medicine',
                    'Critical Stock Replenished',
                    `Replenished ${count} low stock pharmaceutical reserves.`,
                    'success'
                );
                if (window.showToast) window.showToast('Batch restocked', `Updated ${count} stock items.`, 'success');
                refreshData();
            } else {
                if (window.showToast) window.showToast('Stock stable', 'No medication stock alerts currently flagged.', 'info');
            }
        });
    }

    // -------------------------------------------------------------------------
    // 4. EMERGENCY ALERTS
    // -------------------------------------------------------------------------
    function renderEmergencies() {
        if (!emergencyList) return;
        emergencyList.innerHTML = '';

        if (!activeCamp) {
            emergencyList.innerHTML = '<div class="py-3 text-center text-muted small"><i class="bi bi-shield-check text-success me-1"></i> No emergency cases for this camp.</div>';
            return;
        }

        const attendanceList = window.HMCMS_DB.getAttendance();
        const emgPatients = [];

        attendanceList.forEach(att => {
            const patient = patientsList.find(p => p.id === att.patientId);
            if (patient) {
                const isSameCamp = (String(patient.camp_id) === String(activeCamp.id) || 
                                    String(patient.camp) === String(activeCamp.name) || 
                                    String(patient.camp) === String(activeCamp.id));
                if (isSameCamp && patient.status !== 'Completed') {
                    const priority = att.triagePriority || patient.priority || 'Low';
                    if (priority === 'High' || priority === 'Critical' || priority === 'Emergency' || priority === 'Urgent') {
                        emgPatients.push({
                            id: patient.id,
                            name: patient.name,
                            gender: patient.gender,
                            age: patient.age,
                            token: att.token_number || patient.token || att.token || '#000',
                            priority: priority,
                            chronic: patient.chronic,
                            allergies: patient.allergies
                        });
                    }
                }
            }
        });

        if (emgPatients.length === 0) {
            emergencyList.innerHTML = '<div class="py-3 text-center text-muted small"><i class="bi bi-shield-check text-success me-1"></i> No emergency cases for this camp.</div>';
            return;
        }

        const dispatchedIds = JSON.parse(sessionStorage.getItem('hmcms_dispatched_emergencies') || '[]');

        emgPatients.forEach(p => {
            const div = document.createElement('div');
            div.className = 'p-3 rounded border border-danger-subtle bg-danger-subtle bg-opacity-10 mb-3';
            
            const isDispatched = dispatchedIds.includes(p.id);
            
            div.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-danger text-white uppercase text-xs" style="font-size: 9px;">${p.priority} Priority Triage</span>
                    <strong class="text-danger font-monospace">${p.token}</strong>
                </div>
                <p class="text-secondary small mb-3 fw-medium">
                    Patient: <strong>${p.name}</strong> (${p.gender}, Age ${p.age}). <br/>
                    History: ${p.chronic || 'None'}<br/>
                    Allergies: ${p.allergies || 'None'}
                </p>
                <div class="d-flex justify-content-end gap-2 border-top pt-2">
                    ${!isDispatched
                        ? `<button class="btn-custom btn-custom-sm btn-custom-danger py-1 px-2 emg-act-btn" data-id="${p.id}">Notify Doctor</button>`
                        : '<span class="text-success small fw-semibold"><i class="bi bi-check-all"></i> Dispatch Active</span>'
                    }
                </div>
            `;

            const btn = div.querySelector('.emg-act-btn');
            if (btn) {
                btn.onclick = () => {
                    handleEmergency(p.id);
                };
            }

            emergencyList.appendChild(div);
        });
    }

    function handleEmergency(patientId) {
        const dispatchedIds = JSON.parse(sessionStorage.getItem('hmcms_dispatched_emergencies') || '[]');
        if (!dispatchedIds.includes(patientId)) {
            dispatchedIds.push(patientId);
            sessionStorage.setItem('hmcms_dispatched_emergencies', JSON.stringify(dispatchedIds));
        }

        const patient = patientsList.find(p => p.id === patientId);
        const pName = patient ? patient.name : 'Patient';
        const pToken = patient ? patient.token : '';

        window.addSystemNotification(
            'Emergency',
            'Emergency Doctor Dispatch',
            `Critical alert: Medical team notified for ${pName} (Token ${pToken}) at ${activeCamp ? activeCamp.name : 'Camp'}.`,
            'danger'
        );

        if (window.showToast) window.showToast('Doctor Alerted', `Physician notified of critical case for Token ${pToken}.`, 'danger');
        refreshData();
    }



    // -------------------------------------------------------------------------
    // 6. INITIALIZE
    // -------------------------------------------------------------------------
    refreshData();
});
