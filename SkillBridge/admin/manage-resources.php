<?php
require_once '../db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$admin_check = $conn->query("SELECT is_admin FROM users WHERE id = $user_id")->fetch_assoc();
if (!$admin_check || $admin_check['is_admin'] != 1) { header("Location: ../index.php"); exit(); }

if (!isset($_GET['id'])) { header("Location: admin-courses.php"); exit(); }
$course_id = intval($_GET['id']);

$course = $conn->query("SELECT title FROM courses WHERE id = $course_id")->fetch_assoc();
if (!$course) { header("Location: admin-courses.php"); exit(); }

$message = '';

// Handle File Upload (FIXED: Escaped quotes in Notification Message)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resource_file'])) {
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $original_file_name = basename($_FILES["resource_file"]["name"]);
    $file_type = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
    $new_file_path = uniqid() . "_" . $original_file_name;
    $target_file = $target_dir . $new_file_path;
    
    $custom_label = trim($_POST['custom_label']);
    $display_name = !empty($custom_label) ? $custom_label : $original_file_name;
    
    if (move_uploaded_file($_FILES["resource_file"]["tmp_name"], $target_file)) {
        $clean_name = $conn->real_escape_string($display_name);
        $clean_path = $conn->real_escape_string($new_file_path);
        
        $conn->query("INSERT INTO resources (course_id, file_name, file_path, file_type) VALUES ($course_id, '$clean_name', '$clean_path', '$file_type')");
        
        // --- LOG ADMIN ACTIVITY ---
        $action_details = $conn->real_escape_string("Uploaded resource: $display_name to course: " . $course['title']);
        $conn->query("INSERT INTO activity_logs (admin_id, action_type, action_details) VALUES ($user_id, 'UPLOAD_RESOURCE', '$action_details')");

        // --- SEND NOTIFICATIONS TO ENROLLED STUDENTS (FIXED) ---
        // Escape the entire message string so single quotes around the module name don't break the SQL
        $course_title_safe = $conn->real_escape_string($course['title']);
        $notif_msg = $conn->real_escape_string("New learning material '$display_name' has been added to $course_title_safe.");
        
        $enrolled_users = $conn->query("SELECT user_id FROM enrollments WHERE course_id = $course_id");
        while($stu = $enrolled_users->fetch_assoc()){
            $stu_id = $stu['user_id'];
            $conn->query("INSERT INTO notifications (user_id, title, message) VALUES ($stu_id, 'New Material Available', '$notif_msg')");
        }

        $message = "<div class='alert alert-success'>Resource uploaded! Notifications sent to enrolled students.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error uploading file.</div>";
    }
}

// Handle File Deletion 
if (isset($_GET['delete_file'])) {
    $file_id = intval($_GET['delete_file']);
    $file_data = $conn->query("SELECT file_path, file_name FROM resources WHERE id = $file_id")->fetch_assoc();
    if ($file_data) {
        @unlink("../uploads/" . $file_data['file_path']);
        $conn->query("DELETE FROM resources WHERE id = $file_id");
        
        // Log Deletion
        $del_name = $conn->real_escape_string($file_data['file_name']);
        $conn->query("INSERT INTO activity_logs (admin_id, action_type, action_details) VALUES ($user_id, 'DELETE_RESOURCE', 'Deleted resource: $del_name')");

        $message = "<div class='alert alert-warning'>Resource deleted successfully.</div>";
    }
}

$resources = $conn->query("SELECT * FROM resources WHERE course_id = $course_id ORDER BY uploaded_at DESC");
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Manage Resources | SkillBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary-purple: #6c5ce7; --dark-bg: #1c144d; --light-bg: #f4f7fe; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .header { background: white; padding: 15px 0; }
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
            <a class="navbar-brand font-weight-bold" href="admin-dashboard.php" style="color:var(--primary-purple);">SkillBridge Admin</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="admin-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link font-weight-bold text-primary" href="admin-courses.php"><i class="fa fa-arrow-left me-1"></i> Back to Courses</a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>
<section class="banner-sub text-center">
    <div class="container"><h2 class="text-white font-weight-bold">Resources for: <?php echo htmlspecialchars($course['title']); ?></h2></div>
</section>
<div class="container pb-5">
    <?php echo $message; ?>
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="table-card">
                <h5 class="mb-4 font-weight-bold" style="color: var(--primary-purple);">Upload New Resource</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold small">Resource Label / Title</label>
                        <input type="text" name="custom_label" class="form-control" placeholder="e.g. Chapter 1: Intro" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-weight-bold small">Select File</label>
                        <input type="file" name="resource_file" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-purple w-100 font-weight-bold py-2"><i class="fa fa-upload me-2"></i> Upload Resource</button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="table-card">
                <h5 class="mb-4 font-weight-bold">Current Materials</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Displayed Label</th><th>File Type</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            <?php while($r = $resources->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($r['file_name']); ?></strong></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo strtoupper($r['file_type']); ?></span></td>
                                    <td class="text-end">
                                        <a href="manage-resources.php?id=<?php echo $course_id; ?>&delete_file=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete permanently?')"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
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