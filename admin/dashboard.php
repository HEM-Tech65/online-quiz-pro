<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$page_title = "Admin Dashboard";

// Get statistics
$stats = [
    'lecturers' => $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'lecturer'")->fetch_assoc()['count'],
    'students' => $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch_assoc()['count'],
    'quizzes' => $db->query("SELECT COUNT(*) as count FROM quizzes")->fetch_assoc()['count'],
    'subjects' => $db->query("SELECT COUNT(*) as count FROM subjects")->fetch_assoc()['count'],
    'attempts' => $db->query("SELECT COUNT(*) as count FROM quiz_attempts")->fetch_assoc()['count']
];

// Recent activities
$activities = $db->query("SELECT n.*, u.first_name, u.last_name 
                         FROM notifications n
                         JOIN users u ON n.user_id = u.id
                         ORDER BY n.created_at DESC LIMIT 5");

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="subjects.php">
                            <i class="fas fa-book me-2"></i> Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="lecturers.php">
                            <i class="fas fa-chalkboard-teacher me-2"></i> Lecturers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.php">
                            <i class="fas fa-users me-2"></i> Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quizzes/manage.php">
                            <i class="fas fa-question-circle me-2"></i> Quizzes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="results.php">
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
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> New Quiz
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase">Lecturers</h6>
                                    <h2 class="mb-0"><?php echo $stats['lecturers']; ?></h2>
                                </div>
                                <div class="icon-shape bg-white text-primary rounded-circle p-3">
                                    <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="lecturers.php" class="text-white stretched-link">View all</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase">Students</h6>
                                    <h2 class="mb-0"><?php echo $stats['students']; ?></h2>
                                </div>
                                <div class="icon-shape bg-white text-success rounded-circle p-3">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="students.php" class="text-white stretched-link">View all</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase">Quizzes</h6>
                                    <h2 class="mb-0"><?php echo $stats['quizzes']; ?></h2>
                                </div>
                                <div class="icon-shape bg-white text-info rounded-circle p-3">
                                    <i class="fas fa-question-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="quizzes/manage.php" class="text-white stretched-link">View all</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Recent Activities</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($activities->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($activity = $activities->fetch_assoc()): ?>
                                    <div class="list-group-item border-0 px-0 py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light-primary text-primary rounded-circle me-3">
                                                <i class="fas fa-bell"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                                <p class="mb-0 text-muted small"><?php echo htmlspecialchars($activity['message']); ?></p>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?> • 
                                                    <?php echo time_elapsed_string($activity['created_at']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No recent activities</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="quizzes/create.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus me-2"></i> Create Quiz
                                </a>
                                <a href="subjects.php?action=create" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-book me-2"></i> Add Subject
                                </a>
                                <a href="lecturers.php?action=create" class="btn btn-outline-success btn-lg">
                                    <i class="fas fa-user-plus me-2"></i> Add Lecturer
                                </a>
                                <a href="students.php?action=create" class="btn btn-outline-info btn-lg">
                                    <i class="fas fa-user-graduate me-2"></i> Add Student
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
// Helper function to display time elapsed in human-readable format
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $w = floor($diff->d / 7);
    $diff->d -= $w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    $intervals = array(
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $w,
        'd' => $diff->d,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    );
    foreach ($string as $k => &$v) {
        if ($intervals[$k]) {
            $v = $intervals[$k] . ' ' . $v . ($intervals[$k] > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

include '../includes/footer.php';
?>