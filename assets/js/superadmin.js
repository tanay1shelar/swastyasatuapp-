/**
 * HMCMS Super Admin Module - UI Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initial binding if elements exist
    bindSidebarToggle();
    highlightCurrentPage();
});

// Function to bind the sidebar toggle button
function bindSidebarToggle() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const body = document.body;

    if (toggleBtn && !toggleBtn.dataset.bound) {
        // Create backdrop element dynamically
        let backdrop = document.querySelector('.sidebar-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            body.appendChild(backdrop);
        }

        // Toggle sidebar on button click
        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                body.classList.toggle('sidebar-open');
            } else {
                body.classList.toggle('sidebar-collapsed');
            }
        });

        // Close sidebar on backdrop click (mobile only)
        backdrop.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                body.classList.remove('sidebar-open');
            }
        });

        toggleBtn.dataset.bound = "true"; // Prevent multiple bindings if loaded dynamically
    }
}

// Function to highlight active link in sidebar
function highlightCurrentPage() {
    const currentPath = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    
    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        // If current path matches link href, or we are on root/layout and it's the dashboard link
        if (currentPath === linkPath || (currentPath === '' && linkPath === 'layout.html')) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

// Global function to show success toast notifications
window.showSuccessToast = function(message) {
    const toastEl = document.getElementById('successToast');
    if (toastEl) {
        if (message) {
            const msgEl = toastEl.querySelector('#toastMessage span');
            if (msgEl) msgEl.textContent = message;
        }
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
    }
};
