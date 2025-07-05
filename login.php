<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$page_title = "Login";

if ($auth->isLoggedIn()) {
    header("Location: " . BASE_URL . "/" . ($auth->isAdmin() ? "admin/dashboard.php" : 
          ($auth->isLecturer() ? "lecturer/dashboard.php" : "student/dashboard.php")));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($email, $password)) {
        $_SESSION['success_message'] = "Welcome back!";
        header("Location: " . BASE_URL . "/" . ($auth->isAdmin() ? "admin/dashboard.php" : 
              ($auth->isLecturer() ? "lecturer/dashboard.php" : "student/dashboard.php")));
        exit();
    } else {
        $_SESSION['error_message'] = "Invalid email or password";
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">Welcome Back</h2>
                    <p class="text-muted">Please login to your account</p>
                </div>
                
                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                    
                    <div class="text-center">
                        <p class="text-muted">Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>