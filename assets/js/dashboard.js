/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Dashboard Functionality Script
 */

document.addEventListener('DOMContentLoaded', () => {
    // Hide loader
    const loader = document.querySelector('.page-loader');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = '0';
            loader.style.visibility = 'hidden';
        }, 300);
    }

    // Sidebar Toggling
    const sidebarToggle = document.getElementById('sidebarToggle');
    const body = document.body;
    
    // Create overlay for mobile sidebars
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', (e) => {
            e.preventDefault();
            if (window.innerWidth < 992) {
                body.classList.toggle('sidebar-open');
            } else {
                body.classList.toggle('sidebar-collapsed');
                // Store sidebar preference
                localStorage.setItem('sidebar-collapsed', body.classList.contains('sidebar-collapsed'));
            }
        });
    }

    // Dismiss sidebar when clicking overlay
    overlay.addEventListener('click', () => {
        body.classList.remove('sidebar-open');
    });

    // Handle Responsive Resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            body.classList.remove('sidebar-open');
        }
    });

    // Apply sidebar collapsed setting on load if desktop
    if (window.innerWidth >= 992 && localStorage.getItem('sidebar-collapsed') === 'true') {
        body.classList.add('sidebar-collapsed');
    }

    // Live Date and Time Ticker
    const clockElement = document.getElementById('headerClock');
    if (clockElement) {
        const updateClock = () => {
            const now = new Date();
            const options = { 
                weekday: 'short', 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            };
            const dateStr = now.toLocaleDateString('en-US', options);
            const timeStr = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: true 
            });
            clockElement.innerHTML = `<i class="far fa-calendar-alt me-1"></i> ${dateStr} &nbsp;&nbsp; <i class="far fa-clock me-1"></i> ${timeStr}`;
        };
        updateClock();
        setInterval(updateClock, 1000);
    }

    // Set Logged-in Username
    const usernameElement = document.getElementById('navUsername');
    if (usernameElement) {
        const savedUsername = localStorage.getItem('username');
        if (savedUsername) {
            // Capitalize
            const capitalized = savedUsername.charAt(0).toUpperCase() + savedUsername.slice(1);
            usernameElement.innerText = capitalized;
        }
    }

    // Logout Confirmation Modal
    const logoutBtn = document.querySelectorAll('.logout-trigger');
    if (logoutBtn.length > 0) {
        // Build modal dynamically if not exists
        let logoutModal = document.getElementById('logoutModal');
        if (!logoutModal) {
            logoutModal = document.createElement('div');
            logoutModal.id = 'logoutModal';
            logoutModal.className = 'modal fade';
            logoutModal.setAttribute('tabindex', '-1');
            logoutModal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title"><i class="fas fa-sign-out-alt text-danger me-2"></i>Confirm Logout</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-3">
                            <p class="mb-0 text-muted">Are you sure you want to logout of the Healthcare & Medical Camp Management System?</p>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmLogoutBtn">Yes, Logout</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(logoutModal);
        }

        // Bind triggers to show modal
        const modalObj = new bootstrap.Modal(logoutModal);
        logoutBtn.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                modalObj.show();
            });
        });

        // Handle Logout Confirm
        const confirmBtn = document.getElementById('confirmLogoutBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                modalObj.hide();
                // Show loader or clean storage
                localStorage.removeItem('userRole');
                localStorage.removeItem('username');
                
                // Show loading spinner
                const loaderEl = document.createElement('div');
                loaderEl.className = 'page-loader';
                loaderEl.innerHTML = '<div class="spinner-medical"></div>';
                document.body.appendChild(loaderEl);
                
                setTimeout(() => {
                    window.location.href = '../authentication/../authentication/login.php';
                }, 1000);
            });
        }
    }

    // Profile and Change Password Modal Forms Validations
    const profileForm = document.getElementById('profileEditForm');
    if (profileForm) {
        profileForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const saveBtn = profileForm.querySelector('button[type="submit"]');
            const origHtml = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            setTimeout(() => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = origHtml;

                // Close Modal
                const modalEl = document.getElementById('editProfileModal');
                const bootstrapModal = bootstrap.Modal.getInstance(modalEl);
                if (bootstrapModal) bootstrapModal.hide();

                // Alert Toast
                showToast('Success', 'Your profile details have been updated successfully.');
            }, 1200);
        });
    }

    const passwordForm = document.getElementById('changePasswordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const oldPass = document.getElementById('oldPassword');
            const newPass = document.getElementById('newPassword');
            const confPass = document.getElementById('confirmPassword');
            let isValid = true;

            // Clear previous errors
            [oldPass, newPass, confPass].forEach(input => {
                input.classList.remove('is-invalid');
                const errMsg = input.parentElement.querySelector('.invalid-feedback');
                if (errMsg) errMsg.style.display = 'none';
            });

            if (!oldPass.value) {
                oldPass.classList.add('is-invalid');
                isValid = false;
            }
            if (!newPass.value || newPass.value.length < 8) {
                newPass.classList.add('is-invalid');
                isValid = false;
            }
            if (newPass.value !== confPass.value) {
                confPass.classList.add('is-invalid');
                isValid = false;
            }

            if (isValid) {
                const saveBtn = passwordForm.querySelector('button[type="submit"]');
                const origHtml = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

                setTimeout(() => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = origHtml;
                    passwordForm.reset();

                    // Close Modal
                    const modalEl = document.getElementById('changePasswordModal');
                    const bootstrapModal = bootstrap.Modal.getInstance(modalEl);
                    if (bootstrapModal) bootstrapModal.hide();

                    showToast('Success', 'Your password has been changed successfully.');
                }, 1200);
            }
        });
    }

    // Universal search filter for simple dashboard tables
    const tableSearch = document.getElementById('dashboardTableSearch');
    if (tableSearch) {
        tableSearch.addEventListener('input', () => {
            const filter = tableSearch.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // Helper Toast notification creator
    window.showToast = (title, message, bgClass = 'bg-success') => {
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toastId = 'toast_' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong>: ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.getElementById(toastId);
        const toastObj = new bootstrap.Toast(toastEl, { delay: 4000 });
        toastObj.show();

        // Remove element from DOM when hidden
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    };
});
