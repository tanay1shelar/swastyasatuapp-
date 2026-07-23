import { useEffect, useState } from 'react';

export default function PageLoader() {
    const [loading, setLoading] = useState(true);
    const [fadeOut, setFadeOut] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => {
            setFadeOut(true);
            const removeTimer = setTimeout(() => {
                setLoading(false);
            }, 600);
            return () => clearTimeout(removeTimer);
        }, 500);

        return () => clearTimeout(timer);
    }, []);

    if (!loading) return null;

    return (
        <div className={`page-loader-overlay ${fadeOut ? 'fade-out' : ''}`}>
            <div className="loader-content text-center">
                <div className="heartbeat-logo-wrapper mb-3">
                    <svg className="pulse-svg" viewBox="0 0 100 100" width="80" height="80">
                        <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(239, 159, 39, 0.2)" strokeWidth="4"/>
                        <circle cx="50" cy="50" r="42" fill="none" stroke="#EF9F27" strokeWidth="4" className="pulse-circle"/>
                        {/* Medical Cross */}
                        <path d="M45 28 h10 v17 h17 v10 h-17 v17 h-10 v-17 h-17 v-10 h17 z" fill="#EF9F27" className="cross-icon" />
                    </svg>
                </div>
                <h4 className="text-white font-weight-bold mb-2 tracking-wide brand-title">Swasthya Setu</h4>
                <p className="text-white-50 small mb-4">Healthcare & Medical Camp Management</p>
                <div className="loader-progress-bar">
                    <div className="loader-progress-fill"></div>
                </div>
            </div>
        </div>
    );
}
