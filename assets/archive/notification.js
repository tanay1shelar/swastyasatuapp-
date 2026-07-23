/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Interactive Notifications System Controller
 * 
 * Manages 15 mock alerts (Registration, Verification, Attendance, Camp, Medicine, Emergency),
 * sorts logs into chronological sections (Today, Yesterday, Older), and applies priority filters.
 */

(function () {
    // -------------------------------------------------------------------------
    // 1. CHRONOLOGICAL MOCK ALERTS (15 Records)
    // -------------------------------------------------------------------------
    const defaultAlerts = [
        {
            id: 'ALERT-001',
            title: 'Medicine Stock Low',
            message: 'Paracetamol 650mg is below the critical threshold of 10% in Palwal Camp A. Stock replenishment is required immediately to prevent patient queue delay.',
            type: 'danger',
            category: 'Medicine',
            priority: 'High',
            icon: 'bi-capsule',
            time: '10 mins ago',
            section: 'Today',
            unread: true
        },
        {
            id: 'ALERT-002',
            title: 'Emergency: Pediatric Convulsions',
            message: 'Token #122 in Consultation Room 2 is flagged for pediatric high-grade fever and convulsions. Coordinator dispatch initialized.',
            type: 'danger',
            category: 'Emergency',
            priority: 'High',
            icon: 'bi-exclamation-triangle-fill',
            time: '35 mins ago',
            section: 'Today',
            unread: true
        },
        {
            id: 'ALERT-003',
            title: 'Verification: Biometric Match Retry',
            message: 'Multiple fingerprint scan timeouts recorded for Patient Token #215 (Amit Sharma) under Station 1.',
            type: 'warning',
            category: 'Verification',
            priority: 'Medium',
            icon: 'bi-shield-exclamation',
            time: '2 hours ago',
            section: 'Today',
            unread: true
        },
        {
            id: 'ALERT-004',
            title: 'New Patient Registered',
            message: 'Patient Sunita Verma successfully logged and assigned Token #245 under Apollo medical camp.',
            type: 'info',
            category: 'Registration',
            priority: 'Low',
            icon: 'bi-person-plus-fill',
            time: '4 hours ago',
            section: 'Today',
            unread: false
        },
        {
            id: 'ALERT-005',
            title: 'Camp Shift Briefing Initialized',
            message: 'All volunteers and doctors assigned to Apollo Camp A have reported for shift calibration logs.',
            type: 'info',
            category: 'Camp',
            priority: 'Low',
            icon: 'bi-chat-left-text-fill',
            time: '6 hours ago',
            section: 'Today',
            unread: false
        },
        // Yesterday
        {
            id: 'ALERT-006',
            title: 'Vaccine Cold Storage Temperature Alarm',
            message: 'Sensor alert: Vaccine storage cold fridge box B reached 7.8°C (critical limit: 8°C). Compressor cycle checked.',
            type: 'danger',
            category: 'Medicine',
            priority: 'High',
            icon: 'bi-thermometer-high',
            time: 'Yesterday, 10:15 AM',
            section: 'Yesterday',
            unread: true
        },
        {
            id: 'ALERT-007',
            title: 'Vitals Queue High Attendee Triage',
            message: 'Queue congestion warnings: Fortis Screening Camp registration desk count exceeded 50 active triage waiting cards.',
            type: 'warning',
            category: 'Attendance',
            priority: 'Medium',
            icon: 'bi-people-fill',
            time: 'Yesterday, 02:40 PM',
            section: 'Yesterday',
            unread: false
        },
        {
            id: 'ALERT-008',
            title: 'Emergency: Severe Chest Pains',
            message: 'Token #135 flagged for acute dyspnea and blood pressure spikes. Cardiac ambulance dispatched to Bay 1 exit lane.',
            type: 'danger',
            category: 'Emergency',
            priority: 'High',
            icon: 'bi-heart-pulse-fill',
            time: 'Yesterday, 04:30 PM',
            section: 'Yesterday',
            unread: false
        },
        {
            id: 'ALERT-009',
            title: 'Patient Directory Export Dispatched',
            message: 'Central CSV directory export containing patient attendance logs was successfully compiled by Dr. Aditi Sharma.',
            type: 'info',
            category: 'Camp',
            priority: 'Low',
            icon: 'bi-file-earmark-arrow-down-fill',
            time: 'Yesterday, 05:00 PM',
            section: 'Yesterday',
            unread: false
        },
        {
            id: 'ALERT-010',
            title: 'Camp Shift Completed',
            message: 'Nuh Pediatric Camp completed all active queue items. 119 records uploaded to Narayana partner archives.',
            type: 'success',
            category: 'Camp',
            priority: 'Medium',
            icon: 'bi-check-circle-fill',
            time: 'Yesterday, 06:00 PM',
            section: 'Yesterday',
            unread: false
        },
        // Older
        {
            id: 'ALERT-011',
            title: 'UIDAI Verification Compliant Audit',
            message: 'End-of-week security check: 94.8% of camp patient logs successfully completed Aadhaar biometric audit runs.',
            type: 'success',
            category: 'Verification',
            priority: 'Low',
            icon: 'bi-shield-check',
            time: '3 days ago',
            section: 'Older',
            unread: false
        },
        {
            id: 'ALERT-012',
            title: 'Amoxicillin Antibiotic Batch Arrival',
            message: 'Logistics cargo sync: 500 units of Amoxicillin 500mg capsules restocked at Narayana camp reserves.',
            type: 'info',
            category: 'Medicine',
            priority: 'Low',
            icon: 'bi-box-seam',
            time: '4 days ago',
            section: 'Older',
            unread: false
        },
        {
            id: 'ALERT-013',
            title: 'Alwar Camp Site Clearance Approved',
            message: 'Administrative health inspector issued environment clearance certificate for Alwar community outreach clinic.',
            type: 'success',
            category: 'Camp',
            priority: 'Medium',
            icon: 'bi-file-earmark-check-fill',
            time: '5 days ago',
            section: 'Older',
            unread: false
        },
        {
            id: 'ALERT-014',
            title: 'Daily Attendance Report Compiled',
            message: 'Patient queue waiting average check: 12 minutes average consultation time logged across all doctors.',
            type: 'info',
            category: 'Attendance',
            priority: 'Low',
            icon: 'bi-file-bar-graph-fill',
            time: '6 days ago',
            section: 'Older',
            unread: false
        },
        {
            id: 'ALERT-015',
            title: 'Emergency Medical Evacuation Drill Completed',
            message: 'Volunteer staff successfully simulated rapid transport actions for emergency critical attendees under Narayana camp.',
            type: 'success',
            category: 'Emergency',
            priority: 'Low',
            icon: 'bi-truck',
            time: '1 week ago',
            section: 'Older',
            unread: false
        }
    ];

    function getAlerts() {
        if (!sessionStorage.getItem('hmcms_notifications')) {
            sessionStorage.setItem('hmcms_notifications', JSON.stringify(defaultAlerts));
        }
        return JSON.parse(sessionStorage.getItem('hmcms_notifications'));
    }

    function saveAlerts(alerts) {
        sessionStorage.setItem('hmcms_notifications', JSON.stringify(alerts));
    }

    // -------------------------------------------------------------------------
    // 2. CONTROLLER SETUP
    // -------------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        const bellBtn = document.getElementById('notification-bell-btn');
        const badge = document.getElementById('notification-badge');
        const dropdownList = document.getElementById('navbar-notifications-list');
        
        // Page specific selectors
        const searchInput = document.getElementById('notifications-search-input');
        const typeFilter = document.getElementById('notifications-type-filter');
        const priorityFilter = document.getElementById('notifications-priority-filter');
        
        const todayList = document.getElementById('notifications-today-list');
        const yesterdayList = document.getElementById('notifications-yesterday-list');
        const olderList = document.getElementById('notifications-older-list');
        
        const pageBadge = document.getElementById('unread-page-badge');
        const btnMarkAll = document.getElementById('btn-mark-all-read');

        function updateBadgeCount(alerts) {
            const unread = alerts.filter(a => a.unread).length;
            
            if (badge) {
                if (unread > 0) {
                    badge.textContent = unread;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
            if (pageBadge) {
                pageBadge.textContent = `${unread} Unread Alerts`;
                if (unread > 0) {
                    pageBadge.className = 'badge bg-danger rounded-pill px-3 py-1 text-white';
                } else {
                    pageBadge.className = 'badge bg-secondary rounded-pill px-3 py-1 text-white';
                }
            }
        }

        function renderNavbarDropdown(alerts) {
            if (!dropdownList) return;
            dropdownList.innerHTML = '';

            const recentAlerts = alerts.slice(0, 4);

            if (recentAlerts.length === 0) {
                dropdownList.innerHTML = `
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-bell-slash fs-2 mb-2 d-block text-muted"></i>
                        <span class="small">No notifications logged</span>
                    </div>
                `;
                return;
            }

            recentAlerts.forEach(alert => {
                const item = document.createElement('div');
                const bgClass = alert.unread ? 'bg-light-subtle border-start border-3 border-primary' : '';
                item.className = `dropdown-item p-3 border-bottom notification-item-card ${bgClass}`;
                item.style.cursor = 'pointer';
                
                let iconColor = 'text-accent';
                if (alert.type === 'danger') iconColor = 'text-danger';
                if (alert.type === 'success') iconColor = 'text-success';
                if (alert.type === 'warning') iconColor = 'text-warning';

                item.innerHTML = `
                    <div class="d-flex align-items-start gap-2">
                        <span class="${iconColor} mt-1"><i class="bi ${alert.icon || 'bi-bell-fill'}"></i></span>
                        <div class="flex-grow-1">
                            <p class="mb-1 text-primary fw-medium small text-wrap">${alert.title}</p>
                            <span class="text-secondary d-block text-truncate" style="font-size: 10px; max-width: 200px;">${alert.message}</span>
                            <span class="text-muted d-block" style="font-size: 9px; margin-top: 2px;">${alert.time}</span>
                        </div>
                        <button class="btn-close notification-dismiss-btn" data-id="${alert.id}" style="font-size: 8px;" aria-label="Dismiss"></button>
                    </div>
                `;

                item.addEventListener('click', function (e) {
                    if (e.target.closest('.btn-close')) return;
                    openDetails(alert.id);
                });

                const closeBtn = item.querySelector('.btn-close');
                closeBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    deleteAlert(alert.id);
                });

                dropdownList.appendChild(item);
            });
        }

        // Render logs inside categorized buckets on notifications.php
        function renderSectionedLists(alerts) {
            if (!todayList || !yesterdayList || !olderList) return;

            // Apply search, category, and priority filters
            const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const cat = typeFilter ? typeFilter.value : '';
            const pri = priorityFilter ? priorityFilter.value : '';

            const filtered = alerts.filter(alert => {
                const matchesQuery = alert.title.toLowerCase().includes(query) || alert.message.toLowerCase().includes(query);
                const matchesCat = cat === '' || alert.category === cat;
                const matchesPri = pri === '' || alert.priority === pri;
                return matchesQuery && matchesCat && matchesPri;
            });

            // Bucket buckets
            const todayAlerts = filtered.filter(a => a.section === 'Today');
            const yesterdayAlerts = filtered.filter(a => a.section === 'Yesterday');
            const olderAlerts = filtered.filter(a => a.section === 'Older');

            drawBucket(todayList, todayAlerts, 'No alerts logged today.');
            drawBucket(yesterdayList, yesterdayAlerts, 'No alerts logged yesterday.');
            drawBucket(olderList, olderAlerts, 'No older alerts found.');
        }

        function drawBucket(container, bucketAlerts, emptyMessage) {
            container.innerHTML = '';
            if (bucketAlerts.length === 0) {
                container.innerHTML = `<div class="py-3 text-center text-muted small">${emptyMessage}</div>`;
                return;
            }

            bucketAlerts.forEach(alert => {
                const item = document.createElement('div');
                const borderAccent = alert.unread ? `border-start border-4 border-${alert.type}` : `border-start border-4 border-secondary-subtle`;
                const bgClass = alert.unread ? 'bg-light-subtle' : '';
                
                item.className = `p-3 rounded border mb-3 notification-page-item ${borderAccent} ${bgClass}`;
                
                let typeBadge = 'bg-primary-subtle text-primary';
                if (alert.type === 'danger') typeBadge = 'bg-danger-subtle text-danger';
                if (alert.type === 'success') typeBadge = 'bg-success-subtle text-success';
                if (alert.type === 'warning') typeBadge = 'bg-warning-subtle text-warning';

                let priorityBadge = 'bg-secondary-subtle text-secondary';
                if (alert.priority === 'High') priorityBadge = 'bg-danger-subtle text-danger border border-danger';
                if (alert.priority === 'Medium') priorityBadge = 'bg-warning-subtle text-warning border border-warning';

                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="m-0 fw-semibold text-primary">${alert.title}</h6>
                            <span class="badge ${typeBadge} px-2 py-0.5 small" style="font-size: 8px; text-transform: uppercase;">${alert.category}</span>
                            <span class="badge ${priorityBadge} px-2 py-0.5 small" style="font-size: 8px; text-transform: uppercase;">${alert.priority}</span>
                        </div>
                        <span class="text-muted small" style="font-size: 11px;"><i class="bi bi-clock"></i> ${alert.time}</span>
                    </div>
                    <p class="text-secondary small mb-3" style="line-height: 1.5;">${alert.message}</p>
                    <div class="d-flex justify-content-between align-items-center border-top pt-2">
                        <span class="text-muted" style="font-size: 10px;">Alert ID: <strong>${alert.id}</strong></span>
                        <div class="d-flex gap-2">
                            ${alert.unread ? `<button class="btn-custom btn-custom-sm btn-custom-outline py-1 px-2 read-page-btn" data-id="${alert.id}">Mark Read</button>` : ''}
                            <button class="btn-custom btn-custom-sm btn-custom-outline text-danger-hover py-1 px-2 delete-page-btn" data-id="${alert.id}">Delete</button>
                        </div>
                    </div>
                `;

                item.addEventListener('click', function (e) {
                    if (e.target.closest('button')) return;
                    openDetails(alert.id);
                });

                const readBtn = item.querySelector('.read-page-btn');
                if (readBtn) {
                    readBtn.onclick = function (e) {
                        e.stopPropagation();
                        markSingleRead(alert.id);
                    };
                }

                const delBtn = item.querySelector('.delete-page-btn');
                if (delBtn) {
                    delBtn.onclick = function (e) {
                        e.stopPropagation();
                        deleteAlert(alert.id);
                    };
                }

                container.appendChild(item);
            });
        }

        // -------------------------------------------------------------------------
        // 3. MUTATIONS & ACTIONS
        // -------------------------------------------------------------------------
        function openDetails(alertId) {
            const alerts = getAlerts();
            const alert = alerts.find(a => a.id === alertId);
            if (!alert) return;

            if (alert.unread) {
                alert.unread = false;
                saveAlerts(alerts);
                refreshUI();
            }

            const modalTitle = document.getElementById('notificationDetailsTitle');
            const modalBody = document.getElementById('notificationDetailsBody');
            
            if (modalTitle) modalTitle.innerHTML = `<i class="bi ${alert.icon || 'bi-bell-fill'}"></i> ${alert.title}`;
            if (modalBody) {
                let badgeClass = 'bg-primary-subtle text-primary';
                if (alert.type === 'danger') badgeClass = 'bg-danger-subtle text-danger';
                if (alert.type === 'success') badgeClass = 'bg-success-subtle text-success';
                if (alert.type === 'warning') badgeClass = 'bg-warning-subtle text-warning';

                modalBody.innerHTML = `
                    <div class="row g-2">
                        <div class="col-6">
                            <span class="d-block text-muted small">Alert Reference ID</span>
                            <strong class="font-monospace">${alert.id}</strong>
                        </div>
                        <div class="col-6 text-end">
                            <span class="badge ${badgeClass} text-uppercase">${alert.type}</span>
                        </div>
                        <div class="col-12 border-top pt-3 mt-2">
                            <h6 class="fw-semibold text-primary mb-2">Message Payload:</h6>
                            <p class="text-secondary small mb-3" style="line-height: 1.6;">${alert.message}</p>
                        </div>
                        <div class="col-12 border-top pt-2 text-muted small">
                            <i class="bi bi-clock"></i> Generated: <strong>${alert.time}</strong>
                        </div>
                    </div>
                `;
            }

            if (window.showModal) {
                window.showModal('notificationDetailsModal');
            }
        }

        function markSingleRead(alertId) {
            const alerts = getAlerts();
            const alert = alerts.find(a => a.id === alertId);
            if (alert) {
                alert.unread = false;
                saveAlerts(alerts);
                refreshUI();
            }
        }

        function deleteAlert(alertId) {
            const alerts = getAlerts();
            const updated = alerts.filter(a => a.id !== alertId);
            saveAlerts(updated);
            
            if (window.showToast) {
                window.showToast('Log Item Cleared', 'The alert has been removed from session logs.', 'info');
            }
            refreshUI();
        }

        function markAllAsRead() {
            const alerts = getAlerts();
            alerts.forEach(a => a.unread = false);
            saveAlerts(alerts);
            
            if (window.showToast) {
                window.showToast('System Alerts Read', 'All session alerts marked as read.', 'success');
            }
            refreshUI();
        }

        if (bellBtn) {
            bellBtn.addEventListener('click', function () {
                const alerts = getAlerts();
                const unreadCount = alerts.filter(a => a.unread).length;
                if (unreadCount > 0) {
                    alerts.forEach(a => a.unread = false);
                    saveAlerts(alerts);
                    setTimeout(() => {
                        updateBadgeCount(alerts);
                        renderNavbarDropdown(alerts);
                        renderSectionedLists(alerts);
                    }, 800);
                }
            });
        }

        if (btnMarkAll) btnMarkAll.addEventListener('click', markAllAsRead);

        // Bind filter event listeners
        if (searchInput) searchInput.addEventListener('input', () => renderSectionedLists(getAlerts()));
        if (typeFilter) typeFilter.addEventListener('change', () => renderSectionedLists(getAlerts()));
        if (priorityFilter) priorityFilter.addEventListener('change', () => renderSectionedLists(getAlerts()));

        function refreshUI() {
            const alerts = getAlerts();
            updateBadgeCount(alerts);
            renderNavbarDropdown(alerts);
            renderSectionedLists(alerts);
        }

        refreshUI();
    });
})();
