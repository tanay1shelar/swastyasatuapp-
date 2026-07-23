import { Outlet, useLocation } from 'react-router-dom';
import { useEffect } from 'react';
import Sidebar from '../components/Sidebar';
import Header from '../components/Header';
import PageLoader from '../components/PageLoader';

export default function MainLayout() {
    const location = useLocation();

    useEffect(() => {
        document.body.classList.add('is-loaded');
    }, [location.pathname]);

    return (
        <div className="d-flex">
            <PageLoader key={location.pathname} />
            <Sidebar />
            <div className="flex-grow-1">
                <Header />
                <div className="page-transition-wrapper" key={location.pathname}>
                    <Outlet />
                </div>
            </div>
        </div>
    );
}

