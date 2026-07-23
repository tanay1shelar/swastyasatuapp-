/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Interactive Digital Book Experience Engine
 * 
 * Features: GSAP 3D Page Turn Physics, Idle Book Motion, Element Sequencing,
 * Satisfying Button Micro-Interactions, Interactive Card Tilting, and Ambient Particles.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Register GSAP plugins if present
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
    }

    // Initialize all experience modules
    initAmbientBackground();
    initBookOpening();
    initIdleBookMotion();
    initSatisfyingButtons();
    initCardInteractions();
    initContentSequencing();
    initLogoAnimation();
    initScrollAnimations();
    initPageTurnNavigation();
});

/* =========================================================================
   1. AMBIENT BACKGROUND MOTION & PARTICLES
   ========================================================================= */
function initAmbientBackground() {
    if (document.querySelector('.ambient-bg-container')) return;

    const bgContainer = document.createElement('div');
    bgContainer.className = 'ambient-bg-container';
    bgContainer.innerHTML = `
        <div class="ambient-blob ambient-blob-1"></div>
        <div class="ambient-blob ambient-blob-2"></div>
        <canvas id="ambient-particles-canvas"></canvas>
    `;
    document.body.prepend(bgContainer);

    // Particle Canvas Animation Engine
    const canvas = document.getElementById('ambient-particles-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let width = (canvas.width = window.innerWidth);
    let height = (canvas.height = window.innerHeight);

    window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    });

    const particles = Array.from({ length: 35 }, () => ({
        x: Math.random() * width,
        y: Math.random() * height,
        radius: Math.random() * 2.5 + 1,
        color: Math.random() > 0.5 ? 'rgba(239, 159, 39, ' : 'rgba(38, 33, 92, ',
        alpha: Math.random() * 0.4 + 0.1,
        speedX: (Math.random() - 0.5) * 0.4,
        speedY: -Math.random() * 0.5 - 0.2
    }));

    function renderParticles() {
        ctx.clearRect(0, 0, width, height);

        particles.forEach(p => {
            p.x += p.speedX;
            p.y += p.speedY;

            if (p.y < -10) {
                p.y = height + 10;
                p.x = Math.random() * width;
            }
            if (p.x < -10 || p.x > width + 10) {
                p.x = Math.random() * width;
            }

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = p.color + p.alpha + ')';
            ctx.shadowBlur = 8;
            ctx.shadowColor = p.color + '0.5)';
            ctx.fill();
        });

        requestAnimationFrame(renderParticles);
    }
    renderParticles();
}

/* =========================================================================
   2. BOOK OPENING DRAMATIC ANIMATION
   ========================================================================= */
function initBookOpening() {
    const mainContent = document.querySelector('.app-content-wrapper') || document.querySelector('.login-card');
    if (!mainContent) return;

    if (typeof gsap !== 'undefined') {
        gsap.fromTo(mainContent, 
            {
                opacity: 0,
                rotateY: -12,
                translateZ: 80,
                scale: 0.95,
                y: 35
            },
            {
                opacity: 1,
                rotateY: 0,
                translateZ: 0,
                scale: 1,
                y: 0,
                duration: 1.1,
                ease: "power3.out"
            }
        );
    } else {
        mainContent.style.opacity = '1';
    }
}

/* =========================================================================
   3. IDLE BOOK MOTION (BREATHING & FLOATING)
   ========================================================================= */
function initIdleBookMotion() {
    const bookMain = document.querySelector('.app-main') || document.querySelector('.login-card');
    if (!bookMain) return;

    if (typeof gsap !== 'undefined') {
        // Floating movement
        gsap.to(bookMain, {
            y: -5,
            duration: 5,
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut"
        });

        // Breathing scale
        gsap.to(bookMain, {
            scale: 1.002,
            duration: 6,
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut"
        });
    }
}

/* =========================================================================
   4. SATISFYING BUTTON MICRO-INTERACTIONS & RIPPLE CLICK
   ========================================================================= */
