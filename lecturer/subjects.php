<?php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!$auth->isLoggedIn() || !$auth->isLecturer()) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$lecturer_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle add subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);
    if ($name !== '' && $code !== '') {
        $name = $db->escapeString($name);
        $code = $db->escapeString($code);
        $db->query("INSERT INTO subjects (name, code, created_by) VALUES ('$name', '$code', $lecturer_id)");
        $success = "Subject added!";
    } else {
        $error = "Subject name and code cannot be empty.";
    }
}

// Handle delete subject
if (isset($_GET['delete'])) {
    $subject_id = (int)$_GET['delete'];
    // Check if subject is used in quizzes
    $quiz_check = $db->query("SELECT COUNT(*) as cnt FROM quizzes WHERE subject_id = $subject_id");
    $row = $quiz_check->fetch_assoc();
    if ($row['cnt'] > 0) {
        $error = "Cannot delete: This subject is assigned to one or more quizzes.";
    } else {
        $db->query("DELETE FROM subjects WHERE id = $subject_id AND created_by = $lecturer_id");
        $success = "Subject deleted!";
    }
}

// Get subjects for this lecturer
$subjects = $db->query("SELECT * FROM subjects WHERE created_by = $lecturer_id ORDER BY name");

include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Subjects</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Add New Subject</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="name" placeholder="Subject name" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="code" placeholder="Subject code" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="add_subject" class="btn btn-primary w-100">Add Subject</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Your Subjects</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($subjects->num_rows > 0): ?>
                                <?php while ($subject = $subjects->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['name']); ?></td>
                                        <td>
                                            <!-- For simplicity, only delete is provided. Add edit as needed. -->
                                            <a href="?delete=<?php echo $subject['id']; ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirm('Delete this subject?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No subjects found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>