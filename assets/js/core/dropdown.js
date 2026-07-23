/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Dropdown Menu Module
 * 
 * Handles dropdown click toggles and auto-dismissal when clicking outside 
 * the dropdown container. Works alongside Bootstrap's dropdown behaviors.
 */

document.addEventListener('DOMContentLoaded', function () {
    // Select all custom toggle elements on the page
    const dropdownTriggers = document.querySelectorAll('[data-toggle="custom-dropdown"]');

    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const parent = this.parentElement;
            const menu = parent.querySelector('.dropdown-menu-custom') || parent.querySelector('.dropdown-menu');

            if (!menu) return;

            // Close all other open dropdowns first
            closeAllDropdowns(menu);

            // Toggle current dropdown
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            menu.classList.toggle('show');
        });
    });

    // Close dropdowns on document click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('[data-toggle="custom-dropdown"]') && !e.target.closest('.dropdown-menu')) {
            closeAllDropdowns();
        }
    });

    /**
     * Close all custom dropdown menus except the active one (if specified)
     * @param {HTMLElement|null} exceptMenu 
     */
    function closeAllDropdowns(exceptMenu = null) {
        const menus = document.querySelectorAll('.dropdown-menu-custom.show, .dropdown-menu.show');
        menus.forEach(menu => {
            if (menu === exceptMenu) return;
            
            menu.classList.remove('show');
            
            // Reset the trigger aria attributes
            const parent = menu.parentElement;
            const trigger = parent.querySelector('[data-toggle="custom-dropdown"]');
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Export closeAllDropdowns globally for other scripts
    window.closeAllDropdowns = closeAllDropdowns;
});
