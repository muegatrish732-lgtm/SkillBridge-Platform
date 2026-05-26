<?php
// profile.php
require_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- Header/Notification Logic ---
$isLoggedIn = true; 
$user_id = $_SESSION['user_id'];
$userName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
$message = '';

// Initialize variables for logged-in users
$unread_count = 0;
$notifications = null;

// Fetch Notifications count and list
$unread_result = $conn->query("SELECT COUNT(id) AS unread FROM notifications WHERE user_id = $user_id AND is_read = 0");
$unread_count = ($unread_result) ? $unread_result->fetch_assoc()['unread'] : 0;
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

// --- Profile Logic ---
// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $age = !empty($_POST['age']) ? intval($_POST['age']) : 'NULL';
    $phone = $conn->real_escape_string($_POST['phone_number']);
    $course_year = $conn->real_escape_string($_POST['course_year']);
    
    // Update session name just in case it changed
    $_SESSION['full_name'] = $full_name;

    // Handle Profile Picture Upload
    $profile_pic_query = "";
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_name = time() . '_' . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $profile_pic_query = ", profile_picture = '$file_name'";
        } else {
            $message = "<div class='alert alert-danger fixed-top w-75 mx-auto mt-4 shadow' style='z-index: 2000;'>Error uploading image.</div>";
        }
    }

    $sql = "UPDATE users SET full_name='$full_name', email='$email', age=$age, phone_number='$phone', course_year='$course_year' $profile_pic_query WHERE id=$user_id";
    
    if ($conn->query($sql)) {
        $message = "<div class='alert alert-success fixed-top w-75 mx-auto mt-4 shadow' style='z-index: 2000;'><i class='fa fa-check-circle me-1'></i> Profile updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger fixed-top w-75 mx-auto mt-4 shadow' style='z-index: 2000;'>Error updating profile.</div>";
    }
}

