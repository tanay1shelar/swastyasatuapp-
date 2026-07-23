/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Centralized Patient Management Module (patient.js)
 * 
 * Unifies Patient Registration intake, profile Updates, and the Patient List directory
 * into one file, sharing the sessionStorage database and dispatching alerts.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Determine active context on DOM load
    const isRegistrationPage = document.getElementById('patient-registration-form') !== null;
    const isUpdatePage = document.getElementById('patient-update-form') !== null;
    const isListPage = document.getElementById('patient-list-table-body') !== null;

    if (isRegistrationPage) initRegistrationModule();
    if (isUpdatePage) initUpdateModule();
    if (isListPage) initListModule();

    // =========================================================================
    // I. PATIENT REGISTRATION CONSOLE WORKFLOW
    // =========================================================================
    function initRegistrationModule() {
        const form = document.getElementById('patient-registration-form');
        const autoPatientId = document.getElementById('auto-patient-id');
        const autoTokenId = document.getElementById('auto-token-id');
        const campSelect = document.getElementById('registration-camp-select');
        const assignedStaff = document.getElementById('registration-assigned-staff');

        const photoInput = document.getElementById('photo-upload-input');
        const photoPreview = document.getElementById('photo-preview-img');
        const docInput = document.getElementById('doc-upload-input');
        const docPreview = document.getElementById('doc-preview-container');

        const btnReset = document.getElementById('btn-reset-registration');
        const btnDraft = document.getElementById('btn-draft-registration');
        const draftsList = document.getElementById('registration-drafts-list');

        const defaultPhotoPreview = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100%25' height='100%25' viewBox='0 0 100 100' preserveAspectRatio='none'%3E%3Crect width='100' height='100' fill='%23f1f5f9'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='8' fill='%2394a3b8'%3EPhotograph Preview%3C/text%3E%3C/svg%3E";

        function refreshRegistrationIds() {
            const patientsList = window.HMCMS_DB.getPatients();
            const nextIdx = patientsList.length + 1;
            const nextToken = 200 + nextIdx;
            
            if (autoPatientId) autoPatientId.textContent = `PAT-${String(nextIdx).padStart(3, '0')}`;
            if (autoTokenId) autoTokenId.textContent = `#${nextToken}`;

            if (campSelect) {
                campSelect.innerHTML = '<option value="" selected disabled>Select Outreach Camp Site...</option>';
                const activeCamps = window.HMCMS_DB.getCamps().filter(c => c.status === 'Active');
                activeCamps.forEach(camp => {
                    const opt = document.createElement('option');
                    opt.value = camp.id;
                    opt.textContent = `${camp.name} (${camp.region})`;
                    campSelect.appendChild(opt);
                });
            }

            renderDrafts();
        }

        // Dropdown triggers
        if (campSelect) {
            campSelect.addEventListener('change', function () {
                const camps = window.HMCMS_DB.getCamps();
                const selected = camps.find(c => String(c.id) === String(this.value));
                if (selected && assignedStaff) {
                    assignedStaff.value = selected.doctor;
                }
            });
        }

        // Upload previews
        if (photoInput && photoPreview) {
            photoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => photoPreview.src = e.target.result;
                    reader.readAsDataURL(file);
                } else {
                    photoPreview.src = defaultPhotoPreview;
                }
            });
        }

        if (docInput && docPreview) {
            docInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    docPreview.innerHTML = `
                        <div class="text-success text-center">
                            <i class="bi bi-file-earmark-check-fill fs-1 d-block mb-1"></i>
                            <span class="fw-semibold">${file.name}</span>
                        </div>
                    `;
                } else {
                    docPreview.innerHTML = `<i class="bi bi-file-earmark-medical fs-1 d-block mb-1 text-muted"></i><span>Document Preview</span>`;
                }
            });
        }

        // Submit form
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    if (window.showToast) window.showToast('Validation Error', 'Complete required credentials fields.', 'danger');
                    return;
                }

                const formData = new FormData(form);
                const patId = autoPatientId.textContent;
                const token = autoTokenId.textContent;

                const dobVal = new Date(formData.get('dob'));
                const age = Math.abs(new Date(Date.now() - dobVal.getTime()).getUTCFullYear() - 1970);
                
                const now = new Date();
                const regDateStr = now.toISOString().slice(0, 10);
                const regTimeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });

                const newPatient = {
                    id: patId,
                    photo: photoPreview.src.includes('data:image/svg+xml') 
                        ? 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=80' 
                        : photoPreview.src,
                    name: formData.get('name').trim(),
                    gender: formData.get('gender'),
                    age: age,
                    dob: formData.get('dob'),
                    blood: formData.get('blood'),
                    phone: formData.get('phone'),
                    email: formData.get('email') || 'n/a',
                    aadhaar: formData.get('aadhaar') ? formData.get('aadhaar').trim() : '',
                    address: formData.get('address'),
                    allergies: formData.get('allergies') || 'None',
                    chronic: formData.get('chronic') || 'None',
                    medications: formData.get('medications') || 'None',
                    emergencyName: formData.get('emergencyName'),
                    emergencyRelation: formData.get('emergencyRelation'),
                    emergencyPhone: formData.get('emergencyPhone'),
                    token: token,
                    checkin: regTimeStr,
                    status: 'Registered', // Default allocation
                    camp: formData.get('camp'),
                    assignedStaff: assignedStaff.value,
                    doctor: assignedStaff.value,
                    registrationDate: regDateStr,
                    regNumber: `REG-${Math.floor(100000 + Math.random() * 900000)}`,
                    queueNumber: `Q-${String(window.HMCMS_DB.getPatients().length + 1).padStart(3, '0')}`
                };

                // Save patient via direct API POST
                let registrationSuccess = false;
                try {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'api.php', false); // Synchronous
                    
                    const body = new FormData(form);
                    body.append('action', 'register_patient');
                    
                    xhr.send(body);
                    if (xhr.status === 200) {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            registrationSuccess = true;
                        } else {
                            if (window.showToast) window.showToast('Registration Failed', res.message || 'Error occurred.', 'danger');
                            return;
                        }
                    } else {
                        if (window.showToast) window.showToast('Registration Error', 'Server connection failure.', 'danger');
                        return;
                    }
                } catch (e) {
                    console.error(e);
                    if (window.showToast) window.showToast('Registration Error', 'Connection failed.', 'danger');
                    return;
                }

                if (!registrationSuccess) return;

                // Clear draft if matching name
                const drafts = JSON.parse(sessionStorage.getItem('hmcms_registration_drafts') || '[]');
                const filteredDrafts = drafts.filter(d => d.name !== newPatient.name);
                sessionStorage.setItem('hmcms_registration_drafts', JSON.stringify(filteredDrafts));

                // Log system notifications
                window.addSystemNotification(
                    'Registration',
                    'New Patient Registered',
                    `Patient ${newPatient.name} assigned Patient ID ${newPatient.id} (${newPatient.token}).`,
                    'info'
                );

                if (window.showToast) {
                    window.showToast('Registration Succeeded', `Created record card for ${newPatient.name}.`, 'success');
                }

                // Reset forms
                form.reset();
                form.classList.remove('was-validated');
                photoPreview.src = defaultPhotoPreview;
                docPreview.innerHTML = `<i class="bi bi-file-earmark-medical fs-1 d-block mb-1 text-muted"></i><span>Document Preview</span>`;
                refreshRegistrationIds();
            });
        }

        if (btnReset) {
            btnReset.addEventListener('click', function () {
                form.reset();
                form.classList.remove('was-validated');
                photoPreview.src = defaultPhotoPreview;
                docPreview.innerHTML = `<i class="bi bi-file-earmark-medical fs-1 d-block mb-1 text-muted"></i><span>Document Preview</span>`;
                refreshRegistrationIds();
            });
        }

        if (btnDraft) {
            btnDraft.addEventListener('click', function () {
                const name = form.elements['name'].value.trim();
                if (name === '') {
                    if (window.showToast) window.showToast('Draft Failed', 'Name is required to cache draft cards.', 'warning');
                    return;
                }

                const draft = {};
                const elements = form.elements;
                for (let i = 0; i < elements.length; i++) {
                    if (elements[i].name) draft[elements[i].name] = elements[i].value;
                }

                const drafts = JSON.parse(sessionStorage.getItem('hmcms_registration_drafts') || '[]');
                const idx = drafts.findIndex(d => d.name === name);
                if (idx >= 0) drafts[idx] = draft;
                else drafts.push(draft);

                sessionStorage.setItem('hmcms_registration_drafts', JSON.stringify(drafts));
                if (window.showToast) window.showToast('Draft intake Saved', `Form cached for ${name}.`, 'info');
                renderDrafts();
            });
        }

        function renderDrafts() {
            if (!draftsList) return;
            const drafts = JSON.parse(sessionStorage.getItem('hmcms_registration_drafts') || '[]');
            if (drafts.length === 0) {
                draftsList.innerHTML = `<div class="py-4 text-muted small">No drafts saved.</div>`;
                return;
            }

            draftsList.innerHTML = '';
            drafts.forEach((draft, idx) => {
                const el = document.createElement('div');
                el.className = 'p-3 rounded border text-start mb-2 bg-light d-flex justify-content-between align-items-center';
                el.innerHTML = `
                    <div style="overflow: hidden; max-width: 140px;">
                        <strong class="text-primary small d-block text-truncate">${draft.name}</strong>
                        <span class="text-muted d-block" style="font-size: 10px;">${draft.camp || 'No Camp'}</span>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn-custom btn-custom-sm btn-custom-outline py-1 px-2 load-d" data-idx="${idx}">Load</button>
                        <button class="btn-custom btn-custom-sm btn-custom-outline text-danger py-1 px-2 del-d" data-idx="${idx}">&times;</button>
                    </div>
                `;

                el.querySelector('.load-d').onclick = function () {
                    const target = drafts[this.getAttribute('data-idx')];
                    for (let key in target) {
                        if (form.elements[key]) form.elements[key].value = target[key];
                    }
                    if (window.showToast) window.showToast('Draft Loaded', `Restored inputs for ${target.name}.`, 'success');
                };

                el.querySelector('.del-d').onclick = function () {
                    const updated = drafts.filter((_, i) => i !== parseInt(this.getAttribute('data-idx')));
                    sessionStorage.setItem('hmcms_registration_drafts', JSON.stringify(updated));
                    renderDrafts();
                };

                draftsList.appendChild(el);
            });
        }

        refreshRegistrationIds();
    }

    // =========================================================================
    // II. PATIENT PROFILE UPDATE MODULE WORKFLOW
    // =========================================================================
    function initUpdateModule() {
        const searchInput = document.getElementById('patient-update-search');
        const suggestionsBox = document.getElementById('search-suggestions');
        
        const emptyState = document.getElementById('workspace-empty-state');
        const leftPanel = document.getElementById('workspace-left-panel');
        const rightPanel = document.getElementById('workspace-right-panel');

        const cardPhoto = document.getElementById('patient-card-photo');
        const cardName = document.getElementById('patient-card-name');
        const cardId = document.getElementById('patient-card-id');
        const cardCamp = document.getElementById('patient-card-camp');
        const cardDoctor = document.getElementById('patient-card-doctor');
        const cardAadhaar = document.getElementById('patient-card-aadhaar');

        const formUpdate = document.getElementById('patient-update-form');
        const editCamp = document.getElementById('edit-camp');
        const editDoctor = document.getElementById('edit-doctor');

        const btnCancel = document.getElementById('btn-cancel-update');
        const btnReset = document.getElementById('btn-reset-update');
        const btnPrint = document.getElementById('btn-print-patient-card');

        let patientsList = window.HMCMS_DB.getPatients();
        let activePatient = null;

        if (searchInput) {
            // Support pressing Enter to immediately search and select matching patient
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = this.value.toLowerCase().trim();
                    if (query.length === 0) return;
                    
                    const found = patientsList.find(p => 
                        (p.id && p.id.toLowerCase() === query) ||
                        (p.name && p.name.toLowerCase() === query) ||
                        (p.phone && p.phone.toLowerCase() === query)
                    ) || patientsList.find(p => 
                        (p.id && p.id.toLowerCase().includes(query)) ||
                        (p.name && p.name.toLowerCase().includes(query)) ||
                        (p.phone && p.phone.toLowerCase().includes(query))
                    );
                    
                    if (found) {
                        selectPatient(found);
                        suggestionsBox.style.display = 'none';
                    } else {
                        if (window.showToast) window.showToast('Patient Not Found', `No patient matched "${this.value}".`, 'warning');
                    }
                }
            });

            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                
                // Immediately load exact matches (like IDs or Phones) on typing/pasting
                const exactMatch = patientsList.find(p => 
                    (p.id && p.id.toLowerCase() === query) ||
                    (p.phone && p.phone.toLowerCase() === query)
                );
                if (exactMatch) {
                    selectPatient(exactMatch);
                    suggestionsBox.style.display = 'none';
                    return;
                }

                if (query.length < 2) {
                    suggestionsBox.style.display = 'none';
                    return;
                }
                const matches = patientsList.filter(p => 
                    (p.name && p.name.toLowerCase().includes(query)) || 
                    (p.id && p.id.toLowerCase().includes(query)) || 
                    (p.registration_number && p.registration_number.toLowerCase().includes(query)) || 
                    (p.phone && p.phone.toLowerCase().includes(query)) || 
                    (p.aadhaar && p.aadhaar.toLowerCase().includes(query))
                );
                renderSuggestions(matches);
            });
        }

        function renderSuggestions(matches) {
            suggestionsBox.innerHTML = '';
            if (matches.length === 0) {
                suggestionsBox.innerHTML = '<div class="p-2 text-center text-muted small">No patient records found</div>';
                suggestionsBox.style.display = 'block';
                return;
            }

            matches.slice(0, 5).forEach(patient => {
                const item = document.createElement('div');
                item.className = 'suggestion-item p-2';
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <strong class="text-primary small">${patient.name}</strong>
                        <span class="badge bg-secondary-subtle text-secondary small">${patient.id}</span>
                    </div>
                `;
                item.onclick = () => {
                    selectPatient(patient);
                    suggestionsBox.style.display = 'none';
                };
                suggestionsBox.appendChild(item);
            });
            suggestionsBox.style.display = 'block';
        }

        function selectPatient(patient) {
            activePatient = patient;
            if (searchInput) searchInput.value = patient.name;

            if (emptyState) emptyState.style.setProperty('display', 'none', 'important');
            if (leftPanel) leftPanel.style.setProperty('display', 'block', 'important');
            if (rightPanel) rightPanel.style.setProperty('display', 'block', 'important');

            if (cardPhoto) cardPhoto.src = patient.photo;
            if (cardName) cardName.textContent = patient.name;
            if (cardId) cardId.textContent = patient.id;
            if (cardCamp) cardCamp.textContent = patient.camp;
            if (cardDoctor) cardDoctor.textContent = patient.doctor;
            if (cardAadhaar) cardAadhaar.textContent = patient.aadhaar;

            // Fill camps dropdown
            if (editCamp) {
                editCamp.innerHTML = '';
                window.HMCMS_DB.getCamps().forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.name;
                    opt.textContent = c.name;
                    if (c.name === patient.camp) opt.selected = true;
                    editCamp.appendChild(opt);
                });
                editCamp.onchange = function () {
                    const activeCamp = window.HMCMS_DB.getCamps().find(c => c.name === this.value);
                    if (activeCamp && editDoctor) editDoctor.value = activeCamp.doctor;
                };
            }

            // Fill form inputs
            fillForm(patient);
        }

        function fillForm(p) {
            const el = formUpdate.elements;
            if (el['name']) el['name'].value = p.name;
            if (el['gender']) el['gender'].value = p.gender;
            if (el['blood']) el['blood'].value = p.blood;
            if (el['dob']) el['dob'].value = p.dob;
            if (el['phone']) el['phone'].value = p.phone;
            if (el['email']) el['email'].value = p.email || '';
            if (el['address']) el['address'].value = p.address;
            if (el['emergencyName']) el['emergencyName'].value = p.emergencyName;
            if (el['emergencyRelation']) el['emergencyRelation'].value = p.emergencyRelation;
            if (el['emergencyPhone']) el['emergencyPhone'].value = p.emergencyPhone;
            if (el['allergies']) el['allergies'].value = p.allergies;
            if (el['chronic']) el['chronic'].value = p.chronic;
            if (el['medications']) el['medications'].value = p.medications;
            if (editDoctor) editDoctor.value = p.doctor;
            if (el['status']) el['status'].value = p.status;
        }

        if (formUpdate) {
            formUpdate.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!formUpdate.checkValidity()) {
                    formUpdate.classList.add('was-validated');
                    return;
                }

                // Update active reference keys
                const el = formUpdate.elements;
                activePatient.name = el['name'].value.trim();
                activePatient.gender = el['gender'].value;
                activePatient.blood = el['blood'].value;
                activePatient.dob = el['dob'].value;
                activePatient.phone = el['phone'].value.trim();
                activePatient.email = el['email'] ? el['email'].value.trim() : '';
                activePatient.address = el['address'].value.trim();
                activePatient.emergencyName = el['emergencyName'].value.trim();
                activePatient.emergencyRelation = el['emergencyRelation'].value.trim();
                activePatient.emergencyPhone = el['emergencyPhone'].value.trim();
                activePatient.allergies = el['allergies'].value.trim();
                activePatient.chronic = el['chronic'].value.trim();
                activePatient.medications = el['medications'].value.trim();
                activePatient.camp = editCamp.value;
                activePatient.doctor = editDoctor.value;
                activePatient.status = el['status'].value;

                // Sync storage list
                window.HMCMS_DB.savePatients(patientsList);

                // Dispatch system alerts
                window.addSystemNotification(
                    'Registration',
                    'Patient Profile Updated',
                    `Patient ${activePatient.name} (ID: ${activePatient.id}) records modified in session registry.`,
                    'info'
                );

                if (window.showToast) window.showToast('Profile Updates Saved', `Patient details saved successfully.`, 'success');
                selectPatient(activePatient);
            });
        }

        if (btnReset) {
            btnReset.addEventListener('click', function () {
                if (activePatient) fillForm(activePatient);
            });
        }

        if (btnCancel) {
            btnCancel.addEventListener('click', function () {
                activePatient = null;
                if (searchInput) searchInput.value = '';
                if (emptyState) emptyState.style.setProperty('display', 'block', 'important');
                if (leftPanel) leftPanel.style.setProperty('display', 'none', 'important');
                if (rightPanel) rightPanel.style.setProperty('display', 'none', 'important');
            });
        }

        if (btnPrint) {
            btnPrint.onclick = () => {
                if (activePatient && window.showToast) {
                    window.showToast('Intake Card Spooled', `Thermal print request dispatched for ${activePatient.name}.`, 'success');
                }
            };
        }

        // Parameter navigation loader
        const urlParams = new URLSearchParams(window.location.search);
        const paramId = urlParams.get('id');
        if (paramId) {
            const found = patientsList.find(p => p.id.toUpperCase() === paramId.toUpperCase());
            if (found) selectPatient(found);
        }
    }

    // =========================================================================
    // III. PATIENT DIRECTORY LIST DIRECTORY WORKFLOW
    // =========================================================================
    function initListModule() {
        const statsTotal = document.getElementById('stats-total-patients');
        const statsVerified = document.getElementById('stats-verified-patients');
        const statsPending = document.getElementById('stats-pending-patients');
        const statsToday = document.getElementById('stats-today-patients');

        const searchInput = document.getElementById('patient-search-input');
        const campFilter = document.getElementById('filter-camp');
        const statusFilter = document.getElementById('filter-status');
        const sortBySelect = document.getElementById('sort-by');
        const btnRefresh = document.getElementById('btn-refresh-list');

        const tableBody = document.getElementById('patient-list-table-body');
        const pagInfo = document.getElementById('patient-list-pagination-info');
        const pagNav = document.getElementById('patient-list-pagination-nav');

        const modalBody = document.getElementById('viewPatientModalBody');
        const modalPrintBtn = document.getElementById('modal-btn-print-card');
        const deleteName = document.getElementById('deletePatientName');
        const confirmDeleteBtn = document.getElementById('confirmDeletePatientBtn');

        let patientsList = [];
        let filtered = [];
        let currentPage = 1;
        const limit = 10;
        let activePatientToDelete = null;

        function refreshData() {
            patientsList = window.HMCMS_DB.getPatients();
            filtered = [...patientsList];
            
            // Render Stats counters
            if (statsTotal) statsTotal.textContent = patientsList.length;
            if (statsVerified) {
                statsVerified.textContent = patientsList.filter(p => 
                    p.status === 'Verified' || p.status === 'Completed' || p.status === 'In Consultation'
                ).length;
            }
            if (statsPending) {
                statsPending.textContent = patientsList.filter(p => 
                    p.status === 'Registered' || p.status === 'Pending ID' || p.status === 'Waiting'
                ).length;
            }
            if (statsToday) {
                statsToday.textContent = patientsList.filter(p => p.registrationDate === '2026-07-19').length;
            }

            // Fill camps dropdown options
            if (campFilter) {
                campFilter.innerHTML = '<option value="">All Camp Sites</option>';
                window.HMCMS_DB.getCamps().forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.name;
                    opt.textContent = c.name;
                    campFilter.appendChild(opt);
                });
            }

            renderTable();
        }

        function renderTable() {
            if (!tableBody) return;
            tableBody.innerHTML = '';

            // Apply search, filters, sorts
            applyFilters();



            const total = filtered.length;
            const totalPages = Math.ceil(total / limit) || 1;
            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * limit;
            const end = Math.min(start + limit, total);
            const listSlice = filtered.slice(start, end);

            if (pagInfo) {
                pagInfo.textContent = total > 0 
                    ? `Showing ${start + 1} to ${end} of ${total} patient records`
                    : 'No patients found matching the active filters';
            }

            drawPagination(totalPages);

            if (listSlice.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-4 text-muted">No patient records found</td></tr>`;
                return;
            }

            listSlice.forEach(patient => {
                const tr = document.createElement('tr');
                const badgeClass = window.GlobalBadges.getClass(patient.status);

                tr.innerHTML = `
                    <td><strong class="font-monospace text-secondary">${patient.id}</strong></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img class="avatar-table-mini border" src="${patient.photo}" alt="Attendee">
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
                        <span class="badge-custom ${badgeClass} mb-1 d-block">${patient.status}</span>
                        ${(patient.status === 'In Triage' || patient.status === 'In Consultation' || patient.status === 'Completed') && patient.priority 
                            ? `<span class="badge bg-light border text-secondary small d-inline-block mt-1">${patient.priority}</span>` 
                            : ''
                        }
                    </td>
                    <td class="text-end">
                        <button class="btn btn-outline-danger btn-sm py-1 px-2 delete-p" data-id="${patient.id}" title="Delete Patient">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;

                tr.onclick = (e) => {
                    if (e.target.closest('.delete-p')) return;
                    openPatientModal(patient.id);
                };

                const deleteBtn = tr.querySelector('.delete-p');
                if (deleteBtn) {
                    deleteBtn.onclick = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        openDeleteModal(patient.id);
                    };
                }

                tableBody.appendChild(tr);
            });
        }

        function drawPagination(totalPages) {
            if (!pagNav) return;
            pagNav.innerHTML = '';

            const prev = document.createElement('button');
            prev.className = `pagination-btn ${currentPage === 1 ? 'disabled' : ''}`;
            prev.innerHTML = '<i class="bi bi-chevron-left"></i>';
            if (currentPage > 1) prev.onclick = () => { currentPage--; renderTable(); };
            pagNav.appendChild(prev);

            for (let i = 1; i <= totalPages; i++) {
                if (totalPages > 5 && i !== 1 && i !== totalPages && Math.abs(i - currentPage) > 1) {
                    if (i === 2 || i === totalPages - 1) {
                        const dots = document.createElement('span');
                        dots.className = 'mx-1 text-muted';
                        dots.textContent = '...';
                        pagNav.appendChild(dots);
                    }
                    continue;
                }
                const btn = document.createElement('button');
                btn.className = `pagination-btn ${currentPage === i ? 'active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => { currentPage = i; renderTable(); };
                pagNav.appendChild(btn);
            }

            const next = document.createElement('button');
            next.className = `pagination-btn ${currentPage === totalPages ? 'disabled' : ''}`;
            next.innerHTML = '<i class="bi bi-chevron-right"></i>';
            if (currentPage < totalPages) next.onclick = () => { currentPage++; renderTable(); };
            pagNav.appendChild(next);
        }

        function applyFilters() {
            const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const camp = campFilter ? campFilter.value : '';
            const status = statusFilter ? statusFilter.value : '';
            const sort = sortBySelect ? sortBySelect.value : 'name-asc';

            filtered = patientsList.filter(p => {
                const matchesSearch = 
                    (p.name && p.name.toLowerCase().includes(query)) || 
                    (p.id && p.id.toLowerCase().includes(query)) || 
                    (p.registration_number && p.registration_number.toLowerCase().includes(query)) || 
                    (p.phone && p.phone.toLowerCase().includes(query)) || 
                    (p.aadhaar && p.aadhaar.toLowerCase().includes(query));
                const matchesCamp = camp === '' || p.camp === camp;
                const matchesStatus = status === '' || p.status === status;
                return matchesSearch && matchesCamp && matchesStatus;
            });

            filtered.sort((a, b) => {
                if (sort === 'name-asc') return a.name.localeCompare(b.name);
                if (sort === 'name-desc') return b.name.localeCompare(a.name);
                if (sort === 'id-asc') return a.id.localeCompare(b.id);
                if (sort === 'id-desc') return b.id.localeCompare(a.id);
                if (sort === 'reg-desc') return b.registrationDate.localeCompare(a.registrationDate);
                return 0;
            });
        }

        function openPatientModal(id) {
            const patient = patientsList.find(p => p.id === id);
            if (!patient || !modalBody) return;

            const badgeClass = window.GlobalBadges.getClass(patient.status);
            modalBody.innerHTML = `
                <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-3">
                    <img src="${patient.photo}" alt="Portrait" class="rounded-circle border" style="width: 80px; height: 80px; object-fit: cover; border-width: 3px !important;">
                    <div>
                        <span class="badge-custom ${badgeClass} mb-1">${patient.status}</span>
                        <h4 class="fw-bold text-primary m-0">${patient.name}</h4>
                        <span class="text-muted small">Patient ID: <strong>${patient.id}</strong> | Token: <strong>${patient.token}</strong></span>
                    </div>
                </div>
                <div class="row g-3 small text-secondary">
                    <div class="col-6">
                        <span class="d-block text-muted small">Age / Gender</span>
                        <strong>${patient.age} Yrs / ${patient.gender}</strong>
                    </div>
                    <div class="col-6">
                        <span class="d-block text-muted small">Blood Group</span>
                        <strong class="text-primary">${patient.blood}</strong>
                    </div>
                    <div class="col-6 border-top pt-2">
                        <span class="d-block text-muted small">Contact Mobile</span>
                        <strong>${patient.phone}</strong>
                    </div>
                    <div class="col-6 border-top pt-2">
                        <span class="d-block text-muted small">Email Address</span>
                        <strong>${patient.email}</strong>
                    </div>
                    <div class="col-12 border-top pt-2">
                        <span class="d-block text-muted small">Residential Address</span>
                        <span>${patient.address}</span>
                    </div>
                    <div class="col-12 border-top border-bottom py-2 bg-light-subtle rounded mt-2">
                        <h6 class="fw-semibold text-primary mb-1" style="font-size: 11px;"><i class="bi bi-telephone-outbound"></i> Emergency:</h6>
                        <span class="text-muted">Contact Name: <strong class="text-secondary">${patient.emergencyName}</strong> | Phone: <strong class="text-secondary">${patient.emergencyPhone}</strong> (${patient.emergencyRelation})</span>
                    </div>
                    <div class="col-12">
                        <h6 class="fw-semibold text-primary mb-1" style="font-size: 11px;"><i class="bi bi-heart-pulse"></i> Medical History:</h6>
                        <span class="text-muted d-block">Allergies: <strong class="text-danger">${patient.allergies}</strong> | Chronic Conditions: <strong class="text-danger-emphasis">${patient.chronic}</strong></span>
                        <span class="text-muted d-block">Medications: <strong class="text-secondary">${patient.medications}</strong></span>
                    </div>
                </div>
            `;

            if (modalPrintBtn) {
                modalPrintBtn.onclick = () => printBadge(id);
            }

            window.showModal('viewPatientModal');
        }

        function printBadge(id) {
            const p = patientsList.find(p => p.id === id);
            if (p && window.showToast) {
                window.showToast('Printer Spooled', `Dispatched thermal print request for ${p.name}.`, 'success');
            }
        }



        function openDeleteModal(id) {
            activePatientToDelete = id;
            window.showModal('deletePatientModal');
        }

        if (confirmDeleteBtn) {
            confirmDeleteBtn.onclick = function () {
                if (!activePatientToDelete) return;
                
                try {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'api.php', false); // Synchronous GET/POST for consistent adapter operations
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send(new URLSearchParams({
                        action: 'delete_patient',
                        id: activePatientToDelete
                    }).toString());
                    
                    if (xhr.status === 200) {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            window.hideModal('deletePatientModal');
                            displayListAlert('Patient deleted successfully.', 'success');
                            activePatientToDelete = null;
                            currentPage = 1;
                            refreshData();
                            if (window.showToast) {
                                window.showToast('Patient Deleted', 'Patient deleted successfully.', 'success');
                            }
                        } else {
                            displayListAlert(res.message || 'Deletion failed.', 'danger');
                        }
                    } else {
                        displayListAlert('Server error occurred during deletion.', 'danger');
                    }
                } catch (e) {
                    displayListAlert('Connection error: ' + e.message, 'danger');
                }
            };
        }
        function displayListAlert(message, type = 'danger') {
            const container = document.getElementById('alert-container-list');
            if (!container) return;
            container.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show m-3" role="alert">
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
        const btnExportPatients = document.getElementById('btn-export-patients');
        if (btnExportPatients) {
            btnExportPatients.onclick = function (e) {
                e.preventDefault();
                
                if (!filtered || filtered.length === 0) {
                    displayListAlert('No patient records available to export.', 'danger');
                    return;
                }

                const ids = filtered.map(p => p.id).join(',');
                window.location.href = 'api.php?action=export_patients_csv&ids=' + encodeURIComponent(ids);

                if (window.showToast) {
                    window.showToast('Export Initiated', 'Patient list CSV is downloading.', 'success');
                }
            };
        }

        if (btnRefresh) btnRefresh.onclick = () => refreshData();
        if (searchInput) searchInput.addEventListener('input', () => { currentPage = 1; renderTable(); });
        if (campFilter) campFilter.addEventListener('change', () => { currentPage = 1; renderTable(); });
        if (statusFilter) statusFilter.addEventListener('change', () => { currentPage = 1; renderTable(); });
        if (sortBySelect) sortBySelect.addEventListener('change', () => { currentPage = 1; renderTable(); });

        // -------------------------------------------------------------------------
        // PATIENT IMPORT INTEGRATION
        // -------------------------------------------------------------------------
        const btnImportModal = document.getElementById('btn-import-patients-modal');
        const btnImportCancel = document.getElementById('btn-import-cancel');
        const btnImportNext = document.getElementById('btn-import-next');
        const fileInput = document.getElementById('import-file-input');
        const dragDropArea = document.getElementById('drag-drop-area');
        const selectedFilename = document.getElementById('selected-filename');
        const btnTemplate = document.getElementById('btn-download-template');

        let importRows = [];

        if (btnTemplate) {
            btnTemplate.addEventListener('click', function (e) {
                e.preventDefault();
                const templateHeaders = [
                    'Patient ID', 'Registration Number', 'Patient Name', 'Age', 'Gender', 'Date of Birth', 'Blood Group',
                    'Phone Number', 'Email', 'Aadhaar Number', 'Address', 'Village', 'District', 'State',
                    'Emergency Contact', 'Emergency Phone', 'Camp Name', 'Registration Date', 'Verification Status',
                    'Attendance Status', 'Priority', 'Medical Notes'
                ];
                const sampleData = [
                    'PAT-999', 'REG-123456', 'Amit Sharma', '32', 'Male', '1994-05-15', 'O+', '9876543210', 'amit@test.com', '123456789012', '123 Main St', 'Rampur', 'Patna', 'Bihar', 'Rajesh Sharma', '9876543211', 'Rampur Outreach Camp', '2026-07-20', 'Verified', 'Present', 'High', 'Chronic diabetes'
                ];
                const csvContent = "\xEF\xBB\xBF" + templateHeaders.join(',') + "\n" + sampleData.join(',');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'Patient_Import_Template.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }

        if (btnImportModal) {
            btnImportModal.onclick = () => {
                document.getElementById('import-step-upload').style.display = 'block';
                document.getElementById('import-step-preview').style.display = 'none';
                document.getElementById('import-step-progress').style.display = 'none';
                document.getElementById('import-step-report').style.display = 'none';
                
                btnImportNext.textContent = 'Confirm & Import';
                btnImportNext.disabled = true;
                btnImportCancel.textContent = 'Cancel';
                selectedFilename.textContent = 'Supported files: CSV, XLS, XLSX';
                if (fileInput) fileInput.value = '';
                importRows = [];
                
                window.showModal('importPatientsModal');
            };
        }

        if (dragDropArea && fileInput) {
            dragDropArea.onclick = () => fileInput.click();
            dragDropArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                dragDropArea.classList.add('dragover');
            });
            dragDropArea.addEventListener('dragleave', () => {
                dragDropArea.classList.remove('dragover');
            });
            dragDropArea.addEventListener('drop', (e) => {
                e.preventDefault();
                dragDropArea.classList.remove('dragover');
                if (e.dataTransfer.files.length > 0) {
                    handleFileSelect(e.dataTransfer.files[0]);
                }
            });
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0]);
                }
            });
        }

        function handleFileSelect(file) {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['csv', 'xls', 'xlsx'].includes(ext)) {
                if (window.showToast) window.showToast('Invalid File Type', 'Only CSV, XLS, and XLSX files are supported.', 'danger');
                return;
            }

            selectedFilename.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            if (window.showToast) window.showToast('Validating File', 'Decoding spreadsheet data...', 'info');

            const reader = new FileReader();
            reader.onload = function (e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    const sheetData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                    
                    if (sheetData.length < 2) {
                        if (window.showToast) window.showToast('Empty File', 'The document has no data rows.', 'warning');
                        return;
                    }
                    processImportData(sheetData);
                } catch (err) {
                    console.error(err);
                    if (window.showToast) window.showToast('Parsing Failed', 'Format mapping failed.', 'danger');
                }
            };
            reader.readAsArrayBuffer(file);
        }

        function processImportData(sheetData) {
            const row0 = sheetData[0];
            const headers = row0.map(h => String(h || '').trim());
            const expectedHeaders = [
                'Patient ID', 'Registration Number', 'Patient Name', 'Age', 'Gender', 'Date of Birth', 'Blood Group',
                'Phone Number', 'Email', 'Aadhaar Number', 'Address', 'Village', 'District', 'State',
                'Emergency Contact', 'Emergency Phone', 'Camp Name', 'Registration Date', 'Verification Status',
                'Attendance Status', 'Priority', 'Medical Notes'
            ];

            const colIdx = {};
            expectedHeaders.forEach(eh => {
                colIdx[eh] = headers.findIndex(h => h.toLowerCase() === eh.toLowerCase());
            });

            const requiredHeaders = ['Patient Name', 'Age', 'Gender', 'Phone Number', 'Aadhaar Number', 'Camp Name'];
            const missingRequired = requiredHeaders.filter(rh => colIdx[rh] < 0);
            if (missingRequired.length > 0) {
                if (window.showToast) window.showToast('Schema Mismatch', 'Missing required columns: ' + missingRequired.join(', '), 'danger');
                return;
            }

            importRows = [];
            const camps = window.HMCMS_DB.getCamps();
            const existingPatients = window.HMCMS_DB.getPatients();

            for (let i = 1; i < sheetData.length; i++) {
                const row = sheetData[i];
                if (!row || row.length === 0 || row.every(val => val === null || val === '')) continue;

                const patient = {
                    patient_id: colIdx['Patient ID'] >= 0 ? String(row[colIdx['Patient ID']] || '').trim() : '',
                    registration_number: colIdx['Registration Number'] >= 0 ? String(row[colIdx['Registration Number']] || '').trim() : '',
                    name: colIdx['Patient Name'] >= 0 ? String(row[colIdx['Patient Name']] || '').trim() : '',
                    age: colIdx['Age'] >= 0 ? String(row[colIdx['Age']] || '').trim() : '',
                    gender: colIdx['Gender'] >= 0 ? String(row[colIdx['Gender']] || '').trim() : '',
                    dob: colIdx['Date of Birth'] >= 0 ? String(row[colIdx['Date of Birth']] || '').trim() : '',
                    blood: colIdx['Blood Group'] >= 0 ? String(row[colIdx['Blood Group']] || '').trim() : '',
                    phone: colIdx['Phone Number'] >= 0 ? String(row[colIdx['Phone Number']] || '').trim() : '',
                    email: colIdx['Email'] >= 0 ? String(row[colIdx['Email']] || '').trim() : '',
                    aadhaar: colIdx['Aadhaar Number'] >= 0 ? String(row[colIdx['Aadhaar Number']] || '').trim() : '',
                    address: colIdx['Address'] >= 0 ? String(row[colIdx['Address']] || '').trim() : '',
                    village: colIdx['Village'] >= 0 ? String(row[colIdx['Village']] || '').trim() : '',
                    district: colIdx['District'] >= 0 ? String(row[colIdx['District']] || '').trim() : '',
                    state: colIdx['State'] >= 0 ? String(row[colIdx['State']] || '').trim() : '',
                    emergency_contact: colIdx['Emergency Contact'] >= 0 ? String(row[colIdx['Emergency Contact']] || '').trim() : '',
                    emergency_phone: colIdx['Emergency Phone'] >= 0 ? String(row[colIdx['Emergency Phone']] || '').trim() : '',
                    camp_name: colIdx['Camp Name'] >= 0 ? String(row[colIdx['Camp Name']] || '').trim() : '',
                    registration_date: colIdx['Registration Date'] >= 0 ? String(row[colIdx['Registration Date']] || '').trim() : '',
                    verification_status: colIdx['Verification Status'] >= 0 ? String(row[colIdx['Verification Status']] || '').trim() : '',
                    attendance_status: colIdx['Attendance Status'] >= 0 ? String(row[colIdx['Attendance Status']] || '').trim() : '',
                    priority: colIdx['Priority'] >= 0 ? String(row[colIdx['Priority']] || '').trim() : 'Low',
                    medical_notes: colIdx['Medical Notes'] >= 0 ? String(row[colIdx['Medical Notes']] || '').trim() : 'None',
                    rowNum: i + 1
                };

                const errors = [];
                if (!patient.name) errors.push('Name is required.');
                
                const parsedAge = parseInt(patient.age);
                if (isNaN(parsedAge) || parsedAge <= 0) errors.push('Age must be a positive integer.');
                
                if (!['Male', 'Female', 'Other'].includes(patient.gender)) errors.push('Gender must be Male, Female, or Other.');
                
                const cleanPhone = patient.phone.replace(/[^0-9]/g, '');
                if (cleanPhone.length !== 10) errors.push('Phone must be 10 digits.');
                else patient.phone = cleanPhone;

                const cleanAadhaar = patient.aadhaar.replace(/[^0-9]/g, '');
                if (cleanAadhaar.length !== 12) errors.push('Aadhaar must be 12 digits.');
                else patient.aadhaar = cleanAadhaar;

                const normalizeCampName = (str) => String(str || '').trim().toLowerCase().replace(/\s+/g, ' ');
                const normalizedInputCamp = normalizeCampName(patient.camp_name);
                const foundCamp = camps.find(c => normalizeCampName(c.name) === normalizedInputCamp);

                if (!foundCamp) {
                    errors.push(`Camp '${patient.camp_name}' does not exist in HMCMS.`);
                } else if (foundCamp.status !== 'Active') {
                    errors.push(`Camp '${patient.camp_name}' exists but is currently '${foundCamp.status}'. Only Active camps can accept imported patients.`);
                }

                const validBloods = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                if (patient.blood && !validBloods.includes(patient.blood)) errors.push('Invalid Blood Group.');

                let isDuplicate = false;
                if (errors.length === 0) {
                    const dupAadhaar = existingPatients.some(p => p.aadhaar.replace(/[^0-9]/g, '') === patient.aadhaar);
                    const dupPhone = existingPatients.some(p => p.phone.replace(/[^0-9]/g, '') === patient.phone);
                    if (dupAadhaar) {
                        isDuplicate = true;
                        patient.validation_result = 'Duplicate Aadhaar (Skipped)';
                    } else if (dupPhone) {
                        isDuplicate = true;
                        patient.validation_result = 'Duplicate Phone Number (Skipped)';
                    }
                }

                if (errors.length > 0) {
                    patient.status_flag = 'Failed';
                    patient.validation_result = errors.join(' ');
                } else if (isDuplicate) {
                    patient.status_flag = 'Skipped';
                } else {
                    patient.status_flag = 'Valid';
                    patient.validation_result = 'Ready to Import';
                }

                importRows.push(patient);
            }

            renderImportPreview();
        }

        function renderImportPreview() {
            const previewBody = document.getElementById('import-preview-table-body');
            if (!previewBody) return;
            previewBody.innerHTML = '';

            let validCount = 0;
            let invalidCount = 0;
            let skippedCount = 0;

            importRows.forEach(p => {
                if (p.status_flag === 'Valid') validCount++;
                else if (p.status_flag === 'Skipped') skippedCount++;
                else invalidCount++;

                const tr = document.createElement('tr');
                let badgeClass = 'bg-success-subtle text-success';
                let rowClass = '';
                if (p.status_flag === 'Failed') {
                    badgeClass = 'bg-danger-subtle text-danger';
                    rowClass = 'table-danger';
                } else if (p.status_flag === 'Skipped') {
                    badgeClass = 'bg-warning-subtle text-warning';
                    rowClass = 'table-warning';
                }

                tr.className = rowClass;
                tr.innerHTML = `
                    <td class="text-center font-monospace">${p.rowNum}</td>
                    <td><strong>${escapeHtml(p.name)}</strong></td>
                    <td>${escapeHtml(p.camp_name)}</td>
                    <td class="text-center font-monospace">${escapeHtml(p.aadhaar)}</td>
                    <td class="text-center"><span class="badge ${badgeClass}">${p.status_flag}</span></td>
                    <td><small class="fw-medium">${escapeHtml(p.validation_result)}</small></td>
                `;
                previewBody.appendChild(tr);
            });

            document.getElementById('preview-total-count').textContent = importRows.length;
            document.getElementById('preview-valid-count').textContent = validCount;
            document.getElementById('preview-invalid-count').textContent = invalidCount + skippedCount;

            document.getElementById('import-step-upload').style.display = 'none';
            document.getElementById('import-step-preview').style.display = 'block';
            
            btnImportNext.disabled = validCount === 0;
        }

        if (btnImportNext) {
            btnImportNext.onclick = function () {
                document.getElementById('import-step-preview').style.display = 'none';
                document.getElementById('import-step-progress').style.display = 'block';
                btnImportNext.disabled = true;

                const validPatients = importRows.filter(p => p.status_flag === 'Valid');
                const totalImport = validPatients.length;
                
                let imported = 0;
                let skipped = importRows.filter(p => p.status_flag === 'Skipped').length;
                let failed = importRows.filter(p => p.status_flag === 'Failed').length;
                
                const errorReport = [];
                importRows.forEach(p => {
                    if (p.status_flag === 'Failed' || p.status_flag === 'Skipped') {
                        errorReport.push({ rowNum: p.rowNum, name: p.name, reason: p.validation_result });
                    }
                });

                let idx = 0;

                function importNext() {
                    if (idx >= totalImport) {
                        showImportFinalReport(imported, skipped, failed, errorReport);
                        return;
                    }

                    const p = validPatients[idx];
                    const pct = Math.round(((idx + 1) / totalImport) * 100);
                    document.getElementById('import-progress-bar').style.width = pct + '%';
                    document.getElementById('import-progress-details').textContent = `Processing row ${p.rowNum} (${p.name})...`;

                    const body = new FormData();
                    body.append('action', 'import_patient');
                    body.append('patient_id', p.patient_id);
                    body.append('registration_number', p.registration_number);
                    body.append('name', p.name);
                    body.append('gender', p.gender);
                    body.append('age', p.age);
                    body.append('dob', p.dob);
                    body.append('blood', p.blood);
                    body.append('phone', p.phone);
                    body.append('email', p.email);
                    body.append('aadhaar', p.aadhaar);
                    body.append('address', p.address);
                    body.append('village', p.village);
                    body.append('district', p.district);
                    body.append('state', p.state);
                    body.append('emergency_contact', p.emergency_contact);
                    body.append('emergency_phone', p.emergency_phone);
                    body.append('camp_name', p.camp_name);
                    body.append('registration_date', p.registration_date);
                    body.append('verification_status', p.verification_status);
                    body.append('attendance_status', p.attendance_status);
                    body.append('priority', p.priority);
                    body.append('medical_notes', p.medical_notes);

                    try {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'api.php', true);
                        xhr.onload = function () {
                            if (xhr.status === 200) {
                                const res = JSON.parse(xhr.responseText);
                                if (res.success) {
                                    imported++;
                                } else {
                                    if (res.message === 'Duplicate Aadhaar' || res.message === 'Duplicate Phone Number') {
                                        skipped++;
                                        errorReport.push({ rowNum: p.rowNum, name: p.name, reason: res.message });
                                    } else {
                                        failed++;
                                        errorReport.push({ rowNum: p.rowNum, name: p.name, reason: res.message });
                                    }
                                }
                            } else {
                                failed++;
                                errorReport.push({ rowNum: p.rowNum, name: p.name, reason: `HTTP Error ${xhr.status}` });
                            }
                            idx++;
                            importNext();
                        };
                        xhr.onerror = function () {
                            failed++;
                            errorReport.push({ rowNum: p.rowNum, name: p.name, reason: 'Network failure' });
                            idx++;
                            importNext();
                        };
                        xhr.send(body);
                    } catch (err) {
                        failed++;
                        errorReport.push({ rowNum: p.rowNum, name: p.name, reason: err.message });
                        idx++;
                        importNext();
                    }
                }

                importNext();
            };
        }

        function showImportFinalReport(imported, skipped, failed, errorReport) {
            document.getElementById('import-step-progress').style.display = 'none';
            document.getElementById('import-step-report').style.display = 'block';

            document.getElementById('report-imported-count').textContent = imported;
            document.getElementById('report-skipped-count').textContent = skipped;
            document.getElementById('report-failed-count').textContent = failed;

            const errorsBody = document.getElementById('import-report-errors-body');
            if (errorsBody) {
                errorsBody.innerHTML = '';
                if (errorReport.length === 0) {
                    errorsBody.innerHTML = '<tr><td colspan="3" class="text-center py-3 text-muted">No skipped or failed rows.</td></tr>';
                } else {
                    errorReport.forEach(err => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td class="text-center font-monospace">${err.rowNum}</td>
                            <td><strong>${escapeHtml(err.name)}</strong></td>
                            <td class="text-danger small">${escapeHtml(err.reason)}</td>
                        `;
                        errorsBody.appendChild(tr);
                    });
                }
            }

            btnImportNext.textContent = 'Import Finished';
            btnImportNext.disabled = true;
            btnImportCancel.textContent = 'Close';

            // Instantly sync tables
            refreshData();
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Close open dropdowns on document clicks
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                    m.classList.remove('show');
                });
            }
        });

        refreshData();
    }
});
