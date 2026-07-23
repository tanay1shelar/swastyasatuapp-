/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Update Patient Profile Event Controller Script
 * 
 * Manages patient directory queries autocomplete dropdown selection,
 * maps form inputs, handles camp changes, and prints thermal attendee badges.
 */

document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------------------
    // 1. SELECTORS & STATE
    // -------------------------------------------------------------------------
    const searchInput = document.getElementById('patient-update-search');
    const suggestionsBox = document.getElementById('search-suggestions');
    
    // Panel Selectors
    const emptyState = document.getElementById('workspace-empty-state');
    const leftPanel = document.getElementById('workspace-left-panel');
    const rightPanel = document.getElementById('workspace-right-panel');

    // Left display card
    const cardPhoto = document.getElementById('patient-card-photo');
    const cardName = document.getElementById('patient-card-name');
    const cardId = document.getElementById('patient-card-id');
    const cardCamp = document.getElementById('patient-card-camp');
    const cardDoctor = document.getElementById('patient-card-doctor');
    const cardAadhaar = document.getElementById('patient-card-aadhaar');

    // Right form inputs
    const formUpdate = document.getElementById('patient-update-form');
    const editName = document.getElementById('edit-name');
    const editGender = document.getElementById('edit-gender');
    const editBlood = document.getElementById('edit-blood');
    const editDob = document.getElementById('edit-dob');
    const editPhone = document.getElementById('edit-phone');
    const editEmail = document.getElementById('edit-email');
    const editAddress = document.getElementById('edit-address');
    const editEmergencyName = document.getElementById('edit-emergencyName');
    const editEmergencyRelation = document.getElementById('edit-emergencyRelation');
    const editEmergencyPhone = document.getElementById('edit-emergencyPhone');
    const editAllergies = document.getElementById('edit-allergies');
    const editChronic = document.getElementById('edit-chronic');
    const editMedications = document.getElementById('edit-medications');
    const editCamp = document.getElementById('edit-camp');
    const editDoctor = document.getElementById('edit-doctor');
    const editStatus = document.getElementById('edit-status');

    // Buttons
    const btnCancel = document.getElementById('btn-cancel-update');
    const btnReset = document.getElementById('btn-reset-update');
    const btnPrint = document.getElementById('btn-print-patient-card');

    let patientsList = [];
    let campsList = [];
    let activePatient = null;

    function loadData() {
        patientsList = window.HMCMS_DB.getPatients();
        campsList = window.HMCMS_DB.getCamps();
    }

    // -------------------------------------------------------------------------
    // 2. SUGGESTION AUTOCOMPLETE DROPDOWN
    // -------------------------------------------------------------------------
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            if (query.length < 2) {
                hideSuggestions();
                return;
            }

            // Filter patient matches (Name, ID, Phone, Aadhaar)
            const matches = patientsList.filter(p => {
                return (
                    p.name.toLowerCase().includes(query) ||
                    p.id.toLowerCase().includes(query) ||
                    p.phone.toLowerCase().includes(query) ||
                    p.aadhaar.replace(/\s/g, '').includes(query.replace(/\s/g, ''))
                );
            });

            renderSuggestions(matches);
        });

        // Close on blur check
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                hideSuggestions();
            }
        });
    }

    function renderSuggestions(matches) {
        if (!suggestionsBox) return;
        suggestionsBox.innerHTML = '';

        if (matches.length === 0) {
            suggestionsBox.innerHTML = '<div class="text-center py-2 text-muted small">No patient matching search query found</div>';
            suggestionsBox.style.display = 'block';
            return;
        }

        // Limit results suggestions to 6
        const displayLimit = matches.slice(0, 6);
        displayLimit.forEach(patient => {
            const item = document.createElement('div');
            item.className = 'suggestion-item d-flex justify-content-between align-items-center p-2';
            item.innerHTML = `
                <div>
                    <strong class="text-primary small d-block">${patient.name}</strong>
                    <span class="text-muted" style="font-size: 10px;">ID: ${patient.id} | Phone: ${patient.phone}</span>
                </div>
                <span class="badge bg-secondary-subtle text-secondary small font-monospace">${patient.token}</span>
            `;

            item.addEventListener('click', function () {
                selectPatient(patient);
                hideSuggestions();
            });

            suggestionsBox.appendChild(item);
        });

        suggestionsBox.style.display = 'block';
    }

    function hideSuggestions() {
        if (suggestionsBox) suggestionsBox.style.display = 'none';
    }

    // -------------------------------------------------------------------------
    // 3. SELECTION & FORM POPULATION
    // -------------------------------------------------------------------------
    function selectPatient(patient) {
        activePatient = patient;
        searchInput.value = patient.name;

        // Hide empty card state, reveal edit layouts
        if (emptyState) emptyState.style.setProperty('display', 'none', 'important');
        if (leftPanel) leftPanel.style.setProperty('display', 'block', 'important');
        if (rightPanel) rightPanel.style.setProperty('display', 'block', 'important');

        // Populate left summary badge card
        if (cardPhoto) cardPhoto.src = patient.photo;
        if (cardName) cardName.textContent = patient.name;
        if (cardId) cardId.textContent = patient.id;
        if (cardCamp) cardCamp.textContent = patient.camp;
        if (cardDoctor) cardDoctor.textContent = patient.doctor;
        if (cardAadhaar) cardAadhaar.textContent = patient.aadhaar;

        // Populate camp dropdown options dynamically
        if (editCamp) {
            editCamp.innerHTML = '';
            campsList.forEach(camp => {
                const opt = document.createElement('option');
                opt.value = camp.name;
                opt.textContent = camp.name;
                if (camp.name === patient.camp) opt.selected = true;
                editCamp.appendChild(opt);
            });
        }

        // Populate right form inputs
        fillFormFields(patient);

        // Bind dropdown change triggers to update consultant doctor automatically
        editCamp.onchange = function () {
            const campObj = campsList.find(c => c.name === this.value);
            if (campObj && editDoctor) {
                editDoctor.value = campObj.doctor;
            }
        };
    }

    function fillFormFields(patient) {
        if (editName) editName.value = patient.name;
        if (editGender) editGender.value = patient.gender;
        if (editBlood) editBlood.value = patient.blood;
        if (editDob) editDob.value = patient.dob;
        if (editPhone) editPhone.value = patient.phone;
        if (editEmail) editEmail.value = patient.email;
        if (editAddress) editAddress.value = patient.address;
        
        if (editEmergencyName) editEmergencyName.value = patient.emergencyName;
        if (editEmergencyRelation) editEmergencyRelation.value = patient.emergencyRelation;
        if (editEmergencyPhone) editEmergencyPhone.value = patient.emergencyPhone;
        
        if (editAllergies) editAllergies.value = patient.allergies;
        if (editChronic) editChronic.value = patient.chronic;
        if (editMedications) editMedications.value = patient.medications;
        
        if (editDoctor) editDoctor.value = patient.doctor;
        if (editStatus) editStatus.value = patient.status;
    }

    // -------------------------------------------------------------------------
    // 4. SUBMIT UPDATE OR ACTION CANCELS
    // -------------------------------------------------------------------------
    if (formUpdate) {
        formUpdate.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!activePatient) return;

            // Perform validity checks
            if (!formUpdate.checkValidity()) {
                formUpdate.classList.add('was-validated');
                return;
            }

            // Map edited input values back to DB element reference
            activePatient.name = editName.value.trim();
            activePatient.gender = editGender.value;
            activePatient.blood = editBlood.value;
            activePatient.dob = editDob.value;
            activePatient.phone = editPhone.value.trim();
            activePatient.email = editEmail.value.trim();
            activePatient.address = editAddress.value.trim();

            activePatient.emergencyName = editEmergencyName.value.trim();
            activePatient.emergencyRelation = editEmergencyRelation.value.trim();
            activePatient.emergencyPhone = editEmergencyPhone.value.trim();

            activePatient.allergies = editAllergies.value.trim();
            activePatient.chronic = editChronic.value.trim();
            activePatient.medications = editMedications.value.trim();

            activePatient.camp = editCamp.value;
            activePatient.doctor = editDoctor.value;
            activePatient.status = editStatus.value;

            // Age calculation
            const dobDate = new Date(activePatient.dob);
            const diffMs = Date.now() - dobDate.getTime();
            activePatient.age = Math.abs(new Date(diffMs).getUTCFullYear() - 1970);

            // Save to mock database sessionStorage
            window.HMCMS_DB.savePatients(patientsList);

            if (window.showToast) {
                window.showToast(
                    'Record Saved',
                    `Patient demographics for ${activePatient.name} updated successfully.`,
                    'success'
                );
            }

            // Update left summary visual badges
            selectPatient(activePatient);
            formUpdate.classList.remove('was-validated');
        });
    }

    if (btnReset && formUpdate) {
        btnReset.addEventListener('click', function () {
            if (activePatient) {
                fillFormFields(activePatient);
                formUpdate.classList.remove('was-validated');
                if (window.showToast) window.showToast('Fields Reverted', 'Inputs restored to patient backup records.', 'info');
            }
        });
    }

    if (btnCancel) {
        btnCancel.addEventListener('click', function () {
            activePatient = null;
            searchInput.value = '';
            
            // Re-render empty state panel
            if (emptyState) emptyState.style.setProperty('display', 'block', 'important');
            if (leftPanel) leftPanel.style.setProperty('display', 'none', 'important');
            if (rightPanel) rightPanel.style.setProperty('display', 'none', 'important');
            
            if (formUpdate) formUpdate.classList.remove('was-validated');
        });
    }

    if (btnPrint) {
        btnPrint.addEventListener('click', function () {
            if (!activePatient) return;
            
            if (window.showToast) {
                window.showToast(
                    'Card Spooled to Printer',
                    `Outreach Badge thermal output queued for ${activePatient.name} (${activePatient.token}).`,
                    'success'
                );
            }
        });
    }

    // -------------------------------------------------------------------------
    // 5. INITIALIZE
    // -------------------------------------------------------------------------
    loadData();

    // Check URL parameters for auto-selection integration from patient list directory
    const urlParams = new URLSearchParams(window.location.search);
    const paramId = urlParams.get('id');
    if (paramId) {
        const found = patientsList.find(p => p.id.toUpperCase() === paramId.toUpperCase());
        if (found) {
            setTimeout(() => {
                selectPatient(found);
            }, 100);
        }
    }
});
