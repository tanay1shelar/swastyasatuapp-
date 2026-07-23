document.addEventListener("DOMContentLoaded", () => {
    // Inject animated Page Loader Overlay if not present
    if (!document.querySelector('.page-loader-overlay')) {
        const loaderHTML = `
            <div class="page-loader-overlay">
                <div class="loader-content text-center">
                    <div class="heartbeat-logo-wrapper mb-3">
                        <svg class="pulse-svg" viewBox="0 0 100 100" width="80" height="80">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(239, 159, 39, 0.2)" stroke-width="4"/>
                            <circle cx="50" cy="50" r="42" fill="none" stroke="#EF9F27" stroke-width="4" class="pulse-circle"/>
                            <path d="M45 28 h10 v17 h17 v10 h-17 v17 h-10 v-17 h-17 v-10 h17 z" fill="#EF9F27" class="cross-icon" />
                        </svg>
                    </div>
                    <h4 class="text-white font-weight-bold mb-2 tracking-wide brand-title">Swasthya Setu</h4>
                    <p class="text-white-50 small mb-4">Healthcare & Medical Camp Management</p>
                    <div class="loader-progress-bar">
                        <div class="loader-progress-fill"></div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('afterbegin', loaderHTML);
    }

    const loaderOverlay = document.querySelector('.page-loader-overlay');
    setTimeout(() => {
        if (loaderOverlay) {
            loaderOverlay.classList.add('fade-out');
            setTimeout(() => {
                loaderOverlay.remove();
            }, 600);
        }
        document.body.classList.add('is-loaded');
    }, 450);

    // Inject ambient glow elements if not present
    if (!document.querySelector('.ambient-glow-1')) {
        document.body.insertAdjacentHTML('afterbegin', '<div class="ambient-glow-1"></div><div class="ambient-glow-2"></div>');
    }
    // --- TOP SCROLL PROGRESS INDICATOR ---
    if (!document.getElementById('scroll-progress-bar')) {
        const progressBar = document.createElement('div');
        progressBar.id = 'scroll-progress-bar';
        document.body.appendChild(progressBar);
    }
    
    const progressBar = document.getElementById('scroll-progress-bar');
    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const progress = scrollHeight > 0 ? scrollTop / scrollHeight : 0;
        if (progressBar) {
            progressBar.style.transform = `scaleX(${progress})`;
        }
    }, { passive: true });

    // --- WEB AUDIO INTERACTION SOUNDS ---
    function playUISound(type = 'click') {
        if (localStorage.getItem('neo_sound_enabled') === 'false') return;
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            const now = ctx.currentTime;
            if (type === 'click') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(600, now);
                osc.frequency.exponentialRampToValueAtTime(200, now + 0.05);
                gain.gain.setValueAtTime(0.12, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.05);
                osc.start(now);
                osc.stop(now + 0.05);
            } else if (type === 'nav') {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(440, now);
                osc.frequency.exponentialRampToValueAtTime(880, now + 0.08);
                gain.gain.setValueAtTime(0.08, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.08);
                osc.start(now);
                osc.stop(now + 0.08);
            } else if (type === 'toggle') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(520, now);
                osc.frequency.setValueAtTime(660, now + 0.04);
                gain.gain.setValueAtTime(0.1, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.09);
                osc.start(now);
                osc.stop(now + 0.09);
            }
        } catch(e) { }
    }
    window.playUISound = playUISound;

    // --- MOBILE OFF-CANVAS SIDEBAR DRAWER ---
    const sidebar = document.querySelector('.sidebar');
    let backdrop = document.querySelector('.sidebar-backdrop');
    if (!backdrop && sidebar) {
        backdrop = document.createElement('div');
        backdrop.className = 'sidebar-backdrop';
        document.body.appendChild(backdrop);
    }

    function toggleMobileSidebar(open) {
        if (!sidebar) return;
        const isOpen = open !== undefined ? open : !sidebar.classList.contains('show-drawer');
        if (isOpen) {
            sidebar.classList.add('show-drawer');
            if (backdrop) backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
            playUISound('nav');
        } else {
            sidebar.classList.remove('show-drawer');
            if (backdrop) backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
    window.toggleMobileSidebar = toggleMobileSidebar;

    if (backdrop) {
        backdrop.addEventListener('click', () => toggleMobileSidebar(false));
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show-drawer')) {
            toggleMobileSidebar(false);
        }
    });

    // --- GLOBAL KEYBOARD SHORTCUTS ---
    document.addEventListener('keydown', (e) => {
        // Ctrl + K -> Focus active search input
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            const searchInput = document.querySelector('input[type="text"][id$="Search"]');
            if (searchInput) {
                e.preventDefault();
                searchInput.focus();
                showToast('Search input focused (Ctrl + K)', 'info');
            }
        }
        // Alt + T -> Toggle Theme
        if (e.altKey && e.key.toLowerCase() === 't') {
            e.preventDefault();
            if (window.toggleTheme) window.toggleTheme();
        }
    });

    // Smooth transition on sidebar link clicks
    document.querySelectorAll('.sidebar .nav-link, a.btn-nav').forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            playUISound('nav');
            if (window.innerWidth < 992) {
                toggleMobileSidebar(false);
            }
            if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
                e.preventDefault();
                document.body.classList.add('is-navigating');
                setTimeout(() => {
                    window.location.href = href;
                }, 280);
            }
        });
    });

    // Highlight current active sidebar link based on filename
    const currentLocation = location.pathname.split('/').pop() || 'doctor-scheduling.html';
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.endsWith(currentLocation)) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    // Staggered fade in for sidebar items
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    navLinks.forEach((link, index) => {
        setTimeout(() => {
            link.classList.add('in-view');
        }, 100 + (index * 60));
    });

    // --- CELEBRATION CONFETTI BURST ANIMATION ---
    function triggerConfetti() {
        let canvas = document.getElementById('confettiCanvas');
        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.id = 'confettiCanvas';
            document.body.appendChild(canvas);
        }
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particles = Array(40).fill(0).map(() => ({
            x: canvas.width / 2 + (Math.random() * 200 - 100),
            y: canvas.height / 3 + (Math.random() * 100 - 50),
            vx: (Math.random() - 0.5) * 12,
            vy: (Math.random() - 0.7) * 14,
            size: Math.random() * 8 + 4,
            color: ['#EF9F27', '#5DCAA5', '#26215C', '#3B82F6', '#10B981'][Math.floor(Math.random() * 5)],
            rotation: Math.random() * 360,
            vRot: (Math.random() - 0.5) * 10,
            opacity: 1
        }));

        let frame = 0;
        function animateConfetti() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            let alive = false;
            particles.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;
                p.vy += 0.4;
                p.rotation += p.vRot;
                p.opacity -= 0.02;
                if (p.opacity > 0) {
                    alive = true;
                    ctx.save();
                    ctx.translate(p.x, p.y);
                    ctx.rotate((p.rotation * Math.PI) / 180);
                    ctx.globalAlpha = Math.max(0, p.opacity);
                    ctx.fillStyle = p.color;
                    ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                    ctx.restore();
                }
            });
            if (alive && frame < 80) {
                frame++;
                requestAnimationFrame(animateConfetti);
            } else {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }
        requestAnimationFrame(animateConfetti);
    }
    window.triggerConfetti = triggerConfetti;

    // --- 3D MAGNETIC CARD TILT INTERACTION ---
    document.querySelectorAll('.content-card').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            const rotateX = (-y / rect.height) * 8;
            const rotateY = (x / rect.width) * 8;
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-4px)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });

    // --- INTERACTIVE TOAST SYSTEM ---
    function showToast(message, type = 'success') {
        if (type === 'success') triggerConfetti();
        let shell = document.querySelector('.toast-shell');
        if (!shell) {
            shell = document.createElement('div');
            shell.className = 'toast-shell';
            document.body.appendChild(shell);
        }
        const iconClass = type === 'success' ? 'fa-circle-check' : type === 'error' ? 'fa-circle-xmark' : 'fa-circle-info';
        const toast = document.createElement('div');
        toast.className = `hmcms-toast toast-${type}`;
        toast.innerHTML = `
            <i class="fa-solid ${iconClass} toast-icon"></i>
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close ms-2" style="font-size:0.75rem;" onclick="this.parentElement.remove()"></button>
        `;
        shell.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 20);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 3500);
    }
    window.showToast = showToast;

    // --- DARK / LIGHT THEME TOGGLE ---
    const savedTheme = localStorage.getItem('hmcms_theme') || 'light';
    if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('hmcms_theme', next);
        updateThemeToggleUI();
        showToast(`Switched to ${next === 'dark' ? 'Dark' : 'Light'} Mode`, 'info');
    }
    window.toggleTheme = toggleTheme;

    function updateThemeToggleUI() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const toggleBtns = document.querySelectorAll('.theme-toggle-btn');
        toggleBtns.forEach(btn => {
            btn.innerHTML = isDark 
                ? '<i class="fa-solid fa-sun me-1 text-warning"></i> Light Mode' 
                : '<i class="fa-solid fa-moon me-1"></i> Dark Mode';
        });
    }

    // Attach click listeners to any theme toggle buttons
    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
        btn.addEventListener('click', toggleTheme);
    });
    updateThemeToggleUI();

    // --- GENERIC API HANDLER ---
    async function handleFormSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const endpoint = form.getAttribute('data-api-endpoint');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message || 'Action completed successfully!', 'success');
                form.reset();
                // Close any open modal
                const modalEl = form.closest('.modal');
                if (modalEl) {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }
                // Refresh data based on current page
                refreshPageData();
            } else {
                showToast('Error: ' + (result.error || 'Failed to save data.'), 'error');
            }
        } catch (err) {
            console.error('API Error:', err);
            showToast('A network error occurred while communicating with the backend.', 'error');
        }
    }

    // Modal Opener for Stock Updates
    function openUpdateModal(medicineId) {
        const input = document.getElementById('update_medicine_id');
        if (input) input.value = medicineId;
        const modalEl = document.getElementById('updateStockModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }
    window.openUpdateModal = openUpdateModal;

    // Attach submit listener to all forms with data-api-endpoint
    document.querySelectorAll('form[data-api-endpoint]').forEach(form => {
        form.addEventListener('submit', handleFormSubmit);
    });

    // --- DATA FETCHING ---
    function refreshPageData() {
        if (currentLocation.includes('doctor-scheduling')) {
            fetchAssignments();
        } else if (currentLocation.includes('health-worker-allocation')) {
            fetchAllocations();
        } else if (currentLocation.includes('camp-notifications')) {
            fetchNotifications();
        } else if (currentLocation.includes('medicine-inventory')) {
            fetchMedicines();
        }
    }

    // Helper for Skeleton Loading
    function showSkeleton(containerId, isTable = true, cols = 4) {
        const el = document.getElementById(containerId);
        if (!el) return;
        if (isTable) {
            el.innerHTML = Array(3).fill(0).map(() => `
                <tr>
                    ${Array(cols).fill(0).map(() => `<td><span class="skeleton-row"></span></td>`).join('')}
                </tr>
            `).join('');
        } else {
            el.innerHTML = Array(2).fill(0).map(() => `
                <div class="list-group-item p-3 mb-3">
                    <span class="skeleton-row mb-2" style="width: 60%;"></span>
                    <span class="skeleton-row" style="width: 90%;"></span>
                </div>
            `).join('');
        }
    }

    function renderEmptyState(message = "No matching records found") {
        return `
            <tr>
                <td colspan="10" class="text-center py-5">
                    <div class="empty-state-card mx-auto" style="max-width: 380px;">
                        <i class="fa-solid fa-magnifying-glass-chart fa-3x text-muted mb-3" style="opacity: 0.5;"></i>
                        <h6 class="fw-bold mb-1">${message}</h6>
                        <p class="text-muted small mb-0">Try adjusting your search criteria or filters.</p>
                    </div>
                </td>
            </tr>
        `;
    }

    async function fetchAssignments() {
        const tbody = document.getElementById('assignmentsTableBody');
        if (!tbody) return;
        showSkeleton('assignmentsTableBody', true, 4);
        try {
            const res = await fetch('../../api/assignments.php');
            const data = await res.json();
            if (data.success && data.assignments.length > 0) {
                tbody.innerHTML = data.assignments.map(a => `
                    <tr>
                        <td><strong>Camp #${a.camp_id}</strong></td>
                        <td><i class="fa-solid fa-user-doctor text-primary me-1"></i> ${a.doctor_id}</td>
                        <td><i class="fa-regular fa-calendar me-1"></i> ${a.assignment_date}</td>
                        <td><span class="status-pill status-pill-success"><i class="fa-solid fa-circle-check"></i> Assigned</span></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = renderEmptyState("No doctor assignments scheduled yet.");
            }
        } catch(e) { console.error(e); }
    }

    async function fetchAllocations() {
        const tbody = document.getElementById('allocationsTableBody');
        if (!tbody) return;
        showSkeleton('allocationsTableBody', true, 4);
        try {
            const res = await fetch('../../api/allocations.php');
            const data = await res.json();
            if (data.success && data.allocations.length > 0) {
                tbody.innerHTML = data.allocations.map(a => `
                    <tr>
                        <td><strong>Camp #${a.camp_id}</strong></td>
                        <td><i class="fa-solid fa-user-nurse text-success me-1"></i> ${a.worker_id}</td>
                        <td><i class="fa-regular fa-clock me-1"></i> ${a.shift_date}</td>
                        <td><span class="status-pill status-pill-info"><i class="fa-solid fa-circle-check"></i> Allocated</span></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = renderEmptyState("No health worker allocations found.");
            }
        } catch(e) { console.error(e); }
    }

    async function fetchNotifications() {
        const list = document.getElementById('notificationsList');
        if (!list) return;
        showSkeleton('notificationsList', false);
        try {
            const res = await fetch('../../api/notifications.php');
            const data = await res.json();
            if (data.success && data.notifications.length > 0) {
                list.innerHTML = data.notifications.map(n => `
                    <div class="list-group-item p-3 border-start border-4 border-primary mb-3 shadow-sm rounded">
                        <div class="d-flex w-100 justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold text-primary">${n.title}</h6>
                            <small class="text-muted"><i class="fa-regular fa-clock me-1"></i>${new Date(n.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</small>
                        </div>
                        <p class="mb-2 text-secondary" style="font-size:0.92rem;">${n.message}</p>
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-pill status-pill-info"><i class="fa-solid fa-bullhorn"></i> ${n.audience.toUpperCase()}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                list.innerHTML = `<div class="empty-state-card py-4"><p class="text-muted mb-0"><i class="fa-solid fa-bell-slash me-2"></i>No announcements published yet.</p></div>`;
            }
        } catch(e) { console.error(e); }
    }

    async function fetchMedicines() {
        const tbody = document.getElementById('inventoryTableBody');
        if (!tbody) return;
        showSkeleton('inventoryTableBody', true, 7);
        try {
            const res = await fetch('../../api/medicines.php');
            const data = await res.json();
            if (data.success && data.medicines.length > 0) {
                tbody.innerHTML = data.medicines.map((m, i) => {
                    const isLow = m.stock_qty < 100;
                    const statusPill = isLow 
                        ? `<span class="status-pill status-pill-danger"><i class="fa-solid fa-triangle-exclamation"></i> Low (${m.stock_qty})</span>`
                        : `<span class="status-pill status-pill-success"><i class="fa-solid fa-circle-check"></i> ${m.stock_qty} Units</span>`;
                    return `
                        <tr>
                            <td>${i + 1}</td>
                            <td class="fw-bold">${m.name}</td>
                            <td><span class="badge bg-light text-dark border">${m.category}</span></td>
                            <td><code>${m.batch_no}</code></td>
                            <td>${m.expiry_date}</td>
                            <td>${statusPill}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="openUpdateModal(${m.id})" title="Update stock quantity">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Update Stock
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = renderEmptyState("No medicines in inventory.");
            }
        } catch(e) { console.error(e); }
    }

    // --- LIVE SEARCH FILTERING HANDLERS WITH EMPTY STATE ---
    function setupSearchFilter(inputId, containerSelector, itemSelector) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const items = document.querySelectorAll(containerSelector + ' ' + itemSelector);
            let visibleCount = 0;
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query)) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Handle empty search feedback
            const container = document.querySelector(containerSelector);
            let emptyMsg = container ? container.querySelector('.search-empty-msg') : null;
            if (visibleCount === 0 && items.length > 0) {
                if (!emptyMsg) {
                    emptyMsg = document.createElement('div');
                    emptyMsg.className = 'search-empty-msg text-center py-4 w-100 text-muted';
                    emptyMsg.innerHTML = '<i class="fa-solid fa-magnifying-glass me-2"></i>No matching records found for "' + query + '"';
                    container.parentNode.insertBefore(emptyMsg, container.nextSibling);
                }
            } else if (emptyMsg) {
                emptyMsg.remove();
            }
        });
    }

    setupSearchFilter('doctorSearch', '#assignmentsTableBody', 'tr');
    setupSearchFilter('workerSearch', '#allocationsTableBody', 'tr');
    setupSearchFilter('inventorySearch', '#inventoryTableBody', 'tr');
    setupSearchFilter('notificationSearch', '#notificationsList', '.list-group-item');

    // --- INTERSECTION OBSERVER SCROLL REVEALS ---
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    // Add in-view to direct children with staggered delays
                    const children = entry.target.querySelectorAll(':scope > *');
                    children.forEach((child, index) => {
                        setTimeout(() => {
                            child.classList.add('in-view');
                        }, index * 65);
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.reveal-stagger, .content-card, .metric-strip-card').forEach(el => {
            observer.observe(el);
        });
    } else {
        document.querySelectorAll('.reveal-stagger, .content-card, .metric-strip-card').forEach(el => {
            el.classList.add('in-view');
        });
    }

    // Initial load
    refreshPageData();
});


