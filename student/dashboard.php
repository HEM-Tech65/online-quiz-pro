<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isStudent()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$page_title = "Student Dashboard";
$student_id = $_SESSION['user_id'];

// Get student stats
$stats = [
    'quizzes_taken' => $db->query("SELECT COUNT(*) as count FROM quiz_attempts WHERE user_id = $student_id")->fetch_assoc()['count'],
    'quizzes_available' => $db->query("SELECT COUNT(*) as count FROM quizzes WHERE is_published = 1 AND (start_date IS NULL OR start_date <= NOW()) AND (end_date IS NULL OR end_date >= NOW())")->fetch_assoc()['count']
];

// Safely calculate average score
$avgScoreResult = $db->query("SELECT AVG(score) as avg FROM quiz_attempts WHERE user_id = $student_id AND status = 'completed'")->fetch_assoc();
$stats['average_score'] = $avgScoreResult['avg'] ? round($avgScoreResult['avg'], 1) : 0;

// Get recent quiz attempts
$attempts = $db->query("SELECT qa.*, q.title as quiz_title 
                       FROM quiz_attempts qa
                       JOIN quizzes q ON qa.quiz_id = q.id
                       WHERE qa.user_id = $student_id
                       ORDER BY qa.started_at DESC LIMIT 5");

// Get available quizzes
$quizzes = $db->query("SELECT q.*, s.name as subject_name
                      FROM quizzes q
                      JOIN subjects s ON q.subject_id = s.id
                      WHERE q.is_published = 1
                      AND (q.start_date IS NULL OR q.start_date <= NOW())
                      AND (q.end_date IS NULL OR q.end_date >= NOW())
                      AND q.id NOT IN (
                          SELECT quiz_id FROM quiz_attempts 
                          WHERE user_id = $student_id 
                          AND status = 'completed'
                          GROUP BY quiz_id 
                          HAVING COUNT(*) >= q.max_attempts
                      )
                      ORDER BY q.created_at DESC LIMIT 5");

include '../includes/header.php';
?>
<style>
    body {
        background-image: url('../assets/images/lec.png');
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
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
                    <small class="text-muted">Student</small>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quizzes.php">
                            <i class="fas fa-list-ol me-2"></i> Available Quizzes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="results.php">
                            <i class="fas fa-chart-bar me-2"></i> My Results
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
                <h1 class="h2">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?></h1>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase">Quizzes Taken</h6>
                                    <h2 class="mb-0"><?php echo $stats['quizzes_taken']; ?></h2>
                                </div>
                                <div class="icon-shape bg-white text-primary rounded-circle p-3">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="results.php" class="text-white stretched-link">View all</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase">Average Score</h6>
                                    <h2 class="mb-0"><?php echo $stats['average_score']; ?>%</h2>
                                </div>
                                <div class="icon-shape bg-white text-success rounded-circle p-3">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="results.php" class="text-white stretched-link">View details</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase">Available Quizzes</h6>
                                    <h2 class="mb-0"><?php echo $stats['quizzes_available']; ?></h2>
                                </div>
                                <div class="icon-shape bg-white text-info rounded-circle p-3">
                                    <i class="fas fa-question-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="quizzes.php" class="text-white stretched-link">View all</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Available Quizzes</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($quizzes->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                                    <a href="quiz-attempt.php?id=<?php echo $quiz['id']; ?>" 
                                       class="list-group-item list-group-item-action border-0 px-0 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($quiz['title']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($quiz['subject_name']); ?></small>
                                            </div>
                                            <span class="badge bg-primary">
                                                <i class="fas fa-play me-1"></i> Start
                                            </span>
                                        </div>
                                    </a>
                                    <?php endwhile; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="quizzes.php" class="btn btn-sm btn-outline-primary">
                                        View All Available Quizzes
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No quizzes available at the moment</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Recent Attempts</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($attempts->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($attempt = $attempts->fetch_assoc()): ?>
                                    <div class="list-group-item border-0 px-0 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($attempt['quiz_title']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo date('M j, Y', strtotime($attempt['started_at'])); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-<?php 
                                                echo $attempt['status'] == 'completed' ? 'success' : 
                                                ($attempt['status'] == 'in_progress' ? 'warning' : 'secondary'); 
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $attempt['status'])); ?>
                                            </span>
                                        </div>
                                        <?php if ($attempt['status'] == 'completed' && isset($attempt['score'])): ?>
                                        <div class="progress mt-2" style="height: 8px;">
                                            <div class="progress-bar bg-success" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $attempt['score']; ?>%" 
                                                 aria-valuenow="<?php echo $attempt['score']; ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="text-muted">Score: <?php echo $attempt['score']; ?>%</small>
                                        <?php endif; ?>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="results.php" class="btn btn-sm btn-outline-primary">
                                        View All Results
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No quiz attempts yet</p>
                                    <a href="quizzes.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play me-1"></i> Take a Quiz
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
include '../includes/footer.php';
?>