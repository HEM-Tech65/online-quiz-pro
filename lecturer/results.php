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

// Handle export request
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    
    // Re-run the query to get all results (without pagination)
    $export_results = $db->query("SELECT qa.*, q.title as quiz_title, 
                                u.first_name, u.last_name, u.email
                                FROM quiz_attempts qa
                                JOIN quizzes q ON qa.quiz_id = q.id
                                JOIN users u ON qa.user_id = u.id
                                $where
                                ORDER BY qa.completed_at DESC");
    
    if ($export_results->num_rows > 0) {
        // Set headers based on export type
        if ($export_type == 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=quiz_results_' . date('Y-m-d') . '.csv');
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, array(
                'Student Name', 
                'Email', 
                'Quiz Title', 
                'Status', 
                'Score (%)', 
                'Date Completed'
            ));
            
            // Add data rows
            while ($row = $export_results->fetch_assoc()) {
                fputcsv($output, array(
                    $row['first_name'] . ' ' . $row['last_name'],
                    $row['email'],
                    $row['quiz_title'],
                    ucfirst(str_replace('_', ' ', $row['status'])),
                    $row['status'] == 'completed' ? $row['score'] : 'N/A',
                    $row['completed_at'] ? date('Y-m-d H:i:s', strtotime($row['completed_at'])) : 'N/A'
                ));
            }
            
            fclose($output);
            exit();
        } elseif ($export_type == 'excel') {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename=quiz_results_' . date('Y-m-d') . '.xls');
            
            echo "<table border='1'>";
            echo "<tr>";
            echo "<th>Student Name</th>";
            echo "<th>Email</th>";
            echo "<th>Quiz Title</th>";
            echo "<th>Status</th>";
            echo "<th>Score (%)</th>";
            echo "<th>Date Completed</th>";
            echo "</tr>";
            
            while ($row = $export_results->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['first_name'] . ' ' . $row['last_name'] . "</td>";
                echo "<td>" . $row['email'] . "</td>";
                echo "<td>" . $row['quiz_title'] . "</td>";
                echo "<td>" . ucfirst(str_replace('_', ' ', $row['status'])) . "</td>";
                echo "<td>" . ($row['status'] == 'completed' ? $row['score'] : 'N/A') . "</td>";
                echo "<td>" . ($row['completed_at'] ? date('Y-m-d H:i:s', strtotime($row['completed_at'])) : 'N/A') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            exit();
        }
    } else {
        $_SESSION['error'] = "No data available to export";
        header("Location: results.php?" . http_build_query($_GET));
        exit();
    }
}


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
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            Export <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="?export=csv&<?php echo http_build_query($_GET); ?>">CSV Format</a></li>
                            <li><a class="dropdown-item" href="?export=excel&<?php echo http_build_query($_GET); ?>">Excel Format</a></li>
                        </ul>
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