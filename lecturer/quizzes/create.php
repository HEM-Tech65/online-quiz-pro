<?php
// Use absolute paths with __DIR__
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isLecturer()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$page_title = "Create Quiz";
$lecturer_id = $_SESSION['user_id'];
$is_edit = false;
$quiz = null;

// Get subjects taught by this lecturer
$subjects = $db->query("SELECT * FROM subjects WHERE created_by = $lecturer_id ORDER BY name");

// Handle edit mode
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'edit' && isset($_GET['id'])) {
        $quiz_id = (int)$_GET['id'];
        $quiz = $db->query("SELECT * FROM quizzes WHERE id = $quiz_id AND created_by = $lecturer_id")->fetch_assoc();
        
        if ($quiz) {
            $is_edit = true;
            $page_title = "Edit Quiz";
        } else {
            $_SESSION['error_message'] = "Quiz not found or access denied";
            header("Location: manage.php");
            exit();
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $db->escapeString($_POST['title']);
    $description = $db->escapeString($_POST['description']);
    $subject_id = (int)$_POST['subject_id'];
    $time_limit = (int)$_POST['time_limit'];
    $passing_score = (int)$_POST['passing_score'];
    $max_attempts = (int)$_POST['max_attempts'];
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    
    if ($is_edit) {
        $quiz_id = (int)$_POST['quiz_id'];
        $sql = "UPDATE quizzes SET 
                title = '$title',
                description = '$description',
                subject_id = $subject_id,
                time_limit = $time_limit,
                passing_score = $passing_score,
                max_attempts = $max_attempts,
                is_published = $is_published
                WHERE id = $quiz_id AND created_by = $lecturer_id";
        
        if ($db->query($sql)) {
            $_SESSION['success_message'] = "Quiz updated successfully!";
            header("Location: questions.php?quiz_id=$quiz_id");
            exit();
        }
    } else {
        $sql = "INSERT INTO quizzes (title, description, subject_id, time_limit, passing_score, max_attempts, is_published, created_by)
                VALUES ('$title', '$description', $subject_id, $time_limit, $passing_score, $max_attempts, $is_published, $lecturer_id)";
        
        if ($db->query($sql)) {
            $quiz_id = $db->getLastInsertId();
            $_SESSION['success_message'] = "Quiz created successfully! Add questions now.";
            header("Location: questions.php?quiz_id=$quiz_id");
            exit();
        }
    }
    
    $_SESSION['error_message'] = "Failed to save quiz. Please try again.";
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $page_title; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="manage.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Quizzes
                    </a>
                </div>
            </div>

            <form method="POST">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                <?php endif; ?>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Quiz Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo $is_edit ? htmlspecialchars($quiz['title']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                echo $is_edit ? htmlspecialchars($quiz['description']) : ''; 
                            ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subject_id" class="form-label">Subject *</label>
                                <select class="form-select" id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    <?php while ($subject = $subjects->fetch_assoc()): ?>
                                        <option value="<?php echo $subject['id']; ?>" 
                                            <?php echo ($is_edit && $quiz['subject_id'] == $subject['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="time_limit" class="form-label">Time Limit (minutes) *</label>
                                <input type="number" class="form-control" id="time_limit" name="time_limit" 
                                       min="1" value="<?php echo $is_edit ? $quiz['time_limit'] : '30'; ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="passing_score" class="form-label">Passing Score (%)</label>
                                <input type="number" class="form-control" id="passing_score" name="passing_score" 
                                       min="0" max="100" value="<?php echo $is_edit ? $quiz['passing_score'] : '50'; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_attempts" class="form-label">Max Attempts</label>
                                <input type="number" class="form-control" id="max_attempts" name="max_attempts" 
                                       min="1" value="<?php echo $is_edit ? $quiz['max_attempts'] : '1'; ?>">
                            </div>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_published" name="is_published" 
                                   <?php echo ($is_edit && $quiz['is_published']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_published">Publish this quiz</label>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i> <?php echo $is_edit ? 'Update' : 'Create'; ?> Quiz
                    </button>
                    <?php if ($is_edit): ?>
                        <a href="questions.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-question-circle me-2"></i> Manage Questions
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </main>
    </div>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>