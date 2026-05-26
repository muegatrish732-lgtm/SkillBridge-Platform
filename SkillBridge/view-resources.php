<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) { header("Location: my-courses.php"); exit(); }
$course_id = intval($_GET['id']);

// Safety Check: Verify student is actually enrolled in this course
$enroll_check = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
$enroll_check->bind_param("ii", $user_id, $course_id);
$enroll_check->execute();
if ($enroll_check->get_result()->num_rows == 0) {
    header("Location: my-courses.php");
    exit();
}

$course = $conn->query("SELECT title FROM courses WHERE id = $course_id")->fetch_assoc();
$resources = $conn->query("SELECT * FROM resources WHERE course_id = $course_id ORDER BY uploaded_at ASC");
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Learning Modules | SkillBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary-purple: #6c5ce7; --dark-bg: #1c144d; --light-bg: #f4f7fe; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .header { background: white; padding: 15px 0; }
        .banner-sub { background-color: var(--dark-bg); padding: 60px 0; border-bottom-left-radius: 40px; border-bottom-right-radius: 40px; margin-bottom: 40px; }
        .banner-sub { 
            background: linear-gradient(to top, 
                        rgba(28, 20, 77, 0.95) 0%,   /* Darker at the bottom curve */
                        rgba(0, 0, 0, 0.4) 50%,     /* Mid-level dimming */
                        rgba(0, 0, 0, 0.4) 100%),   /* Top dimming for text clarity */
                        url('assets/IMG3.jpg'); /* Your chosen image */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 100px 0; 
            border-bottom-left-radius: 40px; 
            border-bottom-right-radius: 40px; 
            margin-bottom: 50px; 
            color: white;
        }
        .resource-card { background: white; border-radius: 15px; border: 1px solid #eee; padding: 20px; transition: 0.3s; height: 100%; display: flex; align-items: center; }
        .resource-card:hover { border-color: var(--primary-purple); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .icon-box { font-size: 2.5rem; color: var(--primary-purple); margin-right: 20px; }
    </style>
</head>
<body>
<header class="header sticky-top shadow-sm">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light p-0">
            <a class="navbar-brand font-weight-bold" href="index.php" style="color:var(--primary-purple);">SkillBridge</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="my-courses.php"><i class="fa fa-arrow-left"></i> Back to Dashboard</a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>
<section class="banner-sub text-center">
    <div class="container"><h2 class="text-white font-weight-bold"><?php echo htmlspecialchars($course['title']); ?>: Modules</h2></div>
</section>
<div class="container pb-5">
    <div class="row">
        <?php if($resources->num_rows > 0): ?>
            <?php while($r = $resources->fetch_assoc()): ?>
                <div class="col-lg-6 mb-4">
                    <div class="resource-card">
                        <div class="icon-box">
                            <?php 
                                $ext = $r['file_type'];
                                if($ext == 'pdf') echo '<i class="fa fa-file-pdf text-danger"></i>';
                                elseif(in_array($ext, ['doc', 'docx'])) echo '<i class="fa fa-file-word text-primary"></i>';
                                elseif(in_array($ext, ['ppt', 'pptx'])) echo '<i class="fa fa-file-powerpoint text-warning"></i>';
                                else echo '<i class="fa fa-file-lines"></i>';
                            ?>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 font-weight-bold"><?php echo htmlspecialchars($r['file_name']); ?></h6>
                            <p class="small text-muted mb-2">Learning Material (<?php echo strtoupper($r['file_type']); ?>)</p>
                            <a href="uploads/<?php echo $r['file_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="fa fa-download me-1"></i> Download / Read
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-light p-5 border shadow-sm">
                    <i class="fa fa-info-circle fa-3x text-muted mb-3"></i>
                    <h4>No modules uploaded for this course yet.</h4>
                    <p class="text-muted">Please check back later as the instructor updates the resources.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>