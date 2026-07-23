<?php
require_once '../../config/database.php';
require_once '../../includes/session.php';
check_auth(['super-admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - Swasthya Setu</title>
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
                <a href="#" class="menu-item" onclick="showToast('Navigation', 'Managing users module is clicked')">
                    <i class="fas fa-users-cog"></i>
                    <span>Users</span>
                </a>
                <a href="#" class="menu-item" onclick="showToast('Navigation', 'Hospitals management module is clicked')">
                    <i class="fas fa-hospital"></i>
                    <span>Hospitals</span>
                </a>
                <a href="#" class="menu-item" onclick="showToast('Navigation', 'Medical Camps module is clicked')">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Medical Camps</span>
                </a>
                <a href="#" class="menu-item" onclick="showToast('Navigation', 'Doctors database is clicked')">
                    <i class="fas fa-user-md"></i>
                    <span>Doctors</span>
                </a>
                <a href="#" class="menu-item" onclick="showToast('Navigation', 'Health Workers list is clicked')">
                    <i class="fas fa-user-nurse"></i>
                    <span>Health Workers</span>
                </a>
                <a href="#" class="menu-item" onclick="showToast('Navigation', 'Analytical Reports is clicked')">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Reports</span>
                </a>
                <a href="#" class="menu-item" onclick="showToast('Navigation', 'System Analytics is clicked')">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
                <a href="#" class="menu-item" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
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
                    <!-- Search Bar -->
                    <div class="search-box-wrapper d-none d-md-block">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="dashboardTableSearch" placeholder="Search system...">
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <!-- Live Time & Date -->
                    <div id="headerClock" class="header-clock d-none d-lg-block me-3"></div>

                    <!-- User Profile Menu -->
                    <div class="dropdown">
                        <div class="profile-dropdown-btn" data-bs-toggle="dropdown">
                            <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150" alt="Profile">
                            <div class="d-none d-lg-block">
                                <h6 class="mb-0" style="font-size: 0.85rem; font-weight: 600;"><?= htmlspecialchars($_SESSION['full_name']); ?></h6>
                                <span class="text-muted small">Super Admin</span>
                            </div>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 p-2" style="border-radius: 10px;">
                            <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#editProfileModal"><i class="far fa-user me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="fas fa-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="../authentication/../authentication/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Dashboard Body -->
            <div class="content-body">
                <!-- Welcome Section -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1 text-dark fw-bold">Super Admin Dashboard</h4>
                        <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['full_name']); ?>! System audits & hospital management.</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHospitalModal">
                        <i class="fas fa-plus me-2"></i>Add Hospital
                    </button>
                </div>

                <!-- Metrics Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card blue h-100 p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-semibold text-uppercase">Total Patients</span>
                                    <h3 class="mb-0 mt-1 fw-bold">1,452</h3>
                                </div>
                                <div class="stat-icon blue"><i class="fas fa-hospital-user"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card green h-100 p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-semibold text-uppercase">Total Doctors</span>
                                    <h3 class="mb-0 mt-1 fw-bold">128</h3>
                                </div>
                                <div class="stat-icon green"><i class="fas fa-user-md"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card teal h-100 p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-semibold text-uppercase">Medical Camps</span>
                                    <h3 class="mb-0 mt-1 fw-bold">18</h3>
                                </div>
                                <div class="stat-icon teal"><i class="fas fa-campground"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card warning h-100 p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small fw-semibold text-uppercase">Health Workers</span>
                                    <h3 class="mb-0 mt-1 fw-bold">340</h3>
                                </div>
                                <div class="stat-icon warning"><i class="fas fa-user-nurse"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card p-3 border-0 h-100">
                            <h6 class="fw-bold mb-3">Monthly Patient Growth</h6>
                            <div style="height: 300px; position: relative;">
                                <canvas id="monthlyPatientsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card p-3 border-0 h-100">
                            <h6 class="fw-bold mb-3">Camp Attendance Trends</h6>
                            <div style="height: 300px; position: relative;">
                                <canvas id="campAttendanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registered Hospitals Table -->
                <div class="card border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold text-dark">Registered Healthcare Centers</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th>Center Name</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Doctors Count</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="fw-semibold text-dark">City General Hospital</span></td>
                                    <td>Government Hospital</td>
                                    <td>New York Center</td>
                                    <td>34 Doctors</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td><span class="fw-semibold text-dark">Metro Red Cross NGO</span></td>
                                    <td>NGO Clinic</td>
                                    <td>North Suburb</td>
                                    <td>18 Doctors</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                            </tbody>
                        </table>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/../../assets/../../assets/js/dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new Chart(document.getElementById('monthlyPatientsChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{ label: 'Patients', data: [250, 420, 310, 580, 490, 720], backgroundColor: '#26215C', borderRadius: 6 }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
            new Chart(document.getElementById('campAttendanceChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['Camp A', 'Camp B', 'Camp C', 'Camp D', 'Camp E', 'Camp F'],
                    datasets: [{ label: 'Attendance', data: [120, 310, 220, 450, 390, 610], borderColor: '#5DCAA5', backgroundColor: 'rgba(93,202,165,0.1)', fill: true }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        });
    </script>
</body>
</html>
