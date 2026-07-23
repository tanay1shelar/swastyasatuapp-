<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Shared HTML Head / Header File
 * 
 * Auto-includes configuration, setups page HTML headers, imports CSS libraries
 * (Bootstrap 5, Icons, Custom Stylesheets) and starts the root layout wrapper.
 */

// Load core settings if not already included in calling file
require_once dirname(__DIR__) . '/config/config.php';
require_once __DIR__ . '/session.php';

// Default page title fallback
if (!isset($pageTitle)) {
    $pageTitle = 'Dashboard';
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SEO & Identity Optimization -->
    <title><?php echo htmlspecialchars($pageTitle); ?> | <?php echo APP_SHORT_NAME; ?> Portal</title>
    <meta name="description" content="Administrative console for SwasthyaSetu - Healthcare & Medical Camp Management System. High performance clinical records platform.">
    <meta name="author" content="<?php echo APP_DEVELOPER; ?>">
    <meta name="robots" content="noindex, nofollow"> <!-- Hospital internal portal, secure indexing disabled -->

    <!-- Inline SVG Favicon representing hospital shield logo -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232563eb'%3E%3Cpath d='M12 2C11.38 2 3 5 3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5s-8.38-3-9-3zm1 14h-2v-3H8v-2h3V8h2v3h3v2h-3v3z'/%3E%3C/svg%3E">

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Design System Global Tokens -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/variables.css?v=1.1">
    
    <!-- Base Layout Architecture -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/layouts.css?v=1.1">
    
    <!-- Custom Healthcare Component CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/components.css?v=1.1">
    
    <!-- Device Media Breakpoints -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/responsive.css?v=1.1">
</head>
<body>

<!-- Application Master Layout Container -->
<div class="app-container">
