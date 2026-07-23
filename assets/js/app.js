/**
 * HMCMS Global Application Bootstrapper
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('Swasthya Setu HMCMS Initialized.');
    
    // Apply smooth page load state
    document.body.classList.add('is-loaded');

    // Attach passive scroll listeners for scroll progress
    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const progress = scrollHeight > 0 ? (scrollTop / scrollHeight) : 0;
        const bar = document.getElementById('scroll-progress-bar');
        if (bar) {
            bar.style.transform = `scaleX(${progress})`;
        }
    }, { passive: true });
});
