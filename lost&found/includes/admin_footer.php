        </div>
    </main>

    <!-- Footer Section -->
    <footer style="background-color: #1b262c; border-top: 4px solid var(--secondary-color);">
        <div class="container text-center">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start mb-3 mb-md-0">
                    <h5 class="text-white mb-1"><i class="bi bi-shield-lock-fill me-1 text-success"></i>Smart Campus Admin Console</h5>
                    <p class="mb-0 text-muted small">Access and records administration workspace.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 small">&copy; <?php echo date('Y'); ?> College Campus. All rights reserved.</p>
                    <p class="mb-0 mt-1 small">
                        <a href="<?php echo BASE_URL; ?>index.php" class="text-muted text-decoration-none">
                            <i class="bi bi-house-door-fill me-1"></i>Return to Campus Homepage
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle with Popper JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom validation Javascript -->
    <script src="<?php echo BASE_URL; ?>assets/js/validation.js"></script>
</body>
</html>
