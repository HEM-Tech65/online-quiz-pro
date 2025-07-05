<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isLecturer()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$page_title = "Manage Quizzes";
$lecturer_id = $_SESSION['user_id'];

// Handle quiz actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'delete':
            if (isset($_GET['id'])) {
                $quiz_id = (int)$_GET['id'];
                // Verify lecturer owns this quiz
                $check = $db->query("SELECT id FROM quizzes WHERE id = $quiz_id AND created_by = $lecturer_id");
                if ($check->num_rows > 0) {
                    $db->query("DELETE FROM quizzes WHERE id = $quiz_id");
                    $_SESSION['success_message'] = "Quiz deleted successfully";
                } else {
                    $_SESSION['error_message'] = "Quiz not found or access denied";
                }
                header("Location: manage.php");
                exit();
            }
            break;
        case 'publish':
            if (isset($_GET['id'])) {
                $quiz_id = (int)$_GET['id'];
                // Verify lecturer owns this quiz
                $check = $db->query("SELECT id FROM quizzes WHERE id = $quiz_id AND created_by = $lecturer_id");
                if ($check->num_rows > 0) {
                    $db->query("UPDATE quizzes SET is_published = 1 WHERE id = $quiz_id");
                    $_SESSION['success_message'] = "Quiz published successfully";
                } else {
                    $_SESSION['error_message'] = "Quiz not found or access denied";
                }
                header("Location: manage.php");
                exit();
            }
            break;
        case 'unpublish':
            if (isset($_GET['id'])) {
                $quiz_id = (int)$_GET['id'];
                // Verify lecturer owns this quiz
                $check = $db->query("SELECT id FROM quizzes WHERE id = $quiz_id AND created_by = $lecturer_id");
                if ($check->num_rows > 0) {
                    $db->query("UPDATE quizzes SET is_published = 0 WHERE id = $quiz_id");
                    $_SESSION['success_message'] = "Quiz unpublished successfully";
                } else {
                    $_SESSION['error_message'] = "Quiz not found or access denied";
                }
                header("Location: manage.php");
                exit();
            }
            break;
    }
}

// Get quizzes based on filter
$filter = $_GET['filter'] ?? 'all';
$where = "WHERE q.created_by = $lecturer_id";

if ($filter === 'active') {
    $where .= " AND q.is_published = 1";
} elseif ($filter === 'draft') {
    $where .= " AND q.is_published = 0";
}

$quizzes = $db->query("SELECT q.*, s.name as subject_name, 
                      (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count,
                      (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id) as attempt_count
                      FROM quizzes q
                      JOIN subjects s ON q.subject_id = s.id
                      $where
                      ORDER BY q.created_at DESC");

include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Quizzes</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> New Quiz
                    </a>
                </div>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                       href="?filter=all">All Quizzes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'active' ? 'active' : ''; ?>" 
                       href="?filter=active">Active</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $filter === 'draft' ? 'active' : ''; ?>" 
                       href="?filter=draft">Drafts</a>
                </li>
            </ul>

            <!-- Quizzes Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Questions</th>
                            <th>Attempts</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($quizzes->num_rows > 0): ?>
                            <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <a href="questions.php?quiz_id=<?php echo $quiz['id']; ?>">
                                        <?php echo htmlspecialchars($quiz['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($quiz['subject_name']); ?></td>
                                <td><?php echo $quiz['question_count']; ?></td>
                                <td><?php echo $quiz['attempt_count']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $quiz['is_published'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $quiz['is_published'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="create.php?action=edit&id=<?php echo $quiz['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($quiz['is_published']): ?>
                                            <a href="?action=unpublish&id=<?php echo $quiz['id']; ?>" 
                                               class="btn btn-sm btn-outline-warning" title="Unpublish">
                                                <i class="fas fa-eye-slash"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=publish&id=<?php echo $quiz['id']; ?>" 
                                               class="btn btn-sm btn-outline-success" title="Publish">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?php echo $quiz['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger confirm-action" 
                                           title="Delete"
                                           data-confirm-message="Are you sure you want to delete this quiz?">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No quizzes found</p>
                                    <a href="create.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Create Your First Quiz
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php
include '../../includes/footer.php';
?>