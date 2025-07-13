</main> <!-- Close main container from header -->

<footer class="hero-section1 py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <img src="assets/images/logoc.png" alt="gctu logo" class="img-fluid mb-3 justify-content-center" style="width: 80px; height: 80px;">
                <h5>GCTU Online Quiz Pro</h5>
                <p class="">A professional quiz management system for educational institutions.</p>
            </div>
            <div class="col-md-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>" class="text-decoration-none" style="color:white;">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/login.php" class="text-decoration-none" style="color:white;">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/register.php" class="text-decoration-none" style="color:white;">Register</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Contact</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-envelope me-2"></i> support@gctuquizpro.edu</li>
                    <li><i class="fas fa-phone me-2"></i> +233 123 456 789</li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <small>&copy; <?php echo date('Y'); ?> GCTU Online Quiz Pro. All rights reserved.</small>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>

</body>
</html>