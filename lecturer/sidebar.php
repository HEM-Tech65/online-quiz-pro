<?php
// This is lecturer/sidebar.php
?>
<div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <div class="avatar-circle mb-2">
                <?php if (!empty($_SESSION['profile_image'])): ?>
                    <img src="<?php echo BASE_URL . '/assets/images/profiles/' . $_SESSION['profile_image']; ?>" 
                         class="rounded-circle" width="80" height="80">
                <?php else: ?>
                    <div class="initials-circle">
                        <?php echo substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1); ?>
                    </div>
                <?php endif; ?>
            </div>
            <h6 class="text-white mb-1"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h6>
            <small class="text-muted">Lecturer</small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="<?php echo BASE_URL; ?>/lecturer/dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'quizzes') !== false ? 'active' : ''; ?>" 
                   href="<?php echo BASE_URL; ?>/lecturer/quizzes/manage.php">
                    <i class="fas fa-question-circle me-2"></i> My Quizzes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'questions') !== false ? 'active' : ''; ?>" 
                   href="<?php echo BASE_URL; ?>/lecturer/questions/">
                    <i class="fas fa-list-ol me-2"></i> Question Bank
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'subjects.php' ? 'active' : ''; ?>" 
                   href="<?php echo BASE_URL; ?>/lecturer/subjects.php">
                    <i class="fas fa-users me-2"></i> Subjects
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'results.php' ? 'active' : ''; ?>" 
                   href="<?php echo BASE_URL; ?>/lecturer/results.php">
                    <i class="fas fa-chart-bar me-2"></i> Results
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>