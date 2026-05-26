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

// 1. ADD / AWARD CERTIFICATE MANUALLY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cert'])) {
    $student_id = intval($_POST['user_id']);
    $course_id = intval($_POST['course_id']);
    $cert_code = $conn->real_escape_string(trim($_POST['certificate_code']));
    
    // Auto-generate code if left blank
    if (empty($cert_code)) {
        $cert_code = 'SB-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }

    $check_cert = $conn->query("SELECT id FROM certificates WHERE user_id = $student_id AND course_id = $course_id");
    if ($check_cert->num_rows > 0) {
        $message = "<div class='alert alert-danger'>Error: This student already has a certificate for this course.</div>";
    } else {
        $insert_sql = "INSERT INTO certificates (user_id, course_id, certificate_code) VALUES ($student_id, $course_id, '$cert_code')";
        if ($conn->query($insert_sql)) { 
            $message = "<div class='alert alert-success'>Certificate successfully awarded to the student!</div>";
            $conn->query("INSERT INTO activity_logs (admin_id, action_type, action_details) VALUES ($current_admin_id, 'AWARD_CERT', 'Manually awarded certificate $cert_code to User ID: $student_id')");
        }
    }
}

// 2. UPDATE CERTIFICATE DETAILS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cert'])) {
    $cert_id = intval($_POST['cert_id']);
    $cert_code = $conn->real_escape_string($_POST['certificate_code']);

    $update_sql = "UPDATE certificates SET certificate_code = '$cert_code' WHERE id = $cert_id";
    if ($conn->query($update_sql)) {
        $message = "<div class='alert alert-success'>Certificate details updated successfully!</div>";
    }
}

// 3. DELETE / REVOKE CERTIFICATE
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM certificates WHERE id = $del_id")) { 
        $message = "<div class='alert alert-warning'>Certificate has been permanently revoked.</div>"; 
        $conn->query("INSERT INTO activity_logs (admin_id, action_type, action_details) VALUES ($current_admin_id, 'REVOKE_CERT', 'Revoked Certificate ID: $del_id')");
    }
}

// Handle Search
$search_query = "";
$search_condition = "";
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = $conn->real_escape_string(trim($_GET['search']));
    $search_condition = " WHERE cert.certificate_code LIKE '%$search_query%' OR u.full_name LIKE '%$search_query%' OR c.title LIKE '%$search_query%' ";
}

