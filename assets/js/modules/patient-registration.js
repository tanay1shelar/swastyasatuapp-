/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Patient Registration Controller Script
 * 
 * Manages sequential Patient ID & Token generations, camp option dropdowns,
 * photo/document preview, draft saves, and form validation compliance.
 */

document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------------------
    // 1. SELECTORS & STATE
    // -------------------------------------------------------------------------
    const registrationForm = document.getElementById('patient-registration-form');
    const autoPatientId = document.getElementById('auto-patient-id');
    const autoTokenId = document.getElementById('auto-token-id');
    const campSelect = document.getElementById('registration-camp-select');
    const assignedStaff = document.getElementById('registration-assigned-staff');

    // Previews
    const photoInput = document.getElementById('photo-upload-input');
    const photoPreview = document.getElementById('photo-preview-img');
    const docInput = document.getElementById('doc-upload-input');
    const docPreview = document.getElementById('doc-preview-container');

    // Buttons
    const btnReset = document.getElementById('btn-reset-registration');
    const btnDraft = document.getElementById('btn-draft-registration');
    const draftsList = document.getElementById('registration-drafts-list');

    let patientsList = [];
    let activeCamps = [];

    // Default avatars
    const defaultPhotoPreview = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100%25' height='100%25' viewBox='0 0 100 100' preserveAspectRatio='none'%3E%3Crect width='100' height='100' fill='%23f1f5f9'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='8' fill='%2394a3b8'%3EPhotograph Preview%3C/text%3E%3C/svg%3E";

    // -------------------------------------------------------------------------
    // 2. ID ASSIGNMENTS & CAMP DROPDOWNS
    // -------------------------------------------------------------------------
    function initFormState() {
        patientsList = window.HMCMS_DB.getPatients();
        activeCamps = window.HMCMS_DB.getCamps();

        // Increment IDs based on list size
        const nextIdNumber = patientsList.length + 1;
        const nextTokenNumber = 200 + nextIdNumber;

        if (autoPatientId) autoPatientId.textContent = `PAT-${String(nextIdNumber).padStart(3, '0')}`;
        if (autoTokenId) autoTokenId.textContent = `#${nextTokenNumber}`;

        // Populate active camps dropdown
        if (campSelect) {
            campSelect.innerHTML = '<option value="" selected disabled>Select Outreach Camp Site...</option>';
            
            // Only list Scheduled and Active camps
            const validCamps = activeCamps.filter(c => c.status !== 'Completed');
            validCamps.forEach(camp => {
                const opt = document.createElement('option');
                opt.value = camp.name;
                opt.textContent = `${camp.name} (${camp.region})`;
                campSelect.appendChild(opt);
            });

            // Handle camp changes to update assigned doctor station
            campSelect.addEventListener('change', function () {
                const selectedCamp = activeCamps.find(c => c.name === this.value);
                if (selectedCamp && assignedStaff) {
                    assignedStaff.value = selectedCamp.doctor;
                }
            });
        }

        renderDraftsList();
    }

    // -------------------------------------------------------------------------
    // 3. FILE PREVIEW TRIGGERS
    // -------------------------------------------------------------------------
    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    photoPreview.src = e.target.result;
                };
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
                        <span class="fw-semibold">${file.name}</span><br>
                        <span class="text-muted" style="font-size: 10px;">(${(file.size / 1024).toFixed(1)} KB)</span>
                    </div>
                `;
            } else {
                docPreview.innerHTML = `
                    <i class="bi bi-file-earmark-medical fs-1 d-block mb-1 text-muted"></i>
                    <span>Document Preview</span>
                `;
            }
        });
    }

    // -------------------------------------------------------------------------
    // 4. RESET & DRAFT ROUTINES
    // -------------------------------------------------------------------------
    if (btnReset) {
        btnReset.addEventListener('click', function () {
            registrationForm.reset();
            registrationForm.classList.remove('was-validated');
            photoPreview.src = defaultPhotoPreview;
            docPreview.innerHTML = `
                <i class="bi bi-file-earmark-medical fs-1 d-block mb-1 text-muted"></i>
                <span>Document Preview</span>
            `;
            initFormState();
            if (window.showToast) {
                window.showToast('Intake Form Reset', 'All input text fields have been cleared.', 'info');
            }
        });
    }

    if (btnDraft) {
        btnDraft.addEventListener('click', function () {
            const formData = new FormData(registrationForm);
            
            // Validate name presence for drafts
            const name = formData.get('name');
            if (!name || name.trim() === '') {
                if (window.showToast) {
                    window.showToast('Draft Siting Failed', 'A patient name is required to log a draft registry.', 'warning');
                }
                return;
            }

            const draftData = {
                name: name.trim(),
                phone: formData.get('phone'),
                dob: formData.get('dob'),
                gender: formData.get('gender'),
                blood: formData.get('blood'),
                address: formData.get('address'),
                city: formData.get('city'),
                state: formData.get('state'),
                pincode: formData.get('pincode'),
                allergies: formData.get('allergies'),
                chronic: formData.get('chronic'),
                medications: formData.get('medications'),
                emergencyName: formData.get('emergencyName'),
                emergencyRelation: formData.get('emergencyRelation'),
                emergencyPhone: formData.get('emergencyPhone'),
                camp: formData.get('camp'),
                assignedStaff: assignedStaff.value
            };

            // Retrieve current session drafts
            const drafts = JSON.parse(sessionStorage.getItem('hmcms_registration_drafts') || '[]');
            
            // Check if draft for this name already exists to overwrite, or append
            const existingIndex = drafts.findIndex(d => d.name === draftData.name);
            if (existingIndex >= 0) {
                drafts[existingIndex] = draftData;
            } else {
                drafts.push(draftData);
            }

            sessionStorage.setItem('hmcms_registration_drafts', JSON.stringify(drafts));
            
            if (window.showToast) {
                window.showToast('Draft Intake Logged', `Form contents cached for ${draftData.name}.`, 'info');
            }

            renderDraftsList();
        });
    }

    function renderDraftsList() {
        if (!draftsList) return;
        const drafts = JSON.parse(sessionStorage.getItem('hmcms_registration_drafts') || '[]');

        if (drafts.length === 0) {
            draftsList.innerHTML = `
                <div class="py-4 text-muted">
                    <i class="bi bi-journal-medical fs-2 mb-2 d-block text-muted"></i>
                    <span class="small text-secondary">No drafts saved.</span>
                </div>
            `;
            return;
        }

        draftsList.innerHTML = '';
        drafts.forEach((draft, idx) => {
            const div = document.createElement('div');
            div.className = 'p-3 rounded border text-start mb-2 bg-light d-flex justify-content-between align-items-center';
            div.innerHTML = `
                <div style="overflow: hidden; max-width: 140px;">
                    <strong class="text-primary small d-block text-truncate">${draft.name}</strong>
                    <span class="text-muted d-block" style="font-size: 10px;">${draft.camp || 'No Camp Site'}</span>
                </div>
                <div class="d-flex gap-1">
                    <button class="btn-custom btn-custom-sm btn-custom-outline py-1 px-2 load-draft-btn" data-idx="${idx}" title="Restore">Load</button>
                    <button class="btn-custom btn-custom-sm btn-custom-outline text-danger py-1 px-2 del-draft-btn" data-idx="${idx}" title="Delete">&times;</button>
                </div>
            `;

            // Restore draft inputs
            div.querySelector('.load-draft-btn').onclick = function () {
                const targetDraft = drafts[this.getAttribute('data-idx')];
                restoreDraft(targetDraft);
            };

            // Clear draft item
            div.querySelector('.del-draft-btn').onclick = function () {
                const index = parseInt(this.getAttribute('data-idx'));
                const updatedDrafts = drafts.filter((_, i) => i !== index);
                sessionStorage.setItem('hmcms_registration_drafts', JSON.stringify(updatedDrafts));
                renderDraftsList();
                if (window.showToast) {
                    window.showToast('Draft Cleared', 'The selected draft intake card was deleted.', 'info');
                }
            };

            draftsList.appendChild(div);
        });
    }

    function restoreDraft(draft) {
        // Find fields and insert details
        const elements = registrationForm.elements;
        for (let key in draft) {
            if (elements[key]) {
                elements[key].value = draft[key];
            }
        }
        if (window.showToast) {
            window.showToast('Draft Restored', `Loaded registration cache for ${draft.name}.`, 'success');
        }
    }

    // -------------------------------------------------------------------------
    // 5. REGISTRATION SUBMIT
    // -------------------------------------------------------------------------
    if (registrationForm) {
        registrationForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Perform validations
            if (!registrationForm.checkValidity()) {
                registrationForm.classList.add('was-validated');
                if (window.showToast) {
                    window.showToast('Validation Error', 'Please complete all required fields correctly.', 'danger');
                }
                return;
            }

            // Create patient record
            const formData = new FormData(registrationForm);
            const patId = autoPatientId.textContent;
            const token = autoTokenId.textContent;

            // Generate age from DOB
            const dob = new Date(formData.get('dob'));
            const diffMs = Date.now() - dob.getTime();
            const age = Math.abs(new Date(diffMs).getUTCFullYear() - 1970);

            const newPatient = {
                id: patId,
                name: formData.get('name').trim(),
                gender: formData.get('gender'),
                age: age,
                phone: formData.get('phone'),
                token: token,
                checkin: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }),
                status: 'Registered',
                camp: formData.get('camp'),
                assignedStaff: assignedStaff.value
            };

            // Save to sessionStorage database
            patientsList.push(newPatient);
            window.HMCMS_DB.savePatients(patientsList);

            // Trigger success alert
            if (window.showToast) {
                window.showToast(
                    'Patient Registered',
                    `${newPatient.name} successfully registered under ${newPatient.id} (${newPatient.token}).`,
                    'success'
                );
            }

            // If draft exists for this name, delete it
            const drafts = JSON.parse(sessionStorage.getItem('hmcms_registration_drafts') || '[]');
            const updatedDrafts = drafts.filter(d => d.name !== newPatient.name);
            sessionStorage.setItem('hmcms_registration_drafts', JSON.stringify(updatedDrafts));

            // Reset form details and generate next ID
            registrationForm.reset();
            registrationForm.classList.remove('was-validated');
            photoPreview.src = defaultPhotoPreview;
            docPreview.innerHTML = `
                <i class="bi bi-file-earmark-medical fs-1 d-block mb-1 text-muted"></i>
                <span>Document Preview</span>
            `;
            initFormState();
        });
    }

    // Initialize layout state
    initFormState();
});
