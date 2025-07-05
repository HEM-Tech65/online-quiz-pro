<?php
// lecturer/quizzes/questions.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isLecturer()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// Fetch quiz info
$quiz = $db->query("SELECT * FROM quizzes WHERE id = $quiz_id AND created_by = $lecturer_id")->fetch_assoc();
if (!$quiz) {
    $_SESSION['error_message'] = "Quiz not found or access denied.";
    header("Location: manage.php");
    exit();
}

// Handle add question
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $type = $_POST['type'];
    $text = trim($_POST['question']);
    $marks = (int)$_POST['points'];

    if ($type === 'mcq') {
        $option_a = trim($_POST['option_a']);
        $option_b = trim($_POST['option_b']);
        $option_c = trim($_POST['option_c']);
        $option_d = trim($_POST['option_d']);
        $correct = $_POST['correct'];
        if ($text && $option_a && $option_b && $option_c && $option_d && in_array($correct, ['A','B','C','D'])) {
            $text = $db->escapeString($text);
            $option_a = $db->escapeString($option_a);
            $option_b = $db->escapeString($option_b);
            $option_c = $db->escapeString($option_c);
            $option_d = $db->escapeString($option_d);
            $correct = $db->escapeString($correct);
            $db->query("INSERT INTO questions (quiz_id, text, type, marks, option_a, option_b, option_c, option_d, correct_option)
                        VALUES ($quiz_id, '$text', 'mcq', $marks, '$option_a', '$option_b', '$option_c', '$option_d', '$correct')");
            $success = "MCQ question added!";
        } else {
            $error = "Please fill all MCQ fields and select the correct answer.";
        }
    } elseif ($type === 'short_answer') {
        $answer = trim($_POST['short_answer']);
        if ($text && $answer) {
            $text = $db->escapeString($text);
            $answer = $db->escapeString($answer);
            $db->query("INSERT INTO questions (quiz_id, text, type, marks, correct_option)
                        VALUES ($quiz_id, '$text', 'short_answer', $marks, '$answer')");
            $success = "Short answer question added!";
        } else {
            $error = "Please fill the question and answer fields.";
        }
    }
}

// Handle delete question
if (isset($_GET['delete'])) {
    $qid = (int)$_GET['delete'];
    $db->query("DELETE FROM questions WHERE id = $qid AND quiz_id = $quiz_id");
    $success = "Question deleted!";
}

// Fetch questions
$questions = $db->query("SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY id");

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Questions for: <?php echo htmlspecialchars($quiz['title']); ?></h1>
                <a href="manage.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Quizzes
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Add New Question</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="row g-3" id="questionForm">
                        <div class="col-md-4">
                            <label class="form-label">Question Type</label>
                            <select class="form-select" name="type" id="typeSelect" required onchange="toggleQuestionType()">
                                <option value="mcq">Multiple Choice</option>
                                <option value="short_answer">Short Answer</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Question</label>
                            <textarea class="form-control" name="question" required></textarea>
                        </div>
                        <!-- MCQ Options -->
                        <div class="mcq-fields row">
                            <div class="col-md-6">
                                <label class="form-label">Option A</label>
                                <input type="text" class="form-control" name="option_a">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Option B</label>
                                <input type="text" class="form-control" name="option_b">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Option C</label>
                                <input type="text" class="form-control" name="option_c">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Option D</label>
                                <input type="text" class="form-control" name="option_d">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Correct Option</label>
                                <select class="form-select" name="correct">
                                    <option value="">Select</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </div>
                        </div>
                        <!-- Short Answer -->
                        <div class="short-answer-fields row" style="display:none;">
                            <div class="col-md-6">
                                <label class="form-label">Correct Answer</label>
                                <input type="text" class="form-control" name="short_answer">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Points</label>
                            <input type="number" class="form-control" name="points" value="1" min="1" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" name="add_question" class="btn btn-primary w-100">Add Question</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Questions List</h5>
                </div>
                <div class="card-body">
                    <?php if ($questions->num_rows > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Question</th>
                                    <th>Type</th>
                                    <th>Correct</th>
                                    <th>Points</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=1; while ($q = $questions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo htmlspecialchars($q['text']); ?></td>
                                        <td><?php echo htmlspecialchars($q['type']); ?></td>
                                        <td>
                                            <?php
                                            if ($q['type'] === 'mcq') {
                                                echo $q['correct_option'];
                                            } else {
                                                echo htmlspecialchars($q['correct_option']);
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $q['marks']; ?></td>
                                        <td>
                                            <a href="?quiz_id=<?php echo $quiz_id; ?>&delete=<?php echo $q['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this question?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="text-muted">No questions added yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function toggleQuestionType() {
    var type = document.getElementById('typeSelect').value;
    document.querySelector('.mcq-fields').style.display = (type === 'mcq') ? 'flex' : 'none';
    document.querySelector('.short-answer-fields').style.display = (type === 'short_answer') ? 'flex' : 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    toggleQuestionType();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>