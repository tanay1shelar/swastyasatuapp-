<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Upgraded Staff Profile Management Console
 * 
 * Provides details editing, avatar uploads (upload, preview, remove),
 * accessibility settings toggles, change password validation modals,
 * and chronological timeline activity logs.
 */

// Define page parameters
$pageTitle = 'Profile & Settings Console';

// Include system config and root layout components
require_once dirname(dirname(__DIR__)) . '/config/config.php';
include ROOT_PATH . 'includes/header.php';
include ROOT_PATH . 'includes/sidebar.php';
include ROOT_PATH . 'includes/navbar.php';

// Fetch active health worker profile from database
$db = db_connect();
$stmtH = $db->prepare("SELECT * FROM health_workers WHERE employee_id = :empid LIMIT 1");
$stmtH->execute([':empid' => $user['username']]);
$hw = $stmtH->fetch() ?: [
    'phone' => '+91 98765 43210',
    'specialization' => 'Cardiology & General Medicine',
    'experience_years' => 12,
    'qualification' => 'Echocardiography, Electrocardiogram, Pediatrics',
    'dob' => '1988-11-24',
    'gender' => 'Female',
    'assigned_camp' => 'Apollo Rural Health Camp - Phase 1',
    'address' => 'Apollo Hospital Residency, New Delhi, India'
];
?>

