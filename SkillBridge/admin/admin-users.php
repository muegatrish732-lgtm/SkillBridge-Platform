<?php
// Path to db.php is one level up
require_once '../db.php';

// Auth & Admin check
if (!isset($_SESSION['user_id'])) { 
    header("Location: ../login.php"); 
    exit(); 
}

$current_admin_id = $_SESSION['user_id'];
$userName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin';

$admin_check = $conn->query("SELECT is_admin FROM users WHERE id = $current_admin_id")->fetch_assoc();
if (!$admin_check || $admin_check['is_admin'] != 1) { 
    header("Location: ../index.php"); 
    exit(); 
}

// Fetch Profile Picture for Admin Navbar
$nav_pic = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=6c5ce7&color=fff';
$pic_query = $conn->query("SELECT profile_picture FROM users WHERE id = $current_admin_id");
if ($pic_query && $pic_query->num_rows > 0) {
    $pic_row = $pic_query->fetch_assoc();
    if (!empty($pic_row['profile_picture'])) {
        $nav_pic = '../uploads/' . $pic_row['profile_picture'];
    }
}

$message = '';

// 1. ADD USER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $is_admin = intval($_POST['is_admin']);

    $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check_email->num_rows > 0) {
        $message = "<div class='alert alert-danger'>Error: Email already registered.</div>";
    } else {
        $insert_sql = "INSERT INTO users (full_name, email, password, is_admin) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sssi", $full_name, $email, $password, $is_admin);
        if ($stmt->execute()) { $message = "<div class='alert alert-success'>User added successfully!</div>"; }
    }
}

// 2. UPDATE USER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $target_id = intval($_POST['target_id']);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $is_admin = intval($_POST['is_admin']);
    $age = !empty($_POST['age']) ? intval($_POST['age']) : 'NULL';
    $phone = $conn->real_escape_string($_POST['phone_number']);
    $course_year = $conn->real_escape_string($_POST['course_year']);

    $update_sql = "UPDATE users SET full_name = ?, email = ?, is_admin = ?, age = $age, phone_number = ?, course_year = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssissi", $full_name, $email, $is_admin, $phone, $course_year, $target_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>User profile updated successfully!</div>";
    }
}

// 3. DELETE USER
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    if ($del_id == $current_admin_id) { $message = "<div class='alert alert-danger'>Cannot delete yourself!</div>"; } 
    else {
        if ($conn->query("DELETE FROM users WHERE id = $del_id")) { $message = "<div class='alert alert-warning'>User deleted.</div>"; }
    }
}

// 4. FETCH VIEW/EDIT DATA
$view_user = null;
if (isset($_GET['view_id'])) {
    $v_id = intval($_GET['view_id']);
    $view_user = $conn->query("SELECT * FROM users WHERE id = $v_id")->fetch_assoc();
    $enrollments = $conn->query("SELECT e.*, c.title AS course_title FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.user_id = $v_id ORDER BY e.enrolled_at DESC");
    $scores = $conn->query("SELECT s.*, a.title AS quiz_title, c.title AS course_title, a.total_questions FROM assessment_scores s JOIN assessments a ON s.assessment_id = a.id JOIN courses c ON a.course_id = c.id WHERE s.user_id = $v_id ORDER BY s.taken_at DESC");
    $earned_certs = $conn->query("SELECT cert.*, c.title AS course_title FROM certificates cert JOIN courses c ON cert.course_id = c.id WHERE cert.user_id = $v_id ORDER BY cert.issued_at DESC");
}

$edit_user = null;
if (isset($_GET['edit_id'])) {
    $e_id = intval($_GET['edit_id']);
    $edit_user = $conn->query("SELECT * FROM users WHERE id = $e_id")->fetch_assoc();
}

// Handle Search
$search_query = "";
$search_condition = "";
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = $conn->real_escape_string(trim($_GET['search']));
    $search_condition = " WHERE full_name LIKE '%$search_query%' OR email LIKE '%$search_query%' OR course_year LIKE '%$search_query%' ";
}

