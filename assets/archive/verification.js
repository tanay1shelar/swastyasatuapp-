/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Centralized Patient Identity Verification Module (verification.js)
 * 
 * Manages pending queues, coordinates Aadhaar searches, displays complete
 * patient records, and updates patient status to Verified.
 */

document.addEventListener('DOMContentLoaded', function () {
    const isVerificationPage = document.getElementById('verification-pending-list') !== null;
    if (!isVerificationPage) return;

    // -------------------------------------------------------------------------
    // SELECTORS & STATE
    // -------------------------------------------------------------------------
    const queueSearch = document.getElementById('queue-search');
    const pendingList = document.getElementById('verification-pending-list');
    
    const emptyState = document.getElementById('workspace-empty-state');
    const activeForm = document.getElementById('workspace-active-form');
    const searchErrorMsg = document.getElementById('search-error-msg');
    
    const aadhaarInput = document.getElementById('verify-aadhaar-input');
    const btnSearchAadhaar = document.getElementById('btn-fetch-uidai');
    
    // Detail display selectors
    const detailPhoto = document.getElementById('detail-patient-photo');
    const detailName = document.getElementById('detail-patient-name');
    const detailId = document.getElementById('detail-patient-id');
    const detailRegNumber = document.getElementById('detail-reg-number');
    const detailAge = document.getElementById('detail-patient-age');
    const detailGender = document.getElementById('detail-patient-gender');
    const detailBlood = document.getElementById('detail-patient-blood');
    const detailPhone = document.getElementById('detail-patient-phone');
    const detailCamp = document.getElementById('detail-patient-camp');
    const detailRegDate = document.getElementById('detail-patient-reg-date');
    const detailAddress = document.getElementById('detail-patient-address');

    const btnVerifyConfirm = document.getElementById('btn-confirm-verification');
    const historyTable = document.getElementById('verification-history-table');

    let patientsList = [];
    let activePatient = null;

    function refreshData() {
        patientsList = window.HMCMS_DB.getPatients();
        renderQueue();
        renderHistory();
        
        // Auto select patient from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const paramId = urlParams.get('id');
        if (paramId) {
            const found = patientsList.find(p => p.id.toUpperCase() === paramId.toUpperCase());
            if (found) {
                selectPatient(found);
            }
        }
    }

    function renderQueue() {
        if (!pendingList) return;
        pendingList.innerHTML = '';

        // Display Registered, Rejected, and Pending ID patients, explicitly excluding verified profiles
        const pending = patientsList.filter(p => 
            (p.status === 'Registered' || p.status === 'Rejected' || p.status === 'Pending ID' || p.status === 'Waiting') && 
            p.status !== 'Verified' && 
            p.status !== 'In Triage' && 
            p.status !== 'In Consultation' && 
            p.status !== 'Completed'
        );

        const query = queueSearch ? queueSearch.value.toLowerCase().trim() : '';
        const filtered = pending.filter(p => 
            (p.name && p.name.toLowerCase().includes(query)) || 
            (p.id && p.id.toLowerCase().includes(query)) || 
            (p.registration_number && p.registration_number.toLowerCase().includes(query)) || 
            (p.phone && p.phone.toLowerCase().includes(query)) || 
            (p.aadhaar && p.aadhaar.toLowerCase().includes(query)) ||
            (p.token && p.token.toLowerCase().includes(query))
        );

        if (filtered.length === 0) {
            pendingList.innerHTML = `<div class="p-4 text-center text-muted small">No pending verifications queue</div>`;
            return;
        }

        filtered.forEach(patient => {
            const btn = document.createElement('button');
            btn.className = 'list-group-item list-group-item-action p-3 text-start border-bottom';
            if (activePatient && activePatient.id === patient.id) btn.classList.add('active');
            
            const badgeClass = window.GlobalBadges.getClass(patient.status);
            const statusLabel = patient.status === 'Verified' ? 'Verified' : 'Pending';
            
            btn.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="text-primary small mb-0">${patient.name}</strong>
                    <span class="badge-custom ${badgeClass}" style="font-size: 8px;">${statusLabel}</span>
                </div>
                <div class="text-muted small mb-1" style="font-size: 10.5px;">
                    Patient ID: <strong>${patient.id}</strong>
                </div>
                <div class="d-flex justify-content-between text-muted" style="font-size: 10px;">
                    <span><i class="bi bi-geo-alt"></i> ${patient.camp || 'General'}</span>
                    <span><i class="bi bi-clock"></i> ${patient.checkin || 'n/a'}</span>
                </div>
            `;

            btn.onclick = () => {
                const listItems = pendingList.querySelectorAll('.list-group-item');
                listItems.forEach(item => item.classList.remove('active'));
                btn.classList.add('active');
                selectPatient(patient);
            };

            pendingList.appendChild(btn);
        });
    }

    function selectPatient(patient) {
        if (aadhaarInput) {
            if (searchErrorMsg) searchErrorMsg.style.display = 'none';
            
            let val = String(patient.aadhaar || '').replace(/[^0-9]/g, '');
            let matches = val.match(/\d{1,4}/g);
            aadhaarInput.value = matches ? matches.join(' ') : '';
            
            searchPatientByAadhaar(patient.aadhaar);
        }
    }

    const defaultAvatar = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23cbd5e1' width='80' height='80'%3E%3Crect width='24' height='24' rx='12' fill='%23f1f5f9'/%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z' fill='%2394a3b8'/%3E%3C/svg%3E";

    function clearDetailsDOM() {
        if (detailPhoto) detailPhoto.src = defaultAvatar;
        if (detailName) detailName.textContent = '';
        if (detailId) detailId.textContent = '';
        if (detailRegNumber) detailRegNumber.textContent = '';
        if (detailAge) detailAge.textContent = '';
        if (detailGender) detailGender.textContent = '';
        if (detailBlood) detailBlood.textContent = '';
        if (detailPhone) detailPhone.textContent = '';
        if (detailCamp) detailCamp.textContent = '';
        if (detailRegDate) detailRegDate.textContent = '';
        
        const detailStatusBadge = document.getElementById('detail-patient-status-badge');
        if (detailStatusBadge) detailStatusBadge.textContent = '--';
        
        const detailAadhaar = document.getElementById('detail-patient-aadhaar');
        const detailEmergency = document.getElementById('detail-patient-emergency');
        const detailVerStatus = document.getElementById('detail-patient-verification-status');
        const detailAttStatus = document.getElementById('detail-patient-attendance-status');
        if (detailAadhaar) detailAadhaar.textContent = '';
        if (detailEmergency) detailEmergency.textContent = '';
        if (detailVerStatus) detailVerStatus.textContent = '';
        if (detailAttStatus) detailAttStatus.textContent = '';
        
        const verAlert = document.getElementById('verification-alert-container');
        if (verAlert) verAlert.innerHTML = '';
        
        const verMeta = document.getElementById('verified-meta-details');
        if (verMeta) verMeta.style.display = 'none';
        
        const verDate = document.getElementById('verified-date');
        const verTime = document.getElementById('verified-time');
        const verBy = document.getElementById('verified-by');
        const verId = document.getElementById('verified-id');
        if (verDate) verDate.textContent = '--';
        if (verTime) verTime.textContent = '--';
        if (verBy) verBy.textContent = '--';
        if (verId) verId.textContent = '--';
    }

    function searchPatientByAadhaar(aadhaarVal) {
        if (searchErrorMsg) searchErrorMsg.style.display = 'none';
        clearDetailsDOM();
        
        const cleanAadhaar = String(aadhaarVal || '').replace(/\s/g, '');
        if (cleanAadhaar.length !== 12) {
            if (window.showToast) window.showToast('Validation Error', 'Enter 12-digit Aadhaar Card number.', 'warning');
            return;
        }

        // Show loading state
        if (btnSearchAadhaar) btnSearchAadhaar.setAttribute('disabled', 'true');
        if (aadhaarInput) aadhaarInput.setAttribute('disabled', 'true');

        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'api.php?action=search_patient_by_aadhaar&aadhaar=' + encodeURIComponent(cleanAadhaar), true);
        xhr.onload = function () {
            if (btnSearchAadhaar) btnSearchAadhaar.removeAttribute('disabled');
            if (aadhaarInput) aadhaarInput.removeAttribute('disabled');

            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    displayPatientDetails(res.data);
                } else {
                    showSearchError();
                }
            } else {
                showSearchError();
            }
        };
        xhr.onerror = function () {
            if (btnSearchAadhaar) btnSearchAadhaar.removeAttribute('disabled');
            if (aadhaarInput) aadhaarInput.removeAttribute('disabled');
            showSearchError();
        };
        xhr.send();
    }

    function displayPatientDetails(patient) {
        activePatient = patient;

        if (emptyState) emptyState.classList.add('d-none');
        if (activeForm) activeForm.classList.remove('d-none');
        if (searchErrorMsg) searchErrorMsg.style.display = 'none';

        if (detailPhoto) {
            detailPhoto.onerror = function() {
                detailPhoto.src = defaultAvatar;
            };
            detailPhoto.src = patient.photo || defaultAvatar;
        }
        if (detailName) detailName.textContent = patient.name || '';
        if (detailId) detailId.textContent = patient.id || '';
        if (detailRegNumber) detailRegNumber.textContent = patient.registration_number || '';
        if (detailAge) detailAge.textContent = patient.age ? patient.age + ' Years' : '';
        if (detailGender) detailGender.textContent = patient.gender || '';
        if (detailBlood) detailBlood.textContent = patient.blood || '';
        if (detailPhone) detailPhone.textContent = patient.phone || '';
        if (detailCamp) detailCamp.textContent = patient.camp || '';
        if (detailRegDate) detailRegDate.textContent = patient.registrationDate || '';
        if (detailAddress) detailAddress.textContent = patient.address || '';

        const detailStatusBadge = document.getElementById('detail-patient-status-badge');
        if (detailStatusBadge) detailStatusBadge.textContent = patient.status || '';

        // Populate new verification details fields
        const detailAadhaar = document.getElementById('detail-patient-aadhaar');
        const detailEmergency = document.getElementById('detail-patient-emergency');
        const detailVerStatus = document.getElementById('detail-patient-verification-status');
        const detailAttStatus = document.getElementById('detail-patient-attendance-status');
        
        if (detailAadhaar) detailAadhaar.textContent = patient.aadhaar || '';
        if (detailEmergency) {
            detailEmergency.textContent = patient.guardian_name 
                ? `${patient.guardian_name} (${patient.guardian_phone || 'N/A'})` 
                : 'None';
        }
        if (detailVerStatus) {
            if (patient.verification_status === 'Verified') {
                detailVerStatus.innerHTML = `<span class="badge bg-success text-white px-2 py-1"><i class="bi bi-patch-check-fill"></i> VERIFIED</span>`;
            } else {
                detailVerStatus.innerHTML = `<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock-history"></i> PENDING</span>`;
            }
        }
        if (detailAttStatus) detailAttStatus.textContent = patient.attendance_status || '--';

        const verAlert = document.getElementById('verification-alert-container');
        const verMeta = document.getElementById('verified-meta-details');

        if (patient.verification_status === 'Verified') {
            // CASE 3: ALREADY VERIFIED
            if (verAlert) {
                verAlert.innerHTML = `
                    <div class="alert alert-success py-2 px-3 mb-0 small" role="alert">
                        <i class="bi bi-check-circle-fill me-2 text-success"></i> This patient has already been verified.
                    </div>
                `;
            }
            if (verMeta) {
                verMeta.style.display = 'block';
                const verDate = document.getElementById('verified-date');
                const verTime = document.getElementById('verified-time');
                const verBy = document.getElementById('verified-by');
                const verId = document.getElementById('verified-id');
                
                let datePart = 'N/A';
                let timePart = 'N/A';
                if (patient.verification_date) {
                    const parts = patient.verification_date.split(' ');
                    datePart = parts[0] || 'N/A';
                    timePart = parts[1] || 'N/A';
                }
                
                if (verDate) verDate.textContent = datePart;
                if (verTime) verTime.textContent = timePart;
                if (verBy) verBy.textContent = patient.verifier_name || 'System Administrator';
                if (verId) verId.textContent = patient.verification_id || 'N/A';
            }
            // Disable Verify Button
            if (btnVerifyConfirm) btnVerifyConfirm.setAttribute('disabled', 'true');
        } else {
            // CASE 4: NOT VERIFIED
            if (verAlert) verAlert.innerHTML = '';
            if (verMeta) verMeta.style.display = 'none';
            // Enable Verify Button
            if (btnVerifyConfirm) btnVerifyConfirm.removeAttribute('disabled');
        }
    }

    function showSearchError() {
        activePatient = null;
        if (emptyState) emptyState.classList.remove('d-none');
        if (activeForm) activeForm.classList.add('d-none');
        if (searchErrorMsg) searchErrorMsg.style.display = 'block';
        clearDetailsDOM();

        // Disable Verify button
        if (btnVerifyConfirm) btnVerifyConfirm.setAttribute('disabled', 'true');
    }

    if (aadhaarInput) {
        aadhaarInput.addEventListener('input', function () {
            let val = this.value.replace(/[^0-9]/g, '');
            let matches = val.match(/\d{1,4}/g);
            this.value = matches ? matches.join(' ') : '';
            
            if (this.value.trim() === '') {
                if (btnVerifyConfirm) btnVerifyConfirm.setAttribute('disabled', 'true');
                if (emptyState) emptyState.classList.remove('d-none');
                if (activeForm) activeForm.classList.add('d-none');
                if (searchErrorMsg) searchErrorMsg.style.display = 'none';
                clearDetailsDOM();
            }
        });
    }

    if (btnSearchAadhaar) {
        btnSearchAadhaar.onclick = () => {
            searchPatientByAadhaar(aadhaarInput.value);
        };
    }

    const btnClearAadhaar = document.getElementById('btn-clear-aadhaar');
    if (btnClearAadhaar) {
        btnClearAadhaar.onclick = () => {
            closeWorkspace();
        };
    }

    // Trigger verification confirmation modal
    if (btnVerifyConfirm) {
        btnVerifyConfirm.onclick = () => {
            if (!activePatient) return;
            const modalEl = document.getElementById('confirmVerificationModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        };
    }

    // Modal confirmation action click
    const btnModalConfirmVerify = document.getElementById('btn-modal-confirm-verify');
    if (btnModalConfirmVerify) {
        btnModalConfirmVerify.onclick = () => {
            if (!activePatient) return;

            if (btnVerifyConfirm) btnVerifyConfirm.setAttribute('disabled', 'true');
            if (btnModalConfirmVerify) btnModalConfirmVerify.setAttribute('disabled', 'true');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'api.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (btnVerifyConfirm) btnVerifyConfirm.removeAttribute('disabled');
                if (btnModalConfirmVerify) btnModalConfirmVerify.removeAttribute('disabled');

                // Hide modal
                const modalEl = document.getElementById('confirmVerificationModal');
                const bsModal = bootstrap.Modal.getInstance(modalEl);
                if (bsModal) bsModal.hide();

                if (xhr.status === 200) {
                    const res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        if (window.showToast) window.showToast('Verification Complete', `${activePatient.name} identity authenticated.`, 'success');
                        
                        window.addSystemNotification(
                            'Verification',
                            'Patient Identity Verified',
                            `Aadhaar verification completed successfully for ${activePatient.name} (ID: ${activePatient.id}).`,
                            'success'
                        );

                        // Save target Aadhaar to reload the details card refreshed state
                        const savedAadhaar = activePatient.aadhaar;
                        
                        // Sync mockup and reload queue, logs, stats
                        refreshData();
                        
                        // Reload patient details card to show "Already Verified" status
                        searchPatientByAadhaar(savedAadhaar);
                    } else {
                        if (window.showToast) window.showToast('Error', res.message || 'Verification failed.', 'danger');
                    }
                } else {
                    if (window.showToast) window.showToast('Error', 'Server connection failure.', 'danger');
                }
            };
            xhr.onerror = function () {
                if (btnVerifyConfirm) btnVerifyConfirm.removeAttribute('disabled');
                if (btnModalConfirmVerify) btnModalConfirmVerify.removeAttribute('disabled');
                
                const modalEl = document.getElementById('confirmVerificationModal');
                const bsModal = bootstrap.Modal.getInstance(modalEl);
                if (bsModal) bsModal.hide();

                if (window.showToast) window.showToast('Error', 'Connection failed.', 'danger');
            };
            xhr.send(new URLSearchParams({
                action: 'verify_patient',
                id: activePatient.id,
                status: 'Verified',
                remarks: 'Identity confirmed via Aadhaar match'
            }).toString());
        };
    }

    function closeWorkspace() {
        activePatient = null;
        if (aadhaarInput) aadhaarInput.value = '';
        if (emptyState) emptyState.classList.remove('d-none');
        if (activeForm) activeForm.classList.add('d-none');
        if (searchErrorMsg) searchErrorMsg.style.display = 'none';
        clearDetailsDOM();
        
        if (btnVerifyConfirm) btnVerifyConfirm.setAttribute('disabled', 'true');
        
        refreshData();
    }

    function renderHistory() {
        if (!historyTable) return;
        historyTable.innerHTML = '';
        
        const logs = window.HMCMS_DB.getVerifications();
        if (logs.length === 0) {
            historyTable.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted">No recent verification logs found</td></tr>';
            return;
        }

        logs.slice(0, 5).forEach(log => {
            const tr = document.createElement('tr');
            const badgeClass = window.GlobalBadges.getClass(log.status);

            tr.innerHTML = `
                <td>
                    <div class="fw-semibold text-primary">${log.patientName}</div>
                    <span class="text-muted small">${log.patientId}</span>
                </td>
                <td>Aadhaar Verification</td>
                <td class="text-secondary">${log.remarks}</td>
                <td>${log.timestamp}</td>
                <td class="text-end">
                    <span class="badge-custom ${badgeClass}">${log.status}</span>
                </td>
            `;
            historyTable.appendChild(tr);
        });
    }

    if (queueSearch) queueSearch.addEventListener('input', renderQueue);

    function displayVerificationAlert(message, type = 'danger') {
        const container = document.getElementById('alert-container-verification');
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

    // Export CSV Handler
    const btnExportVerification = document.getElementById('btn-export-verification');
    if (btnExportVerification) {
        btnExportVerification.onclick = function (e) {
            e.preventDefault();
            const logs = window.HMCMS_DB.getVerifications();
            if (logs.length === 0) {
                displayVerificationAlert('No verification records available to export.', 'danger');
                return;
            }

            const patientIds = logs.map(log => log.patientId).join(',');
            window.location.href = 'api.php?action=export_verifications_csv&patient_ids=' + encodeURIComponent(patientIds);

            if (window.showToast) {
                window.showToast('Export Initiated', 'Verification report CSV is downloading.', 'success');
            }
        };
    }

    refreshData();
});