// Fetch Current User Data
$user_data = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
// Update userName just in case it was updated in DB but not session
$userName = $user_data['full_name']; 
$profile_image = !empty($user_data['profile_picture']) ? 'uploads/' . $user_data['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=6c5ce7&color=fff&size=150';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | SkillBridge</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { 
            --primary-purple: #6c5ce7; 
            --dark-bg: #1c144d; 
            --lime-green: #d4ff3f; 
        }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: #f8f9fa; 
        }
        
        /* Header & Navbar Styles */
        .header { 
            padding: 15px 0; 
            background: white; 
        }
        .navbar-logo {
            height: 65px;
            width: auto;
            object-fit: contain;
            transition: 0.3s;
        }
        .navbar-brand {
            padding-top: 5px;
            padding-bottom: 5px;
        }
        @media (max-width: 991px) {
            .navbar-logo {
                height: 45px;
            }
        }
        
        /* Page Styles */
        .banner-sub { 
            background: linear-gradient(to top, 
                        rgba(28, 20, 77, 0.95) 0%, 
                        rgba(0, 0, 0, 0.4) 50%, 
                        rgba(0, 0, 0, 0.4) 100%), 
                        url('assets/IMG3.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 100px 0; 
            border-bottom-left-radius: 40px; 
            border-bottom-right-radius: 40px; 
            margin-bottom: 50px; 
            color: white;
        }
        
        /* Profile Specific Styles */
        .profile-card { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee; margin-top: -30px; position: relative; z-index: 10;}
        .profile-pic { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 4px solid var(--primary-purple); box-shadow: 0 5px 15px rgba(108, 92, 231, 0.2); margin-bottom: 20px; }
        .btn-purple { background-color: var(--primary-purple); color: white; border: none; font-weight: bold; transition: 0.3s; }
        .btn-purple:hover { background-color: #5b4bc4; color: white; transform: translateY(-2px); }

        /* Unified Footer CSS */
        .footer-con { background: #111; color: white; padding: 80px 0 20px; margin-top: 80px; }
        .copyright { border-top: 1px solid #222; padding-top: 20px; margin-top: 50px; text-align: center; font-size: 14px; color: #666; }
        
        /* Unified Logout Button */
        .log_out { 
            background: #d4ff3f; 
            color: black !important; 
            border-radius: 5px; 
            padding: 8px 18px !important; 
            font-weight: 600;
            transition: 0.3s;
        }
        .log_out:hover { 
            background: #3f8617; 
            transform: translateY(-1px); 
        }
    </style>
</head>
<body>
    <?php echo $message; ?>

    <!-- Navigation Header -->
    <header class="header sticky-top shadow-sm">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light p-0">
                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    <img src="assets/SBLOGO2.png" alt="SkillBridge Logo" class="navbar-logo">
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="courses.php">Browse Courses</a></li>
                        
                        <?php if($isLoggedIn): ?>
                            <li class="nav-item"><a class="nav-link" href="my-courses.php">My Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="assessments.php">Assessments</a></li>
                            <li class="nav-item"><a class="nav-link" href="certifications.php">Certifications</a></li>
                            <li class="nav-item"><a class="nav-link" href="profile.php">My Profile</a></li>
                            
                            <!-- Notifications Dropdown -->
                            <li class="nav-item dropdown mx-2">
                                <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-bell fs-5" style="color: var(--dark-bg);"></i>
                                    <?php if($unread_count > 0): ?>
                                        <span class="position-absolute translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; top: 10px; left: 90%;">
                                            <?php echo $unread_count; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-0 shadow border-0" style="width: 280px; border-radius: 12px; overflow: hidden;">
                                    <div class="bg-light p-3 border-bottom d-flex justify-content-between align-items-center">
                                        <h6 class="m-0 fw-bold small">Notifications</h6>
                                        <a href="my-courses.php?read_notifs=1" class="x-small text-decoration-none" style="font-size: 11px;">Mark all read</a>
                                    </div>
                                    <div class="notif-scroll" style="max-height: 300px; overflow-y: auto;">
                                        <?php if($notifications && $notifications->num_rows > 0): ?>
                                            <?php while($n = $notifications->fetch_assoc()): ?>
                                                <div class="p-3 border-bottom small <?php echo $n['is_read'] == 0 ? 'bg-white' : 'bg-light opacity-75'; ?>">
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($n['title']); ?></div>
                                                    <div class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars($n['message']); ?></div>
                                                </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <div class="p-4 text-center text-muted small">No new notifications.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>

                            <li class="nav-item d-flex align-items-center">
                                <a class="nav-link log_out" href="profile.php?action=logout">Log Out</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <section class="banner-sub text-center">
        <div class="container">
            <h2 class="font-weight-bold display-5">Personal Profile</h2>
            <p class="opacity-75">Manage your account information and preferences.</p>
        </div>
    </section>

    <div class="container pb-5">
        <div class="profile-card">
            <form method="POST" enctype="multipart/form-data" class="row">
                <!-- Left Side: Profile Picture -->
                <div class="col-md-4 text-center border-end pe-md-4 mb-4 mb-md-0">
                    <img src="<?php echo $profile_image; ?>" alt="Profile Picture" class="profile-pic" id="picPreview">
                    <h5 class="font-weight-bold mt-2"><?php echo htmlspecialchars($user_data['full_name']); ?></h5>
                    <p class="text-muted small mb-4"><?php echo htmlspecialchars($user_data['email']); ?></p>
                    
                    <div class="mb-3 text-start">
                        <label class="form-label small font-weight-bold">Update Profile Picture</label>
                        <input class="form-control form-control-sm" type="file" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                    </div>
                </div>

                <!-- Right Side: Personal Details -->
                <div class="col-md-8 ps-md-4">
                    <h5 class="font-weight-bold mb-4" style="color: var(--primary-purple);">Account Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small font-weight-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small font-weight-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small font-weight-bold">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($user_data['phone_number']); ?>" placeholder="e.g. 09123456789">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label small font-weight-bold">Age</label>
                            <input type="number" name="age" class="form-control" value="<?php echo htmlspecialchars($user_data['age']); ?>" placeholder="e.g. 21">
                        </div>
                        <div class="col-md-12 mb-4">
                            <label class="form-label small font-weight-bold">Course / Set & Year</label>
                            <input type="text" name="course_year" class="form-control" value="<?php echo htmlspecialchars($user_data['course_year']); ?>" placeholder="e.g. BSIT 3A">
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-purple px-4 py-2 rounded-pill shadow-sm"><i class="fa fa-save me-2"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer-con text-white">
        <div class="container text-center">
            <div class="copyright">
                <p class="mb-0">© 2026 SkillBridge Platform. Developed for Academic Purposes.</p>
                <small class="opacity-50">S. Cojetia | T. Muega | S. Alameda</small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS is required for Notifications Dropdown and Mobile Menu -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('picPreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>