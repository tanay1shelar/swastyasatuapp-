/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Theme & UI Utilities Module
 * 
 * Manages color theme (light/dark modes) and clinical UI display preferences.
 */

document.addEventListener('DOMContentLoaded', function () {
    const themeToggle = document.getElementById('theme-toggle-btn');
    const prefDarkMode = document.getElementById('pref-dark-mode');

    // Load active theme
    const savedTheme = localStorage.getItem('app-theme') || 'light';
    applyTheme(savedTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            applyTheme(newTheme);
        });
    }

    if (prefDarkMode) {
        prefDarkMode.addEventListener('change', function () {
            const newTheme = this.checked ? 'dark' : 'light';
            applyTheme(newTheme);
        });
    }

    /**
     * Apply theme by mutating document element attributes and updating switches
     * @param {string} theme 'light' | 'dark'
     */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('app-theme', theme);
        
        // Update top navbar button icon
        if (themeToggle) {
            const icon = themeToggle.querySelector('i');
            if (icon) {
                if (theme === 'dark') {
                    icon.className = 'bi bi-sun-fill';
                    themeToggle.setAttribute('title', 'Switch to Light Mode');
                } else {
                    icon.className = 'bi bi-moon-stars-fill';
                    themeToggle.setAttribute('title', 'Switch to Dark Mode');
                }
            }
        }

        // Update profile switch checkbox
        if (prefDarkMode) {
            prefDarkMode.checked = (theme === 'dark');
        }
    }
});
