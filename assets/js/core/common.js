/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Shared Core Utility & Global Helper Module
 * 
 * Provides global clocks, toast alerts, reusable custom modal controls,
 * the global navbar search, status badge color mapping, and notification dispatchers.
 */

// =========================================================================
// 1. SYSTEM CLOCK
// =========================================================================
function initSystemClock() {
    const dateEl = document.getElementById('navbar-current-date');
    const timeEl = document.getElementById('navbar-current-time');
    if (!dateEl && !timeEl) return;

    function updateClock() {
        const now = new Date();
        const dateOptions = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' };
        const dateStr = now.toLocaleDateString('en-US', dateOptions);
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
        const timeStr = now.toLocaleTimeString('en-US', timeOptions);
        
        if (dateEl) dateEl.innerHTML = `<i class="bi bi-calendar3"></i> ${dateStr}`;
        if (timeEl) timeEl.innerHTML = `<i class="bi bi-clock"></i> ${timeStr}`;
    }
    updateClock();
    setInterval(updateClock, 1000);
}

// =========================================================================
// 2. MODALS AND LOADER HELPERS
// =========================================================================
function showLoader(text = 'Syncing active data reserves...') {
    let overlay = document.getElementById('app-loader-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'app-loader-overlay';
        overlay.className = 'loader-overlay';
        overlay.innerHTML = `
            <div class="loader-spinner"></div>
            <div class="loader-text" id="app-loader-text">${text}</div>
        `;
        document.body.appendChild(overlay);
    } else {
        document.getElementById('app-loader-text').textContent = text;
    }
    setTimeout(() => overlay.classList.add('active'), 50);
}

function hideLoader() {
    const overlay = document.getElementById('app-loader-overlay');
    if (overlay) overlay.classList.remove('active');
}

function showToast(title, message, type = 'info', duration = 4000) {
    let container = document.getElementById('app-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'app-toast-container';
        container.className = 'toast-custom-container';
        document.body.appendChild(container);
    }

    let iconClass = 'bi-info-circle-fill';
    if (type === 'success') iconClass = 'bi-check-circle-fill';
    if (type === 'danger') iconClass = 'bi-exclamation-triangle-fill';
    if (type === 'warning') iconClass = 'bi-exclamation-circle-fill';

    const toast = document.createElement('div');
    toast.className = `toast-custom toast-custom-${type}`;
    toast.innerHTML = `
        <i class="bi ${iconClass} toast-custom-icon"></i>
        <div class="toast-custom-content">
            <div class="toast-custom-title">${title}</div>
            <div class="toast-custom-message">${message}</div>
        </div>
        <button class="toast-custom-close">&times;</button>
    `;

    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 50);

    const closeBtn = toast.querySelector('.toast-custom-close');
    closeBtn.onclick = () => dismissToast(toast);

    const autoDismiss = setTimeout(() => dismissToast(toast), duration);

    function dismissToast(el) {
        clearTimeout(autoDismiss);
        el.classList.remove('show');
        el.style.transform = 'translateX(120%)';
        setTimeout(() => {
            el.remove();
            if (container.children.length === 0) container.remove();
        }, 300);
    }
}

function showModal(modalId) {
    const backdrop = document.getElementById(modalId);
    if (!backdrop) return;
    backdrop.classList.add('show');
    document.body.style.overflow = 'hidden';

    const closeBtns = backdrop.querySelectorAll('[data-dismiss="modal"], .modal-custom-close');
    closeBtns.forEach(btn => btn.onclick = () => hideModal(modalId));

    backdrop.onclick = (e) => {
        if (e.target === backdrop) hideModal(modalId);
    };
}

