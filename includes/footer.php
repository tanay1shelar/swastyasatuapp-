<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Shared Footer Component
 * 
 * Renders the layout's admin footer details, closes layout structure tags,
 * and loads Bootstrap 5 JS and custom modular JavaScript files.
 */

// Safe access validation
if (!defined('APP_NAME')) {
    exit('Direct access not permitted.');
}
?>

    <!-- Reusable System Footer -->
    <footer class="app-footer">
        <div>
            <strong><?php echo APP_NAME; ?></strong> &copy; <?php echo APP_COPYRIGHT_YEAR; ?>
        </div>
        <div>
            <span>Version <?php echo APP_VERSION; ?></span>
            <span class="mx-2">|</span>
            <span>Partner: <strong><?php echo APP_DEVELOPER; ?></strong></span>
        </div>
    </footer>

</div><!-- /.app-main -->
</div><!-- /.app-container -->

<!-- Bootstrap 5 JS Bundle CDN (Includes Popper.js for tooltips/dropdowns) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Custom Framework Core JS Files (Sequential Loading) -->
<script src="<?php echo BASE_URL; ?>assets/js/core/common.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/core/sidebar.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/core/dropdown.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/core/notifications.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/core/theme.js"></script>

</body>
</html>
