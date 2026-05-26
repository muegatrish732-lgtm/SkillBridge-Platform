<?php
require_once '../db.php';

// Auth & Admin check
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$admin_check = $conn->query("SELECT is_admin FROM users WHERE id = $user_id")->fetch_assoc();
if (!$admin_check || $admin_check['is_admin'] != 1) { header("Location: ../index.php"); exit(); }

$message = '';

// Handle Course Addition (FIXED: Removed 'price' from query)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $category = $conn->real_escape_string($_POST['category']);
    $lessons = intval($_POST['lessons']);
    
    // Removed price from the INSERT statement since modules are now free
    $sql = "INSERT INTO courses (title, category, lessons) VALUES ('$title', '$category', $lessons)";
    if ($conn->query($sql)) {
        $message = "<div class='alert alert-success'>Course added successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error adding course.</div>";
    }
}

// Handle Course Deletion
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM courses WHERE id = $del_id")) {
        $message = "<div class='alert alert-warning'>Course deleted successfully.</div>";
    }
}

// Handle Search Engine
$search_query = "";
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_term = $conn->real_escape_string(trim($_GET['search']));
    $search_query = " WHERE title LIKE '%$search_term%' OR category LIKE '%$search_term%' ";
}

// Fetch courses
$courses = $conn->query("SELECT * FROM courses" . $search_query . " ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Manage Courses | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary-purple: #6c5ce7; --dark-bg: #1c144d; --light-bg: #f4f7fe; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .header { background: white; z-index: 1000; padding: 15px 0; }
        .log_out { background: #e74c3c; color: white !important; border-radius: 5px; padding: 8px 20px !important; margin-left: 15px; }

        .banner-sub { background-color: var(--dark-bg); padding: 40px 0; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; margin-bottom: 40px; }
        .table-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 20px rgba(0,0,0,0.02); }
        .btn-purple { background-color: var(--primary-purple); color: white; border: none; }
        .btn-purple:hover { background-color: #5b4bc4; color: white; }
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
                    <li class="nav-item active"><a class="nav-link text-primary font-weight-bold " href="admin-courses.php">Manage Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-assessments.php">Manage Assessments</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-users.php">Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-certificates.php">Certificates</a></li>
                    <li class="nav-item"><a class="nav-link log_out" href="admin-dashboard.php?action=logout">Log Out <i class="fa-solid fa-sign-out-alt ms-1"></i></a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>
<section class="banner-sub text-center"><h2 class="text-white font-weight-bold">Course Management</h2></section>
<div class="container pb-5">
    <?php echo $message; ?>
    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h5 class="m-0 font-weight-bold">Active Modules</h5>
            
            <div class="d-flex align-items-center flex-wrap mt-2 mt-md-0">
                <!-- NEW SEARCH ENGINE FORM -->
                <form method="GET" action="admin-courses.php" class="d-flex mb-0 me-3">
                    <div class="input-group input-group-sm" style="min-width: 250px;">
                        <input type="text" name="search" class="form-control" placeholder="Search by title or category..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-outline-secondary text-dark" title="Search"><i class="fa fa-search"></i></button>
                        <?php if(isset($_GET['search']) && trim($_GET['search']) !== ''): ?>
                            <!-- Clear search button -->
                            <a href="admin-courses.php" class="btn btn-outline-danger" title="Clear Search"><i class="fa fa-times"></i></a>
                        <?php endif; ?>
                    </div>
                </form>

                <button class="btn btn-purple btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fa fa-plus me-1"></i> Add Course</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Title</th><th>Category</th><th>Lessons</th><th>Resources</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    <?php if($courses->num_rows > 0): ?>
                        <?php while($c = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['title']); ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($c['category']); ?></span></td>
                                <td><?php echo $c['lessons']; ?></td>
                                <td>
                                    <a href="manage-resources.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-file-upload"></i> Manage Files
                                    </a>
                                </td>
                                <td class="text-end">
                                    <a href="admin-courses.php?delete=<?php echo $c['id']; ?>" class="text-danger ms-3" onclick="return confirm('Delete course?')"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <!-- Empty state if search finds nothing -->
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fa fa-search fa-2x mb-2 opacity-50"></i><br>
                                No courses found matching your criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h5>Create New Course</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Course Title</label><input type="text" name="title" class="form-control" required></div>
                <div class="mb-3"><label>Category</label><input type="text" name="category" class="form-control" placeholder="e.g. Development" required></div>
                <div class="mb-3"><label>Total Lessons</label><input type="number" name="lessons" class="form-control" required></div>
            </div>
            <div class="modal-footer"><button type="submit" name="add_course" class="btn btn-purple">Save Course</button></div>
        </form>
    </div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>