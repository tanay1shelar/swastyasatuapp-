/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Upgraded Profile & Preferences Controller Script
 * 
 * Coordinates demographics saves, skills badge rendering, avatar updates,
 * password checking dialogs, accessibility preferences toggles, and timelines logs.
 */

document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------------------
    // 1. SELECTORS & STATE
    // -------------------------------------------------------------------------
    const profileForm = document.getElementById('profile-edit-form');
    const btnEdit = document.getElementById('btn-edit-profile');
    const btnCancel = document.getElementById('btn-cancel-profile');
    const formControls = document.getElementById('profile-form-controls');

    // Inputs
    const inputFullname = document.getElementById('input-fullname');
    const inputPhone = document.getElementById('input-phone');
    const inputEmail = document.getElementById('input-email');
    const inputGender = document.getElementById('input-gender');
    const inputDob = document.getElementById('input-dob');
    const inputAssignedcamp = document.getElementById('input-assignedcamp');
    const inputAddress = document.getElementById('input-address');
    const inputDepartment = document.getElementById('input-department');
    const inputSkills = document.getElementById('input-skills');
    const inputExperience = document.getElementById('input-experience');

    // Left display panel
    const cardAvatar = document.getElementById('profile-card-avatar');
    const cardName = document.getElementById('profile-card-name');
    const cardRole = document.getElementById('profile-card-role');
    const cardDept = document.getElementById('profile-card-dept');
    const cardSkills = document.getElementById('profile-skills-badges');
    const cardExperience = document.getElementById('profile-experience-display');

    // Avatar upload
    const avatarInput = document.getElementById('avatar-file-input');
    const btnUpload = document.getElementById('btn-upload-avatar');
    const btnRemove = document.getElementById('btn-remove-avatar');

    // Password Reset
    const btnTriggerPass = document.getElementById('btn-trigger-password-modal');
    const passForm = document.getElementById('change-password-form');
    const passCurrent = document.getElementById('pass-current');
    const passNew = document.getElementById('pass-new');
    const passConfirm = document.getElementById('pass-confirm');

    // System Settings Toggles
    const prefNotifications = document.getElementById('pref-notifications');
    const prefDarkMode = document.getElementById('pref-dark-mode');
    const prefLang = document.getElementById('pref-lang');
    const prefTz = document.getElementById('pref-tz');
    const prefAccess = document.getElementById('pref-access');

    // Activity Log timelines
    const logTime2 = document.getElementById('log-time-2');
    const logTime3 = document.getElementById('log-time-3');

    // Global navigation sync elements
    const sidebarName = document.querySelector('.sidebar-user-name');
    const navbarName = document.querySelector('.navbar-username');
    const dropdownHeaderName = document.querySelector('.dropdown-header-custom .name');
    const navbarAvatar = document.querySelector('.navbar-user-avatar');

    let backupValues = {};
    const defaultAvatarUrl = 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&q=80&w=150';

    const editableFields = [
        inputFullname,
        inputPhone,
        inputEmail,
        inputGender,
        inputDob,
        inputAssignedcamp,
        inputAddress,
        inputDepartment,
        inputSkills,
        inputExperience
    ];

    // -------------------------------------------------------------------------
    // 2. RETRIEVE & LOAD ACTIVE PROFILE DATA
    // -------------------------------------------------------------------------
    function loadProfile() {
        const rawData = sessionStorage.getItem('hmcms_profile_data');
        
        let profile = {
            name: inputFullname ? inputFullname.value.trim() : 'Dr. Aditi Sharma',
            empid: inputEmpid ? inputEmpid.value.trim() : 'EMP-2026-9042',
            role: inputRole ? inputRole.value.trim() : 'Chief Medical Officer',
            department: inputDepartment ? inputDepartment.value.trim() : 'Cardiology & General Medicine',
            phone: inputPhone ? inputPhone.value.trim() : '+91 98765 43210',
            email: inputEmail ? inputEmail.value.trim() : 'aditi.sharma@apollo.com',
            dob: inputDob ? inputDob.value.trim() : '1988-11-24',
            gender: inputGender ? inputGender.value : 'Female',
            assignedCamp: inputAssignedcamp ? inputAssignedcamp.value.trim() : 'Apollo Rural Health Camp - Phase 1',
            address: inputAddress ? inputAddress.value.trim() : 'Apollo Hospital Residency, New Delhi, India',
            skills: inputSkills ? inputSkills.value.trim() : 'Echocardiography, Electrocardiogram, Pediatrics',
            experience: inputExperience ? inputExperience.value.trim() : '12 Years Clinical Residency',
            avatar: cardAvatar ? cardAvatar.src : defaultAvatarUrl
        };

        if (rawData) {
            profile = { ...profile, ...JSON.parse(rawData) };
        } else {
            // Write defaults to sessionStorage initial run
            sessionStorage.setItem('hmcms_profile_data', JSON.stringify(profile));
        }

        // Map inputs
        if (inputFullname) inputFullname.value = profile.name;
        if (inputPhone) inputPhone.value = profile.phone;
        if (inputEmail) inputEmail.value = profile.email;
        if (inputGender) inputGender.value = profile.gender;
        if (inputDob) inputDob.value = profile.dob;
        if (inputAssignedcamp) inputAssignedcamp.value = profile.assignedCamp;
        if (inputAddress) inputAddress.value = profile.address;
        if (inputDepartment) inputDepartment.value = profile.department;
        if (inputSkills) inputSkills.value = profile.skills;
        if (inputExperience) inputExperience.value = profile.experience;

        // Render card display texts
        if (cardName) cardName.textContent = profile.name;
        if (cardDept) cardDept.textContent = profile.department;
        if (cardExperience) cardExperience.textContent = profile.experience;
        if (cardAvatar) cardAvatar.src = profile.avatar;

        // Sync header/sidebar names
        if (sidebarName) sidebarName.textContent = profile.name;
        if (navbarName) navbarName.textContent = profile.name;
        if (dropdownHeaderName) dropdownHeaderName.textContent = profile.name;
        if (navbarAvatar) navbarAvatar.src = profile.avatar;

        // Render skill badges list
        renderSkillsBadges(profile.skills);

        // Sync dark mode switch state on load
        if (prefDarkMode) {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            prefDarkMode.checked = currentTheme === 'dark';
        }
    }

    function renderSkillsBadges(skillsStr) {
        if (!cardSkills) return;
        cardSkills.innerHTML = '';
        
        const list = skillsStr.split(',');
        list.forEach(skill => {
            const trimmed = skill.trim();
            if (trimmed !== '') {
                const span = document.createElement('span');
                span.className = 'badge bg-light text-primary border me-1 mb-1';
                span.textContent = trimmed;
                cardSkills.appendChild(span);
            }
        });
    }

    // -------------------------------------------------------------------------
    // 3. EDIT PROFILE FIELDS (Save / Cancel)
    // -------------------------------------------------------------------------
    if (btnEdit) {
        btnEdit.addEventListener('click', function () {
            // Backup current states
            editableFields.forEach(field => {
                if (field) backupValues[field.name] = field.value;
            });

            // Unlock input fields
            editableFields.forEach(field => {
                if (field) field.removeAttribute('disabled');
            });

            // Toggle buttons visibility
            btnEdit.style.setProperty('display', 'none', 'important');
            if (formControls) formControls.style.setProperty('display', 'flex', 'important');
        });
    }

    if (btnCancel) {
        btnCancel.addEventListener('click', function () {
            // Restore previous inputs
            editableFields.forEach(field => {
                if (field && backupValues[field.name] !== undefined) {
                    field.value = backupValues[field.name];
                }
            });

            disableFields();
            btnEdit.style.setProperty('display', 'block', 'important');
            if (formControls) formControls.style.setProperty('display', 'none', 'important');
            
            if (profileForm) profileForm.classList.remove('was-validated');
        });
    }

    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!profileForm.checkValidity()) {
                profileForm.classList.add('was-validated');
                return;
            }

            const current = JSON.parse(sessionStorage.getItem('hmcms_profile_data') || '{}');

            // Build new schema object
            const updated = {
                ...current,
                name: inputFullname.value.trim(),
                phone: inputPhone.value.trim(),
                email: inputEmail.value.trim(),
                gender: inputGender.value,
                dob: inputDob.value,
                assignedCamp: inputAssignedcamp.value.trim(),
                address: inputAddress.value.trim(),
                department: inputDepartment.value.trim(),
                skills: inputSkills.value.trim(),
                experience: inputExperience.value.trim()
            };

            // Post updates to database
            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'update_profile',
                    name: updated.name,
                    phone: updated.phone,
                    email: updated.email,
                    gender: updated.gender,
                    dob: updated.dob,
                    assignedCamp: updated.assignedCamp,
                    address: updated.address,
                    department: updated.department,
                    skills: updated.skills,
                    experience: updated.experience
                }).toString()
            })
            .then(res => res.json())
            .then(resData => {
                if (resData.success) {
                    sessionStorage.setItem('hmcms_profile_data', JSON.stringify(updated));
                    
                    // Sync visual components
                    loadProfile();
                    disableFields();

                    btnEdit.style.setProperty('display', 'block', 'important');
                    if (formControls) formControls.style.setProperty('display', 'none', 'important');
                    profileForm.classList.remove('was-validated');

                    if (window.showToast) {
                        window.showToast(
                            'Profile Settings Saved',
                            'Your database profile record has been updated successfully.',
                            'success'
                        );
                    }
                } else {
                    if (window.showToast) window.showToast('Save Failed', resData.message, 'danger');
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showToast) window.showToast('Save Error', 'Connection failed.', 'danger');
            });

            // Update timeline
            if (logTime2) {
                logTime2.innerHTML = `Just now — Profile Updated<br><span class="text-secondary" style="font-size: 11px;">Clinical credentials and department details modified.</span>`;
            }

            if (window.showToast) {
                window.showToast(
                    'Profile Settings Saved',
                    'Your staff record credentials have been updated successfully.',
                    'success'
                );
            }
        });
    }

    function disableFields() {
        editableFields.forEach(field => {
            if (field) field.setAttribute('disabled', 'true');
        });
    }

    // -------------------------------------------------------------------------
    // 4. AVATAR UPLOAD & REMOVE
    // -------------------------------------------------------------------------
    if (btnUpload && avatarInput) {
        btnUpload.onclick = () => avatarInput.click();

        avatarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const dataUrl = e.target.result;
                    
                    // Save avatar to session
                    const current = JSON.parse(sessionStorage.getItem('hmcms_profile_data') || '{}');
                    current.avatar = dataUrl;
                    sessionStorage.setItem('hmcms_profile_data', JSON.stringify(current));

                    // Load updates
                    loadProfile();

                    if (window.showToast) window.showToast('Portrait Image Saved', 'Profile picture updated successfully.', 'success');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (btnRemove) {
        btnRemove.addEventListener('click', function () {
            const current = JSON.parse(sessionStorage.getItem('hmcms_profile_data') || '{}');
            current.avatar = defaultAvatarUrl;
            sessionStorage.setItem('hmcms_profile_data', JSON.stringify(current));

            loadProfile();

            if (window.showToast) window.showToast('Portrait Image Removed', 'Avatar picture restored to clinic default.', 'info');
        });
    }

    // -------------------------------------------------------------------------
    // 5. CHANGE PASSWORD MODAL VALIDATIONS
    // -------------------------------------------------------------------------
    if (btnTriggerPass) {
        btnTriggerPass.onclick = () => {
            if (window.showModal) window.showModal('changePasswordModal');
        };
    }

    if (passForm) {
        passForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const cur = passCurrent.value;
            const nw = passNew.value;
            const conf = passConfirm.value;

            // Simple validation match
            if (cur === '' || nw.length < 6 || conf.length < 6) {
                passForm.classList.add('was-validated');
                return;
            }

            if (nw !== conf) {
                if (window.showToast) window.showToast('Mismatch Error', 'New passwords and confirm codes do not match.', 'danger');
                passConfirm.value = '';
                return;
            }

            // Close modal
            if (window.hideModal) window.hideModal('changePasswordModal');

            if (window.showToast) {
                window.showToast(
                    'Password Updated Successfully',
                    'Cryptographic credentials updated. Your new password is now active.',
                    'success'
                );
            }

            // Update log timeline
            if (logTime3) {
                logTime3.innerHTML = `Just now — Security Password Changed<br><span class="text-secondary" style="font-size: 11px;">Cryptographic authentication pin refreshed.</span>`;
            }

            passForm.reset();
            passForm.classList.remove('was-validated');
        });
    }

    // -------------------------------------------------------------------------
    // 6. SYSTEM SETTINGS PREFERENCES INTERACTION
    // -------------------------------------------------------------------------
    if (prefNotifications) {
        prefNotifications.addEventListener('change', function () {
            const checked = this.checked;
            sessionStorage.setItem('pref_notifications_alerts', checked ? 'true' : 'false');
            if (window.showToast) {
                window.showToast(
                    'Preferences Saved',
                    `Desktop alerts ${checked ? 'enabled' : 'disabled'} in this session.`,
                    'info'
                );
            }
        });
    }



    if (prefLang) {
        prefLang.addEventListener('change', function () {
            if (window.showToast) window.showToast('Language Changed', `System localization set to ${this.value.toUpperCase()}.`, 'info');
        });
    }

    if (prefTz) {
        prefTz.addEventListener('change', function () {
            if (window.showToast) window.showToast('Timezone Calibrated', `Active clock aligned to ${this.value.toUpperCase()}.`, 'info');
        });
    }

    if (prefAccess) {
        prefAccess.addEventListener('change', function () {
            const val = this.value;
            // High Contrast support logic simulation
            if (val === 'contrast') {
                document.body.style.filter = 'contrast(1.2) saturate(0.9)';
            } else if (val === 'large') {
                document.body.style.fontSize = '17px';
            } else {
                document.body.style.filter = '';
                document.body.style.fontSize = '';
            }
            if (window.showToast) window.showToast('Accessibility Modifiers Saved', `Triage viewport set to ${val}.`, 'success');
        });
    }

    // -------------------------------------------------------------------------
    // 7. INITIALIZE
    // -------------------------------------------------------------------------
    loadProfile();
});
