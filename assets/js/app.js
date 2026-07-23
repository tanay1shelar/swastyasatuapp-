/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Master Application & UI Interaction Controller (app.js)
 * 
 * Implements macOS Neo Healthcare animations, IntersectionObserver scroll reveals,
 * 3D pointer tilt interactions, and scroll progress tracking.
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Scroll Progress Bar Indicator
    initScrollProgressBar();

    // 2. IntersectionObserver Scroll Reveal Animations
    initScrollReveals();

    // 3. 3D Pointer Tilt Interactions for Desktop Clinical Cards
    initCardPointerTilt();
});

/**
 * Creates and updates top viewport scroll progress indicator
 */
function initScrollProgressBar() {
    let progressBar = document.querySelector('.scroll-progress-bar');
    if (!progressBar) {
        progressBar = document.createElement('div');
        progressBar.className = 'scroll-progress-bar';
        document.body.appendChild(progressBar);
    }

    window.addEventListener('scroll', function () {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const progress = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
        progressBar.style.width = `${Math.min(100, Math.max(0, progress))}%`;
    }, { passive: true });
}

/**
 * Uses IntersectionObserver to trigger soft entrance reveals on scroll
 */
function initScrollReveals() {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return; // Respect accessibility reduced motion setting
    }

    const revealElements = document.querySelectorAll('.neo-reveal, .neo-reveal-left, .neo-reveal-right, .card-custom, .stat-widget');
    if (!revealElements.length) return;

    // Apply base class if missing
    revealElements.forEach(el => {
        if (!el.classList.contains('neo-reveal') && !el.classList.contains('neo-reveal-left') && !el.classList.contains('neo-reveal-right')) {
            el.classList.add('neo-reveal');
        }
    });

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                obs.unobserve(entry.target); // Unobserve after entering to save memory
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
    });

    revealElements.forEach(el => observer.observe(el));
}

/**
 * Implements subtle 3D pointer tilt effect for desktop cards
 */
function initCardPointerTilt() {
    if (window.matchMedia('(hover: none) or (max-width: 991px)').matches) return; // Disable on touch devices

    const cards = document.querySelectorAll('.card-custom, .stat-widget');

    cards.forEach(card => {
        card.addEventListener('mousemove', function (e) {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = ((y - centerY) / centerY) * -5; // max 5deg tilt
            const rotateY = ((x - centerX) / centerX) * 5;

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px) scale(1.015)`;
        });

        card.addEventListener('mouseleave', function () {
            card.style.transform = ''; // Smooth reset
        });
    });
}
