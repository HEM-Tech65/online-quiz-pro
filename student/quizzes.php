<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isStudent()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$page_title = "Available Quizzes";
$student_id = $_SESSION['user_id'];

// Get available quizzes with attempt counts
$quizzes = $db->query("SELECT q.*, s.name as subject_name,
                      (SELECT COUNT(*) FROM quiz_attempts 
                       WHERE quiz_id = q.id AND user_id = $student_id) as attempts
                      FROM quizzes q
                      JOIN subjects s ON q.subject_id = s.id
                      WHERE q.is_published = 1
                      AND (q.start_date IS NULL OR q.start_date <= NOW())
                      AND (q.end_date IS NULL OR q.end_date >= NOW())
                      ORDER BY q.created_at DESC");

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Available Quizzes</h1>
            </div>

            <!-- Quizzes List -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if ($quizzes->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($quiz = $quizzes->fetch_assoc()): 
                                $can_attempt = $quiz['attempts'] < $quiz['max_attempts'];
                            ?>
                                <div class="list-group-item border-0 px-0 py-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-3">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                            <p class="mb-2"><?php echo htmlspecialchars($quiz['description']); ?></p>
                                            <div class="d-flex gap-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-book me-1"></i>
                                                    <?php echo htmlspecialchars($quiz['subject_name']); ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php echo $quiz['time_limit']; ?> minutes
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-redo me-1"></i>
                                                    Attempts: <?php echo $quiz['attempts']; ?>/<?php echo $quiz['max_attempts']; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($can_attempt): ?>
                                                <a href="quiz-attempt.php?id=<?php echo $quiz['id']; ?>" 
                                                   class="btn btn-primary">
                                                    <i class="fas fa-play me-1"></i> Start Quiz
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    Attempts exhausted
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No quizzes available at the moment</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
include '../includes/footer.php';
?>