function hideModal(modalId) {
    const backdrop = document.getElementById(modalId);
    if (backdrop) {
        backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// =========================================================================
// 3. REUSABLE BADGES CLASS FORMATTER
// =========================================================================
const GlobalBadges = {
    getClass: function (status) {
        const norm = status ? status.toLowerCase().trim() : '';
        if (norm === 'registered' || norm === 'waiting') return 'badge-custom-primary';
        if (norm === 'verified' || norm === 'completed' || norm === 'present') return 'badge-custom-success';
        if (norm === 'rejected' || norm === 'absent' || norm === 'failed') return 'badge-custom-danger';
        if (norm === 'pending id' || norm === 'pending' || norm === 'late' || norm === 'in triage' || norm === 'in consultation') return 'badge-custom-warning';
        return 'badge bg-secondary';
    }
};

// =========================================================================
// 4. REUSABLE SYSTEM NOTIFICATIONS DISPATCHER
// =========================================================================
function addSystemNotification(category, title, message, type = 'info') {
    const defaultAlerts = [
        {
            id: 'ALERT-001',
            title: 'Medicine Stock Low',
            message: 'Paracetamol 650mg is below the critical threshold of 10% in Palwal Camp A.',
            type: 'danger',
            category: 'Medicine',
            priority: 'High',
            icon: 'bi-capsule',
            time: 'Just now',
            section: 'Today',
            unread: true
        }
    ];

    const alerts = JSON.parse(sessionStorage.getItem('hmcms_notifications') || JSON.stringify(defaultAlerts));
    
    // Icon mapping
    let icon = 'bi-bell-fill';
    if (category === 'Registration') icon = 'bi-person-plus-fill';
    if (category === 'Verification') icon = 'bi-shield-check';
    if (category === 'Attendance') icon = 'bi-calendar-check';
    if (category === 'Camp') icon = 'bi-hospital';
    if (category === 'Medicine') icon = 'bi-capsule';
    if (category === 'Emergency') icon = 'bi-exclamation-triangle-fill';

    // Priority mapping
    let priority = 'Low';
    if (type === 'danger') priority = 'High';
    if (type === 'warning') priority = 'Medium';

    const newAlert = {
        id: `ALERT-${String(alerts.length + 1).padStart(3, '0')}`,
        title: title,
        message: message,
        type: type,
        category: category,
        priority: priority,
        icon: icon,
        time: 'Just now',
        section: 'Today',
        unread: true
    };

    alerts.unshift(newAlert); // Prepend to show on top
    sessionStorage.setItem('hmcms_notifications', JSON.stringify(alerts));

    // Dispatch global CustomEvent to update bell badge in active view
    const event = new CustomEvent('hmcms_new_notification', { detail: newAlert });
    window.dispatchEvent(event);
}

// =========================================================================
// 5. REUSABLE GLOBAL SEARCH COMPONENT
// =========================================================================
function initGlobalSearch() {
    const searchBar = document.getElementById('global-patient-search');
    const suggestions = document.getElementById('global-search-suggestions');
    if (!searchBar || !suggestions) return;

    searchBar.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        if (query.length < 2) {
            suggestions.style.display = 'none';
            return;
        }

        const patientsList = JSON.parse(sessionStorage.getItem('hmcms_patients') || '[]');
        
        // Match Patient ID, Name, Phone, or Aadhaar
        const matches = patientsList.filter(p => {
            return (
                p.id.toLowerCase().includes(query) ||
                p.name.toLowerCase().includes(query) ||
                p.phone.toLowerCase().includes(query) ||
                p.aadhaar.replace(/\s/g, '').includes(query.replace(/\s/g, ''))
            );
        });

        renderGlobalSearchSuggestions(matches);
    });

    function renderGlobalSearchSuggestions(matches) {
        suggestions.innerHTML = '';
        if (matches.length === 0) {
            suggestions.innerHTML = '<div class="text-center py-2 text-muted small">No patient files located</div>';
            suggestions.style.display = 'block';
            return;
        }

        matches.slice(0, 5).forEach(patient => {
            const item = document.createElement('div');
            item.className = 'dropdown-item d-flex justify-content-between align-items-center p-2';
            item.style.cursor = 'pointer';
            item.style.borderRadius = 'var(--radius-sm)';
            item.innerHTML = `
                <div>
                    <strong class="text-primary small d-block">${patient.name}</strong>
                    <span class="text-muted" style="font-size: 10px;">ID: ${patient.id} | Phone: ${patient.phone}</span>
                </div>
                <span class="badge bg-secondary-subtle text-secondary small font-monospace">${patient.token}</span>
            `;

            item.addEventListener('click', function () {
                suggestions.style.display = 'none';
                searchBar.value = '';
                // Redirect user directly to Update Patient workspace with query ID
                window.location.href = `update-patient.php?id=${patient.id}`;
            });

            suggestions.appendChild(item);
        });

        suggestions.style.display = 'block';
    }

    // Close suggestions on click outside
    document.addEventListener('click', function (e) {
        if (!searchBar.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.style.display = 'none';
        }
    });
}

// Initialize hooks
document.addEventListener('DOMContentLoaded', () => {
    initSystemClock();
    initGlobalSearch();
});

// Export to window scope
window.showLoader = showLoader;
window.hideLoader = hideLoader;
window.showToast = showToast;
window.showModal = showModal;
window.hideModal = hideModal;
window.GlobalBadges = GlobalBadges;
window.addSystemNotification = addSystemNotification;
