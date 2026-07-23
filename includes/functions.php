<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Shared Layout & Service Function Wrappers
 * 
 * Standardized includes entry point for layout helper functions.
 * Integrates seamlessly with core data services in config/functions.php.
 */

// Prevent direct access to include files
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied.");
}

// Ensure core data functions are loaded
require_once dirname(__DIR__) . '/config/functions.php';

/**
 * Render active breadcrumb list item
 * @param string $title
 * @param string $url
 * @param bool $isActive
 */
function renderBreadcrumbItem($title, $url = '#', $isActive = false) {
    if ($isActive) {
        echo '<li class="breadcrumb-custom-item active" aria-current="page">' . htmlspecialchars($title) . '</li>';
    } else {
        echo '<li class="breadcrumb-custom-item"><a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($title) . '</a></li>';
    }
}
