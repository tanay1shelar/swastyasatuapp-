/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Patient Verification Event Controller Script
 * 
 * Simulates UIDAI Aadhaar verification challenges, OTP text alerts, 
 * fingerprint biometrics scans, and maintains verification audit logs.
 */

document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------------------
    // 1. SELECTORS & STATE
    // -------------------------------------------------------------------------
    const queueSearch = document.getElementById('queue-search');
    const pendingList = document.getElementById('verification-pending-list');
    
    // Workspace Selectors
    const emptyState = document.getElementById('workspace-empty-state');
    const activeForm = document.getElementById('workspace-active-form');
    
    // Patient display details
    const workName = document.getElementById('work-patient-name');
    const workId = document.getElementById('work-patient-id');
    const workToken = document.getElementById('work-patient-token');
    const workDemographics = document.getElementById('work-patient-demographics');
    
    // Step 1: Aadhaar Check
    const aadhaarInput = document.getElementById('verify-aadhaar-input');
    const btnCheckUidai = document.getElementById('btn-fetch-uidai');
    const statusAadhaar = document.getElementById('aadhaar-verify-status');

    // Step 2: SMS OTP Check
    const sectionOtp = document.getElementById('verify-otp-section');
    const phoneDisplay = document.getElementById('verify-phone-display');
    const otpInput = document.getElementById('verify-otp-input');
    const btnSendOtp = document.getElementById('btn-send-otp');
    const btnVerifyOtp = document.getElementById('btn-verify-otp');
    const statusOtp = document.getElementById('otp-verify-status');

    // Step 3: Biometrics Scan
    const sectionBio = document.getElementById('verify-bio-section');
    const btnScan = document.getElementById('btn-trigger-scan');
    const scanIcon = document.getElementById('fingerprint-scan-icon');
    const scanSpinner = document.getElementById('fingerprint-spinner');
    const statusBio = document.getElementById('bio-verify-status');

    // Actions Row
    const actionButtons = document.getElementById('workspace-action-buttons');
    const btnVerifyConfirm = document.getElementById('btn-confirm-verification');
    const btnRejectConfirm = document.getElementById('btn-reject-verification');

    // Audit logs table
    const historyTable = document.getElementById('verification-history-table');

    let patientsList = [];
    let verificationsLog = [];
    let activePatient = null;

    // Load DB values
    function loadData() {
        patientsList = window.HMCMS_DB.getPatients();
        verificationsLog = window.HMCMS_DB.getVerifications();
    }

    // -------------------------------------------------------------------------
    // 2. QUEUE & HISTORY RENDERING
    // -------------------------------------------------------------------------
    function renderPendingQueue() {
        if (!pendingList) return;
        pendingList.innerHTML = '';

        // Pending queue includes patients with status "Registered" or "Pending ID" or "In Triage"
        const pendingPatients = patientsList.filter(p => 
            p.status === 'Registered' || p.status === 'Pending ID' || p.status === 'In Triage'
        );

        // Apply search query
        const query = queueSearch ? queueSearch.value.toLowerCase().trim() : '';
        const filtered = pendingPatients.filter(p => 
            p.name.toLowerCase().includes(query) || 
            p.id.toLowerCase().includes(query) ||
            p.token.toLowerCase().includes(query)
        );

        if (filtered.length === 0) {
            pendingList.innerHTML = `
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-shield-check fs-2 mb-2 d-block text-muted"></i>
                    <span class="small">Pending queue is empty</span>
                </div>
            `;
            return;
        }

        filtered.forEach(patient => {
            const btn = document.createElement('button');
            btn.className = 'list-group-item list-group-item-action p-3 text-start border-bottom';
            btn.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="text-primary small">${patient.name}</strong>
                    <span class="badge bg-secondary-subtle text-secondary small" style="font-size: 9px;">${patient.token}</span>
                </div>
                <div class="d-flex justify-content-between text-muted" style="font-size: 10px;">
                    <span>ID: ${patient.id}</span>
                    <span>Status: <strong class="text-warning">${patient.status}</strong></span>
                </div>
            `;

            // Active list selector toggle
            if (activePatient && activePatient.id === patient.id) {
                btn.classList.add('active');
            }

            btn.addEventListener('click', function () {
                // Clear active selected class
                const items = pendingList.querySelectorAll('.list-group-item');
                items.forEach(i => i.classList.remove('active'));
                
                btn.classList.add('active');
                initializeWorkspace(patient);
            });

            pendingList.appendChild(btn);
        });
    }

    function renderHistoryLogs() {
        if (!historyTable) return;
        historyTable.innerHTML = '';

        // Read direct from DB store
        const logs = window.HMCMS_DB.getVerifications();

        if (logs.length === 0) {
            historyTable.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">No recent verification logs found.</td>
                </tr>
            `;
            return;
        }

        logs.slice(0, 5).forEach(log => {
            const tr = document.createElement('tr');
            
            let statusBadge = 'badge-custom-success';
            if (log.status === 'Pending' || log.status === 'Rejected') {
                statusBadge = 'badge-custom-danger';
            }
            
            tr.innerHTML = `
                <td>
                    <div class="fw-semibold text-primary">${log.patientName}</div>
                    <span class="text-muted small">${log.patientId}</span>
                </td>
                <td>${log.method}</td>
                <td class="text-secondary">${log.remarks}</td>
                <td>${log.timestamp}</td>
                <td class="text-end">
                    <span class="badge-custom ${statusBadge}">${log.status}</span>
                </td>
            `;
            historyTable.appendChild(tr);
        });
    }

    // -------------------------------------------------------------------------
    // 3. WORKSPACE INTIALIZATION & FLOW CONTROLS
    // -------------------------------------------------------------------------
    function initializeWorkspace(patient) {
        activePatient = patient;
        
        // Hide empty state, reveal forms
        if (emptyState) emptyState.style.setProperty('display', 'none', 'important');
        if (activeForm) activeForm.style.setProperty('display', 'block', 'important');

        // Populate metadata labels
        if (workName) workName.textContent = patient.name;
        if (workId) workId.textContent = patient.id;
        if (workToken) workToken.textContent = patient.token;
        if (workDemographics) workDemographics.textContent = `${patient.age} Yrs - ${patient.gender}`;
        if (phoneDisplay) phoneDisplay.value = patient.phone;

        // Reset workspace layout steps & disable controls
        resetWorkspaceSteps();
    }

    function resetWorkspaceSteps() {
        // Step 1: Aadhaar Check
        if (aadhaarInput) {
            aadhaarInput.value = '';
            aadhaarInput.removeAttribute('disabled');
        }
        if (btnCheckUidai) btnCheckUidai.removeAttribute('disabled');
        if (statusAadhaar) statusAadhaar.style.display = 'none';

        // Step 2: SMS OTP
        if (sectionOtp) {
            sectionOtp.style.opacity = '0.5';
            sectionOtp.style.pointerEvents = 'none';
        }
        if (otpInput) {
            otpInput.value = '';
            otpInput.setAttribute('disabled', 'true');
        }
        if (btnSendOtp) {
            btnSendOtp.style.display = 'block';
            btnSendOtp.removeAttribute('disabled');
            btnSendOtp.innerHTML = 'Send SMS';
        }
        if (btnVerifyOtp) {
            btnVerifyOtp.style.display = 'none';
            btnVerifyOtp.removeAttribute('disabled');
        }
        if (statusOtp) statusOtp.style.display = 'none';

        // Step 3: Biometrics scan
        if (sectionBio) {
            sectionBio.style.opacity = '0.5';
            sectionBio.style.pointerEvents = 'none';
        }
        if (btnScan) btnScan.removeAttribute('disabled');
        if (scanIcon) {
            scanIcon.className = 'bi bi-fingerprint text-muted fs-1';
            scanIcon.style.display = 'block';
        }
        if (scanSpinner) scanSpinner.style.display = 'none';
        if (statusBio) statusBio.style.display = 'none';

        // Actions Row
        if (actionButtons) {
            actionButtons.style.opacity = '0.5';
            actionButtons.style.pointerEvents = 'none';
        }
    }

    // Format Aadhaar space delimiters (XXXX XXXX XXXX)
    if (aadhaarInput) {
        aadhaarInput.addEventListener('input', function (e) {
            // Remove non-numbers
            let val = this.value.replace(/[^0-9]/g, '');
            // Slice segments
            let matches = val.match(/\d{1,4}/g);
            if (matches) {
                this.value = matches.join(' ');
            } else {
                this.value = '';
            }
        });
    }

    // -------------------------------------------------------------------------
    // STEP 1: FETCH UIDAI AADHAAR
    // -------------------------------------------------------------------------
    if (btnCheckUidai) {
        btnCheckUidai.addEventListener('click', function () {
            const rawAadhaar = aadhaarInput.value.replace(/\s/g, '');
            if (rawAadhaar.length !== 12) {
                if (window.showToast) {
                    window.showToast('Validation Failed', 'Please input a valid 12-digit Aadhaar ID number.', 'warning');
                }
                return;
            }

            // Disable triggers
            btnCheckUidai.setAttribute('disabled', 'true');
            aadhaarInput.setAttribute('disabled', 'true');

            if (window.showToast) window.showToast('UIDAI Query Dispatched', 'Contacting Aadhaar registry node...', 'info');

            // Simulating API loading latencies
            setTimeout(() => {
                if (statusAadhaar) statusAadhaar.style.display = 'block';
                
                // Unlock Step 2
                if (sectionOtp) {
                    sectionOtp.style.opacity = '1';
                    sectionOtp.style.pointerEvents = 'auto';
                }

                if (window.showToast) {
                    window.showToast(
                        'Aadhaar Linked',
                        `Demographics matched. SMS challenge or Biometric check-up authorized.`,
                        'success'
                    );
                }
            }, 800);
        });
    }

    // -------------------------------------------------------------------------
    // STEP 2: SMS OTP verification
    // -------------------------------------------------------------------------
    if (btnSendOtp) {
        btnSendOtp.addEventListener('click', function () {
            btnSendOtp.setAttribute('disabled', 'true');
            btnSendOtp.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

            setTimeout(() => {
                btnSendOtp.style.display = 'none';
                if (btnVerifyOtp) btnVerifyOtp.style.display = 'block';
                if (otpInput) otpInput.removeAttribute('disabled');
                
                if (window.showToast) {
                    window.showToast('Verification PIN Sent', `A 6-digit verification code was sent to ${phoneDisplay.value}.`, 'info');
                }
            }, 800);
        });
    }

    if (btnVerifyOtp) {
        btnVerifyOtp.addEventListener('click', function () {
            const otp = otpInput.value.trim();
            if (otp.length !== 6 || isNaN(otp)) {
                if (window.showToast) window.showToast('Invalid Code', 'Please enter a valid 6-digit numeric OTP code.', 'warning');
                return;
            }

            btnVerifyOtp.setAttribute('disabled', 'true');
            otpInput.setAttribute('disabled', 'true');

            setTimeout(() => {
                if (statusOtp) statusOtp.style.display = 'block';
                
                // Unlock Step 3
                if (sectionBio) {
                    sectionBio.style.opacity = '1';
                    sectionBio.style.pointerEvents = 'auto';
                }

                if (window.showToast) window.showToast('OTP Authenticated', 'Mobile identity checked and validated.', 'success');
            }, 600);
        });
    }

    // -------------------------------------------------------------------------
    // STEP 3: FINGERPRINT SCAN
    // -------------------------------------------------------------------------
    if (btnScan) {
        btnScan.addEventListener('click', function () {
            btnScan.setAttribute('disabled', 'true');
            
            // Toggle spinner state
            if (scanIcon) scanIcon.style.display = 'none';
            if (scanSpinner) scanSpinner.style.display = 'block';

            if (window.showToast) window.showToast('Scanner Active', 'Initializing USB Fingerprint sensor scanning...', 'info');

            setTimeout(() => {
                if (scanSpinner) scanSpinner.style.display = 'none';
                if (scanIcon) {
                    scanIcon.style.display = 'block';
                    scanIcon.className = 'bi bi-fingerprint text-success fs-1';
                }
                if (statusBio) statusBio.style.display = 'block';

                // Unlock Actions row
                if (actionButtons) {
                    actionButtons.style.opacity = '1';
                    actionButtons.style.pointerEvents = 'auto';
                }

                if (window.showToast) window.showToast('Biometrics Matched', 'Thumbprint scans authenticated successfully.', 'success');
            }, 1500);
        });
    }

    // -------------------------------------------------------------------------
    // 4. VERIFY / REJECT ACTION ACTIONS
    // -------------------------------------------------------------------------
    if (btnVerifyConfirm) {
        btnVerifyConfirm.addEventListener('click', function () {
            if (!activePatient) return;

            // Update state in DB
            activePatient.status = 'Verified';
            window.HMCMS_DB.savePatients(patientsList);

            // Log entry
            const logDate = new Date();
            const log = {
                patientId: activePatient.id,
                patientName: activePatient.name,
                method: 'Biometric + OTP Check',
                status: 'Verified',
                timestamp: logDate.toISOString().slice(0, 10) + ' ' + logDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }),
                remarks: 'Thumbprint scan matched successfully (98.6% rating)'
            };

            const logs = window.HMCMS_DB.getVerifications();
            logs.unshift(log); // Prepend
            sessionStorage.setItem('hmcms_verifications', JSON.stringify(logs));

            if (window.showToast) {
                window.showToast(
                    'Verification Complete', 
                    `Identity files for ${activePatient.name} have been authenticated.`, 
                    'success'
                );
            }

            resetWorkspace();
        });
    }

    if (btnRejectConfirm) {
        btnRejectConfirm.addEventListener('click', function () {
            if (!activePatient) return;

            // Reject state updates
            activePatient.status = 'Pending ID';
            window.HMCMS_DB.savePatients(patientsList);

            // Log rejected log
            const logDate = new Date();
            const log = {
                patientId: activePatient.id,
                patientName: activePatient.name,
                method: 'UIDAI Query Failed',
                status: 'Rejected',
                timestamp: logDate.toISOString().slice(0, 10) + ' ' + logDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }),
                remarks: 'Biometric fingerprint scan timeout/mismatch'
            };

            const logs = window.HMCMS_DB.getVerifications();
            logs.unshift(log);
            sessionStorage.setItem('hmcms_verifications', JSON.stringify(logs));

            if (window.showToast) {
                window.showToast(
                    'Identity Rejected', 
                    `Verification rejected for ${activePatient.name}. Status set to Pending ID.`, 
                    'danger'
                );
            }

            resetWorkspace();
        });
    }

    function resetWorkspace() {
        activePatient = null;
        if (emptyState) emptyState.style.setProperty('display', 'block', 'important');
        if (activeForm) activeForm.style.setProperty('display', 'none', 'important');
        
        loadData();
        renderPendingQueue();
        renderHistoryLogs();
    }

    // Filter queue search input
    if (queueSearch) {
        queueSearch.addEventListener('input', renderPendingQueue);
    }

    // -------------------------------------------------------------------------
    // 5. INITIALIZATION
    // -------------------------------------------------------------------------
    loadData();
    renderPendingQueue();
    renderHistoryLogs();
});
