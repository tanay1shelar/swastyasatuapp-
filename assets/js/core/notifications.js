/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Reactive Notifications & Alerts Controller Module (notifications.js)
 * 
 * Manages chronological buckets, updates unread badge indicators, dismisses logs,
 * and handles custom 'hmcms_new_notification' events to update the UI reactively.
 */

(function () {
    // -------------------------------------------------------------------------
    // 1. STATE & UTILITIES
    // -------------------------------------------------------------------------
    function getAlerts() {
        try {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'api.php?action=get_notifications', false); // SYNCHRONOUS
            xhr.send();
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                return res.success ? res.data : [];
            }
        } catch (e) {
            console.error("Notifications Proxy connection error: ", e);
        }
        return [];
    }

    function saveAlerts(alerts) {
        // Not used, read operations are live from DB
    }

    // -------------------------------------------------------------------------
    // 2. DOM EVENT CONTROLLERS
    // -------------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function () {
        const bellBtn = document.getElementById('notification-bell-btn');
        const badge = document.getElementById('notification-badge');
        const dropdownList = document.getElementById('navbar-notifications-list');
        
        // Page specific lists
        const todayList = document.getElementById('notifications-today-list');
        const yesterdayList = document.getElementById('notifications-yesterday-list');
        const olderList = document.getElementById('notifications-older-list');
        const pageBadge = document.getElementById('unread-page-badge');
        
        const searchInput = document.getElementById('notifications-search-input');
        const typeFilter = document.getElementById('notifications-type-filter');
        const priorityFilter = document.getElementById('notifications-priority-filter');
        const btnMarkAll = document.getElementById('btn-mark-all-read');

        function updateBadge(alerts) {
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
                pageBadge.className = unread > 0 
                    ? 'badge bg-danger rounded-pulse px-3 py-1 text-white' 
                    : 'badge bg-secondary rounded-pulse px-3 py-1 text-white';
            }
        }

        function renderDropdown(alerts) {
            if (!dropdownList) return;
            dropdownList.innerHTML = '';

            const slice = alerts.slice(0, 4); // Show latest 4 in menu
            if (slice.length === 0) {
                dropdownList.innerHTML = '<div class="p-3 text-center text-muted small">No notifications found</div>';
                return;
            }

            slice.forEach(alert => {
                const item = document.createElement('div');
                const bg = alert.unread ? 'bg-light-subtle border-start border-3 border-primary' : '';
                item.className = `dropdown-item p-3 border-bottom notification-item-card ${bg}`;
                item.style.cursor = 'pointer';

                let color = 'text-accent';
                if (alert.type === 'danger') color = 'text-danger';
                if (alert.type === 'success') color = 'text-success';
                if (alert.type === 'warning') color = 'text-warning';

                item.innerHTML = `
                    <div class="d-flex align-items-start gap-2">
                        <span class="${color} mt-1"><i class="bi ${alert.icon || 'bi-bell-fill'}"></i></span>
                        <div class="flex-grow-1">
                            <p class="mb-1 text-primary fw-medium small text-wrap">${alert.title}</p>
                            <span class="text-secondary d-block text-truncate" style="font-size: 10px; max-width: 200px;">${alert.message}</span>
                            <span class="text-muted d-block" style="font-size: 9px; margin-top: 2px;">${alert.time}</span>
                        </div>
                        <button class="btn-close" data-id="${alert.id}" style="font-size: 8px;" aria-label="Dismiss"></button>
                    </div>
                `;

                item.onclick = (e) => {
                    if (e.target.closest('.btn-close')) return;
                    openModalDetails(alert.id);
                };

                item.querySelector('.btn-close').onclick = (e) => {
                    e.stopPropagation();
                    deleteSingle(alert.id);
                };

                dropdownList.appendChild(item);
            });
        }

        function renderSectionedPageList(alerts) {
            if (!todayList || !yesterdayList || !olderList) return;

            const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const cat = typeFilter ? typeFilter.value : '';
            const pri = priorityFilter ? priorityFilter.value : '';

            const filtered = alerts.filter(a => {
                const matchesSearch = a.title.toLowerCase().includes(query) || a.message.toLowerCase().includes(query);
                const matchesCat = cat === '' || a.category === cat;
                const matchesPri = pri === '' || a.priority === pri;
                return matchesSearch && matchesCat && matchesPri;
            });

            drawSectionBucket(todayList, filtered.filter(a => a.section === 'Today'), 'No notifications logged today.');
            drawSectionBucket(yesterdayList, filtered.filter(a => a.section === 'Yesterday'), 'No notifications logged yesterday.');
            drawSectionBucket(olderList, filtered.filter(a => a.section === 'Older'), 'No older history logs located.');
        }

        function drawSectionBucket(container, bucket, emptyText) {
            container.innerHTML = '';
            if (bucket.length === 0) {
                container.innerHTML = `<div class="p-3 text-center text-muted small">${emptyText}</div>`;
                return;
            }

            bucket.forEach(alert => {
                const item = document.createElement('div');
                const border = alert.unread ? `border-start border-4 border-${alert.type}` : 'border-start border-4 border-secondary-subtle';
                const bg = alert.unread ? 'bg-light-subtle' : '';
                item.className = `p-3 rounded border mb-3 notification-page-item ${border} ${bg}`;

                let typeClass = 'bg-primary-subtle text-primary';
                if (alert.type === 'danger') typeClass = 'bg-danger-subtle text-danger';
                if (alert.type === 'success') typeClass = 'bg-success-subtle text-success';
                if (alert.type === 'warning') typeClass = 'bg-warning-subtle text-warning';

                let priorityClass = 'bg-secondary-subtle text-secondary';
                if (alert.priority === 'High') priorityClass = 'bg-danger-subtle text-danger border border-danger';
                if (alert.priority === 'Medium') priorityClass = 'bg-warning-subtle text-warning border border-warning';

                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="m-0 fw-semibold text-primary">${alert.title}</h6>
                            <span class="badge ${typeClass} px-2 py-0.5 small" style="font-size: 8px; text-transform: uppercase;">${alert.category}</span>
                            <span class="badge ${priorityClass} px-2 py-0.5 small" style="font-size: 8px; text-transform: uppercase;">${alert.priority}</span>
                        </div>
                        <span class="text-muted small" style="font-size: 11px;"><i class="bi bi-clock"></i> ${alert.time}</span>
                    </div>
                    <p class="text-secondary small mb-3">${alert.message}</p>
                    <div class="d-flex justify-content-between align-items-center border-top pt-2">
                        <span class="text-muted" style="font-size: 10px;">ID: <strong>${alert.id}</strong></span>
                        <div class="d-flex gap-2">
                            ${alert.unread ? `<button class="btn-custom btn-custom-sm btn-custom-outline py-1 px-2 read-page-btn" data-id="${alert.id}">Mark Read</button>` : ''}
                            <button class="btn-custom btn-custom-sm btn-custom-outline text-danger-hover py-1 px-2 delete-page-btn" data-id="${alert.id}">Delete</button>
                        </div>
                    </div>
                `;

                item.onclick = (e) => {
                    if (e.target.closest('button')) return;
                    openModalDetails(alert.id);
                };

                const readBtn = item.querySelector('.read-page-btn');
                if (readBtn) {
                    readBtn.onclick = (e) => {
                        e.stopPropagation();
                        markSingleRead(alert.id);
                    };
                }

                const delBtn = item.querySelector('.delete-page-btn');
                if (delBtn) {
                    delBtn.onclick = (e) => {
                        e.stopPropagation();
                        deleteSingle(alert.id);
                    };
                }

                container.appendChild(item);
            });
        }

        // Actions
        function openModalDetails(id) {
            const list = getAlerts();
            const alert = list.find(a => a.id === id);
            if (!alert) return;

            if (alert.unread) {
                alert.unread = false;
                saveAlerts(list);
                refreshUI();
            }

            const title = document.getElementById('notificationDetailsTitle');
            const body = document.getElementById('notificationDetailsBody');
            if (title) title.innerHTML = `<i class="bi ${alert.icon}"></i> ${alert.title}`;
            if (body) {
                const badgeClass = window.GlobalBadges.getClass(alert.type);
                body.innerHTML = `
                    <div class="row g-2">
                        <div class="col-6">
                            <span class="d-block text-muted small">Alert ID</span>
                            <strong>${alert.id}</strong>
                        </div>
                        <div class="col-6 text-end">
                            <span class="badge ${badgeClass} text-uppercase">${alert.priority} Priority</span>
                        </div>
                        <div class="col-12 border-top pt-2 mt-2">
                            <h6 class="fw-semibold text-primary m-0 mb-1">Message Detail:</h6>
                            <p class="text-secondary small mb-3">${alert.message}</p>
                        </div>
                        <div class="col-12 border-top pt-2 text-muted small">
                            <i class="bi bi-clock"></i> Generated: <strong>${alert.time}</strong>
                        </div>
                    </div>
                `;
            }
            if (window.showModal) window.showModal('notificationDetailsModal');
        }

        function markSingleRead(id) {
            const list = getAlerts();
            const alert = list.find(a => a.id === id);
            if (alert) {
                alert.unread = false;
                saveAlerts(list);
                refreshUI();
            }
        }

        function deleteSingle(id) {
            const list = getAlerts();
            const updated = list.filter(a => a.id !== id);
            saveAlerts(updated);
            refreshUI();
            if (window.showToast) window.showToast('Cleared Log', 'Alert cleared from session databases.', 'info');
        }

        function markAllRead() {
            try {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'api.php', false); // SYNCHRONOUS
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send(new URLSearchParams({ action: 'mark_notifications_read' }).toString());
            } catch (e) {
                console.error(e);
            }
            refreshUI();
            if (window.showToast) window.showToast('All Alerts Read', 'System alerts cleared.', 'success');
        }

        // Bell click clears badge unread count
        if (bellBtn) {
            bellBtn.addEventListener('click', function () {
                const list = getAlerts();
                const count = list.filter(a => a.unread).length;
                if (count > 0) {
                    list.forEach(a => a.unread = false);
                    saveAlerts(list);
                    setTimeout(() => {
                        updateBadge(list);
                        renderDropdown(list);
                        renderSectionedPageList(list);
                    }, 1000);
                }
            });
        }

        if (btnMarkAll) btnMarkAll.onclick = markAllRead;

        if (searchInput) searchInput.addEventListener('input', () => renderSectionedPageList(getAlerts()));
        if (typeFilter) typeFilter.addEventListener('change', () => renderSectionedPageList(getAlerts()));
        if (priorityFilter) priorityFilter.addEventListener('change', () => renderSectionedPageList(getAlerts()));

        // =========================================================================
        // 3. REACTIVE EVENT LISTENER (Window CustomEvents)
        // =========================================================================
        // Whenever window.addSystemNotification is triggered, we receive it here!
        window.addEventListener('hmcms_new_notification', (e) => {
            const newAlert = e.detail;
            const list = getAlerts();
            
            // Re-render UI immediately
            updateBadge(list);
            renderDropdown(list);
            renderSectionedPageList(list);

            // Pop toast alert message of alert details
            let toastType = 'info';
            if (newAlert.type === 'danger') toastType = 'danger';
            if (newAlert.type === 'success') toastType = 'success';
            if (newAlert.type === 'warning') toastType = 'warning';

            if (window.showToast) {
                window.showToast(newAlert.title, newAlert.message, toastType, 5000);
            }
        });

        function refreshUI() {
            const list = getAlerts();
            updateBadge(list);
            renderDropdown(list);
            renderSectionedPageList(list);
        }

        refreshUI();
    });
})();
