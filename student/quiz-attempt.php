<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isStudent()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student_id = $_SESSION['user_id'];

// Check if quiz exists and is available
$quiz = $db->query("SELECT q.*, s.name as subject_name 
                   FROM quizzes q
                   JOIN subjects s ON q.subject_id = s.id
                   WHERE q.id = $quiz_id AND q.is_published = 1
                   AND (q.start_date IS NULL OR q.start_date <= NOW())
                   AND (q.end_date IS NULL OR q.end_date >= NOW())")->fetch_assoc();

if (!$quiz) {
    $_SESSION['error_message'] = "Quiz not available";
    header("Location: quizzes.php");
    exit();
}

// Check if student has attempts left
$attempts = $db->query("SELECT COUNT(*) as count FROM quiz_attempts 
                       WHERE user_id = $student_id AND quiz_id = $quiz_id")->fetch_assoc()['count'];

if ($attempts >= $quiz['max_attempts']) {
    $_SESSION['error_message'] = "You have exhausted your attempts for this quiz";
    header("Location: quizzes.php");
    exit();
}

// Start or continue attempt
$attempt = $db->query("SELECT * FROM quiz_attempts 
                      WHERE user_id = $student_id AND quiz_id = $quiz_id
                      AND status = 'in_progress'")->fetch_assoc();

if (!$attempt) {
    $db->query("INSERT INTO quiz_attempts (user_id, quiz_id, status) 
               VALUES ($student_id, $quiz_id, 'in_progress')");
    $attempt_id = $db->getLastInsertId();
} else {
    $attempt_id = $attempt['id'];
}

// Get quiz questions
$questions = $db->query("SELECT * FROM questions WHERE quiz_id = $quiz_id ORDER BY RAND()");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process answers and calculate score
    $score = 0;
    $total_questions = $questions->num_rows;
    
    // Reset pointer for questions
    $questions->data_seek(0);
    
    while ($question = $questions->fetch_assoc()) {
        $question_id = $question['id'];
        $is_correct = false;
        $marks_obtained = 0;
        
        if ($question['type'] == 'mcq' || $question['type'] == 'true_false') {
            $selected_option = $_POST['answers'][$question_id] ?? null;
            
            if ($selected_option) {
                $correct_option = $db->query("SELECT id FROM options 
                                            WHERE question_id = $question_id 
                                            AND is_correct = 1")->fetch_assoc();
                
                $is_correct = ($selected_option == $correct_option['id']);
                $marks_obtained = $is_correct ? $question['marks'] : 0;
                $score += $marks_obtained;
            }
        }
        
        // For short answer questions, manual grading would be needed
        // This is a simplified version that gives partial marks
        elseif ($question['type'] == 'short_answer') {
            $answer_text = $_POST['answers'][$question_id] ?? '';
            $marks_obtained = !empty($answer_text) ? ($question['marks'] * 0.5) : 0;
            $score += $marks_obtained;
            $is_correct = false; // Needs manual grading
        }
        
        // Save answer
        $answer_data = [
            'attempt_id' => $attempt_id,
            'question_id' => $question_id,
            'is_correct' => $is_correct,
            'marks_obtained' => $marks_obtained
        ];
        
        if ($question['type'] == 'mcq' || $question['type'] == 'true_false') {
            $answer_data['option_id'] = null; // Not used
            $answer_data['answer_text'] = $_POST['answers'][$question_id] ?? '';
        } else {
            $answer_data['option_id'] = null;
            $answer_data['answer_text'] = $_POST['answers'][$question_id] ?? '';
        }

        // Insert answer into database
        $db->query("INSERT INTO answers (attempt_id, question_id, option_id, answer_text, is_correct, marks_obtained)
                   VALUES ({$answer_data['attempt_id']}, {$answer_data['question_id']}, 
                           NULL, 
                           '" . $db->escapeString($answer_data['answer_text'] ?? '') . "',
                           {$answer_data['is_correct']}, 
                           {$answer_data['marks_obtained']})");
    }
    
    // Calculate percentage score
    $total_marks = $db->query("SELECT SUM(marks) as total FROM questions WHERE quiz_id = $quiz_id")->fetch_assoc()['total'];
    $percentage_score = round(($score / $total_marks) * 100, 2);
    
    // Update attempt
    $db->query("UPDATE quiz_attempts 
               SET status = 'completed', 
                   score = $percentage_score, 
                   completed_at = NOW() 
               WHERE id = $attempt_id");
    
    $_SESSION['quiz_score'] = $percentage_score;
    header("Location: quiz-result.php?attempt_id=$attempt_id");
    exit();
}

include '../includes/header.php';
?>

<div class="container quiz-container">
    <div class="quiz-header bg-light p-3 mb-4 rounded">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                <p class="mb-0 text-muted">Subject: <?php echo htmlspecialchars($quiz['subject_name']); ?></p>
            </div>
            <div class="text-end">
                <div class="quiz-timer bg-danger text-white p-2 rounded">
                    <i class="fas fa-clock me-1"></i>
                    <span id="time-remaining"><?php echo $quiz['time_limit']; ?>:00</span>
                </div>
            </div>
        </div>
    </div>

    <form id="quiz-form" method="POST">
        <input type="hidden" name="attempt_id" value="<?php echo $attempt_id; ?>">
        
        <div class="question-navigation mb-4">
            <div class="d-flex flex-wrap gap-2">
                <?php $q_num = 1; while ($question = $questions->fetch_assoc()): ?>
                    <a href="#question-<?php echo $question['id']; ?>" 
                       class="btn btn-sm btn-outline-primary question-nav-btn"
                       data-question-id="<?php echo $question['id']; ?>">
                        <?php echo $q_num++; ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="quiz-questions">
            <?php 
            $questions->data_seek(0); // Reset pointer
            $q_num = 1; 
            while ($question = $questions->fetch_assoc()): 
                $options = $db->query("SELECT * FROM options WHERE question_id = {$question['id']}");
            ?>
                <div class="card mb-4 question-card" id="question-<?php echo $question['id']; ?>">
                    <div class="card-body">
                        <h5 class="card-title">Question #<?php echo $q_num++; ?></h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($question['text'])); ?></p>
                        
                        <?php if ($question['type'] == 'mcq'): ?>
                            <div class="options-list">
                                <?php
                                $option_labels = ['A', 'B', 'C', 'D'];
                                foreach ($option_labels as $label):
                                    $option_text = $question['option_' . strtolower($label)];
                                    if ($option_text):
                                ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio"
                                               name="answers[<?php echo $question['id']; ?>]"
                                               id="option-<?php echo $question['id'] . '-' . $label; ?>"
                                               value="<?php echo $label; ?>">
                                        <label class="form-check-label" for="option-<?php echo $question['id'] . '-' . $label; ?>">
                                            <?php echo htmlspecialchars($option_text); ?>
                                        </label>
                                    </div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        <?php elseif ($question['type'] == 'true_false'): ?>
                            <div class="options-list">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" 
                                           name="answers[<?php echo $question['id']; ?>]" 
                                           id="question-<?php echo $question['id']; ?>-true"
                                           value="true">
                                    <label class="form-check-label" for="question-<?php echo $question['id']; ?>-true">
                                        True
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="answers[<?php echo $question['id']; ?>]" 
                                           id="question-<?php echo $question['id']; ?>-false"
                                           value="false">
                                    <label class="form-check-label" for="question-<?php echo $question['id']; ?>-false">
                                        False
                                    </label>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="form-group">
                                <textarea class="form-control" 
                                          name="answers[<?php echo $question['id']; ?>]" 
                                          rows="3"></textarea>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="quiz-submit text-center py-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-paper-plane me-2"></i> Submit Quiz
            </button>
            <p class="text-muted mt-2">You can't change answers after submission</p>
        </div>
    </form>
</div>

<script>
// Timer functionality
const timeLimit = <?php echo $quiz['time_limit'] * 60; ?>;
let timeRemaining = timeLimit;

function updateTimer() {
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    document.getElementById('time-remaining').textContent = 
        `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    
    if (timeRemaining <= 0) {
        document.getElementById('quiz-form').submit();
    } else {
        timeRemaining--;
        setTimeout(updateTimer, 1000);
    }
}

// Start timer
updateTimer();

// Question navigation
document.querySelectorAll('.question-nav-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const questionId = this.getAttribute('data-question-id');
        document.querySelector(`#question-${questionId}`).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Prevent accidental navigation
window.addEventListener('beforeunload', function(e) {
    if (timeRemaining > 0) {
        e.preventDefault();
        e.returnValue = 'You have an ongoing quiz. Are you sure you want to leave?';
    }
});
</script>

<?php
include '../includes/footer.php';
?>