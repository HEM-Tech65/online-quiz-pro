<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$page_title = "Register";

if ($auth->isLoggedIn()) {
    header("Location: " . BASE_URL . "/" . 
          ($auth->isAdmin() ? "admin/dashboard.php" : 
          ($auth->isLecturer() ? "lecturer/dashboard.php" : "student/dashboard.php")));
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = $auth->register($_POST);
    
    if ($result['success']) {
        $success = true;
        $_SESSION['success_message'] = "Registration successful! Please login.";
        header("Location: " . BASE_URL . "/login.php");
        exit();
    } else {
        $errors = $result['errors'];
    }
}

include 'includes/header.php';
?>
<style>
    body {
        background-image: url('assets/images/reg.png');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }
</style>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6 animate-on-scroll">
        <div class="card shadow-sm animate-on-scroll delay-1">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">Create Your Account</h2>
                    <p class="text-muted">Join our Online Quiz System</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" autocomplete="off">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">At least 8 characters</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">I am a:</label>
                        <select class="form-select" id="role" name="role">
                            <option value="student" <?php echo ($_POST['role'] ?? '') == 'student' ? 'selected' : ''; ?>>Student</option>
                            <option value="lecturer" <?php echo ($_POST['role'] ?? '') == 'lecturer' ? 'selected' : ''; ?>>Lecturer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="departmentField" style="display: none;">
                        <label for="department" class="form-label">Department</label>
                        <input type="text" class="form-control" id="department" name="department" 
                               value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                        <i class="fas fa-user-plus me-2"></i> Register
                    </button>
                    
                    <div class="text-center">
                        <p class="text-muted">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Show department field only for lecturers
document.getElementById('role').addEventListener('change', function() {
    document.getElementById('departmentField').style.display = 
        this.value === 'lecturer' ? 'block' : 'none';
});
</script>

<?php
include 'includes/footer.php';
?>