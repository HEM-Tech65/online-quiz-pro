<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/online-quiz-pro/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/online-quiz-pro/includes/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/online-quiz-pro/includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isLecturer()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}


$page_title = "Question Bank";
$lecturer_id = $_SESSION['user_id'];

// Handle question actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'delete':
            if (isset($_GET['id'])) {
                $question_id = (int)$_GET['id'];
                // Verify lecturer owns this question
                $check = $db->query("SELECT q.id FROM questions q JOIN quizzes qu ON q.quiz_id = qu.id WHERE q.id = $question_id AND qu.created_by = $lecturer_id");
                if ($check->num_rows > 0) {
                    $db->query("DELETE FROM questions WHERE id = $question_id");
                    $_SESSION['success_message'] = "Question deleted successfully";
                } else {
                    $_SESSION['error_message'] = "Question not found or access denied";
                }
                header("Location: index.php");
                exit();
            }
            break;
    }
}

// Get questions with filters
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$type = $_GET['type'] ?? 'all';

$where = "WHERE q.created_by = $lecturer_id";
if ($quiz_id > 0) {
    $where .= " AND qu.id = $quiz_id";
}
if ($type !== 'all') {
    $where .= " AND qn.type = '$type'";
}

$questions = $db->query("SELECT qn.*, qu.title as quiz_title, qu.id as quiz_id
                        FROM questions qn
                        JOIN quizzes qu ON qn.quiz_id = qu.id
                        WHERE qu.created_by = $lecturer_id
                        " . ($quiz_id > 0 ? " AND qu.id = $quiz_id" : "") . "
                        " . ($type !== 'all' ? " AND qn.type = '$type'" : "") . "
                        ORDER BY qn.created_at DESC");
                        
// Get available quizzes for filter
$quizzes = $db->query("SELECT id, title FROM quizzes WHERE created_by = $lecturer_id ORDER BY title");

include($_SERVER['DOCUMENT_ROOT'] . '/online-quiz-pro/includes/header.php');
?>

<div class="container-fluid">
    <div class="row">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/online-quiz-pro/lecturer/sidebar.php'); ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Question Bank</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="../quizzes/create.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> New Quiz
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <label for="quiz_id" class="form-label">Quiz</label>
                            <select class="form-select" id="quiz_id" name="quiz_id">
                                <option value="0">All Quizzes</option>
                                <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                                    <option value="<?php echo $quiz['id']; ?>" 
                                        <?php echo $quiz_id == $quiz['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($quiz['title']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="type" class="form-label">Question Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="all">All Types</option>
                                <option value="mcq" <?php echo $type === 'mcq' ? 'selected' : ''; ?>>Multiple Choice</option>
                                <option value="true_false" <?php echo $type === 'true_false' ? 'selected' : ''; ?>>True/False</option>
                                <option value="short_answer" <?php echo $type === 'short_answer' ? 'selected' : ''; ?>>Short Answer</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Apply</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Questions List -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if ($questions->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($question = $questions->fetch_assoc()): ?>
                            <div class="list-group-item border-0 px-0 py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1 me-3">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($question['text']); ?></h6>
                                        <div class="d-flex gap-3 mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-question-circle me-1"></i>
                                                <?php echo ucfirst(str_replace('_', ' ', $question['type'])); ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="fas fa-book me-1"></i>
                                                <?php echo htmlspecialchars($question['quiz_title']); ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="fas fa-star me-1"></i>
                                                <?php echo $question['marks']; ?> marks
                                            </small>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                type="button" 
                                                id="questionActions" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" 
                                                   href="../quizzes/questions.php?quiz_id=<?php echo $question['quiz_id']; ?>&edit=<?php echo $question['id']; ?>">
                                                    <i class="fas fa-edit me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger confirm-action" 
                                                   href="?action=delete&id=<?php echo $question['id']; ?>"
                                                   data-confirm-message="Are you sure you want to delete this question?">
                                                    <i class="fas fa-trash me-2"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No questions found</p>
                            <?php if ($quiz_id > 0): ?>
                                <a href="../quizzes/questions.php?quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add Questions
                                </a>
                            <?php else: ?>
                                <a href="../quizzes/create.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Create a Quiz
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>