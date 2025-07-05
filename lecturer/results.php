<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isLecturer()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$page_title = "Quiz Results";
$lecturer_id = $_SESSION['user_id'];

// Get filter parameters
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

// Build filter conditions
$where = "WHERE q.created_by = $lecturer_id";
if ($quiz_id > 0) {
    $where .= " AND qa.quiz_id = $quiz_id";
}
if ($student_id > 0) {
    $where .= " AND qa.user_id = $student_id";
}

// Get results
$results = $db->query("SELECT qa.*, q.title as quiz_title, u.first_name, u.last_name, u.email
                      FROM quiz_attempts qa
                      JOIN quizzes q ON qa.quiz_id = q.id
                      JOIN users u ON qa.user_id = u.id
                      $where
                      ORDER BY qa.completed_at DESC");

// Get available quizzes for filter
$quizzes = $db->query("SELECT id, title FROM quizzes WHERE created_by = $lecturer_id ORDER BY title");

// Get students who have taken quizzes
$students = $db->query("SELECT DISTINCT u.id, u.first_name, u.last_name
                       FROM quiz_attempts qa
                       JOIN users u ON qa.user_id = u.id
                       JOIN quizzes q ON qa.quiz_id = q.id
                       WHERE q.created_by = $lecturer_id
                       ORDER BY u.first_name");

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../lecturer/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Quiz Results</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
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
                            <label for="student_id" class="form-label">Student</label>
                            <select class="form-select" id="student_id" name="student_id">
                                <option value="0">All Students</option>
                                <?php while ($student = $students->fetch_assoc()): ?>
                                    <option value="<?php echo $student['id']; ?>" 
                                        <?php echo $student_id == $student['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Apply</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Quiz</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($results->num_rows > 0): ?>
                                    <?php while ($result = $results->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($result['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($result['quiz_title']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $result['status'] == 'completed' ? 'success' : 
                                                ($result['status'] == 'in_progress' ? 'warning' : 'secondary'); 
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $result['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($result['status'] == 'completed' && isset($result['score'])): ?>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" 
                                                         role="progressbar" 
                                                         style="width: <?php echo $result['score']; ?>%" 
                                                         aria-valuenow="<?php echo $result['score']; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <?php echo $result['score']; ?>%
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($result['completed_at']): ?>
                                                <?php echo date('M j, Y g:i A', strtotime($result['completed_at'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">In progress</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="result-details.php?attempt_id=<?php echo $result['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No results found</p>
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

<?php
include __DIR__ . '/../includes/footer.php';
?>