<main class="app-content-wrapper">
    
    <!-- 1. BREADCRUMBS & PAGE HEADER -->
    <div class="app-page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-custom-item"><a href="index.php">Health Worker Portal</a></li>
                    <li class="breadcrumb-custom-item active" aria-current="page">Profile Settings</li>
                </ol>
            </nav>
            <h1 class="page-title">Profile Settings & Workspace</h1>
            <p class="text-secondary mb-0">Update credentials, upload clinical badges, adjust display preferences, and review logins.</p>
        </div>
    </div>

    <!-- 2. WORKSPACE GRID -->
    <div class="row">
        
        <!-- Left Panel: Avatar & Preferences Toggles (4 Columns) -->
        <div class="col-12 col-lg-4 mb-4">
            
            <!-- Avatar Card -->
            <div class="card-custom text-center py-4 mb-4">
                <div class="card-custom-body d-flex flex-column align-items-center justify-content-center">
                    
                    <!-- Portrait image -->
                    <div class="profile-avatar-container mb-3 position-relative">
                        <img id="profile-card-avatar" class="profile-avatar-large shadow-md" src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="User Avatar">
                    </div>

                    <!-- Image Upload/Remove Action Triggers -->
                    <div class="d-flex gap-2 mb-3">
                        <input type="file" id="avatar-file-input" accept="image/*" style="display: none;">
                        <button type="button" class="btn-custom btn-custom-outline btn-custom-sm" id="btn-upload-avatar">
                            <i class="bi bi-camera"></i> Upload
                        </button>
                        <button type="button" class="btn-custom btn-custom-outline btn-custom-sm text-danger" id="btn-remove-avatar">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>

                    <!-- Details -->
                    <h4 id="profile-card-name" class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                    <span id="profile-card-role" class="badge bg-primary-subtle text-primary px-3 py-1 mb-3"><?php echo ($user['role'] === 'Administrator') ? 'Health Worker' : htmlspecialchars($user['role']); ?></span>
                    
                    <!-- Skills and Experience overview text -->
                    <div class="text-start w-100 border-top mt-3 pt-3 px-3">
                        <div class="mb-2">
                            <span class="d-block text-muted small">Specialized Skills / Focus</span>
                            <div class="d-flex flex-wrap gap-1 mt-1" id="profile-skills-badges">
                                <?php 
                                $skills = explode(',', $hw['qualification'] ?? 'Echocardiography, Electrocardiogram, Pediatrics');
                                foreach ($skills as $s): 
                                ?>
                                    <span class="badge bg-light text-primary border"><?php echo htmlspecialchars(trim($s)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mb-0">
                            <span class="d-block text-muted small">Experience tenure</span>
                            <strong class="text-secondary small" id="profile-experience-display"><?php echo htmlspecialchars($hw['experience_years'] ?? '0'); ?> Years Clinical Residency</strong>
                        </div>
                    </div>
                    
                    <!-- Edit Actions buttons -->
                    <div class="w-100 mt-4 px-3 d-flex flex-column gap-2">
                        <button class="btn-custom btn-custom-primary w-100" id="btn-edit-profile">
                            <i class="bi bi-pencil-square"></i> Edit Details
                        </button>
                        <button class="btn-custom btn-custom-outline w-100" id="btn-trigger-password-modal">
                            <i class="bi bi-shield-lock"></i> Change Security Password
                        </button>
                    </div>

                </div>
            </div>

            <!-- Preferences Settings Toggles -->
            <div class="card-custom mb-4">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-sliders text-accent"></i> System Preferences</h5>
                </div>
                <div class="card-custom-body p-3">
                    <!-- Notification switch -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="pref-notifications" checked>
                        <label class="form-check-label small text-secondary" for="pref-notifications">System Desktop Alerts</label>
                    </div>

                    <!-- Dark Mode switch -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="pref-dark-mode">
                        <label class="form-check-label small text-secondary" for="pref-dark-mode">Dark Mode Interface</label>
                    </div>

                    <!-- Language Selector -->
                    <div class="mb-3">
                        <label class="form-label-custom small">Language Localization</label>
                        <select class="form-control-custom text-xs" id="pref-lang" style="height: 34px; font-size: var(--font-size-xs);">
                            <option value="en" selected>English (US / IN)</option>
                            <option value="hi">हिन्दी (Hindi)</option>
                        </select>
                    </div>

                    <!-- Timezone Selector -->
                    <div class="mb-3">
                        <label class="form-label-custom small">Operational Time Zone</label>
                        <select class="form-control-custom text-xs" id="pref-tz" style="height: 34px; font-size: var(--font-size-xs);">
                            <option value="ist" selected>Asia/Kolkata (IST — GMT+5:30)</option>
                            <option value="gmt">GMT (GMT+0:00)</option>
                        </select>
                    </div>

                    <!-- Accessibility Selector -->
                    <div>
                        <label class="form-label-custom small">Accessibility Options</label>
                        <select class="form-control-custom text-xs" id="pref-access" style="height: 34px; font-size: var(--font-size-xs);">
                            <option value="normal" selected>Normal Rendering</option>
                            <option value="contrast">High Contrast Mode</option>
                            <option value="large">Enlarged Layout Fonts</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Panel: Data Fields Form & Activity logs (8 Columns) -->
        <div class="col-12 col-lg-8">
            
            <!-- Forms settings card -->
            <div class="card-custom mb-4">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-person-lines-fill text-accent"></i> Credentials & Demographics</h5>
                </div>
                <div class="card-custom-body p-4">
                    
                    <form class="needs-validation" id="profile-edit-form" novalidate>
                        
                        <div class="row g-3">
                            <!-- Field 1: Full Name -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Full Name</label>
                                    <input type="text" class="form-control-custom" name="fullname" id="input-fullname" value="<?php echo htmlspecialchars($user['name']); ?>" required disabled>
                                    <div class="invalid-feedback">Full name is required.</div>
                                </div>
                            </div>
                            <!-- Field 2: Employee ID (HR allocated, read-only) -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Employee ID (Read-only)</label>
                                    <input type="text" class="form-control-custom" name="empid" id="input-empid" value="<?php echo htmlspecialchars($user['username']); ?>" required disabled>
                                </div>
                            </div>
                            
                            <!-- Field 3: Access Role (HR allocated, read-only) -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">User Access Role (Read-only)</label>
                                    <input type="text" class="form-control-custom" name="role" id="input-role" value="<?php echo ($user['role'] === 'Administrator') ? 'Health Worker' : htmlspecialchars($user['role']); ?>" required disabled>
                                </div>
                            </div>
                            <!-- Field 4: Department -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Clinical Department</label>
                                    <input type="text" class="form-control-custom" name="department" id="input-department" value="<?php echo htmlspecialchars($hw['specialization'] ?? 'N/A'); ?>" required disabled>
                                </div>
                            </div>

                            <!-- Field 5: Phone -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Contact Phone</label>
                                    <input type="tel" class="form-control-custom" name="phone" id="input-phone" value="<?php echo htmlspecialchars($hw['phone'] ?? 'N/A'); ?>" pattern="^\+91 [0-9]{5}\s[0-9]{5}$" placeholder="+91 XXXXX XXXXX" required disabled>
                                    <div class="invalid-feedback">Format: +91 XXXXX XXXXX</div>
                                </div>
                            </div>
                            <!-- Field 6: Email -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Email Address</label>
                                    <input type="email" class="form-control-custom" name="email" id="input-email" value="<?php echo htmlspecialchars($user['email']); ?>" required disabled>
                                </div>
                            </div>

                            <!-- Field 7: DOB -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Date of Birth</label>
                                    <input type="date" class="form-control-custom" name="dob" id="input-dob" value="<?php echo htmlspecialchars($hw['dob'] ?? ''); ?>" required disabled>
                                </div>
                            </div>
                            <!-- Field 8: Gender -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Gender Identification</label>
                                    <select class="form-control-custom" name="gender" id="input-gender" style="height: 38px;" required disabled>
                                        <option value="Male" <?php echo (($hw['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo (($hw['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo (($hw['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Field 9: Assigned Camp Site -->
                            <div class="col-12">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Assigned Operational Camp</label>
                                    <input type="text" class="form-control-custom" name="assignedcamp" id="input-assignedcamp" value="<?php echo htmlspecialchars($hw['assigned_camp'] ?? 'N/A'); ?>" required disabled>
                                </div>
                            </div>

                            <!-- Field 10: Address -->
                            <div class="col-12">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Permanent Residential Address</label>
                                    <textarea class="form-control-custom" name="address" id="input-address" rows="2" required disabled><?php echo htmlspecialchars($hw['address'] ?? 'N/A'); ?></textarea>
                                </div>
                            </div>

                            <!-- Field 11: Skills (Comma separated list input) -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Professional Skills (Comma Separated)</label>
                                    <input type="text" class="form-control-custom" name="skills" id="input-skills" value="<?php echo htmlspecialchars($hw['qualification'] ?? 'N/A'); ?>" required disabled>
                                </div>
                            </div>
                            
                            <!-- Field 12: Experience details -->
                            <div class="col-12 col-md-6">
                                <div class="form-group-custom">
                                    <label class="form-label-custom">Clinical Experience tenure statement</label>
                                    <input type="text" class="form-control-custom" name="experience" id="input-experience" value="<?php echo htmlspecialchars($hw['experience_years'] ?? '0'); ?> Years Clinical Residency" required disabled>
                                </div>
                            </div>
                        </div>

                        <!-- Save & Cancel form controllers -->
                        <div class="d-flex justify-content-end gap-2 mt-4" id="profile-form-controls" style="display: none !important;">
                            <button type="button" class="btn-custom btn-custom-outline" id="btn-cancel-profile">Cancel</button>
                            <button type="submit" class="btn-custom btn-custom-success" id="btn-save-profile">Save Changes</button>
                        </div>

                    </form>

                </div>
            </div>

            <!-- Activity Logs Timeline -->
            <div class="card-custom">
                <div class="card-custom-header">
                    <h5 class="card-custom-title"><i class="bi bi-clock-history text-accent"></i> Personal Activity logs (Audit Trail)</h5>
                </div>
                <div class="card-custom-body p-3">
                    <ul class="profile-timeline ps-3 mb-0 small text-secondary">
                        <li class="timeline-event mb-3">
                            <span class="d-block fw-bold text-primary" id="log-time-1">Today, 09:00 AM — Recent Login</span>
                            <span>Dashboard logged successfully from terminal console (IP 192.168.1.42).</span>
                        </li>
                        <li class="timeline-event mb-3">
                            <span class="d-block fw-bold text-primary" id="log-time-2">Yesterday, 04:30 PM — Profile Updated</span>
                            <span>Contact mobile number and home residency credentials modified.</span>
                        </li>
                        <li class="timeline-event mb-3">
                            <span class="d-block fw-bold text-primary" id="log-time-3">3 days ago — Security Password Changed</span>
                            <span>Authentication cryptographic salt hash recalculated. System credentials validated.</span>
                        </li>
                        <li class="timeline-event mb-3">
                            <span class="d-block fw-bold text-primary" id="log-time-4">1 week ago — Camp Site Assigned</span>
                            <span>Assigned to Apollo Rural Health Camp Site - Phase 1 as Lead Clinician.</span>
                        </li>
                        <li class="timeline-event">
                            <span class="d-block fw-bold text-primary" id="log-time-5">2 weeks ago — Account Initialized</span>
                            <span>HR credentials registered under EMP-2026-9042 with Administrator roles.</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

    </div>

    <!-- 3. MODAL FOR CHANGE PASSWORD -->
    <div class="modal-custom-backdrop" id="changePasswordModal">
        <div class="modal-custom-dialog">
            <div class="modal-custom-header">
                <h5 class="modal-custom-title"><i class="bi bi-shield-lock-fill text-accent"></i> Reset Security Password</h5>
                <button class="modal-custom-close" data-dismiss="modal">&times;</button>
            </div>
            <form id="change-password-form" class="needs-validation" novalidate>
                <div class="modal-custom-body">
                    <div class="form-group-custom mb-3">
                        <label class="form-label-custom">Current Password</label>
                        <input type="password" class="form-control-custom" id="pass-current" required>
                        <div class="invalid-feedback">Please enter current password.</div>
                    </div>
                    <div class="form-group-custom mb-3">
                        <label class="form-label-custom">New Password</label>
                        <input type="password" class="form-control-custom" id="pass-new" minlength="6" required>
                        <div class="invalid-feedback">Password must be at least 6 characters.</div>
                    </div>
                    <div class="form-group-custom">
                        <label class="form-label-custom">Confirm New Password</label>
                        <input type="password" class="form-control-custom" id="pass-confirm" minlength="6" required>
                        <div class="invalid-feedback">Passwords must match exactly.</div>
                    </div>
                </div>
                <div class="modal-custom-footer">
                    <button type="button" class="btn-custom btn-custom-outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-custom btn-custom-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>

</main><!-- /.app-content-wrapper -->

<!-- Scoped visual styles for timelines and avatar borders -->
<style>
    .profile-avatar-large {
        width: 140px;
        height: 140px;
        border-radius: var(--radius-full);
        object-fit: cover;
        border: 4px solid var(--border-color);
        transition: var(--transition-normal);
    }
    html[data-theme="dark"] .profile-avatar-large {
        border-color: var(--border-color);
    }
    
    .profile-timeline {
        list-style-type: none;
        position: relative;
        border-left: 2px solid var(--border-color);
        padding-left: 1.5rem !important;
    }
    .profile-timeline .timeline-event {
        position: relative;
    }
    .profile-timeline .timeline-event::before {
        content: "";
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: var(--radius-full);
        background-color: var(--accent);
        left: -29px;
        top: 4px;
        border: 2px solid var(--bg-card);
    }
    .form-control-custom:disabled {
        background-color: var(--bg-app);
        opacity: 0.85;
    }
</style>

<!-- Load profile editor script -->
<script src="<?php echo BASE_URL; ?>assets/js/modules/profile-edit.js"></script>

<?php
// Include structural footer scripts
include ROOT_PATH . 'includes/footer.php';
?>
