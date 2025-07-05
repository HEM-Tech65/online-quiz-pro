</main> <!-- Close main container from header -->

<footer class="bg-light py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Online Quiz Pro</h5>
                <p class="text-muted">A professional quiz management system for educational institutions.</p>
            </div>
            <div class="col-md-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>" class="text-decoration-none">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/login.php" class="text-decoration-none">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/register.php" class="text-decoration-none">Register</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Contact</h5>
                <ul class="list-unstyled text-muted">
                    <li><i class="fas fa-envelope me-2"></i> support@quizpro.edu</li>
                    <li><i class="fas fa-phone me-2"></i> +233 123 456 789</li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="text-center text-muted">
            <small>&copy; <?php echo date('Y'); ?> Online Quiz Pro. All rights reserved.</small>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>

</body>
</html>