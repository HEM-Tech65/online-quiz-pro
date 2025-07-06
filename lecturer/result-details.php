<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (!$auth->isLoggedIn() || !$auth->isLecturer()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];
$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;

if (!$attempt_id) {
    $_SESSION['error_message'] = "Invalid attempt ID";
    header("Location: results.php");
    exit();
}

// Get attempt details
$attempt = $db->query("
    SELECT qa.*, q.title as quiz_title, 
           u.first_name, u.last_name, u.email,
           s.name as subject_name,
           q.passing_score
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN users u ON qa.user_id = u.id
    JOIN subjects s ON q.subject_id = s.id
    WHERE qa.id = $attempt_id AND q.created_by = $lecturer_id
")->fetch_assoc();

if (!$attempt) {
    $_SESSION['error_message'] = "Attempt not found or access denied";
    header("Location: results.php");
    exit();
}

// Get all questions and answers for this attempt
$questions = $db->query("
    SELECT q.id, q.text as question_text, q.type, q.marks,
           a.answer_text as student_answer, 
           q.correct_option as correct_answer,
           (a.answer_text = q.correct_option) as is_correct
    FROM answers a
    JOIN questions q ON a.question_id = q.id
    WHERE a.attempt_id = $attempt_id
    ORDER BY q.id
");

// Calculate score percentage
$total_questions = $db->query("SELECT COUNT(*) as total FROM questions WHERE quiz_id = {$attempt['quiz_id']}")->fetch_assoc()['total'];
$score_percentage = $total_questions > 0 ? round(($attempt['score'] / $total_questions) * 100) : 0;
$passing_percentage = $attempt['passing_score'] ?? 50;
$is_passed = $score_percentage >= $passing_percentage;

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../lecturer/sidebar.php'; ?>
        
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
                            <p><strong>Email:</strong> <?= htmlspecialchars($attempt['email']) ?></p>
                            <p><strong>Subject:</strong> <?= htmlspecialchars($attempt['subject_name']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date:</strong> <?= date('F j, Y g:i A', strtotime($attempt['completed_at'] ?? $attempt['started_at'])) ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-<?= $attempt['status'] == 'completed' ? 'success' : 'warning' ?>">
                                    <?= ucfirst(str_replace('_', ' ', $attempt['status'])) ?>
                                </span>
                            </p>
                            <p><strong>Result:</strong> 
                                <span class="badge bg-<?= $is_passed ? 'success' : 'danger' ?>">
                                    <?= $is_passed ? 'Passed' : 'Failed' ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Score: <?= $attempt['score'] ?? 0 ?>/<?= $total_questions ?></span>
                            <span><?= $score_percentage ?>% (Passing: <?= $passing_percentage ?>%)</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-<?= $is_passed ? 'success' : 'danger' ?>" 
                                 role="progressbar" 
                                 style="width: <?= $score_percentage ?>%" 
                                 aria-valuenow="<?= $score_percentage ?>" 
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
                                    <th style="width: 5%">#</th>
                                    <th style="width: 45%">Question</th>
                                    <th style="width: 20%">Correct Answer</th>
                                    <th style="width: 20%">Student's Answer</th>
                                    <th style="width: 10%">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($questions && $questions->num_rows > 0): ?>
                                    <?php $i = 1; while ($question = $questions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($question['question_text']) ?></td>
                                        <td><?= htmlspecialchars($question['correct_answer']) ?></td>
                                        <td><?= htmlspecialchars($question['student_answer']) ?></td>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>