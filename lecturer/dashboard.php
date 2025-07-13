<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isLecturer()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$page_title = "Lecturer Dashboard";
$lecturer_id = $_SESSION['user_id'];

// Get lecturer stats
$stats = [
    'quizzes' => $db->query("SELECT COUNT(*) as count FROM quizzes WHERE created_by = $lecturer_id")->fetch_assoc()['count'],
    'active_quizzes' => $db->query("SELECT COUNT(*) as count FROM quizzes WHERE created_by = $lecturer_id AND is_published = 1")->fetch_assoc()['count'],
    'students' => $db->query("SELECT COUNT(DISTINCT user_id) as count FROM quiz_attempts qa JOIN quizzes q ON qa.quiz_id = q.id WHERE q.created_by = $lecturer_id")->fetch_assoc()['count']
];

// Safely calculate average score
$avgScoreResult = $db->query("SELECT AVG(score) as avg FROM quiz_attempts qa JOIN quizzes q ON qa.quiz_id = q.id WHERE q.created_by = $lecturer_id AND qa.status = 'completed'")->fetch_assoc();
$stats['average_score'] = $avgScoreResult['avg'] ? round($avgScoreResult['avg'], 1) : 0;

// Get recent quizzes
$recent_quizzes = $db->query("SELECT q.*, s.name as subject_name 
                             FROM quizzes q
                             JOIN subjects s ON q.subject_id = s.id
                             WHERE q.created_by = $lecturer_id
                             ORDER BY q.created_at DESC LIMIT 5");

// Get recent attempts
$recent_attempts = $db->query("SELECT qa.*, q.title as quiz_title, u.first_name, u.last_name
                              FROM quiz_attempts qa
                              JOIN quizzes q ON qa.quiz_id = q.id
                              JOIN users u ON qa.user_id = u.id
                              WHERE q.created_by = $lecturer_id
                              ORDER BY qa.started_at DESC LIMIT 5");

include '../includes/header.php';
?>

<!--<style>
    body {
        background-image: url('../assets/images/logo.png');
        background-repeat: no-repeat;
        background-position: center;
        width: auto;
    }
</style>-->

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
         <?php include __DIR__ . '/../lecturer/sidebar.php'; ?>
        <!--<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quizzes/manage.php">
                            <i class="fas fa-question-circle me-2"></i> My Quizzes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="questions/">
                            <i class="fas fa-list-ol me-2"></i> Question Bank
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
        </nav>-->

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="quizzes/create.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> New Quiz
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase">My Quizzes</h6>
                                    <h2 class="mb-0"><?php echo $stats['quizzes']; ?></h2>
                                </div>
                                <div class="icon-shape bg-white text-primary rounded-circle p-3">
                                    <i class="fas fa-question-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="quizzes/manage.php" class="text-white stretched-link">View all</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase">Active Quizzes</h6>
                                    <h2 class="mb-0"><?php echo $stats['active_quizzes']; ?></h2>
                                </div>
                                <div class="icon-shape bg-white text-success rounded-circle p-3">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="quizzes/manage.php?filter=active" class="text-white stretched-link">View active</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase">Students</h6>
                                    <h2 class="mb-0"><?php echo $stats['students']; ?></h2>
                                </div>
                                <div class="icon-shape bg-white text-info rounded-circle p-3">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="results.php" class="text-white stretched-link">View students</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase">Avg. Score</h6>
                                    <h2 class="mb-0"><?php echo $stats['average_score']; ?>%</h2>
                                </div>
                                <div class="icon-shape bg-white text-warning rounded-circle p-3">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="results.php" class="text-white stretched-link">View analytics</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">Recent Quizzes</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($recent_quizzes->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($quiz = $recent_quizzes->fetch_assoc()): ?>
                                    <a href="quizzes/manage.php?action=view&id=<?php echo $quiz['id']; ?>" 
                                       class="list-group-item list-group-item-action border-0 px-0 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($quiz['title']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($quiz['subject_name']); ?></small>
                                            </div>
                                            <span class="badge bg-<?php echo $quiz['is_published'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $quiz['is_published'] ? 'Published' : 'Draft'; ?>
                                            </span>
                                        </div>
                                    </a>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No quizzes created yet</p>
                                    <a href="quizzes/create.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus me-1"></i> Create Your First Quiz
                                    </a>
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
                            <?php if ($recent_attempts->num_rows > 0): ?>
                                <div class="list-group list-group-flush">
                                    <?php while ($attempt = $recent_attempts->fetch_assoc()): ?>
                                    <div class="list-group-item border-0 px-0 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($attempt['quiz_title']); ?></h6>
                                                <small class="text-muted">
                                                    By <?php echo htmlspecialchars($attempt['first_name'].' '.$attempt['last_name']); ?>
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
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No quiz attempts yet</p>
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