function initSatisfyingButtons() {
    const buttonSelector = '.btn, .btn-custom, .btn-primary, .btn-login, .sidebar-toggle-btn, .navbar-action-btn, .navbar-user-trigger';
    const buttons = document.querySelectorAll(buttonSelector);

    buttons.forEach(btn => {
        // Ripple Animation on Click
        btn.addEventListener('click', function (e) {
            const rect = this.getBoundingClientRect();
            const diameter = Math.max(rect.width, rect.height) * 2;
            const x = e.clientX - rect.left - diameter / 2;
            const y = e.clientY - rect.top - diameter / 2;

            const ripple = document.createElement('span');
            ripple.className = 'btn-ripple-wave';
            ripple.style.width = ripple.style.height = `${diameter}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;

            // Remove existing ripples to avoid stacking
            const oldRipple = this.querySelector('.btn-ripple-wave');
            if (oldRipple) oldRipple.remove();

            this.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 650);
        });

        // Press down animation
        btn.addEventListener('mousedown', function () {
            this.classList.add('is-pressing');
        });

        btn.addEventListener('mouseup', function () {
            this.classList.remove('is-pressing');
        });

        btn.addEventListener('mouseleave', function () {
            this.classList.remove('is-pressing');
        });
    });
}

/* =========================================================================
   5. FEATURE CARDS MICRO-INTERACTIONS & 3D CURSOR TILT
   ========================================================================= */
function initCardInteractions() {
    const cards = document.querySelectorAll('.card-custom, .stat-widget, .card, .login-card');

    cards.forEach(card => {
        // 3D Parallax Tilt Effect on Mouse Move
        card.addEventListener('mousemove', function (e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = ((y - centerY) / centerY) * -6; // Up to 6 deg tilt
            const rotateY = ((x - centerX) / centerX) * 6;

            if (typeof gsap !== 'undefined') {
                gsap.to(this, {
                    rotateX: rotateX,
                    rotateY: rotateY,
                    y: -8,
                    scale: 1.02,
                    duration: 0.3,
                    ease: "power2.out"
                });
            }
        });

        card.addEventListener('mouseleave', function () {
            if (typeof gsap !== 'undefined') {
                gsap.to(this, {
                    rotateX: 0,
                    rotateY: 0,
                    y: 0,
                    scale: 1,
                    duration: 0.5,
                    ease: "power2.out"
                });
            }
        });
    });
}

/* =========================================================================
   6. PAGE CONTENT ANIMATION SEQUENCING
   ========================================================================= */
function initContentSequencing() {
    if (typeof gsap === 'undefined') return;

    const mainContent = document.querySelector('.app-content-wrapper') || document.body;

    // Strict Sequence: Title -> Subtitle -> Image -> Cards -> Paragraph -> Buttons
    const titles = mainContent.querySelectorAll('.page-title, h1, .login-title, .card-custom-title');
    const subtitles = mainContent.querySelectorAll('.breadcrumb-custom, .text-secondary.mb-0, .lead, h2, h3');
    const images = mainContent.querySelectorAll('img, .brand-icon-wrapper, .stat-widget-icon');
    const cards = mainContent.querySelectorAll('.card-custom, .stat-widget, .card, .login-card, .table-responsive');
    const paragraphs = mainContent.querySelectorAll('p, .text-muted, .form-text');
    const buttons = mainContent.querySelectorAll('.btn-custom, .btn, .btn-login');

    const tl = gsap.timeline({ delay: 0.1 });

    // 1. Title: Fade + Slide Upward
    if (titles.length > 0) {
        tl.from(titles, {
            y: 30,
            opacity: 0,
            duration: 0.6,
            ease: "power3.out",
            stagger: 0.1
        });
    }

    // 2. Subtitle: Fade + slight delay
    if (subtitles.length > 0) {
        tl.from(subtitles, {
            y: 20,
            opacity: 0,
            duration: 0.5,
            ease: "power3.out",
            stagger: 0.08
        }, "-=0.3");
    }

    // 3. Image / Icon: Scale + Rotate slight + Fade
    if (images.length > 0) {
        tl.from(images, {
            scale: 0.8,
            rotate: -8,
            opacity: 0,
            duration: 0.5,
            ease: "back.out(1.4)",
            stagger: 0.08
        }, "-=0.3");
    }

    // 4. Cards: Stagger animation
    if (cards.length > 0) {
        tl.from(cards, {
            y: 35,
            scale: 0.95,
            opacity: 0,
            duration: 0.6,
            ease: "power3.out",
            stagger: 0.12
        }, "-=0.2");
    }

    // 5. Paragraph: Fade + slight delay
    if (paragraphs.length > 0) {
        tl.from(paragraphs, {
            y: 15,
            opacity: 0,
            duration: 0.4,
            ease: "power2.out",
            stagger: 0.05
        }, "-=0.3");
    }

    // 6. Buttons: Pop scale + Fade
    if (buttons.length > 0) {
        tl.from(buttons, {
            scale: 0.9,
            opacity: 0,
            duration: 0.4,
            ease: "back.out(1.5)",
            stagger: 0.08
        }, "-=0.2");
    }
}

/* =========================================================================
   7. LOGO IDLE & ALIVE ANIMATION
   ========================================================================= */
function initLogoAnimation() {
    const logoIcons = document.querySelectorAll('.brand-icon-wrapper, .login-logo');
    if (typeof gsap === 'undefined' || logoIcons.length === 0) return;

    logoIcons.forEach(logo => {
        gsap.to(logo, {
            scale: 1.06,
            duration: 2.5,
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut"
        });

        gsap.to(logo, {
            y: -3,
            duration: 3.5,
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut"
        });
    });
}

/* =========================================================================
   8. SCROLL ANIMATIONS FOR ICONS & SECTIONS
   ========================================================================= */
function initScrollAnimations() {
    const icons = document.querySelectorAll('.bi, i');

    if (typeof IntersectionObserver !== 'undefined') {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (typeof gsap !== 'undefined') {
                        gsap.fromTo(entry.target, 
                            { scale: 0.8, rotate: -10, opacity: 0 },
                            { scale: 1, rotate: 0, opacity: 1, duration: 0.5, ease: "back.out(1.5)" }
                        );
                    } else {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'scale(1)';
                    }
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        icons.forEach(icon => observer.observe(icon));
    }
}

/* =========================================================================
   9. PAGE TURN TRANSITION NAVIGATION (NEXT / PREVIOUS PAGE CURVES)
   ========================================================================= */
function initPageTurnNavigation() {
    const links = document.querySelectorAll('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript"])');
    const mainContent = document.querySelector('.app-content-wrapper') || document.querySelector('.login-card') || document.body;

    links.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (!href || href === '#' || href.startsWith('http') && !href.includes(window.location.hostname)) return;

            e.preventDefault();

            // Determine if navigating next or previous
            const isPrev = this.getAttribute('data-direction') === 'prev' || href.includes('index.php');
            
            if (typeof gsap !== 'undefined' && mainContent) {
                const turnTL = gsap.timeline({
                    onComplete: () => {
                        window.location.href = href;
                    }
                });

                // 3D Page turn curl & lift transition
                turnTL.to(mainContent, {
                    translateZ: 100,
                    rotateY: isPrev ? 45 : -45,
                    transformOrigin: isPrev ? "right center" : "left center",
                    opacity: 0,
                    scale: 0.92,
                    duration: 0.55,
                    ease: "power2.in"
                });
            } else {
                window.location.href = href;
            }
        });
    });
}
