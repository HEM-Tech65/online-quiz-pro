<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isStudent()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;
$student_id = $_SESSION['user_id'];

// Get attempt details - no date restrictions
$attempt = $db->query("
    SELECT qa.*, q.title as quiz_title, q.passing_score,
           u.first_name, u.last_name, u.email,
           s.name as subject_name
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN users u ON qa.user_id = u.id
    JOIN subjects s ON q.subject_id = s.id
    WHERE qa.id = $attempt_id 
    AND qa.user_id = $student_id
    AND qa.status = 'completed'
")->fetch_assoc();

if (!$attempt) {
    $_SESSION['error_message'] = "Results not available. Please ensure you completed the quiz.";
    header("Location: results.php");
    exit();
}

// Get all questions and answers
$questions = $db->query("
    SELECT q.id, q.text as question_text, q.type, q.marks,
           a.answer_text as student_answer, 
           q.correct_option as correct_answer,
           a.is_correct
    FROM answers a
    JOIN questions q ON a.question_id = q.id
    WHERE a.attempt_id = $attempt_id
    ORDER BY q.id
");

$is_passed = $attempt['score'] >= $attempt['passing_score'];

include '../includes/header.php';
?>

<!-- Rest of your HTML remains unchanged -->
 
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Quiz Results: <?= htmlspecialchars($attempt['quiz_title']) ?></h1>
                <a href="results.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Results
                </a>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Attempt Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Student:</strong> <?= htmlspecialchars($attempt['first_name'] . ' ' . $attempt['last_name']) ?></p>
                            <p><strong>Subject:</strong> <?= htmlspecialchars($attempt['subject_name']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date:</strong> <?= date('F j, Y g:i A', strtotime($attempt['completed_at'])) ?></p>
                            <p><strong>Result:</strong> 
                                <span class="badge bg-<?= $is_passed ? 'success' : 'danger' ?>">
                                    <?= $is_passed ? 'Passed' : 'Failed' ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Score: <?= $attempt['score'] ?>%</span>
                            <span>Passing Score: <?= $attempt['passing_score'] ?>%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-<?= $is_passed ? 'success' : 'danger' ?>" 
                                 role="progressbar" 
                                 style="width: <?= $attempt['score'] ?>%" 
                                 aria-valuenow="<?= $attempt['score'] ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Question Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Question</th>
                                    <th>Your Answer</th>
                                    <th>Correct Answer</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($questions->num_rows > 0): ?>
                                    <?php $i = 1; while ($question = $questions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($question['question_text']) ?></td>
                                        <td><?= htmlspecialchars($question['student_answer']) ?></td>
                                        <td><?= htmlspecialchars($question['correct_answer']) ?></td>
                                        <td class="text-center">
                                            <?php if ($question['is_correct']): ?>
                                                <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><i class="fas fa-times"></i></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            No questions found for this attempt
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>