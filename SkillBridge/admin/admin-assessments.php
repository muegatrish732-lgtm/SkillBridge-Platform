<?php
require_once '../db.php';

// Auth & Admin check
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$admin_check = $conn->query("SELECT is_admin FROM users WHERE id = $user_id")->fetch_assoc();
if (!$admin_check || $admin_check['is_admin'] != 1) { header("Location: ../index.php"); exit(); }

$message = '';

// Handle Assessment Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_assessment'])) {
    $course_id = intval($_POST['course_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $total_questions = intval($_POST['total_questions']);
    
    $sql = "INSERT INTO assessments (course_id, title, total_questions) VALUES ($course_id, '$title', $total_questions)";
    if ($conn->query($sql)) {
        $message = "<div class='alert alert-success'>Assessment created successfully!</div>";
    }
}

// Handle Assessment Editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_assessment'])) {
    $assessment_id = intval($_POST['assessment_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $total_questions = intval($_POST['total_questions']);
    
    $sql = "UPDATE assessments SET title='$title', total_questions=$total_questions WHERE id=$assessment_id";
    if ($conn->query($sql)) {
        $message = "<div class='alert alert-success'>Assessment updated successfully!</div>";
    }
}

// Handle Assessment Deletion
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM assessments WHERE id = $del_id")) {
        $message = "<div class='alert alert-warning'>Assessment deleted successfully.</div>";
    }
}

// Handle Search
$search_query = "";
$search_condition = "";
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = $conn->real_escape_string(trim($_GET['search']));
    $search_condition = " WHERE a.title LIKE '%$search_query%' OR c.title LIKE '%$search_query%' ";
}

// Fetch all assessments joined with course details
$assessments_sql = "
    SELECT a.*, c.title AS course_name,
    (SELECT COUNT(id) FROM assessment_questions WHERE assessment_id = a.id) as current_q_count
    FROM assessments a 
    JOIN courses c ON a.course_id = c.id 
    $search_condition
    ORDER BY a.created_at DESC
";
$assessments = $conn->query($assessments_sql);
$courses_dropdown = $conn->query("SELECT id, title FROM courses ORDER BY title ASC");
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Manage Assessments | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root { --primary-purple: #6c5ce7; --dark-bg: #1c144d; --light-bg: #f4f7fe; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .header { background: white; padding: 15px 0; }
        .log_out { background: #e74c3c; color: white !important; border-radius: 5px; padding: 8px 20px !important; margin-left: 15px; }

        .banner-sub { background-color: var(--dark-bg); padding: 40px 0; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; margin-bottom: 40px; }
        .table-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 20px rgba(0,0,0,0.02); }
    </style>
</head>
<body>

<header class="header sticky-top shadow-sm">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light p-0">
            <a class="navbar-brand font-weight-bold" href="admin-dashboard.php" style="color:var(--primary-purple); font-size: 24px;">SkillBridge <span class="text-danger small">ADMIN</span></a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="admin-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-courses.php">Manage Courses</a></li>
                    <li class="nav-item active"><a class="nav-link text-primary font-weight-bold" href="admin-assessments.php">Manage Assessments</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-users.php">Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-certificates.php">Certificates</a></li>
                    <li class="nav-item"><a class="nav-link log_out" href="admin-dashboard.php?action=logout">Log Out <i class="fa-solid fa-sign-out-alt ms-1"></i></a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>

<section class="banner-sub text-center"><h2 class="text-white font-weight-bold">Assessment Management</h2></section>

<div class="container pb-5">
    <?php echo $message; ?>
    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="font-weight-bold m-0" style="color: var(--primary-purple);">Configured Quizzes</h5>
            
            <!-- Search Form -->
            <form method="GET" action="admin-assessments.php" class="d-flex w-50 mx-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by Assessment or Course title..." value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-search"></i> Search</button>
                    <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                        <a href="admin-assessments.php" class="btn btn-outline-danger" title="Clear Search"><i class="fa-solid fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>

            <button class="btn btn-primary" style="background-color: var(--primary-purple);" data-bs-toggle="modal" data-bs-target="#addQuizModal">
                <i class="fa-solid fa-plus me-1"></i> Add Assessment Container
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Quiz Title</th><th>Linked Course</th><th>Questions Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    <?php if($assessments->num_rows > 0): ?>
                        <?php while($a = $assessments->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($a['title']); ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($a['course_name']); ?></span></td>
                                <td>
                                    <span class="badge <?php echo ($a['current_q_count'] >= $a['total_questions']) ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                        <?php echo $a['current_q_count']; ?> / <?php echo $a['total_questions']; ?> Added
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="admin-manage-questions.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-info text-white me-1"><i class="fa-solid fa-list-check"></i> Manage Questions</a>
                                    <button class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editQuizModal" onclick="setEditData(<?php echo $a['id']; ?>, '<?php echo addslashes($a['title']); ?>', <?php echo $a['total_questions']; ?>)"><i class="fa-solid fa-edit"></i> Edit</button>
                                    <a href="admin-assessments.php?delete=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this assessment entirely?');"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">
                            <?php echo (isset($_GET['search']) && !empty($_GET['search'])) ? 'No assessments found matching your search.' : 'No assessments found.'; ?>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addQuizModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h5 class="modal-title font-weight-bold">Create New Assessment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Link to Course</label>
                    <select name="course_id" class="form-select" required>
                        <?php while($course = $courses_dropdown->fetch_assoc()): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Assessment Title</label><input type="text" name="title" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Total Questions Target</label><input type="number" name="total_questions" class="form-control" value="10" required></div>
            </div>
            <div class="modal-footer"><button type="submit" name="add_assessment" class="btn btn-primary">Save Assessment</button></div>
        </form>
    </div></div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editQuizModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h5 class="modal-title font-weight-bold">Edit Assessment Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="assessment_id" id="edit_id">
                <div class="mb-3"><label class="form-label">Assessment Title</label><input type="text" name="title" id="edit_title" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Total Questions Target</label><input type="number" name="total_questions" id="edit_total" class="form-control" required></div>
            </div>
            <div class="modal-footer"><button type="submit" name="edit_assessment" class="btn btn-primary">Update Details</button></div>
        </form>
    </div></div>
</div>

<script>
    function setEditData(id, title, total) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_title').value = title;
        document.getElementById('edit_total').value = total;
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>