// FETCH ALL CERTIFICATES
$certs_query = $conn->query("
    SELECT cert.*, u.full_name, c.title AS course_title 
    FROM certificates cert 
    JOIN users u ON cert.user_id = u.id 
    JOIN courses c ON cert.course_id = c.id 
    $search_condition
    ORDER BY cert.issued_at DESC
");

// FETCH USERS & COURSES FOR THE "ADD" DROPDOWNS
$users_list = $conn->query("SELECT id, full_name, email FROM users WHERE is_admin = 0 ORDER BY full_name ASC");
$courses_list = $conn->query("SELECT id, title FROM courses ORDER BY title ASC");

?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Manage Certificates | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root { --primary-purple: #6c5ce7; --dark-bg: #1c144d; --light-bg: #f4f7fe; --gold: #f1c40f; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .header { background: white; z-index: 1000; padding: 15px 0; }
        .log_out { background: #e74c3c; color: white !important; border-radius: 5px; padding: 8px 20px !important; margin-left: 15px; text-decoration: none; }
        .banner-sub { background-color: var(--dark-bg); padding: 50px 0; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; margin-bottom: 40px; }
        .table-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 20px rgba(0,0,0,0.02); margin-bottom: 30px; }
        .btn-purple { background-color: var(--primary-purple); color: white; border: none; }
        .btn-purple:hover { background-color: #5b4bc4; color: white; }
        
        /* Realistic Certificate Preview Styles */
        .certificate-preview { border: 15px solid var(--dark-bg); padding: 40px; background: #fff; text-align: center; box-shadow: inset 0 0 0 5px var(--gold); }
        .cert-title { font-family: 'Times New Roman', serif; font-size: 3.5rem; color: var(--dark-bg); font-weight: bold; text-transform: uppercase;}
        .cert-subtitle { font-size: 1.2rem; color: #555; margin-bottom: 30px; }
        .cert-name { font-family: 'Times New Roman', serif; font-size: 3rem; color: var(--primary-purple); border-bottom: 2px solid #ddd; display: inline-block; padding: 0 40px; font-style: italic; margin-bottom: 30px;}
        .cert-course { font-size: 1.8rem; font-weight: bold; color: #333; margin: 20px 0; }
        .seal { font-size: 5rem; color: var(--gold); }
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
                    <li class="nav-item"><a class="nav-link" href="admin-users.php">Manage Users</a></li>
                    <li class="nav-item active"><a class="nav-link font-weight-bold text-primary" href="admin-certificates.php">Certificates</a></li>
                    <li class="nav-item"><a class="nav-link log_out" href="admin-dashboard.php?action=logout">Log Out <i class="fa-solid fa-sign-out-alt ms-1"></i></a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>

<section class="banner-sub text-center"><div class="container"><h2 class="text-white font-weight-bold">Certificate Management</h2></div></section>

<div class="container pb-5">
    <?php echo $message; ?>

    <!-- MAIN CERTIFICATES LIST -->
    <div class="table-card shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="m-0 font-weight-bold" style="color: var(--primary-purple);"><i class="fa fa-award me-2"></i> Issued Certificates</h5>
            
            <!-- Search Form -->
            <form method="GET" action="admin-certificates.php" class="d-flex w-50 mx-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by ID code, student, or course..." value="<?php echo htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : ''); ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-search"></i> Search</button>
                    <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                        <a href="admin-certificates.php" class="btn btn-outline-danger" title="Clear Search"><i class="fa-solid fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>

            <button class="btn btn-purple btn-sm" data-bs-toggle="modal" data-bs-target="#addCertModal"><i class="fa fa-plus me-1"></i> Award Certificate</button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID Code</th>
                        <th>Student Name</th>
                        <th>Course / Module</th>
                        <th>Date Issued</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($certs_query->num_rows > 0): ?>
                        <?php while($cert = $certs_query->fetch_assoc()): ?>
                            <tr>
                                <td class="font-monospace fw-bold text-primary"><?php echo $cert['certificate_code']; ?></td>
                                <td><strong><?php echo htmlspecialchars($cert['full_name']); ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($cert['course_title']); ?></span></td>
                                <td class="small"><?php echo date('M d, Y', strtotime($cert['issued_at'])); ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <!-- View Certificate -->
                                        <button class="btn btn-sm btn-outline-primary" title="Preview" 
                                                onclick="previewCert('<?php echo addslashes($cert['full_name']); ?>', '<?php echo addslashes($cert['course_title']); ?>', '<?php echo date('F j, Y', strtotime($cert['issued_at'])); ?>', '<?php echo $cert['certificate_code']; ?>')" 
                                                data-bs-toggle="modal" data-bs-target="#certPreviewModal">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <!-- Edit Certificate Code -->
                                        <button class="btn btn-sm btn-outline-warning" title="Edit details" 
                                                onclick="editCert(<?php echo $cert['id']; ?>, '<?php echo $cert['certificate_code']; ?>')" 
                                                data-bs-toggle="modal" data-bs-target="#editCertModal">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <!-- Delete Certificate -->
                                        <a href="admin-certificates.php?delete=<?php echo $cert['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to revoke and delete this certificate?')" title="Revoke">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">
                            <?php echo (isset($_GET['search']) && !empty($_GET['search'])) ? 'No certificates found matching your search.' : 'No certificates have been issued yet.'; ?>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add / Award Certificate Modal -->
<div class="modal fade" id="addCertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title font-weight-bold">Award Certificate</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small font-weight-bold">Select Student</label>
                        <select name="user_id" class="form-select" required>
                            <option value="" selected disabled>Choose a student...</option>
                            <?php while($u = $users_list->fetch_assoc()): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['full_name']) . ' (' . htmlspecialchars($u['email']) . ')'; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small font-weight-bold">Select Course</label>
                        <select name="course_id" class="form-select" required>
                            <option value="" selected disabled>Choose a course...</option>
                            <?php while($c = $courses_list->fetch_assoc()): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small font-weight-bold">Custom Certificate Code (Optional)</label>
                        <input type="text" name="certificate_code" class="form-control" placeholder="Leave blank to auto-generate">
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_cert" class="btn btn-purple w-100">Award Certificate</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Certificate Modal -->
<div class="modal fade" id="editCertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5 class="modal-title font-weight-bold">Edit Certificate</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="cert_id" id="edit_cert_id">
                    <div class="mb-3">
                        <label class="form-label small font-weight-bold">Certificate ID / Code</label>
                        <input type="text" name="certificate_code" id="edit_cert_code" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" name="update_cert" class="btn btn-warning w-100 fw-bold">Save Changes</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Realistic Certificate Preview Modal -->
<div class="modal fade" id="certPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-0 bg-light">
                <h6 class="m-0 fw-bold text-primary">Certificate Preview</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="certificate-preview">
                    <h1 class="cert-title">Certificate of Completion</h1>
                    <p class="cert-subtitle">This is to proudly certify that</p>
                    <h2 class="cert-name" id="previewStudentName">Student Name</h2>
                    <p class="cert-subtitle">has successfully completed the module requirements for</p>
                    <h3 class="cert-course" id="previewCourseTitle">Course Name</h3>
                    <div class="mt-5 d-flex justify-content-between px-5">
                        <div><p class="mb-1 fw-bold" id="previewDate">Date</p><div style="border-top: 2px solid #333; width: 150px;">Date Issued</div></div>
                        <i class="fa-solid fa-award seal"></i>
                        <div><p class="mb-1" style="font-family:'Brush Script MT', cursive; font-size:1.5rem;">SkillBridge</p><div style="border-top: 2px solid #333; width: 150px;">System Admin</div></div>
                    </div>
                    <p class="mt-4 text-muted small" id="previewCertId">ID: </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editCert(id, code) {
        document.getElementById('edit_cert_id').value = id;
        document.getElementById('edit_cert_code').value = code;
    }

    function previewCert(student, course, date, id) {
        document.getElementById('previewStudentName').innerText = student;
        document.getElementById('previewCourseTitle').innerText = course;
        document.getElementById('previewDate').innerText = date;
        document.getElementById('previewCertId').innerText = "Certificate ID: " + id;
    }
</script>
</body>
</html>