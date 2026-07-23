<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
check_auth(['citizen']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Dashboard - Swasthya Setu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../assets/../../assets/css/style.css?v=1.3" rel="stylesheet">
</head>
<body>
    <!-- Page Loading Animation -->
    <div class="page-loader text-center">
        <img src="../../assets/images/logo/logo.png" alt="Swasthya Setu Logo" height="140" class="mb-4" style="filter: drop-shadow(0 10px 25px rgba(38,33,92,0.15));">
        <div class="spinner-medical mx-auto mb-3"></div>
        <p class="text-muted small text-uppercase" style="letter-spacing: 1px;">Loading Portal...</p>
    </div>

    <!-- App Wrapper -->
    <div class="app-wrapper">
        <!-- Sidebar -->
        <aside class="app-sidebar">
            <div class="sidebar-logo text-center py-2" style="display: flex; justify-content: center; align-items: center; height: auto;">
                <img src="../../assets/images/logo/logo.png" alt="Swasthya Setu Logo" style="height: 60px; width: auto; filter: drop-shadow(0 3px 6px rgba(0,0,0,0.1));">
            </div>
            <div class="sidebar-menu">
                <a href="#" class="menu-item active">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="menu-item" onclick="showToast('Navigation', 'Upcoming camps list')">
                    <i class="fas fa-campground"></i>
                    <span>Upcoming Camps</span>
                </a>
                <a href="#" class="menu-item" onclick="showToast('Navigation', 'Medical history')">
                    <i class="fas fa-notes-medical"></i>
                    <span>Medical History</span>
                </a>
                <a href="#" class="menu-item" onclick="showToast('Navigation', 'Prescriptions list')">
                    <i class="fas fa-file-download"></i>
                    <span>Download Prescriptions</span>
                </a>
            </div>
            <div class="sidebar-footer">
                <a href="../authentication/../authentication/logout.php" class="menu-item text-danger">
                    <i class="fas fa-sign-out-alt text-danger"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="app-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light p-2 border-0" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="search-box-wrapper d-none d-md-block">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" placeholder="Search my records...">
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div id="headerClock" class="header-clock d-none d-lg-block me-3"></div>

                    <!-- User Profile -->
                    <div class="dropdown">
                        <div class="profile-dropdown-btn" data-bs-toggle="dropdown">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150" alt="Profile">
                            <div class="d-none d-lg-block">
                                <h6 class="mb-0" style="font-size: 0.85rem; font-weight: 600;"><?= htmlspecialchars($_SESSION['full_name']); ?></h6>
                                <span class="text-muted small">Citizen / Patient</span>
                            </div>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 p-2">
                            <li><a class="dropdown-item py-2 text-danger" href="../authentication/../authentication/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Body -->
            <div class="content-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 text-dark fw-bold">Citizen Healthcare Portal</h4>
                        <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['full_name']); ?>!</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card blue h-100 p-3">
                            <span class="text-muted small text-uppercase">Camps Near Me</span>
                            <h3 class="mb-0 mt-1 fw-bold">4 Active</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card green h-100 p-3">
                            <span class="text-muted small text-uppercase">Active Token</span>
                            <h3 class="mb-0 mt-1 fw-bold">#TK-101</h3>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card teal h-100 p-3">
                            <span class="text-muted small text-uppercase">Prescriptions</span>
                            <h3 class="mb-0 mt-1 fw-bold">3 Files</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="app-footer">
                <p class="mb-0">&copy; 2026 Swasthya Setu | Connecting Communities to Better Healthcare</p>
            </footer>
        </main>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/../../assets/../../assets/js/dashboard.js"></script>
</body>
</html>
