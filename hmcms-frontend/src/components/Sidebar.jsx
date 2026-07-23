import { Link, useLocation } from 'react-router-dom';

export default function Sidebar() {
    const location = useLocation();

    return (
        <nav className="sidebar p-3 d-flex flex-column">
            <div className="mb-4 d-flex align-items-center justify-content-between">
                <div className="d-flex align-items-center gap-2">
                    <img src="/assets/images/swasthyasetu-logo.png" alt="Swasthya Setu Logo" style={{ height: "42px", width: "42px", borderRadius: "50%", objectFit: "cover", boxShadow: "0 2px 8px rgba(0,0,0,0.25)" }} />
                    <div>
                        <h6 className="sidebar-brand-title mb-0" style={{ fontWeight: 800, fontSize: "1.05rem", letterSpacing: "0.5px", color: "var(--text-primary)" }}>Swasthya Setu</h6>
                        <small className="sidebar-brand-subtitle d-block" style={{ fontSize: "0.65rem", lineHeight: "1.1", marginTop: "2px", color: "var(--text-secondary)" }}>Connecting Communities</small>
                    </div>
                </div>
                <button 
                    type="button" 
                    className="btn-close d-lg-none" 
                    aria-label="Close sidebar"
                    onClick={() => window.toggleMobileSidebar && window.toggleMobileSidebar(false)}
                />
            </div>
            <ul className="nav nav-pills flex-column mb-auto">
                <li className="nav-item">
                    <Link to="/scheduling" className={`nav-link in-view ${location.pathname === '/' || location.pathname === '/scheduling' ? 'active' : ''}`}>
                        <i className="fa-solid fa-user-doctor me-2"></i> Scheduling
                    </Link>
                </li>
                <li>
                    <Link to="/allocation" className={`nav-link in-view ${location.pathname === '/allocation' ? 'active' : ''}`}>
                        <i className="fa-solid fa-users me-2"></i> Resource Allocation
                    </Link>
                </li>
                <li>
                    <Link to="/inventory" className={`nav-link in-view ${location.pathname === '/inventory' ? 'active' : ''}`}>
                        <i className="fa-solid fa-pills me-2"></i> Medicine Inventory
                    </Link>
                </li>
                <li>
                    <Link to="/notifications" className={`nav-link in-view ${location.pathname === '/notifications' ? 'active' : ''}`}>
                        <i className="fa-solid fa-bullhorn me-2"></i> Notifications Center
                    </Link>
                </li>
            </ul>
            <hr />
            <a href="#" className="nav-link in-view"><i className="fa-solid fa-right-from-bracket me-2"></i> Sign Out</a>
        </nav>
    );
}
