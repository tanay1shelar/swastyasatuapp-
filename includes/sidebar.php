<?php $currentPage = basename(dirname($_SERVER['PHP_SELF'])); ?>
<nav id="sidebar">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&display=swap');
        .logo-text-premium {
            font-family: 'Playfair Display', serif;
            color: var(--sidebar-text);
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            white-space: nowrap;
            letter-spacing: 0.5px;
        }
    </style>
    <div class="sidebar-header" style="padding: 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
        <img src="../../assets/images/SwasthyaSetu.jpeg" alt="Logo" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid var(--sidebar-text); box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex-shrink: 0;">
        <div class="logo-text-premium">Swasthya Setu</div>
    </div>
    
    <div class="sidebar-nav">
        <div class="nav-item">
            <a href="../dashboard/index.php" class="nav-link <?= $currentPage == 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="../centers/index.php" class="nav-link <?= $currentPage == 'centers' ? 'active' : '' ?>">
                <i class="fa-solid fa-building-user"></i>
                <span>Healthcare Centers</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="../monitoring/index.php" class="nav-link <?= $currentPage == 'monitoring' ? 'active' : '' ?>">
                <i class="fa-solid fa-desktop"></i>
                <span>Camp Monitoring</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="../reports/index.php" class="nav-link <?= $currentPage == 'reports' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-invoice"></i>
                <span>Reports</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="../settings/index.php" class="nav-link <?= $currentPage == 'settings' ? 'active' : '' ?>">
                <i class="fa-solid fa-gear"></i>
                <span>System Settings</span>
            </a>
        </div>
    </div>
    
    <div class="sidebar-footer">
        <a href="#" class="logout-btn" style="text-decoration: none;">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</nav>

<?php
// Global Sidebar template

