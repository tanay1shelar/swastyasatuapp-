<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Shared Placeholder Layout Engine
 * 
 * DRY component that renders structured placeholder content for modules 
 * scheduled for future development phases.
 */

// Prevent direct access to includes
if (!defined('APP_NAME')) {
    require_once dirname(__DIR__) . '/config/config.php';
    require_once dirname(__DIR__) . '/config/session.php';
}

// Fallback metadata if variables are not preset
$pageTitle = $pageTitle ?? 'Module';
$moduleIcon = $moduleIcon ?? 'bi-cone-striped';
$moduleName = $moduleName ?? 'Module Details';
$moduleDesc = $moduleDesc ?? 'This module is planned for future implementation phases.';
$estimatedStatus = $estimatedStatus ?? 'Module Ready for Development';
$badgeClass = $badgeClass ?? 'badge-custom-primary';

// Include layout headers
include dirname(__DIR__) . '/includes/header.php';
include dirname(__DIR__) . '/includes/sidebar.php';
include dirname(__DIR__) . '/includes/navbar.php';
?>

<!-- Reusable Module Placeholder Content Area -->
<main class="app-content-wrapper">
    
    <!-- Breadcrumb and Page Header -->
    <div class="app-page-header">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb-custom">
                    <li class="breadcrumb-custom-item"><a href="index.php">Administrative Portal</a></li>
                    <li class="breadcrumb-custom-item active" aria-current="page"><?php echo htmlspecialchars($pageTitle); ?></li>
                </ol>
            </nav>
            <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <p class="text-secondary mb-0"><?php echo htmlspecialchars($moduleDesc); ?></p>
        </div>
    </div>

    <!-- Centered Custom Information Container -->
    <div class="row justify-content-center mt-5">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card-custom text-center py-5 px-4 border-top border-4 border-primary">
                <div class="card-custom-body d-flex flex-column align-items-center justify-content-center">
                    
                    <!-- Circular Icon Wrapper -->
                    <div class="mb-4 text-primary" style="font-size: 3.5rem; width: 90px; height: 90px; background-color: var(--accent-light); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center;">
                        <i class="bi <?php echo htmlspecialchars($moduleIcon); ?>"></i>
                    </div>

                    <!-- Module Title & Details -->
                    <h3 class="fw-bold text-primary mb-2"><?php echo htmlspecialchars($moduleName); ?></h3>
                    <p class="text-secondary px-3 mb-3">
                        This module is a core component of the Health Worker workspace. It is fully integrated into the layouts, sidebars, and session contexts of SwasthyaSetu.
                    </p>

                    <!-- Operational Status Badge -->
                    <div class="mb-4">
                        <span class="badge-custom <?php echo htmlspecialchars($badgeClass); ?>">
                            <i class="bi bi-clock-history me-1"></i> <?php echo htmlspecialchars($estimatedStatus); ?>
                        </span>
                    </div>

                    <!-- Architectural Information Alert -->
                    <div class="alert alert-info border-0 bg-light text-secondary small py-3 px-4 rounded mb-4 text-start" style="max-width: 480px; line-height: 1.6;">
                        <div class="d-flex gap-2">
                            <i class="bi bi-info-circle-fill text-primary mt-1"></i>
                            <div>
                                <strong>System Architecture Notice:</strong><br>
                                The page routing, design systems, and sidebar bindings are successfully configured. Front-end validation checks, database connections, and MySQL CRUD operations will be integrated in the next development phase.
                            </div>
                        </div>
                    </div>

                    <!-- Disabled Call-To-Action Button -->
                    <button class="btn-custom btn-custom-outline btn-custom-lg w-100 disabled" style="max-width: 300px;" disabled>
                        <i class="bi bi-cone-striped me-2"></i> Ready for Development
                    </button>

                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Include layout closing wrappers
include dirname(__DIR__) . '/includes/footer.php';
?>
