/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Camp Assistance Workspace Controller Script
 * 
 * Manages consultation queues, handles Call and Complete actions,
 * initializes medicine inventories with Restock triggers, and triages emergency cases.
 */

document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------------------
    // 1. SELECTORS & STATE
    // -------------------------------------------------------------------------
    const queueTableBody = document.getElementById('camp-patient-queue-table');
    const medicineTableBody = document.getElementById('camp-medicine-inventory-table');
    const emergencyList = document.getElementById('camp-emergency-list');

    // Badges & Actions
    const queueBadge = document.getElementById('queue-count-badge');
    const queueSummary = document.getElementById('camp-queue-summary');
    const btnRestockAll = document.getElementById('btn-restock-all');

    let patientsList = [];
    
    // Default medicine inventory list
    const defaultMedicines = [
        { id: 'MED-001', name: 'Paracetamol 650mg', category: 'Analgesic / Antipyretic', qty: 250, unit: 'Tablets', minQty: 50 },
        { id: 'MED-002', name: 'Ibuprofen 400mg', category: 'NSAID (Pain relief)', qty: 35, unit: 'Tablets', minQty: 50 },
        { id: 'MED-003', name: 'Amoxicillin 500mg', category: 'Antibiotic (Penicillin)', qty: 120, unit: 'Capsules', minQty: 40 },
        { id: 'MED-004', name: 'ORS Powder Packets', category: 'Oral Rehydration Salts', qty: 85, unit: 'Sachets', minQty: 30 },
        { id: 'MED-005', name: 'Metformin 500mg', category: 'Oral Antidiabetic', qty: 300, unit: 'Tablets', minQty: 60 },
        { id: 'MED-006', name: 'Cetirizine 10mg', category: 'Antihistamine (Allergies)', qty: 12, unit: 'Tablets', minQty: 40 }
    ];

    // Default emergencies
    const defaultEmergencies = [
        { id: 'EMG-001', token: '#122', details: 'High-grade fever & convulsions (Pediatric, 4 Yrs)', status: 'Pending', action: 'Notify Doctor' },
        { id: 'EMG-002', token: '#135', details: 'Severe chest pains & acute dyspnea (Geriatric, 68 Yrs)', status: 'Pending', action: 'Call Ambulance' }
    ];

    // Retrieve storage structures
    function loadData() {
        patientsList = window.HMCMS_DB.getPatients();
        
        if (!sessionStorage.getItem('hmcms_medicines')) {
            sessionStorage.setItem('hmcms_medicines', JSON.stringify(defaultMedicines));
        }
        if (!sessionStorage.getItem('hmcms_emergencies')) {
            sessionStorage.setItem('hmcms_emergencies', JSON.stringify(defaultEmergencies));
        }
    }

    function getMedicines() {
        return JSON.parse(sessionStorage.getItem('hmcms_medicines'));
    }

    function saveMedicines(meds) {
        sessionStorage.setItem('hmcms_medicines', JSON.stringify(meds));
    }

    function getEmergencies() {
        return JSON.parse(sessionStorage.getItem('hmcms_emergencies'));
    }

    function saveEmergencies(emgs) {
        sessionStorage.setItem('hmcms_emergencies', JSON.stringify(emgs));
    }

    // -------------------------------------------------------------------------
    // 2. PATIENT QUEUE RENDERING & MUTATIONS
    // -------------------------------------------------------------------------
    function renderPatientQueue() {
        if (!queueTableBody) return;
        queueTableBody.innerHTML = '';

        // Filter patients checked in today at the active camp site
        // Statuses of active queue: "In Triage", "Verified", "Registered" (not completed or failed)
        // Also include patients currently "In Consultation"
        const activeQueue = patientsList.filter(p => 
            p.status === 'In Triage' || p.status === 'Verified' || p.status === 'Registered' || p.status === 'In Consultation'
        );

        // Sort by priority logic: High -> Medium -> Low
        const priorityWeight = { 'High': 3, 'Medium': 2, 'Low': 1 };
        activeQueue.sort((a, b) => {
            // Put 'In Consultation' first
            if (a.status === 'In Consultation' && b.status !== 'In Consultation') return -1;
            if (b.status === 'In Consultation' && a.status !== 'In Consultation') return 1;
            
            // Map priority priorityWeight
            const pA = getPriorityOfPatient(a.id);
            const pB = getPriorityOfPatient(b.id);
            return priorityWeight[pB] - priorityWeight[pA];
        });

        // Update headers count badges
        const count = activeQueue.length;
        if (queueBadge) queueBadge.textContent = `${count} patients waiting`;
        if (queueSummary) queueSummary.textContent = `${count} Active Triage`;

        if (activeQueue.length === 0) {
            queueTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-people fs-2 mb-2 d-block text-muted"></i>
                        <span>Intake queue is clear. No patients waiting.</span>
                    </td>
                </tr>
            `;
            return;
        }

        activeQueue.forEach(patient => {
            const tr = document.createElement('tr');
            
            // Retrieve priority of patient from attendance log
            const priority = getPriorityOfPatient(patient.id);
            let priorityBadge = 'badge-custom-primary';
            if (priority === 'High') priorityBadge = 'badge-custom-danger';
            if (priority === 'Medium') priorityBadge = 'badge-custom-warning';

            // Intake status
            let statusBadge = 'badge-custom-primary'; // Queue
            let textStatus = 'In Queue';
            if (patient.status === 'In Consultation') {
                statusBadge = 'badge-custom-warning';
                textStatus = 'In Consultation';
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
                        ? `<button class="btn-custom btn-custom-sm btn-custom-outline call-patient-btn" data-id="${patient.id}"><i class="bi bi-megaphone"></i> Call Next</button>`
                        : `<button class="btn-custom btn-custom-sm btn-custom-success complete-patient-btn" data-id="${patient.id}"><i class="bi bi-check-lg"></i> Complete</button>`
                    }
                </td>
            `;

            // Button actions
            const callBtn = tr.querySelector('.call-patient-btn');
            if (callBtn) {
                callBtn.onclick = function () {
                    callPatient(patient.id);
                };
            }

            const completeBtn = tr.querySelector('.complete-patient-btn');
            if (completeBtn) {
                completeBtn.onclick = function () {
                    completeConsultation(patient.id);
                };
            }

            queueTableBody.appendChild(tr);
        });
    }

    function getPriorityOfPatient(patientId) {
        const attendance = window.HMCMS_DB.getAttendance();
        const record = attendance.find(a => a.patientId === patientId);
        return record ? record.triagePriority : 'Low';
    }

    function callPatient(patientId) {
        const patient = patientsList.find(p => p.id === patientId);
        if (patient) {
            // Remove any other 'In Consultation' flag first to simulate single desk pipeline
            patientsList.forEach(p => {
                if (p.status === 'In Consultation') p.status = 'In Triage';
            });

            patient.status = 'In Consultation';
            window.HMCMS_DB.savePatients(patientsList);

            if (window.showToast) {
                window.showToast(
                    'Patient Called',
                    `Calling ${patient.name} (${patient.token}) to Consultation Station 2.`,
                    'success'
                );
            }

            loadData();
            renderPatientQueue();
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

            if (window.showToast) {
                window.showToast(
                    'Consultation Finished',
                    `Log checkout details saved for ${patient.name}.`,
                    'success'
                );
            }

            loadData();
            renderPatientQueue();
        }
    }

    // -------------------------------------------------------------------------
    // 3. MEDICINE INVENTORY RENDERING & RESTOCK
    // -------------------------------------------------------------------------
    function renderMedicineInventory() {
        if (!medicineTableBody) return;
        medicineTableBody.innerHTML = '';

        const medicines = getMedicines();

        medicines.forEach(med => {
            const tr = document.createElement('tr');
            
            // Stock level classifications
            let status = 'In Stock';
            let statusBadge = 'badge-custom-success';
            
            if (med.qty <= 0) {
                status = 'Out of Stock';
                statusBadge = 'badge-custom-danger';
            } else if (med.qty < med.minQty) {
                status = 'Low Stock';
                statusBadge = 'badge-custom-warning';
            }

            tr.innerHTML = `
                <td><strong class="text-primary">${med.name}</strong></td>
                <td>${med.category}</td>
                <td class="text-center font-monospace">${med.qty} ${med.unit}</td>
                <td class="text-center">
                    <span class="badge-custom ${statusBadge}">${status}</span>
                </td>
                <td class="text-end">
                    <button class="btn-custom btn-custom-sm btn-custom-outline restock-btn" data-id="${med.id}"><i class="bi bi-plus-lg"></i> Restock</button>
                </td>
            `;

            tr.querySelector('.restock-btn').onclick = function () {
                restockMedicine(med.id);
            };

            medicineTableBody.appendChild(tr);
        });
    }

    function restockMedicine(medId) {
        const meds = getMedicines();
        const med = meds.find(m => m.id === medId);
        
        if (med) {
            med.qty += 100; // Add 100 units
            saveMedicines(meds);
            
            if (window.showToast) {
                window.showToast('Medicine Restocked', `Added 100 units to ${med.name} inventory.`, 'success');
            }
            renderMedicineInventory();
        }
    }

    if (btnRestockAll) {
        btnRestockAll.addEventListener('click', function () {
            const meds = getMedicines();
            let countRestocked = 0;

            meds.forEach(med => {
                if (med.qty < med.minQty) {
                    med.qty += 150;
                    countRestocked++;
                }
            });

            if (countRestocked > 0) {
                saveMedicines(meds);
                if (window.showToast) window.showToast('Inventory Update', `Restocked ${countRestocked} critical shortage items.`, 'success');
                renderMedicineInventory();
            } else {
                if (window.showToast) window.showToast('Inventory Stable', 'No stock shortages flagged in inventory cards.', 'info');
            }
        });
    }

    // -------------------------------------------------------------------------
    // 4. EMERGENCY CASES DISPATCH
    // -------------------------------------------------------------------------
    function renderEmergencies() {
        if (!emergencyList) return;
        emergencyList.innerHTML = '';

        const emergencies = getEmergencies();

        if (emergencies.length === 0) {
            emergencyList.innerHTML = `
                <div class="py-3 text-center text-muted small">
                    <i class="bi bi-shield-check text-success fs-3 mb-1 d-block"></i>
                    <span>No critical triage dispatches logged.</span>
                </div>
            `;
            return;
        }

        emergencies.forEach(emg => {
            const div = document.createElement('div');
            div.className = 'p-3 rounded border border-danger-subtle bg-danger-subtle bg-opacity-10 mb-3';
            
            div.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-danger text-white uppercase text-xs" style="font-size: 9px;">Critical Emergency</span>
                    <strong class="text-danger font-monospace">${emg.token}</strong>
                </div>
                <p class="text-secondary small mb-3 fw-medium">${emg.details}</p>
                <div class="d-flex justify-content-end gap-2 border-top pt-2">
                    ${emg.status === 'Pending'
                        ? `<button class="btn-custom btn-custom-sm btn-custom-danger py-1 px-2 emg-action-btn" data-id="${emg.id}">${emg.action}</button>`
                        : '<span class="text-success small fw-semibold"><i class="bi bi-check-all"></i> Responder Dispatched</span>'
                    }
                </div>
            `;

            const actionBtn = div.querySelector('.emg-action-btn');
            if (actionBtn) {
                actionBtn.onclick = function () {
                    handleEmergencyAction(emg.id);
                };
            }

            emergencyList.appendChild(div);
        });
    }

    function handleEmergencyAction(emgId) {
        const emgs = getEmergencies();
        const emg = emgs.find(e => e.id === emgId);
        
        if (emg) {
            emg.status = 'Dispatched';
            saveEmergencies(emgs);

            const alertTitle = emg.action === 'Notify Doctor' ? 'Doctor Notified' : 'Ambulance Dispatched';
            const alertText = emg.action === 'Notify Doctor' 
                ? 'Chief Physician has been alerted to prioritize Station Triage.' 
                : 'Emergency Response Vehicle has deployed to exit bay 1.';

            if (window.showToast) {
                window.showToast(alertTitle, alertText, 'danger');
            }

            renderEmergencies();
        }
    }

    // -------------------------------------------------------------------------
    // 5. INITIALIZE
    // -------------------------------------------------------------------------
    loadData();
    renderPatientQueue();
    renderMedicineInventory();
    renderEmergencies();
});
