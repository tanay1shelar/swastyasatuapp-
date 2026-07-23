            </div> <!-- /.main-content -->
        </div> <!-- /#content-wrapper -->
    </div> <!-- /#wrapper -->

    <!-- Global Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1055;">
        <div id="successToast" class="toast align-items-center border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" style="background-color: var(--secondary-color); color: #fff;">
            <div class="d-flex">
                <div class="toast-body fw-semibold d-flex align-items-center gap-2" id="toastMessage">
                    <i class="fa-solid fa-circle-check fa-lg"></i> 
                    <span>The change is saved successfully.</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js (Loaded only on reports page) -->
    <?php if(basename(dirname($_SERVER['PHP_SELF'])) == 'reports'): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../assets/js/charts.js"></script>
    <?php endif; ?>

    <!-- Custom JS -->
    <script src="../../assets/js/superadmin.js?v=2"></script>
</body>
</html>
