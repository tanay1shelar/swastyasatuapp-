/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Sidebar Navigation Module
 * 
 * Handles desktop collapse, mobile offcanvas drawer toggle, 
 * overlay click dismissals, and auto-highlighting of active links.
 */

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('app-sidebar');
    const toggleBtn = document.getElementById('sidebar-toggle');
    
    // Check if sidebar elements exist on the page
    if (!sidebar || !toggleBtn) return;

    // Retrieve previous sidebar state from localStorage for persistent user experience
    const sidebarState = localStorage.getItem('sidebar-collapsed');
    if (sidebarState === 'true' && window.innerWidth >= 992) {
        sidebar.classList.add('collapsed');
    }

    // Toggle click handler
    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        
        if (window.innerWidth >= 992) {
            // Desktop Collapse toggle
            sidebar.classList.toggle('collapsed');
            
            // Persist the state
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed);
        } else {
            // Mobile Drawer toggle
            sidebar.classList.toggle('mobile-open');
            toggleMobileOverlay(sidebar.classList.contains('mobile-open'));
        }
    });

    /**
     * Create/Remove Backdrop Overlay on Mobile screens
     */
    function toggleMobileOverlay(show) {
        let overlay = document.getElementById('sidebar-mobile-overlay');
        
        if (show) {
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'sidebar-mobile-overlay';
                overlay.className = 'sidebar-overlay-active';
                document.body.appendChild(overlay);
                document.body.style.overflow = 'hidden'; // Lock scroll on mobile
                
                // Clicking the overlay closes the sidebar drawer
                overlay.addEventListener('click', function () {
                    sidebar.classList.remove('mobile-open');
                    toggleMobileOverlay(false);
                });
            }
        } else {
            if (overlay) {
                overlay.remove();
                document.body.style.overflow = '';
            }
        }
    }

    // Dismiss mobile drawer on Escape key press
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
            toggleMobileOverlay(false);
        }
    });

    // Dismiss mobile drawer when screen resizes to desktop width
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('mobile-open');
            toggleMobileOverlay(false);
            
            // Re-apply collapsed state on resize if it was set
            const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
        } else {
            // On mobile, remove collapsed layout shifts
            sidebar.classList.remove('collapsed');
        }
    });

    /**
     * Active Menu Highlighting
     * Matches page filenames and updates class states
     */
    const currentPath = window.location.pathname;
    const pageName = currentPath.substring(currentPath.lastIndexOf('/') + 1) || 'index.php';
    
    const menuLinks = document.querySelectorAll('.nav-menu .nav-link');
    let matched = false;

    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && (href === pageName || (pageName === 'index.php' && href === './') || (href.includes(pageName) && pageName !== 'index.php'))) {
            // Remove active from any existing item
            document.querySelectorAll('.nav-menu .nav-item').forEach(item => {
                item.classList.remove('active');
            });
            // Add active class to closest li element
            link.closest('.nav-item').classList.add('active');
            matched = true;
        }
    });

    // Fallback: If no match, default to dashboard (index.php)
    if (!matched && pageName === 'index.php') {
        const dashboardLink = document.querySelector('.nav-menu a[href="index.php"]');
        if (dashboardLink) {
            dashboardLink.closest('.nav-item').classList.add('active');
        }
    }
});