$users_query = $conn->query("SELECT * FROM users $search_condition ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Manage Users | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary-purple: #6c5ce7; --dark-bg: #1c144d; --light-bg: #f4f7fe; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .header { background: white; z-index: 1000; padding: 15px 0; }
        .log_out { background: #e74c3c; color: white !important; border-radius: 5px; padding: 8px 20px !important; margin-left: 15px; }
        .banner-sub { background-color: var(--dark-bg); padding: 50px 0; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; margin-bottom: 40px; }
        .table-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 20px rgba(0,0,0,0.02); margin-bottom: 30px; }
        .btn-purple { background-color: var(--primary-purple); color: white; border: none; }
        .btn-purple:hover { background-color: #5b4bc4; color: white; }
        .admin-view-pic { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 3px solid var(--primary-purple); margin-right: 20px; }
        .table-pic { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 2px solid #ddd; margin-right: 10px; }
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
                    <li class="nav-item"><a class="nav-link" href="admin-assessments.php">Manage Assessments</a></li>
                    <li class="nav-item active"><a class="nav-link font-weight-bold text-primary" href="admin-users.php">Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-certificates.php">Certificates</a></li>
                    <li class="nav-item"><a class="nav-link log_out" href="admin-dashboard.php?action=logout">Log Out <i class="fa-solid fa-sign-out-alt ms-1"></i></a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>

<section class="banner-sub text-center"><div class="container"><h2 class="text-white font-weight-bold">User Management & Monitoring</h2></div></section>

<div class="container pb-5">
    <?php echo $message; ?>

    <!-- 1. VIEW USER ACTIVITY -->
    <?php if($view_user): ?>
        <div class="table-card shadow-sm border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
                <div class="d-flex align-items-center">
                    <?php $v_img = !empty($view_user['profile_picture']) ? '../uploads/' . $view_user['profile_picture'] : 'https://ui-avatars.com/api/?name='.urlencode($view_user['full_name']).'&background=6c5ce7&color=fff'; ?>
                    <img src="<?php echo $v_img; ?>" class="admin-view-pic">
                    <div>
                        <h4 class="font-weight-bold mb-1"><?php echo htmlspecialchars($view_user['full_name']); ?></h4>
                        <p class="text-muted mb-0">
                            <i class="fa fa-envelope me-1"></i> <?php echo htmlspecialchars($view_user['email']); ?> | 
                            <i class="fa fa-phone me-1 ms-2"></i> <?php echo !empty($view_user['phone_number']) ? htmlspecialchars($view_user['phone_number']) : 'N/A'; ?> | 
                            <i class="fa fa-book me-1 ms-2"></i> <?php echo !empty($view_user['course_year']) ? htmlspecialchars($view_user['course_year']) : 'N/A'; ?>
                        </p>
                    </div>
                </div>
                <a href="admin-users.php" class="btn btn-outline-secondary btn-sm"><i class="fa fa-times"></i> Close View</a>
            </div>

            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h6 class="font-weight-bold text-primary mb-3"><i class="fa fa-graduation-cap me-2"></i> Enrolled Courses</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th>Course Name</th><th>Progress</th></tr></thead>
                        <tbody>
                            <?php while($e = $enrollments->fetch_assoc()): ?>
                                <tr><td class="small"><?php echo htmlspecialchars($e['course_title']); ?></td><td><span class="badge bg-info text-dark"><?php echo $e['progress']; ?>%</span></td></tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-4 mb-4">
                    <h6 class="font-weight-bold text-success mb-3"><i class="fa fa-clipboard-check me-2"></i> Assessment Scores</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th>Quiz</th><th>Score</th></tr></thead>
                        <tbody>
                            <?php while($s = $scores->fetch_assoc()): ?>
                                <tr><td class="small"><?php echo htmlspecialchars($s['quiz_title']); ?></td><td><strong><?php echo $s['score']; ?>/<?php echo $s['total_questions']; ?></strong></td></tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <!-- ADMIN CERTIFICATES VIEW -->
                <div class="col-lg-4 mb-4">
                    <h6 class="font-weight-bold mb-3" style="color: #f1c40f;"><i class="fa fa-award me-2"></i> Earned Certificates</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th>Module Certified</th><th>Code ID</th></tr></thead>
                        <tbody>
                            <?php if($earned_certs->num_rows > 0): ?>
                                <?php while($cert = $earned_certs->fetch_assoc()): ?>
                                    <tr>
                                        <td class="small"><?php echo htmlspecialchars($cert['course_title']); ?></td>
                                        <td class="small font-monospace text-muted"><?php echo $cert['certificate_code']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center text-muted small py-3">No certificates earned yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 2. EDIT USER SECTION -->
    <?php if($edit_user): ?>
        <div class="table-card shadow-sm border-start border-4 border-warning">
            <h5 class="font-weight-bold mb-4">Update User Profile</h5>
            <form action="admin-users.php" method="POST" class="row">
                <input type="hidden" name="target_id" value="<?php echo $edit_user['id']; ?>">
                <div class="col-md-4 mb-3"><label class="small font-weight-bold">Full Name</label><input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($edit_user['full_name']); ?>" required></div>
                <div class="col-md-4 mb-3"><label class="small font-weight-bold">Email Address</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required></div>
                <div class="col-md-4 mb-3"><label class="small font-weight-bold">System Role</label>
                    <select name="is_admin" class="form-select">
                        <option value="0" <?php echo $edit_user['is_admin'] == 0 ? 'selected' : ''; ?>>Student</option>
                        <option value="1" <?php echo $edit_user['is_admin'] == 1 ? 'selected' : ''; ?>>Administrator</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3"><label class="small font-weight-bold">Phone Number</label><input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($edit_user['phone_number']); ?>"></div>
                <div class="col-md-2 mb-3"><label class="small font-weight-bold">Age</label><input type="number" name="age" class="form-control" value="<?php echo htmlspecialchars($edit_user['age']); ?>"></div>
                <div class="col-md-4 mb-3"><label class="small font-weight-bold">Course/Year</label><input type="text" name="course_year" class="form-control" value="<?php echo htmlspecialchars($edit_user['course_year']); ?>"></div>
                <div class="col-md-3 mb-3 d-flex align-items-end"><button type="submit" name="update_user" class="btn btn-warning w-100 font-weight-bold">Save Changes</button></div>
            </form>
            <a href="admin-users.php" class="small text-muted text-decoration-none"><i class="fa fa-arrow-left"></i> Cancel Edit</a>
        </div>
    <?php endif; ?>

    <!-- MAIN USERS LIST -->
    <div class="table-card shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="m-0 font-weight-bold" style="color: var(--primary-purple);">Registered Accounts</h5>
            
            <!-- Search Form Added Here -->
            <form method="GET" action="admin-users.php" class="d-flex w-50 mx-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, or course..." value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-search"></i> Search</button>
                    <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                        <a href="admin-users.php" class="btn btn-outline-danger" title="Clear Search"><i class="fa-solid fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>

            <button class="btn btn-purple btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fa fa-user-plus me-1"></i> Add User</button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>User</th><th>Contact / Info</th><th>Role</th><th class="text-center">Manage</th></tr></thead>
                <tbody>
                    <?php if($users_query->num_rows > 0): ?>
                        <?php while($user = $users_query->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php $img = !empty($user['profile_picture']) ? '../uploads/' . $user['profile_picture'] : 'https://ui-avatars.com/api/?name='.urlencode($user['full_name']).'&background=6c5ce7&color=fff'; ?>
                                        <img src="<?php echo $img; ?>" class="table-pic">
                                        <div>
                                            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                                            <span class="small text-muted"><?php echo htmlspecialchars($user['email']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="small">
                                    <?php echo !empty($user['course_year']) ? htmlspecialchars($user['course_year']) : '<span class="text-muted">No Course Set</span>'; ?><br>
                                    <?php echo !empty($user['phone_number']) ? htmlspecialchars($user['phone_number']) : '<span class="text-muted">No Phone</span>'; ?>
                                </td>
                                <td><span class="badge <?php echo $user['is_admin'] ? 'bg-primary' : 'bg-secondary'; ?>"><?php echo $user['is_admin'] ? 'Admin' : 'Student'; ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="admin-users.php?view_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></a>
                                        <a href="admin-users.php?edit_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-warning"><i class="fa fa-edit"></i></a>
                                        <a href="admin-users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete user?')"><i class="fa fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">
                            <?php echo (isset($_GET['search']) && !empty($_GET['search'])) ? 'No users found matching your search.' : 'No users found.'; ?>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h5 class="modal-title font-weight-bold">Create New User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label small font-weight-bold">Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small font-weight-bold">Email Address</label><input type="email" name="email" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small font-weight-bold">Password</label><input type="password" name="password" class="form-control" required></div>
                <div class="mb-3"><label class="form-label small font-weight-bold">System Role</label>
                    <select name="is_admin" class="form-select" required><option value="0">Student</option><option value="1">Administrator</option></select>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" name="add_user" class="btn btn-purple w-100">Add User</button></div>
        </form>
    </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>