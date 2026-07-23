import { useState, useEffect } from 'react';

export default function Header() {
    const [theme, setTheme] = useState(() => localStorage.getItem('hmcms_theme') || 'light');
    const [soundEnabled, setSoundEnabled] = useState(() => localStorage.getItem('neo_sound_enabled') !== 'false');
    const [scrollProgress, setScrollProgress] = useState(0);

    useEffect(() => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('hmcms_theme', theme);
    }, [theme]);

    useEffect(() => {
        const handleScroll = () => {
            const scrollTop = window.scrollY || document.documentElement.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            setScrollProgress(scrollHeight > 0 ? scrollTop / scrollHeight : 0);
        };
        window.addEventListener('scroll', handleScroll, { passive: true });
        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    const playSound = (type = 'click') => {
        if (!soundEnabled) return;
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
            } else if (type === 'toggle') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(520, now);
                osc.frequency.setValueAtTime(660, now + 0.04);
                gain.gain.setValueAtTime(0.1, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.09);
                osc.start(now);
                osc.stop(now + 0.09);
            }
        } catch (e) {}
    };

    const toggleTheme = () => {
        playSound('toggle');
        setTheme(prev => (prev === 'dark' ? 'light' : 'dark'));
    };

    const toggleSound = () => {
        const next = !soundEnabled;
        setSoundEnabled(next);
        localStorage.setItem('neo_sound_enabled', String(next));
        if (next) playSound('toggle');
    };

    const toggleMobileSidebar = () => {
        playSound('click');
        if (window.toggleMobileSidebar) {
            window.toggleMobileSidebar();
        } else {
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');
            if (sidebar) {
                sidebar.classList.toggle('show-drawer');
                if (backdrop) backdrop.classList.toggle('show');
            }
        }
    };

    return (
        <>
            <div 
                id="scroll-progress-bar" 
                style={{
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    width: '100%',
                    height: '3px',
                    background: 'linear-gradient(90deg, #0071E3 0%, #2563EB 50%, #38BDF8 100%)',
                    transformOrigin: '0 50%',
                    transform: `scaleX(${scrollProgress})`,
                    zIndex: 9999,
                    transition: 'transform 100ms linear'
                }} 
            />
            <header className="top-header p-3 d-flex justify-content-between align-items-center">
                <div className="d-flex align-items-center gap-3">
                    <button 
                        onClick={toggleMobileSidebar} 
                        className="btn btn-outline-secondary btn-sm d-lg-none rounded-pill px-3" 
                        aria-label="Toggle mobile sidebar"
                        type="button"
                    >
                        <i className="fa-solid fa-bars"></i>
                    </button>
                    <img src="/assets/images/swasthyasetu-logo.png" alt="Swasthya Setu Logo" className="brand-logo" style={{height:"44px", width:"auto", display:"inline-block"}} />
                    <div className="ekg-banner-wrapper d-none d-md-flex align-items-center">
                        <svg className="ekg-svg" viewBox="0 0 300 40" width="180" height="26">
                            <path className="ekg-path" d="M0,20 L40,20 L50,10 L60,30 L70,5 L80,35 L90,20 L140,20 L150,10 L160,30 L170,5 L180,35 L190,20 L300,20" stroke="#5DCAA5" strokeWidth="2.5" fill="none" strokeLinecap="round" strokeLinejoin="round" />
                        </svg>
                    </div>
                </div>
                <div className="d-flex align-items-center gap-2 gap-md-3">
                    <button 
                        onClick={toggleSound} 
                        className="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm theme-toggle-btn d-flex align-items-center"
                        type="button"
                        title="Toggle Interaction Sounds"
                    >
                        {soundEnabled ? (
                            <><i className="fa-solid fa-volume-high me-1 text-primary"></i> Sound On</>
                        ) : (
                            <><i className="fa-solid fa-volume-xmark me-1 text-muted"></i> Muted</>
                        )}
                    </button>
                    <button 
                        onClick={toggleTheme} 
                        className="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm theme-toggle-btn d-flex align-items-center"
                        type="button"
                    >
                        {theme === 'dark' ? (
                            <><i className="fa-solid fa-sun me-1 text-warning"></i> Light Mode</>
                        ) : (
                            <><i className="fa-solid fa-moon me-1"></i> Dark Mode</>
                        )}
                    </button>
                    <div className="dropdown">
                        <button className="btn btn-light dropdown-toggle rounded-pill px-3 py-1 btn-sm" type="button" data-bs-toggle="dropdown">
                            <i className="fa-solid fa-user-circle me-1 text-primary"></i> Admin
                        </button>
                        <ul className="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a className="dropdown-item" href="#">Profile</a></li>
                            <li><a className="dropdown-item" href="#">Settings</a></li>
                            <li><hr className="dropdown-divider" /></li>
                            <li><a className="dropdown-item text-danger" href="#">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>
        </>
    );
}
