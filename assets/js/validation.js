/**
 * Healthcare & Medical Camp Management System (HMCMS)
<<<<<<< HEAD
 * Form Validation Helper Script (assets/js/validation.js)
 */
function validateRequiredFields(formElement) {
    return formElement ? formElement.checkValidity() : true;
}
=======
 * Authentication Logic Script
 */

document.addEventListener('DOMContentLoaded', () => {
    // Hide loader if present
    const loader = document.querySelector('.page-loader');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = '0';
            loader.style.visibility = 'hidden';
        }, 400);
    }

    // Toggle Password Visibility
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            // Toggle icon
            const icon = togglePasswordBtn.querySelector('i');
            if (icon) {
                if (type === 'text') {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        });
    }

    // Validate email pattern
    const isValidEmail = (email) => {
        const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    };

    // Show input error
    const showError = (inputEl, message) => {
        inputEl.classList.add('is-invalid');
        const errorEl = inputEl.parentElement.querySelector('.error-message') || 
                        inputEl.parentElement.parentElement.querySelector('.error-message');
        if (errorEl) {
            errorEl.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> ${message}`;
            errorEl.style.display = 'flex';
        }
    };

    // Clear input error
    const clearError = (inputEl) => {
        inputEl.classList.remove('is-invalid');
        const errorEl = inputEl.parentElement.querySelector('.error-message') || 
                        inputEl.parentElement.parentElement.querySelector('.error-message');
        if (errorEl) {
            errorEl.style.display = 'none';
        }
    };

    // Clear all errors
    const clearAllErrors = (formEl) => {
        formEl.querySelectorAll('.form-control, .form-select').forEach(input => {
            clearError(input);
        });
    };

    // Login Form Validation and Submission
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            clearAllErrors(loginForm);

            const roleSelect = document.getElementById('role');
            const usernameInput = document.getElementById('username');
            const passwordInput = document.getElementById('password');

            let isValid = true;

            // 1. Role Selection check
            if (!roleSelect.value) {
                showError(roleSelect, 'Please select your role to proceed.');
                isValid = false;
            }

            // 2. Username/Email Check
            if (!usernameInput.value.trim()) {
                showError(usernameInput, 'Username or Email is required.');
                isValid = false;
            } else if (usernameInput.value.includes('@') && !isValidEmail(usernameInput.value)) {
                showError(usernameInput, 'Please enter a valid email address.');
                isValid = false;
            }

            // 3. Password Check
            if (!passwordInput.value) {
                showError(passwordInput, 'Password is required.');
                isValid = false;
            } else if (passwordInput.value.length < 8) {
                showError(passwordInput, 'Password must be at least 8 characters.');
                isValid = false;
            }

            if (isValid) {
                // Store user details in localStorage to simulate session
                localStorage.setItem('userRole', roleSelect.value);
                localStorage.setItem('username', usernameInput.value.split('@')[0]);

                // Show loading spinner on button
                const submitBtn = loginForm.querySelector('button[type="submit"]');
                const originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Signing in...';

                // Simulate brief API check and login redirect
                setTimeout(() => {
                    // Show success popup (Bootstrap modal/alert)
                    const toastContainer = document.getElementById('toastContainer');
                    if (toastContainer) {
                        toastContainer.innerHTML = `
                            <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        <i class="fas fa-check-circle me-2"></i> Login successful! Redirecting to dashboard...
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    // Role redirect mapping
                    const roleRedirects = {
                        'super-admin': '../dashboard/dashboard-super-admin.html',
                        'camp-admin': '../dashboard/dashboard-camp-admin.html',
                        'doctor': '../dashboard/dashboard-doctor.html',
                        'health-worker': '../dashboard/dashboard-health-worker.html',
                        'citizen': '../dashboard/dashboard-citizen.html'
                    };

                    setTimeout(() => {
                        window.location.href = roleRedirects[roleSelect.value] || '../dashboard/dashboard-citizen.html';
                    }, 1200);
                }, 1000);
            }
        });

        // Event listeners to clear errors on typing
        loginForm.querySelectorAll('.form-control, .form-select').forEach(input => {
            input.addEventListener('input', () => {
                if (input.value) clearError(input);
            });
        });
    }

    // Forgot Password Form Submission
    const forgotForm = document.getElementById('forgotPasswordForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', (e) => {
            e.preventDefault();
            clearAllErrors(forgotForm);

            const emailInput = document.getElementById('email');
            const usernameInput = document.getElementById('username');
            let isValid = true;

            if (!emailInput.value.trim()) {
                showError(emailInput, 'Email address is required.');
                isValid = false;
            } else if (!isValidEmail(emailInput.value.trim())) {
                showError(emailInput, 'Please enter a valid email address.');
                isValid = false;
            }

            if (!usernameInput.value.trim()) {
                showError(usernameInput, 'Username is required.');
                isValid = false;
            }

            if (isValid) {
                const submitBtn = forgotForm.querySelector('button[type="submit"]');
                const originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending...';

                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;

                    // Display success feedback message
                    const alertBox = document.getElementById('forgotSuccessAlert');
                    if (alertBox) {
                        alertBox.classList.remove('d-none');
                        forgotForm.reset();
                    }
                }, 1200);
            }
        });
    }
});
>>>>>>> origin/main
