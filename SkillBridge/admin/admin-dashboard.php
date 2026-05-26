<?php
require_once '../db.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];

$nav_pic = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=6c5ce7&color=fff';
$pic_query = $conn->query("SELECT profile_picture FROM users WHERE id = $user_id");
if ($pic_query && $pic_query->num_rows > 0) {
    $pic_row = $pic_query->fetch_assoc();
    if (!empty($pic_row['profile_picture'])) { $nav_pic = '../uploads/' . $pic_row['profile_picture']; }
}

$admin_check_sql = "SELECT is_admin FROM users WHERE id = ?";
$stmt_admin = $conn->prepare($admin_check_sql);
$stmt_admin->bind_param("i", $user_id);
$stmt_admin->execute();
$admin_data = $stmt_admin->get_result()->fetch_assoc();
if (!$admin_data || $admin_data['is_admin'] != 1) { header("Location: ../index.php"); exit(); }

$total_users = $conn->query("SELECT COUNT(id) AS count FROM users WHERE is_admin = 0")->fetch_assoc()['count'];
$total_courses = $conn->query("SELECT COUNT(id) AS count FROM courses")->fetch_assoc()['count'];
$total_enrollments = $conn->query("SELECT COUNT(id) AS count FROM enrollments")->fetch_assoc()['count'];
$total_assessments_taken = $conn->query("SELECT COUNT(id) AS count FROM assessment_scores")->fetch_assoc()['count'];

$recent_users = $conn->query("SELECT full_name, email, created_at, profile_picture FROM users WHERE is_admin = 0 ORDER BY created_at DESC LIMIT 5");

// NEW: Fetch Admin Activity Logs from the new table
$system_logs = $conn->query("SELECT a.*, u.full_name FROM activity_logs a JOIN users u ON a.admin_id = u.id ORDER BY a.created_at DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Admin Dashboard | SkillBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary-purple: #6c5ce7; --dark-bg: #1c144d; --light-bg: #f4f7fe; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .header { background: white; z-index: 1000; padding: 15px 0; }
        .log_out { background: #e74c3c; color: white !important; border-radius: 5px; padding: 8px 20px !important; margin-left: 15px; }
        .banner-sub { background-color: var(--dark-bg); padding: 50px 0; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; border-left: 5px solid var(--primary-purple); box-shadow: 0 10px 20px rgba(0,0,0,0.02); height: 100%; transition: 0.3s; }
        .stat-card h3 { font-size: 2.5rem; font-weight: 800; color: var(--primary-purple); margin-bottom: 5px; }
        .table-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 20px rgba(0,0,0,0.02); height: 100%; }
    </style>
</head>
<body>

<header class="header sticky-top shadow-sm">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light p-0">
            <a class="navbar-brand font-weight-bold" href="admin-dashboard.php" style="color:var(--primary-purple); font-size: 24px;">SkillBridge <span class="text-danger small">ADMIN</span></a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item active"><a class="nav-link text-primary font-weight-bold" href="admin-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-courses.php">Manage Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-assessments.php">Manage Assessments</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-users.php">Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin-certificates.php">Certificates</a></li>
                    <li class="nav-item"><a class="nav-link log_out" href="admin-dashboard.php?action=logout">Log Out <i class="fa-solid fa-sign-out-alt ms-1"></i></a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>

<section class="banner-sub text-center">
    <div class="container"><h2 class="text-white font-weight-bold">System Administration</h2></div>
</section>

<div class="container pb-5">
    <div class="row mb-5">
        <div class="col-md-3 mb-4"><div class="stat-card border-left-primary"><p class="text-muted small"><i class="fa-solid fa-users me-2"></i>Total Students</p><h3><?php echo $total_users; ?></h3></div></div>
        <div class="col-md-3 mb-4"><div class="stat-card" style="border-left-color: #2ecc71;"><p class="text-muted small"><i class="fa-solid fa-book me-2"></i>Available Courses</p><h3 style="color:#2ecc71;"><?php echo $total_courses; ?></h3></div></div>
        <div class="col-md-3 mb-4"><div class="stat-card" style="border-left-color: #f1c40f;"><p class="text-muted small"><i class="fa-solid fa-graduation-cap me-2"></i>Course Enrollments</p><h3 style="color:#f1c40f;"><?php echo $total_enrollments; ?></h3></div></div>
        <div class="col-md-3 mb-4"><div class="stat-card" style="border-left-color: #e74c3c;"><p class="text-muted small"><i class="fa-solid fa-clipboard-check me-2"></i>Assessments Taken</p><h3 style="color:#e74c3c;"><?php echo $total_assessments_taken; ?></h3></div></div>
    </div>

    <div class="row">
        <!-- New Users Table -->
        <div class="col-lg-5 mb-4">
            <div class="table-card">
                <h5 class="font-weight-bold mb-4">Recent User Registrations</h5>
                <table class="table table-hover align-middle">
                    <thead><tr><th>Name</th><th>Date Joined</th></tr></thead>
                    <tbody>
                        <?php while($u = $recent_users->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php $img = !empty($u['profile_picture']) ? '../uploads/' . $u['profile_picture'] : 'https://ui-avatars.com/api/?name='.urlencode($u['full_name']).'&background=6c5ce7&color=fff'; ?>
                                    <img src="<?php echo $img; ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                                    <strong><?php echo htmlspecialchars($u['full_name']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($u['email']); ?></small>
                                </td>
                                <td class="small"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- NEW: Activity Logs Table -->
        <div class="col-lg-7 mb-4">
            <div class="table-card">
                <h5 class="font-weight-bold mb-4" style="color: #e74c3c;"><i class="fa fa-shield-halved me-2"></i> System Activity Logs</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light"><tr><th>Admin</th><th>Action</th><th>Details</th><th>Time</th></tr></thead>
                        <tbody>
                            <?php if($system_logs->num_rows > 0): ?>
                                <?php while($log = $system_logs->fetch_assoc()): ?>
                                    <tr>
                                        <td class="small font-weight-bold"><?php echo htmlspecialchars($log['full_name']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($log['action_type']); ?></span></td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($log['action_details']); ?></td>
                                        <td class="small text-muted"><?php echo date('M d, g:i A', strtotime($log['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-3 text-muted">No system activity logged.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>