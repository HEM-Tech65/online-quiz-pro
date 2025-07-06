<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isStudent()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$page_title = "My Results";
$student_id = $_SESSION['user_id'];

// Get all completed attempts regardless of quiz dates
$attempts = $db->query("SELECT qa.*, q.title as quiz_title, s.name as subject_name
                       FROM quiz_attempts qa
                       JOIN quizzes q ON qa.quiz_id = q.id
                       JOIN subjects s ON q.subject_id = s.id
                       WHERE qa.user_id = $student_id
                       AND qa.status = 'completed'
                       ORDER BY qa.started_at DESC");

// Cleanup any stuck attempts (safety measure)
$db->query("UPDATE quiz_attempts 
           SET status = 'completed' 
           WHERE user_id = $student_id 
           AND status = 'in_progress'
           AND completed_at IS NOT NULL");

include '../includes/header.php';
?>

<!-- Rest of your HTML remains unchanged -->
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Quiz Results</h1>
            </div>

            <!-- Results Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if ($attempts->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Quiz</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Score</th>
                                        <th>Date Taken</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($attempt = $attempts->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                                        <td><?php echo htmlspecialchars($attempt['subject_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $attempt['status'] == 'completed' ? 'success' : 
                                                ($attempt['status'] == 'in_progress' ? 'warning' : 'secondary'); 
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $attempt['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($attempt['status'] == 'completed' && isset($attempt['score'])): ?>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" 
                                                         role="progressbar" 
                                                         style="width: <?php echo $attempt['score']; ?>%" 
                                                         aria-valuenow="<?php echo $attempt['score']; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <?php echo $attempt['score']; ?>%
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y g:i A', strtotime($attempt['started_at'])); ?>
                                        </td>
                                        <td>
                                            <?php if ($attempt['status'] == 'completed'): ?>
                                                <a href="quiz-result.php?attempt_id=<?php echo $attempt['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i> View Details
                                                </a>
                                            <?php elseif ($attempt['status'] == 'in_progress'): ?>
                                                <a href="quiz-attempt.php?id=<?php echo $attempt['quiz_id']; ?>" 
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-play me-1"></i> Continue
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No quiz results found</p>
                            <a href="quizzes.php" class="btn btn-primary">
                                <i class="fas fa-play me-1"></i> Take a Quiz
                            </a>
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